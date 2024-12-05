<?php
session_start(); // Start the session to store the CAPTCHA
header('Content-Type: image/png');

// Create an image
$image = imagecreate(100, 40);
$background_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0); // Black text

// Generate a random CAPTCHA code
$captcha_code = rand(1000, 9999);
$_SESSION['captcha'] = $captcha_code;

// Add the CAPTCHA code to the image
imagestring($image, 5, 25, 10, $captcha_code, $text_color);

// Output the image
imagepng($image);
imagedestroy($image);
?>
