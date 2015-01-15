<?php

namespace Gregwar\Image\Source;

use Gregwar\Image\Image;

/**
 * Open an image from a file
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
        $validTypes = array(
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_JPEG => 'jpeg'
        );

        if (function_exists('exif_imagetype')) {
            $type = @exif_imagetype($this->file);
        }
        elseif (function_exists('getimagesize')) {
            $info = @getimagesize($this->file);
            $type = is_array($info) && isset($info[2]) ? $info[2] : false;
        }
        else {
            $type = false;
        }

        if (false !== $type && isset($validTypes[$type])) {
            return $validTypes[$type];
        }

        $parts = explode('.', $this->file);
        $ext = strtolower($parts[count($parts)-1]);

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
