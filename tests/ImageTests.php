<?php

use Gregwar\Image\Image;

/**
 * Unit testing for Image
 */
class ImageTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing the basic width & height
     */
    public function testBasics()
    {
        $image = $this->open('monalisa.jpg');

        $this->assertEquals($image->width(), 771);
        $this->assertEquals($image->height(), 961);
    }

    /**
     * Testing the resize
     */
    public function testResize()
    {
        $image = $this->open('monalisa.jpg');

        $out = $this->output('monalisa_small.jpg');
        $image
            ->resize(300, 200)
            ->save($out)
            ;

        $this->assertTrue(file_exists($out));

        $i = imagecreatefromjpeg($out);
        $this->assertEquals(300, imagesx($i));
        $this->assertEquals(200, imagesy($i));
    }

    /**
     * Testing the resize %
     */
    public function testResizePercent()
    {
        $image = $this->open('monalisa.jpg');

        $out = $this->output('monalisa_small.jpg');
        $image
            ->resize('50%')
            ->save($out)
            ;
        
        $this->assertTrue(file_exists($out));

        $i = imagecreatefromjpeg($out);
        $this->assertEquals(386, imagesx($i));
        $this->assertEquals(481, imagesy($i));
    }

    /**
     * Testing to create an image, jpeg, gif and png
     */
    public function testCreateImage()
    {
        $black = $this->output('black.jpg');

        Image::create(150, 200)
            ->fill('black')
            ->save($black, 100);

        $i = imagecreatefromjpeg($black);
        $this->assertTrue(file_exists($black));
        $this->assertEquals(150, imagesx($i));
        $this->assertEquals(200, imagesy($i));

        $j = imagecolorat($i, 40, 40);
        $this->assertEquals(0, $j);

        $black = $this->output('black.png');
        Image::create(150, 200)
            ->fill('black')
            ->save($black, 'png');

        $i = imagecreatefrompng($black);
        $this->assertTrue(file_exists($black));
        $this->assertEquals(150, imagesx($i));
        $this->assertEquals(200, imagesy($i));

        $black = $this->output('black.gif');
        Image::create(150, 200)
            ->fill('black')
            ->save($black, 'gif');

        $i = imagecreatefromgif($black);
        $this->assertTrue(file_exists($black));
        $this->assertEquals(150, imagesx($i));
        $this->assertEquals(200, imagesy($i));
    }

    /**
     * Testing type guess
     */
    public function testGuess()
    {
        $image = $this->open('monalisa.jpg');
        $this->assertEquals('jpeg', $image->guessType());
        $image = $this->open('monalisa.png');
        $this->assertEquals('png', $image->guessType());
        $image = $this->open('monalisa.gif');
        $this->assertEquals('gif', $image->guessType());
    }

    /**
     * Testing that caching an image without operations will result
     * in the original image
     */
    public function testNoCache()
    {
        $monalisa = __DIR__ . '/files/monalisa.jpg';
        $image = $this->open('monalisa.jpg');
        $this->assertEquals($monalisa, $image->guess());
    }

    /**
     * Testing using cache
     */
    public function testCache()
    {
        $output = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->guess();

        $this->assertTrue(file_exists($output));
        $i = imagecreatefromjpeg($output);
        $this->assertEquals(100, imagesx($i));
        $this->assertEquals(50, imagesy($i));

        $output2 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->guess();

        $this->assertEquals($output, $output2);
        
        $output3 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->png();
        $this->assertTrue(file_exists($output));
        $i = imagecreatefrompng($output3);
        $this->assertEquals(100, imagesx($i));
        $this->assertEquals(50, imagesy($i));
        
        $output4 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->gif();
        $this->assertTrue(file_exists($output));
        $i = imagecreatefromgif($output4);
        $this->assertEquals(100, imagesx($i));
        $this->assertEquals(50, imagesy($i));
    }

    /**
     * Testing creating image from data
     */
    public function testData()
    {
        $data = file_get_contents(__DIR__ . '/files/monalisa.jpg');

        $output = $this->output('mona.jpg');
        $image = Image::fromData($data);
        $image->save($output);

        $this->assertTrue(file_exists($output));
        $i = imagecreatefromjpeg($output);
        $this->assertEquals(771, imagesx($i));
        $this->assertEquals(961, imagesy($i));
        
    }

    /**
     * Opening an image
     */
    protected function open($file)
    {
        $image = Image::open(__DIR__ . '/files/' . $file);
        $image->setCacheDir(__DIR__.'/output/cache/');
        return $image;
    }

    /**
     * Testing transparent image
     */
    public function testTransparent()
    {
        $gif = $this->output('transparent.gif');
        $i = Image::create(200, 100)
            ->fill('transparent')
            ->save($gif, 'gif');

        $this->assertTrue(file_exists($gif));
        $img = imagecreatefromgif($gif);
        $this->assertEquals(200, imagesx($img));
        $this->assertEquals(100, imagesy($img));
        $index = imagecolorat($img, 2, 2);
        $color = imagecolorsforindex($img, $index);
        $this->assertEquals(127, $color['alpha']);
    }

    public function testNonExistingFile()
    {
        $jpg = $this->output('a.jpg');
        $img = $this->open('non_existing_file.jpg')
            ->negate();
        $error = $img->save($jpg);

        $this->assertTrue(file_exists($error));
    }

    /**
     * * @expectedException              \Exception
     */
    public function testNonExistingFileNoFallback()
    {
        Image::open('non_existing_file.jpg')
            ->useFallback(false)
            ->save();
    }

    /**
     * Outputing an image to a file
     */
    protected function output($file)
    {
        return __DIR__ . '/output/' . $file;
    }

    /**
     * Reinitialize the output dir
     */
    public function setUp()
    {
        $dir = $this->output('');
        `rm -rf $dir`;
        mkdir($dir);
        mkdir($this->output('cache'));
    }
}
