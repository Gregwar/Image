<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

Image::open('test.png')
    ->resize('6%')
    ->negate()
    ->save('out.jpg');
