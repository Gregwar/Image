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
     * User-defined resource
     */
    protected $resource = null;

    /**
     * Type name
     */
    protected $type = 'jpeg';

    /**
     * Transformations hash
     */
    protected $hash = null;

    /**
     * File
     */
    protected $file = null;

    /**
     * Image data
     */
    protected $data = null;

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
        $this->file = $originalFile;
        $this->width = $width;
        $this->height = $height;

        if ($originalFile !== null) {
            $this->type = $this->guessType();
        }
    }

    /**
     * Sets the image data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Sets the resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
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

        $this->adapter->setData($this->data);
        $this->adapter->setResource($this->resource);
        $this->adapter->setFile($this->file);
        $this->adapter->setType($this->type);
        $this->adapter->setDimensions($this->width, $this->height);
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
        return $this->file;
    }

    /**
     * Defines the file only after instantiation
     *
     * @param string $originalFile the file path
     */
    public function fromFile($originalFile)
    {
        $this->file = $originalFile;

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
    public function guessType()
    {
        if (function_exists('exif_imagetype')) {
            $type = @exif_imagetype($this->file);

            if (false !== $type) {
                if ($type == IMAGETYPE_JPEG) {
                    return 'jpeg';
                }

                if ($type == IMAGETYPE_GIF) {
                    return 'gif';
                }

                if ($type == IMAGETYPE_PNG) {
                    return 'png';
                }
            }
        }

        $parts = explode('.', $this->file);
        $ext = strtolower($parts[count($parts)-1]);

        if (isset(self::$types[$ext])) {
            return self::$types[$ext];
        }

        return 'jpeg';
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
    public function generateHash($type = 'guess', $quality = 80)
    {
        $inputInfos = 0;

        if ($this->file) {
            try {
                $inputInfos = filectime($this->file);
            } catch (\Exception $e) {
            }
        } else {
            $inputInfos = array($this->width, $this->height);
        }

        $datas = array(
            $this->file,
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
            $type = $this->type;
        }

        if (!count($this->operations) && $type == $this->guessType()) {
            return $this->getFilename($this->file);
        }

        // Computes the hash
        $this->hash = $this->getHash($type, $quality);

        // Generates the cache file
        list($actualFile, $file) = $this->generateFileFromHash($this->hash.'.'.$type);

        // If the files does not exists, save it
        if (!file_exists($actualFile)) {
            $this->save($actualFile, $type, $quality);
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
            $type = $this->type;
        }

        if (!isset(self::$types[$type])) {
            throw new \InvalidArgumentException('Given type ('.$type.') is not valid');
        }

        $type = self::$types[$type];

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
        return new Image($file);
    }

    /**
     * Creates an instance of a new resource
     */
    public static function create($width, $height)
    {
        return new Image(null, $width, $height);
    }

    /**
     * Creates an instance of image from its data
     */
    public static function fromData($data)
    {
        $image = new Image();
        $image->setData($data);

        return $image;
    }

    /**
     * Creates an instance of image from resource
     */
    public static function fromResource($resource)
    {
        $image = new Image();
        $image->setResource($resource);

        return $image;
    }
}
