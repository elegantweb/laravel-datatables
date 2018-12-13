<?php

namespace Elegant\DataTables;

use Illuminate\Http\Request as HttpRequest;

/**
 * @see https://datatables.net/manual/server-side
 */
class Request
{
    /**
     * HTTP request instance.
     *
     * @var HttpRequest
     */
    protected $request;

    /**
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Returns the draw number.
     *
     * @return int
     */
    public function draw()
    {
        return $this->filterDraw($this->request->input('draw'));
    }

    /**
     * @param mixed $draw
     * @return int
     */
    protected function filterDraw($draw)
    {
        return filter_var($draw, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
    }

    /**
     * Returns the start point of the data.
     *
     * @return int|null
     */
    public function start()
    {
        return $this->filterStart($this->request->input('start'));
    }

    /**
     * @param mixed $start
     * @return int|null
     */
    protected function filterStart($start)
    {
        return filter_var($start, FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
    }

    /**
     * Returns the length of the data.
     *
     * @return int|null
     */
    public function length()
    {
        return $this->filterLength($this->request->input('length'));
    }

    /**
     * @param mixed $length
     * @return int|null
     */
    protected function filterLength($length)
    {
        $filtered = filter_var($length, FILTER_VALIDATE_INT, ['options' => ['default' => null]]);

        // cause of security reasons we have to limit length value
        if (null === $filtered) {
            return null;
        } else {
            return min($filtered, 100);
        }
    }

    /**
     * Indicates if we have paging.
     *
     * @return bool
     */
    public function hasPaging()
    {
        return $this->start() && $this->length();
    }

    /**
     * Returns the global search.
     *
     * @return array Array including value and regex parameters
     */
    public function search()
    {
        return $this->filterSearch($this->request->input('search'));
    }

    /**
     * Indicates if we have search value.
     *
     * @return bool
     */
    public function hasSearch()
    {
        return $this->request->input('search.value') != '';
    }

    /**
     * Returns orderings.
     *
     * @return array Array of arrays including column index and sort direction
     */
    public function order()
    {
        return $this->filterOrder($this->request->input('order'));
    }

    /**
     * Returns columns.
     *
     * @return array Array of arrays including column data
     */
    public function columns()
    {
        return $this->filterColumns($this->request->input('columns'));
    }

    /**
     * Returns searchable columns.
     *
     * @return array
     */
    public function searchableColumns()
    {
        return array_filter($this->columns(), function ($column) {
            return $column['searchable'];
        });
    }

    /**
     * Returns orderable columns.
     *
     * @return array
     */
    public function orderableColumns()
    {
        return array_filter($this->columns(), function ($column) {
            return $column['orderable'];
        });
    }

    /**
     * Returns columns that have search.
     *
     * @return array
     */
    public function searchColumns()
    {
        return array_filter($this->searchableColumns(), function ($column) {
            return $column['search']['value'] != '';
        });
    }

    /**
     * @param mixed $order
     * @return array
     */
    protected function filterOrder($order)
    {
        return array_map([$this, 'filterOrderValue'], $order);
    }

    /**
     * @param mixed $columns
     * @return array
     */
    protected function filterColumns($columns)
    {
        return array_map([$this, 'filterColumn'], $columns);
    }

    /**
     * @param mixed $search
     * @return array
     */
    protected function filterSearch($search)
    {
        $search['regex'] = filter_var($search['regex'], FILTER_VALIDATE_BOOLEAN);

        return $search;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function filterOrderValue($value)
    {
        $value['dir'] = in_array($value['dir'], ['asc', 'desc']) ? $value['dir'] : 'desc';

        return $value;
    }

    /**
     * @param mixed $column
     * @return array
     */
    protected function filterColumn($column)
    {
        $column['name'] = empty($column['name']) ? $column['data'] : $column['name'];
        $column['searchable'] = filter_var($column['searchable'], FILTER_VALIDATE_BOOLEAN);
        $column['orderable'] = filter_var($column['orderable'], FILTER_VALIDATE_BOOLEAN);
        $column['search'] = $this->filterSearch($column['search']);

        return $column;
    }
}
