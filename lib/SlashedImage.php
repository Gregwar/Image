<?php

namespace Slashed;


/**
 * Class for manipulation of images
 *
 * @author Grégoire Passault <g.passault@alienor.net>
 */
class Image
{
    /**
     * Direcory to use for file caching
     */
    private $cacheDir = 'cache/images';

    /**
     * GD Ressource
     */
    private $gd = null;

    /**
     * Transformations hash
     */
    private $hash = '';

    /**
     * File
     */
    private $file = '';

    /**
     * Supported types
     */
    public static $types = array(
        'jpg'   => 'jpg',
        'jpeg'  => 'jpg',
        'png'   => 'png',
        'gif'   => 'gif'
    );

    /**
     * Operations array
     */
    private $operations = array();

    public function __construct($originalFile = '')
    {
        $this->file = $originalFile;
    }

    /**
     * Create and returns the absolute directory for a file
     *
     * @param string $file the file name
     *
     * @return string the full file name
     */
    public function file($file) {
        $directory = $this->cacheDir;

        if (!file_exists($directory))
            mkdir($directory); 

        for ($i=0; $i<5; $i++) {
            $c = $file[$i];
            $directory.='/'.$c;
            if (!file_exists($directory)) {
                mkdir($directory);
            }
        }

        $file = $directory.'/'.substr($file,5);
        return $file;
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
     * Guess the file type
     */
    private function guessType()
    {
        $parts = explode('.', $this->file);
        $ext = strtolower($parts[count($parts)-1]);

        if (isset(Image::$types[$ext]))
            return Image::$types[$ext];

        return 'jpeg';
    }

    /**
     * Try to open the file
     */
    public function openFile()
    {
        $type = $this->guessType();

        if ($type == 'jpeg')
            $this->openJpeg();

        if ($type == 'gif')
            $this->openGif();

        if ($type == 'png')
            $this->openPng();

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

            $this->operations[] = array($methodName, $args);

            return $this;
        }

        throw new \Exception('Invalid method: '.$func);
    }

    /**
     * Resizes the image. It will never be enlarged.
     *
     * @param int $w the width 
     * @param int $h the height
     * @param int $bg the background
     */
    private function _resize($w = null, $h = null, $bg = 0xffffff, $force = false, $rescale = false, $crop = false)
    {
        $width = imagesx($this->gd);
        $height = imagesy($this->gd);
        $scale = 1.0;
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

        if ($bg!='transparent') {
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
    private function _forceResize($w=null,$h=null,$bg=0xffffff)
    {
        $this->_resize($w, $h, $bg, true);
    }

    /**
     * Resizes the image preserving scale. Can enlarge it.
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */  
    private function _scaleResize($width, $height, $background=0xffffff)
    {
        $this->_resize($w, $h, $bg, false, true);
    }

    /**
     * Works as resize() excepts that the layout will be cropped
     *
     * @param int $w the width
     * @param int $h the height
     * @param int $bg the background
     */
    private function _cropResize($width, $height, $background=0xffffff)
    {
        $this->_resize($w, $h, $bg, false, false, true);
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
    private function _brightness($b)
    {
        imagefilter($this->gd, IMG_FILTER_BRIGHTNESS, $b);
    }

    /**
     * Contrasts the image
     *
     * @param int $c the contrast
     */
    private function _contrast($c)
    {
        imagefilter($this->gd, IMG_FILTER_CONTRAST, $c);
    }

    /**
     * Apply a grey level effect on the image
     */
    private function _grey()
    {
        imagefilter($this->gd, IMG_FILTER_GRAYSCALE);
    }

    /**
     * Emboss the image
     */
    public function _emboss()
    {
        imagefilter($this->gd, IMG_FILTER_EMBOSS);
    }

    /**
     * Smooth the image
     */
    public function _smooth($p)
    {
        imagefilter($this->gd, IMG_FILTER_SMOOTH, $p);
    }

    /**
     * Sharps the image
     */
    public function _sharp()
    {
        imagefilter($this->gd, IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * Edges the image
     */
    public function _edge()
    {
        imagefilter($this->gd, IMG_FILTER_EDGEDETECT);
    }

    /**
     * Colorize the image
     */
    public function _colorize($red, $green, $blue)
    {
        imagefilter($this->gd, IMG_FILTER_COLORIZE, $red, $green, $blue);
    }

    /**
     * Sepias the image
     */
    public function _sepia()
    {
        imagefilter($this->gd, IMG_FILTER_GRAYSCALE);
        imagefilter($this->gd, IMG_FILTER_COLORIZE, 100, 50, 0);
    }

    /**
     * Gets the cache file name and generate it if it does not exists.
     * Note that if it exists, all the image computation process will
     * not be done.
     */
    public function cacheFile($type = 'jpg', $quality = 80)
    {
        $datas = array(
            $this->file,
            filectime($this->file),
            serialize($this->operations),
            $type,
            $quality
        );

        // Computes the hash
        $this->hash = sha1(implode(' ', $datas));

        // Generates the cache file
        $file = $this->file($this->hash.'.'.$type);

        // If the files does not exists, save it
        if (!file_exists($file)) {
            $this->save($file, $type, $quality);
        }

        return $file;
    }

    /**
     * Generates and output a jpeg cached file
     */
    public function jpeg($quality = 80)
    {
        return $this->cacheFile("jpg", $quality);
    }

    /**
     * Generates and output a gif cached file
     */
    public function gif()
    {
        return $this->cacheFile("gif");
    }

    /**
     * Generates and output a png cached file
     */
    public function png()
    {
        return $this->cacheFile('png');
    }

    /**
     * Save the file to a given output
     */
    public function save($file, $type = 'jpg', $quality = 80)
    {
        if (!in_array($type, Image::$types))
            throw new \InvalidArgumentException('Given type ('.$type.') is not valid');

        $type = Image::$types[$type];

        $this->openFile();

        // Renders the effects
        foreach ($this->operations as $operation) {
            call_user_func_array(array($this, $operation[0]), $operation[1]);
        }
        $success = false;

        if ($type == 'jpg') 
            $success = imagejpeg($this->gd, $file, $quality);

        if ($type == 'gif')
            $success = imagegif($this->gd, $file);

        if ($type == 'png')
            $success = imagepng($this->gd, $file);

        if (!$success)
            return false;

        return $file;
    }

    /**
     * Create an instance, usefull for one-line chaining
     */
    public static function create($file = '')
    {
        return new Image($file);
    }
}

