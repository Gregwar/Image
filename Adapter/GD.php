<?php

namespace Gregwar\Image\Adapter;

use Gregwar\Image\ImageColor;

class GD extends Common
{
    public static $gdTypes = array(
        'jpeg'  => IMG_JPG,
        'gif'   => IMG_GIF,
        'png'   => IMG_PNG,
    );

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
        imagefill($n, 0, 0, ImageColor::parse($bg));
        imagecopyresampled($n, $this->resource, 0, 0, 0, 0, $w, $h, $w, $h);
        imagedestroy($this->resource);
        $this->resource = $n;
    }

    /**
     * Resizes the image. It will never be enlarged.
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    public function resize($w = null, $h = null, $bg = 0xffffff, $force = false, $rescale = false, $crop = false)
    {
        $width = $this->width();
        $height = $this->height();
        $scale = 1.0;

        if ($h === null && preg_match('#^(.+)%$#mUsi', $w, $matches)) {
            $w = round($width * ((float)$matches[1]/100.0));
            $h = round($height * ((float)$matches[1]/100.0));
        }

        if (!$rescale && (!$force || $crop)) {
            if ($w!=null && $width>$w) {
                $scale = $width/$w;
            }

            if ($h!=null && $height>$h) {
                if ($height/$h > $scale)
                    $scale = $height/$h;
            }
        } else {
            if ($w!=null) {
                $scale = $width/$w;
                $new_width = $w;
            }

            if ($h!=null) {
                if ($w!=null && $rescale) {
                    $scale = max($scale,$height/$h);
                } else {
                    $scale = $height/$h;
                }
                $new_height = $h;
            }
        }

        if (!$force || $w==null || $rescale) {
            $new_width = round($width/$scale);
        }

        if (!$force || $h==null || $rescale) {
            $new_height = round($height/$scale);
        }

        if ($w == null || $crop) {
            $w = $new_width;
        }

        if ($h == null || $crop) {
            $h = $new_height;
        }

        $n = imagecreatetruecolor($w, $h);

        if ($bg != 'transparent') {
            imagefill($n, 0, 0, ImageColor::parse($bg));
        } else {
            imagealphablending($n, false);

            $color = imagecolorallocatealpha($n, 0, 0, 0, 127);

            imagefill($n, 0, 0, $color);
            imagesavealpha($n, true);
        }

        imagecopyresampled($n, $this->resource, ($w-$new_width)/2, ($h-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height);
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
        $this->resource = imagerotate($this->resource, $angle, ImageColor::parse($background));
        imagealphablending($this->resource, true);
        imagesavealpha($this->resource, true);
    }

    /**
     * Fills the image
     */
    public function fill($color = 0xffffff, $x = 0, $y = 0)
    {
        imagealphablending($this->resource, false);
        imagefilledrectangle($this->resource, $x, $y, $this->width(), $this->height(), ImageColor::parse($color));
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

        imagettftext($this->resource, $size, $angle, $x, $y, ImageColor::parse($color), $font, $text);
    }

    /**
     * Draws a rectangle
     */
    public function rectangle($x1, $y1, $x2, $y2, $color, $filled = false)
    {
        if ($filled) {
            imagefilledrectangle($this->resource, $x1, $y1, $x2, $y2, ImageColor::parse($color));
        } else {
            imagerectangle($this->resource, $x1, $y1, $x2, $y2, ImageColor::parse($color));
        }
    }

    /**
     * Draws a rounded rectangle
     */
    public function roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $filled = false) {
        if ($color) {
            $color = ImageColor::parse($color);
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
        imageline($this->resource, $x1, $y1, $x2, $y2, ImageColor::parse($color));
    }

    /**
     * Draws an ellipse
     */
    public function ellipse($cx, $cy, $width, $height, $color = 0x000000, $filled = false)
    {
        if ($filled) {
            imagefilledellipse($this->resource, $cx, $cy, $width, $height, ImageColor::parse($color));
        } else {
            imageellipse($this->resource, $cx, $cy, $width, $height, ImageColor::parse($color));
        }
    }

    /**
     * Draws a circle
     */
    public function circle($cx, $cy, $r, $color = 0x000000, $filled = false)
    {
        $this->ellipse($cx, $cy, $r, $r, ImageColor::parse($color), $filled);
    }

    /**
     * Draws a polygon
     */
    public function polygon(array $points, $color, $filled = false)
    {
        if ($filled)
        {
            imagefilledpolygon($this->resource, $points, count($points)/2, ImageColor::parse($color));
        } else {
            imagepolygon($this->resource, $points, count($points)/2, ImageColor::parse($color));
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
    public function convertToTrueColor()
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
    protected function openJpeg()
    {
        $this->resource = @imagecreatefromjpeg($this->file);
    }

    /**
     * Try to open the file using gif
     */
    protected function openGif()
    {
        $this->resource = @imagecreatefromgif($this->file);
    }

    /**
     * Try to open the file using PNG
     */
    protected function openPng()
    {
        $this->resource = @imagecreatefrompng($this->file);
    }

    public function supports($type)
    {
        return (imagetypes() & self::$gdTypes[$this->type]);
    }
}
