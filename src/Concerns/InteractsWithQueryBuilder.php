<?php

namespace Elegant\DataTables\Concerns;

trait InteractsWithQueryBuilder
{
    /**
     * Source we will get results from.
     *
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected $source;

    /**
     * Counts records.
     *
     * @return int
     */
    public function count()
    {
        return $this->source->count();
    }

    /**
     * Applies global filter to the columns at the source.
     *
     * @param array $search
     * @param array $columns
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
     * Applies filter to the columns at the source.
     *
     * @param array $columns
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
     * Applies sort at the source.
     *
     * @param array $order
     * @param array $columns
     */
    public function sort($order, array $columns)
    {
        foreach ($this->order as $order) {
            $this->order($this->source, $columns[$order['column']]['name'], $order['dir']);
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
     * Applies paginate at the source.
     *
     * @param int $start
     * @param int $length
     */
    public function paginate($start, $length)
    {
        $this->source->offset($start)->limit($length);
    }

    /**
     * Returns records.
     *
     * @return array
     */
    public function get()
    {
        return $this->source->get();
    }
}
