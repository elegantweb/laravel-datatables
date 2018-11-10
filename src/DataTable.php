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
    protected $draw;

    /**
     * Total number of records.
     *
     * @var int
     */
    protected $total;

    /**
     * Total number of records after filter.
     *
     * @var int
     */
    protected $totalFiltered;

    /**
     * Records data.
     *
     * @var array
     */
    protected $data;

    /**
     * @param int $draw Draw number
     * @param int $total Total records
     * @param int $totalFiltered Total records after filter
     * @param array $data Records data
     */
    public function __construct($draw, $total, $totalFiltered, array $data)
    {
        $this->draw = $draw;
        $this->total = $total;
        $this->totalFiltered = $totalFiltered;
        $this->data = $data;
    }

    /**
     * Returns total number of records.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Returns total number of records after filter.
     *
     * @return int
     */
    public function getTotalFiltered()
    {
        return $this->totalFiltered;
    }

    /**
     * Returns records data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Array representation of the datatable.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'draw' => $this->draw,
            'recordsTotal' => $this->total,
            'recordsFiltered' => $this->totalFiltered,
            'data' => $this->data,
        ];
    }

    /**
     * Specifies data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * JSON representation of the datatable.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
