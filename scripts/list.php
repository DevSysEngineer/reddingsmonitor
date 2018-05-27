<?php

namespace Reddingsmonitor\Scripts;

use Reddingsmonitor\Classes;

try {
    /* Require some files */
    require_once 'load.php';
    require_once '../classes/Placemark.php';

    /* Get KML content */
    $content = file_get_contents($config->getKMLLocation());

    /* Create object */
    $object = new \stdClass;
    $object->payload = [];

    /* Load XML */
    $dom = new \DOMDocument;
    $result = $dom->loadXML($content);
    if ($result === NULL) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Get placemaks */
    $placemarkElements = $dom->getElementsByTagName('Placemark');
    foreach ($placemarkElements as $placemarkElement) {
        $placemarkObj = new Classes\Placemark($placemarkElement);
        if ($placemarkObj->isValid()) {
            $object->payload[] = $placemarkObj->toStdClass();
        }
    }

    /* Encode object */
    $content = json_encode($object);

    /* Set headers */
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($content));

    /* Show content */
    echo $content;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
