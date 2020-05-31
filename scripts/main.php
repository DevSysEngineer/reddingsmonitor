<?php

namespace Reddingsmonitor\Scripts;

use MatthiasMullie\Minify;

try {
    /* Require load */
    require_once 'load.php';

    /* Create replace array */
    $replace = [
        '%refreshSeconds%' => ($config->getRefreshSeconds() * 1000),
        '%scriptMain%' => $config->createScriptURL($_GET["token"], 'list')
    ];

    /* Check if main file not exists */
    $mainFile = $config->getFilesDir('main.js');
    if (!file_exists($mainFile)) {
        /* Minifier current javascript file */
        $minifier = new Minify\JS('../javascripts/main.js');
        $content = $minifier->minify();

        /* Write minifier output to file */
        file_put_contents($mainFile, $content);
    } else {
        $content = file_get_contents($mainFile);
    }

    /* Get javascript location */
    $output = str_replace(array_keys($replace), array_values($replace), $content);

    /* Set header */
    header('Content-Type: text/javascript');
    header('Content-Length: ' . strlen($output));

    /* Show output */
    echo $output;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
