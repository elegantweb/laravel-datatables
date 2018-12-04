<?php

namespace Elegant\DataTables;

use Illuminate\Http\Request;

class DataTableBuilder
{
    /**
     * The source we will fetch result from.
     *
     * @var mixed
     */
    protected $source;

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
     * Sets the source we will fetch result from.
     *
     * @param  mixed $source
     * @return $this
     */
    public function source($source)
    {
        $this->source = $source;

        return $this;
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
     * Applies filters.
     *
     * @param DataTableRequest $dtr
     */
    protected function applyFilter(DataTableRequest $dtr)
    {
        if ($this->defaultFilter and $dtr->hasSearch()) {
            $this->fetcher->globalFilter($dtr->search(), $dtr->searchableColumns());
        }

        if ($this->defaultFilter) {
             $this->fetcher->columnFilter($dtr->searchColumns());
        }

        if ($this->filter) {
            call_user_func($this->filter, $this->source);
        }
    }

    /**
     * Applies sort.
     *
     * @param DataTableRequest $dtr
     */
    protected function applySort(DataTableRequest $dtr)
    {
        if ($this->defaultSort) {
            $this->fetcher->sort($dtr->order(), $dtr->orderableColumns());
        }

        if ($this->sort) {
            call_user_func($this->sort, $this->source);
        }
    }

    /**
     * Applies paging.
     *
     * @param DataTableRequest $dtr
     */
    protected function applySort(DataTableRequest $dtr)
    {
        if ($dtr->hasPaging()) {
            $this->fetcher->paginate($dtr->start(), $dtr->length());
        }
    }

    /**
     * Fetches data.
     *
     * @param  DataTableRequest $dtr
     * @return array Including total, total filtered, data
     */
    protected function fetch(DataTableRequest $dtr)
    {
        $total = $this->fetcher->count();

        $this->applyFilter($dtr);

        $totalFiltered = $this->fetcher->count();

        $this->applySort($dtr);
        $this->applyPaging($dtr);

        $data = $this->fetcher->fetch($dtr->columns());

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
        $processor = $this->createProcessor();

        $processor->add($this->addon);
        $processor->raw($this->raw);
        $processor->include($this->include);
        $processor->exclude($this->exclude);

        $data = $processor->process($data);

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
        list($total, $totalFiltered, $data) = $this->fetch($dtr);

        $this->process($data);

        return new DataTable(
            $draw, $total, $totalFiltered, $data
        );
    }
}
