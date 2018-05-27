<?php

namespace Reddingsmonitor\Scripts;

try {
    /* Require load */
    require_once 'load.php';

    /* Create replace array */
    $replace = [
        '%refreshSeconds%' => ($config->getRefreshSeconds() * 1000),
        '%scriptMain%' => $config->createScriptURL($_GET["token"], 'list')
    ];

    /* Get javascript location */
    $content = file_get_contents('../javascripts/main.js');
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
