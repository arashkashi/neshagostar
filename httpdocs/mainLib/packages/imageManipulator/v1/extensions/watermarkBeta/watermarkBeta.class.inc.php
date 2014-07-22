<?php
/**
* @version $Id: watermarkBeta.class.inc.php 278 2009-08-16 08:18:16Z salek $
*/

class cmfcImageManipulatorV1ExtWatermarkBeta {
	
	var $_extensionParent;
	
	var $_extensionInfo=array(
		'version'=>'beta',
		'framework'=>'imageManipulatorV1'
	);
	
	var $_functionsListForExtensionParent=array(
		'watermarkApply'=>'watermarkApply'
		//'watermarkTextApply'=>'watermarkTextApply',
	);
	
	var $_variablesListForExtensionParent=array(
		'_watermarkVerticalMargin'=>'_verticalMargin',
		'_watermarkHorizontalMargin'=>'_horizontalMargin',
		'_watermarkVerticalAlign'=>'_verticalAlign',
		'_watermarkHorizontalAlign'=>'_horizontalAlign',
		'_watermarkOpacity'=>'_opacity',
		'_watermarkRepeatsNumber'=>'_repeatsNumber',
		'_watermarkMode'=>'_mode',
		'_watermarkPattern'=>'_pattern',
		'_watermarkSpaceBetweenTextsVertical'=>'_spaceBetweenTextsVertical',
		'_watermarkSpaceBetweenTextsHorizontal'=>'_spaceBetweenTextsHorizontal',
	
		'_watermarkImageFilePath'=>'_imageFilePath',
	
		'_watermarkTextSize'=>'_textSize',
		'_watermarkTextAngle'=>'_textAngle',
		'_watermarkTextPosition'=>'_textPosition',
		'_watermarkTextColor'=>'_textColor',
		'_watermarkTextFont'=>'_textFont',
		'_watermarkText'=>'_text',
		
		'_watermarkImageHeight'=>'_imageHeight',
		'_watermarkImageWidth'=>'_imageWidth',
		'_watermarkImageResizeMode'=>'_imageResizeMode',
	);
	
	#--(Begin)-->Watermark area
	var $_verticalMargin; //integer or '5-20' top,bottom
	var $_horizontalMargin; //integer or '5-20' left,right
	var $_verticalAlign; // "left","center","right"
	var $_horizontalAlign; // "top","center","bottom"
	var $_opacity; //integer or '20-100' min,max
	var $_repeatsNumber=5; //number of ranodm draws
	var $_mode='text';//possible values are "text","image"
	var $_pattern='';//zigZag,diagonal
	
	var $_imageFilePath='';//
	
	var $_textSize='5-20'; //integer or array(5,20,6,9) or '5-20'
	var $_textAngle; //integer or '5-20'
	var $_textPosition; //array(left,top) -> top,bottom,center,left,right,center
	var $_textColor='FFFFFF'; //integer RGB Hexadecimal or array(000000,FFFFFF,array(555555,666666)) or '000000-FFFFFF'
	var $_textFont; //string "/fonts/tahoma.ttf" or array("/fonts/tahoma.ttf","/fonts/arial.ttf")
	var $_text='only for test'; //string or array of strings : array('yahoo.com','sony.com','b.com')
	
	var $_imageHeight; //integer or '5-20' min,max
	var $_imageWidth; //integer or '5-20' min,max
	var $_imageResizeMode; //byMaxSize, byMinSize,
	var $_imgWatermark;
	
	
	/**
	* @desc get list of functions that does not start with __
	*/
	function getFunctionsListForExtensionParent() {
		return $this->_functionsListForExtensionParent;
		/*
		$__classMethods = get_class_methods(get_class($this));
		$classMethods=array();
		foreach ($__classMethods as $methodName) {
			if (strpos('!@~'.$methodName,'!@~__')===false) {
				$classMethods[]=$methodName;
			}
		}
		
		return ;
		*/
	}
	
	
	/**
	* @desc get list of functions that does not start with __
	*/
	function getVariablesListForExtensionParent() {
		return $this->_variablesListForExtensionParent;
		/*
		$__classMethods = get_class_vars(get_class($this));
		$classMethods=array();
		foreach ($__classMethods as $methodName) {
			//if (strpos($methodName,'__')===false) {
				$classMethods[]=$methodName;
			//}
		}
		
		return ;
		*/
	}
	
	
	function getExtensionInfo() {
		return $this->_extensionInfo;
	}
	
