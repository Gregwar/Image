Gregwar's Image class
=====================

The `Gregwar\Image` class purpose is to provide a simple object-oriented images handling and caching API.

Usage
=====

Basic handling
--------------

Using methods chaining, you can open, transform and save a file in a single line:

```php
<?php
require_once('lib/Gregwar/Image.php');

use Gregwar\Image\Image;

Image::open('in.png')
    ->resize(100, 100)
    ->negate()
    ->save('out.jpg');
```

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

* `grayscale()`: converts the image to grayscale

* `emboss()`: emboss the image

* `smooth($p)`: smooth the image

* `sharp()`: applies a mean removal filter on the image

* `edge()`: applies an edge effect on the image

* `colorize($red, $green, $blue)`: colorize the image (from -255 to +255 for each color)

* `sepia()`: applies a sepia effect

* `merge($image, $x, $y, $width, $height)`: merges two images

* `fill($color, $x, $y)`: fills the image with the given color

* `write($font, $text, $x, $y, $size, $angle, $color, $position)`: writes text over image, $position can be any of 'left', 'right', or 'center'

* `rectangle($x1, $y1, $x2, $y2, $color, $filled=false)`: draws a rectangle

* `roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $filled=false)`: draws a rounded rectangle ($radius can be anything from 0)

* `line($x1, $y1, $x2, $y2, $color)`: draws a line

* `ellipse($cx, $cy, $width, $height, $color, $filled=false)`: draws an ellipse

* `circle($cx, $cy, $r, $color, $filled=false)`: draws a circle

* `zoomCrop($width, $height, $background)`: resize and crop the image to fit to given dimensions

* `fillBackground($bg=0xffffff)`: fills the background of a transparent image to the 'bg' color

You can also create image from scratch using:

```php
<?php
    Image::create(200, 100);
```

Where 200 is the width and 100 the height

Saving the image
----------------

You can save the image to an explicit file using `save($file, $type = 'jpg', $quality = 80)`:

```php
<?php
    // ...
    $image->save('output.jpg', 'jpg', 85);
```

You can also get the contents of the image using `get($type = 'jpg', $quality = 80)`, which will return the binary contents of the image

Using cache
-----------

Each operation above is not actually applied on the opened image, but added in an operations
array. This operation array, the name, type and modification time of file are hashed using
`sha1()` and the hash is used to look up for a cache file.

Once the cache directory configured, you can call the following methods:

* `jpeg($quality = 80)`: lookup or create a jpeg cache file on-the-fly

* `gif()`: lookup or create a gif cache file on-the-fly

* `png()`: lookup or create a png cache file on-the-fly

* `guess($quality = 80)`: guesses the type (use the same as input) and lookup or create a
  cache file on-the-fly

* `setName($prettyName)`: sets a name prefix for the file, if you want it to be more SEO-friendly.
   for instance, if you call it "Fancy Image", the cache will look like something/fancy-image-something.jpg.

For instance:

```php
<?php
require_once('lib/Gregwar/Image.php');

use Gregwar\Image\Image;

echo Image::open('test.png')
    ->sepia()
    ->jpeg();

//Outputs: cache/images/1/8/6/9/c/86e4532dbd9c073075ef08e9751fc9bc0f4.jpg
```

If the original file and operations do not change, the hashed value will be the same and the
cache will not be generated again.

You can use this directly in an HTML document:


```php
<?php
require_once('lib/Gregwar/Image.php');

use Gregwar\Image\Image;

// ...
<img src="<?php echo Image::open('image.jpg')->resize(150, 150)->jpeg(); ?>" />
// ...
```

This is powerful since if you change the original image or any of your code the cached hash
will change and the file will be regenerated.

Writing image
-------------

You can also create your own image on-the-fly using drawing functions:


```php
<img src="<?php echo Image::create(300, 300)
    ->fill(0xffaaaa)    // Filling with a light red
    ->rectangle(0xff3333, 0, 100, 300, 200, true) // Drawing a red rectangle
    // Writing "Hello $username !" on the picture using a custom TTF font file
    ->write('./fonts/CaviarDreams.ttf', 'Hello '.$username.'!', 150, 150, 20, 0, 'white', 'center')
    ->jpeg(); ?>" />
```

Garbage Collect
===============

To prevent the cache from growing forever, you can use the provided GarbageCollect class as below:

```php
<?php
// This could be a cron called each day @3:00AM for instance
use Gregwar\Image\GarbageCollect;

// Removes all the files from ../cache that are more than 30 days
// old. A verbose output will explain which files are deleted
GarbageCollect::dropOldFiles(__DIR__.'/../cache', 30, true);

```

Using with composer
===================

This repository is available with composer under the name `gregwar/image`, so simply add this to
your requires :

```
    "requires": {
        ...
        "gregwar/image": "dev-master"
        ...
    }
```

And update your dependencies, you'll be able to use the composer autoloader to load the class

License
=======

`Gregwar\Image` is under MIT License

Development
===========

`Gregwar\Image` is using PHP metaprogramming paradigms so it make it easy to enhance.

Each function begining with a single underscore `_` can be added to the operations array and
will be automatically called when compiling down the image.

You could for instance add your own method:

```php
<?php
    // ***
    private function _myFilter()
    {
        $this->_negate();
        $this->_sepia();
    }
```

Which could be usable on the Images

```php
<?php
    $image->myFilter();
```

Do not hesitate to fork this repository and customize it !
