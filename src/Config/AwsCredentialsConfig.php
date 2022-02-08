<?php

namespace Webmavens\LaravelScandocument\Config;

Class AwsCredentialsConfig
{
	public static function getCredential(){
        $config = array();
        $config['region'] = config('laravelscandocument.AWS_DEFAULT_REGION');
        $config['access_key'] = config('laravelscandocument.AWS_ACCESS_KEY_ID');
        $config['secret_key'] = config('laravelscandocument.AWS_SECRET_ACCESS_KEY');
        $config['bucket'] = config('laravelscandocument.AWS_BUCKET');
        $config['arn_topic_id'] = config('laravelscandocument.AWS_ARN_TOPIC_ID');
        $config['sns_topic_id'] = config('laravelscandocument.AWS_SNS_TOPIC_ID');

        return $config;
    }

    public static function getTextractClient()
    {
        $clientTextract = [
            'region' => config('laravelscandocument.AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key'    => config('laravelscandocument.AWS_ACCESS_KEY_ID'),
                'secret' => config('laravelscandocument.AWS_SECRET_ACCESS_KEY'),
            ]
        ];

        return $clientTextract;
    }
}