<?php
/****************************************************************************************
							Audio & Visual CAPTCHA v1.3
															
								        By
						 Nicklas Swärdh - nick@nswardh.com
						
								  www.nswardh.com
								  
			  (Please respect the author, do not remove these lines!)
****************************************************************************************/
session_start();



// if $_SESSION['downloadprotect'] contains the right values, execute the script and generate the file!
if (isset($_SESSION['downloadprotect']) && $_SESSION['downloadprotect'] == md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'])) {



// Load the sounds
function GetAudio($file, $dir = 'mp3/') {


$file = strtolower($file); // make sure the filename is lowercase or the fileload might fail...


	$handle = fopen($dir . $file, "rb"); // Read as binary
	$size = filesize($dir . $file);
	
	
	
		// If PHP5 is being used, use stream_get_line() function, else use fread()
		if (function_exists('stream_get_line')) {
		
			$load = stream_get_line($handle, $size);  // Reads files faster than fread and fgets! ;)
		
		} else {
		
			$load = fread($handle, $size);
		
		}



	fclose($handle);

return array("mp3" => $load, "size" => $size);

}



// save the  $_SESSION cookie into 
$captcha = $_SESSION['sess_captcha'];



$order = array("intro",
				$captcha{0},	// 1:st char
				"and",			// Attempt to confuse any possible OCR programs
				$captcha{1},	// 2:nd char
				"and",			// Attempt to confuse any possible OCR programs
				$captcha{2},	// 3:nd char
				"finally",		// Attempt to confuse any possible OCR programs
				$captcha{3});	// 4:th char



// Load the soundfiles into an array
foreach($order as $key => $value) {



	// Is it a number or a CAPITAL letter?
	if (ctype_digit($value)) {
	
		$audio[] = GetAudio("number.mp3");		// to clarify it´s a coming number
		
	} elseif (ctype_upper($value)) {
	
		$audio[] = GetAudio("uppercase.mp3");	// to clarify a coming UPPERCASE letter
		
	} elseif (strlen($value) == 1 && ctype_lower($value)) {
	
		$audio[] = GetAudio("lowercase.mp3");	// to clarify a coming lowercase letter
		
	}
	
	
	
//... and load the actual char!
$audio[] = GetAudio("$value.mp3");
}




// Parse the soundfiles and sum the filesize
foreach ($audio as $key => $value) {
$mp3 .= $audio[$key]['mp3'];
$size += $audio[$key]['size'];
}



unset($order, $audio); // Free up some memory by removing the arrays, we dont need them anymore ;)



	// Send the generated mp3 file
	header("Pragma: no-cache");
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Content-Type: audio/mepeg');
	header("Content-Disposition: attachment; filename=\"validate.mp3\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-length: $size");

	
	
echo $mp3;

}


// Throw the cookie away (to prevent direct downloads of the generated mp3). Do not remove this line!
unset($_SESSION['downloadprotect']);
?>