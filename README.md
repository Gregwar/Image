Gregwar's Image class
=====================

The `Gregwar\Image` class purpose is to provide a simple object-oriented images handling and caching API.

Usage
=====

Basic handling
--------------

Using methods chaining, you can open, transform and save a file in a single line:

    require_once('lib/Gregwar/Image.php');

    use Gregwar\Image;

    Image::open('in.png')
        ->resize(100, 100)
        ->negate()
        ->save('out.jpg');

The methods available are:

* `resize($width, $height, $background)`: resizes the image, will preserve scale and never 
   enlarge it

* `scaleResize($width, $height, $background)`: resizes the image, will preserve scale

* `forceResize($width, $height, $background)`: resizes the image, will orce the image to 
   be exactly $width by $height

* `cropResize($width, $height, $background)`: resizes the image preserving scale and croping
  the whitespaces

* `crop($x, $y, $w, $h)`: crops the image to a box located on coordinates $x,y and
   which size is $w by $h

* `negate()`: negates the image colors

* `brighness($b)`: applies a brightness effect to the image (from -255 to +255)

* `contrast($c)`: applies a contrast effect to the image (from -100 to +100)

* `grey()`: converts the image to grayscale

* `emboss()`: emboss the image

* `smooth($p)`: smooth the image

* `sharp()`: applies a mean removal filter on the image

* `edge()`: applies an edge effect on the image

* `colorize($red, $green, $blue)`: colorize the image (from -255 to +255 for each color)

* `sepia()`: applies a sepia effect

Saving the image
----------------

You can save the image to an explicit file using `save($file, $type = 'jpg', $quality = 80)`:

    $image->save('output.jpg', 'jpg', 85);

Using cache
-----------

Each operation above is not actually applied on the opened image, but added in an operations
array. This operation array, the name, type and modification time of file are hashed using
`sha1()` and the hash is used to look up for a cache file.

Once the cache directory configured, you can call the following methods:

* `jpeg($quality = 80)`: lookup or create a jpeg cache file on-the-fly

* `gif()`: lookup or create a gif cache file on-the-fly

* `png()`: lookup or create a png cache file on-the-fly

For instance:

    require_once('lib/Gregwar/Image.php');

    use Gregwar\Image;

    echo Image::open('test.png')
        ->sepia()
        ->jpeg();

    //Outputs: cache/images/1/8/6/9/c/86e4532dbd9c073075ef08e9751fc9bc0f4.jpg

If the original file and operations do not change, the hashed value will be the same and the
cache will not be generated again.

You can use this directly in an HTML document:

    
    require_once('lib/Gregwar/Image.php');

    use Gregwar\Image;

    // ...
    <img src="<?php echo Image::open('image.jpg')->resize(150, 150)->jpeg(); ?>" />
    // ...

This is powerful since if you change the original image or any of your code the cached hash
will change and the file will be regenerated. 

