<?php
/****************************************************************************************
							Audio & Visual CAPTCHA v1.3
															
								        By
						 Nicklas Swärdh - nick@nswardh.com
						
								  www.nswardh.com
								  
			  (Please respect the author, do not remove these lines!)
****************************************************************************************/
session_start();



// A few setings...
$font = 'trebuc.ttf';	// Font-type...
$size = 30;				// Font-size...
$spacing = 15; 			// Initial spacing of first char
$height = 38;			// Height from the top of the image
$blur = 2; 				// X times the value of 10;



// The CAPTCHA code
$captcha = $_SESSION['sess_captcha'];



// Dir where the images are located
$dir = "images";



// Create a 200 x 50px picture
$im = imagecreatetruecolor(200, 50);



// Get the background
$background = ImageCreateFrompng("$dir/back.png");



// First layer
$layer1 = ImageCreateFrompng("$dir/layer1.png");



// Second layer
$layer2 = ImageCreateFrompng("$dir/layer2.png");



// Get a random number for the x and y cordinates
$rand_x = mt_rand(0, 400);
$rand_y = mt_rand(0, 550);



// Add the background
imagecopy($im, $background, 0, 0, $rand_x, $rand_y, 200, 50);



for($i=0; $i < strlen($captcha); $i++) {

	// Randomize a color for each char!
	$color = imagecolorallocate($im, mt_rand(230, 255), mt_rand(230, 255),mt_rand(230, 255));

	// Add the chars
	imagettftext($im, $size, mt_rand(-20, 20), $spacing, $height, $color, $font, $captcha{$i});

$spacing += 50; // Adds width between the chars
}



// Apply Gaussian Blur to the text, only applies if PHP5 is being used!!
if (function_exists('imagefilter')) {
for ($i = 0; $i < $blur; $i++) imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR, 10);
}



// finally, add 2 more layers
imagecopy($im, $layer1, 0, 0, $rand_x, $rand_y, 200, 50);
imagecopy($im, $layer2, 0, 0, $rand_x, $rand_y, 200, 50);



// Set a few headers...
header("Pragma: no-cache");
header('Cache-Control: no-store, no-cache, must-revalidate');
header("Content-type: image/jpeg");



// Display the generated picture
imagejpeg($im, '', 80);
imagedestroy($im);
?>