<?php
/**
* @desc 
* <code>
* #--(Begin)-->define a captcha object
* $smartCaptcha=cmfcSmartCaptcha::factory(array(
*    'sessionVarName'=>'niceCaptchaCode'
* ));
* $smartCaptcha->display();
* #--(End)-->define a captcha object
* </code>
* @todo
* 	- Using new technique to include captcha.php inside package.
* 	- support for parallel using via uinque id
* 	- auto generating captcha image via a new method, and using a predefined captcha.php inside the package.
*/


define ('CMF_ValidationBeta_Ok',true);
define ('CMF_ValidationBeta_Error',2);
define ('CMF_ValidationBeta_Is_Not_Valid_Email',3);

class cmfCaptchaSmartV1Visual {
    var $_options=array();
    
	var $_messagesValue=array(
		CMF_ValidationBeta_Error=>'Unknown error',
		CMF_ValidationBeta_Is_Not_Valid_Email=>'__value__ of __title__ is not valid email',
	);
    
    var $_font='trebuc.ttf';  // Font-type...
    var $_fontsFolderPath; //no trailing slash
    var $_size=30; // Font-size... 
	var $_fontAngle=array(-20,20);
    var $_marginTop=38;  // Height from the top of the image 
	var $_marginLeft=25;  // Height from the top of the image 
    var $_spacing=15; // Initial spacing of first char
    var $_blur = 0;// X times the value of 10;
    var $_width=120;
    var $_height=40;
    var $_textHeight=0;
	var $_color;
    var $_sessionVarName='niceCaptchaCode';
    
    // The CAPTCHA code
    var $_captcha;
	/**
	* mixed,chars,charsLowercase,charsUppercase,digits
	*/
    var $_captchaType='mixed,chars,digits';
    var $_captchaLength=4;
    // Dir where the images are located 
    var $_backgroundImagesFolderPath=''; ////no trailing slash
    
    
    function cmfCaptchaSmartV1Visual($options) {
        @session_start();
        $this->setOptions($options);
        if (empty($this->_backgroundImagesFolderPath))
            $this->_backgroundImagesFolderPath=dirname(__file__).'/visual/images';
        if (empty($this->_fontsFolderPath))
            $this->_fontsFolderPath=dirname(__file__).'/visual/fonts';
        if (empty($this->_captcha)) {
            $this->_captcha=$this->generateCaptchaCode($this->_captchaLength, $this->_captchaType);
		}
		$_SESSION[$this->_sessionVarName]=$this->_captcha;
    }
    
    function isError($obj) {
        return PEAR::isError($obj);
        /*
        if (is_object($obj))
            if (strtolower(get_class($obj))==strtolower(''))
                return true;
        
        return false;
        */
    }
    
	function setOptions($options) {
		foreach ($options as $name=>$value) {
			$this->setOption($name,$value);
		}
	}
	
	function setOption($name,$value) {
		$this->{'_'.$name}=$value;
	}
    
    
	/*
		return $this->raiseError('', CMF_Language_Error_Unknown_Short_Name,
							PEAR_ERROR_RETURN,NULL, 
							array('shortName'=>$shortName));
	*/
							
	function raiseError($message = null, $code = null, $mode = null, $options = null,
                         $userinfo = null, $error_class = null, $skipmsg = false) {
		if (isset($this->_messagesValue[$code]) && empty($message))
			$message=$this->_messagesValue[$code];
			
		if (is_array($userinfo) && !empty($message) ) {
			foreach ($userinfo as $key=>$value) {
				$replacements['__'.$key.'__']=$value;
			}
			$message=cmfcString::replaceVariables($replacements,$message);
		}
		return PEAR:: raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
	}
    
    
    function display() {
		$width=$this->_width;
		$height=$this->_height;
		
        // Create a 200 x 50px picture
        $im = imagecreatetruecolor($width, $height);

        // Get the background
        $background = ImageCreateFrompng($this->_backgroundImagesFolderPath."/back.png");
		list($backWidth,$backHeight) = getimagesize($this->_backgroundImagesFolderPath."/back.png");

        // Get a random number for the x and y cordinates
        $rand_x = mt_rand(0, $backWidth);
		$rand_y = mt_rand(0, $backHeight);

		if (($backWidth/2)<=$width) $rand_x=0;
		if (($backHeight/2)<=$height) $rand_y=0;
		
        // Add the background
        imagecopy($im, $background, 0, 0, $rand_x, $rand_y, $width, $height);
        $spacing=$this->_marginLeft;
        for($i=0; $i < strlen($this->_captcha); $i++) {
            // Randomize a color for each char!
			if (is_null($this->_color))
	            $color = imagecolorallocate($im, mt_rand(230, 255), mt_rand(230, 255),mt_rand(230, 255));
			elseif (is_array($this->_color))
				if (is_array($this->_color['r']) and is_array($this->_color['g']) and is_array($this->_color['b'])) {
					$color = imagecolorallocate($im, mt_rand($this->_color['r'][0],$this->_color['r'][1]), mt_rand($this->_color['g'][0],$this->_color['g'][1]),mt_rand($this->_color['b'][0],$this->_color['r'][1]));
				} elseif (isset($this->_color['r']) and isset($this->_color['g']) and isset($this->_color['b'])) {
					$color = imagecolorallocate($im, $this->_color['r'], $this->_color['g'],$this->_color['b']);
				}

            // Add the chars
            imagettftext($im, $this->_size, mt_rand(-20, 20), $spacing, $this->_marginTop, $color,  $this->_fontsFolderPath.'/'.$this->_font, $this->_captcha{$i});

            $spacing += $this->_spacing; // Adds width between the chars
        }

        // Apply Gaussian Blur to the text, only applies if PHP5 is being used!!
        if (function_exists('imagefilter')) {
            for ($i = 0; $i < $this->_blur; $i++) imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR, 10);
        }
        // finally, add 2 more layers

