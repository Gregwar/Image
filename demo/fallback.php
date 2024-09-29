<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

echo Image::open('img/something-wrong.png')
    ->resize(100, 0)
    ->negate()
    ->jpeg();
