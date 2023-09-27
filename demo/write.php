<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

Image::create(300, 300)
    ->fill('rgb(255, 150, 150)')
    ->circle(150, 150, 200, 'red', true)
    ->write('./fonts/CaviarDreams.ttf', 'Hello world!', 150, 150, 20, 0, 'white', 'center')
    ->save('out.jpg', 'jpeg', 95);
