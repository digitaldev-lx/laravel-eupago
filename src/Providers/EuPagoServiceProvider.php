<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Providers;

use DigitaldevLx\LaravelEupago\Console\CheckExpiredReferencesCommand;
use Illuminate\Support\ServiceProvider;

class EuPagoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->setConfigurations();
        $this->setProviders();
    }

    public function boot(): void
    {
        $this->setPublishableFiles();

        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'eupago');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckExpiredReferencesCommand::class,
            ]);
        }
    }

    private function setConfigurations(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/eupago.php', 'eupago'
        );
    }

    private function setProviders(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    private function setPublishableFiles(): void
    {
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../../resources/lang' => resource_path('lang/vendor/eupago'),
        ], 'translations');

        $this->publishes([
            __DIR__.'/../../config/eupago.php' => config_path('eupago.php'),
        ], 'config');
    }
}
