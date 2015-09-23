<?php

namespace Gregwar\Image\Adapter;

use Gregwar\Image\ImageColor;
use Gregwar\Image\Image;

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

    public function __construct()
    {
        parent::__construct();

        if (!(extension_loaded('gd') && function_exists('gd_info'))) {
            throw new \RuntimeException('You need to install GD PHP Extension to use this library');
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'GD';
    }

    /**
     * @inheritdoc
     */
    public function fillBackground($background = 0xffffff)
    {
        $w = $this->width();
        $h = $this->height();
        $n = imagecreatetruecolor($w, $h);
        imagefill($n, 0, 0, ImageColor::gdAllocate($this->resource, $background));
        imagecopyresampled($n, $this->resource, 0, 0, 0, 0, $w, $h, $w, $h);
        imagedestroy($this->resource);
        $this->resource = $n;

        return $this;
    }

    /**
     * Do the image resize
     *
     * @return $this
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
            $color = ImageColor::gdAllocate($this->resource, 'transparent');

            imagefill($n, 0, 0, $color);
            imagesavealpha($n, true);
        }

        imagecopyresampled($n, $this->resource, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($this->resource);

        $this->resource = $n;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function crop($x, $y, $width, $height)
    {
        $destination = imagecreatetruecolor($width, $height);
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        imagecopy($destination, $this->resource, 0, 0, $x, $y, $this->width(), $this->height());
        imagedestroy($this->resource);
        $this->resource = $destination;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function negate()
    {
        imagefilter($this->resource, IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function brightness($brightness)
    {
        imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, $brightness);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function contrast($contrast)
    {
        imagefilter($this->resource, IMG_FILTER_CONTRAST, $contrast);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function grayscale()
    {
        imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function emboss()
    {
        imagefilter($this->resource, IMG_FILTER_EMBOSS);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function smooth($p)
    {
        imagefilter($this->resource, IMG_FILTER_SMOOTH, $p);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sharp()
    {
        imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function edge()
    {
        imagefilter($this->resource, IMG_FILTER_EDGEDETECT);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function colorize($red, $green, $blue)
    {
        imagefilter($this->resource, IMG_FILTER_COLORIZE, $red, $green, $blue);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sepia()
    {
        imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
        imagefilter($this->resource, IMG_FILTER_COLORIZE, 100, 50, 0);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function merge(Image $other, $x = 0, $y = 0, $width = null, $height = null)
    {
        $other = clone $other;
        $other->init();
        $other->applyOperations();

        imagealphablending($this->resource, true);

        if (null == $width) {
            $width = $other->width();
        }

        if (null == $height) {
            $height = $other->height();
        }

        imagecopyresampled($this->resource, $other->getAdapter()->getResource(), $x, $y, 0, 0, $width, $height, $width, $height);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rotate($angle, $background = 0xffffff)
    {
        $this->resource = imagerotate($this->resource, $angle, ImageColor::gdAllocate($this->resource, $background));
        imagealphablending($this->resource, true);
        imagesavealpha($this->resource, true);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fill($color = 0xffffff, $x = 0, $y = 0)
    {
        imagealphablending($this->resource, false);
        imagefill($this->resource, $x, $y, ImageColor::gdAllocate($this->resource, $color));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function write($font, $text, $x = 0, $y = 0, $size = 12, $angle = 0, $color = 0x000000, $align = 'left')
    {
        imagealphablending($this->resource, true);

        if ($align != 'left') {
            $sim_size = self::TTFBox($font, $text, $size, $angle);

            if ($align == 'center') {
                $x -= $sim_size['width'] / 2;
            }

            if ($align == 'right') {
                $x -= $sim_size['width'];
            }
        }

        imagettftext($this->resource, $size, $angle, $x, $y, ImageColor::gdAllocate($this->resource, $color), $font, $text);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rectangle($x1, $y1, $x2, $y2, $color, $filled = false)
    {
        if ($filled) {
            imagefilledrectangle($this->resource, $x1, $y1, $x2, $y2, ImageColor::gdAllocate($this->resource, $color));
        } else {
            imagerectangle($this->resource, $x1, $y1, $x2, $y2, ImageColor::gdAllocate($this->resource, $color));
        }

        return $this;
    }

    /**
     * @inheritdoc
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

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function line($x1, $y1, $x2, $y2, $color = 0x000000)
    {
        imageline($this->resource, $x1, $y1, $x2, $y2, ImageColor::gdAllocate($this->resource, $color));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function ellipse($cx, $cy, $width, $height, $color = 0x000000, $filled = false)
    {
        if ($filled) {
            imagefilledellipse($this->resource, $cx, $cy, $width, $height, ImageColor::gdAllocate($this->resource, $color));
        } else {
            imageellipse($this->resource, $cx, $cy, $width, $height, ImageColor::gdAllocate($this->resource, $color));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function circle($cx, $cy, $r, $color = 0x000000, $filled = false)
    {
        return $this->ellipse($cx, $cy, $r, $r, ImageColor::gdAllocate($this->resource, $color), $filled);
    }

    /**
     * @inheritdoc
     */
    public function polygon(array $points, $color, $filled = false)
    {
        if ($filled) {
            imagefilledpolygon($this->resource, $points, count($points)/2, ImageColor::gdAllocate($this->resource, $color));
        } else {
            imagepolygon($this->resource, $points, count($points)/2, ImageColor::gdAllocate($this->resource, $color));
        }

        return $this;
    }

    /**
     *  @inheritdoc
     */
    public function flip($flipVertical, $flipHorizontal) {

        if (!$flipVertical && !$flipHorizontal) {
            return $this;
        }

        if (function_exists('imageflip')) {
            if ($flipVertical && $flipHorizontal) {
                $flipMode = \IMG_FLIP_BOTH;
            } else if ($flipVertical && !$flipHorizontal) {
                $flipMode = \IMG_FLIP_VERTICAL;
            } else if (!$flipVertical && $flipHorizontal) {
                $flipMode = \IMG_FLIP_HORIZONTAL;
            }
            
            imageflip($this->resource, $flipMode);
        } else {
            $width = $this->width();
            $height = $this->height();

            $src_x      = 0;
            $src_y      = 0;
            $src_width  = $width;
            $src_height = $height;

            if ($flipVertical) {
                $src_y      = $height -1;
                $src_height = -$height;
            }

            if ($flipHorizontal) {
                $src_x      = $width -1;
                $src_width  = -$width;
            }

            $imgdest = imagecreatetruecolor ($width, $height);
            imagealphablending($imgdest, false);
            imagesavealpha($imgdest, true);

            if (imagecopyresampled($imgdest, $this->resource, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height)) {
                imagedestroy($this->resource);
                $this->resource = $imgdest;
            }
        }
        
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function width()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return imagesx($this->resource);
    }

    /**
     * @inheritdoc
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
            if (function_exists('imagepalettetotruecolor')) {
                // Available in PHP 5.5
                imagepalettetotruecolor($this->resource);
            } else {
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

        imagesavealpha($this->resource, true);
    }

    /**
     * @inheritdoc
     */
    public function saveGif($file)
    {
        $transColor = imagecolorallocatealpha($this->resource, 255, 255, 255, 127);
        imagecolortransparent($this->resource, $transColor);
        imagegif($this->resource, $file);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function savePng($file)
    {
        imagepng($this->resource, $file);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function saveJpeg($file, $quality)
    {
        imagejpeg($this->resource, $file, $quality);
        return $this;
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

    /**
     * @inheritdoc
     */
    public function enableProgressive(){
        imageinterlace($this->resource, 1);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sharpen($amount = 80, $radius = 0.5, $threshold = 3)
    {
        // Attempt to calibrate the parameters to Photoshop:
        if ($amount > 500) {
            $amount = 500;
        }
        $amount = $amount * 0.016;

        if ($radius > 50) {
            $radius = 50;
        }
        $radius = $radius * 2;

        if ($threshold > 255) {
            $threshold = 255;
        }

        // Only integers make sense.
        $radius = abs(round($radius));     

        // no radius? return object, nothing to do
        if ($radius == 0) {
            return $this;
        }

        $gdimage = & $this->resource;
        $w = $this->width();
        $h = $this->width();

        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);

        // Gaussian blur matrix:
        //
        //    1    2    1
        //    2    4    2
        //    1    2    1
        //
        //////////////////////////////////////////////////


        if (function_exists('imageconvolution')) { // PHP >= 5.1
            $matrix = array(
                array( 1, 2, 1 ),
                array( 2, 4, 2 ),
                array( 1, 2, 1 )
            );
            imagecopy($imgBlur, $gdimage, 0, 0, 0, 0, $w, $h);
            imageconvolution($imgBlur, $matrix, 16, 0);
            
        } else {

            // Move copies of the image around one pixel at the time and merge them with weight
            // according to the matrix. The same matrix is simply repeated for higher radii.
            for ($i = 0; $i < $radius; $i++)    {
                imagecopy($imgBlur, $gdimage, 0, 0, 1, 0, $w - 1, $h); // left
                imagecopymerge($imgBlur, $gdimage, 1, 0, 0, 0, $w, $h, 50); // right
                imagecopymerge($imgBlur, $gdimage, 0, 0, 0, 0, $w, $h, 50); // center
                imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

                imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
                imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
            }
        }

        if ($threshold > 0) {
            // Calculate the difference between the blurred pixels and the original
            // and set the pixels
            for ($x = 0; $x < $w - 1; $x++)    { // each row
                for ($y = 0; $y < $h; $y++)    { // each pixel

                    $rgbOrig = @ImageColorAt($gdimage, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = @ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    // When the masked pixels differ less from the original
                    // than the threshold specifies, they are set to their original value.
                    $rNew = (abs($rOrig - $rBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                    : $rOrig;
                    $gNew = (abs($gOrig - $gBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                    : $gOrig;
                    $bNew = (abs($bOrig - $bBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                    : $bOrig;



                    if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                        $pixCol = ImageColorAllocate($gdimage, $rNew, $gNew, $bNew);
                        ImageSetPixel($gdimage, $x, $y, $pixCol);
                    }
                }
            }
        } else {
            for ($x = 0; $x < $w; $x++)    { // each row
                for ($y = 0; $y < $h; $y++)    { // each pixel
                    $rgbOrig = ImageColorAt($gdimage, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if ($rNew > 255) {
                        $rNew = 255;
                    } elseif ($rNew < 0) {
                        $rNew = 0;
                    }
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if ($gNew > 255) {
                        $gNew = 255;
                    } elseif ($gNew < 0) {
                        $gNew = 0;
                    }
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if ($bNew > 255) {
                        $bNew = 255;
                    } elseif ($bNew < 0) {
                        $bNew = 0;
                    }
                    $rgbNew = ($rNew << 16) + ($gNew << 8) + $bNew;
                    ImageSetPixel($gdimage, $x, $y, $rgbNew);
                }
            }
        }
        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);

        return $this;
    }
}
