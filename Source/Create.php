<?php

namespace Gregwar\Image\Source;

class Create extends Source
{
    protected $width;
    protected $height;

    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getInfos()
    {
        return array($this->width, $this->height);
    }
}
