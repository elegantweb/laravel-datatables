<?php

namespace Elegant\DataTables\Fetchers;

use Elegant\DataTables\Fetchers\Concerns\InteractsWithQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class QueryFetcher
{
    use InteractsWithQueryBuilder;

    /**
     * @param QueryBuilder $source
     */
    public function __construct(QueryBuilder $source)
    {
        $this->source = $source;
    }
}
