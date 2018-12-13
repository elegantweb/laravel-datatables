<?php

namespace Elegant\DataTables;

use DateTime;
use Elegant\DataTables\Contracts\Transformer as TransformerContract;

class Transformer implements TransformerContract
{
    /**
     * @param mixed $value
     */
    protected function transformValue(&$value)
    {
        if ($value instanceof DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        } else if (is_object($value)) {
            $value = (string) $value;
        } else {
            $value = $value;
        }
    }

    /**
     * @param array $row
     */
    protected function transformRow(&$row)
    {
        foreach ($row as &$value) {
            $this->transformValue($value);
        }
    }

    /**
     * @inheritdoc
     */
    public function transform(array &$data)
    {
        foreach ($data as &$row) {
            $this->transformRow($row);
        }
    }
}
