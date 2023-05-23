<?php

namespace Elegant\DataTables\Tests;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Elegant\DataTables\DataTablesServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return __NAMESPACE__ . '\Database\Factories' . Str::after($modelName, __NAMESPACE__ . '\Fixtures\Models') . 'Factory';
        });
    }

    protected function getPackageProviders($app)
    {
        return [DataTablesServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [
            __DIR__ . '/resources/views',
        ]);

        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
