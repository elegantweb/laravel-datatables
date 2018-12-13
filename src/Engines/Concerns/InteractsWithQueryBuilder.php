<?php

namespace Elegant\DataTables\Engines\Concerns;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait InteractsWithQueryBuilder
{
    /**
     * Original source we store to reset state.
     *
     * @var QueryBuilder|EloquentBuilder
     */
    protected $original;

    /**
     * Source we will get results from.
     *
     * @var QueryBuilder|EloquentBuilder
     */
    protected $source;

    /**
     * @inheritdoc
     */
    public function reset()
    {
        $this->source = clone $this->original;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->source->count();
    }

    /**
     * @inheritdoc
     */
    public function globalFilter($search, array $columns)
    {
        $this->source->where(function ($query) use ($search, $columns) {
            foreach ($columns as $key => $column) {
                $this->search($query, $column['name'], $search['value'], $search['regex']);
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function columnFilter(array $columns)
    {
        foreach ($columns as $key => $column) {
            $this->search($this->source, $column['name'], $column['search']['value'], $column['search']['regex'], 'and');
        }
    }

    /**
     * Searches using the column at the source.
     *
     * @param mixed  $query
     * @param string $column Column name
     * @param string $value
     * @param bool   $regex
     * @param string $boolean
     */
    protected function search($query, $column, $value, $regex = false, $boolean = 'or')
    {
        // It contains dot so it is a JSON reference
        if (str_contains($column, '.')) {
            $column = str_replace('.', '->', $column);
        }

        if ($regex) {
            $query->where($column, 'REGEXP', $value, $boolean);
        } else {
            $query->where($column, 'LIKE', $value, $boolean);
        }
    }

    /**
     * @inheritdoc
     */
    public function sort($order, array $columns)
    {
        foreach ($order as $value) {
            $this->order($this->source, $columns[$value['column']]['name'], $value['dir']);
        }
    }

    /**
     * Orders the column at the source.
     *
     * @param mixed $query
     * @param array $column
     * @param array $order
     */
    protected function order($query, $column, $dir)
    {
        $query->orderBy($column, $dir);
    }

    /**
     * @inheritdoc
     */
    public function paginate($start, $length)
    {
        $this->source->offset($start)->limit($length);
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->source->get();
    }

    /**
     * @inheritdoc
     */
    public function call(callable $callable)
    {
        call_user_func($callable, $this->source);
    }
}
