<?php

namespace Gregwar\Image\Adapter;

abstract class Adapter
{
    /**
     * File
     */
    protected $file = null;

    /**
     * Resource
     */
    protected $resource = null;

    /**
     * Data
     */
    protected $data = null;

    /**
     * Type
     */
    protected $type = null;

    /**
     * Width & height
     */
    protected $width = null;
    protected $height = null;

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function setDimensions($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Init the resource
     */
    abstract public function init();

    /**
     * Gets the name of the adapter
     */
    abstract public function getName();

    /**
     * Gets the width and the height for writing some text
     */
    public static function TTFBox($font, $text, $size, $angle = 0)
    {
        $box = imagettfbbox($size, $angle, $font, $text);

        return array(
            'width' => abs($box[2] - $box[0]),
            'height' => abs($box[3] - $box[5])
        );
    }

    /**
     * Image width
     */
    abstract public function width();

    /**
     * Image height
     */
    abstract public function height();

    abstract public function saveGif($file);
    abstract public function savePng($file);
    abstract public function saveJpeg($file, $quality);
}
