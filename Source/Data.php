<?php

namespace Gregwar\Image\Source;

/**
 * Having image in some string.
 */
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

    public function guessType()
    {
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($this->data);
            switch ($mime) {
                case 'image/gif':
                    return 'gif';
                case 'image/png':
                    return 'png';
                default:
                    return 'jpeg';
            }
        }

        return 'jpeg';
    }
}
