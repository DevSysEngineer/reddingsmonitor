<?php

/* Set header */
header('Content-Type: image/png');

/* Get if variable GET is empty */
if (empty($_GET) || empty($_GET['text'])) {
    header('HTTP/1.0 400 Bad Request');
    exit;
}

/* Get some information */
$length = strlen($_GET['text']);
$fontSize = 10;
$xAs = (($fontSize / 2) + 2);
$width = ($xAs * $length);
$height = 25;
$fontPath = '/usr/share/fonts/truetype/freefont/FreeSans.ttf';

/* Create image */
$image = @imagecreate($width, $height);
if ($image === FALSE) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

/* Create background color */
$backgroundColor = @imagecolorallocate($image, 254, 254, 254);
if ($backgroundColor === FALSE) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

/* Create border color */
$borderColor = imagecolorallocate($image, 51, 51, 51);
if ($backgroundColor === FALSE) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

/* Set border color */
$result = @imagerectangle($image, 0, 0, ($width - 1), ($height - 1), $borderColor);
if ($result === FALSE) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

/* Create background color */
$textColor = @imagecolorallocate($image, 51, 51, 51);
if ($textColor === FALSE) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

/* Create image string */
$result = imagettftext($image, $fontSize, 0, $xAs, (($height / 2) + ($fontSize / 2)), $textColor, $fontPath, $_GET['text']);
if ($result === FALSE) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

/* Show image and destroy data */
imagepng($image);
imagedestroy($image);
