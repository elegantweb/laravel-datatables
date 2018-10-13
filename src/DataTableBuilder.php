<?php

namespace Elegant\DataTables;

use Illuminate\Http\Request;

class DataTableBuilder
{
    protected $source;
    protected $request;
    protected $addonColumns = [];

    public function addSource($source)
    {
        $this->source = $source;

        return $this;
    }

    public function addColumn($name, $value)
    {
        $this->addonColumns[] = ['name' => $name, 'value' => $value];

        return $this;
    }

    protected function search($search, $column)
    {
        if ($search['regex']) {
            $this->source->where($column['name'], 'REGEX', $search['value']);
        } else {
            $this->source->where($column['name'], 'LIKE', $search['value']);
        }
    }

    protected function filter($search, $columns)
    {
        // global filter
        if ('' != $search['value']) {
            foreach ($columns as $column) {
                if ($column['searchable']) {
                    $this->search($search, $column);
                }
            }
        }

        // column specific filter
        foreach ($columns as $column) {
            if ('' != $search['value']) {
                $this->search($column['search'], $column);
            }
        }
    }

    protected function sort($orders, $columns)
    {
        foreach ($orders as $order) {
            $this->source->orderBy($columns[$order['column']]['name'], $order['dir']);
        }
    }

    protected function paginate($start, $length)
    {
        $this->source->offset($start)->limit($length);
    }

    protected function compileValue($value, array $data)
    {
        if ($value instanceof Coluser) {
            $value = $column['value'](...$data);
        } elseif (view()->exists($column['value'])) {
            $value = view($str, $data);
        } else {
            $value = $column['value'];
        }
    }

    protected function setupSourceColumns(&$row, $model)
    {
        $row = $model->toArray() + $row;
    }

    protected function setupAddonColumns(&$row, $model)
    {
        foreach ($this->addonColumns as $column) {
            $row[$column['name']] = $this->compileValue($column['value'], ['model' => $model]);
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

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request ?? request();
    }

    public function build()
    {
        $request = $this->getRequest();

        $draw = $request->input('draw', 1);

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $columns = $request->input('columns', []);
        $search = $request->input('search', []);
        $order = $request->input('order', []);

        $total = $this->source->getCountForPagination();

        $this->filter($search, $columns);

        $totalFiltered = $this->source->getCountForPagination();

        $this->sort($order, $columns);
        $this->paginate($start, $length);

        $models = $this->source->get();

        $data = $this->setupRows($models);

        return new DataTable(
            $draw, $total, $totalFiltered, $data
        );
    }
}
