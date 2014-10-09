<?php

use Gregwar\Image\Image;

use Gregwar\Image\ImageColor;

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
     * in the original image when force cache is disabled
     */
    public function testNoCache()
    {
        $monalisa = __DIR__ . '/files/monalisa.jpg';
        $image = $this->open('monalisa.jpg')->setForceCache(false);
        $this->assertEquals($monalisa, $image->guess());
        $image = $this->open('monalisa.jpg');
        $this->assertNotEquals($monalisa, $image->guess());
        $image = $this->open('monalisa.jpg')->setForceCache(true);
        $this->assertNotEquals($monalisa, $image->guess());
    }

    public function testActualCache()
    {
        $output = $this->open('monalisa.jpg')
            ->setCacheDir('/magic/path/to/cache/')
            ->resize(100, 50)->negate()
            ->guess();

        $this->assertContains('/magic/path/to/cache', $output);
        $file = str_replace('/magic/path/to', __DIR__.'/output/', $output);
        $this->assertTrue(file_exists($file));
    }

    public function testCacheData()
    {
        $output = $this->open('monalisa.jpg')
            ->resize(300, 200)
            ->cacheData();

        $jpg = imagecreatefromstring($output);
        $this->assertEquals(300, imagesx($jpg));
        $this->assertEquals(200, imagesy($jpg));
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
        $image->setActualCacheDir(__DIR__.'/output/cache/');
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
     * * @expectedException              \UnexpectedValueException
     */
    public function testNonExistingFileNoFallback()
    {
        Image::open('non_existing_file.jpg')
            ->useFallback(false)
            ->save($this->output('a.jpg'));
    }

    /**
     * Test that image::save returns the file name
     */
    public function testSaveReturn()
    {
        $red = $this->output('red.jpg');
        $result = Image::create(10, 10)
            ->fill('red')
            ->save($red);

        $this->assertEquals($red, $result);
    }

    /**
     * Testing merge
     */
    public function testMerge()
    {
        $out = $this->output('merge.jpg');
        Image::create(100, 100)
            ->fill('red')
            ->merge(Image::create(50, 50)
                ->fill('black')
            )
            ->save($out);

        // Merge file exists
        $this->assertTrue(file_exists($out));

        // Test that the upper left zone contain a black pixel, and the lower
        // down contains a red one
        $img = imagecreatefromjpeg($out);
        $this->assertColorEquals('black', imagecolorat($img, 5, 12));
        $this->assertColorEquals('red', imagecolorat($img, 55, 62));
    }

    /**
     * Test that dependencies are well generated
     */
    public function testDependencies()
    {
        $this->assertEquals(array(), Image::create(100, 100)
            ->getDependencies());
        $this->assertEquals(array(), Image::create(100, 100)
            ->merge(Image::create(100, 50))
            ->getDependencies());

        $this->assertEquals(array('toto.jpg'), Image::open('toto.jpg')
            ->merge(Image::create(100, 50))
            ->getDependencies());
        
        $this->assertEquals(array('toto.jpg', 'titi.jpg'), Image::open('toto.jpg')
            ->merge(Image::open('titi.jpg'))
            ->getDependencies());
        
        $this->assertEquals(array('toto.jpg', 'titi.jpg', 'tata.jpg'), Image::open('toto.jpg')
            ->merge(Image::open('titi.jpg')
                    ->merge(Image::open('tata.jpg')))
            ->getDependencies());
    }

    /**
     * Test that pretty name (for referencing) is well respected
     */
    public function testPrettyName()
    {
        $output = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->setPrettyName('davinci', false)
            ->guess();

        $this->assertContains('davinci', $output);
        
        $output2 = $this->open('monalisa.jpg')
            ->resize(100, 55)->negate()
            ->setPrettyName('davinci', false)
            ->guess();

        $this->assertEquals($output, $output2);
        
        $prefix1 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->setPrettyName('davinci')
            ->guess();
        $prefix2 = $this->open('monalisa.jpg')
            ->resize(100, 55)->negate()
            ->setPrettyName('davinci')
            ->guess();

        $this->assertContains('davinci', $prefix1);
        $this->assertContains('davinci', $prefix2);
        $this->assertNotEquals($prefix1, $prefix2);

        $transliterator = '\Behat\Transliterator\Transliterator';

        if (class_exists($transliterator)) {
            $nonLatinName1 = 'ダヴィンチ';
            $nonLatinOutput1 = $this->open('monalisa.jpg')
                ->resize(100, 50)->negate()
                ->setPrettyName($nonLatinName1, false)
                ->guess();

            $this->assertContains(
                $transliterator::urlize($transliterator::transliterate($nonLatinName1)),
                $nonLatinOutput1
            );

            $nonLatinName2 = 'давинчи';
            $nonLatinOutput2 = $this->open('monalisa.jpg')
                ->resize(100, 55)->negate()
                ->setPrettyName($nonLatinName2)
                ->guess();

            $this->assertContains(
                $transliterator::urlize($transliterator::transliterate($nonLatinName2)),
                $nonLatinOutput2
            );
        }
    }

    /**
     * Testing inlining
     */
    public function testInline()
    {
        $output = $this->open('monalisa.jpg')
            ->resize(20, 20)
            ->inline();

        $this->assertEquals(0, strpos($output, 'data:image/jpeg;base64,'));

        $data = base64_decode(substr($output, 23));
        $image = imagecreatefromstring($data);

        $this->assertEquals(20, imagesx($image));
        $this->assertEquals(20, imagesy($image));
    }

    /**
     * Asaserting that two colors are equals
     * (JPG compression is not preserving colors for instance, so we
     * need a non-strict way to compare it)
     */
    protected function assertColorEquals($c1, $c2, $delta = 8)
    {
        $c1 = ImageColor::parse($c1);
        $c2 = ImageColor::parse($c2);
        list($r1, $g1, $b1) = $this->toRGB($c1);
        list($r2, $g2, $b2) = $this->toRGB($c2);

        $this->assertLessThan($delta, abs($r1 - $r2));
        $this->assertLessThan($delta, abs($g1 - $g2));
        $this->assertLessThan($delta, abs($b1 - $b2));
    }

    protected function toRGB($color)
    {
        $b = $color&0xff;
        $g = ($color>>8)&0xff;
        $r = ($color>>16)&0xff;

        return array($r, $g, $b);
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
