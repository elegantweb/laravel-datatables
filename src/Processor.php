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
     * Indicates if the column should be escaped.
     *
     * @param string $name
     * @return bool
     */
    protected function shouldEscapeColumn($name)
    {
        return !in_array($name, $this->raw);
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

        foreach ($columns as $name => $value) {
            if ($this->isColumnRequired($name)) {
                array_set($row, $name, $this->shouldEscapeColumn($name) ? e($value) : $value);
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
        foreach ($this->addon as $name => $data) {
            if ($this->isColumnRequired($name)) {
                array_set($row, $name, Helper::resolveData($data, compact('record'), $this->shouldEscapeColumn($name)));
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
