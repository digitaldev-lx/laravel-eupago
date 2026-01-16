<?php

namespace DigitaldevLx\LaravelEupago\Tests;

use DigitaldevLx\LaravelEupago\Providers\EuPagoServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'DigitaldevLx\\LaravelEupago\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            EuPagoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config()->set('eupago.env', 'test');
        config()->set('eupago.api_key', 'test-api-key');
        config()->set('eupago.channel', 'demo');
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function defineRoutes($router)
    {
        $router->group(['prefix' => 'eupago'], function ($router) {
            require __DIR__.'/../routes/web.php';
        });
    }
}
