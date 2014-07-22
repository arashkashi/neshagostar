<?php
/****************************************************************************************
							Audio & Visual CAPTCHA v1.3
															
								        By
						 Nicklas Swärdh - nick@nswardh.com
						
								  www.nswardh.com
								  
			  (Please respect the author, do not remove these lines!)
****************************************************************************************/
session_start();



// Bake a session-cookie. Will be used in audio.php to validate the download of the generated mp3 file!
$_SESSION['downloadprotect'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);



// Recieve the form...
if (isset($_POST['submit'])) {
$code = trim($_POST['code']);



	// Compare the entered code with the CAPTCHA using md5() hashes
	if (md5($code) == md5($_SESSION['sess_captcha'])) {
	echo "<p style=\"color: #00ff00\">Yup, the code is correct!</p>";
	} else {
	echo "<p style=\"color: #ff0000\">Hey, listen up and try again!</p>";
	}


}


/* 

	NOTE: A few chars has been removed, to prevent confusion
	between the letters. The removed chars are:

		Bb - could be confused with number 8 and spoken Pp
		I  - could be confused with number 1
		l  - could be confused with number 1
		Nn - could be confused with the spoken Mm
		Zz - could be confused with the spoken Cc
		
*/
$_SESSION['sess_captcha'] = substr(str_shuffle("0123456789acdefghijkmopqrstuvwxyACDEFGHJKLMOPQRSTUVWXY"), 0, 4);


?>

<p><a href="http://www.nswardh.com/shout">Audio & Visual CAPTCHA - v1.3</a></p>

Having a hard time to read? Move your mouse over the speaker...<br />
(The following chars has been removed to prevent confusion: <i>Bb, I, l, Nn and Zz</i>)
<div style="float: left; margin-right: 10px"><img src="visual/visual.php" alt="" title="Cant read the numbers? Move the mouse over the speaker and listen..." style="border: 1px solid #000000" /></div>



<div style="float: left">

	<object type="application/x-shockwave-flash" name="movie" data="audio/voice.swf" style="width: 50px; height: 57px">
	<param name="movie" value="audio/voice.swf" />
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="menu" value="false" />
	<param name="quality" value="high" />
	</object>

</div>


<div style="clear: both; padding-top: 15px; padding-right: 15px">


	<form method="post" action="" style="margin: 0px;">
	<input type="text" maxlength="4" name="code" id="code" />
	<input type="submit" name="submit" value="Verify" />
	</form>

</div>