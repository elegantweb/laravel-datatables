<?php

namespace Elegant\DataTables\Contracts;

interface Driver
{
    /**
     * Resets the driver.
     */
    public function reset();

    /**
     * Counts data.
     *
     * @return int
     */
    public function count();

    /**
     * Applies filter to all columns.
     *
     * @param array $search
     * @param array $columns
     */
    public function globalFilter($search, array $columns);

    /**
     * Applies filter to the provided columns.
     *
     * @param array $columns
     */
    public function columnFilter(array $columns);

    /**
     * Applies sort.
     *
     * @param array $order
     * @param array $columns
     */
    public function sort(array $order, array $columns);

    /**
     * Applies pagination.
     *
     * @param int $start
     * @param int $length
     */
    public function paginate($start, $length);

    /**
     * Returns data.
     *
     * @return mixed
     */
    public function get();

    /**
     * Calls a callable using source as param.
     *
     * @param callable $callable
     */
    public function call(callable $callable);
}