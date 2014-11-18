<?php
require_once('../autoload.php');

use Gregwar\Image\Image;

// resize() will never enlarge the image
Image::open('mona.jpg')
    ->resize(250, 250, 'red')
    ->save('resize.jpg');

// scaleResize() will also preserve the scale, but won't
// enlage the image
Image::open('mona.jpg')
    ->scaleResize(250, 250, 'red')
    ->save('scaleResize.jpg');

// forceResize() will resize matching the *exact* given size
Image::open('mona.jpg')
    ->forceResize(250, 250)
    ->save('forceResize.jpg');

// cropResize() preserves scale just like resize() but will 
// trim the whitespace (if any) in the resulting image
Image::open('mona.jpg')
    ->cropResize(250, 250)
    ->save('cropResize.jpg');

// zoomCrop() resizes the image so that a part of it appear in
// the given area
Image::open('mona.jpg')
    ->zoomCrop(200, 200)
    ->save('zoomCrop.jpg');

// You can specify the position using the xPos and yPos arguments
Image::open('mona.jpg')
    ->zoomCrop(200, 200, 'transparent', 'center', 'top')
    ->save('zoomCropTop.jpg');
