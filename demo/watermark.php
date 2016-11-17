<?php

require_once '../autoload.php';

use Gregwar\Image\Image;

// Opening mona.jpg
$img = Image::open('img/mona.jpg');

// Opening vinci.png
$watermark = Image::open('img/vinci.png');

// Mergine vinci text into mona in the top-right corner
$img->merge($watermark, $img->width()-$watermark->width(),
    $img->height()-$watermark->height())
    ->save('out.jpg', 'jpg');
