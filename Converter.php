<?php
/**
 * Converter resource
 * User: Clem
 * Date: 30/05/2019
 * Time: 21:46
 */

namespace Gregwar\Image;

class Converter
{

  /**
   * Convert Cm to Pixels using resolution in DPI
   * @param float $cm
   * @param int $resolution in DPI (PPP)
   * @return float
   */
  static function cmToPixels($cm, $resolution = 96) {
    return $resolution * $cm / 2.54;
  }

  /**
   * Convert Inch to Pixels using resolution in DPI
   * @param float $inch
   * @param int $resolution in DPI (PPP)
   * @return float
   */
  static function inchToPixels($inch, $resolution = 96) {
    return $resolution * $inch;
  }

  /**
   * Convert Pixels to Cm using resolution in DPI
   * @param int $pixels
   * @param int $resolution in DPI (PPP)
   * @return float|int|boolean
   */
  static function pixelsToCm($pixels, $resolution = 96) {
    if($resolution == 0) {
      return false;
    }
    return $pixels * 2.54 / $resolution;
  }

  /**
   * Convert Pixels to Inch using resolution in DPI
   * @param int $pixels
   * @param int $resolution in DPI (PPP)
   * @return float|boolean
   */
  static function pixelsToInch($pixels, $resolution = 96) {
    if($resolution == 0) {
      return false;
    }
    return $pixels / $resolution;
  }

}