<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

Image::open('img/test.png')
    ->resize('26%')
    ->negate()
    ->save('out.jpg');
