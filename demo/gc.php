<?php
require_once('../GarbageCollect.php');

use Gregwar\Image\GarbageCollect;

GarbageCollect::dropOldFiles(__DIR__.'/cache', 5, true);
