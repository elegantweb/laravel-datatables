<?php

namespace Elegant\DataTables\Fetchers;

use Elegant\DataTables\Fetchers\Concerns\InteractsWithQueryBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ElequentFetcher
{
    use InteractsWithQueryBuilder {
        search as traitSearch;
    }

    /**
     * @param QueryBuilder $source
     */
    public function __construct(QueryBuilder $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    protected function search($query, $column, $value, $regex, $boolean = 'or')
    {
        if ($this->isRelation($query, $column)) {
            $this->searchRelation($query, ...explode('.', $column, 2), $value, $regex, $boolean);
        } else {
            $this->traitSearch($query, $column, $value, $regex, $boolean);
        }
    }

    /**
     * Searchs inside a relationship.
     *
     * @param mixed  $query
     * @param string $relation Relation name
     * @param string $column Column name
     * @param string $value
     * @param bool   $regex
     * @param string $boolean
     */
    protected function searchRelation($query, $relation, $column, $value, $regex, $boolean = 'or')
    {
        $method = 'or' === $boolean ? 'orWhereHas' : 'whereHas';

        $query->{$method}($relation, function ($query) use ($column, $value, $regex) {
            $this->search($query, $column, $value, $regex, 'and');
        });
    }

    /**
     * Indicates if the column is for a relationship.
     *
     * @param  mixed $query
     * @param  string $column
     * @return bool
     */
    protected function isRelation($query, $column)
    {
        if (str_contains($column, '.')) {
            return $query->{explode('.', $column)[0]}() instanceof Relation;
        } else {
            return false;
        }
    }

    /**
     * Eagerly loads a relationship.
     *
     * @param mixed  $column
     * @param string $column
     */
    protected function eagerLoadRelation($query, $column)
    {
        $query->with(implode('.', array_splice(explode('.', $column), 0, -1)));
    }

    /**
     * Select only required columns.
     *
     * @param mixed $query
     * @param array $columns
     */
    protected function select($query, array $columns)
    {
        foreach ($columns as $column) {
            if ($this->isRelation($query, $column)) {
                $this->eagerLoadRelation($query, $column);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $columns)
    {
        $this->select($this->source, array_column($columns, 'data'));

        return $this->traitFetch($columns);
    }
}
