<?php

namespace Elegant\DataTables;

use Elegant\DataTables\Contracts\Processor as ProcessorContract;
use Elegant\DataTables\Support\Helper;

class Processor implements ProcessorContract
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
    public function raw(array $keys)
    {
        $this->raw = $keys;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function include(array $keys)
    {
        $this->include = $keys;

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function process($data)
    {
        $rows = [];

        $this->setupRows($rows, Heleper::convertToArray($data));

        return $rows;
    }
}
