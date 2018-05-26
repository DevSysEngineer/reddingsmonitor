<?php

namespace Reddingsmonitor\KMLs;

use Reddingsmonitor\Classes;

try {
    /* Require some files */
    require_once '../load.php';
    require_once '../classes/Placemark.php';

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

    /* Get KML content */
    $content = file_get_contents($config->getKMLLocation());

    /* Load XML */
    $dom = new \DOMDocument;
    $result = $dom->loadXML($content);
    if ($result === NULL) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Get placemak objects */
    $placemarkObjs = [];
    $placemarkElements = $dom->getElementsByTagName('Placemark');
    foreach ($placemarkElements as $placemarkElement) {
        $placemarkObj = new Classes\Placemark($placemarkElement);
        if ($placemarkObj->isValid()) {
            $newElement = $placemarkObj->toXML($dom);
            $placemarkElement->parentNode->replaceChild($newelement, $placemarkElement);
        }
    }

    /* Get output from dom */
    $output = $dom->saveXML();

    /* Check if token exists in $_GET */
    if (!empty($_GET['token'])) {
        /* Check if token is invalid */
        $token = $_GET['token'];
        if (!$auth->checkToken($token)) {
            header('HTTP/1.0 405 Method Not Allowed');
            exit;
        }

        /* Store map data */
        $auth->setMapData($token, 'main', $output);
    }

    /* Set headers */
    header('Content-Type: application/vnd.google-earth.kml+xml');
    header('Content-Length: ' . strlen($output));
    header('Accept-Ranges: bytes');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $config->getRefreshSeconds()) . ' GMT');
    header('Cache-Control: max-age=' . $config->getRefreshSeconds());

    /* Show output */
    echo $output;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
