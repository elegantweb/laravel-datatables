<?php

namespace Elegant\DataTables;

use Closure;
use LogicException;
use Elegant\DataTables\Contracts\Driver as DriverContract;
use Elegant\DataTables\Contracts\Processor as ProcessorContract;

class Factory
{
    /**
     * Datatable request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Driver creators.
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Default processor.
     *
     * @var ProcessorContract|null
     */
    protected $processor;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns the request object.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Creates new datatable builder.
     *
     * @param  object $source
     * @param  ProcessorContract $processor
     * @return Builder
     */
    public function make($source, ProcessorContract $processor = null)
    {
        return new Builder($this->request, $this->createDriver($source), $this->resolveProcessor($processor));
    }

    /**
     * Resolves processor.
     *
     * @param  ProcessorContract|null $processor
     * @return ProcessorContract
     */
    protected function resolveProcessor(ProcessorContract $processor)
    {
        if (is_null($processor)) {
            return $this->getDefaultProcessor();
        } else {
            return $processor;
        }
    }

    /**
     * Sets the default processor.
     *
     * @param ProcessorContract $processor
     */
    public function setDefaultProcessor(ProcessorContract $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Returns the default processor.
     *
     * @return ProcessorContract
     */
    public function getDefaultProcessor()
    {
        if (is_null($this->processor)) {
            return new Processor();
        } else {
            return $this->processor;
        }
    }

    /**
     * Finds a driver for the source.
     *
     * @param  object $source
     * @return Closure
     */
    protected function findDriver($source)
    {
        foreach ($this->drivers as $class => $cb) {
            if ($source instanceof $class) {
                return $cb;
            }
        }
    }

    /**
     * Creates a new driver instance.
     *
     * @param  object $source
     * @return DriverContract
     */
    protected function createDriver($source)
    {
        if (null === $cb = $this->findDriver($source)) {
            return $cb();
        } else {
            throw new LogicException("No driver found for class [$class].");
        }
    }

    /**
     * Registers a custom driver creator.
     *
     * @param string $class
     * @param Closure $callback
     */
    public function extend($class, Closure $callback)
    {
        $this->drivers[$class] = $callback;
    }
}
