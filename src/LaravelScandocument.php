<?php

namespace Webmavens\LaravelScandocument;

use Webmavens\LaravelScandocument\Models\LaravelScandocumentData;
use Webmavens\LaravelScandocument\Services\LaravelScandocumentService;

class LaravelScandocument
{
	public static function sendDocToScan($imgPath,$jobTag = null, $type = null)
	{
        $jobId = '';
        if ($type == 'Image') {
            $result = LaravelScandocumentService::sendDocForImage($imgPath,$jobTag);
        } else {
            $result = LaravelScandocumentService::sendDoc($imgPath,$jobTag);
            $laravelScandocument = new LaravelScandocumentData;
            $laravelScandocument->path = $imgPath;
            if ($result) {
                $laravelScandocument->jobid = $result['JobId'];
                $jobId = $result['JobId'];
            }
            $laravelScandocument->save();
            return $jobId;
        }
	}
}
