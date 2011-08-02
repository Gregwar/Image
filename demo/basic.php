<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

Image::create('test.png')
    ->resize(100, 100)
    ->negate()
    ->save('out.jpg');
