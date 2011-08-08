<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

Image::create(300, 300)
    ->fill('0xfaa')
    ->circle(150, 150, 200, 'red', true)
    ->write('./fonts/CaviarDreams.ttf', "Hello world!", 150, 150, 20, 0, 'white', 'center')
    ->save('out.jpg', 'jpeg', 95);
