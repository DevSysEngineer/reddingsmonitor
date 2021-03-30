<?php

namespace Reddingsmonitor\Scripts;

/* Set dir */
chdir(__DIR__);

try {
    /* Require load */
    $rootPath = '../../';
    require_once '../load.php';

    /* Check if config variable not exists */
    if (empty($config) || empty($auth)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    /* Check if global get variable is empty */
    if (empty($_GET) || empty($_GET['secretkey']) || empty($_GET['token'])) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Check if secret key is invalid */
    $secretKey = $_GET['secretkey'];
    if (!$config->checkSecretKey($secretKey)) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }

    /* Check if token is invalid */
    $token = $_GET['token'];
    if (!$auth->checkToken($token)) {
        header('HTTP/1.0 405 Method Not Allowed');
        exit;
    }
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
?>
