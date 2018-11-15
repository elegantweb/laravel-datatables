<?php

namespace Elegant\DataTables\Fetchers;

use Elegant\DataTables\Fetchers\Concerns\InteractsWithQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class QueryFetcher
{
    use InteractsWithQueryBuilder {
        fetch as traitFetch;
    }

    /**
     * @param QueryBuilder $source
     */
    public function __construct(QueryBuilder $source)
    {
        $this->source = $source;
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
            if (str_contains($column, '.')) {
                $query->addSelect(explode('.', $column)[0]);
            } else {
                $query->addSelect($column);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $columns)
    {
        $this->select($this->source, array_column($columns, 'name'));

        return $this->traitFetch($columns);
    }
}
