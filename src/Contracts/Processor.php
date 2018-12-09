<?php

namespace Elegant\DataTables\Contracts;

interface Processor
{
    /**
     * Sets addon columns, addon columns will be added to the result.
     *
     * @param  array $columns
     * @return $this
     */
    public function add(array $columns);

    /**
     * Sets raw columns, raw columns won't be escaped.
     *
     * @param  array $keys
     * @return $this
     */
    public function raw(array $keys);

    /**
     * Columns to be included to the result.
     *
     * @param  array $keys
     * @return $this
     */
    public function include(array $keys);

    /**
     * Columns to be excluded from the result.
     *
     * @param  array $keys
     * @return $this
     */
    public function exclude(array $keys);

    /**
     * Processes the data.
     *
     * @param  mixed $data
     * @return array
     */
    public function process($data);
}
