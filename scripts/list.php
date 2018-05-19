<?php

namespace Reddingsmonitor\Scripts;

try {
    /* Require load */
    require_once 'load.php';
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
?>
