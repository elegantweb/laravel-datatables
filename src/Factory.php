<?php

namespace Elegant\DataTables;

use Closure;
use InvalidArgumentException;
use Elegant\DataTables\Contracts\Engine as EngineContract;
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
     * Engine creators.
     *
     * @var array
     */
    protected $engines = [];

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
     * @param object $source
     * @param ProcessorContract $processor
     * @return Builder
     */
    public function make($source, ProcessorContract $processor = null)
    {
        return new Builder($this->request, $this->createEngine($source), $this->resolveProcessor($processor));
    }

    /**
     * Resolves processor.
     *
     * @param ProcessorContract|null $processor
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
     * Finds a engine for the source.
     *
     * @param object $source
     * @return Closure
     */
    protected function findEngine($source)
    {
        foreach ($this->engines as $class => $cb) {
            if ($source instanceof $class) {
                return $cb;
            }
        }
    }

    /**
     * Creates a new engine instance.
     *
     * @param object $source
     * @return EngineContract
     */
    protected function createEngine($source)
    {
        if (null === $cb = $this->findEngine($source)) {
            return $cb($source);
        } else {
            throw new InvalidArgumentException('No engine supported for the source.');
        }
    }

    /**
     * Registers a custom engine creator.
     *
     * @param string $class
     * @param Closure $callback
     */
    public function extend($class, Closure $callback)
    {
        $this->engines[$class] = $callback;
    }
}