        // First layer
		
		$layerFile=$this->_backgroundImagesFolderPath."/layer1.png";
		if (file_exists($layerFile)) {
	        $layer = ImageCreateFrompng($layerFile);
			list($layerWidth,$layerHeight) = getimagesize($layerFile);
	        $rand_x = mt_rand(0, round($layerWidth/2));
			$rand_y = mt_rand(0, round($layerHeight/2));
			if (($layerWidth/2)<=$width) $rand_x=0;
			if (($layerHeight/2)<=$height) $rand_y=0;
			imagecopy($im, $layer, 0, 0, $rand_x, $rand_y, $width, $height);
		}
        // Second layer
		$layerFile=$this->_backgroundImagesFolderPath."/layer2.png";
		if (file_exists($layerFile)) {
	        $layer = ImageCreateFrompng($layerFile);
			list($layerWidth,$layerHeight) = getimagesize($layerFile);
	        $rand_x = mt_rand(0, round($layerWidth/2));
			$rand_y = mt_rand(0, round($layerHeight/2));
			if (($layerWidth/2)<=$width) $rand_x=0;
			if (($layerHeight/2)<=$height) $rand_y=0;
			imagecopy($im, $layer, 0, 0, $rand_x, $rand_y, $width, $height);
		}
		
	    // Get the foreground
		$layerFile=$this->_backgroundImagesFolderPath."/foreground.png";
		if (file_exists($layerFile)) {
	        $layer = ImageCreateFrompng($layerFile);
			imagealphablending($layer, true); 
			imagecopy($im, $layer, 0, 0, 0, 0, $width, $height);
		}
        
        // Set a few headers...
        header("Pragma: no-cache");
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Content-type: image/jpeg");
        // Display the generated picture
        imagejpeg($im, '', 80);
        imagedestroy($im);
    }
    
    
    
    function generateCaptchaCode($length, $type = 'mixed') {
        if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')&& ($type != 'charsLowercase')&& ($type != 'charsUppercase')) return false;

        $rand_value = '';
        while (strlen($rand_value) < $length) {
            if ($type == 'digits') {
                $char = mt_rand(0,9);
            } else {
                $char = chr(mt_rand(0,255));
            }
            
            if ($type == 'mixed') {
                if (eregi('^[a-z0-9]$', $char)) $rand_value .= $char;
            } elseif ($type == 'chars') {
                if (eregi('^[a-z]$', $char)) $rand_value .= $char;
            } elseif ($type == 'charsLowercase') {
                if (ereg('^[a-z]$', $char)) $rand_value .= $char;
            } elseif ($type == 'charsUppercase') {
                if (ereg('^[A-Z]$', $char)) $rand_value .= $char;
            } elseif ($type == 'digits') {
                if (ereg('^[0-9]$', $char)) $rand_value .= $char;
            }
        }

        return $rand_value;
    }
    
    function isEnteredCaptchaValid($value) {
        if ($this->_captcha==$value) return true;
        return false;
    }
    
    
	// following function is gathered from php.net, author is "fankounter@libero.it"
	// it is used for gathering the fonts from the spesicfied folder. I have modifeid it slightly
	function ls($__dir="./",$__pattern="*.*")
	{
		settype($__dir, "string");
		settype($__pattern, "string");
		$__ls = array();
		$__regexp = preg_quote($__pattern,"/");
		$__regexp = preg_replace("/[\\x5C][\x2A]/",".*",$__regexp);
		$__regexp = preg_replace("/[\\x5C][\x3F]/",".", $__regexp);
		if(is_dir($__dir))
		 if(($__dir_h=@opendir($__dir))!==FALSE)
		 {
		  while(($__file=readdir($__dir_h))!==FALSE)
		   if(preg_match("/^".$__regexp."$/",$__file))
			array_push($__ls,$__file);
		  closedir($__dir_h);
		  sort($__ls,SORT_STRING);
		 }
		return $__ls;
	}
	
	// function picks a random font for each letter each time
	function randomFont() {
		$fonts = $this -> ls($this -> fontsFolder, "*.ttf");
		$rand = mt_rand(0, sizeof($fonts)-1);
		$this -> font = $this -> fontsFolder.$fonts[$rand];
	}
	
	// the font size would be determined randomly within the limits defined above.
	function randomFontSize() {
		$this -> size = mt_rand($this -> fontSizeMin, $this -> fontSizeMax );
	}
}