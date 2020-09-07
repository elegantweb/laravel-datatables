<?php

namespace Elegant\DataTables\Contracts;

interface Engine
{
    /**
     * Resets the engine.
     */
    public function reset();

    /**
     * Select data.
     */
    public function select(array $columns);

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
     * Applies sort to the provided columns.
     *
     * @param array $columns
     */
    public function sort(array $columns);

    /**
     * Applies pagination.
     *
     * @param int $start
     * @param int|null $length
     */
    public function paginate($start, $length = null);

    /**
     * Counts data.
     *
     * @return int
     */
    public function count();

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
