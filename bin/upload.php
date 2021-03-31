<?php

namespace Reddingsmonitor\Bin;

/* Set dir */
chdir(__DIR__);

function getToken(string $url) {
    /* Try to get token */
    $result = FALSE;
    do {
        /* Try to open website */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . 'access.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
    } while ($result === FALSE);

    /* Return token */
    return $result;
}

$targetURL = 'http://zandvoort-rc.reddingsmonitor.nl/';

do {
    $token = getToken($targetURL);
    exit($token);
} while ($error === TRUE);

// $targetURL = 'http://zandvoort-rc.reddingsmonitor.nl/scripts/upload.php';
// $filename = 'test.kml';

// /* Cretae POST data */
// $cFile = curl_file_create($filename);
// $post = [
//   'extra_info' => '123456',
//   'kml'=> $cFile
// ];


// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $targetURL);
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
// $result=curl_exec($ch);
// curl_close($ch);