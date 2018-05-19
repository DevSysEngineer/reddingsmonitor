<?php

namespace Reddingsmonitor\NetLinks;

try {
    /* Require load */
    require_once '../load.php';

    /* Check if config variable not exists */
    if (empty($config)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Check if global get variable is empty */
    if (empty($_GET) || empty($_GET['secretkey'])) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Check if secret key is invalid */
    $secretKey = $_GET['secretkey'];
    if (!$config->checkSecretKey($secretKey)) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Set header */
    header('Content-Type: application/vnd.google-earth.kml+xml');
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
?>
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">
    <Document>
        <NetworkLink>
            <Link>
                <href><?= $config->createKMLURL('main'); ?></href>
                <refreshMode>onInterval</refreshMode>
                <refreshInterval>10</refreshInterval>
            </Link>
        </NetworkLink>
    </Document>
</kml>
