<?php

namespace Gregwar\Image\Adapter;

abstract class Common extends Adapter
{
    /**
     * The image resource handler
     */
    protected $resource = null;

    public function __construct()
    {
    }

    /**
     * Perform a zoom crop of the image to desired width and height
     *
     * @param integer $width  Desired width
     * @param integer $height Desired height
     * @param int $bg
     * @return void
     */
    public function zoomCrop($width, $height, $bg = 0xffffff)
    {
        // Calculate the different ratios
        $originalRatio = $this->width() / $this->height();
        $newRatio = $width / $height;

        // Compare ratios
        if ($originalRatio > $newRatio) {
            // Original image is wider
            $newHeight = $height;
            $newWidth = (int) $height * $originalRatio;
        } else {
            // Equal width or smaller
            $newHeight = (int) $width / $originalRatio;
            $newWidth = $width;
        }

        // Perform resize
        $this->resize($newWidth, $newHeight, $bg, true);

        // Calculate cropping area
        $xPos = (int) ($newWidth - $width) / 2;
        $yPos = (int) ($newHeight - $height) / 2;

        // Crop image to reach desired size
        $this->crop($xPos, $yPos, $width, $height);
    }

    /**
     * Resizes the image forcing the destination to have exactly the
     * given width and the height
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    public function forceResize($width = null, $height = null, $background = 0xffffff)
    {
        $this->resize($width, $height, $background, true);
    }

    /**
     * Resizes the image preserving scale. Can enlarge it.
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    public function scaleResize($width = null, $height = null, $background=0xffffff, $crop = false)
    {
        $this->resize($width, $height, $background, false, true, $crop);
    }

    /**
     * Works as resize() excepts that the layout will be cropped
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    public function cropResize($width = null, $height = null, $background=0xffffff)
    {
        $this->resize($width, $height, $background, false, false, true);
    }

    /**
     * Opens the image
     */
    abstract protected function openGif($file);
    abstract protected function openJpeg($file);
    abstract protected function openPng($file);

    /**
     * Creates an image
     */
    abstract protected function createImage($width, $height);

    /**
     * Creating an image using $data
     */
    abstract protected function createImageFromData($data);

    /**
     * Loading image from $resource
     */
    protected function loadResource($resource)
    {
        $this->resource = $resource;
    }

    protected function loadFile($file, $type)
    {
        if (!$this->supports($type)) {
            throw new \RuntimeException('Type '.$type.' is not supported by GD');
        }

        if ($type == 'jpeg') {
            $this->openJpeg($file);
        }

        if ($type == 'gif') {
            $this->openGif($file);
        }

        if ($type == 'png') {
            $this->openPng($file);
        }

        if (false === $this->resource) {
            throw new \UnexpectedValueException('Unable to open file ('.$file.')');
        } else {
            $this->convertToTrueColor();
        }
    }

    /**
     * Loads or create the image from its source
     */
    public function init()
    {
        $source = $this->source;

        if ($source instanceof \Gregwar\Image\Source\File) {
            $this->loadFile($source->getFile(), $source->guessType());
        } else if ($source instanceof \Gregwar\Image\Source\Create) {
            $this->createImage($source->getWidth(), $source->getHeight());
        } else if ($source instanceof \Gregwar\Image\Source\Data) {
            $this->createImageFromData($source->getData());
        } else if ($source instanceof \Gregwar\Image\Source\Resource) {
            $this->loadResource($source->getResource());
        } else {
            throw new \Exception('Unsupported image source type '.get_class($source));
        }

        return $this;
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

        $this->doResize($bg, $w, $h, $new_width, $new_height);
    }

    /**
     * Trim background color arround the image
     *
     * @param int $bg the background
     */
    protected function _trimColor($background=0xffffff)
    {
        $width = $this->width();
        $height = $this->height();

        $b_top = 0;
        $b_lft = 0;
        $b_btm = $height - 1;
        $b_rt = $width - 1;

        //top
        for(; $b_top < $height; ++$b_top) {
            for($x = 0; $x < $width; ++$x) {
                if ($this->getColor($x, $b_top) != $background) {
                    break 2;
                }
            }
        }

        // bottom
        for(; $b_btm >= 0; --$b_btm) {
            for($x = 0; $x < $width; ++$x) {
                if ($this->getColor($x, $b_btm) != $background) {
                    break 2;
                }
            }
        }

        // left
        for(; $b_lft < $width; ++$b_lft) {
            for($y = $b_top; $y <= $b_btm; ++$y) {
                if ($this->getColor($b_lft, $y) != $background) {
                    break 2;
                }
            }
        }
    
        // right
        for(; $b_rt >= 0; --$b_rt) {
            for($y = $b_top; $y <= $b_btm; ++$y) {
                if ($this->getColor($b_rt, $y) != $background) {
                    break 2;
                }
            }
        }
    
        $b_btm++;
        $b_rt++;
                
        $this->crop($b_lft, $b_top, $b_rt - $b_lft, $b_btm - $b_top);
    }

    /**
     * Resizes the image to an image having size of $target_width, $target_height, using
     * $new_width and $new_height and padding with $bg color
     */
    abstract protected function doResize($bg, $target_width, $target_height, $new_width, $new_height);

    /**
     * Crops the image at $x, $y with a size of $w x $h
     */
    abstract public function crop($x, $y, $w, $h);

    /**
     * Gets the color of the $x, $y pixel
     */
    abstract protected function getColor($x, $y);
}
