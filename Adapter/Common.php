<?php

namespace Gregwar\Image\Adapter;

abstract class Common extends Adapter
{
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
     * Try to open the file
     *
     * XXX: this logic should maybe be factorized
     */
    public function init()
    {
        if (null === $this->file) {
            if (null === $this->data) {
                if (null === $this->resource) {
                    $this->createImage($this->width, $this->height);
                }
            } else {
                $this->createImageFromData($this->data);

                if (false === $this->resource) {
                    throw new \UnexpectedValueException('Unable to create file from string.');
                }
            }
        } else {
            if (null === $this->resource) {
                if (!$this->supports($this->type)) {
                    throw new \RuntimeException('Type '.$this->type.' is not supported by GD');
                }

                if ($this->type == 'jpeg') {
                    $this->openJpeg();
                }

                if ($this->type == 'gif') {
                    $this->openGif();
                }

                if ($this->type == 'png') {
                    $this->openPng();
                }

                if (false === $this->resource) {
                    throw new \UnexpectedValueException('Unable to open file ('.$this->file.')');
                } else {
                    $this->convertToTrueColor();
                }
            }
        }

        if ($this->resource) {
            imagesavealpha($this->resource, true);
        }

        return $this;
    }
}
