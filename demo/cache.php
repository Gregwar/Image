<?php

require_once '../autoload.php';

use Gregwar\Image\Image;

// Note: create a "cache" directory before try this
echo Image::open('img/test.png')
    ->sepia();
echo "\n";
