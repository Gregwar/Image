<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\Image;

?>
<img src="<?php echo Image::open('img/test.png')->resize('50%')->inline() ?>" />
