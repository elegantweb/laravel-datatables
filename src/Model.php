<?php

namespace Elegant\DataTables;

use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Model implements JsonSerializable, Jsonable, Arrayable
{
    /**
     * Source we will get results from.
     *
     * @var object
     */
    protected $source;

    /**
     * Datatable factory instance.
     *
     * @var Factory
     */
    protected static $factory;

    /**
     * @param object $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * Returns the factory instance.
     *
     * @param Factory $factory
     */
    public static function setFactory(Factory $factory)
    {
        static::$factory = $factory;
    }

    /**
     * Sets the factory instance.
     *
     * @param Factory $factory
     */
    public static function setFactory(Factory $factory)
    {
        static::$factory = $factory;
    }
}
