<?php

namespace Reddingsmonitor\Scripts;

use MatthiasMullie\Minify;

/* Set dir */
chdir(__DIR__);

try {
    /* Require load */
    require_once 'load.php';

    /* Check if main file not exists */
    $mainFile = $config->getFilesDir('main.css');
    if (!file_exists($mainFile)) {
        /* Minifier current CSS file */
        $minifier = new Minify\CSS('../../styles/main.css');
        $content = $minifier->minify();

        /* Write minifier output to file */
        file_put_contents($mainFile, $content);
    } else {
        $content = file_get_contents($mainFile);
    }

    /* Set header */
    header('Content-Type: text/css');
    header('Content-Length: ' . strlen($output));

    /* Show output */
    echo $content;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
