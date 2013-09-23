<?php

namespace Gregwar\Image\Adapter;

use Gregwar\Image\Source\Source;

abstract class Adapter
{
    /**
     * Source
     */
    protected $source = null;

    public function setSource(Source $source)
    {
        $this->source = $source;
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
     * Image width
     */
    abstract public function width();

    /**
     * Image height
     */
    abstract public function height();

    /**
     * Save the image as a git, png or jpeg
     */
    abstract public function saveGif($file);
    abstract public function savePng($file);
    abstract public function saveJpeg($file, $quality);

    /**
     * Does this adapter supports the given type ?
     */
    protected function supports($type)
    {
        return false;
    }

    /**
     * Converts the image to true color
     */
    protected function convertToTrueColor()
    {
    }
}
