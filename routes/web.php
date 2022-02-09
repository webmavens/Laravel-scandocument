<?php

use Illuminate\Support\Facades\Route;
use Webmavens\LaravelScandocument\Controllers\ScanDocumentController;

Route::post('/textractCallback',[ScanDocumentController::class,'index']);