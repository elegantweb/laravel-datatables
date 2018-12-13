<?php

namespace Elegant\DataTables;

use Elegant\DataTables\Engines\QueryEngine;
use Elegant\DataTables\Engines\EloquentEngine;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\ServiceProvider;

class DataTablesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        Model::setFactory($this->app['datatables']);

        $this->registerEngines();
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
     * Register default engines.
     *
     * @param  Factory  $factory
     * @return void
     */
    protected function registerEngines($factory)
    {
        foreach (['query', 'eloquent'] as $engine) {
            $this->{'register'.ucfirst($engine).'Engine'}($this->app['datatables']);
        }
    }

    /**
     * Register the query engine.
     *
     * @param  Factory  $factory
     * @return void
     */
    protected function registerQueryEngine($factory)
    {
        $factory->extend(QueryBuilder::class, function ($source) {
            return new QueryEngine($source);
        });
    }

    /**
     * Register the eloquent engine.
     *
     * @param  Factory  $factory
     * @return void
     */
    protected function registerEloquentEngine($factory)
    {
        $factory->extend(EloquentBuilder::class, function ($source) {
            return new EloquentEngine($source);
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
