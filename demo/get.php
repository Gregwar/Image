<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

$image = Image::open('img/test.png')
    ->resize(100, 100)
    ->negate()
    ->get('jpeg');

echo $image;
