<?php
/**
* 
* @version $Id: imageManipulatorV1.class.inc.php 432 2009-10-06 08:07:19Z salek $
* 
* 
* This is a class allows you to crop an image in a variety of ways.
* You can crop in an absolute or relative way (to a certain size or
* by a certain size), both as a pixel number or a percentage.  You
* can also save or display the cropped image.  The cropping can be
* done in 9 different positions: top left, top, top right, left, 
* centre, right, bottom left, bottom, or bottom right.  Or you can
* crop automatically based on a threshold limit.  The original
* image can be loaded from the file system or from a string (for
* example, data returned from a database.)
* 
* [Feedback]
* There is message board at the following address: 
* http://php.amnuts.com/forums/index.php
* 	
* [Support]
* 	Please use that to post up any comments, questions, bug reports, etc.  You
* 	can also use the board to show off your use of the script.
* 
* If you like this script, or any of my others, then please take a moment
* to consider giving a donation.  This will encourage me to make updates and
* create new scripts which I would make available to you.  If you would like
* to donate anything, then there is a link from my website to PayPal.
* 	
* [Example of use]
* 
* <code>
* 	require 'cropcanvas.class.php'; 
*	$cc = new canvasCrop();
* 
*	$cc->loadImage('original1.png');
*	$cc->cropBySize(100, 100, CMF_IMB_BottomRight);
*	$cc->saveImage('final1.png');
*	
*	$cc->flushImages();
*	
*	$cc->loadImage('original2.png');
*	$cc->cropByPercent(15, 50, ccCENTER);
*	$cc->saveImage('final2.jpg', 90);
*	
*	$cc->flushImages();
*	
*	$cc->loadImage('original3.png');
*	$cc->cropToDimensions(67, 37, 420, 255);
*	$cc->showImage('png');
* </code>
* 
* [New functionslites example (by Sina Salek)]
* 
* <code>
*	require 'cropcanvas.class.php';
*   $cc = new canvasCrop();
*   
*   if ($cc->loadImage(dirname(__FILE__). '/original.png'))
*   {
*      $cc->cropBySize('100', '100', CMF_IMB_BottomRight);
*      $cc->saveImage(dirname(__FILE__). '/final1.jpg');
*      $cc->flushImages();
*   }
*
*   if ($cc->loadImage(dirname(__FILE__). '/original2.png'))
*   {
*       $cc->cropByPercent(15, 50, ccCENTER);
*       $cc->saveImage(dirname(__FILE__). '/final2.jpg', 90);
*       $cc->flushImages();
*   }
*	
*   $cc = new canvasCrop();
*
*   if ($cc->loadImage(dirname(__FILE__). '/ImageManager/gallery/Winter.jpg'))
*   {
*       $cc->resizeByMaxSize(100,'height',120);
*       $cc->saveImage(dirname(__FILE__). '/final2.jpg', 90);
*       $cc->flushImages();
*   }

*   if ($cc->loadImage(dirname(__FILE__). '/original3.png'))
*   {
*       $cc->cropToDimensions(67, 37, 420, 255);
*       $cc->showImage('png');
*   }
* </code>
* 
* @author Andrew Collington, 2003 , php@amnuts.com, http://php.amnuts.com/ 
* @author Sina Salek, http://sina.salek.ws
* @version 1.2.0, 26th November, 2003 (original version)
* @todo
* - The should be two mode for cache. disabled and ignored. when ignored display.php will be used instead
* - Using new technique to place imageDisplayer file inside package.
* - Supporting GIF http://stackoverflow.com/questions/718491/resize-animated-gif-file-without-destroying-animation/718497
* - Image manipulator class only has basic manipulation functions even watermark. even basic caching funtionaity
* 		- There will be an interface for predefined filters and caching
* 		- Interface suppor template system like wysiwyg 		
* - Add support for filters : PHP5's imagefilter() (They have problem with alpha blend)
* 		Simulation for PHP4 is possible : http://www.puremango.co.uk/2009/04/php-4-and-5-image-blur/
* - Add support for text generating
* - New version of cropcanvas was avaialble : http://php.amnuts.com/forums/viewtopic.php?t=385
* - New image manipulator should support custom templates
* - Caching system should be rewritten and name of cache files should be well defined and non duplicate
* - All of the functions realted to the interface should be separated and either extend or implement it
* - Much better error handling
* - Display an image containing error message when the original image can't be load is a good idea
* - There should be global options and they should be overridable buy templates
* - Generating a unique cache name automatically
* - Adding template info or template unique identifer to cache file name as bounes
* 		which means that these features can become disabled for increasing performance performance.
* 		after implemnting template system , this identifer can be generated once per each template on each page   
* 		
* @changelog
* 	+ resizeSmart has two new priority inorder to replace resizeByMin[Max]Size
* 	+ Simplifing imageManipulator method with new options strucutre and actions chaining
* 	+ Image Watermark implement and text,image watermark are not using the same code
* 	+ New interactive demo should also be implmeented 
* 	+ watermark text should become completed
* 	+ getForBrowser() bug fixed
* 	+ support for parameteric image manipulation (by Sina Salek)
* 		this class is an image manipulation system designed to replace all functions like wsfGetImageTag and etc.
*		this porpuse is achieved using the following member functions and data.
*	+ watermark support (by sinasalek)
* 	+ new resize functionalities added (by Sina Salek)
* 
*/

if (!class_exists('PEAR')) trigger_error('cmfcImageManipulatorV1 needs PEAR class',E_USER_ERROR);
if (!class_exists('cmfcClassesCore')) trigger_error('cmfcImageManipulatorV1 needs /packages/cmf/beta/classesCore.class.inc.php',E_USER_ERROR);

require_once(dirname(__FILE__).'/extensionManager.class.inc.php');

define('CMF_ImageManipulatorV1_Ok',true);
define('CMF_ImageManipulatorV1_Error',2);
define('CMF_ImageManipulatorV1_Item_Already_Added',3);

define('CMF_ImageManipulatorBeta_Ok',true);
define('CMF_ImageManipulatorBeta_Error',2);
define('CMF_ImageManipulatorBeta_Item_Already_Added',3);

define("CMF_IMB_TopLeft",     0);
define("CMF_IMB_Top",         1);
define("CMF_IMB_TopRight",    2);
define("CMF_IMB_Left",        3);
define("CMF_IMB_Center",      4);
define("CMF_IMB_Right",       5);
define("CMF_IMB_BottomLeft",  6);
define("CMF_IMB_Bottom",      7);
define("CMF_IMB_BottomRight", 8);


class cmfcImageManipulatorV1 extends cmfcImageManipulatorV1ExtensionManager {
	var $_imgOrig;
	
	var $_imgFinal;
	var $_showDebug;
	var $_gdVersion;
	var $_debugInfo = array();
	
	var $_cacheFolderRPath;
	var $_quality=90;
	var $_sitePath;
	var $_siteUrl;
	var $_disableCache;
	var $_notResizeEqualImageSize = false;
	
	var $_cropPositions=array(
		CMF_IMB_TopLeft=>'topLeft',
		CMF_IMB_Top=>'top',
		CMF_IMB_TopRight=>'topRight',
		CMF_IMB_Left=>'left',
		CMF_IMB_Center=>'center',
		CMF_IMB_Right=>'right',
		CMF_IMB_BottomLeft=>'bottomLeft',
		CMF_IMB_Bottom=>'bottom',
		CMF_IMB_BottomRight=>'bottomRight'
	);
	
