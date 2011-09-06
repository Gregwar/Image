<?php
require_once('../lib/Gregwar/Image/Image.php');

use Gregwar\Image\Image;

Image::open('img/test.png')
    ->resize('26%')
    ->negate()
    ->save('out.jpg');
