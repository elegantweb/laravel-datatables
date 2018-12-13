<?php

namespace Elegant\DataTables;

use Closure;
use InvalidArgumentException;
use Elegant\DataTables\Contracts\Engine as EngineContract;
use Elegant\DataTables\Contracts\Processor as ProcessorContract;
use Elegant\DataTables\Contracts\Transformer as TransformerContract;

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
     * Default transformer.
     *
     * @var TransformerContract|null
     */
    protected $transformer;

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
     * @param TransformerContract $transformer
     * @return Builder
     */
    public function make(
        $source,
        ProcessorContract $processor = null,
        TransformerContract $transformer = null
    ) {
        return new Builder(
            $this->getRequest(),
            $this->createEngine($source),
            $this->resolveProcessor($processor),
            $this->resolveTransformer($transformer)
        );
    }

    /**
     * Resolves processor.
     *
     * @param ProcessorContract|null $processor
     * @return ProcessorContract
     */
    protected function resolveProcessor(?ProcessorContract $processor)
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
     * Resolves transformer.
     *
     * @param TransformerContract|null $transformer
     * @return TransformerContract
     */
    protected function resolveTransformer(?TransformerContract $transformer)
    {
        if (is_null($transformer)) {
            return $this->getDefaultTransformer();
        } else {
            return $transformer;
        }
    }

    /**
     * Sets the default transformer.
     *
     * @param TransformerContract $transformer
     */
    public function setDefaultProcessor(TransformerContract $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Returns the default transformer.
     *
     * @return ProcessorContract
     */
    public function getDefaultTransformer()
    {
        if (is_null($this->transformer)) {
            return new Transformer();
        } else {
            return $this->transformer;
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
            throw new InvalidArgumentException('No engine supported for the source.');
        } else {
            return $cb($source);
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
