<?php

namespace Gregwar\Image\Adapter;

use Gregwar\Image\Image;

class Imagick extends Common
{
    /**
     * @var \Imagick
     */
    protected $resource;

    /**
     * @inheritdoc
     */
	public function __construct()
    {
        parent::__construct();

        if (!(extension_loaded('imagick') && class_exists('Imagick'))) {
            throw new \RuntimeException('You need to install Imagick PHP Extension to use this library');
        }
	}

    /**
     * @inheritdoc
     */
	public function getName()
    {
		return 'ImageMagick';
	}

    /**
     * @inheritdoc
     */
    public function width()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return $this->resource->getImageWidth();
    }

    /**
     * @inheritdoc
     */
    public function height()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return $this->resource->getImageHeight();
    }

    /**
     * @inheritdoc
     */
    public function saveGif($file)
    {
        $this->resource->setImageFormat('gif');
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function savePng($file)
    {
        $this->resource->setImageFormat('png');
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function saveJpeg($file, $quality)
    {
        $this->resource->setImageFormat('jpeg');
        $this->resource->setCompressionQuality($quality);
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function crop($x, $y, $width, $height)
    {
        $this->resource->cropImage($width, $height, $x, $y);

        return $this;
    }

    /**
     * Fills the image background to $bg if the image is transparent
     *
     * @param int $background background color
     *
     * @return $this
     */
    public function fillBackground($background = 0xffffff)
    {
        // TODO: Implement fillBackground() method.
    }

    /**
     * Negates the image
     *
     * @return $this
     */
    public function negate()
    {
        // TODO: Implement negate() method.
    }

    /**
     * Changes the brightness of the image
     *
     * @param int $brightness the brightness
     *
     * @return $this
     */
    public function brightness($brightness)
    {
        // TODO: Implement brightness() method.
    }

    /**
     * Contrasts the image
     *
     * @param int $contrast the contrast [-100, 100]
     *
     * @return $this
     */
    public function contrast($contrast)
    {
        // TODO: Implement contrast() method.
    }

    /**
     * Apply a grayscale level effect on the image
     *
     * @return $this
     */
    public function grayscale()
    {
        // TODO: Implement grayscale() method.
    }

    /**
     * Emboss the image
     *
     * @return $this
     */
    public function emboss()
    {
        // TODO: Implement emboss() method.
    }

    /**
     * Smooth the image
     *
     * @param int $p value between [-10,10]
     *
     * @return $this
     */
    public function smooth($p)
    {
        // TODO: Implement smooth() method.
    }

    /**
     * Sharps the image
     *
     * @return $this
     */
    public function sharp()
    {
        // TODO: Implement sharp() method.
    }

    /**
     * Edges the image
     *
     * @return $this
     */
    public function edge()
    {
        // TODO: Implement edge() method.
    }

    /**
     * Colorize the image
     *
     * @param int $red value in range [-255, 255]
     * @param int $green value in range [-255, 255]
     * @param int $blue value in range [-255, 255]
     *
     * @return $this
     */
    public function colorize($red, $green, $blue)
    {
        // TODO: Implement colorize() method.
    }

    /**
     * apply sepia to the image
     *
     * @return $this
     */
    public function sepia()
    {
        // TODO: Implement sepia() method.
    }

    /**
     * Merge with another image
     *
     * @param Image $other
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function merge(Image $other, $x = 0, $y = 0, $width = null, $height = null)
    {
        // TODO: Implement merge() method.
    }

    /**
     * Rotate the image
     *
     * @param float $angle
     * @param int $background
     *
     * @return $this
     */
    public function rotate($angle, $background = 0xffffff)
    {
        // TODO: Implement rotate() method.
    }

    /**
     * Fills the image
     *
     * @param int $color
     * @param int $x
     * @param int $y
     *
     * @return $this
     */
    public function fill($color = 0xffffff, $x = 0, $y = 0)
    {
        // TODO: Implement fill() method.
    }

    /**
     * write text to the image
     *
     * @param string $font
     * @param string $text
     * @param int $x
     * @param int $y
     * @param int $size
     * @param int $angle
     * @param int $color
     * @param string $align
     */
    public function write($font, $text, $x = 0, $y = 0, $size = 12, $angle = 0, $color = 0x000000, $align = 'left')
    {
        // TODO: Implement write() method.
    }

    /**
     * Draws a rectangle
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $color
     * @param bool $filled
     *
     * @return $this
     */
    public function rectangle($x1, $y1, $x2, $y2, $color, $filled = false)
    {
        // TODO: Implement rectangle() method.
    }

    /**
     * Draws a rounded rectangle
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $radius
     * @param int $color
     * @param bool $filled
     *
     * @return $this
     */
    public function roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $filled = false)
    {
        // TODO: Implement roundedRectangle() method.
    }

    /**
     * Draws a line
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $color
     *
     * @return $this
     */
    public function line($x1, $y1, $x2, $y2, $color = 0x000000)
    {
        // TODO: Implement line() method.
    }

    /**
     * Draws an ellipse
     *
     * @param int $cx
     * @param int $cy
     * @param int $width
     * @param int $height
     * @param int $color
     * @param bool $filled
     *
     * @return $this
     */
    public function ellipse($cx, $cy, $width, $height, $color = 0x000000, $filled = false)
    {
        // TODO: Implement ellipse() method.
    }

    /**
     * Draws a circle
     *
     * @param int $cx
     * @param int $cy
     * @param int $r
     * @param int $color
     * @param bool $filled
     *
     * @return $this
     */
    public function circle($cx, $cy, $r, $color = 0x000000, $filled = false)
    {
        // TODO: Implement circle() method.
    }

    /**
     * Draws a polygon
     *
     * @param array $points
     * @param int $color
     * @param bool $filled
     *
     * @return $this
     */
    public function polygon(array $points, $color, $filled = false)
    {
        // TODO: Implement polygon() method.
    }

    /**
     * Flips the image
     *
     * @param int $flipVertical
     * @param int $flipHorizontal
     *
     * @return $this
     */
    public function flip($flipVertical, $flipHorizontal)
    {
        // TODO: Implement flip() method.
    }

    /**
     * @inheritdoc
     */
    protected function openGif($file)
    {
        $this->resource = new \Imagick($file);
    }

    /**
     * @inheritdoc
     */
    protected function openJpeg($file)
    {
        $this->resource = new \Imagick($file);
    }

    /**
     * @inheritdoc
     */
    protected function openPng($file)
    {
        $this->resource = new \Imagick($file);
    }

    /**
     * Creates an image
     */
    protected function createImage($width, $height)
    {
        // TODO: Implement createImage() method.
    }

    /**
     * Creating an image using $data
     */
    protected function createImageFromData($data)
    {
        // TODO: Implement createImageFromData() method.
    }

    /**
     * Resizes the image to an image having size of $target_width, $target_height, using
     * $new_width and $new_height and padding with $bg color
     */
    protected function doResize($bg, $target_width, $target_height, $new_width, $new_height)
    {
        // TODO: Implement doResize() method.
    }

    /**
     * Gets the color of the $x, $y pixel
     */
    protected function getColor($x, $y)
    {
        // TODO: Implement getColor() method.
    }
}
