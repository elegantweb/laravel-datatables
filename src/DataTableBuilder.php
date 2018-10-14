<?php

namespace Elegant\DataTables;

use Illuminate\Http\Request;

class DataTableBuilder
{
    protected $source;
    protected $draw = 1;
    protected $start = 0;
    protected $length = 10;
    protected $columns = [];
    protected $addonColumns = [];
    protected $rawColumns = [];
    protected $search;
    protected $orders = [];

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function addColumn($name, $value)
    {
        $this->addonColumns[] = ['name' => $name, 'value' => $value];

        return $this;
    }

    public function rawColumns(array $keys)
    {
        $this->rawColumns = $keys;

        return $this;
    }

    protected function isColumnNeeded($key)
    {
        foreach ($this->columns as $column) {
            if ($column['data'] === $key) {
                return true;
            }
        }
    }

    protected function shouldEscapeColumn($key)
    {
        return !in_array($key, $this->rawColumns);
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
            foreach ($this->columns as $column) {
                if ($column['searchable']) {
                    $this->search($this->search, $column);
                }
            }
        }

        // column specific filter
        foreach ($this->columns as $column) {
            if ('' != $column['search']['value']) {
                $this->search($column['search'], $column);
            }
        }
    }

    protected function sort()
    {
        foreach ($this->orders as $order) {
            $this->source->orderBy($this->columns[$order['column']]['data'], $order['dir']);
        }
    }

    protected function paginate()
    {
        // Don't allow more than 100 records cause of security reasons
        $diff = $this->length - $this->start;
        if ($diff > 100) {
            $this->source->offset($this->start)->limit($this->length - $diff);
        } else {
            $this->source->offset($this->start)->limit($this->length);
        }
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
        // No need to escape blade views at all, so just return the content
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
        foreach ($this->addonColumns as list($name, $value)) {
            if ($this->isColumnNeeded($name)) {
                $row[$name] = $this->resolveValue($value, compact('model'), $this->shouldEscapeColumn($name));
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

    public function build(Request $request = null)
    {
        $request = $request ?? request();

        $this->draw = $request->input('draw', 1);

        $this->start = $request->input('start', 0);
        $this->length = $request->input('length', 10);

        $this->columns = $request->input('columns', []);
        $this->search = $request->input('search');
        $this->orders = $request->input('order', []);

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
