<?php

namespace Gregwar\Image\Source;

class Source
{
    public function guessType()
    {
        return 'jpeg';
    }

    public function correct()
    {
        return true;
    }

    public function getInfos()
    {
        return null;
    }
}