	var $_messagesValue=array(
		CMF_ImageManipulatorBeta_Error=>'Unknown error',
	);
	/*
	return $this->raiseError('', CMF_UserFavoriteBeta_Error,
			PEAR_ERROR_RETURN,NULL);
	*/
	/**
	* @return canvasCrop
	* @param bool $debug
	* @desc Class initializer
	*/
	function __construct($options)
	{
		//$this->_showDebug = ($debug ? true : false);
		$this->_gdVersion = (function_exists('imagecreatetruecolor')) ? 2 : 1;
		$this->loadExtension('watermarkBeta');
		$this->setOptions($options);
	}
	
	function log($function, $data, $level = 'INFO'){
		$level = strtoupper($level);
		if ($this->_showDebug)
			$this->_debugInfo[$function][$level][] = $data;
	}
	
	function dumpLog($level = ''){
		if ($this->_showDebug){
			if ($level){
				$level = strtoupper($level);
				if (!empty($this->_debugInfo[$level]) )
					cmfcHtml::printr($this->_debugInfo[$level]);
			}
			else
				cmfcHtml::printr($this->_debugInfo);
		}
		$this->_debugInfo = '';
	}
	/**
	* @return bool
	* @param string $filename
	* @desc Load an image from the file system - method based on file extension
	*/
	function loadImageCustom($filename) {
		if (!@file_exists($filename))
		{
			$this->_debug('loadImage', "The supplied file name '$filename' does not point to a readable file.");
			return false;
		}
		
		$ext  = strtolower($this->_getExtension($filename));
		$func = "imagecreatefrom$ext";
		
		if (!@function_exists($func))
		{
			$this->_debug('loadImage', "That file cannot be loaded with the function '$func'.");
			return false;
		}
		
		$imgOrig = @$func($filename);
		
		if ($imgOrig == null)
		{
			$this->_debug('loadImage', 'The image could not be loaded.');
			return false;
		}
		
		return $imgOrig;
	}
	
	/**
	* @return bool
	* @param string $string
	* @desc Load an image from a string (eg. from a database table)
	*/
	function loadImageFromStringCustom($string)
	{
		$imgOrig = @ImageCreateFromString($string);
		if (!$imgOrig)
		{
			$this->_debug('loadImageFromString', 'The image could not be loaded.');
			return false;
		}
		return $imgOrig;
	}
	
	/**
	* @return bool
	* @param string $filename
	* @desc Load an image from the file system - method based on file extension
	*/
	function loadImage($filename)
	{
		$this->_imgOrig=$this->loadImageCustom($filename);

		if (!$this->_imgOrig) {
			return false;
		}
		return true;
	}
	
	

	/**
	* @return bool
	* @param string $string
	* @desc Load an image from a string (eg. from a database table)
	*/
	function loadImageFromString($string)
	{
		$this->_imgOrig=$this->loadImageCustom($filename);
		if (!$this->_imgOrig) {
			return false;
		}
		return true;
	}
	
	
	/**
	* @return bool
	* @param string $filename
	* @param int $quality
	* @desc Save the cropped image
	*/
	function saveImage($filename, $quality = 100)
	{
		if ($this->_imgFinal == null)
		{
			$this->_debug('saveImage', 'There is no processed image to save.');
			return false;
		}

		$ext = strtolower($this->_getExtension($filename));
		$func = "image$ext";

		if (!@function_exists($func))
		{
			$this->_debug('saveImage', "That file cannot be saved with the function '$func'.");
			return false;
		}

		$saved = false;
		if ($ext == 'png') $saved = $func($this->_imgFinal, $filename);
		if ($ext == 'gif') $saved = $func($this->_imgFinal, $filename);
		if ($ext == 'jpeg') $saved = $func($this->_imgFinal, $filename, $quality);
		if ($saved == false)
		{
			$this->_debug('saveImage', "Could not save the output file '$filename' as a $ext.");
			return false;
		}
		if ($this->_imgFinal==$this->_imgOrig) {
			imagedestroy($this->_imgFinal);
		} else {
			imagedestroy($this->_imgOrig);
			imagedestroy($this->_imgFinal);
		}
		
		return true;
	}
	
	
	/**
	* @return bool
	* @param string $type
	* @param int $quality
	* @desc Shows the cropped image without any saving
	*/
	function showImage($type = 'png', $quality = 100)
	{
		if ($this->_imgFinal == null)
		{
			$this->_debug('showImage', 'There is no processed image to show.');
			return false;
		}
		if ($type == 'png')
		{
			echo @ImagePNG($this->_imgFinal);
			return true;
		}
		else if ($type == 'jpg' || $type == 'jpeg')
		{
			echo @ImageJPEG($this->_imgFinal, '', $quality);
			return true;
		}
		else if ($type == 'gif')
		{
			echo @ImageGIF($this->_imgFinal);
			return true;
		}
		else
		{
			$this->_debug('showImage', "Could not show the output file as a $type.");
			return false;
		}
	}
	
	
	/**
	* @return int
	* @param int $x
	* @param int $y
	* @param int $position
	* @desc Determines the dimensions to crop to if using the 'crop by size' method
	*/
	function cropBySize($x, $y, $position = CMF_IMB_Center)
	{
		if ($x == 0)
		{
			$nx = @ImageSX($this->_imgOrig);
		}
		else
		{
			$nx = @ImageSX($this->_imgOrig) - $x;
		}
		if ($y == 0)
		{
			$ny = @ImageSY($this->_imgOrig);
		}
		else
		{
			$ny = @ImageSY($this->_imgOrig) - $y;
		}
		return ($this->_cropSize(-1, -1, $nx, $ny, $position, 'cropBySize'));
	}


	/**
	* @return int
	* @param int $x
	* @param int $y
	* @param int $position
	* @desc Determines the dimensions to crop to if using the 'crop to size' method
	*/
	function cropToSize($x, $y, $position = CMF_IMB_Center)
	{
		if ($x == 0) $x = 1;
		if ($y == 0) $y = 1;
		return ($this->_cropSize(-1, -1, $x, $y, $position, 'cropToSize'));
	}


	/**
	* @return int
	* @param int $sx
	* @param int $sy
	* @param int $ex
	* @param int $ey
	* @desc Determines the dimensions to crop to if using the 'crop to dimensions' method
	*/
	function cropToDimensions($sx, $sy, $ex, $ey)
	{
		$nx = abs($ex - $sx);
		$ny = abs($ey - $sy);
		return ($this->_cropSize($sx, $sy, $nx, $ny, $position, 'cropToDimensions'));
	}


	/**
	* @return int
	* @param int $percentx
	* @param int $percenty
	* @param int $position
	* @desc Determines the dimensions to crop to if using the 'crop by percentage' method
	*/
	function cropByPercent($percentx, $percenty, $position = CMF_IMB_Center)
	{
		if ($percentx == 0)
		{
			$nx = @ImageSX($this->_imgOrig);
		}
		else
		{
			$nx = @ImageSX($this->_imgOrig) - (($percentx / 100) * @ImageSX($this->_imgOrig));
		}
		if ($percenty == 0)
		{
			$ny = @ImageSY($this->_imgOrig);
		}
		else
		{
			$ny = @ImageSY($this->_imgOrig) - (($percenty / 100) * @ImageSY($this->_imgOrig));
		}
		return ($this->_cropSize(-1, -1, $nx, $ny, $position, 'cropByPercent'));
	}


