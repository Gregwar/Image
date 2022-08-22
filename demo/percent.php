<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

Image::open('img/test.png')
    ->resize('26%')
    ->negate()
    ->save('out.jpg', 'jpg');
