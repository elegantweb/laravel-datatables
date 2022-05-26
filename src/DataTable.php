<?php

namespace Elegant\DataTables;

use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class DataTable implements JsonSerializable, Jsonable, Arrayable
{
    /**
     * Draw number.
     *
     * @var int
     */
    public $draw;

    /**
     * Total number of records.
     *
     * @var int
     */
    public $total;

    /**
     * Total number of records after filter.
     *
     * @var int
     */
    public $totalFiltered;

    /**
     * Records data.
     *
     * @var array
     */
    public $data;

    /**
     * Error.
     *
     * @var string|null
     */
    public $error = null;

    /**
     * @param int $draw Draw number
     * @param int $total Total records
     * @param int $totalFiltered Total records after filter
     * @param array $data Records data
     * @param string|null $error
     */
    public function __construct($draw, $total, $totalFiltered, array $data, $error = null)
    {
        $this->draw = $draw;
        $this->total = $total;
        $this->totalFiltered = $totalFiltered;
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * Array representation of the datatable.
     *
     * @return array
     */
    public function toArray()
    {
        $arr = [];
        $arr['draw'] = $this->draw;
        $arr['recordsTotal'] = $this->total;
        $arr['recordsFiltered'] = $this->totalFiltered;
        $arr['data'] = $this->data;
        if ($this->error) $arr['error'] = $this->error;
        return $arr;
    }

    /**
     * Specifies data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * JSON representation of the datatable.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
