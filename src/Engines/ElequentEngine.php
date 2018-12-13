<?php

namespace Elegant\DataTables\Engines;

use Elegant\DataTables\Contracts\Engine;
use Elegant\DataTables\Engines\Concerns\InteractsWithQueryBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class EloquentEngine implements Engine
{
    use InteractsWithQueryBuilder {
        search as traitSearch;
    }

    /**
     * @param QueryBuilder $source
     */
    public function __construct(QueryBuilder $source)
    {
        $this->original = $source;
        $this->source = clone $source;
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
     * @param mixed $query
     * @param string $column
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
}
