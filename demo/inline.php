<?php
require_once '../autoload.php';

use Gregwar\Image\Image;

?>
<img src="<?php echo Image::open('img/test.png')->resize('50%')->inline() ?>" />
