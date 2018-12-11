<?php

namespace Elegant\DataTables;

use Exception;
use Elegant\DataTables\Contracts\Driver;
use Elegant\DataTables\Contracts\Processor;

class Builder
{
    /**
     * Datatable request instance.
     *
     * @var Driver
     */
    protected $driver;

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
     * Sets the request.
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
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
     * @param string $name
     * @param mixed $data
     * @return $this
     */
    public function add($name, $data)
    {
        $this->addon[$name] = $data;

        return $this;
    }

    /**
     * Sets raw columns, raw columns won't be escaped.
     *
     * @param array $names
     * @return $this
     */
    public function raw(array $names)
    {
        $this->raw = $names;

        return $this;
    }

    /**
     * Columns to be included to the result.
     *
     * @param array $names
     * @return $this
     */
    public function include(array $names)
    {
        $this->include = $names;

        return $this;
    }

    /**
     * Columns to be excluded from the result.
     *
     * @param array $names
     * @return $this
     */
    public function exclude(array $names)
    {
        $this->exclude = $names;

        return $this;
    }

    /**
     * Sets whitelisted columns, whitelisted columns will be orderable and searchable.
     *
     * @param array $names
     * @return $this
     */
    public function whitelist(array $names)
    {
        $this->whitelist = $names;

        return $this;
    }

    /**
     * Pushes columns to the whitelisted columns.
     *
     * @param array $names
     * @return $this
     */
    public function pushToWhitelist(array $names)
    {
        $this->whitelist = array_merge($this->whitelist, $names);

        return $this;
    }

    /**
     * Sets blacklisted columns, whitelisted columns won't be orderable and searchable.
     *
     * @param array $names
     * @return $this
     */
    public function blacklist(array $names)
    {
        $this->blacklist = $names;

        return $this;
    }

    /**
     * Pushes columns to the blacklisted columns.
     *
     * @param array $names
     * @return $this
     */
    public function pushToBlacklist(array $names)
    {
        $this->blacklist = array_merge($this->blacklist, $names);

        return $this;
    }

    /**
     * Indicates if the key is safe to search/order.
     *
     * @param string $name
     * @return bool
     */
    public function isSafe($name)
    {
        if (empty($this->whitelist)) {
            return !in_array($name, $this->blacklist);
        } else {
            return in_array($name, $this->whitelist);
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
     * @param callable $callback
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
     * @param callable $callback
     * @return $this
     */
    public function sort(callable $callback)
    {
        $this->sort = $callback;

        return $this;
    }

    /**
     * Returns safe searchable columns.
     *
     * @return array
     */
    protected function searchableColumns()
    {
        return array_filter($this->request->searchableColumns(), function ($column) {
            return $this->isSafe($column['name']);
        });
    }

    /**
     * Returns safe search columns.
     *
     * @return array
     */
    protected function searchColumns()
    {
        return array_filter($this->request->searchColumns(), function ($column) {
            return $this->isSafe($column['name']);
        });
    }

    /**
     * Returns safe orderable columns.
     *
     * @return array
     */
    protected function orderableColumns()
    {
        return array_filter($this->request->orderableColumns(), function ($column) {
            return $this->isSafe($column['name']);
        });
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
            $this->driver->call($this->filter);
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
            $this->driver->call($this->sort);
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

        if (0 === $total) {
            return [0, 0, []];
        }

        $this->applyFilter();

        $totalFiltered = $this->driver->count();

        if (0 === $totalFiltered) {
            return [$total, 0, []];
        }

        $this->applySort();
        $this->applyPaging();

        $data = $this->driver->get();

        $this->process($data);

        return [
            $total, $totalFiltered, $data
        ];
    }

    /**
     * Processes the data.
     *
     * @param mixed $data
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
     * Error results.
     *
     * @return array Including total, total filtered, data, error
     */
    protected function errorResults(Exception $e)
    {
        logger()->error($e);

        if (config('app.debug')) {
            return [0, 0, [], sprintf("Exception Message:\n\n%s", $e->getMessage())];
        } else {
            return [0, 0, [], 'Server Error'];
        }
    }

    /**
     * Creates new datatable.
     *
     * @param int $total Total records
     * @param int $totalFiltered Total records after filter
     * @param array $data Records data
     * @param string|null $error
     * @return DataTable
     */
    protected function make($total, $totalFiltered, array $data, $error = null)
    {
        return new DataTable($this->request->draw(), $total, $totalFiltered, $data, $error);
    }

    /**
     * Builds the datatable.
     *
     * @return DataTable
     */
    public function build()
    {
        try {
            return $this->make(...$this->results());
        } catch (Exception $e) {
            return $this->make(...$this->errorResults($e));
        }
    }
}
