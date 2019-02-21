<?php

namespace Elegant\DataTables;

use Exception;
use Elegant\DataTables\Contracts\Engine;
use Elegant\DataTables\Contracts\Processor;

class Builder
{
    /**
     * Datatable request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Engine to interact with.
     *
     * @var Engine
     */
    protected $engine;

    /**
     * Processor to interact with.
     *
     * @var Processor
     */
    protected $processor;

    /**
     * Transformer to interact with.
     *
     * @var Transformer
     */
    protected $transformer;

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
     * Enable default filter functions?
     *
     * @var bool
     */
    protected $defaultFilters = true;

    /**
     * Enable default sort functions?
     *
     * @var bool
     */
    protected $defaultSorts = true;

    /**
     * Custom filter function.
     *
     * @var callable
     */
    protected $filter;

    /**
     * Custom sort function.
     *
     * @var callable
     */
    protected $sort;

    /**
     * Custom filter functions for columns.
     *
     * @var array
     */
    protected $columnFilters = [];

    /**
     * Custom sort functions for columns.
     *
     * @var array
     */
    protected $columnSorts = [];

    /**
     * @param Request $request
     * @param Engine $engine
     * @param Processor $processor
     * @param Transformer $transformer
     */
    public function __construct(Request $request, Engine $engine, Processor $processor, Transformer $transformer)
    {
        $this->request = $request;
        $this->engine = $engine;
        $this->processor = $processor;
        $this->transformer = $transformer;
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
     * Returns the engine.
     *
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Returns the processor.
     *
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Returns the transformer.
     *
     * @return Transformer
     */
    public function getTransformer()
    {
        return $this->transformer;
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
     * Enables/Disables default filter functions.
     *
     * @return bool $value
     * @return $this
     */
    public function defaultFilters(bool $value)
    {
        $this->defaultFilters = $value;

        return $this;
    }

    /**
     * Enables/Disables default sort functions.
     *
     * @return bool $value
     * @return $this
     */
    public function defaultSorts(bool $value)
    {
        $this->defaultSorts = $value;

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
     * Sets custom filter function for the column.
     *
     * @param string $name Column name
     * @param callable $callback
     * @return $this
     */
    public function columnFilter($name, callable $callback)
    {
        $this->columnFilters[$name] = $callback;

        return $this;
    }

    /**
     * Sets custom sort function for the column.
     *
     * @param string $name Column name
     * @param callable $callback
     * @return $this
     */
    public function columnSort($name, callable $callback)
    {
        $this->columnSorts[$name] = $callback;

        return $this;
    }

    /**
     * Returns safe searchable columns.
     *
     * @return array
     */
    protected function searchableColumns()
    {
        $columns = $this->request->searchableColumns();

        $this->addCustomFilters($columns);
        $this->filterSafeColumns($columns);

        return $columns;
    }

    /**
     * Returns safe search columns.
     *
     * @return array
     */
    protected function searchColumns()
    {
        $columns = $this->request->searchColumns();

        $this->addCustomFilters($columns);
        $this->filterSafeColumns($columns);

        return $columns;
    }

    /**
     * Returns safe orderable columns.
     *
     * @return array
     */
    protected function orderableColumns()
    {
        $columns = $this->request->orderableColumns();

        $this->addCustomSorts($columns);
        $this->filterSafeColumns($columns);

        return $columns;
    }

    /**
     * Returns safe order columns.
     *
     * @return array
     */
    protected function orderColumns()
    {
        $columns = $this->request->orderColumns();

        $this->addCustomSorts($columns);
        $this->filterSafeColumns($columns);

        return $columns;
    }

    /**
     * Adds custom column filters to the column collection.
     *
     * @param array $columns
     */
    protected function addCustomFilters(&$columns)
    {
        foreach ($columns as &$column) {
            $column['filter'] = $this->columnFilters[$column['name']] ?? null;
        }
    }

    /**
     * Adds custom column sorts to the column collection.
     *
     * @param array $columns
     */
    protected function addCustomSorts(&$columns)
    {
        foreach ($columns as &$column) {
            $column['sort'] = $this->columnSorts[$column['name']] ?? null;
        }
    }

    /**
     * Filters safe columns.
     *
     * @param array $columns
     */
    protected function filterSafeColumns(&$columns)
    {
        $columns = array_filter($columns, function ($column) {
            return $this->isSafe($column['name']);
        });
    }

    /**
     * Applies filter.
     */
    protected function applyFilter()
    {
        if ($this->defaultFilters and $this->request->hasSearch()) {
            $this->engine->globalFilter($this->request->search(), $this->searchableColumns());
        }

        if ($this->defaultFilters) {
            $this->engine->columnFilter($this->searchColumns());
        }

        if ($this->filter) {
            $this->engine->call($this->filter);
        }
    }

    /**
     * Applies select.
     */
    protected function applySelect()
    {
        $this->engine->select($this->request->columns());
    }

    /**
     * Applies sort.
     */
    protected function applySort()
    {
        if ($this->defaultSorts) {
            $this->engine->sort($this->orderColumns());
        }

        if ($this->sort) {
            $this->engine->call($this->sort);
        }
    }

    /**
     * Applies paging.
     */
    protected function applyPaging()
    {
        if ($this->request->hasPaging()) {
            $this->engine->paginate($this->request->start(), $this->request->length());
        }
    }

    /**
     * Results.
     *
     * @return array Including total, total filtered, data
     */
    protected function results()
    {
        $this->engine->reset();

        $total = $this->engine->count();

        if (0 === $total) {
            return [0, 0, []];
        }

        $this->applyFilter();

        $totalFiltered = $this->engine->count();

        if (0 === $totalFiltered) {
            return [$total, 0, []];
        }

        $this->applySelect();
        $this->applySort();
        $this->applyPaging();

        $records = $this->engine->get();

        $data = $this->process($records);

        $this->transform($data);

        return [
            $total, $totalFiltered, $data
        ];
    }

    /**
     * Processes the records.
     *
     * @param mixed $records
     * @return array
     */
    protected function process($records)
    {
        $this->processor->add($this->addon);
        $this->processor->raw($this->raw);
        $this->processor->include($this->include);
        $this->processor->exclude($this->exclude);

        $data = $this->processor->process($records);

        return $data;
    }

    /**
     * Transforms the data.
     *
     * @param array $data
     */
    protected function transform(&$data)
    {
        $this->transformer->transform($data);
    }

    /**
     * Error results.
     *
     * @param Exception $exception
     * @return array Including total, total filtered, data, error
     */
    protected function errorResults(Exception $exception)
    {
        logger()->error($exception);

        if (config('app.debug')) {
            throw $exception;
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
