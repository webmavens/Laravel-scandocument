<?php

namespace Webmavens\LaravelScandocument\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Webmavens\LaravelScandocument\Events\ScanDocumentDataReceived;
use Webmavens\LaravelScandocument\Models\LaravelScandocumentData;
use Webmavens\LaravelScandocument\Services\LaravelScandocumentService;

Class ScanDocumentController extends BaseController
{
	public function index()
	{
		$message = Message::fromRawPostData();
		$validator = new MessageValidator();
		// Validate the message and log errors if invalid.
		try {
		   $validator->validate($message);
		} catch (InvalidSnsMessageException $e) {
		   // Pretend we're not here if the message is invalid.
		   http_response_code(404);
		   error_log('SNS Message Validation Error: ' . $e->getMessage());
		   die();
		}
		if ($message['Type'] === 'SubscriptionConfirmation') {
		   // Confirm the subscription by sending a GET request to the SubscribeURL
		   file_get_contents($message['SubscribeURL']);
		}
		if ($message['Type'] === 'Notification') {
			$data = json_decode($message['Message'], true);
			if(isset($data['JobId']) && $data['JobId'] != ''){
				$result = LaravelScandocumentService::getContent($data['JobId']);
				$docContent = LaravelScandocumentService::extractLinesFromDoc($result);
				LaravelScandocumentData::where('jobid', $data['JobId'])
									  ->update([
									  	'data' => $docContent
									  ]);
				event(new ScanDocumentDataReceived($data['JobId']));
			}
		}
		if ($message['Type'] === 'UnsubscribeConfirmation') {
		    // provided as the message's SubscribeURL field.
		    file_get_contents($message['SubscribeURL']);
		}
		return true;
	}
}