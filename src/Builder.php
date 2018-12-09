<?php

namespace Elegant\DataTables;

use Elegant\DataTables\Contracts\Driver;
use Elegant\DataTables\Contracts\Processor;

class Builder
{
    /**
     * Driver to interact with.
     *
     * @var Driver
     */
    protected $driver;

    /**
     * Processor to interact with.
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
     * User filter.
     *
     * @var callable
     */
    protected $filter;

    /**
     * User sort.
     *
     * @var callable
     */
    protected $sort;

    /**
     * Enable default filter?
     *
     * @var bool
     */
    protected $defaultFilter = true;

    /**
     * Enable default sort?
     *
     * @var bool
     */
    protected $defaultSort = true;

    /**
     * @param Request $request
     * @param Driver $driver
     * @param Processor $processor
     */
    public function __construct(Request $request, Driver $driver, Processor $processor)
    {
        $this->request = $request;
        $this->driver = $driver;
        $this->processor = $processor;
    }

    /**
     * Returns the request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Returns the driver.
     *
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Returns the processor.
     *
     * @return Driver
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Adds a new column to the result.
     *
     * @param  string $name
     * @param  mixed $data
     * @return $this
     */
    public function add($name, $data)
    {
        $this->addon[] = ['name' => $name, 'data' => $data];

        return $this;
    }

    /**
     * Sets raw columns, raw columns won't be escaped.
     *
     * @param  array $keys
     * @return $this
     */
    public function raw(array $keys)
    {
        $this->raw = $keys;

        return $this;
    }

    /**
     * Columns to be included to the result.
     *
     * @param  array $keys
     * @return $this
     */
    public function include(array $keys)
    {
        $this->include = $keys;

        return $this;
    }

    /**
     * Columns to be excluded from the result.
     *
     * @param  array $keys
     * @return $this
     */
    public function exclude(array $keys)
    {
        $this->exclude = $keys;

        return $this;
    }

    /**
     * Sets whitelisted columns, whitelisted columns will be orderable and searchable.
     *
     * @param  array $keys
     * @return $this
     */
    public function whitelist(array $keys)
    {
        $this->whitelist = $keys;

        return $this;
    }

    /**
     * Pushes columns to the whitelisted columns.
     *
     * @param  array $keys
     * @return $this
     */
    public function pushToWhitelist(array $keys)
    {
        $this->whitelist = array_merge($this->whitelist, $keys);

        return $this;
    }

    /**
     * Sets blacklisted columns, whitelisted columns won't be orderable and searchable.
     *
     * @param  array $keys
     * @return $this
     */
    public function blacklist(array $keys)
    {
        $this->blacklist = $keys;

        return $this;
    }

    /**
     * Pushes columns to the blacklisted columns.
     *
     * @param  array $keys
     * @return $this
     */
    public function pushToBlacklist(array $keys)
    {
        $this->blacklist = array_merge($this->blacklist, $keys);

        return $this;
    }

    /**
     * Indicates if the key is in blacklist.
     *
     * @param  string $key
     * @return bool
     */
    public function isBlacklisted($key)
    {
        if (empty($this->whitelist)) {
            return !in_array($key, $this->blacklist);
        } else {
            return in_array($key, $this->whitelist);
        }
    }

    /**
     * Enables default filter.
     *
     * @return $this
     */
    public function enableDefaultFilter()
    {
        $this->defaultFilter = true;

        return $this;
    }

    /**
     * Disabled default filter.
     *
     * @return $this
     */
    public function disableDefaultFilter()
    {
        $this->defaultFilter = false;

        return $this;
    }

    /**
     * Enables default sort.
     *
     * @return $this
     */
    public function enableDefaultSort()
    {
        $this->defaultSort = true;

        return $this;
    }

    /**
     * Disabled default sort.
     *
     * @return $this
     */
    public function disableDefaultSort()
    {
        $this->defaultSort = false;

        return $this;
    }

    /**
     * Sets custom filter function.
     *
     * @param  callable $callback
     * @return $this
     */
    public function filter(callable $callback)
    {
        $this->filter = $callback;

        return $this;
    }

    /**
     * Sets custom sort function.
     *
     * @param  callable $callback
     * @return $this
     */
    public function sort(callable $callback)
    {
        $this->sort = $callback;

        return $this;
    }

    /**
     * Applies filter.
     */
    protected function applyFilter()
    {
        if ($this->defaultFilter and $this->request->hasSearch()) {
            $this->driver->globalFilter($this->request->search(), $this->searchableColumns());
        }

        if ($this->defaultFilter) {
             $this->driver->columnFilter($this->searchColumns());
        }

        if ($this->filter) {
            $this->driver->use($this->filter);
        }
    }

    /**
     * Applies sort.
     */
    protected function applySort()
    {
        if ($this->defaultSort) {
            $this->driver->sort($this->request->order(), $this->orderableColumns());
        }

        if ($this->sort) {
            $this->driver->use($this->sort);
        }
    }

    /**
     * Applies paging.
     */
    protected function applyPaging()
    {
        if ($this->request->hasPaging()) {
            $this->driver->paginate($this->request->start(), $this->request->length());
        }
    }

    /**
     * Results.
     *
     * @return array Including total, total filtered, data
     */
    protected function results()
    {
        $this->driver->reset();

        $total = $this->driver->count();

        if (0 == $total) {
            return [0, 0, []];
        }

        $this->applyFilter();

        $totalFiltered = $this->driver->count();

        if (0 == $totalFiltered) {
            return [$total, 0, []];
        }

        $this->applySort();
        $this->applyPaging();

        $data = $this->driver->get();

        return [
            $total, $totalFiltered, $data,
        ];
    }

    /**
     * Processes the data.
     *
     * @param  mixed $data
     * @return array
     */
    protected function process($data)
    {
        $this->processor->add($this->addon);
        $this->processor->raw($this->raw);
        $this->processor->include($this->include);
        $this->processor->exclude($this->exclude);

        $data = $this->processor->process($data);

        return $data;
    }

    /**
     * Builds the datatable.
     *
     * @return DataTable
     */
    public function build()
    {
        $draw = $this->request->draw();
        list($total, $totalFiltered, $data) = $this->results();

        $this->process($data);

        return new DataTable(
            $draw, $total, $totalFiltered, $data
        );
    }
}
