<?php

namespace Elegant\DataTables;

use Illuminate\Support\ServiceProvider;

class DataTablesServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactory();

        $this->registerRequest();
    }

    /**
     * Register datatable factory.
     *
     * @return void
     */
    protected function registerFactory()
    {
        $this->app->singleton('datatables', function ($app) {
            return new Factory($app->get('datatables.request'));
        });
    }

    /**
     * Register datatable request.
     *
     * @return void
     */
    protected function registerRequest()
    {
        $this->app->singleton('datatables.request', function ($app) {
            return new Request($app->get('request'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'datatables', 'datatables.request',
        ];
    }
}
