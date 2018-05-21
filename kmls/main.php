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

    /* Get KML location */
    $content = file_get_contents($config->getKMLLocation());

    /* Check if token exists in $_GET */
    if (!empty($_GET['token'])) {
        /* Check if token is invalid */
        $token = $_GET['token'];
        if (!$auth->checkToken($token)) {
            header('HTTP/1.0 405 Method Not Allowed');
            exit;
        }

        /* Store map data */
        $auth->setMapData($token, 'main', $content);
    }

    /* Set headers */
    header('Content-Type: application/vnd.google-earth.kml+xml');
    header('Content-Length: ' . strlen($content));
    header('Accept-Ranges: bytes');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $config->getRefreshSeconds()) . ' GMT');
    header('Cache-Control: max-age=' . $config->getRefreshSeconds());

    /* Show content */
    echo $content;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
