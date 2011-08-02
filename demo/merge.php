<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

Image::open('test.png')
    ->merge(Image::open('test2.jpg')->cropResize(100, 100))
    ->save('out.jpg');
