<?php

namespace Elegant\DataTables\Engines\Concerns;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;

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
     * Qualify the given column name by the table.
     *
     * @param mixed $query
     * @param string $name Column name
     * @return string
     */
    abstract protected function qualifyColumn($query, $name);

    /**
     * @inheritdoc
     */
    public function globalFilter($search, array $columns)
    {
        $this->source->where(function ($query) use ($search, $columns) {
            foreach ($columns as $key => $column) {
                $this->filter($query, $column, $search, 'or');
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function columnFilter(array $columns)
    {
        foreach ($columns as $key => $column) {
            $this->filter($this->source, $column, $column['search'], 'and');
        }
    }

    /**
     * Applies filter to the column.
     *
     * @param mixed $query
     * @param array $column
     * @param array $search
     * @param string $boolean
     */
    protected function filter($query, $column, $search, $boolean = 'or')
    {
        if (isset($column['filter'])) {
            $this->callCustomFilter($query, $column['filter'], $search['value'], $search['regex'], $boolean);
        } else {
            $this->search($query, $column['name'], $search['value'], $search['regex'], $boolean);
        }
    }

    /**
     * Searches using the column at the source.
     *
     * @param mixed $query
     * @param string $name Column name
     * @param string $value
     * @param bool $regex
     * @param string $boolean
     */
    protected function search($query, $name, $value, $regex = false, $boolean = 'or')
    {
        $name = $this->qualifyColumn($query, $this->resolveJsonColumn($name));

        if ($regex) {
            $query->where($name, 'REGEXP', $value, $boolean);
        } else {
            $query->where($name, 'LIKE', "%{$value}%", $boolean);
        }
    }

    /**
     * @inheritdoc
     */
    public function sort(array $columns)
    {
        // apply priority
        usort($columns, fn ($a, $b) => $a['order']['pri'] <=> $b['order']['pri']);

        foreach ($columns as $column) {
            if (isset($column['sort'])) {
                $this->callCustomSort($this->source, $column['sort'], $column['order']['dir']);
            } else {
                $this->order($this->source, $column['name'], $column['order']['dir']);
            }
        }
    }

    /**
     * Orders the column at the source.
     *
     * @param mixed $query
     * @param string $name Column name
     * @param string $dir
     */
    protected function order($query, $name, $dir)
    {
        $query->orderBy($this->qualifyColumn($query, $this->resolveJsonColumn($name)), $dir);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function resolveJsonColumn($name)
    {
        if (Str::contains($name, '.')) {
            return str_replace('.', '->', $name);
        } else {
            return $name;
        }
    }

    /**
     * @inheritdoc
     */
    public function paginate($start, $length = null)
    {
        $this->source->offset($start);

        if (isset($length))
            $this->source->limit($length);
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

    /**
     * Calls the custom filter function.
     *
     * @param mixed $query
     * @param callable $filter
     * @param string $value
     * @param string $regex
     * @param string $boolean
     */
    protected function callCustomFilter($query, callable $filter, $value, $regex, $boolean)
    {
        $query->where(function ($query) use ($filter, $value, $regex) {
            call_user_func($filter, $query, $value, $regex);
        }, null, null, $boolean);
    }

    /**
     * Calls the custom sort function.
     *
     * @param mixed $query
     * @param callable $sort
     * @param string $dir
     */
    protected function callCustomSort($query, callable $sort, $dir)
    {
        call_user_func($sort, $query, $dir);
    }
}
