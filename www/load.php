<?php

namespace Reddingsmonitor;

if (empty($rootPath)) {
    $rootPath = '../';
}

try {
    /* Load vendor */
    require_once $rootPath . 'vendor/autoload.php';

    /* Check if prefix file exists */
    $prefixFile = $rootPath . 'configs/name.prefix';
    if (file_exists($prefixFile)) {
        $prefixName = trim(file_get_contents($prefixFile));
    } else {
        $prefixName = '';
    }

    /* Load other files */
    require_once $rootPath . 'classes/Config.php';
    require_once $rootPath . 'configs/config.php';
    require_once $rootPath . 'classes/Auth.php';
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
