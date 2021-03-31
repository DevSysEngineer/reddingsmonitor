<?php

namespace Reddingsmonitor;

/* Set dir */
chdir(__DIR__);

try {
    /* Require load */
    require_once 'load.php';

    /* Stop here if we don't have any file */
    if (empty($_FILES) || empty($_Files['kml'])) {
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

    /* Move file */
    move_uploaded_file($_FILES['kml']['tmp_name'], $config->getKMLLocation());
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}