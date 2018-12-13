<?php

namespace Elegant\DataTables\Engines;

use Elegant\DataTables\Contracts\Engine;
use Elegant\DataTables\Concerns\Engines\InteractsWithQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
}
