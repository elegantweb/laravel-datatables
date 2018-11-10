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
        // It contains dot so it can be a relation
        if (str_contains($column, '.') and $this->isRelation(explode('.', $column)[0])) {
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

        $query->with($relation); // eager load relation

        $query->{$method}($relation, function ($query) use ($column, $value, $regex) {
            $this->search($query, $column, $value, $regex, 'and');
        });
    }

    /**
     * Indicates if the name is for a relationship.
     *
     * @param  mixed  $query
     * @param  string $name
     * @return bool
     */
    protected function isRelation($query, $name)
    {
        return $query->{$name}() instanceof Relation;
    }
}
