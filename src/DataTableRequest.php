<?php

namespace Elegant\DataTables;

use Illuminate\Http\Request;

class DataTableRequest extends Request
{
    protected $draw;
    protected $start;
    protected $length;
    protected $columns = [];
    protected $search = [];
    protected $order = [];

    public function draw()
    {
        return $this->filterDraw($this->input('draw'));
    }

    public function start()
    {
        return $this->filterStart($this->input('start'));
    }

    public function length()
    {
        return $this->filterLength($this->input('length'));
    }

    public function search()
    {
        return $this->filterSearch($request->input('search'));
    }

    public function hasSearch()
    {
        return $this->input('search.value') != '';
    }

    public function order()
    {
        return $this->filterOrder($this->input('order', []));
    }

    public function columns()
    {
        return $this->filterColumns($this->input('columns', []));
    }

    public function searchableColumns()
    {
        $searchable = [];

        foreach ($this->columns as $column) {
            if ($column['searchable']) {
                $searchable[] = $column;
            }
        }

        return $searchable;
    }

    public function searchColumns()
    {
        $search = [];

        foreach ($this->searchableColumns() as $column) {
            if ($column['search']['value'] != '') {
                $search[] = $column;
            }
        }

        return $search;
    }

    public function orderableColumns()
    {
        $orderable = [];

        foreach ($this->columns as $column) {
            if ($column['orderable']) {
                $orderable[] = $column;
            }
        }

        return $orderable;
    }

    protected function filterDraw($draw)
    {
        return filter_var($draw, FILTER_VALIDATE_INT);
    }

    protected function filterStart($start)
    {
        return filter_var($start, FILTER_VALIDATE_INT);
    }

    protected function filterLength($length)
    {
        return min(filter_var($length, FILTER_VALIDATE_INT, ['options' => ['default' => 10]]), 100);
    }

    protected function filterSearch($search)
    {
        $search['regex'] = filter_var($search['regex'], FILTER_VALIDATE_BOOLEAN);
        $search['value'] = filter_var($search['value'], $search['regex'] ? FILTER_VALIDATE_REGEXP : FILTER_SANITIZE_STRING);

        return $search;
    }

    protected function filterOrder($order)
    {
        $filtered = [];

        foreach ($order as $rule) {
            if ($this->isValidOrderRule($rule)) {
                $filtered[] = $this->filterOrderRule($rule);
            }
        }

        return $filtered;
    }

    protected function filterColumns($columns)
    {
        $filtered = [];

        foreach ($columns as $column) {
            if ($this->isValidColumn($column)) {
                $filtered[] = $this->filterColumn($column);
            }
        }

        return $filtered;
    }

    protected function filterColumn($column)
    {
        $column['searchable'] = filter_var($column['searchable'], FILTER_VALIDATE_BOOLEAN);
        $column['orderable'] = filter_var($column['orderable'], FILTER_VALIDATE_BOOLEAN);
        $column['search'] = $this->filterSearch($column['search']);

        return $column;
    }
}
