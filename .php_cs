<?php

require_once './vendor/autoload.php';

use SLLH\StyleCIBridge\ConfigBridge;

return ConfigBridge::create()
    ->setUsingCache(true)
;
