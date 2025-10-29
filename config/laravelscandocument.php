<?php
// config for Webmavens/LaravelScandocument
return [
	'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'),
    'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'),
    'AWS_BUCKET' => env('AWS_BUCKET'),
    'AWS_ARN_TOPIC_ID' => env('AWS_ARN_TOPIC_ID'),
    'AWS_SNS_TOPIC_ID' => env('AWS_SNS_TOPIC_ID'),
];
