<?php

use Gregwar\Cache\Cache;
use Gregwar\Cache\CacheInterface;
use Gregwar\Image\Image;
use Gregwar\Image\ImageColor;

/**
 * Unit testing for Image.
 */
class ImageTests extends \PHPUnit\Framework\TestCase
{
    /**
     * Testing the basic width & height.
     */
    public function testBasics(): void
    {
        $image = $this->open('monalisa.jpg');

        self::assertSame($image->width(), 771);
        self::assertSame($image->height(), 961);
    }

    /**
     * Testing the resize.
     */
    public function testResize(): void
    {
        $image = $this->open('monalisa.jpg');

        $out = $this->output('monalisa_small.jpg');
        $image
            ->resize(300, 200)
            ->save($out)
            ;

        self::assertFileExists($out);

        $i = imagecreatefromjpeg($out);
        self::assertSame(300, imagesx($i));
        self::assertSame(200, imagesy($i));
    }

    /**
     * Testing the resize %.
     */
    public function testResizePercent(): void
    {
        $image = $this->open('monalisa.jpg');

        $out = $this->output('monalisa_small.jpg');
        $image
            ->resize('50%')
            ->save($out)
            ;

        self::assertFileExists($out);

        $i = imagecreatefromjpeg($out);
        self::assertSame(386, imagesx($i));
        self::assertSame(481, imagesy($i));
    }

    /**
     * Testing to create an image, jpeg, gif and png.
     */
    public function testCreateImage(): void
    {
        $black = $this->output('black.jpg');

        Image::create(150, 200)
            ->fill('black')
            ->save($black, 100);

        $i = imagecreatefromjpeg($black);
        self::assertFileExists($black);
        self::assertSame(150, imagesx($i));
        self::assertSame(200, imagesy($i));

        $j = imagecolorat($i, 40, 40);
        self::assertSame(0, $j);

        $black = $this->output('black.png');
        Image::create(150, 200)
            ->fill('black')
            ->save($black, 'png');

        $i = imagecreatefrompng($black);
        self::assertFileExists($black);
        self::assertSame(150, imagesx($i));
        self::assertSame(200, imagesy($i));

        $black = $this->output('black.gif');
        Image::create(150, 200)
            ->fill('black')
            ->save($black, 'gif');

        $i = imagecreatefromgif($black);
        self::assertFileExists($black);
        self::assertSame(150, imagesx($i));
        self::assertSame(200, imagesy($i));
    }

    /**
     * Testing type guess.
     */
    public function testGuess(): void
    {
        $image = $this->open('monalisa.jpg');
        self::assertSame('jpeg', $image->guessType());
        $image = $this->open('monalisa.png');
        self::assertSame('png', $image->guessType());
        $image = $this->open('monalisa.gif');
        self::assertSame('gif', $image->guessType());
    }

    public function testDefaultCacheSystem(): void
    {
        $image = $this->open('monalisa.jpg');
        self::assertInstanceOf('Gregwar\Cache\Cache', $image->getCacheSystem());
    }

    public function testCustomCacheSystem(): void
    {
        $image = $this->open('monalisa.jpg');
        $cache = new Cache();
        $image->setCacheSystem($cache);
        self::assertInstanceOf(Gregwar\Cache\CacheInterface::class, $image->getCacheSystem());
    }

    /**
     * Testing that caching an image without operations will result
     * in the original image when force cache is disabled.
     */
    public function testNoCache(): void
    {
        $monalisa = __DIR__.'/files/monalisa.jpg';
        $image = $this->open('monalisa.jpg')->setForceCache(false);
        self::assertSame($monalisa, $image->guess());
        $image = $this->open('monalisa.jpg');
        self::assertNotSame($monalisa, $image->guess());
        $image = $this->open('monalisa.jpg')->setForceCache(true);
        self::assertNotSame($monalisa, $image->guess());
    }

    public function testActualCache(): void
    {
        $output = $this->open('monalisa.jpg')
            ->setCacheDir('/magic/path/to/cache/')
            ->resize(100, 50)->negate()
            ->guess();

        self::assertStringContainsString('/magic/path/to/cache', $output);
        $file = str_replace('/magic/path/to', __DIR__.'/output/', $output);
        self::assertFileExists($file);
    }

