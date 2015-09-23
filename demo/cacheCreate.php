<?php

require_once '../autoload.php';

use Gregwar\Image\Image;

/*
 * Use the cache with a from-scratch image
 */

echo
Image::create(300, 350)
    ->fill('rgb(255, 150, 150)')
    ->circle(150, 150, 200, 'red', true)
    ->write('./fonts/CaviarDreams.ttf', 'Hello world!', 150, 150, 20, 0, 'white', 'center')
    ->jpeg();

echo "\n";
