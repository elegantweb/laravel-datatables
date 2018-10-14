<?php

namespace Elegant\DataTables;

use Illuminate\Http\Request;

class DataTableBuilder
{
    protected $source;
    protected $offset = 0;
    protected $limit = 10;
    protected $columns = [];
    protected $addonColumns = [];
    protected $rawColumns = [];
    protected $search;
    protected $order;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function addColumn($name, $value)
    {
        $this->addonColumns[$name] = ['name' => $name, 'value' => $value];

        return $this;
    }

    public function rawColumns(array $keys)
    {
        $this->rawColumns = $keys;

        return $this;
    }

    protected function isColumnNeeded($key)
    {
        return isset($this->columns[$key]);
    }

    protected function shouldEscapeColumn($key)
    {
        return !in_array($key, $this->rawColumns);
    }

    protected function isColumnSearchable($key)
    {
        if (isset($this->addonColumns[$key])) {
            return false;
        } else {
            return $this->columns[$key]['searchable'];
        }
    }

    protected function isColumnOrderable($key)
    {
        if (isset($this->addonColumns[$key])) {
            return false;
        } else {
            return $this->columns[$key]['orderable'];
        }
    }

    protected function search($search, $column)
    {
        if ($search['regex']) {
            $this->source->where($column['data'], 'REGEX', $search['value']);
        } else {
            $this->source->where($column['data'], 'LIKE', $search['value']);
        }
    }

    protected function filter()
    {
        // global filter
        if ('' != $this->search['value']) {
            foreach ($this->columns as $key => $column) {
                if ($this->isColumnSearchable($key)) {
                    $this->search($this->search, $column);
                }
            }
        }

        // column specific filter
        foreach ($this->columns as $key => $column) {
            if ($this->isColumnSearchable($key)) {
                if ('' != $column['search']['value']) {
                    $this->search($column['search'], $column);
                }
            }
        }
    }

    protected function sort()
    {
        foreach ($this->order as $order) {
            if ($this->isColumnOrderable($order['column'])) {
                $this->source->orderBy($this->columns[$order['column']]['data'], $order['dir']);
            }
        }
    }

    protected function paginate()
    {
        $this->source->offset($this->offset)->limit($this->limit);
    }

    protected function setupSourceColumns(&$row, $model)
    {
        $sourceColumns = array_dot($model->toArray());

        foreach ($sourceColumns as $key => $value) {
            if ($this->isColumnNeeded($key)) {
                $row[$key] = $this->shouldEscapeColumn($key) ? e($value) : $value;
            }
        }
    }

    protected function resolveValue($value, $params, $escape)
    {
        if ($value instanceof Coluser) {
            $value = $value(...$params);
        }
        // No need to escape blade views, so just return the content
        elseif (view()->exists($value)) {
            return (string) view($value, $params);
        }

        if ($escape) {
            return e($value);
        } else {
            return $value;
        }
    }

    protected function setupAddonColumns(&$row, $model)
    {
        foreach ($this->addonColumns as $key => $column) {
            if ($this->isColumnNeeded($key)) {
                $row[$key] = $this->resolveValue($column['value'], compact('model'), $this->shouldEscapeColumn($key));
            }
        }
    }

    protected function setupColumns($model)
    {
        $row = [];

        $this->setupSourceColumns($row, $model);
        $this->setupAddonColumns($row, $model);

        return $row;
    }

    protected function setupRows($models)
    {
        $rows = [];

        foreach ($models as $model) {
            $rows[] = $this->setupColumns($model);
        }

        return $rows;
    }

    protected function resolveColumnKey($column)
    {
        if ('' == $column['name']) {
            return $column['data'];
        } else {
            return $column['name'];
        }
    }

    protected function resolveColumn($column)
    {
        $column['searchable'] = filter_var($column['searchable'], FILTER_VALIDATE_BOOLEAN);
        $column['orderable'] = filter_var($column['orderable'], FILTER_VALIDATE_BOOLEAN);
        $column['search']['regex'] = filter_var($column['search']['regex'], FILTER_VALIDATE_BOOLEAN);

        return $column;
    }

    protected function registerColumns($columns)
    {
        foreach ($columns as $column) {
            $this->columns[$this->resolveColumnKey($column)] = $this->resolveColumn($column);
        }
    }

    protected function registerPaging($start, $length)
    {
        // Don't allow more than 100 records cause of security reasons
        if (($length - $start) > 100) {
            list($this->offset, $this->limit) = [$start, 100];
        } else {
            list($this->offset, $this->limit) = [$start, $length];
        }
    }

    protected function registerSearch($search)
    {
        $this->search = $search;
    }

    protected function registerOrder($order)
    {
        $keys = array_keys($this->columns);

        foreach ($order as $o) {
            $this->order[] = ['column' => $keys[$o['column']]] + $o;
        }
    }

    public function build(Request $request = null)
    {
        $request = $request ?? request();

        $draw = $request->input('draw', 1);

        $this->registerPaging($request->input('start', 0), $request->input('length', 10));
        $this->registerColumns($request->input('columns', []));
        $this->registerSearch($request->input('search'));
        $this->registerOrder($request->input('order'));

        $total = $this->source->count();

        $this->filter();

        $totalFiltered = $this->source->count();

        $this->sort();
        $this->paginate();

        $models = $this->source->get();

        $data = $this->setupRows($models);

        return new DataTable(
            $draw, $total, $totalFiltered, $data
        );
    }
}