    public function testCacheData(): void
    {
        $output = $this->open('monalisa.jpg')
            ->resize(300, 200)
            ->cacheData();

        $jpg = imagecreatefromstring($output);
        self::assertSame(300, imagesx($jpg));
        self::assertSame(200, imagesy($jpg));
    }

    /**
     * Testing using cache.
     */
    public function testCache(): void
    {
        $output = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->guess();

        self::assertFileExists($output);
        $i = imagecreatefromjpeg($output);
        self::assertSame(100, imagesx($i));
        self::assertSame(50, imagesy($i));

        $output2 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->guess();

        self::assertSame($output, $output2);

        $output3 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->png();
        self::assertFileExists($output);
        $i = imagecreatefrompng($output3);
        self::assertSame(100, imagesx($i));
        self::assertSame(50, imagesy($i));

        $output4 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->gif();
        self::assertFileExists($output);
        $i = imagecreatefromgif($output4);
        self::assertSame(100, imagesx($i));
        self::assertSame(50, imagesy($i));
    }

    /**
     * Testing Gaussian blur filter.
     */
    public function testGaussianBlur(): void
    {
        $image = $this->open('monalisa.jpg')
            ->gaussianBlur();
        $secondImage = $this->open('monalisa.jpg')
            ->gaussianBlur(5);

        self::assertFileExists($image);
        self::assertFileExists($secondImage);
    }

    /**
     * Testing creating image from data.
     */
    public function testData(): void
    {
        $data = file_get_contents(__DIR__.'/files/monalisa.jpg');

        $output = $this->output('mona.jpg');
        $image = Image::fromData($data);
        $image->save($output);

        self::assertFileExists($output);
        $i = imagecreatefromjpeg($output);
        self::assertSame(771, imagesx($i));
        self::assertSame(961, imagesy($i));
    }

    /**
     * Opening an image.
     */
    protected function open(string $file): Image
    {
        $image = Image::open(__DIR__.'/files/'.$file);
        $image->setCacheDir(__DIR__.'/output/cache/');
        $image->setActualCacheDir(__DIR__.'/output/cache/');

        return $image;
    }

    /**
     * Testing transparent image.
     */
    public function testTransparent(): void
    {
        $gif = $this->output('transparent.gif');
        $i = Image::create(200, 100)
            ->fill('transparent')
            ->save($gif, 'gif');

        self::assertFileExists($gif);
        $img = imagecreatefromgif($gif);
        self::assertSame(200, imagesx($img));
        self::assertSame(100, imagesy($img));
        $index = imagecolorat($img, 2, 2);
        $color = imagecolorsforindex($img, $index);
        self::assertSame(127, $color['alpha']);
    }

    public function testNonExistingFile(): void
    {
        $jpg = $this->output('a.jpg');
        $img = $this->open('non_existing_file.jpg')
            ->negate();
        $error = $img->save($jpg);

        self::assertFileExists($error);
    }

    public function testNonExistingFileNoFallback(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        Image::open('non_existing_file.jpg')
            ->useFallback(false)
            ->save($this->output('a.jpg'));
    }

    /**
     * Test that image::save returns the file name.
     */
    public function testSaveReturn(): void
    {
        $red = $this->output('red.jpg');
        $result = Image::create(10, 10)
            ->fill('red')
            ->save($red);

        self::assertSame($red, $result);
    }

    /**
     * Testing merge.
     */
    public function testMerge(): void
    {
        $out = $this->output('merge.jpg');
        Image::create(100, 100)
            ->fill('red')
            ->merge(Image::create(50, 50)
                ->fill('black')
            )
            ->save($out);

        // Merge file exists
        self::assertFileExists($out);

        // Test that the upper left zone contain a black pixel, and the lower
        // down contains a red one
        $img = imagecreatefromjpeg($out);
        $this->assertColorEquals('black', imagecolorat($img, 5, 12));
        $this->assertColorEquals('red', imagecolorat($img, 55, 62));
    }

    /**
     * Test that dependencies are well generated.
     */
    public function testDependencies(): void
    {
        self::assertSame(array(), Image::create(100, 100)
            ->getDependencies());
        self::assertSame(array(), Image::create(100, 100)
            ->merge(Image::create(100, 50))
            ->getDependencies());

        self::assertSame(array('toto.jpg'), Image::open('toto.jpg')
            ->merge(Image::create(100, 50))
            ->getDependencies());

        self::assertSame(array('toto.jpg', 'titi.jpg'), Image::open('toto.jpg')
            ->merge(Image::open('titi.jpg'))
            ->getDependencies());

        self::assertSame(array('toto.jpg', 'titi.jpg', 'tata.jpg'), Image::open('toto.jpg')
            ->merge(Image::open('titi.jpg')
                    ->merge(Image::open('tata.jpg')))
            ->getDependencies());
    }

