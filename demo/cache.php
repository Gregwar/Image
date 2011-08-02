<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

// Note: create a "cache" directory before try this

echo Image::open('test.png')
    ->sepia()
    ->jpeg();
