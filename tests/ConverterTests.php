<?php
/**
 * Tests for Converter class
 * User: Clem
 * Date: 31/05/2019
 * Time: 09:39
 */

use \Gregwar\Image\Converter;

class ConverterTests extends \PHPUnit\Framework\TestCase
{
  public function testCmToPixels()
  {
    // Basic convertion
    $this->assertEquals(37.795275591, Converter::cmToPixels(1), '', 0.001);

    // 6cm convertion
    $this->assertEquals(226.771653543, Converter::cmToPixels(6), '', 0.001);

    // 6cm convertion with 300 dpi
    $this->assertEquals(708.6614173, Converter::cmToPixels(6, 300), '', 0.001);
  }

  public function testInchToPixels()
  {
    // Basic convertion
    $this->assertEquals(96, Converter::inchToPixels(1));

    // 6inch convertion
    $this->assertEquals(576, Converter::inchToPixels(6));

    // 6inch convertion with 300 dpi
    $this->assertEquals(1800, Converter::inchToPixels(6, 300));
  }

  public function testPixelsToCm()
  {
    // Basic convertion
    $this->assertEquals(0.026458333, Converter::pixelsToCm(1), '', 0.001);

    // 960 pixels convertion
    $this->assertEquals(25.4, Converter::pixelsToCm(960), '', 0.001);

    // 960 pixels with 300 dpi
    $this->assertEquals(8.128, Converter::pixelsToCm(960, 300), '', 0.001);

    // 960 pixels with 0 dpi
    $this->assertEquals(false, Converter::pixelsToCm(960, 0));
  }

  public function testPixelsToInch()
  {
    // Basic convertion
    $this->assertEquals(0.010416667, Converter::pixelsToInch(1), '', 0.001);

    // 960 pixels convertion
    $this->assertEquals(10, Converter::pixelsToInch(960), '', 0.001);

    // 960 pixels with 300 dpi
    $this->assertEquals(3.2, Converter::pixelsToInch(960, 300), '', 0.001);

    // 960 pixels with 0 dpi
    $this->assertEquals(false, Converter::pixelsToInch(960, 0));
  }
}