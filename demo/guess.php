<?php
require_once('../autoload.php');

use Gregwar\Image\Image;

Image::open('img/test.png')
    ->resize(100, 100)
    ->negate()
    ->guess(55);
