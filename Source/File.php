<?php

namespace Gregwar\Image\Source;

use Gregwar\Image\Image;

/**
 * Open an image from a file.
 */
class File extends Source
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function correct()
    {
        return false !== @exif_imagetype($this->file);
    }

    public function guessType()
    {
        $type = false;

        if (function_exists('exif_imagetype')) {
            $type = @exif_imagetype($this->file);
        } elseif (function_exists('getimagesize')) {
            $type = @getimagesize($this->file);
            $type = $type ? $type[2] : false;
        }

        if ($type === false) {
            $parts = explode('.', $this->file);
            $ext = strtolower($parts[count($parts) - 1]);
        } else {
            $ext = image_type_to_extension($type, false);
        }

        if (isset(Image::$types[$ext])) {
            return Image::$types[$ext];
        }

        return 'jpeg';
    }

    public function getInfos()
    {
        return $this->file;
    }
}