	/**
	* @return int
	* @param int $percentx
	* @param int $percenty
	* @param int $position
	* @desc Determines the dimensions to crop to if using the 'crop to percentage' method
	*/
	function cropToPercent($percentx, $percenty, $position = CMF_IMB_Center)
	{
		if ($percentx == 0)
		{
			$nx = @ImageSX($this->_imgOrig);
		}
		else
		{
			$nx = ($percentx / 100) * @ImageSX($this->_imgOrig);
		}
		if ($percenty == 0)
		{
			$ny = @ImageSY($this->_imgOrig);
		}
		else
		{
			$ny = ($percenty / 100) * @ImageSY($this->_imgOrig);
		}
		return ($this->_cropSize(-1, -1, $nx, $ny, $position, 'cropByPercent'));
	}


	/**
	* @return bool
	* @param int $threshold
	* @desc Determines the dimensions to crop to if using the 'automatic crop by threshold' method
	*/
	function cropByAuto($threshold = 254)
	{
		if ($threshold < 0) $threshold = 0;
		if ($threshold > 255) $threshold = 255;

		$sizex = @ImageSX($this->_imgOrig);
		$sizey = @ImageSY($this->_imgOrig);

		$sx = $sy = $ex = $ey = -1;
		for ($y = 0; $y < $sizey; $y++)
		{
			for ($x = 0; $x < $sizex; $x++)
			{
				if ($threshold >= $this->_getThresholdValue($this->_imgOrig, $x, $y))
				{
					if ($sy == -1) $sy = $y;
					else $ey = $y;

					if ($sx == -1) $sx = $x;
					else
					{
						if ($x < $sx) $sx = $x;
						else if ($x > $ex) $ex = $x;
					}
				}
			}
		}
		$nx = abs($ex - $sx);
		$ny = abs($ey - $sy);
		return ($this->_cropSize($sx, $sy, $nx, $ny, CMF_IMB_TopLeft, 'cropByAuto'));
	}


	/**
	* @return void
	* @desc Destroy the resources used by the images
	*/
	function flushImages()
	{
		@ImageDestroy($this->_imgOrig);
		@ImageDestroy($this->_imgFinal);
		$this->_imgOrig = $this->_imgFinal = null;
	}
	

	/**
	* @return bool
	* @param int $ox Original image width
	* @param int $oy Original image height
	* @param int $nx New width
	* @param int $ny New height
	* @param int $position Where to place the crop
	* @param string $function Name of the calling function
	* @desc Creates the cropped image based on passed parameters
	*/
	function _cropSize($ox, $oy, $nx, $ny, $position, $function)
	{
		if ($this->_imgOrig == null)
		{
			$this->_debug($function, 'The original image has not been loaded.');
			return false;
		}
		if (($nx <= 0) || ($ny <= 0))
		{
			$this->_debug($function, 'The image could not be cropped because the size given is not valid.');
			return false;
		}
		if (($nx > @ImageSX($this->_imgOrig)) || ($ny > @ImageSY($this->_imgOrig)))
		{
			$this->_debug($function, 'The image could not be cropped because the size given is larger than the original image.');
			return false;
		}
		if ($ox == -1 || $oy == -1)
		{
			list($ox, $oy) = $this->_getCopyPosition($nx, $ny, $position);
		}
		if ($this->_gdVersion == 2)
		{
			$this->_imgFinal = @ImageCreateTrueColor($nx, $ny);
			@ImageCopyResampled($this->_imgFinal, $this->_imgOrig, 0, 0, $ox, $oy, $nx, $ny, $nx, $ny);
		}
		else
		{
			$this->_imgFinal = @ImageCreate($nx, $ny);
			@ImageCopyResized($this->_imgFinal, $this->_imgOrig, 0, 0, $ox, $oy, $nx, $ny, $nx, $ny);
		}
		return true;
	}


	/**
	* @return array
	* @param int $nx
	* @param int $ny
	* @param int $position
	* @desc Determines dimensions of the crop
	*/
	function _getCopyPosition($nx, $ny, $position)
	{
		$ox = @ImageSX($this->_imgOrig);
		$oy = @ImageSY($this->_imgOrig);
		$this->log(__FUNCTION__, 'position: '.$position,'ERROR');
		switch($position)
		{
			case CMF_IMB_TopLeft:
				return array(0, 0);
			case CMF_IMB_Top:
				return array(ceil(($ox - $nx) / 2), 0);
			case CMF_IMB_TopRight:
				return array(($ox - $nx), 0);
			case CMF_IMB_Left:
				return array(0, ceil(($oy - $ny) / 2));
			case CMF_IMB_Center:
				$info =  array(ceil(($ox - $nx) / 2), ceil(($oy - $ny) / 2));
				$this->log( __FUNCTION__, $info,'ERROR');
				return $info;
			case CMF_IMB_Right:
				return array(($ox - $nx), ceil(($oy - $ny) / 2));
			case CMF_IMB_BottomLeft:
				return array(0, ($oy - $ny));
			case CMF_IMB_Bottom:
				return array(ceil(($ox - $nx) / 2), ($oy - $ny));
			case CMF_IMB_BottomRight:
				return array(($ox - $nx), ($oy - $ny));
		}
	}


