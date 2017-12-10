<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

Image::open('in.gif')
    ->resize(500, 500)
    ->save('out.png', 'png');
