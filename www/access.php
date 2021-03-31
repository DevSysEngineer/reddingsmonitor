<?php

namespace Reddingsmonitor;

/* Set dir */
chdir(__DIR__);

try {
    /* Require load */
    require_once 'load.php';

    /* Check if config variable not exists */
    if (empty($config)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Check if global server variable is empty */
    if (empty($_SERVER) || empty($_SERVER['REMOTE_ADDR'])) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Check if remote IP is not allowed */
    $remoteIP = $_SERVER['REMOTE_ADDR'];
    if (!$config->allowIP($remoteIP)) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Create token */
    $token = $auth->createToken();
    if ($token === NULL) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Show token */
    exit($token->getUUID());
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}