    /**
     * Test that pretty name (for referencing) is well respected.
     */
    public function testPrettyName(): void
    {
        $output = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->setPrettyName('davinci', false)
            ->guess();

        self::assertStringContainsString('davinci', $output);

        $output2 = $this->open('monalisa.jpg')
            ->resize(100, 55)->negate()
            ->setPrettyName('davinci', false)
            ->guess();

        self::assertSame($output, $output2);

        $prefix1 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->setPrettyName('davinci')
            ->guess();
        $prefix2 = $this->open('monalisa.jpg')
            ->resize(100, 55)->negate()
            ->setPrettyName('davinci')
            ->guess();

        self::assertStringContainsString('davinci', $prefix1);
        self::assertStringContainsString('davinci', $prefix2);
        self::assertNotSame($prefix1, $prefix2);

        $transliterator = '\Behat\Transliterator\Transliterator';

        if (class_exists($transliterator)) {
            $nonLatinName1 = 'ダヴィンチ';
            $nonLatinOutput1 = $this->open('monalisa.jpg')
                ->resize(100, 50)->negate()
                ->setPrettyName($nonLatinName1, false)
                ->guess();

            self::assertContains(
                $transliterator::urlize($transliterator::transliterate($nonLatinName1)),
                $nonLatinOutput1
            );

            $nonLatinName2 = 'давинчи';
            $nonLatinOutput2 = $this->open('monalisa.jpg')
                ->resize(100, 55)->negate()
                ->setPrettyName($nonLatinName2)
                ->guess();

            self::assertContains(
                $transliterator::urlize($transliterator::transliterate($nonLatinName2)),
                $nonLatinOutput2
            );
        }
    }

    /**
     * Testing inlining.
     */
    public function testInline(): void
    {
        $output = $this->open('monalisa.jpg')
            ->resize(20, 20)
            ->inline();

        self::assertSame(0, strpos($output, 'data:image/jpeg;base64,'));

        $data = base64_decode(substr($output, 23));
        $image = imagecreatefromstring($data);

        self::assertSame(20, imagesx($image));
        self::assertSame(20, imagesy($image));
    }

    /**
     * Testing that width() can be called after cache
     */
    public function testWidthPostCache(): void
    {
        $this->open('monalisa.jpg')
            ->resize(100, 50)->negate()
            ->png();
        
        $dummy2 = $this->open('monalisa.jpg')
            ->resize(100, 50)->negate();
        $png = $dummy2->png();

        $i = imagecreatefrompng($png);
        self::assertEquals(imagesx($i), $dummy2->width());
    }

    /**
     * Asserting that two colors are equals
     * (JPG compression is not preserving colors for instance, so we
     * need a non-strict way to compare it).
     */
    protected function assertColorEquals($c1, $c2, $delta = 8): void
    {
        $c1 = ImageColor::parse($c1);
        $c2 = ImageColor::parse($c2);
        list($r1, $g1, $b1) = $this->toRGB($c1);
        list($r2, $g2, $b2) = $this->toRGB($c2);

        self::assertLessThan($delta, abs($r1 - $r2));
        self::assertLessThan($delta, abs($g1 - $g2));
        self::assertLessThan($delta, abs($b1 - $b2));
    }

    protected function toRGB($color): array
    {
        $b = $color & 0xff;
        $g = ($color >> 8) & 0xff;
        $r = ($color >> 16) & 0xff;

        return array($r, $g, $b);
    }
    
    /**
     * Outputting an image to a file.
     */
    protected function output(string $file): string
    {
        return __DIR__.'/output/'.$file;
    }

    /**
     * Reinitialize the output dir.
     */
    public function setUp(): void
    {
        $dir = $this->output('');
        `rm -rf $dir`;
        if( !mkdir($dir) && !is_dir($dir) ){
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        if( !mkdir($concurrentDirectory = $this->output('cache')) && !is_dir($concurrentDirectory) ){
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }
}
