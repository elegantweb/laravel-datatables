<?php

namespace Elegant\DataTables\Processors;

class DataProcessor
{
    protected $addonColumns = [];
    protected $rawColumns = [];
    protected $exclude = [];
    protected $include = [];

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

    protected function shouldEscapeColumn($key)
    {
        return !in_array($key, $this->rawColumns);
    }
}
