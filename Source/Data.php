<?php

namespace Gregwar\Image\Source;

class Data extends Source
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getInfos()
    {
        return sha1($this->data);
    }
}
