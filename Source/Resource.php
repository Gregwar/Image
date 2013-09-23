<?php

namespace Gregwar\Image\Source;

class Resource extends Source
{
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }
}
