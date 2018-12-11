<?php

namespace Elegant\DataTables\Drivers;

use Elegant\DataTables\Contracts\Driver;
use Elegant\DataTables\Concerns\Drivers\InteractsWithQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class QueryDriver implements Driver
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
