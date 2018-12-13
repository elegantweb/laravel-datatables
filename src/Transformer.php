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
            $row[$key] = $value->format('Y-m-d H:i:s');
        } else if (is_object($value)) {
            $row[$key] = (string) $value;
        } else {
            $row[$key] = $value;
        }
    }

    /**
     * @param array $row
     */
    protected function transformRow(array &$row)
    {
        foreach ($row as $key => $value) {
            $row[$key] = $this->transformValue($value);
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
