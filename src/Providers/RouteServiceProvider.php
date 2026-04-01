<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::middleware('web')
            ->prefix('eupago')
            ->name('eupago.')
            ->group(__DIR__.'/../../routes/web.php');
    }
}