	/**
	* @return float
	* @param resource $im
	* @param int $x
	* @param int $y
	* @desc Determines the intensity value of a pixel at the passed co-ordinates
	*/
	function _getThresholdValue($im, $x, $y)
	{
		$rgb = ImageColorAt($im, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		$intensity = ($r + $g + $b) / 3;
		return $intensity;
	}
	

	/**
	* @return string
	* @param string $filename
	* @desc Get the extension of a file name
	*/
	function _getExtension($filename)
	{
		$ext  = @strtolower(@substr($filename, (@strrpos($filename, ".") ? @strrpos($filename, ".") + 1 : @strlen($filename)), @strlen($filename)));
		return ($ext == 'jpg') ? 'jpeg' : $ext;
	}


	/**
	* @return void
	* @param string $function
	* @param string $string
	* @desc Shows debugging information
	*/
	function _debug($function, $string)
	{
		//echo "<p><strong style=\"color:#FF0000\">Error in function $function:</strong> $string</p>\n";
		$this->log($function, $string, 'ERROR');
	}


	/**
	* @return array
	* @desc Try to ascertain what the version of GD being used is, based on phpinfo output
	*/
	function _getGDVersion()
	{
		static $version = array();
		
		if (empty($version))
		{
			ob_start();
			phpinfo();
			$buffer = ob_get_contents();
			ob_end_clean();
			if (preg_match("|<B>GD Version</B></td><TD ALIGN=\"left\">([^<]*)</td>|i", $buffer, $matches))
			{
				$version = explode('.', $matches[1]);
			}
			else if (preg_match("|GD Version </td><td class=\"v\">bundled \(([^ ]*)|i", $buffer, $matches))
			{
				$version = explode('.', $matches[1]);
			}
			else if (preg_match("|GD Version </td><td class=\"v\">([^ ]*)|i", $buffer, $matches))
			{
				$version = explode('.', $matches[1]);
			}
		}
		return $version;
	}
	
	
	
	/**
	* @return int
	* @param int $size
	* @param int $is_width
	* @param int $max
	* @desc Determines the dimensions to resize
	*/
	function resizeByMaxSize($size, $is_width=true, $max=null)
	{
		if ($is_width=='width') {$is_width=true;} else {$is_width=false;}
		$width = @ImageSX($this->_imgOrig);
		$height= @ImageSY($this->_imgOrig);
		$ratio = $width / $height;
		
		if ($is_width==true) {
			$nx=round($size);
			$ny=round($nx/$ratio);
			if($nx>$width)
			{
				$nx=$width;
				$ny=$height;
			}
			if ($ny>$max and !is_null($max)) {
				$ny=$max;
				$nx=round($ny*$ratio);
			}
		}
		
		if (!$is_width) {
			$ny=round($max);
			$nx=round($ny*$ratio);
			if ($nx>$width and !is_null($width)) {
				$nx=$width;
				$ny=round($nx/$ratio);
			}
		}
		
		$this->log(
			__FUNCTION__,
			array(
				'ny' => $ny,
				'nx' => $nx,
				'height' => $height,
				'width' => $width,
				'max' => $max,
				'mode' => ($is_width)?'byWidth': 'byHeight',
			)
		);

		return ($this->_resize($width, $height, $nx, $ny, 'resizeByMaxSize'));
	}
	
	

	/**
	 * Smart crop Function should be able to calculate background color according to must
	 * Used colors on picture edges and its snap position
	 * Scenarios
	 * + Resize it to exact width and height
	 * + Resize width to exact width and calculate height and vise versa
	 * + Resize width if it's bigger than max width and calculate height and vise versa
	 * + Resize width if it's bigger than max width but use the original height and vise versa, give width or height priority over other one 
	 * 		+ If they're smaller
	 * 		+ If width smaller and height bigger
	 * 		+ if height smaller and width bigger
	 * + Resize both width and height if they're bigger than the defined width and height
	 * + Resize but don't let it be pixelate
	 * @param $width variant 100|array('min'=>10,'max'=>100,'scale'=>false)|auto|original
	 * @param $height variant 100|array('min'=>10,'max'=>100,'scale'=>true)|auto|original
	 * @param $priority array array('width','height','smallerDimension','biggerDimension') 
	 */
	function resizeSmart($width='auto',$height='auto',$priority=array('width','height')) {
		//global $orgWidth;
		//global $orgHeight; 
		$orgWidth = @ImageSX($this->_imgOrig);
		$orgHeight= @ImageSY($this->_imgOrig);
		
		$ratio = $orgWidth / $orgHeight;
		$ratioHeightBased = $orgHeight/$orgWidth;
		
		#--(Begin)-->Lets check and correct the parameters first
		if (empty($width)) {
			$width='auto';			
		}
		if (empty($height)) {
			$height='auto';			
		}
		if (isset($width['max'])) {
			if (isset($width['max']) and isset($width['max'])) {
				if ($width['min']>$width['max']) {
					unset($width['max']);				
				}
			}
		}
		if (isset($width['height'])) {
			if (isset($width['height']) and isset($width['height'])) {
				if ($width['height']>$width['height']) {
					unset($width['height']);				
				}
			}
		}
		if (empty($priority)) {
			$priority=array('width','height');
		}
		if (is_array($width['max'])) {
			if ($width['max']=='original') {
				$width['max']=$orgWidth;				
			}
			if ($width['min']=='original') {
				$width['min']=$orgWidth;				
			}
		}
		if (is_array($height['max'])) {
			if ($height['max']=='original') {
				$height['max']=$orgHeight;				
			}
			if ($height['min']=='original') {
				$height['min']=$orgHeight;				
			}
		}
		if (is_array($height)) {
			if (empty($height['max']) and empty($height['min']) and $height['min']===$height['max']) {
				$height='auto';				
			}
		}
		if (is_array($width)) {
			if (empty($width['max']) and empty($width['min']) and $width['min']===$width['max']) {
				$width='auto';				
			}
		}
		
		if (in_array('smallerDimension',$priority)) {
			if ($orgWidth>=$orgHeight) {
				$priority=array('height','width');
			}
			if ($orgWidth<$orgHeight) {
				$priority=array('width','height');
			}
		}
		if (in_array('biggerDimension',$priority)) {
			if ($orgWidth>=$orgHeight) {
				$priority=array('width','height');
			}
			if ($orgWidth<$orgHeight) {
				$priority=array('height','width');
			}
		}
		#--(End)-->Lets check and correct the parameters first
		
		#--(Begin)-->Simple resizing with no limitation
		if (is_numeric($width)) {
			$nx=$width;
		}
		if (is_numeric($height)) {
			$ny=$height;
		}
		if (is_numeric($width) and is_numeric($height)) {
			//We're forcing it to resize the image to the exact size we requested
			$nx=$width;
			$ny=$height;
			
		} elseif (is_numeric($width) and $height=='auto') {
			//We let it decide about one or both the sizes
			$nx=$width;
			$ny=round($nx/$ratio);
			
		} elseif ($width=='auto' and is_numeric($height)) {
			//We let it decide about one or both the sizes
			$ny=$height;
			$nx=round($ny*$ratio);
			
		} elseif (($width=='auto' and $height=='auto') or ($width=='original' and $height=='original')) {
			//We haven't specify anysize , so it will be filled with the default size
			$nx=$orgWidth;
			$ny=$orgHeight;
			
		} elseif ($width=='original' or $height=='original' or is_array($width) or is_array($height)) {
			if ($width=='original' or is_array($width)) {
				$nx=$orgWidth;
			}
			if ($height=='original' or is_array($height)) {
				$ny=$orgHeight;
			}
		}
		#--(End)-->Simple resizing with no limitation 
		
		#--(Begin)-->Apply Limitations
		$previousFilterInfo=array();
		for ($i=0;$i<=1;$i++) {
			if ($priority[$i]=='width') {
				$info=&$width;
				$infoSecond=&$height;
				$nn=&$nx;
				$nnSecond=&$ny;
				$nnOrg=&$orgWidth;
				$oRatio=&$ratio;
				$oRatioSecond=&$ratioHeightBased;
			} else {
				$info=&$height;
				$infoSecond=&$width;
				$nn=&$ny;
				$nnOrg=&$orgHeight;
				$nnSecond=&$nx;
				$oRatio=&$ratioHeightBased;
				$oRatioSecond=&$ratio;
			}
			
			if (is_array($info)) {
				if (isset($info['max'])) {
					if ($nn>$info['max']) {
						$nn=$info['max'];
					}
				}
				if (isset($info['min'])) {
					if ($nn<$info['min']) {
						if ($nnOrg<$info['min'] and $info['zoomInIfRequire']==true) {						
							$nn=$info['min'];
						} else {
							$nn=$nnOrg;
						}
					}
				}
				
				#--(Begin)-->Apply the new size if it does not exceed previous filter conditions
				$__applyToSecondDimension=true;
				if (!empty($previousFilterInfo)) {
					
					$__n=round($nn/$oRatio);//new possible size
					if (($__n>$previousFilterInfo['max'] and isset($previousFilterInfo['max'])) or ($__n<$previousFilterInfo['min'] and isset($previousFilterInfo['min']))) {
						$__applyToSecondDimension=false;
					}
					if ($__applyToSecondDimension!=true and $info['ignoreAspectRatio']!=true) {
						$nn=round($nnSecond/$oRatioSecond);						
					}
				}
				#--(End)-->Apply the new size if it does not exceed previous filter conditions

				#--(Begin)-->Calculate the second dimension according to the first one
				if ($__applyToSecondDimension and ($infoSecond=='auto' or is_array($infoSecond))) {
					$nnSecond=round($nn/$oRatio);
				}
				#--(End)-->Calculate the second dimension according to the first one
				
				$previousFilterInfo=$info;
			}
		}
		#--(End)-->Apply Limitations
		
		//return array('orgWidth'=>$orgWidth,'orgHeight'=>$orgHeight,'orgRatio'=>$ratio,'width'=>$nx,'height'=>$ny,'ratio'=>$nx/$ny);
		return ($this->_resize($orgWidth, $orgHeight, $nx, $ny, 'resizeSmart'));
	}


	/**
	* @return int
	* @param int $size
	* @param int $is_width
	* @param int $max
	* @desc Determines the dimensions to resize
	*/
	
	/*
    $cc = new canvasCrop();

    if ($cc->loadImage(dirname(__FILE__). '/ImageManager/gallery/Winter.jpg'))
    {
        $cc->resizeByMinSize(100,'height',120);
        $cc->showImage('jpg');
    }
	*/
	function resizeByMinSize($size, $is_width=true, $min=null)
	{
		if ($is_width=='width') {$is_width=true;} else {$is_width=false;}
		$width = @ImageSX($this->_imgOrig);
		$height= @ImageSY($this->_imgOrig);
		$ratio = $width / $height;
		
		if ($is_width==true) {
			$nx=round($size);
			$ny=round($nx/$ratio);
			if ($ny<$min and !is_null($min)) {
				$ny=$min;
				$nx=round($ny*$ratio);
			}
		}
		
		if ($is_width!=true) {
			$ny=round($size);
			$nx=round($ny*$ratio);
			if ($nx<$min and !is_null($min)) {
				$nx=$min;
				$ny=round($nx/$ratio);
			}
		}

		return ($this->_resize($width, $height, $nx, $ny, 'resizeByMinSize'));
	}
	
	
	function resizeByFixSize($nWidth,$nHeight)
	{
		$width = @ImageSX($this->_imgOrig);
		$height= @ImageSY($this->_imgOrig);
		$ratio = $width / $height;
		
		if (empty($nWidth)) {
			$nWidth=round($nHeight*$ratio);
		}
		
		if (empty($nHeight)) {
			$nHeight=round($nWidth/$ratio);
		}

		return ($this->_resize($width, $height, $nWidth, $nHeight, 'resizeByFixSize'));
	}
	
	
	function resize($nwidth,$nheight)
	{
		$width = @ImageSX($this->_imgOrig);
		$height= @ImageSY($this->_imgOrig);
		if (empty($nheight)) $nheight=$height;
		if (empty($nwidth)) $nwidth=$width;
		
		return ($this->_resize($width, $height, $nwidth, $nheight, 'resize'));
	}


	/**
	* @return bool
	* @param int $ox Original image width
	* @param int $oy Original image height
	* @param int $nx New width
	* @param int $ny New height
	* @param string $function Name of the calling function
	* @desc Creates the cropped image based on passed parameters
	*/
	function _resize($ox, $oy, $nx, $ny, $function)
	{
		if ($this->_imgOrig == null)
		{
			$this->_debug($function, 'The original image has not been loaded.');
			return false;
		}
		if (($nx <= 0) || ($ny <= 0))
		{
			$this->_debug($function, 'The image could not be resized because the size given is not valid.');
			return false;
		}

		if ($this->_gdVersion == 2)
		{
			$this->_imgFinal = @ImageCreateTrueColor($nx, $ny);
			imageAlphaBlending($this->_imgFinal, false);	
			imageSaveAlpha($this->_imgFinal,true);
		
			@ImageCopyResampled($this->_imgFinal, $this->_imgOrig, 0, 0, 0, 0, $nx, $ny, $ox, $oy);
		}
		else
		{
			$this->_imgFinal = @ImageCreate($nx, $ny);
			@ImageCopyResized($this->_imgFinal, $this->_imgOrig, 0, 0, 0, 0, $nx, $ny, $ox, $oy);
		}
		return true;
	}
	
	
	
   function alphaBlending ($dest, $source, $dest_x, $dest_y) {
  
       ## lets blend source pixels with source alpha into destination =)
       for ($y = 0; $y < imagesy($source); $y++) {
           for ($x = 0; $x < imagesx($source); $x++) {
                          
               $argb_s = imagecolorat ($source    ,$x            ,$y);
               $argb_d = imagecolorat ($dest    ,$x+$dest_x    ,$y+$dest_y);
                          
               $a_s    = ($argb_s >> 24) << 1; ## 7 to 8 bits.
               $r_s    =  $argb_s >> 16    & 0xFF;
               $g_s    =  $argb_s >>  8    & 0xFF;
               $b_s    =  $argb_s            & 0xFF;
                              
               $r_d    =  $argb_d >> 16    & 0xFF;
               $g_d    =  $argb_d >>  8    & 0xFF;
               $b_d    =  $argb_d            & 0xFF;
                              
               ## source pixel 100% opaque (alpha == 0)
               if ($a_s == 0) {
                   $r_d = $r_s; $g_d = $g_s; $b_d = $b_s;
               }
               ## source pixel 100% transparent (alpha == 255)
               else if ($a_s > 253) {
               ## using source alpha only, we have to mix (100-"some") percent
               ## of source with "some" percent of destination.
               } else {
                   $r_d = (($r_s * (0xFF-$a_s)) >> 8) + (($r_d * $a_s) >> 8);
                   $g_d = (($g_s * (0xFF-$a_s)) >> 8) + (($g_d * $a_s) >> 8);
                   $b_d = (($b_s * (0xFF-$a_s)) >> 8) + (($b_d * $a_s) >> 8);
               }
                              
               $rgb_d = imagecolorallocatealpha ($dest, $r_d, $g_d, $b_d, 0);
               imagesetpixel ($dest, $x, $y, $rgb_d);
           }
       }
   }
   
	
	/**
	array (
		'fileName'=>'test.jpg',
		'fileRelativePath'=>'files/new',
		'sitePath'=>'',
		'siteUrl'=>'',
		'cacheFolderRPath'=>'',
		'width'=>100,
		'height'=>100,
		'mode'=>'fixSize,watermark', //
		'cropPosition'=>'center', //topLeft,top,topRight,left,center,right,bottomLeft,bottom,bottomRight
		'modesInfo'=>array(
			
		),
		'attributes'=>array(
			'id'=>'',
			'alt'=>'',
			'style'=>''
		)
	)
	*/
	function convertGetToManipulateInfo() {
		$fileOptions = array(
			'fileFullPath' 	=> urldecode($_GET['file']),
			'width' 	=> $_GET['width'],
			'targetRPath'=> $_GET['target'],
			'height' 	=> $_GET['height'],
			'mode' 		=> $_GET['mode'],
			'quality' 	=> $_GET['quality'],
			'cropPosition' 	=> $_GET['cropPosition']
		);
		if ($_GET['debug'])
			$this->_showDebug = $_GET['debug'];
		return $this->convertToManipulateInfo($fileOptions);
	}
	
	function convertToManipulateInfo($fileOptions) {
		
		if (!trim($fileOptions['quality'])) $fileOptions['quality']= $this->_quality;
		if (is_null($fileOptions['sitePath'])) $fileOptions['sitePath']=$this->_sitePath;
		if (is_null($fileOptions['siteUrl'])) $fileOptions['siteUrl']=$this->_siteUrl;
		if (is_null($fileOptions['cacheFolderRPath'])) $fileOptions['cacheFolderRPath']=$this->_cacheFolderRPath;
        $fileOptions['fileRelativePath']=cmfcDirectory::normalizePath(trim($fileOptions['fileRelativePath'],'/\\'));
        $fileOptions['cacheFolderRPath']=cmfcDirectory::normalizePath(trim($fileOptions['cacheFolderRPath'],'/\\'));
		
		if (!empty($fileOptions['fileFullPath'])) {
			$pathInfo=pathinfo($fileOptions['fileFullPath']);
			$fileOptions['fileName']=$pathInfo['basename'];       
			$fileOptions['fileRelativePath']=$pathInfo['dirname'];
		}
		$fileOptions['sourceFilePath']=$fileOptions['sitePath'].'/'.$fileOptions['fileRelativePath'].'/'.$fileOptions['fileName'];
		$fileOptions['fileExtension']= cmfcFile::getFileExtension($fileOptions['fileName']);
        
        $fileOptions['sourceFilePath']=cmfcDirectory::normalizePath($fileOptions['sourceFilePath']);
		
		$this->setOptions($fileOptions);
		
		#--(Begin)--> creating an array of required modes
		if ($fileOptions['modesFormatVersion']!=2) {
			$mode = $fileOptions['mode'];
			if (is_string($mode))
				$modes 	= explode (',', $mode);
			elseif (is_array($mode))
				$modes 	= $mode;
			$fileOptions['modes']=$modes;
		} else {
			//$fileOptions['modes']=$mode;			
		}
		#--(End)--> creating an array of required modes
		
		return $fileOptions;
	}
	
	function manipulateImage($fileOptions) {

		if ($fileOptions['version']!=2) {
			return $this->manipulateImageLegacy($fileOptions);
		}
		
		$fileOptions=$this->convertToManipulateInfo($fileOptions);
		
		#--(Begin)--> Prepare source file and cache file info
		$sourceFilePath=$fileOptions['sourceFilePath'];
		$cacheInfo=$this->getCacheFilePath($fileOptions);

		$targetFilePath = $fileOptions['targetRPath'];
		if (!$targetFilePath){
			$targetFilePath = $fileOptions['cacheFolderRPath'] .'/'. $cacheInfo['fileName'];
		}
		#--( End )--> Prepare source file and cache file info

		if ($this->_disableCache or $cacheInfo['isOutDated']==true or $cacheInfo['isAlreadyCreated']!=true) {
				
			if ($this->loadImage($sourceFilePath)) {
				$this->_imgFinal=$this->_imgOrig;
				foreach ($fileOptions['actions'] as $actionTemplateName=>$actionInfo) {
					foreach ($actionInfo['subActions'] as $subSctionInfo) {
						switch($subSctionInfo['name']) {
							
							case 'resizeSmart':
								$this-> resizeSmart(
									$subSctionInfo['parameters']['width'],
									$subSctionInfo['parameters']['height'],
									$subSctionInfo['parameters']['priority']
								);
							break;
							
							case 'crop':
								if (is_null($subSctionInfo['parameters']['position'])) {
									$subSctionInfo['parameters']['position']='topLeft';
								}
								$subSctionInfo['parameters']['position'] = array_search($subSctionInfo['parameters']['position'], $this->_cropPositions);
								
								$this-> cropToSize(
									$subSctionInfo['parameters']['width'],
									$subSctionInfo['parameters']['height'],
									$subSctionInfo['parameters']['position']
								);
							break;
								
							case 'watermark':
								$this->setOptions(array(
									'watermarkMode'=>$subSctionInfo['parameters']['mode'],
									'watermarkImageFilePath'=>$subSctionInfo['parameters']['imageFilePath'],
									'watermarkText'=>$subSctionInfo['parameters']['text'],
									'watermarkTextSize'=>$subSctionInfo['parameters']['textSize'],
									'watermarkTextColor'=>$subSctionInfo['parameters']['textColor'],
									'watermarkTextAngle'=>$subSctionInfo['parameters']['textAngle'],
									'watermarkTextFont'=>$subSctionInfo['parameters']['textFont'],
									'watermarkOpacity'=>$subSctionInfo['parameters']['opacity'],
									'watermarkRepeatsNumber'=>$subSctionInfo['parameters']['repeatsNumber'],
									'watermarkPattern'=>$subSctionInfo['parameters']['pattern'],
									'watermarkVerticalAlign'=>$subSctionInfo['parameters']['verticalAlign'],
									'watermarkHorizontalAlign'=>$subSctionInfo['parameters']['horizontalAlign'],
									'watermarkVerticalMargin'=>$subSctionInfo['parameters']['verticalMargin'],
									'watermarkHorizontalMargin'=>$subSctionInfo['parameters']['horizontalMargin'],					
									'watermarkSpaceBetweenTextsVertical'=>$subSctionInfo['parameters']['spaceBetweenTextsVertical'],
									'watermarkSpaceBetweenTextsHorizontal'=>$subSctionInfo['parameters']['spaceBetweenTextsHorizontal']
								));
								$this->callMethod('watermarkApply');
							break;
						}
						$this->_imgOrig = $this->_imgFinal;
					}
				}

				if (strtolower($fileOptions['fileExtension']) == 'jpg' or strtolower($fileOptions['fileExtension']) == 'jpeg') {
					$saved = $this-> saveImage($cacheInfo['file'], $fileOptions['quality']);
				} else {
					$saved = $this-> saveImage($cacheInfo['file']);
				}

				if ($saved) {
					@chmod($cacheInfo['file'], 0777);
				}
			}
			
		} else {
			$saved=true;
		}
		
		if ($saved == true) {
			
			if ($targetFilePath != $fileOptions['cacheFolderRPath'] .'/'. $cacheInfo['fileName']){
				rename(
					$fileOptions['sitePath'] .'/'. $fileOptions['cacheFolderRPath'] .'/'. $cacheInfo['fileName'],
					$fileOptions['sitePath'] .'/'. $targetFilePath
				);
			}
			return $targetFilePath;
		} 
		else {
			return false;
		}
	}
	
	function getCacheFilePath($fileOptions) {		
		#--(Begin)-->Convert modes to string
		if ($fileOptions['version']==2) {
			//str_replace("\n",'',print_r($fileOptions['actions'],true));
			$modesStr=md5(serialize($fileOptions['actions']));
		} else {
			$modes=array();
			if (!is_array($fileOptions['mode'])) {
				$modes[] = array($fileOptions['mode']=>array());
			} else {
				$modes=$fileOptions['mode'];
			}
			$modesStr=md5(serialize($fileOptions));
		}
		/*
		 * cmfcHtml::printr($fileOptions);
		foreach ($modes as $modeName=>$modeInfo) {
			if (!is_array($modeInfo)) {
				$modeName=$modeInfo;
				$modeInfo=array();				
			}
			$modesStr.=','.$modeName;
			if (!empty($modeInfo)) {
				
			}
			//$modes = implode('_',$fileOptions['mode']);
		}
		*/
		#--(End)-->Convert modes to string
		
		#--(Begin)-->Convert file relative path to a file name
		$cacheFileName 	= str_replace(array('/','\\',' '), array('_','_','_'), $fileOptions['fileRelativePath'].'/'.$fileOptions['fileName']);
		#--(End)-->Convert file relative path to a file name
		
		#--(Begin)-->
		$fileExtensionOriginal=cmfcFile::getFileExtension($fileOptions['fileName'],true);
		$cacheFileName	= str_replace(
			'.'.$fileExtensionOriginal,
			"_".$modesStr."_.".$fileExtensionOriginal,
			$cacheFileName
		);
		#--(End)-->
		
		#--(Begin)-->Check and see if the cache is outdated or not
		$isOutdate=false;
		if (@filemtime($cacheFile) < @filemtime($fileOptions['sourceFilePath'])) {
			$isOutdate=true;
		}
		#--(End)-->Check and see if the cache is outdated or not
		
		$cacheFile = $fileOptions['sitePath']. '/'. $fileOptions['cacheFolderRPath'] .'/'. $cacheFileName;
        
		$result=array(
			'file'=>cmfcDirectory::normalizePath($cacheFile),
			'fileName'=>$cacheFileName,
			'isOutdated'=>$isOutdate,
			'isAlreadyCreated'=>(is_file($cacheFile))?true:false
		);
		return $result;
	}
	
	function getAsImageTag($fileOptions) {
		$filePath = $this -> manipulateImage(&$fileOptions);
        
        if ($filePath!=false) {
		    list($width, $height, $type, $attr) = getimagesize($this->_sitePath .'/'.$filePath);
		    $fileOptions['width']=$width;
		    $fileOptions['height']=$height;
        }
		
		$attributes = $fileOptions['attributes'];
		
		$attributes['src'] = $this->_siteUrl.'/'.$filePath;
		$attributes['border'] = 0;

		if (!isset($attributes['width']) and !empty($fileOptions['width'])) {
			$attributes['width']=$fileOptions['width'];
		}
		if (!isset($attributes['height']) and !empty($fileOptions['height'])) {
			$attributes['height']=$fileOptions['height'];
		}		
		$this->log(__FUNCTION__, array( 'returnedFilePath' => $filePath) );
		$html 	= '<img {attributes} />';
		$replacements = array(
			'{attributes}' => cmfcHtml::attributesToHtml($attributes)
		);
		$finalHtml = cmfcString::replaceVariables($replacements, $html);
		$this->dumpLog();
		
		return $finalHtml;
	}

	
	function photoCacheSrc($fileOptions) {
		$filePath = $this -> manipulateImage(&$fileOptions);
		
		$attributes = $fileOptions['attributes'];
		
		$attributes['src'] = $fileOptions['siteUrl'].'/'.$filePath;
		$attributes['border'] = 0;
		
		$this->log(__FUNCTION__, array( 'returnedFilePath' => $filePath) );
		$html 	= '<img {attributes} />';
		$replacements = array(
			'{attributes}' => cmfcHtml::attributesToHtml($attributes)
		);
		$finalHtml = cmfcString::replaceVariables($replacements, $html);
		$this->dumpLog();
		
		return $attributes['src'];
	}
	
	function getForBrowser($fileOptions=null) {
		$fileOptions = $this->convertGetToManipulateInfo();
		
		$file = $this->manipulateImage($fileOptions);
		
		if ($img = $this->loadImageCustom($this->_sitePath.'/'.$file))
			$this->_imgFinal = $img;
		
		$this->showImage($fileOptions['fileExtension'],$fileOptions['quality']);
		
		$this -> dumpLog('ERROR');
	}
	
	
	/**
	 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
	 * This is a function like imagecopymerge but it handle alpha channel well!!!
	 **/
	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
		$opacity=$pct;
		// getting the watermark width
		$w = imagesx($src_im);
		// getting the watermark height
		$h = imagesy($src_im);
		 
		// creating a cut resource
		$cut = imagecreatetruecolor($src_w, $src_h);
		// copying that section of the background to the cut
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		// inverting the opacity
		$opacity = 100 - $opacity;
		 
		// placing the watermark now
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
	}

	
function imageRotate($image  , $angle  , $bgd_color  ,$ignore_transparent= 0) {
	$angle=-1*$angle;
  return imagerotate($image  , $angle  , $bgd_color  ,$ignore_transparent);	
 
   // convert degrees to radians
   $angle = $angle + 180;
   $angle = deg2rad($angle);
 
   $src_x = imagesx($src_img);
   $src_y = imagesy($src_img);
 
   $center_x = floor($src_x/2);
   $center_y = floor($src_y/2);

   $cosangle = cos($angle);
   $sinangle = sin($angle);

   $corners=array(array(0,0), array($src_x,0), array($src_x,$src_y), array(0,$src_y));

   foreach($corners as $key=>$value) {
     $value[0]-=$center_x;        //Translate coords to center for rotation
     $value[1]-=$center_y;
     $temp=array();
     $temp[0]=$value[0]*$cosangle+$value[1]*$sinangle;
     $temp[1]=$value[1]*$cosangle-$value[0]*$sinangle;
     $corners[$key]=$temp;   
   }
  
   $min_x=1000000000000000;
   $max_x=-1000000000000000;
   $min_y=1000000000000000;
   $max_y=-1000000000000000;
  
   foreach($corners as $key => $value) {
     if($value[0]<$min_x)
       $min_x=$value[0];
     if($value[0]>$max_x)
       $max_x=$value[0];
  
     if($value[1]<$min_y)
       $min_y=$value[1];
     if($value[1]>$max_y)
       $max_y=$value[1];
   }

   $rotate_width=round($max_x-$min_x);
   $rotate_height=round($max_y-$min_y);

   $rotate=imagecreatetruecolor($rotate_width,$rotate_height);
   imagealphablending($rotate, false);
   imagesavealpha($rotate, true);

   //Reset center to center of our image
   $newcenter_x = ($rotate_width)/2;
   $newcenter_y = ($rotate_height)/2;

   for ($y = 0; $y < ($rotate_height); $y++) {
     for ($x = 0; $x < ($rotate_width); $x++) {
       // rotate...
       $old_x = round((($newcenter_x-$x) * $cosangle + ($newcenter_y-$y) * $sinangle))
         + $center_x;
       $old_y = round((($newcenter_y-$y) * $cosangle - ($newcenter_x-$x) * $sinangle))
         + $center_y;
     
       if ( $old_x >= 0 && $old_x < $src_x
             && $old_y >= 0 && $old_y < $src_y ) {

           $color = imagecolorat($src_img, $old_x, $old_y);
       } else {
         // this line sets the background colour
         $color = imagecolorallocatealpha($src_img, 255, 255, 255, 127);
       }
       imagesetpixel($rotate, $x, $y, $color);
     }
   }
  
  return($rotate);
}



