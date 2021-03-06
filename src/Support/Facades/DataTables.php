<?php

namespace Elegant\DataTables\Support\Facades;

use Illuminate\Support\Facades\Facade;

class DataTables extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'datatables';
    }
}
