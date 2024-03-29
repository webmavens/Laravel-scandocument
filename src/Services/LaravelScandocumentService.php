<?php

namespace Webmavens\LaravelScandocument\Services;

use Aws\Textract\TextractClient;
use Throwable;
use Webmavens\LaravelScandocument\Config\AwsCredentialsConfig;

Class LaravelScandocumentService
{
	public static function sendDoc($imgPath, $jobTag = null)
	{
		try
		{
			$configuration = AwsCredentialsConfig::getCredential();
			$clientTextract = AwsCredentialsConfig::getTextractClient();
			$client = new TextractClient($clientTextract);
			$requestData = array();
			if(is_null($jobTag)){
				$jobTag = 'Image';
			}
			$imageName = $imgPath;
			if (stripos($imgPath, '://')) {
				$imageName = basename($imgPath);
			}
	        $requestData =[
	            'DocumentLocation' => [
	                'S3Object' => [
	                    'Bucket' => $configuration['bucket'],
	                    'Name' => $imageName
	                ],
	            ],
	            'JobTag' => $jobTag,
	            'NotificationChannel' => [
	                'RoleArn' => $configuration['arn_topic_id'],
	                'SNSTopicArn' => $configuration['sns_topic_id']
	            ],
	            'FeatureTypes' => ['TABLES', 'FORMS']
	        ];
	        $result = $client->startDocumentAnalysis($requestData);
	        return $result;
	    } catch(Throwable $e){
			report($e);
			return false;
		}
	}

	public static function getContent($jobId)
	{
		try
		{
			$clientTextract = AwsCredentialsConfig::getTextractClient();
			$client = new TextractClient($clientTextract);
			$requestData = array();
	        $requestData =[
	            'JobId' => $jobId,
	        ];
	        $resp = $client->getDocumentAnalysis($requestData);
	        $result = $resp->toArray();

	        return $result;
		} catch(Throwable $e){
			report($e);
			return false;
		}
	}

	public static function extractLinesFromDoc($result)
	{
		$docContent = '';
		if(strtolower($result['JobStatus']) == 'succeeded') {
			# Get the text blocks
            $blocks = $result['Blocks'];
            $docContent = '';
            foreach ($blocks as $key => $value) {
                if (isset($value['BlockType']) && $value['BlockType']) {
                    $blockType = $value['BlockType'];
                    if (isset($value['Text']) && $value['Text']) {
                        $text = $value['Text'];
                        if ($blockType == 'LINE') {
                            $docContent .= $text . "<br>";
                        }
                    }
                }
            }
		} else {
			$docContent = $result;
		}
		if($docContent == ''){
			$docContent = $result;
		}
		return $docContent;
	}

	public static function sendDocForImage($contents, $jobTag = null)
	{
		try
		{
			$clientTextract = AwsCredentialsConfig::getTextractClient();
			$client = new TextractClient($clientTextract);
			
			$requestData = array();
			if(is_null($jobTag)){
				$jobTag = 'Image';
			}
	        $requestData = [
	            'Document' => [
                	'Bytes' => $contents
            	],
            	'FeatureTypes' => ['TABLES'], // REQUIRED
	        ];
	        $result = $client->analyzeDocument($requestData);
	        return $result;
	    } catch(Throwable $e){
			report($e);
			return false;
		}
	}
}