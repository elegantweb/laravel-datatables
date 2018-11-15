<?php

namespace Elegant\DataTables\Processors;

use Elegant\DataTables\Support\Helper;

class DefaultProcessor
{
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
     * Sets addon columns, addon columns will be added to the result.
     *
     * @param  array $columns
     * @return $this
     */
    public function add(array $columns)
    {
        $this->addon = $columns;

        return $this;
    }

    /**
     * Sets raw columns, raw columns won't be escaped.
     *
     * @param  array $keys
     * @return $this
     */
    public function raw(array $keys)
    {
        $this->raw = $keys;

        return $this;
    }

    /**
     * Columns to be included to the result.
     *
     * @param  array $keys
     * @return $this
     */
    public function include(array $keys)
    {
        $this->include = $keys;

        return $this;
    }

    /**
     * Columns to be excluded from the result.
     *
     * @param  array $keys
     * @return $this
     */
    public function exclude(array $keys)
    {
        $this->exclude = $keys;

        return $this;
    }

    /**
     * Indicates if the column should be at the result.
     *
     * @param  string $key
     * @return bool
     */
    protected function isColumnNeeded($key)
    {
        if (empty($this->include)) {
            return !in_array($key, $this->exclude);
        } else {
            return in_array($key, $this->include);
        }
    }

    /**
     * Indicates if the column should be escaped.
     *
     * @param  string $key
     * @return bool
     */
    protected function shouldEscapeColumn($key)
    {
        return !in_array($key, $this->rawColumns);
    }

    /**
     * Sets up the source columns at the row.
     *
     * @param array $row
     * @param array $record
     */
    protected function setupSourceColumns(&$row, array $record)
    {
        $columns = array_dot($record);

        foreach ($columns as $key => $value) {
            if ($this->isColumnNeeded($key)) {
                array_set($row, $key, $this->shouldEscapeColumn($key) ? e($value) : $value);
            }
        }
    }

    /**
     * Sets up the addon columns at the row.
     *
     * @param array $row
     * @param array $record
     */
    protected function setupAddonColumns(&$row, $record)
    {
        foreach ($this->addonColumns as $key => $column) {
            if ($this->isColumnNeeded($key)) {
                array_set($row, $key, Helper::resolveData($column['data'], compact('record'), $this->shouldEscapeColumn($key)));
            }
        }
    }

    /**
     * Sets up row.
     *
     * @param array $row
     * @param array $record
     */
    protected function setupRow(&$row, $record)
    {
        $this->setupSourceColumns($row, $record);
        $this->setupAddonColumns($row, $record);
    }

    /**
     * Sets up rows.
     *
     * @param array $rows
     * @param array $records
     */
    protected function setupRows(&$rows, array $records)
    {
        foreach ($records as $record) {
            $this->setupRow($rows[], $record);
        }
    }

    /**
     * Processes the data.
     *
     * @param  mixed $data
     * @return array
     */
    public function process($data)
    {
        $rows = [];

        $this->setupRows($rows, Heleper::convertToArray($data));

        return $rows;
    }
}
