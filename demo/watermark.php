<?php

require_once '../autoload.php';

use Gregwar\Image\Image;

$img = Image::open('img/mona.jpg');
$watermark = Image::open('img/vinci.png');
$img->merge($watermark, $img->width()-$watermark->width(),
    $img->height()-$watermark->height())
    ->save('out.jpg', 'jpg');
