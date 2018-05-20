<?php

namespace Reddingsmonitor\KMLs;

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
    header('Content-Type: text/plain');

    /* Get KML location */
    echo file_get_contents($config->getKMLLocation());
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
?>
