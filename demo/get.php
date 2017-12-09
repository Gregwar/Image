<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

$image = Image::open('img/test.png')
    ->resize(100, 100)
    ->negate()
    ->get('jpeg');

echo $image;
