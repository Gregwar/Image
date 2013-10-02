<?php

namespace Gregwar\Image\Adapter;

use Gregwar\Image\ImageColor;

class GD extends Common
{
    public static $gdTypes = array(
        'jpeg'  => \IMG_JPG,
        'gif'   => \IMG_GIF,
        'png'   => \IMG_PNG,
    );

    protected function loadResource($resource)
    {
        parent::loadResource($resource);
        imagesavealpha($this->resource, true);
    }

    /**
     * Gets the width and the height for writing some text
     */
    public static function TTFBox($font, $text, $size, $angle = 0)
    {
        $box = imagettfbbox($size, $angle, $font, $text);

        return array(
            'width' => abs($box[2] - $box[0]),
            'height' => abs($box[3] - $box[5])
        );
    }

    public function getName()
    {
        return 'GD';
    }

    public function __construct()
    {
        parent::__construct();

        if (!(extension_loaded('gd') && function_exists('gd_info'))) {
            throw new \RuntimeException('You need to install GD PHP Extension to use this library');
        }
    }

    /**
     * Fills the image background to $bg if the image is transparent
     *
     * @param $bg the background color
     */
    public function fillBackground($bg = 0xffffff)
    {
        $w = $this->width();
        $h = $this->height();
        $n = imagecreatetruecolor($w, $h);
        imagefill($n, 0, 0, ImageColor::gdAllocate($this->resource, $bg));
        imagecopyresampled($n, $this->resource, 0, 0, 0, 0, $w, $h, $w, $h);
        imagedestroy($this->resource);
        $this->resource = $n;
    }

    /**
     * Do the image resize
     */
    protected function doResize($bg, $target_width, $target_height, $new_width, $new_height)
    {
        $width = $this->width();
        $height = $this->height();
        $n = imagecreatetruecolor($target_width, $target_height);

        if ($bg != 'transparent') {
            imagefill($n, 0, 0, ImageColor::gdAllocate($this->resource, $bg));
        } else {
            imagealphablending($n, false);

            $color = imagecolorallocatealpha($n, 0, 0, 0, 127);

            imagefill($n, 0, 0, $color);
            imagesavealpha($n, true);
        }

        imagecopyresampled($n, $this->resource, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($this->resource);

        $this->resource = $n;
    }
    
    /**
     * Crops the image
     *
     * @param int $x the top-left x position of the crop box
     * @param int $y the top-left y position of the crop box
     * @param int $w the width of the crop box
     * @param int $h the height of the crop box
     */
    public function crop($x, $y, $w, $h)
    {
        $destination = imagecreatetruecolor($w, $h);
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        imagecopy($destination, $this->resource, 0, 0, $x, $y, $this->width(), $this->height());
        imagedestroy($this->resource);
        $this->resource = $destination;
    }

    /**
     * Negates the image
     */
    public function negate()
    {
        imagefilter($this->resource, IMG_FILTER_NEGATE);
    }

    /**
     * Changes the brightness of the image
     *
     * @param int $brightness the brightness
     */
    public function brightness($b)
    {
        imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, $b);
    }

    /**
     * Contrasts the image
     *
     * @param int $c the contrast
     */
    public function contrast($c)
    {
        imagefilter($this->resource, IMG_FILTER_CONTRAST, $c);
    }

    /**
     * Apply a grayscale level effect on the image
     */
    public function grayscale()
    {
        imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
    }

    /**
     * Emboss the image
     */
    public function emboss()
    {
        imagefilter($this->resource, IMG_FILTER_EMBOSS);
    }

    /**
     * Smooth the image
     */
    public function smooth($p)
    {
        imagefilter($this->resource, IMG_FILTER_SMOOTH, $p);
    }

