<?php

namespace Elegant\DataTables;

use JsonSerializable;
use Illuminate\Support\Str;
use Elegant\DataTables\Contracts\Processor;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Model implements JsonSerializable, Jsonable, Arrayable
{
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
    protected $defaultFilter = true;

    /**
     * Status of default sort.
     *
     * @var bool
     */
    protected $defaultSort = true;

    /**
     * Datatable factory instance.
     *
     * @var Factory
     */
    protected static $factory;

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
     * @param Factory $factory
     */
    public static function setFactory(Factory $factory)
    {
        static::$factory = $factory;
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

        $this->setCustomFilter($builder);
        $this->setCustomSort($builder);

        $builder->raw($this->raw);
        $builder->include($this->include);
        $builder->exclude($this->exclude);

        $builder->whitelist($this->whitelist);
        $builder->blacklist($this->blacklist);

        $builder->defaultFilter($this->defaultFilter);
        $builder->defaultSort($this->defaultSort);

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
    protected function setCustomFilter(Builder $builder)
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
    protected function setCustomSort(Builder $builder)
    {
        if (method_exists($this, 'sort')) {
            $builder->sort([$this, 'sort']);
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
        return $this->datatable()->toJson();
    }
}