	/**
	* @return bool
	* @param string $filename
	* @desc Load an image from the file system - method based on file extension
	*/
	function loadWatermark($filename)
	{
		$this->_imgWatermark=$this->_extensionParent->loadImageCustom($filename);
		if (!$this->_imgWatermark) {
			return false;
		}
		return true;
	}
	

	

	
	function watermarkApply() {		

		if ($this->_extensionParent->_imgOrig == null) {
			$this->_extensionParent->LOG(__FUNCTION__, 'The original image has not been loaded.', 'ERROR');
			return false;
		}

		#--(Begin)-->fetch original image size
		$width = @ImageSX($this->_extensionParent->_imgOrig);
		$height= @ImageSY($this->_extensionParent->_imgOrig);
		$this->_extensionParent->log(__FUNCTION__, 'width: '.$width);
		$this->_extensionParent->log(__FUNCTION__, 'height: '.$height);
		#--(End)-->fetch original image size
		
		#--(Begin)-->Create destination image variable
		$this->_extensionParent->_imgFinal=$this->_extensionParent->_imgOrig;
		#--(End)-->Create destination image variable
		#--(Begin)-->Prepare Variables
		if ($this->_mode=='image') {

			$watermarkImage=$this->_extensionParent->loadImageCustom($this->_imageFilePath);
			
		} elseif ($this->_mode=='text') {				
			$this->_extensionParent->log(__FUNCTION__, '$textColor: '.$textColor."  ".'$text: '.$this->_text.' $font: '.$this->_textFont. ' size: '.$textSize);
			$watermarkTextOptions=array(
				'text'=>$this->_text,
				'fontFilePath'=>$this->_textFont,
				//'fontAngle'=>$this->_textAngle,//degree 0 to 360
				'fontSize'=>$this->_textSize,//pixel
				'fontOpacity'=>0, //percent
				'fontColor'=>$this->_textColor,//hex
				'padding'=>0,//pixel 
				'backgroundColor'=>'000000',//hex
				'backgroundOpacity'=>100, //percent
			);
		}		

		#--(End)-->Prepare Variables
		if ($this->_pattern=='zigZag') {
			if ($this->_mode=='text') {
				if (is_null($this->_textSize)) {
					$textSize=$width/17;
				} else {
					$textSize=$this->_textSize;
				}
					
				$watermarkTextOptions['fontSize']=$textSize;
				$watermarkImage=$this->convertTextToImage($watermarkTextOptions);
			}
			if ($this->_textAngle!=0) {
				$watermarkImage=$this->_extensionParent->imagerotate($watermarkImage, $this->_textAngle, -1);
			}
			$watermarkImageSize=array(
				'width' => @ImageSX($watermarkImage),
				'height'=> @ImageSY($watermarkImage)
			);
			
			if ($this->_horizontalMargin=='auto') {
				if ($width>$height) {
					$this->_horizontalMargin=round($width/12);
					if ($this->_verticalMargin='auto') {
						$this->_verticalMargin=round($this->_horizontalMargin/12);
					}
				}
			}
			if ($this->_verticalMargin=='auto') {
				if ($height>$width) {
					$this->_verticalMargin=round($height/12);
					if ($this->_horizontalMargin='auto') {
						$this->_horizontalMargin=round($this->_verticalMargin/12);
					}
				}
			}

			if (is_null($this->_spaceBetweenTextsVertical)) {
				$stepY=$watermarkImageSize['height']+round($watermarkImageSize['height']/2);
			} else {
				$stepY=$this->_spaceBetweenTextsVertical;
			}
			if (is_null($this->_spaceBetweenTextsHorizontal)) {
				$stepX=$watermarkImageSize['width']+round($watermarkImageSize['width']/2);
			} else {
				$stepX=$this->_spaceBetweenTextsHorizontal;
			}
			if ($stepY!=0 and $stepX!=0) {
				$n=0;
				for ($y=(int)$this->_verticalMargin;$y<$height-(int)$this->_verticalMargin;$y=$y+$stepY) {
					$n++;
					$tpTop = $y;
					if ($n%2==0) {
						$p=0;
					} else {
						$p=1;
					}
					for ($x=(int)$this->_horizontalMargin;$x<$width-(int)$this->_horizontalMargin;$x=$x+$stepX) {				
						$p++;
						$tpLeft = $x;
						if ($p%2!=0) {
							//echo "$n,$p,$tpLeft,$tpTop<br />";
							$ret = $this->_extensionParent->imagecopymerge_alpha($this->_extensionParent->_imgFinal, $watermarkImage, $tpLeft, $tpTop, 0, 0, $watermarkImageSize['width'],$watermarkImageSize['height'],$this->_opacity);
						}
					}
				}
			}
		} elseif ($this->_pattern=='diagonal') {

			if ($this->_mode=='text') {
				if (is_null($this->_textSize) or $this->_textSize=='auto') {
					$textSize=$width/17;
				} else {
					$textSize=$this->_textSize;
				}
				$watermarkTextOptions['fontSize']=$textSize;
				$watermarkImage=$this->convertTextToImage($watermarkTextOptions);
			}
			if ($this->_textAngle!=0) {
				$watermarkImage=$this->_extensionParent->imagerotate($watermarkImage, $this->_textAngle, -1);
			}
			$watermarkImageSize=array(
				'width' => @ImageSX($watermarkImage),
				'height'=> @ImageSY($watermarkImage)
			);
			
			if ($this->_horizontalMargin=='auto') {
				if ($width>$height) {
					$this->_horizontalMargin=round($width/12);
					if ($this->_verticalMargin='auto') {
						$this->_verticalMargin=round($this->_horizontalMargin/12);
					}
				}
			}
			if ($this->_verticalMargin=='auto') {
				if ($height>$width) {
					$this->_verticalMargin=round($height/12);
					if ($this->_horizontalMargin='auto') {
						$this->_horizontalMargin=round($this->_verticalMargin/12);
					}
				}
			}
			if ($this->_horizontalMargin>$this->_verticalMargin) {
				$this->_horizontalMargin=$this->_verticalMargin;
			} else {
				$this->_verticalMargin=$this->_horizontalMargin;
			}
			
			$ratio=$width/$height;
			$count=3;

			for ($n=0;$n<$count;$n=$n+1) {
				$x=$width-($width/($count-1))*$n;
				$y=($height/($count-1))*$n;
				$tpLeft=$x-($watermarkImageSize['width']/2);
				$tpTop=$y-($watermarkImageSize['height']/2);
				
				if ($tpLeft<0) $tpLeft=$this->_horizontalMargin;
				if ($tpTop<$watermarkImageSize['height']) $tpTop=$watermarkImageSize['height']+$this->_verticalMargin;
				
				if ($width-$tpLeft<$watermarkImageSize['width']) $tpLeft=$width-$watermarkImageSize['width']-$this->_horizontalMargin;
				//if ($height-$tpTop<$textImageSize['height']) $tpTop=$height-$textImageSize['height']-$this->_verticalMargin;

				$tpLeft=round($tpLeft);
				$tpTop=round($tpTop);
				
				$ret = $this->_extensionParent->imagecopymerge_alpha($this->_extensionParent->_imgFinal, $watermarkImage, $tpLeft, $tpTop, 0, 0, $watermarkImageSize['width'],$watermarkImageSize['height'],$this->_opacity);
			}
			
		} else {
			if ($this->_mode=='text') {
				$watermarkImage=$this->convertTextToImage($watermarkTextOptions);
			}
			if ($this->_textAngle!=0) {
				$watermarkImage=$this->_extensionParent->imagerotate($watermarkImage, $this->_textAngle, -1);
			}
			$watermarkImageSize=array(
				'width' => @ImageSX($watermarkImage),
				'height'=> @ImageSY($watermarkImage)
			);
			
			#--(Begin)-->calculating watermark unique position
			if ($this->_verticalAlign=='top') {
				$tpTop=0+$this->_verticalMargin;
			} elseif ($this->_verticalAlign=='center') {
				$tpTop = round($height/2)-round($watermarkImageSize['height']/2);
			} else {//bottom
				$tpTop = $height - ($watermarkImageSize['height'])-$this->_verticalMargin;
			}
			if ($this->_horizontalAlign=='left') {
				$tpLeft=0+$this->_horizontalMargin;
			} elseif ($this->_horizontalAlign=='center') {
				$tpLeft = round($width/2)-round($watermarkImageSize['width']/2);
			} else {//right
				$tpLeft = $width - ($watermarkImageSize['width'])-$this->_horizontalMargin;
			}
			#--(End)-->calculating watermark unique position			
			//echo "$tpLeft,$tpTop";
			$ret = $this->_extensionParent->imagecopymerge_alpha($this->_extensionParent->_imgFinal, $watermarkImage, $tpLeft, $tpTop, 0, 0, $watermarkImageSize['width'],$watermarkImageSize['height'],$this->_opacity);
		}
		//header('Content-type: image/png');
		//imagePNG($this->_extensionParent->_imgFinal);
		//imagePNG($watermarkImage);
		//exit;
		$this->_extensionParent-> log(__FUNCTION__, $ret);
	}
	
	
	/**
	 * array(
	 *	'text'=>'Salam',
	 *	'fontFilePath'=>realpath(dirname(__FIlE__).'/../../../../dependencies/fonts/arial.ttf'),
	 *	'fontAngle'=>45,//degree 0 to 360
	 *	'fontSize'=>48,//pixel
	 *	'fontOpacity'=>10, //percent
	 *	'fontColor'=>'AABBFF',//hex
	 *	'padding'=>0,//pixel 
	 *	'backgroundColor'=>'000000',//hex
	 *	'backgroundOpacity'=>50, //percent
	 * )
	 * @todo
	 * 	- Use imagestring when angle is zero
	 * @param $options
	 * @return GDImage
	 */
	function convertTextToImage($options=array()) {
        $width = 0;
        $height = 0;
        $offset_x = 0;
        $offset_y = 0;
        $bounds = array();
        $image = "";

		if (!file_exists($options['fontFilePath'])) {
			return false;
		}
	    if (!isset($options['fontAngle'])) {
        	$options['fontAngle']=0;
        }
		if (!isset($options['fontSize'])) {
        	$options['fontSize']=25;
        }
		if (!isset($options['fontColor'])) {
        	$options['fontColor']='FFFFFF';
        }
		if (!empty($options['fontOpacity'])) {
			$options['fontOpacity']=round($options['fontOpacity']*127/100);
		} else {
			$options['fontOpacity']=0;
		}
		if (!isset($options['padding'])) {
        	$options['padding']=0;
        }
		if (!isset($options['backgroundColor'])) {
        	$options['backgroundColor']='000000';
        }
		if (!isset($options['backgroundOpacity'])) {
        	$options['backgroundOpacity']=0;
        }
		$options['backgroundOpacity']=round($options['backgroundOpacity']*127/100);
                
        sscanf($options['fontColor'], "%2x%2x%2x", $f_red, $f_green, $f_blue);
        sscanf($options['backgroundColor'], "%2x%2x%2x", $bg_red, $bg_green, $bg_blue);
        //echo "$f_red, $f_green, $f_blue<br />";
        //echo "$bg_red, $bg_green, $bg_blue";
        //cmfcHtml::printr($options);

        
        if (1==1) {
	        // determine font height.
	        $bounds = ImageTTFBBox($options['fontSize'], $options['fontAngle'], $options['fontFilePath'], $options['text']);
	        //echo print_r($bounds,true);
	        if ($options['fontAngle'] < 0) {
				$font_height = abs($bounds[7]-$bounds[1]);                
	        } else if ($options['fontAngle'] > 0) {
				$font_height = abs($bounds[1]-$bounds[7]);
	        } else {
				$font_height = abs($bounds[7]-$bounds[1]);
	        }
	
	        // determine bounding box.
	        $bounds = ImageTTFBBox($options['fontSize'], $options['fontAngle'], $options['fontFilePath'], $options['text']);
	        if ($options['fontAngle'] < 0) {
				$width = abs($bounds[4]-$bounds[0]);
				$height = abs($bounds[3]-$bounds[7]);
				$offset_y = $font_height;
				$offset_x = 0;
	                
	        } else if ($options['fontAngle'] > 0) {
				$width = abs($bounds[2]-$bounds[6]);
				$height = abs($bounds[1]-$bounds[5]);
				$offset_y = abs($bounds[7]-$bounds[5])+$font_height;
				$offset_x = abs($bounds[0]-$bounds[6]);
	                
	        } else {
				$width = abs($bounds[4]-$bounds[6]);
				$height = abs($bounds[7]-$bounds[1]);
				$offset_y = $font_height;;
				$offset_x = 0;
	        }
	        
	        $image = imagecreatetruecolor($width+($options['padding']*2)+1,$height+($options['padding']*2)+1);
	        
	        if ($options['fontOpacity']>0) {
	        	$foreground = imagecolorallocatealpha($image, $f_red, $f_green, $f_blue,$options['fontOpacity']);
	        } else {
	        	$foreground = imagecolorallocate($image, $f_red, $f_green, $f_blue);
	        }
	
	        if ($options['backgroundOpacity']>0) {
	        	$background = imagecolorallocatealpha($image, $bg_red, $bg_green, $bg_blue,$options['backgroundOpacity']);
	        	ImageFill($image, 0, 0 , $background);
	        	imagealphablending($image, true);
	        	imageSaveAlpha($image, true);
	        } else {
	        	$background = imagecolorallocate($image, $bg_red, $bg_green, $bg_blue);
	        	ImageColorTransparent($image, $background);
	        }
	
	        //If the interlace bit is set and the image is used as a JPEG image, the image is created as a progressive JPEG. 
	        ImageInterlace($image, false);
	        // render it.
	        ImageTTFText($image, $options['fontSize'], $options['fontAngle'], $offset_x+$options['padding'], $offset_y+$options['padding'], $foreground, $options['fontFilePath'], $options['text']);
        }
        return $image;
	}
	
	
	function isWatermarkValueWithinRange($tpLeft, $tpTop, $tpRight, $tpBottom , $randomWatermarkInfo) {
		foreach ($randomWatermarkInfo as $info) {
			if (($tpLeft >= $info['left'] and $tpLeft <= $info['right'] and
				$tpTop >= $info['top'] and $tpTop <= $info['bottom']) or
				($tpRight >= $info['left'] and $tpRight <= $info['right'] and
				$tpBottom >= $info['top'] and $tpBottom <= $info['bottom'])
				) return true;
		}
		return false;
	}
	
	
	/**
	* @desc nly random text mode works right now.
	*/
	function watermarkApplyOld() {
		if ($this->_extensionParent->_imgOrig == null) {
			$this->_extensionParent->_debug($function, 'The original image has not been loaded.');
			return false;
		}
		
		#--(Begin)-->fetch original image size
		$width = @ImageSX($this->_extensionParent->_imgOrig);
		$height= @ImageSY($this->_extensionParent->_imgOrig);
		#--(End)-->fetch original image size
		
		if ($this->_gdVersion == 2) {
			$this->_extensionParent->_imgFinal = @ImageCreateTrueColor($width, $height);
			imageSaveAlpha($this->_extensionParent->_imgFinal, true);
			imageAlphaBlending($this->_extensionParent->_imgFinal, true);
			/*
			@ImageCopyResampled($this->_extensionParent->_imgFinal, $this->_extensionParent->_imgOrig, 0, 0, 0, 0, $width, $height, $width, $height);
			*/
		} else {
			$this->_extensionParent->_imgFinal = @ImageCreate($width, $height);
			@ImageCopyResized($this->_extensionParent->_imgFinal, $this->_extensionParent->_imgOrig, 0, 0, 0, 0, $width, $height, $width, $height);
		}
		
		//$this->_extensionParent->_imgFinal = @ImageCreateTrueColor($width, $height);
		
		#--(Begin)-->create destination image
		if ($this->_extensionParent->_gdVersion == 2) {
			$this->_extensionParent->_imgFinal = @ImageCreateTrueColor($width, $height);
			@ImageCopyResampled($this->_extensionParent->_imgFinal, $this->_extensionParent->_imgOrig, 0, 0, 0, 0, $width, $height, $width, $height);
		} else {
			$this->_extensionParent->_imgFinal = @ImageCreate($width, $height);
			@ImageCopyResized($this->_extensionParent->_imgFinal, $this->_extensionParent->_imgOrig, 0, 0, 0, 0, $width, $height, $width, $height);
		}
		#--(End)-->create destination image
		
		if ($this->_mode=='text') {

			#--(Begin)-->calculating pre defined vairables
			sscanf($this->_textColor, "%2x%2x%2x", $red, $green, $blue);
			$textColorHex=$this->_textColor;
			$textColorOpacity=0;
			if (!empty($this->_opacity))
				$textColorOpacity=round($this->_opacity*127/100);
			$textColor=imagecolorallocatealpha($this->_extensionParent->_imgFinal ,$red, $green, $blue,$textColorOpacity);
			#--(End)-->calculating pre defined vairables
			
			if (!empty($this->_textPosition)) {
				$this->_repeatsNumber=0;
			}
			
			$randomWatermarkInfo=array();
			foreach (range(0,$this->_repeatsNumber) as $number) {
				#--(Begin)-->calculating vairables
				if (is_string($this->_textSize)) {
					list($sizeFrom,$sizeTo)=explode('-',$this->_textSize);
					$size=rand($sizeFrom,$sizeTo);
				} elseif (is_integer($this->_textSize)) {
					$size=$this->_textSize;
				}
				
				#--(Begin)-->calculating text side by pixel
				$watermarkTextSize = imagettfbbox($size, 0, $this->_textFont, $this->_text);
				$watermarkTextSize['width']=abs($watermarkTextSize[2]);
				$watermarkTextSize['height']=abs($watermarkTextSize[5]);
				#--(End)-->calculating text side by pixel
				
				#--(Begin)-->calculating watermark unique position
				if (empty($this->_textPosition)) {
					do {
						$tpLeft=rand(0,$width);
						$tpTop=rand(0,$height);
						$tpRight=$tpLeft+$watermarkTextSize[0];
						$tpBottom=$tpTop+$watermarkTextSize[1];
					} while($this->isWatermarkValueWithinRange($tpLeft, $tpTop, $tpRight, $tpBottom, $randomWatermarkInfo));
					
					$randomWatermarkInfo[]=array(
						'left'=>$tpLeft,
						'top'=>$tpTop,
						'right'=>$tpRight,
						'bottom'=>$tpBottom
					);
				} else {
					$whatIsMostly='';
					if (in_array('top',$this->_textPosition)) {
						$tpTop=$watermarkTextSize['height']+$this->_verticalMargin;
						$whatIsMostly='vertical';
					}
					if (in_array('bottom',$this->_textPosition)) {
						$tpTop=$height+$this->_verticalMargin;
						$whatIsMostly='vertical';
					}
				
					if (in_array('left',$this->_textPosition)) {
						$whatIsMostly='horizontal';
						$tpLeft=0+$this->_horizontalMargin;
					}
					if (in_array('right',$this->_textPosition)) {
						$whatIsMostly='horizontal';
						$tpLeft=$width-($watermarkTextSize['width'])+$this->_horizontalMargin;
					}
					
					if (in_array('center',$this->_textPosition)) {
						if ($whatIsMostly=='horizontal' or $whatIsMostly=='') {
							$tpTop=round(($height/2)-($watermarkTextSize['height']/2))+$this->_verticalMargin;
						}
						if ($whatIsMostly=='vertical' or $whatIsMostly=='') {
							$tpLeft=round(($width/2)-($watermarkTextSize['width']/2))+$this->_horizontalMargin;
						}
					}
				}
				#--(End)-->calculating watermark unique position
				
				if (is_array($this->_textColor)) {
					srand((float) microtime() * 10000000);
					$randKey=array_rand($this->_textColor);
					$textColor=$this->_textColor[$randKey];
					sscanf($textColor, "%2x%2x%2x", $red, $green, $blue);
					$textColor=imagecolorallocatealpha($this->_extensionParent->_imgFinal ,$red, $green, $blue,$textColorOpacity);
					//$textColor=imagecolorallocatealpha($this->_extensionParent->_imgFinal ,$red, $green, $blue,$textColorOpacity);
				}
				#--(End)-->calculating vairables
				imagettftext($this->_extensionParent->_imgFinal, $size, 0, $tpLeft, $tpTop, $textColor, $this->_textFont, $this->_text);
			}
		}
		
		#--(Begin)-->merge image with watermark with indicating opacity
		//$imgResult=$this->getTextImage($this->_text, $size, 0, $textColorHex, $this->_textFont);
		//$this->alphaBlending ($this->_extensionParent->_imgFinal,$imgResult, $tpLeft, $tpTop);   
		//imagepng($imgResult, "files/cache/output.png");
		//$imgResult=imagecreatefrompng("files/cache/output.png");
		//imagecopymerge($this->_extensionParent->_imgFinal, $imgResult, $tpLeft, $tpTop, 0, 0, @ImageSX($imgResult), @ImageSY($imgResult), $this->_opacity);
		#--(Begin)-->merge image with watermark with indicating opacity
		
	}
	#--(End)-->Watermark area
	
	
	
	
	/**
	* @desc
	* @param $color : should be hexa
	*/