    /**
     * Sharps the image
     */
    public function sharp()
    {
        imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * Edges the image
     */
    public function edge()
    {
        imagefilter($this->resource, IMG_FILTER_EDGEDETECT);
    }

    /**
     * Colorize the image
     */
    public function colorize($red, $green, $blue)
    {
        imagefilter($this->resource, IMG_FILTER_COLORIZE, $red, $green, $blue);
    }

    /**
     * Sepias the image
     */
    public function sepia()
    {
        imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
        imagefilter($this->resource, IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Merge with another image
     */
    public function merge(Image $other, $x = 0, $y = 0, $w = null, $h = null)
    {
        $other = clone $other;
        $other->init();
        $other->applyOperations();

        imagealphablending($this->resource, true);

        if (null == $w) {
            $w = $other->width();
        }

        if (null == $h) {
            $h = $other->height();
        }

        imagecopyresampled($this->resource, $other->resource, $x, $y, 0, 0, $w, $h, $w, $h);
    }

    /**
     * Rotate the image
     */
    public function rotate($angle, $background = 0xffffff)
    {
        $this->resource = imagerotate($this->resource, $angle, ImageColor::gdAllocate($this->resource, $background));
        imagealphablending($this->resource, true);
        imagesavealpha($this->resource, true);
    }

    /**
     * Fills the image
     */
    public function fill($color = 0xffffff, $x = 0, $y = 0)
    {
        imagealphablending($this->resource, false);
        imagefilledrectangle($this->resource, $x, $y, $this->width(), $this->height(), ImageColor::gdAllocate($this->resource, $color));
    }

    /**
     * Writes some text
     */
    public function write($font, $text, $x = 0, $y = 0, $size = 12, $angle = 0, $color = 0x000000, $pos = 'left')
    {
        imagealphablending($this->resource, true);

        if ($pos != 'left') {
            $sim_size = self::TTFBox($font, $text, $size, $angle);

            if ($pos == 'center') {
                $x -= $sim_size['width'] / 2;
            }

            if ($pos == 'right') {
                $x -= $sim_size['width'];
            }
        }

        imagettftext($this->resource, $size, $angle, $x, $y, ImageColor::gdAllocate($this->resource, $color), $font, $text);
    }

    /**
     * Draws a rectangle
     */
    public function rectangle($x1, $y1, $x2, $y2, $color, $filled = false)
    {
        if ($filled) {
            imagefilledrectangle($this->resource, $x1, $y1, $x2, $y2, ImageColor::gdAllocate($this->resource, $color));
        } else {
            imagerectangle($this->resource, $x1, $y1, $x2, $y2, ImageColor::gdAllocate($this->resource, $color));
        }
    }

    /**
     * Draws a rounded rectangle
     */
    public function roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $filled = false) {
        if ($color) {
            $color = ImageColor::gdAllocate($this->resource, $color);
        }

        if ($filled == true) {
            imagefilledrectangle($this->resource, $x1+$radius, $y1, $x2-$radius, $y2, $color);
            imagefilledrectangle($this->resource, $x1, $y1+$radius, $x1+$radius-1, $y2-$radius, $color);
            imagefilledrectangle($this->resource, $x2-$radius+1, $y1+$radius, $x2, $y2-$radius, $color);

            imagefilledarc($this->resource,$x1+$radius, $y1+$radius, $radius*2, $radius*2, 180 , 270, $color, IMG_ARC_PIE);
            imagefilledarc($this->resource,$x2-$radius, $y1+$radius, $radius*2, $radius*2, 270 , 360, $color, IMG_ARC_PIE);
            imagefilledarc($this->resource,$x1+$radius, $y2-$radius, $radius*2, $radius*2, 90 , 180, $color, IMG_ARC_PIE);
            imagefilledarc($this->resource,$x2-$radius, $y2-$radius, $radius*2, $radius*2, 360 , 90, $color, IMG_ARC_PIE);
        } else {
            imageline($this->resource, $x1+$radius, $y1, $x2-$radius, $y1, $color);
            imageline($this->resource, $x1+$radius, $y2, $x2-$radius, $y2, $color);
            imageline($this->resource, $x1, $y1+$radius, $x1, $y2-$radius, $color);
            imageline($this->resource, $x2, $y1+$radius, $x2, $y2-$radius, $color);

            imagearc($this->resource,$x1+$radius, $y1+$radius, $radius*2, $radius*2, 180 , 270, $color);
            imagearc($this->resource,$x2-$radius, $y1+$radius, $radius*2, $radius*2, 270 , 360, $color);
            imagearc($this->resource,$x1+$radius, $y2-$radius, $radius*2, $radius*2, 90 , 180, $color);
            imagearc($this->resource,$x2-$radius, $y2-$radius, $radius*2, $radius*2, 360 , 90, $color);
        }
    }

    /**
     * Draws a line
     */
    public function line($x1, $y1, $x2, $y2, $color = 0x000000)
    {
        imageline($this->resource, $x1, $y1, $x2, $y2, ImageColor::gdAllocate($this->resource, $color));
    }

    /**
     * Draws an ellipse
     */
    public function ellipse($cx, $cy, $width, $height, $color = 0x000000, $filled = false)
    {
        if ($filled) {
            imagefilledellipse($this->resource, $cx, $cy, $width, $height, ImageColor::gdAllocate($this->resource, $color));
        } else {
            imageellipse($this->resource, $cx, $cy, $width, $height, ImageColor::gdAllocate($this->resource, $color));
        }
    }

    /**
     * Draws a circle
     */
    public function circle($cx, $cy, $r, $color = 0x000000, $filled = false)
    {
        $this->ellipse($cx, $cy, $r, $r, ImageColor::gdAllocate($this->resource, $color), $filled);
    }

    /**
     * Draws a polygon
     */
    public function polygon(array $points, $color, $filled = false)
    {
        if ($filled) {
            imagefilledpolygon($this->resource, $points, count($points)/2, ImageColor::gdAllocate($this->resource, $color));
        } else {
            imagepolygon($this->resource, $points, count($points)/2, ImageColor::gdAllocate($this->resource, $color));
        }
    }

    /**
     * Gets the width
     */
    public function width()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return imagesx($this->resource);
    }

    /**
     * Gets the height
     */
    public function height()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return imagesy($this->resource);
    }

