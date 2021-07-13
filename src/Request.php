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
        return filter_var($start, FILTER_VALIDATE_INT, ['options' => ['default' => null, 'min_range' => 0]]);
    }

    /**
     * Returns the length of the data.
     *
     * This value may include -1, which means unlimited.
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
        return filter_var($length, FILTER_VALIDATE_INT, ['options' => ['default' => null, 'min_range' => -1]]);
    }

    /**
     * Returns the real length of the data.
     *
     * This value doesn't include -1.
     *
     * @return int|null
     */
    public function realLength()
    {
        $length = $this->length();

        return -1 == $length ? null : $length;
    }

    /**
     * Indicates if we have paging.
     *
     * @return bool
     */
    public function hasPaging()
    {
        return null !== $this->start() && null !== $this->length();
    }

    /**
     * Returns the global search.
     *
     * @return array|null Array including value and regex parameters
     */
    public function search()
    {
        return $this->resolveSearch($this->request->input('search'));
    }

    /**
     * Indicates if we have search value.
     *
     * @return bool
     */
    public function hasSearch()
    {
        return null !== $this->search();
    }

    /**
     * Resolves search.
     *
     * @param mixed $search
     * @return array|null
     */
    protected function resolveSearch($search)
    {
        if (is_array($search) and isset($search['value'], $search['regex']) and '' != $search['value']) {
            return $this->filterSearch($search);
        }
    }

    /**
     * @param array $search
     * @return array
     */
    protected function filterSearch(array $search)
    {
        $search['regex'] = filter_var($search['regex'], FILTER_VALIDATE_BOOLEAN);

        return $search;
    }

    /**
     * Returns orderings.
     *
     * @return array Array of arrays including column index and sort direction
     */
    public function order()
    {
        return $this->resolveOrder($this->request->input('order'));
    }

    /**
     * Resolves order.
     *
     * @param mixed $order
     * @return array
     */
    protected function resolveOrder($order)
    {
        if (is_array($order)) {
            return $this->filterOrder($order);
        } else {
            return [];
        }
    }

    /**
     * @param array $order
     * @return array
     */
    protected function filterOrder(array $order)
    {
        return array_filter(array_map([$this, 'resolveOrderValue'], $order));
    }

    /**
     * @param mixed $value
     * @return array|null
     */
    protected function resolveOrderValue($value)
    {
        if (is_array($value) and isset($value['column'], $value['dir']) and is_numeric($value['column']) and in_array($value['dir'], ['asc', 'desc'])) {
            return $this->filterOrderValue($value);
        }
    }

    /**
     * @param array $value
     * @return array|null
     */
    protected function filterOrderValue(array $value)
    {
        $value['column'] = filter_var($value['column'], FILTER_VALIDATE_INT);

        return $value;
    }

    /**
     * Returns columns.
     *
     * @return array Array of arrays including column data
     */
    public function columns()
    {
        return $this->resolveColumns($this->request->input('columns'));
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
            return isset($column['search']);
        });
    }

    /**
     * Indicates if we have search columns.
     *
     * @return bool
     */
    public function hasSearchColumns()
    {
        return count($this->searchColumns()) > 0;
    }

    /**
     * Returns columns that have order.
     *
     * @return array
     */
    public function orderColumns()
    {
        return array_filter($this->orderableColumns(), function ($column) {
            return isset($column['order']);
        });
    }

    /**
     * Resolves columns.
     *
     * @param mixed $columns
     * @return array
     */
    protected function resolveColumns($columns)
    {
        if (is_array($columns)) {
            return $this->filterColumns($columns);
        } else {
            return [];
        }
    }

    /**
     * @param array $columns
     * @return array
     */
    protected function filterColumns(array $columns)
    {
        return array_filter(array_map([$this, 'resolveColumn'], $columns, array_keys($columns)));
    }

    /**
     * Resolves column.
     *
     * @param mixed $column
     * @param int $index
     * @return array
     */
    protected function resolveColumn($column, $index)
    {
        if (is_array($column) and isset($column['data']) and '' != $column['data']) {
            return $this->filterColumn($column, $index);
        }
    }

    /**
     * @param array $column
     * @param int $index
     * @return array
     */
    protected function filterColumn(array $column, $index)
    {
        $column['name'] = isset($column['name']) && '' != $column['name'] ? $column['name'] : $column['data'];
        $column['searchable'] = isset($column['searchable']) ? filter_var($column['searchable'], FILTER_VALIDATE_BOOLEAN) : false;
        $column['orderable'] = isset($column['orderable']) ? filter_var($column['orderable'], FILTER_VALIDATE_BOOLEAN) : false;
        $column['search'] = isset($column['search']) ? $this->resolveSearch($column['search']) : null;
        $column['order'] = $this->findColumnOrder($index);

        return $column;
    }

    /**
     * Finds column order from order array.
     *
     * @param int $index Column Index
     * @return array|null
     */
    protected function findColumnOrder($index)
    {
        $order = $this->order();

        // we don't have any order
        if (is_null($order)) {
            return null;
        }

        foreach ($order as $key => $value) {
            if ($value['column'] == $index) {
                return ['dir' => $value['dir'], 'pri' => $key];
            }
        }
    }
}
