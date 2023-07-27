<?php

namespace Webmavens\LaravelScandocument\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ScanDocumentDataReceived
{
    use Dispatchable, SerializesModels;

    public $jobId;

    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }
}