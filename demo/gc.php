<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gregwar\Image\GarbageCollect;

GarbageCollect::dropOldFiles(__DIR__.'/cache', 5, true);
