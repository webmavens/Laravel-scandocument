<?php

namespace Webmavens\LaravelScandocument;

use Webmavens\LaravelScandocument\Models\LaravelScandocumentData;
use Webmavens\LaravelScandocument\Services\LaravelScandocumentService;

class LaravelScandocument
{
	public static function sendDocToScan($imgPath,$jobTag = null)
	{
        $result = LaravelScandocumentService::sendDoc($imgPath,$jobTag);
        $laravelScandocument = new LaravelScandocumentData;
        $laravelScandocument->path = $imgPath;
        if ($result) {
        	$laravelScandocument->jobid = $result['JobId'];
        }
        $laravelScandocument->save();
        return true;
	}
}
