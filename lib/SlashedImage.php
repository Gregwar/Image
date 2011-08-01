<?php

class Image
{
  public static $cacheDir = "cache/images";
  public $gd;
  private $hash;
  private $file;
  private $operations;

  public function __construct($file="") {
    $this->gd = null;
    $this->hash = "";
    $this->operations = array();
    $this->file=$file;
  }

  public static function file($file) {
    $directory = Image::$cacheDir;
    if (!file_exists($directory))
      mkdir($directory); 
    for ($i=0; $i<5; $i++) {
      $c = $file[$i];
      $directory.="/$c";
      if (!file_exists($directory)) {
    mkdir($directory);
      }
    }
    $file = $directory."/".substr($file,5);
    return $file;
  }

  public function fromFile($file) {
    $this->file = $file;
    return $this;
  }

  public function openFile() {
    $file = $this->file;
    if (!$this->fromJpeg($file))
      if (!$this->fromGif($file))
    $this->fromPng($file);
    return $this;
  }

  public function fromJpeg($file) {
    $this->file = $file;
    $this->gd = @imagecreatefromjpeg($file);
    return ($this->gd !== false);
  }

  public function fromGif($file) {
    $this->file = $file;
    $this->gd = @imagecreatefromgif($file);
    return ($this->gd !== false);
  }

  public function fromPng($file) {
    $this->file = $file;
    $this->gd = @imagecreatefrompng($file);
    return ($this->gd !== false);
  }

  public function resize($w, $h, $bg=0xFFFFFF) {
    $this->operations[] = array("resize",$w,$h,$bg);
    return $this;
  }

  public function forceResize($w, $h, $bg=0xFFFFFF) {
    $this->operations[] = array("forceResize",$w,$h,$bg);
    return $this;
  }

  public function scaleResize($w, $h, $bg=0xFFFFFF) {
    $this->operations[] = array("scaleResize",$w,$h,$bg);
    return $this;
  }

  public function cropResize($w, $h, $bg=0xFFFFFF) {
    $this->operations[] = array("cropResize",$w,$h,$bg);
    return $this;
  }

  public function crop($x, $y, $w, $h) {
    $this->operations[] = array("crop",$x,$y,$w,$h);
    return $this;
  }

  public function stamp() {
    $this->operations[] = array("stamp");
    return $this;
  }

  public function negate() {
    $this->operations[] = array("negate");
    return $this;
  }

  public function brightness($b) {
    $this->operations[] = array("brightness", $b);
    return $this;
  }

  public function smooth($p) {
    $this->operations[] = array("smooth", $p);
    return $this;
  }

  public function sharp($p) {
    $this->operations[] = array("sharp");
    return $this;
  }

  public function contrast($c) {
    $this->operations[] = array("contrast", $c);
    return $this;
  }

  public function grey() {
    $this->operations[] = array("grey");
    return $this;
  }

  public function emboss() {
    $this->operations[] = array("emboss");
    return $this;
  }

  public function edge() {
    $this->operations[] = array("edge");
    return $this;
  }

  public function colorize($r, $g, $b) {
    $this->operations[] = array("colorize", $r, $g, $b);
    return $this;  
  }

  public function sepia() {
    $this->operations[] = array("sepia");
    return $this;
  }

  public function _resize($w=null,$h=null,$bg,$force=false,$rescale=false,$crop=false) {
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

    if ($bg!="transparent") {
      imagefill($n, 0, 0, $bg);
    } else {
      imagealphablending($n,false);
      $color = imagecolorallocatealpha($n, 0, 0, 0, 127);
      imagefill($n,0,0,$color);
      imagesavealpha($n,true);
    }
    imagecopyresampled($n, $this->gd, ($w-$new_width)/2, ($h-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height);
    imagedestroy($this->gd);
    $this->gd = $n;
  }

  public function _crop($x, $y, $w, $h) {
    $dst = imagecreatetruecolor($w, $h);
    imagecopy($dst, $this->gd, 0, 0, $x, $y, imagesx($this->gd), imagesy($this->gd));
    imagedestroy($this->gd);
    $this->gd = $dst;
  }

  public function _negate() {
    imagefilter($this->gd, IMG_FILTER_NEGATE);
  }

  public function _brightness($b) {
    imagefilter($this->gd, IMG_FILTER_BRIGHTNESS, $b);
  }

  public function _contrast($c) {
    imagefilter($this->gd, IMG_FILTER_CONTRAST, $c);
  }

  public function _grey() {
    imagefilter($this->gd, IMG_FILTER_GRAYSCALE);
  }

  public function _emboss() {
    imagefilter($this->gd, IMG_FILTER_EMBOSS);
  }

  public function _smooth($p) {
    imagefilter($this->gd, IMG_FILTER_SMOOTH, $p);
  }

  public function _sharp($p) {
    imagefilter($this->gd, IMG_FILTER_MEAN_REMOVAL);
  }

  public function _edge() {
    imagefilter($this->gd, IMG_FILTER_EDGEDETECT);
  }

  public function _colorize($r, $g, $b) {
    imagefilter($this->gd, IMG_FILTER_COLORIZE, $r, $g, $b);
  }

  public function _stamp() {
    seezat_stamp($this->gd);
  }

  public function _sepia() {
    imagefilter($this->gd, IMG_FILTER_GRAYSCALE);
    imagefilter($this->gd, IMG_FILTER_COLORIZE, 100, 50, 0);
  }

  public function cacheFile($type = "jpg", $quality = 80) {
    $hash=      $this->file." ";
    $hash.=     filectime($this->file)." ";
    $hash.= serialize($this->operations)." ".$type." ".$quality;
    $this->hash = sha1($hash);
    $file = Image::file($this->hash.".".$type);
    if (!file_exists($file)) {
      $this->save($file, $type, $quality);
    }
    return $file;
  }

  public function save($file, $type="jpg", $quality = 80) {
    $this->openFile();

    foreach ($this->operations as $o) {
      switch ($o[0]) {
      case "resize":
    $this->_resize($o[1],$o[2],$o[3]);
    break;
      case "forceResize":
    $this->_resize($o[1],$o[2],$o[3], true);
    break;
      case "scaleResize":
    $this->_resize($o[1],$o[2],$o[3], true, true);
    break;
      case "cropResize":
    $this->_resize($o[1],$o[2],$o[3], true, true, true);
    break;
      case "crop":
    $this->_crop($o[1],$o[2],$o[3],$o[4]);
    break;
      case "stamp":
    $this->_stamp();
    break;
      case "negate":
    $this->_negate();
    break;
      case "brightness":
    $this->_brightness($o[1]);
    break;
      case "contrast":
    $this->_contrast($o[1]);
    break;
      case "grey":
    $this->_grey();
    break;
      case "emboss":
    $this->_emboss();
    break;
      case "sharp":
    $this->_sharp();
    break;
      case "smooth":
    $this->_smooth($o[1]);
    break;
      case "edge":
    $this->_edge();
    break;
      case "colorize":
    $this->_colorize($o[1], $o[2], $o[3]);
    break;
      case "sepia":
    $this->_sepia();
    break;
      }
    }
    if ($type=="jpg") 
      $success=imagejpeg($this->gd, $file, $quality);
    if ($type=="gif")
      $success=imagegif($this->gd, $file);
    if ($type=="png")
      $success=imagepng($this->gd, $file);
    if (!$success)
      return FALSE;
    return $file;
  }

  public function jpeg($quality = 80) {
    return $this->cacheFile("jpg", $quality);
  }

  public function gif() {
    return $this->cacheFile("gif");
  }

  public function png() {
    return $this->cacheFile("png");
  }
}

