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
    protected $addonColumns = [];

    /**
     * Columns that should not be escaped.
     *
     * @var array
     */
    protected $rawColumns = [];

    /**
     * Columns that should be included in final result.
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
     * @param  mixed $value
     * @return $this
     */
    public function addColumn($name, $value)
    {
        $this->addonColumns[$name] = $value;

        return $this;
    }

    /**
     * Sets raw columns, raw columns won't be escaped.
     *
     * @param  array $keys
     * @return $this
     */
    public function rawColumns(array $keys)
    {
        $this->rawColumns = $keys;

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
     * Builds the datatable.
     *
     * @param  Request|null $request
     * @return DataTable
     */
    public function build(Request $request = null)
    {
        $dtr = $this->resolveRequest($request);

        $draw = $dtr->draw();

        $fetcher = $this->createFetcher();

        $total = $fetcher->count();

        if ($this->defaultFilter and $dtr->hasSearch()) $fetcher->globalFilter($dtr->search(), $dtr->searchableColumns());
        if ($this->defaultFilter) $fetcher->columnFilter($dtr->searchColumns());
        if ($this->filter) $fetcher->use($this->filter);

        $totalFiltered = $fetcher->count();

        if ($this->defaultSort) $fetcher->sort($dtr->order());
        if ($this->sort) $fetcher->use($this->sort);

        $fetcher->paginate($dtr->start(), $dtr->length());

        $data = $fetcher->fetch();

        $dp = $this->createDataProcessor();

        $dp->add($this->addonColumns);
        $dp->raw($this->rawColumns);
        $dp->include($this->include);
        $dp->exclude($this->exclude);

        $data = $dp->process($data);

        return new DataTable(
            $draw, $total, $totalFiltered, $data
        );
    }
}
