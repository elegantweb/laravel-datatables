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
        if ($this->isRelated($query, $column)) {
            $this->searchRelated($query, $column, $value, $regex, $boolean);
        } else {
            $this->traitSearch($query, $column, $value, $regex, $boolean);
        }
    }

    /**
     * Searchs for the related column.
     *
     * @param mixed  $query
     * @param string $column Column name
     * @param string $value
     * @param bool   $regex
     * @param string $boolean
     */
    protected function searchRelated($query, $column, $value, $regex, $boolean = 'or')
    {
        list($relation, $column) = explode('.', $column, 2);

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
    protected function isRelated($query, $column)
    {
        list($relation,) = explode('.', $column);

        if (method_exists($query, $relation)) {
            return $query->{$relation}() instanceof Relation;
        } else {
            return false;
        }
    }

    /**
     * Eagerly loads a relationship.
     *
     * @param mixed  $query
     * @param string $relation
     */
    protected function eagerLoadRelation($query, $relation)
    {
        $query->with($relation);
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
            if ($this->isRelated($query, $column)) {
                $this->eagerLoadRelation($query, implode('.', array_splice(explode('.', $column), 0, -1)));
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
