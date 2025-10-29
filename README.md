# This will send to textract for scanning image.

This package allows you to scan document with laravel.[Amazon Simple Notification Service (Amazon SNS)](https://aws.amazon.com/sns/) is used for scan document. It is using offical [AWS SDK for PHP](https://github.com/aws/aws-sdk-php) and [Amazon SNS Message Validator for PHP](https://github.com/aws/aws-php-sns-message-validator).

## Installation

You can install the package via composer:

```bash
composer require webmavens/laravelscandocument
```

Publish migrate file.

```php
php artisan vendor:publish --tag="laravelscandocument-migrations"
```

```php
php artisan migrate
```

## AWS Setup (Automatic)

This package includes a powerful command that will automatically create and configure all required AWS resources for you — including:

- S3 Bucket (for document storage)
- SNS Topic (for Textract notifications)
- IAM Role (for Textract permissions)
- IAM User (with access keys)

All credentials and ARNs will be automatically written into your .env file.

## Run the setup command

```php
php artisan aws:setup
```

You’ll be asked for your AWS Admin Access Key, Secret Key, and Region.

Once the command completes, it will output details of the created AWS resources and save the following environment variables automatically:

```php
AWS_ACCESS_KEY_ID=YOUR_NEW_ACCESS_KEY
AWS_SECRET_ACCESS_KEY=YOUR_NEW_SECRET_KEY
AWS_DEFAULT_REGION=YOUR_REGION
AWS_BUCKET=YOUR_BUCKET_NAME
AWS_SNS_TOPIC_ID=YOUR_SNS_TOPIC_ARN
AWS_ARN_TOPIC_ID=YOUR_TEXTRACT_ROLE_ARN
```

## Callback URL

When the command creates your SNS topic, it automatically subscribes your callback endpoint:
```php
https://{YOUR_DOMAIN_NAME}/textractCallback
```

**Note :- Please do not set up raw message delivery for callback url.**

## Usage

- Send document to scan

```php
$laravelScandocument = new \Webmavens\LaravelScandocument\LaravelScandocument();
// $path = File path from s3 eg. uploads/test.jpg
// $jobtag = Type of document
$response = $laravelScandocument->sendDocToScan($path, $jobtag); //$jobtag is optional.It should be string.
```
- This will upload your document to AWS Textract and process it automatically and return JOBID in response.
- You’ll receive the extracted data via your SNS callback endpoint (/textractCallback).

- You will find scan document text in **laravel_scandocument_data** table.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [webmavens](https://github.com/webmavens)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
