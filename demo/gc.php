<?php

require_once '../autoload.php';

use Gregwar\Image\GarbageCollect;

GarbageCollect::dropOldFiles(__DIR__.'/cache', 5, true);
