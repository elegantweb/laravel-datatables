<?php

namespace Elegant\DataTables;

use Elegant\DataTables\Contracts\Processor as ProcessorContract;
use Elegant\DataTables\Support\Helper;
use Illuminate\Support\Arr;

class Processor implements ProcessorContract
{
    /**
     * Columns requested by the client.
     *
     * @var array
     */
    protected $requested = [];

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
     * @inheritdoc
     */
    public function request(array $columns)
    {
        $this->requested = $columns;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function add(array $columns)
    {
        $this->addon = $columns;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function raw(array $names)
    {
        $this->raw = $names;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function include(array $names)
    {
        $this->include = $names;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function exclude(array $names)
    {
        $this->exclude = $names;

        return $this;
    }

    /**
     * Indicates if the column should be at the result.
     *
     * @param string $name
     * @return bool
     */
    protected function isColumnRequired($name)
    {
        if (empty($this->include)) {
            return !in_array($name, $this->exclude);
        } else {
            return in_array($name, $this->include);
        }
    }

    /**
     * Indicates if the column is a raw column, raw columns should be inacted in either way.
     *
     * @param string $name
     * @return bool
     */
    protected function isColumnRaw($name)
    {
        return !in_array($name, $this->raw);
    }

    /**
     * Indicates if the column should be escaped.
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    protected function shouldEscapeColumn($name, $value)
    {
        return is_string($value) && $this->isColumnRaw($name);
    }

    /**
     * Sets up the source columns at the row.
     *
     * @param array $row
     * @param mixed $record
     */
    protected function setupSourceColumns(&$row, $record)
    {
        $columns = Arr::dot(Helper::convertToArray($record));

        foreach ($columns as $name => $value) {
            if ($this->isColumnRequired($name)) {
                Arr::set($row, $name, $this->shouldEscapeColumn($name, $value) ? e($value) : $value);
            }
        }
    }

    /**
     * Sets up the addon columns at the row.
     *
     * @param array $row
     * @param mixed $record
     */
    protected function setupAddonColumns(&$row, $record)
    {
        foreach ($this->addon as $name => $data) {
            if ($this->isColumnRequired($name)) {
                Arr::set($row, $name, Helper::resolveData($data, compact('record'), $this->isColumnRaw($name)));
            }
        }
    }

    /**
     * Sets up the requested columns at the row which are not set yet.
     *
     * @param array $row
     * @param mixed $record
     */
    protected function setupUndefinedRequestedColumns(&$row, $record)
    {
        foreach ($this->requested as $name) {
            if (!Arr::has($row, $name) and $this->isColumnRequired($name)) {
                Arr::set($row, $name, '');
            }
        }
    }

    /**
     * Sets up row.
     *
     * @param array $row
     * @param mixed $record
     */
    protected function setupRow(&$row, $record)
    {
        $this->setupSourceColumns($row, $record);
        $this->setupAddonColumns($row, $record);
        $this->setupUndefinedRequestedColumns($row, $record);
    }

    /**
     * Sets up rows.
     *
     * @param array $rows
     * @param mixed $records
     */
    protected function setupRows(&$rows, $records)
    {
        foreach ($records as $record) {
            $this->setupRow($rows[], $record);
        }
    }

    /**
     * @inheritdoc
     */
    public function process($records)
    {
        $this->setupRows($rows, $records);

        return $rows;
    }
}
