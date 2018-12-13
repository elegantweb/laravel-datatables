<?php

namespace Elegant\DataTables\Contracts;

interface Transformer
{
    /**
     * Transforms the data.
     *
     * @param array $data
     */
    public function transform(array &$data);
}
