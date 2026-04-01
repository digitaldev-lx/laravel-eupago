<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Http\Controllers\CallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EuPago Routes
|--------------------------------------------------------------------------
*/

Route::get('callback', [CallbackController::class, 'callback'])->name('callback');
