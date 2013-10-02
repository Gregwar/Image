<?php

namespace Gregwar\Image;

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
     * The actual cache dir
     */
    protected $actualCacheDir = null;

    /**
     * Internal adapter
     */
    protected $adapter = null;

    /**
     * Pretty name for the image
     */
    protected $prettyName = '';

    /**
     * Transformations hash
     */
    protected $hash = null;

    /**
     * The image source
     */
    protected $source = null;

    /**
     * Supported types
     */
    public static $types = array(
        'jpg'   => 'jpeg',
        'jpeg'  => 'jpeg',
        'png'   => 'png',
        'gif'   => 'gif',
    );
    
    /**
     * Fallback image
     */
    protected $fallback;

    /**
     * Use fallback image
     */
    protected $useFallbackImage = true;

    /**
     * Change the caching directory
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * The actual cache dir
     */
    public function setActualCacheDir($actualCacheDir)
    {
        $this->actualCacheDir = $actualCacheDir;

        return $this;
    }

    /**
     * Sets the pretty name of the image
     */
    public function setPrettyName($name)
    {
        $name = strtolower($name);
        $name = str_replace(' ', '-', $name);
        $this->prettyName = preg_replace('/([^a-z0-9\-]+)/m', '', $name);

        return $this;
    }

    /**
     * Operations array
     */
    protected $operations = array();

    public function __construct($originalFile = null, $width = null, $height = null)
    {
        $this->setFallback(null);

        if ($originalFile) {
            $this->source = new Source\File($originalFile);
        } else {
            $this->source = new Source\Create($width, $height);
        }
    }

    /**
     * Sets the image data
     */
    public function setData($data)
    {
        $this->source = new Source\Data($data);
    }

    /**
     * Sets the resource
     */
    public function setResource($resource)
    {
        $this->source = new Source\Resource($resource);
    }

    /**
     * Use the fallback image or not
     */
    public function useFallback($useFallbackImage = true)
    {
        $this->useFallbackImage = $useFallbackImage;

        return $this;
    }

    /**
     * Sets the fallback image to use
     */
    public function setFallback($fallback = null)
    {
        if ($fallback === null) {
            $this->fallback = __DIR__ . '/images/error.jpg';
        } else {
            $this->fallback = $fallback;
        }

        return $this;
    }

    /**
     * Gets the fallack image path
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Gets the fallback into the cache dir
     */
    public function getCacheFallback()
    {
        $file = ($this->actualCacheDir ?: $this->cacheDir) . '/fallback.jpg';

        if (!file_exists($file)) {
            file_put_contents($file, file_get_contents($this->fallback));
        }

        return $this->cacheDir . '/fallback.jpg';
    }

    public function getAdapter()
    {
        if (null === $this->adapter) {
            // Defaults to GD
            $this->setAdapter('gd');
        }

        return $this->adapter;
    }

    public function setAdapter($adapter)
    {
        if ($adapter instanceof Adapter\Adapter) {
            $this->adapter = $adapter;
        } else {
            if (is_string($adapter)) {
                $adapter = strtolower($adapter);

                switch ($adapter) {
                case 'gd':
                    $this->adapter = new Adapter\GD;
                    break;
                case 'imagemagick':
                case 'imagick':
                    $this->adapter = new Adapter\Imagick;
                    break;
                default:
                    throw new \Exception('Unknown adapter: '.$adapter);
                    break;
                }
            } else {
                throw new \Exception('Unable to load the given adapter (not string or Adapter)');
            }
        }

        $this->adapter->setSource($this->source);
    }

    /**
     * Create and returns the absolute directory for a hash
     *
     * @param string $hash the hash
     *
     * @return string the full file name
     */
    public function generateFileFromHash($hash)
    {
        $directory = $this->cacheDir;

        if ($this->actualCacheDir === null) {
            $actualDirectory = $directory;
        } else {
            $actualDirectory = $this->actualCacheDir;
        }

        for ($i=0; $i<5; $i++) {
            $c = $hash[$i];
            $directory .= '/' . $c;
            $actualDirectory .= '/' . $c;
        }

        $endName = substr($hash, 5);

        if ($this->prettyName) {
            $endName = $this->prettyName . '-' . $endName;
        }

        $file = $directory . '/' . $endName;
        $actualFile = $actualDirectory . '/' . $endName;

        return array($actualFile, $file);
    }

    /**
     * Get the file path
     *
     * @return mixed a string with the filen name, null if the image
     *         does not depends on a file
     */
    public function getFilePath()
    {
        if ($this->source instanceof Source\File) {
            return $this->source->getFile();
        } else {
            return null;
        }
    }

    /**
     * Defines the file only after instantiation
     *
     * @param string $originalFile the file path
     */
    public function fromFile($originalFile)
    {
        $this->source = new Source\File($originalFile);

        return $this;
    }

    /**
     * Tells if the image is correct
     */
    public function correct()
    {
        return $thi->source->correct();
    }

    /**
     * Guess the file type
     */
    public function guessType()
    {
        return $this->source->guessType();
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
    public function __call($methodName, $args)
    {
        $adapter = $this->getAdapter();
        $reflection = new \ReflectionClass(get_class($adapter));

        if ($reflection->hasMethod($methodName)) {
            $method = $reflection->getMethod($methodName);

            if ($method->getNumberOfRequiredParameters() > count($args)) {
                throw new \InvalidArgumentException('Not enough arguments given for '.$func);
            }

            $this->addOperation($methodName, $args);

            return $this;
        }

        throw new \BadFunctionCallException('Invalid method: '.$func);
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
                if ($arg instanceof self) {
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
    public function generateHash($type = 'guess', $quality = 80)
    {
        $inputInfos = 0;

        $inputInfos = $this->source->getInfos();

        $datas = array(
            $inputInfos,
            $this->serializeOperations(),
            $type,
            $quality
        );

        $this->hash = sha1(serialize($datas));
    }

    /**
     * Gets the hash
     */
    public function getHash($type = 'guess', $quality = 80)
    {
        if (null === $this->hash) {
            $this->generateHash();
        }

        return $this->hash;
    }

    /**
     * Gets the cache file name and generate it if it does not exists.
     * Note that if it exists, all the image computation process will
     * not be done.
     */
    public function cacheFile($type = 'jpg', $quality = 80)
    {
        if ($type == 'guess') {
            $type = $this->guessType();
        }

        if (!count($this->operations) && $type == $this->guessType()) {
            return $this->getFilename($this->getFilePath());
        }

        // Computes the hash
        $this->hash = $this->getHash($type, $quality);

        // Generates the cache file
        list($actualFile, $file) = $this->generateFileFromHash($this->hash.'.'.$type);

        // If the files does not exists, save it
        if (!file_exists($actualFile)) {
            $file = $this->save($actualFile, $type, $quality);
        }

        return $this->getFilename($file);
    }

    /**
     * Hook to helps to extends and enhance this class
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
     * Generates and output an image using the same type as input
     */
    public function guess($quality = 80)
    {
        return $this->cacheFile('guess', $quality);
    }

    /**
     * Applies the operations
     */
    public function applyOperations()
    {
        // Renders the effects
        foreach ($this->operations as $operation) {
            call_user_func_array(array($this->adapter, $operation[0]), $operation[1]);
        }
    }

    /**
     * Initialize the adapter
     */
    public function init()
    {
        $this->getAdapter()->init();
    }

    /**
     * Save the file to a given output
     */
    public function save($file, $type = 'guess', $quality = 80)
    {
        if ($file) {
            $directory = dirname($file);

            if (!is_dir($directory)) {
                @mkdir($directory, 0777, true);
            }
        }

        if (is_int($type)) {
            $quality = $type;
            $type = 'jpeg';
        }

        if ($type == 'guess') {
            $type = $this->guessType();
        }

        if (!isset(self::$types[$type])) {
            throw new \InvalidArgumentException('Given type ('.$type.') is not valid');
        }

        $type = self::$types[$type];

        try {
            $this->init();
            $this->applyOperations();

            $success = false;

            if (null == $file) {
                ob_start();
            }

            if ($type == 'jpeg') {
                $this->getAdapter()->saveJpeg($file, $quality);
            }

            if ($type == 'gif') {
                $this->getAdapter()->saveGif($file);
            }

            if ($type == 'png') {
                $this->getAdapter()->savePng($file);
            }

            if (!$success) {
                return false;
            }

            return (null === $file ? ob_get_clean() : $file);

        } catch (\Exception $e) {
            if ($this->useFallbackImage) {
                return (null === $file ? file_get_contents($this->fallback) : $this->getCacheFallback());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get the contents of the image
     */
    public function get($type = 'guess', $quality = 80)
    {
        return $this->save(null, $type, $quality);
    }

    /* Image API */

    /**
     * Image width
     */
    public function width()
    {
        return $this->getAdapter()->width();
    }

    /**
     * Image height
     */
    public function height()
    {
        return $this->getAdapter()->height();
    }

    /**
     * Tostring defaults to jpeg
     */
    public function __toString()
    {
        return $this->guess();
    }

    /**
     * Returning basic html code for this image
     */
    public function html($title = '')
    {
        return '<img title="' . $title . '" src="' . $this->jpeg() . '" />';
    }

    /**
     * Creates an instance, usefull for one-line chaining
     */
    public static function open($file = '')
    {
        return new self($file);
    }

    /**
     * Creates an instance of a new resource
     */
    public static function create($width, $height)
    {
        return new self(null, $width, $height);
    }

    /**
     * Creates an instance of image from its data
     */
    public static function fromData($data)
    {
        $image = new self();
        $image->setData($data);

        return $image;
    }

    /**
     * Creates an instance of image from resource
     */
    public static function fromResource($resource)
    {
        $image = new self();
        $image->setResource($resource);

        return $image;
    }
}
