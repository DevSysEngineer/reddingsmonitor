<?php

namespace Reddingsmonitor;

if (empty($rootPath)) {
    $rootPath = '../';
}

try {
    require_once $rootPath . 'vendor/autoload.php';
    require_once $rootPath . 'classes/Config.php';
    require_once $rootPath . 'configs/config.php';
    require_once $rootPath . 'classes/Auth.php';
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
