<?php

namespace Elegant\DataTables;

use Elegant\DataTables\Engines\QueryEngine;
use Elegant\DataTables\Engines\ElequentEngine;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\ServiceProvider;

class DataTablesServiceProvider extends ServiceProvider
{
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
            $factory = new Factory($app->get('datatables.request'));
            $this->registerEngines($factory);
            return $factory;
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
        foreach (['query', 'elequent'] as $engine) {
            $this->{'register'.ucfirst($engine).'Engine'}($factory);
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
     * Register the elequent engine.
     *
     * @param  Factory  $factory
     * @return void
     */
    protected function registerElequentEngine($factory)
    {
        $factory->extend(EloquentBuilder::class, function ($source) {
            return new ElequentEngine($source);
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
