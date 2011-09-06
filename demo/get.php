<?php
require_once('../lib/Gregwar/Image/Image.php');

use Gregwar\Image\Image;

$image = Image::open('img/test.png')
    ->resize(100, 100)
    ->negate()
    ->get('jpeg');

echo $image;
