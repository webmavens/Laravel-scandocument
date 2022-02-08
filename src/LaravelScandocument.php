<?php

namespace Webmavens\LaravelScandocument;

use Webmavens\LaravelScandocument\Config\AwsCredentialsConfig;
use Aws\S3\S3Client;
use Aws\Sns\Message;
use Webmavens\LaravelScandocument\Services\LaravelScandocumentService;

class LaravelScandocument
{
	public static function sendDocToScan($imgPath,$jobTag = null)
	{
        $result = LaravelScandocumentService::sendDoc($imgPath,$jobTag);
        if ($result) {
            return $result['JobId'];
        } else {
            return false;
        }
	}

	public static function scanDocument($contents)
	{
		$message = Message::fromJsonString($contents);
		if ($message['Type'] === 'SubscriptionConfirmation') {
		   // Confirm the subscription by sending a GET request to the SubscribeURL
		   file_get_contents($message['SubscribeURL']);
		}
		if ($message['Type'] === 'Notification') {
		   // Do whatever you want with the message body and data.
			$data = json_decode($message['Message'], true);
			if(isset($data['JobId']) && $data['JobId'] != ''){
				$result = LaravelScandocumentService::getContent($data['JobId']);
				$docContent = LaravelScandocumentService::extractLinesFromDoc($result);
				return $docContent;
			}
		}
		if ($message['Type'] === 'UnsubscribeConfirmation') {
		    // provided as the message's SubscribeURL field.
		    file_get_contents($message['SubscribeURL']);
		}
	}
}
