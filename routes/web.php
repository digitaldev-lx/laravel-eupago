<?php

use DigitaldevLx\LaravelEupago\Http\Controllers\CallbackController;
use DigitaldevLx\LaravelEupago\Http\Controllers\MBController;
use DigitaldevLx\LaravelEupago\Http\Controllers\MBWayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EuPago Routes
|--------------------------------------------------------------------------
*/

Route::get('callback', [CallbackController::class, 'callback'])->name('callback');