	function manipulateImageLegacy($fileOptions) {	
		$debugInfo = '';
		$fileOptions=$this->convertToManipulateInfo($fileOptions);
		
		$sourceFilePath=$fileOptions['sourceFilePath'];
		
		#--(Begin)--> 	analyze display mode
		list($orgWidth,$orgHeight) 	= getimagesize($sourceFilePath);
		$tempWidth 		= $fileOptions['width'];
		$tempHeight 	= $fileOptions['height'];

		if ($fileOptions['overrideSize'] != true){
			if ($fileOptions['width'] > $orgWidth) $tempWidth 	= $orgWidth;
			if ($fileOptions['height'] > $orgHeight) $tempHeight = $orgHeight;
		}
		
		$watermarkMode = false;

		if (is_array($fileOptions['modes'])) {
			foreach ($fileOptions['modes'] as $key=>$mode) {
				if (is_array($mode)) {
					$__modeInfo=$mode;
					$mode=$key;
				}
				
				switch ($mode) {
				case 'stretch' :
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'strech',
							'width' 	=> $fileOptions['width'],
							'height' 	=> $fileOptions['height'],
						)
					);
					break;
				
				case 'resizeByFixSize' :
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'byFixSize',
							'width' 	=> $tempWidth,
							'height' 	=> $tempHeight,
						)
					);
					break;
				
