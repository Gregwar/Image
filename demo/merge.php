<?php

require_once '../autoload.php';

use Gregwar\Image\Image;

Image::open('img/test.png')
    ->merge(Image::open('img/test2.jpg')->cropResize(100, 100))
    ->save('out.jpg', 'jpg');
