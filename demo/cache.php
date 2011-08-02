<?php
require_once('../lib/Gregwar/Image.php');

use Gregwar\Image;

// Note: create a "cache" directory before try this

echo Image::create('test.png')
    ->sepia()
    ->jpeg();
