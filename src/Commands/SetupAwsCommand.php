<?php

namespace Webmavens\LaravelScandocument\Commands;

use Aws\Iam\IamClient;
use Aws\Sns\SnsClient;
use Aws\Sts\StsClient;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Validator;

class SetupAwsCommand extends Command
{
    protected $signature = 'aws:setup';
    protected $description = 'Setup AWS resources using admin keys and create IAM user';

    public function handle()
    {
        // Step 0: Ask for admin credentials
        $adminKey = $this->askValid(
            'What is your ADMIN aws access key?',
            'aws_admin_key',
            ['required'],
            ''
        );

        $adminSecret = $this->askValid(
            'What is your ADMIN aws secret access key?',
            'aws_secret_key',
            ['required'],
            ''
        );

        $region = $this->askValid(
            'What is your aws region?',
            'aws_region_key',
            ['required'],
            'us-east-1'
        );

        $appName = env('APP_NAME') ?: 'Laravel';
        $appUrl = env('APP_URL');
        $this->info('Starting AWS setup...');

        // ---------- Step 1: Create S3 Bucket using admin credentials ----------
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $adminKey,
                'secret' => $adminSecret,
            ],
        ]);

        $bucketName = strtolower($appName . '-public-' . uniqid());

        try {
            $s3Client->createBucket([
                'Bucket' => $bucketName,
                'CreateBucketConfiguration' => ['LocationConstraint' => $region],
            ]);

            // Disable block public access
            $s3Client->putPublicAccessBlock([
                'Bucket' => $bucketName,
                'PublicAccessBlockConfiguration' => [
                    'BlockPublicAcls' => false,
                    'IgnorePublicAcls' => false,
                    'BlockPublicPolicy' => false,
                    'RestrictPublicBuckets' => false,
                ],
            ]);

            // Then you can attach public-read policy
            $policy = [
                'Version' => '2012-10-17',
                'Statement' => [
                    [
                        'Sid' => 'PublicReadGetObject',
                        'Effect' => 'Allow',
                        'Principal' => '*',
                        'Action' => 's3:GetObject',
                        'Resource' => "arn:aws:s3:::{$bucketName}/*",
                    ],
                ],
            ];

            $s3Client->putBucketPolicy([
                'Bucket' => $bucketName,
                'Policy' => json_encode($policy),
            ]);

            $this->info("S3 Bucket {$bucketName} created with public read access.");
        } catch (AwsException $e) {
            $this->error("S3 Error: " . $e->getMessage());
            return;
        }

        // ---------- Step 2: Create SNS Topic using admin credentials ----------
        $snsClient = new SnsClient([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $adminKey,
                'secret' => $adminSecret,
            ],
        ]);

        $sts = new StsClient([
            'version' => '2011-06-15',
            'region' => $region,
            'credentials' => [
                'key' => $adminKey,
                'secret' => $adminSecret,
            ],
        ]);

        $accountId = $sts->getCallerIdentity()['Account'];
        $snsTopicName = strtolower($appName . '-sns-' . uniqid());
        $snsEndpointURL = $appUrl . '/textractCallback';

        try {
            $topic = $snsClient->createTopic(['Name' => $snsTopicName]);
            $topicArn = $topic['TopicArn'];

            $snsPolicy = [
                'Version' => '2008-10-17',
                'Id' => '__default_policy_ID',
                'Statement' => [
                    [
                        'Sid' => '__default_statement_ID',
                        'Effect' => 'Allow',
                        'Principal' => ['Service' => 'textract.amazonaws.com'],
                        'Action' => 'SNS:Publish',
                        'Resource' => "arn:aws:sns:{$region}:{$accountId}:{$snsTopicName}",
                    ],
                    [
                        'Sid' => 'AllowOwnerAccess',
                        'Effect' => 'Allow',
                        'Principal' => ['AWS' => '*'],
                        'Action' => [
                            'SNS:GetTopicAttributes',
                            'SNS:SetTopicAttributes',
                            'SNS:AddPermission',
                            'SNS:RemovePermission',
                            'SNS:DeleteTopic',
                            'SNS:Subscribe',
                            'SNS:ListSubscriptionsByTopic',
                            'SNS:Publish',
                            'SNS:Receive',
                        ],
                        'Resource' => "arn:aws:sns:{$region}:{$accountId}:{$snsTopicName}",
                    ],
                ],
            ];

            $snsClient->setTopicAttributes([
                'TopicArn'       => $topicArn,
                'AttributeName'  => 'Policy',
                'AttributeValue' => json_encode($snsPolicy),
            ]);

            $snsClient->subscribe([
                'TopicArn' => $topicArn,
                'Protocol' => 'https',
                'Endpoint' => $snsEndpointURL,
            ]);

            $this->info("SNS Topic {$snsTopicName} created: {$topicArn}");
        } catch (AwsException $e) {
            $this->error("SNS Error: " . $e->getMessage());
            return;
        }

        // ---------- Step 3: Create Textract Role using admin credentials ----------
        $iamClient = new IamClient([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $adminKey,
                'secret' => $adminSecret,
            ],
        ]);

        $roleName = strtolower($appName . '-textract-' . uniqid());
        $trustPolicy = [
            'Version' => '2012-10-17',
            'Statement' => [
                [
                    'Effect'    => 'Allow',
                    'Principal' => ['Service' => 'textract.amazonaws.com'],
                    'Action'    => 'sts:AssumeRole',
                ],
            ],
        ];

        try {
            $role = $iamClient->createRole([
                'RoleName'                 => $roleName,
                'AssumeRolePolicyDocument' => json_encode($trustPolicy),
            ]);

            $iamClient->attachRolePolicy([
                'RoleName'  => $roleName,
                'PolicyArn' => 'arn:aws:iam::aws:policy/AmazonTextractFullAccess',
            ]);

            $publishPolicy = [
                'Version' => '2012-10-17',
                'Statement' => [
                    [
                        'Effect'   => 'Allow',
                        'Action'   => 'sns:Publish',
                        'Resource' => $topicArn,
                    ],
                ],
            ];

            $iamClient->putRolePolicy([
                'RoleName'       => $roleName,
                'PolicyName'     => 'TextractSNSTopicPublishAccess',
                'PolicyDocument' => json_encode($publishPolicy),
            ]);

            $this->info("Textract Role {$roleName} created: {$role['Role']['Arn']}");
        } catch (AwsException $e) {
            $this->error("IAM Role Error: " . $e->getMessage());
            return;
        }

        // ---------- Step 4: Create IAM User and attach policies ----------
        $userName = strtolower($appName . '-user-' . uniqid());

        try {
            $user = $iamClient->createUser(['UserName' => $userName]);

            // Attach AdministratorAccess for simplicity (or attach fine-grained policies)
            $iamClient->attachUserPolicy([
                'UserName' => $userName,
                'PolicyArn' => 'arn:aws:iam::aws:policy/AdministratorAccess',
            ]);

            $accessKey = $iamClient->createAccessKey(['UserName' => $userName]);
            $awsAccessKey = $accessKey['AccessKey']['AccessKeyId'];
            $awsSecretKey = $accessKey['AccessKey']['SecretAccessKey'];

            $this->info("IAM User {$userName} created and access keys generated.");
        } catch (AwsException $e) {
            $this->error("IAM User Error: " . $e->getMessage());
            return;
        }

        // ---------- Step 5: Save all environment variables ----------
        $this->setEnvironmentValue([
            'AWS_ACCOUNT_ID'        => $accountId,
            'AWS_ACCESS_KEY_ID'     => $awsAccessKey,
            'AWS_SECRET_ACCESS_KEY' => $awsSecretKey,
            'AWS_DEFAULT_REGION'    => $region,
            'AWS_BUCKET'            => $bucketName,
            'AWS_SNS_TOPIC_ID'      => $topicArn,
            'AWS_ARN_TOPIC_ID'      => $role['Role']['Arn'],
        ]);

        $this->info('✅ AWS setup completed successfully.');
    }

    protected function setEnvironmentValue(array $values)
    {
        $envFile = base_path('.env');
        $content = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            if (preg_match("/^{$key}=.*$/m", $content)) {
                $content = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $content);
        \Artisan::call('config:clear');
    }

    protected function askValid($question, $field, $rules, $defaultValue)
    {
        $value = $this->ask($question, $defaultValue);

        if ($message = $this->validateInput($rules, $field, $value)) {
            $this->error($message);

            return $this->askValid($question, $field, $rules, $defaultValue);
        }

        return $value;
    }

    protected function validateInput($rules, $fieldName, $value)
    {
        $validator = Validator::make([
            $fieldName => $value,
        ], [
            $fieldName => $rules,
        ]);

        return $validator->fails()
            ? $validator->errors()->first($fieldName)
            : null;
    }
}
