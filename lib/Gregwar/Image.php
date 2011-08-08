<?php
namespace Gregwar;

require_once ('ImageColor.php');

/**
 * Images handling class
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class Image
{
    /**
     * Direcory to use for file caching
     */
    protected $cacheDir = 'cache/images';

    /**
     * GD Ressource
     */
    protected $gd = null;

    /**
     * Transformations hash
     */
    protected $hash = null;

    /**
     * File
     */
    protected $file = null;

    /**
     * Dimensions for new resources
     */
    protected $width = null;
    protected $height = null;

    /**
     * Supported types
     */
    public static $types = array(
        'jpg'   => 'jpeg',
        'jpeg'  => 'jpeg',
        'png'   => 'png',
        'gif'   => 'gif',
    );

    public static $gdTypes = array(
        'jpeg'  => IMG_JPG,
        'gif'   => IMG_GIF,
        'png'   => IMG_PNG,
    );

    /**
     * Change the caching directory
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Operations array
     */
    protected $operations = array();

    public function __construct($originalFile = null, $width = null, $height = null)
    {
        $this->file = $originalFile;
        $this->width = $width;
        $this->height = $height;

        if (!(extension_loaded('gd') && function_exists('gd_info'))) {
            throw new \RuntimeException('You need to install GD PHP Extension to use this library');
        }
    }

    /**
     * Create and returns the absolute directory for a hash
     *
     * @param string $hash the hash
     *
     * @return string the full file name
     */
    public function generateFileFromhash($hash) {
        $directory = $this->cacheDir;

        if (!file_exists($directory))
            mkdir($directory); 

        for ($i=0; $i<5; $i++) {
            $c = $hash[$i];
            $directory .= '/'.$c;
            if (!file_exists($directory)) {
                mkdir($directory);
            }
        }

        return $directory.'/'.substr($hash,5);
    }

    /**
     * Defines the file only after instantiation
     *
     * @param string $originalFile the file path
     */
    public function fromFile($originalFile)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Tells if the image is correct
     */
    public function correct()
    {
        return (false !== @exif_imagetype($this->file));
    }

    /**
     * Guess the file type
     */
    protected function guessType()
    {
        $type = @exif_imagetype($this->file);

        if (false !== $type) {
            if ($type == IMAGETYPE_JPEG)
                return 'jpeg';
            if ($type == IMAGETYPE_GIF)
                return 'gif';
            if ($type == IMAGETYPE_PNG)
                return 'png';
        }

        $parts = explode('.', $this->file);
        $ext = strtolower($parts[count($parts)-1]);

        if (isset(self::$types[$ext]))
            return self::$types[$ext];

        return 'jpeg';
    }

    /**
     * Converts the image to true color
     */
    protected function convertToTrueColor()
    {
        if (!imageistruecolor($this->gd))
        {
            $w = imagesx($this->gd);
            $h = imagesy($this->gd);

            $img = imagecreatetruecolor($w, $h);
            imagecopy($img, $this->gd, 0, 0, 0, 0, $w, $h);

            $this->gd = $img;
        }
    }


    /**
     * Try to open the file
     */
    public function initGd()
    {
        if (null === $this->file) {
            $this->gd = imagecreatetruecolor($this->width, $this->height);
        } else {
            if (null === $this->gd) {
                $type = $this->guessType();

                if (!(imagetypes() & self::$gdTypes[$type]))
                    throw new \RuntimeException('Type '.$type.' is not supported by GD');

                if ($type == 'jpeg')
                    $this->openJpeg();

                if ($type == 'gif')
                    $this->openGif();

                if ($type == 'png')
                    $this->openPng();

                if (null === $this->gd) {
                    throw new \UnexpectedValueException('Unable to open file ('.$this->file.')');
                } else {
                    $this->convertToTrueColor();
                }
            }
        }

        if ($this->gd)
            imagesavealpha($this->gd, true);

        return $this;
    }

    /**
     * Try to open the file using jpeg
     *
     */
    public function openJpeg()
    {
        $this->gd = imagecreatefromjpeg($this->file);
    }

    /**
     * Try to open the file using gif
     */
    public function openGif()
    {
        $this->gd = imagecreatefromgif($this->file);
    }

    /**
     * Try to open the file using PNG
     */
    public function openPng()
    {
        $this->gd = imagecreatefrompng($this->file);
    }

    /**
     * Adds an operation
     */
    protected function addOperation($method, $args)
    {
        $this->operations[] = array($method, $args);
    }

    /**
     * Generic function
     */
    public function __call($func, $args)
    {
        $reflection = new \ReflectionClass(get_class($this));
        $methodName = '_'.$func;

        if ($reflection->hasMethod($methodName)) {
            $method = $reflection->getMethod($methodName);

            if ($method->getNumberOfRequiredParameters() > count($args))
                throw new \InvalidArgumentException('Not enough arguments given for '.$func);

            $this->addOperation($methodName, $args);

            return $this;
        }

        throw new \BadFunctionCallException('Invalid method: '.$func);
    }

    /**
     * Resizes the image. It will never be enlarged.
     *
     * @param int $w the width 
     * @param int $h the height
     * @param int $bg the background
     */
    protected function _resize($w = null, $h = null, $bg = 0xffffff, $force = false, $rescale = false, $crop = false)
    {
        $width = imagesx($this->gd);
        $height = imagesy($this->gd);
        $scale = 1.0;

        if ($h === null && preg_match('#^(.+)%$#mUsi', $w, $matches)) {
            $w = (int)($width * ((float)$matches[1]/100.0));
            $h = (int)($height * ((float)$matches[1]/100.0));
        }

        if (!$force || $crop) {
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
                if ($w!=null && $rescale)
                    $scale = max($scale,$height/$h);
                else
                    $scale = $height/$h;
                $new_height = $h;
            }
        }

        if (!$force || $w==null || $rescale)
            $new_width = (int)($width/$scale);
        if (!$force || $h==null || $rescale)
            $new_height = (int)($height/$scale);

        if ($w == null || $crop)
            $w = $new_width;
        if ($h == null || $crop)
            $h = $new_height;

        $n = imagecreatetruecolor($w, $h);

        if ($bg != 'transparent') {
            imagefill($n, 0, 0, $bg);
        } else {
            imagealphablending($n,false);
            $color = imagecolorallocatealpha($n, 0, 0, 0, 127);
            imagefill($n, 0, 0, $color);
            imagesavealpha($n,true);
        }
        imagecopyresampled($n, $this->gd, ($w-$new_width)/2, ($h-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($this->gd);
        $this->gd = $n;
    }

    /**
     * Resizes the image forcing the destination to have exactly the
     * given width and the height
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    protected function _forceResize($width = null, $height = null, $background = 0xffffff)
    {
        $this->_resize($width, $height, $background, true);
    }

    /**
     * Resizes the image preserving scale. Can enlarge it.
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */  
    protected function _scaleResize($width, $height, $background=0xffffff)
    {
        $this->_resize($width, $height, $background, false, true);
    }

    /**
     * Works as resize() excepts that the layout will be cropped
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    protected function _cropResize($width, $height, $background=0xffffff)
    {
        $this->_resize($width, $height, $background, false, false, true);
    }

    /**
     * Crops the image 
     *
     * @param int $x the top-left x position of the crop box
     * @param int $y the top-left y position of the crop box
     * @param int $w the width of the crop box
     * @param int $h the height of the crop box
     */
    public function _crop($x, $y, $w, $h) {
        $dst = imagecreatetruecolor($w, $h);
        imagecopy($dst, $this->gd, 0, 0, $x, $y, imagesx($this->gd), imagesy($this->gd));
        imagedestroy($this->gd);
        $this->gd = $dst;
    }

    /**
     * Negates the image
     */
    public function _negate()
    {
        imagefilter($this->gd, IMG_FILTER_NEGATE);
    }

    /**
     * Changes the brightness of the image
     *
     * @param int $brightness the brightness
     */
    protected function _brightness($b)
    {
        imagefilter($this->gd, IMG_FILTER_BRIGHTNESS, $b);
    }

    /**
     * Contrasts the image
     *
     * @param int $c the contrast
     */
    protected function _contrast($c)
    {
        imagefilter($this->gd, IMG_FILTER_CONTRAST, $c);
    }

    /**
     * Apply a grayscale level effect on the image
     */
    protected function _grayscale()
    {
        imagefilter($this->gd, IMG_FILTER_GRAYSCALE);
    }

    /**
     * Emboss the image
     */
    protected function _emboss()
    {
        imagefilter($this->gd, IMG_FILTER_EMBOSS);
    }

    /**
     * Smooth the image
     */
    protected function _smooth($p)
    {
        imagefilter($this->gd, IMG_FILTER_SMOOTH, $p);
    }

    /**
     * Sharps the image
     */
    protected function _sharp()
    {
        imagefilter($this->gd, IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * Edges the image
     */
    protected function _edge()
    {
        imagefilter($this->gd, IMG_FILTER_EDGEDETECT);
    }

    /**
     * Colorize the image
     */
    protected function _colorize($red, $green, $blue)
    {
        imagefilter($this->gd, IMG_FILTER_COLORIZE, $red, $green, $blue);
    }

    /**
     * Sepias the image
     */
    protected function _sepia()
    {
        imagefilter($this->gd, IMG_FILTER_GRAYSCALE);
        imagefilter($this->gd, IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Merge with another image
     */
    protected function _merge(Image $other, $x = 0, $y = 0, $w = null, $h = null)
    {
        $other = clone $other;
        $other->initGd();
        $other->applyOperations();

        imagealphablending($this->gd, true);

        if (null == $w)
            $w = $other->width();

        if (null == $h)
            $h = $other->height();

        imagecopyresampled($this->gd, $other->gd, $x, $y, 0, 0, $w, $h, $w, $h);
    }

    /**
     * Rotate the image 
     */
    protected function _rotate($angle, $background = 0xffffff)
    {
        $this->gd = imagerotate($this->gd, $angle, $background);
    }

    /**
     * Fills the image
     */
    protected function _fill($color = 0xffffff, $x = 0, $y = 0)
    {
        imagealphablending($this->gd, false);

        imagefilledrectangle($this->gd, $x, $y, imagesx($this->gd), imagesy($this->gd), ImageColor::parse($color));
    }

    /**
     * Writes some text
     */
    protected function _write($font, $text, $x = 0, $y = 0, $size = 12, $angle = 0, $color = 0x000000, $pos = 'left')
    {
        imagealphablending($this->gd, true);

        if ($pos != 'left') {
            $sim_size = self::TTFBox($font, $text, $size, $angle);

            if ($pos == 'center') {
                $x -= $sim_size['width'] / 2;
            } 
            if ($pos == 'right') {
                $x -= $sim_size['width'];
            }
        }
        imagettftext($this->gd, $size, $angle, $x, $y, ImageColor::parse($color), $font, $text);
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

    /**
     * Draws a rectangle
     */
    protected function _rectangle($color, $x1, $y1, $x2, $y2, $filled = false)
    {
        if ($filled) {
            imagefilledrectangle($this->gd, $x1, $y1, $x2, $y2, ImageColor::parse($color));
        } else {
            imagerectangle($this->gd, $x1, $y1, $x2, $y2, ImageColor::parse($color));
        }
    }

    /**
     * Draws a line
     */
    protected function _line($x1, $y1, $x2, $y2, $color = 0x000000) 
    {
        imageline($this->gd, $x1, $y1, $c2, $y2, ImageColor::parse($color));
    }

    /**
     * Draws an ellipse
     */
    protected function _ellipse($cx, $cy, $width, $height, $color = 0x000000, $filled = false)
    {
        if ($filled)
            imagefilledellipse($this->gd, $cx, $cy, $width, $height, ImageColor::parse($color));
        else
            imageellipse($this->gd, $cx, $cy, $width, $height, ImageColor::parse($color));
    }

    /**
     * Draws a circle
     */
    protected function _circle($cx, $cy, $r, $color = 0x000000, $filled)
    {
        $this->_ellipse($cx, $cy, $r, $r, ImageColor::parse($color), $filled);
    }


    /**
     * Serialization of operations
     */
    public function serializeOperations()
    {
        $datas = array();

        foreach ($this->operations as $operation) {
            $method = $operation[0];
            $args = $operation[1];
            foreach ($args as &$arg) {
                if ($arg instanceof Image) {
                    $arg = $arg->getHash();
                }
            }
            $datas[] = array($method, $args);
        }

        return serialize($datas);
    }

    /**
     * Generates the hash
     */
    public function generateHash($type = 'jpeg', $quality = 80) 
    {
        $datas = array(
            $this->file,
            filectime($this->file),
            $this->serializeOperations(),
            $type,
            $quality
        );

        $this->hash = sha1(serialize($datas));
    }

    /**
     * Gets the hash
     */
    public function getHash($type = 'jpeg', $quality = 80)
    {
        if (null === $this->hash)
            $this->generateHash();

        return $this->hash;
    }

    /**
     * Gets the cache file name and generate it if it does not exists.
     * Note that if it exists, all the image computation process will
     * not be done.
     */
    public function cacheFile($type = 'jpg', $quality = 80)
    {
        if (!count($this->operations) && $type == $this->guessType())
            return $this->getFilename($this->file);

        // Computes the hash
        $this->hash = $this->getHash($type, $quality);

        // Generates the cache file
        $file = $this->generateFileFromHash($this->hash.'.'.$type);

        // If the files does not exists, save it
        if (!file_exists($file)) {
            $this->save($file, $type, $quality);
        }

        return $this->getFilename($file);
    }

    /**
     * Hook to helps to extends and enhance this c lass
     */
    protected function getFilename($filename)
    {
        return $filename;
    }

    /**
     * Generates and output a jpeg cached file
     */
    public function jpeg($quality = 80)
    {
        return $this->cacheFile('jpg', $quality);
    }

    /**
     * Generates and output a gif cached file
     */
    public function gif()
    {
        return $this->cacheFile('gif');
    }

    /**
     * Generates and output a png cached file
     */
    public function png()
    {
        return $this->cacheFile('png');
    }

    /**
     * Applies the operations
     */
    public function applyOperations()
    {
        // Renders the effects
        foreach ($this->operations as $operation) {
            call_user_func_array(array($this, $operation[0]), $operation[1]);
        }
    }

    /**
     * Save the file to a given output
     */
    public function save($file, $type = 'jpeg', $quality = 80)
    {
        if (is_int($type)) {
            $quality = $type;
            $type = 'jpeg';
        }

        if (!isset(self::$types[$type]))
            throw new \InvalidArgumentException('Given type ('.$type.') is not valid');

        $type = self::$types[$type];

        $this->initGd();

        $this->applyOperations();

        $success = false;

        if (null == $file)
            ob_start();

        if ($type == 'jpeg') 
            $success = imagejpeg($this->gd, $file, $quality);

        if ($type == 'gif')
            $success = imagegif($this->gd, $file);

        if ($type == 'png')
            $success = imagepng($this->gd, $file);

        if (!$success)
            return false;

        return (null === $file ? ob_get_clean() : $file);
    }

    /**
     * Get the contents of the image
     */
    public function get($type = 'jpeg', $quality = 80)
    {
        return $this->save(null, $type, $quality);
    }

    /* Image API */

    /**
     * Gets the width
     */
    public function width()
    {
        if (null === $this->gd)
            $this->initGd();

        return imagesx($this->gd);
    }

    /**
     * Gets the height
     */
    public function height()
    {
        if (null === $this->gd)
            $this->initGd();

        return imagesy($this->gd);
    }

    /**
     * Tostring defaults to jpeg
     */
    public function __toString()
    {
        return $this->jpeg();
    }

    /**
     * Creates an instance, usefull for one-line chaining
     */
    public static function open($file = '')
    {
        return new Image($file);
    }

    /**
     * Creates an instance of a new resource
     */
    public static function create($width, $height)
    {
        return new Image(null, $width, $height);
    }
}

