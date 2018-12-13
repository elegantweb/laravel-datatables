<?php

namespace Elegant\DataTables\Contracts;

interface Processor
{
    /**
     * Sets addon columns, addon columns will be added to the result.
     *
     * @param array $columns
     * @return $this
     */
    public function add(array $columns);

    /**
     * Sets raw columns, raw columns won't be escaped.
     *
     * @param array $names
     * @return $this
     */
    public function raw(array $names);

    /**
     * Columns to be included to the result.
     *
     * @param array $names
     * @return $this
     */
    public function include(array $names);

    /**
     * Columns to be excluded from the result.
     *
     * @param array $names
     * @return $this
     */
    public function exclude(array $names);

    /**
     * Processes the records.
     *
     * @param mixed $records
     * @return array
     */
    public function process($records);
}
