<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

// Note: create a "cache" directory before try this
echo Image::open('img/test.png')
    ->sepia();
echo "\n";
