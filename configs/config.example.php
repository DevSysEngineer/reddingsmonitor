<?php

namespace Reddingsmonitor\Configs;

/* Check if config object exists */
if (empty($config)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

try {
    /* Set URL */
    $config->setURL('http://xxxxx.reddingsmonitor.nl');

    /* Set secret key */
    $config->setSecretKey('D347rhhn4gedg54dhjsxjh4334b3bn4bn43wxk74bdn777878778d');

    /* Whitelist IP's */
    $config->whitelistIPs([]);

    /* Set Google Maps API */
    $config->setGoogleMapsAPIKey('');
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