	function getTextImage($text, $orgSize, $angle, $color, $font) {
		sscanf($color, "%2x%2x%2x", $red, $green, $blue);
		$size = imageTTFBBox($orgSize, $angle, $font, $text);

		$image = imageCreateTrueColor(abs($size[2]) + abs($size[0]), abs($size[7]) + abs($size[1]));
		
		imageSaveAlpha($image, true);
		imageAlphaBlending($image, false);
		
		$color = imagecolorallocatealpha($image, $red, $green, $blue, 127);
		imagefill($image, 0, 0, $color);
		
		
		$color = imagecolorallocate($image, $red, $green, $blue);
		imagettftext($image, $orgSize, $angle, 0, abs($size[5]), $color, $font, $text);
		//imagepng($image, "files/cache/output.png");
		$this->_extensionParent->log(
			__FUNCTION__, 
			array(
				'text' => $text,
				'orgSize' => $orgSize,
				'angle' => $angle,
				'color' => $color,
				'font' => $font,			
			),
			'INFO'
		);
		
		return $image;
	}
	
	
	function convertBoundingBox ($bbox) {
		if ($bbox[0] >= -1) {
			$xOffset = -abs($bbox[0] + 1);
		} else {
			$xOffset = abs($bbox[0] + 2);
		}
			
		$width = abs($bbox[2] - $bbox[0]);
		
		if ($bbox[0] < -1) $width = abs($bbox[2]) + abs($bbox[0]) - 1;
			$yOffset = abs($bbox[5] + 1);
		if ($bbox[5] >= -1) $yOffset = -$yOffset; // Fixed characters below the baseline.
			$height = abs($bbox[7]) - abs($bbox[1]);
		if ($bbox[3] > 0) $height = abs($bbox[7] - $bbox[1]) - 1;
		return array(
			'width' => $width,
			'height' => $height,
			'xOffset' => $xOffset, // Using xCoord + xOffset with imagettftext puts the left most pixel of the text at xCoord.
			'yOffset' => $yOffset, // Using yCoord + yOffset with imagettftext puts the top most pixel of the text at yCoord.
			'belowBasepoint' => max(0, $bbox[1])
		);
	}
}
?>