    protected function createImage($width, $height)
    {
        $this->resource = imagecreatetruecolor($width, $height);
    }

    protected function createImageFromData($data)
    {
        $this->resource = @imagecreatefromstring($data);
    }
    
    /**
     * Converts the image to true color
     */
    protected function convertToTrueColor()
    {
        if (!imageistruecolor($this->resource)) {
            $transparentIndex = imagecolortransparent($this->resource);

            $w = $this->width();
            $h = $this->height();

            $img = imagecreatetruecolor($w, $h);
            imagecopy($img, $this->resource, 0, 0, 0, 0, $w, $h);

            if ($transparentIndex != -1) {
                $width = $this->width();
                $height = $this->height();

                imagealphablending($img, false);
                imagesavealpha($img, true);

                for ($x=0; $x<$width; $x++) {
                    for ($y=0; $y<$height; $y++) {
                        if (imagecolorat($this->resource, $x, $y) == $transparentIndex) {
                            imagesetpixel($img, $x, $y, 127 << 24);
                        }
                    }
                }
            }

            $this->resource = $img;
        }
        
        imagesavealpha($this->resource, true);
    }
    
    public function saveGif($file)
    {
        $transColor = imagecolorallocatealpha($this->resource, 0, 0, 0, 127);
        imagecolortransparent($this->resource, $transColor);
        imagegif($this->resource, $file);
    }

    public function savePng($file)
    {
        imagepng($this->resource, $file);
    }

    public function saveJpeg($file, $quality)
    {
        $success = imagejpeg($this->resource, $file, $quality);
    }

    /**
     * Try to open the file using jpeg
     *
     */
    protected function openJpeg($file)
    {
        $this->resource = @imagecreatefromjpeg($file);
    }

    /**
     * Try to open the file using gif
     */
    protected function openGif($file)
    {
        $this->resource = @imagecreatefromgif($file);
    }

    /**
     * Try to open the file using PNG
     */
    protected function openPng($file)
    {
        $this->resource = @imagecreatefrompng($file);
    }

    /**
     * Does this adapter supports type ?
     */
    protected function supports($type)
    {
        return (imagetypes() & self::$gdTypes[$type]);
    }
    
    protected function getColor($x, $y)
    {
        return imagecolorat($this->resource, $x, $y);
    }
}
