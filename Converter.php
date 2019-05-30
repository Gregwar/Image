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
   * Convert Cm to Pixels using resolution in ppp
   * @param float $cm
   * @param int $resolution in PPP / DPI
   * @return float
   */
  static function cmToPixels($cm, $resolution = 150) {
    return $resolution * $cm / 2.54;
  }

  /**
   * Convert Inch to Pixels using resolution in ppp
   * @param float $inch
   * @param int $resolution in PPP / DPI
   * @return float
   */
  static function inchToPixel($inch, $resolution = 150) {
    return $resolution * $inch;
  }

  /**
   * Convert Pixels to Cm using resolution in ppp
   * @param $pixels
   * @param int $resolution in PPP / DPI
   * @return float|int
   */
  static function pixelsToCm($pixels, $resolution = 150) {
    return $pixels * 2.54 / $resolution;
  }

  /**
   * Convert Pixels to Inch using resolution in ppp
   * @param float $inch
   * @param int $resolution in PPP / DPI
   * @return float
   */
  static function pixelsToInch($pixels, $resolution = 150) {
    return $pixels * $resolution;
  }

}