				case 'resizeByMaxSize' :
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'byMaxSize',
							'width' 	=> $tempWidth,
							'height' 	=> $tempHeight,
						)
					);
					break;
				
				case 'cropToSize' :
					if (!isset($__modeInfo['width'])) {
						$__modeInfo['width']=$tempWidth;
					}
					if (!isset($__modeInfo['height'])) {
						$__modeInfo['height']=$tempHeight;
					}
					if (!isset($__modeInfo['position'])) {
						$__modeInfo['position']=$fileOptions['cropPosition'];
					}
					$__modeInfo['position'] = array_search($__modeInfo['position'], $this->_cropPositions);
					$modeInfo[] = array(
						'crop' => array(
							'position' 	=> $__modeInfo['position'],
							'style' 	=> 'toSize',
							'width' 	=> $__modeInfo['width'],
							'height' 	=> $__modeInfo['height'],
						),
					);
					break;
				
				case 'resizeByMinSize' :
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'byMinSize',
							'width' 	=> $tempWidth,
							'height' 	=> $tempHeight,
						)
					);
					break;
					
				case 'resizeSmart' :
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'smart',
							'width' 	=> $__modeInfo['width'],
							'height' 	=> $__modeInfo['height'],
							'priority'=> $__modeInfo['priority']
						)
					);
					break;
				
				
				case 'watermark' :
				case 'textWatermark':						
					$watermarkMode = true;
					$modeInfo[] = array(
						'textWatermark' => $__modeInfo,
					);
					break;
				
				case 'full' :
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'byMaxSize',
							'width' 	=> $orgWidth,
							'height' 	=> $orgHeight,
						),
					);
					break;
						
				default:
					$fileOptions['cropPosition'] = array_search($fileOptions['cropPosition'], $this->_cropPositions);
					$modeInfo[] = array(
						'resize' => array(
							'style' 	=> 'byMaxSize',
							'width' 	=> $tempWidth,
							'height' 	=> $tempHeight,
						),
						'crop' => array(
							'position' 	=> $fileOptions['cropPosition'],
							'style' 	=> 'toSize',
							'width' 	=> $tempWidth,
							'height' 	=> $tempHeight,
						),
					);
				}
			}
		} else {
			$modeInfo[] = array(
				'resize' => array(
					'style' 	=> 'byMaxSize',
					'width' 	=> $tempWidth,
					'height' 	=> $tempHeight,
				),
				'crop' => array(
					'position' 	=> $fileOptions['cropPosition'],
					'style' 	=> 'toSize',
					'width' 	=> $tempWidth,
					'height' 	=> $tempHeight,
				),
			);
		}
		$this->log(__FUNCTION__,  array('modeInfo' => $modeInfo) );
			
		//$this-> _debugInfo[] = 'modeInfo: '.print_r($modeInfo, true);
		#--(End)-->analyze display mode
		
		#--(Begin)-->prepare source file and cache file info
		$fileOptions['sourceFilePath']=$sourceFilePath;
		$cacheInfo=$this->getCacheFilePath($fileOptions);

		$targetFilePath = $fileOptions['targetRPath'];
		if (!$targetFilePath){
			$targetFilePath = $fileOptions['cacheFolderRPath'] .'/'. $cacheInfo['fileName'];
		}
		#--(End)-->prepare source file and cache file info
		
		$this->log(__FUNCTION__, 'source: '.$sourceFilePath);
		$this->log(__FUNCTION__, 'sitePath: '.$fileOptions['sitePath']);
		$this->log(__FUNCTION__, 'cacheFolder: '.$fileOptions['cacheFolderRPath']);
		$this->log(__FUNCTION__, 'cacheFile: '.$cacheInfo['file']);
		
		if($this->_notResizeEqualImageSize && !$watermarkMode && $fileOptions['width']==$orgWidth && $fileOptions['height']==$orgHeight)
		{
			copy(
				$sourceFilePath,
				$fileOptions['sitePath'] . $targetFilePath
			);
			return $targetFilePath;
		}

		if ($this->_disableCache or $cacheInfo['isOutDated']==true or $cacheInfo['isAlreadyCreated']!=true) {

			if ($this-> loadImage($sourceFilePath)) {
				$this->_imgFinal=$this->_imgOrig;
				foreach ($modeInfo as $mode) {
					foreach ($mode as $modePartName => $modePartInfo) {
						
						if (empty($modePartName)) {
							continue;
						}
						
						if ($modePartInfo['width']) {
							$resizeType = 'width';
						} else {
							$resizeType = 'height';
						}
						
						$this->log(
							__FUNCTION__, 
							array(
								'modeInfo' => array(
									'modeName' => $modePartName,
									'resizeType' => $resizeType,
									'modeInfo' => $modePartInfo,
								)
							)
						);
						switch($modePartName) {
							case 'resize':
								if ($modePartInfo['style'] == 'byMinSize') {
									$this-> resizeByMinSize(
										$modePartInfo['width'],
										$resizeType,
										$modePartInfo['height']
									);
								}
								if ($modePartInfo['style'] == 'byMaxSize') {
									$this-> resizeByMaxSize(
										$modePartInfo['width'],
										$resizeType,
										$modePartInfo['height']
									);
								}
								
								if ($modePartInfo['style']=='byFixSize') {
									$this-> resizeByFixSize(
										$modePartInfo['width'],
										$modePartInfo['height']
									);
								}
								
								if ($modePartInfo['style']=='strech') {
									$this-> resize(
										$modePartInfo['width'],
										$modePartInfo['height']
									);
								}
								if ($modePartInfo['style']=='smart') {
									$this-> resizeSmart(
										$modePartInfo['width'],
										$modePartInfo['height'],
										$modePartInfo['priority']
									);
								}
							break;
							
							case 'crop':
								$this-> cropToSize(
									$modePartInfo['width'],
									$modePartInfo['height'],
									$modePartInfo['position']
								);
							break;
								
							case 'watermark':
								$this->callMethod('watermarkApplyOld');
							break;
							
							case 'textWatermark':
								//$this->_debugInfo[] = 'going to $this->textWatermark()';
								$this->setOptions(array(
									'watermarkMode'=>$modePartInfo['mode'],
									'watermarkImageFilePath'=>$modePartInfo['imageFilePath'],
									'watermarkText'=>$modePartInfo['text'],
									'watermarkTextSize'=>$modePartInfo['textSize'],
									'watermarkTextColor'=>$modePartInfo['textColor'],
									'watermarkTextAngle'=>$modePartInfo['textAngle'],
									'watermarkTextFont'=>$modePartInfo['textFont'],
									'watermarkOpacity'=>$modePartInfo['opacity'],
									'watermarkRepeatsNumber'=>$modePartInfo['repeatsNumber'],
									'watermarkPattern'=>$modePartInfo['pattern'],
									'watermarkVerticalAlign'=>$modePartInfo['verticalAlign'],
									'watermarkHorizontalAlign'=>$modePartInfo['horizontalAlign'],
									'watermarkVerticalMargin'=>$modePartInfo['verticalMargin'],
									'watermarkHorizontalMargin'=>$modePartInfo['horizontalMargin'],					
									'watermarkSpaceBetweenTextsVertical'=>$modePartInfo['spaceBetweenTextsVertical'],
									'watermarkSpaceBetweenTextsHorizontal'=>$modePartInfo['spaceBetweenTextsHorizontal']
								));
								$this->callMethod('watermarkApply');
							break;
						}
						$this->_imgOrig = $this->_imgFinal;
					}
				}
				//$this->_debugInfo[] = 'this: '.print_r($this, true);
								
				if (strtolower($fileOptions['fileExtension']) == 'jpg' or strtolower($fileOptions['fileExtension']) == 'jpeg') {
					@$saved = $this-> saveImage($cacheInfo['file'], $fileOptions['quality']);
				} else {
					@$saved = $this-> saveImage($cacheInfo['file']);
				}

				if ($saved) {
					@chmod($cacheInfo['file'], 0777);
				}
			}
			
		} else {
			$saved=true;
		}
		
		if ($saved == true) {
			$fileOptions['width'] = @ImageSX($this->_imgOrig);
			$fileOptions['height'] = @ImageSY($this->_imgOrig);
			
			if ($targetFilePath != $fileOptions['cacheFolderRPath'] .'/'. $cacheInfo['fileName']){
				rename(
					$fileOptions['sitePath'] .'/'. $fileOptions['cacheFolderRPath'] .'/'. $cacheInfo['fileName'],
					$fileOptions['sitePath'] .'/'. $targetFilePath
				);
			}
			return $targetFilePath;
		} 
		else {
			return false;
		}
	}
}


?>