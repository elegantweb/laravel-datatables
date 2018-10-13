<?php

namespace Elegant\DataTables;

use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class DataTable implements JsonSerializable, Jsonable, Arrayable
{
    protected $draw;
    protected $total;
    protected $totalFiltered;
    protected $data;

    public function __construct($draw, $total, $totalFiltered, $data)
    {
        $this->draw = $draw;
        $this->total = $total;
        $this->totalFiltered = $totalFiltered;
        $this->data = $data;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getTotalFiltered()
    {
        return $this->totalFiltered;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toArray()
    {
        return [
            'draw' => $this->draw,
            'recordsTotal' => $this->total,
            'recordsFiltered' => $this->totalFiltered,
            'data' => $this->data,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
