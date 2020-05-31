<?php

namespace Reddingsmonitor;

try {
    require_once 'vendor/autoload.php';
    require_once 'classes/Config.php';
    require_once 'configs/config.php';
    require_once 'classes/Auth.php';
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
