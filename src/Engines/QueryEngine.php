<?php

namespace Elegant\DataTables\Engines;

use Elegant\DataTables\Contracts\Engine;
use Elegant\DataTables\Engines\Concerns\InteractsWithQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

class QueryEngine implements Engine
{
    use InteractsWithQueryBuilder;

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
    public function select(array $columns)
    {
        // PASS
    }

    /**
     * @inheritdoc
     */
    protected function qualifyColumn($query, $column)
    {
        if (Str::contains($column, '.')) {
            return $column;
        } else {
            return sprintf('%s.%s', $query->from, $column);
        }
    }
}
