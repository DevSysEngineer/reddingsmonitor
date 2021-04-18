<?php

namespace Reddingsmonitor\Scripts;

use Reddingsmonitor\Classes;
use DateTime;

/* Set dir */
chdir(__DIR__);

try {
    /* Require some files */
    require_once 'load.php';
    require_once '../../classes/Placemark.php';

    /* Check if file not eixts */
    $location = $config->getKMLLocation();
    if (!file_exists($location)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Get KML content */
    $content = file_get_contents($location);

    /* Get modify time */
    $modifyTime = filemtime($location);
    if ($modifyTime === FALSE) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Get diff in minutes */
    $nowDateTime = new DateTime('now');
    $fileDateTime = new DateTime('now');
    $fileDateTime->setTimestamp($modifyTime);
    $minutesDiff = abs($nowDateTime->getTimestamp() - $fileDateTime->getTimestamp());
    if ($minutesDiff > 0) {
        $minutesDiff = $minutesDiff / 60;
    }

    /* Create object */
    $object = new \stdClass;
    $object->payload = [];
    $object->minutesDiff = $minutesDiff;
    $object->md5 = NULL;

    /* Check if the content is not FALSE */
    if ($content !== FALSE) {
        /* Set XML settings */
        $xmlPreviousValue = libxml_use_internal_errors(TRUE);

        /* Load XML */
        $dom = new \DOMDocument;
        $result = $dom->loadXML($content);

        /* Reset values */
        libxml_clear_errors();
        libxml_use_internal_errors($xmlPreviousValue);

        /* Check result */
        if ($result !== NULL) {
            /* Set md5 hash */
            $object->md5 = md5($content);

            /* Get placemaks */
            $placemarkElements = $dom->getElementsByTagName('Placemark');
            foreach ($placemarkElements as $placemarkElement) {
                $placemarkObj = new Classes\Placemark($placemarkElement);
                if ($placemarkObj->isValid()) {
                    $object->payload[] = $placemarkObj->toStdClass();
                }
            }

            /* Sort placemaks */
            usort($object->payload, function($a, $b) {
                return strcmp($a->id, $b->id);
            });
        }
    }

    /* Encode object */
    $content = json_encode($object);

    /* Create timestamp */
    $timestamp = gmdate('D, d M Y H:i:s', time() + $config->getRefreshSeconds()) . ' GMT';

    /* Set headers */
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($content));
    header('Expires: ' . $timestamp);
    header('Pragma: cache');
    header('Cache-Control: max-age=' . $config->getRefreshSeconds());

    /* Show content */
    echo $content;
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
