<?php

namespace Elegant\DataTables;

use JsonSerializable;
use Elegant\DataTables\Contracts\Processor;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class Model implements JsonSerializable, Jsonable, Arrayable
{
    /**
     * Datatable factory instance.
     *
     * @var Factory
     */
    protected static $factory;

    /**
     * Datatable instance.
     *
     * @var DataTable|null
     */
    protected $datatable;

    /**
     * Source we will get results from.
     *
     * @var object
     */
    protected $source;

    /**
     * Processor instance.
     *
     * @var Processor
     */
    protected $processor;

    /**
     * Columns that should be added to final result.
     *
     * @var array
     */
    protected $addon = [];

    /**
     * Columns that should not be escaped.
     *
     * @var array
     */
    protected $raw = [];

    /**
     * Columns that should be included at final result.
     *
     * @var array
     */
    protected $include = [];

    /**
     * Columns that should be excluded from final result.
     *
     * @var array
     */
    protected $exclude = [];

    /**
     * Whitelisted columns for order and search.
     *
     * @var array
     */
    protected $whitelist = [];

    /**
     * Blacklisted columns for order and search.
     *
     * @var array
     */
    protected $blacklist = [];

    /**
     * Status of default filter.
     *
     * @var bool
     */
    protected $defaultFilters = true;

    /**
     * Status of default sort.
     *
     * @var bool
     */
    protected $defaultSorts = true;

    /**
     * Columns that have custom filter function.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Columns that have custom sort function.
     *
     * @var array
     */
    protected $sorts = [];

    /**
     * @param object $source
     * @param Processor|null $processor
     */
    public function __construct($source, Processor $processor = null)
    {
        $this->source = $source;
        $this->processor = $processor;
    }

    /**
     * Returns the factory instance.
     *
     * @return Factory
     */
    public static function getFactory()
    {
        return static::$factory;
    }

    /**
     * Sets the factory instance.
     *
     * @param Factory $factory
     */
    public static function setFactory(Factory $factory)
    {
        static::$factory = $factory;
    }

    /**
     * Returns the datatable instance.
     *
     * @return DataTable
     */
    public function datatable()
    {
        if ($this->datatable) {
            return $this->datatable;
        } else {
            return $this->datatable = $this->make();
        }
    }

    /**
     * Creates new datatable builder.
     *
     * @return Builder
     */
    public function makeBuilder()
    {
        return static::$factory->make($this->source, $this->processor);
    }

    /**
     * Create the datatable of the model.
     *
     * @return DataTable
     */
    public function make()
    {
        $builder = $this->makeBuilder();

        $this->addAddonColumns($builder);

        $this->setFilter($builder);
        $this->setSort($builder);

        $this->setColumnFilters($builder);
        $this->setColumnSorts($builder);

        $builder->raw($this->raw);
        $builder->include($this->include);
        $builder->exclude($this->exclude);

        $builder->whitelist($this->whitelist);
        $builder->blacklist($this->blacklist);

        $builder->defaultFilters($this->defaultFilters);
        $builder->defaultSorts($this->defaultSorts);

        $dt = $builder->build();

        return $dt;
    }

    /**
     * Adds addon columns to the builder.
     *
     * @param Builder $builder
     */
    protected function addAddonColumns(Builder $builder)
    {
        foreach ($this->addon as $name) {
            if (method_exists($this, $method = sprintf('column%s', Str::studly($name)))) {
                $builder->add($name, [$this, $method]);
            }
        }
    }

    /**
     * Sets custom filter function if there were any.
     *
     * @param Builder $builder
     */
    protected function setFilter(Builder $builder)
    {
        if (method_exists($this, 'filter')) {
            $builder->filter([$this, 'filter']);
        }
    }

    /**
     * Sets custom sort function if there were any.
     *
     * @param Builder $builder
     */
    protected function setSort(Builder $builder)
    {
        if (method_exists($this, 'sort')) {
            $builder->sort([$this, 'sort']);
        }
    }

    /**
     * Sets columns' custom filter functions.
     *
     * @param Builder $builder
     */
    protected function setColumnFilters(Builder $builder)
    {
        foreach ($this->filters as $name) {
            if (method_exists($this, $method = sprintf('column%sFilter', Str::studly(str_replace('.', '_', $name))))) {
                $builder->columnFilter($name, [$this, $method]);
            }
        }
    }

    /**
     * Sets columns' custom sort functions.
     *
     * @param Builder $builder
     */
    protected function setColumnSorts(Builder $builder)
    {
        foreach ($this->sorts as $name) {
            if (method_exists($this, $method = sprintf('column%sSort', Str::studly($name)))) {
                $builder->columnSort($name, [$this, $method]);
            }
        }
    }

    /**
     * Array representation of the datatable.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->datatable()->toArray();
    }

    /**
     * Specifies data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->datatable()->jsonSerialize();
    }

    /**
     * JSON representation of the datatable.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return $this->datatable()->toJson($options);
    }
}
