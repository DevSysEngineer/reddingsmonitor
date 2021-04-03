<?php

namespace Reddingsmonitor;

use DOMDocument;

/* Set dir */
chdir(__DIR__);

try {
    /* Require load */
    require_once 'load.php';

    /* Stop here if we don't have any file */
    if (empty($_FILES) || empty($_FILES['kml'])) {
        header('HTTP/1.0 400 Bad Request');
        exit;
    }

    if (is_array($_FILES['kml']['tmp_name'])) {
        header('HTTP/1.0 400 Bad Request');
        exit;
    }

    /* Check if uploading failed */ 
    if (!is_uploaded_file($_FILES['kml']['tmp_name'])) {
        header('HTTP/1.0 400 Bad Request');
        exit;
    }

    /* Get KML content */
    $content = file_get_contents($_FILES['kml']['tmp_name']);
    if ($content === FALSE) {
        exit('FAILED');
    }

    /* Set XML settings */
    $xmlPreviousValue = libxml_use_internal_errors(TRUE);

    /* Load XML */
    $dom = new DOMDocument;
    $result = $dom->loadXML($content);

    /* Reset values */
    libxml_clear_errors();
    libxml_use_internal_errors($xmlPreviousValue);

    /* Check result */
    if ($result === NULL) {
        exit('FAILED');
    }

    /* Check if we have elements */
    $elements = $dom->getElementsByTagName('Document');
    if (empty($elements)) {
        exit('FAILED');
    }

    /* Move file */
    if (move_uploaded_file($_FILES['kml']['tmp_name'], $config->getKMLLocation())) {
        exit('OK'); 
    } else {
        exit('FAILED');
    }
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}