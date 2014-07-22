<?php
/**
* Optimizer (Minifier, Compressor)
* This class simplifies and automated the time consuming proccess of web page optimization
* Advantages 
* - Easy to use Fully automated process / Can understand relative path , urls , full path even inside css files
* - Decreasing javascript,css,html size up to 1/4
* - Improving page load speed dramatically
* - Fully customaizable
* - Tiny and barely recognizable overload on server            
* 
* @version v1 $Id: optimizerMultiFileV1.class.inc.php 436 2009-10-12 07:44:52Z salek $
* @author sina salek
* @website http://sina.salek.ws
* @company persiantools.com
* @todo
* 	- Replacing javascript minifier with JSMIN+ http://crisp.tweakblogs.net/blog/1681/jsmin+-version-11.html
* 	- Move css files to top
* 	- Caching urls is important, so a customizable period can be used for updaing urls cache, instead of calling them each time
* 		but it's important that the whole things was failsafe.
* 	- Providing some sort of an api for other caller like Ajax everywhere so they can rely on it to compress and optimze contents
*		- Something like applyMethod($stringName='optimizerPackage.content.'//if empty hash code will be generate,$content,$method,$type='js'//css) it generates string hash as file name
*			and store  
* 	- It appears that there is an alternative way for finding document root path
* 		realpath( "." );
* 	- Making it w3c validator friendly casue it does not understand compressed files
* 			if( strstr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')!==false
* 	- Css method, when url wasn't exists, use its path to fix the url so when image placed later, there will
* 		be no need to empty cache
* 	- Using cache folder to make it smarter, it can for example find an appropirate algorithem according to the file
* 		structure and save the result in a cache file then refresh it for example every 30 minute.
* 	- Groups files filter, so for example , example or documentation folder can be ignored and not copied
* 		to cache folder
*	- Support custom cache folder per include. some scripts include other files from their 
*		folder (There is a problem with htaccess).  
* 	- Considering this as an alternative for php packer
* 		Implementing ; safe packer for javascript  
* 		- A very rich PHP5 component collection : http://code.google.com/p/minify/wiki/ComponentClasses
* 		- (Does not fix semicolon issue but is the most efficent one) http://dean.edwards.name/download/#packer
* 		- http://www.crockford.com/javascript/jsmin.html
* 		- yuicompressor(java based) whenever possible
* 			There is a wrapper avaialbe here : http://code.google.com/p/minify/wiki/ComponentClasses
* 			IT's also possible via implementing JAVA Bridge which requires specfic server configuratoin like installing Apache Tomcat
* 	- Inject require optimization htaccess entries into website htaccess like future expires, mode deflate etc
* 		- http://aciddrop.com/php-speedy/
* 		- http://code.google.com/p/minify/
* 	- Since a cache folder is avaialble it can use it to cache files information
* 		for small period of time, the result would be almost no overload on server.
* 		This can be very userful in case of remote files or more complex tasks 
* 	- Implementing CDN. defining CDN list page which will be chosen randomly or
* 		acording to ip address.
* 		uploading files to CDN server automaically using FTP on diffrent period or focebly
* 		even by using cron
* 	- Optimzing image files. can be done using linux extensions
*	- Method for getting clinet internet speed but sending a request to server after the page has been loaded
*		so the website can optimzie itself according to user internet speed. (it can also be achieved using a simple link for setting speed!)
*		but either way optimzier should support different modes 
* 	- Implementing combine techinque. all the js and css should become be one file
* 		- This one can include css inlcude inside css and paste it content inside caller http://code.google.com/p/minify/source/browse/trunk/min/lib/Minify/ImportProcessor.php
*	- Implementing autopage optimization,
*		- Analyze and optimze all the inclue css or js files
*		- Inject require htaccess entries
*		- Store a cached version or use realtime caching
* @changelog
* 	+ Critical : CSS compress method does not fix urls, when it's called independently 
* 	+ htaccess for gzip files does not work properly
* 	+ Fixing css urls has issue with similar urls
* 	+ Finding a solution for non line break removal safe, new option added and new algorithm (jsmin) implemented
* 	+ Support grouping for files which need their related files near them like .js plugin files
* 		Moving the whole directory to cache folder 
* 	+ Cache file extensions should indicate which methoed has been applied to file. js.packed.js.jgz 
* 	+ CSS url relative path correction should be smart and works on all circumstanses 
* 	+ Debug functionality, disablaing cache temporary and enable debug method 
*  	+ Optimzing remote files and caching them on local server
* 	+ Better includes files path correction. there are three types :
* 		All the urls will be compared to page's path
* 		- relative : files/interface/common or ../files/interface
* 		- absolute : /files/interface/common.css
* 		- remote (won't be supported anytime soon) : http://files/interface/common.css  
*   + Supports query string in css and javascript include urls lile ....js?ver=20.1.2
* 	+ CSS url relative path correction
*/
if (version_compare(phpversion(), "5.0.0", '>=')) {
	require_once(dirname(__FILE__).'/plugins/packer.php-1.1/class.JavaScriptPacker.php');
	require_once(dirname(__FILE__).'/plugins/jsmin/jsmin1-1-1.php');
} else {
	require_once(dirname(__FILE__).'/plugins/packer.php-1.1/class.JavaScriptPacker.php4');
}

define ('CMF_OptimizerMultiFileV1_Ok',true);
define ('CMF_OptimizerMultiFileV1_Error',2);
/**
* @package 
*/
class cmfcOptimizerMultiFileV1 extends cmfcClassesCore {
	
	/**
	 * Full path of the page.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_pageFolderPath='';
	/**
	 * Page browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_pageFolderPathBrowser='';
	/**
	 * Full path of the site
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPath='';
	/**
	 * site browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPathBrowser='';
	
	/**
	 * Full path of the cache folder which has to be accessible via browser
	 * @var string 
	 */
	var $_cacheFolderPath='';
	/**
	 * cache folder browser path excluding site url. which has to be accessible via browser
	 * @var string 
	 */
	var $_cacheFolderPathBrowser='';
	
	/**
	* files path should be relative to url
	*/
	var $_files=array();
	/**
	 * Default optimization methods, it can also be defined for each file
	 * @var array
	 */
	var $_filesDefaultMethods=array('minify','compress');
	
	/**
	* This will optimized current page using
	* @notice not implmented yet
	* @var boolean
	*/
	var $_autoPageOptimization=false;
	/**
	 * 
	 * @var unknown_type
	 */
	var $_autoPageMethods=array('minify','compress');
	
	/**
	 * Fix relative css urls like ../ or test/css.css to point the right file.
	 * Since it works almost all the time, there is not need to change it value
	 * @var boolean
	 */
	var $_cssFixRelativeUrls=true;
	
	/**
	 * 
	 * @var boolean
	 */
	var $_debugEnabled=false;
	
	/**
	 * Overwrite cache files without checking if they've really changed 
	 * @var boolean
	 */
	var $_cacheEnabled=true;
	
	/**
	 * Some javascript may not have semicolon at the end of all
	 * statements, user either has to add it or set this option for that javascript
	 * to false.
	 * This is the default value for all files
	 * @notice This option is only avaialbe for php5
	 * @var boolean
	 */
	var $_javascriptRemoveLineBreaks=true;
	
	/**
	 * 
	 * @var 
	 */
	var $_defaultFolderPermission=0777;
	
	/**
	 * 
	 * @var 
	 */
	var $_defaultFilePermission=0777;

	/**
    * Construction
    */
	function __construct($options) {
		$this->_fields=array();
		$this->setOptions($options);
		//$this->addCommandHandler('validate',array(&$this,'__validate'));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see v2/cmfcClassesCore#setOptions($options, $merge)
	 */
	function setOptions($options,$merge=false) {
		if (!$merge) {
			if (!isset($options['pageFolderPath'])) {
				$options['pageFolderPath']=dirname($_SERVER['SCRIPT_FILENAME']);
			}
			if (!isset($options['pageFolderPathBrowser'])) {
				$options['pageFolderPathBrowser']=dirname($_SERVER['SCRIPT_NAME']);
			}
			if (!isset($options['siteFolderPath'])) {
				$options['siteFolderPath']=$_SERVER['DOCUMENT_ROOT'];
			} 
			if (!isset($options['siteFolderPathBrowser'])) {
				$options['siteFolderPathBrowser']='';
			}
			if (isset($options['cacheFolderPath']) and !isset($options['cacheFolderPathBrowser'])) {
				$options['cacheFolderPathBrowser']=cmfcDirectory::normalizePath('/'.str_replace($options['siteFolderPath'],'',$options['cacheFolderPath']));
			}
		}
		parent::setOptions($options,$merge);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see v2/cmfcClassesCore#setOption($name, $value, $merge)
	 */
	function setOption($name,$value,$merge=false) {
		if ($name=='pageFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
		} elseif ($name=='siteFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
		} elseif ($name=='cacheFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
			if (!is_writable($value)) {
				trigger_error('Optimizer package need "'.$value.'" folder to be writable',E_USER_ERROR);
			}
		}
		
		parent::setOption($name,$value,$merge);
	}
	
	/**
	* Generates cache file path, according to method and file group
	*/
	function getCacheFile($filePath,$fileInfo=array(),$method=null) {
		
		if (!isset($fileInfo['groupInfo'])) {
			$filePath=str_replace($this->_siteFolderPath,'',$filePath);
			$cacheFilePath=$this->_cacheFolderPath.'/'.str_replace(array('/','\\',':'),array('_'),$filePath);
			$cacheFilePath=cmfcDirectory::normalizePath($cacheFilePath);

		} elseif (strpos($filePath,$this->_cacheFolderPath)!==false) {
			$cacheFilePath=$filePath;
		} else {
			#--(Begin)-->Cache file path is different when file is part of a group
			$filePath=str_replace($fileInfo['groupInfo']['path'],'',$filePath);
			$cacheFilePath=$fileInfo['groupInfo']['cachePath'].'/'.$filePath;
			$cacheFilePath=cmfcDirectory::normalizePath($cacheFilePath);
			#--(End)-->Cache file path is different when file is part of a group
		}
		

		if (!is_null($fileInfo['options']['methods']) or !empty($method)) {
			if (!empty($method)) {
				$method=array($method);
			} elseif(!empty($fileInfo['options']['methods'])) {
				$method=$fileInfo['options']['methods'];
			}
			
			if (in_array('minify',$method)) {
				$cacheFilePath=$cacheFilePath.'.min.'.$fileInfo['extension'];
				
			} elseif (in_array('compress',$method)) {
				
				if ($fileInfo['extension']=='css') {
					$cacheFilePath=$cacheFilePath.'.cgz';
				} elseif ($fileInfo['extension']=='js') {
					$cacheFilePath=$cacheFilePath.'.jgz';
				} else {
					$cacheFilePath=$cacheFilePath.'.gz';
				}
			}
		}
		return $cacheFilePath;
	}
	
	/**
	 * Expire the cache when the time has come and copy group folder if file was part of a group
	 * @param $filePath
	 * @return string
	 */
	function isFileCacheExpired($cacheFilePath,$fileInfo) {
		$result=true;
		#--(Begin)-->Check if it's require to overwrite or create the cache
		if ( file_exists( $cacheFilePath)) {
			$src_last_modified = filemtime( $fileInfo['path'] );
			$dst_last_modified = filemtime( $cacheFilePath );
			// The gzip version of the file exists, but it is older
			// than the source file. We need to recreate it...
			if ( $src_last_modified > $dst_last_modified or $this->_cacheEnabled!=true) {
				unlink( $cacheFilePath );
				
			} else {
				$result=false;
			}
		}
		#--(End)-->Check if it's require to overwrite or create the cache
		
		#--(Begin)-->If file was changed, update its group as well
		if ($result) {
			if (!empty($fileInfo['groupInfo'])) {
				cmfcDirectory::copy($fileInfo['groupInfo']['path'],$fileInfo['groupInfo']['cachePath'],array('folderPermission'=>$this->_defaultFolderPermission,'filePermission'=>$this->_defaultFilePermission));					
			}
		}
		#--(End)-->If file was changed, update its group as well
		
		return $result;
	}
	
	
	/**
	 * Analyze fiels groups and fill their require parametrs like path, group folder cache path ,etc
	 * @param $groups
	 * @return unknown_type
	 */
	function groupsPrepare($groups) {
		if (!empty($groups)) {
			foreach ($groups as $originalPath=>$groupInfo) {
				
				#--(Begin)-->Fix and fill group path
				if ($originalPath[0]=='/') {//Absolute path
					$groupInfo['path']=cmfcDirectory::normalizePath($this->_siteFolderPath.$originalPath);
					$groupInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_siteFolderPathBrowser.$originalPath);
					$groupInfo['pathType']='absolute';
					 
				} elseif (strpos($originalPath,'://')!==false) {//Remote path					
				} else {//Relative path
					if (strpos($originalPath,'../')!==false) {//Support ../
						$groupInfo['path']=realpath(cmfcDirectory::normalizePath($this->_pageFolderPath.'/'.$originalPath));
						$groupInfo['pathBrowser']='/'.cmfcDirectory::normalizePath(str_replace($this->_siteFolderPath,'',$groupInfo['path']));
						$groupInfo['pathType']='veryRelative';
					} else {
						$groupInfo['path']=cmfcDirectory::normalizePath($this->_pageFolderPath.'/'.$originalPath);
						$groupInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_pageFolderPathBrowser.'/'.$originalPath);
						$groupInfo['pathType']='relative';				
					}
				}
				$groupInfo['cachePath']=$this->getCacheFile($groupInfo['path']);
				#--(End)-->Fix and fill group path
				
				$groups[$originalPath]=$groupInfo;
			}
			return $groups;
		}
		return false;
	}
	
	/**
	* If the file is part of a group files, attach its group info to its file info
	*/
	function groupGetFileInfoBasedOnGroup($fileInfo,$groups) {
		
		if (!empty($groups)) {
			#--(Begin)-->Check if file is part of a group
			$groupName=null;
			foreach ($groups as $originalPath=>$groupInfo) {
				if (strpos($fileInfo['path'],$groupInfo['path'])!==false) {
					$groupName=$originalPath;
					break;
				} 
			}
			#--(End)-->Check if file is part of a group

			#--(Begin)-->Copy group files to cache if any of cache files changed
			if (!empty($groupName)) {
				$fileInfo['groupInfo']=$groupInfo;
				//$cachePath=$this->getCacheFile($fileInfo['path'],$fileInfo);				
			}
			#--(End)-->Copy group files to cache if any of cache files changed
		}
		#--(End)-->Check if file is part of a group
		return $fileInfo;
	} 
	
	/**
	 * 
	 * @param $file
	 * @param $fileOptions
	 * @param $options
	 * @return string
	 */
	function processFile($file,$fileOptions,$options) {
		$result=true;
		if (!isset($fileOptions['methods'])) {
			$fileOptions['methods']=$this->_filesDefaultMethods;
		}
		
		#--(Begin)-->Analyze and preapre file path
		$file=trim($file);
		$fileInfo=array();
		$fileInfo['htmlTagPath']=$file;
		$fileInfo['options']=$fileOptions;
		$fileInfo['genericOptions']=$options;

		if ($file[0]=='/') {//Absolute path
			$fileInfo['path']=cmfcDirectory::normalizePath($this->_siteFolderPath.$file);
			$fileInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_siteFolderPathBrowser.$file);
			$fileInfo['pathType']='absolute';
			 
		} elseif (strpos($file,'://')!==false) {//Remote path
			
			$_fp=$this->getCacheFile($file);
			if (!file_exists($_fp)) {
				$_c=file_get_contents($file);
				file_put_contents($_fp,$_c);
				$fileInfo['path']=$_fp;
				$fileInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_cacheFolderPathBrowser.basename($_fp));				
			} else {
				$fileInfo['path']=null;
				$fileInfo['pathBrowser']=null;
			}
			$fileInfo['url']=$file;
			$fileInfo['pathType']='remote';

		} else {//Relative path
			if (strpos($file,'../')!==false) {//Support ../
				$fileInfo['path']=realpath(cmfcDirectory::normalizePath($this->_pageFolderPath.'/'.$file));
				$fileInfo['pathBrowser']='/'.cmfcDirectory::normalizePath(str_replace($this->_siteFolderPath,'',$fileInfo['path']));
				$fileInfo['pathType']='veryRelative';
			} else {
				$fileInfo['path']=cmfcDirectory::normalizePath($this->_pageFolderPath.'/'.$file);
				$fileInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_pageFolderPathBrowser.'/'.$file);
				$fileInfo['pathType']='relative';				
			}
		}
		if (!file_exists($fileInfo['path'])) {
			$result=false;
		} else {
			$fileInfo['extension']=strtolower(cmfcFile::getFileExtension($fileInfo['path']));
			$fileInfo['generated']['path']=$fileInfo['path'];
			
			$fileInfo=$this->groupGetFileInfoBasedOnGroup($fileInfo,$options['groups']);
		}
		if ($this->_debugEnabled) {
			cmfcHtml::printr($fileInfo);
		}
		#--(End)-->Analyze and preapre file path
		
		#--(Begin)-->Apply optimizations
		if ($result!==false) {
			
			if (in_array('fixIncludePath',$fileOptions['methods'])) {
				$r=$this->methodFixIncludePath($fileInfo);
				if ($r===false) {
				} else {
					$fileInfo=$r;
				}
			}
			
			if (in_array('minify',$fileOptions['methods'])) {
				$r=$this->methodMinify($fileInfo);
				if ($r===false) {
				} else {
					$fileInfo=$r;
				}
			}
			if (in_array('compress',$fileOptions['methods'])) {
				$r=$this->methodCompress($fileInfo);
				if ($r===false) {

				} else {
					$fileInfo=$r;
				}
			}
			
			$result=$fileInfo;
		}
		#--(End)-->Apply optimizations
		
		return $result;
	}
	
	/**
	* Excluding files from optimization or settings specific parameters for them
	* <code>
	* echo $optimizer->getTagsOptimizedVersion('
	* 	<link href="interface/css/common.css" rel="stylesheet" type="text/css" /> 
	*   <link rel="stylesheet" href="interface/javascripts/jquery.colorpicker/css/colorpicker.css" type="text/css" />
    *   <link rel="stylesheet" media="screen" type="text/css" href="interface/javascripts/jquery.colorpicker/css/layout.css" />
    * ',
	* 'files'=>array(
	* 	'interface/css/common.css'=>array(
	* 		//'methods'=>array('compress')
	* 	),
	* 	'/packages/xajax/xajax_js/xajax.js'=>array(//It's already minified
	* 		'methods'=>array()
	* 	),
	* 	'http://general_lib.local/packages/xajax/xajax_js/xajax.js'=>array(
	* 		'methods'=>array('')
	* 	),
	* 	'/packages/xajax/xajax_js/xajax.js'=>array(//This one is not semicolon safe, we need to mention it
	* 		'javascriptRemoveLineBreaks'=>true,
	* 		'methods'=>array()
	* 	)
	* ),
	* 'groups'=>array(//We want the whole folder to be copied to cache folder not only the defined files
	* 	'interface/javascripts/jquery.colorpicker'=>array()
	* )
	* );
	* </code>
	*/
	function getTagsOptimizedVersion($htmlTags,$options) {
		
		#--(Begin)-->Add htaccess file if it does not exists
		$htaccessFilePath=$this->_cacheFolderPath.'/.htaccess';
		$srcLastModified = @filemtime( $htaccessFilePath );
		// The gzip version of the file exists, but it is older
		// than the source file. We need to recreate it...
		if ($srcLastModified-mktime()>3600 or $this->_cacheEnabled!=true or !file_exists( $htaccessFilePath )) {
			$htaccess='
			# Compressed css files
			# Set the encoding sent to the browser
			AddEncoding x-gzip .cgz
			AddType text/css .cgz
			
			# Compressed javascript files
			AddEncoding x-gzip .jgz
			AddType application/x-javascript .jgz
			
			FileETag none
			
			<IfModule mod_expires.c>
			# enable expirations
			ExpiresActive On
			ExpiresDefault "access plus 1 week"
			</IfModule>
			';
			file_put_contents($htaccessFilePath,$htaccess);
			chmod($htaccessFilePath,$this->_defaultFilePermission);
		}
		#--(End)-->Add htaccess file if it does not exists
		
		$options['groups']=$this->groupsPrepare($options['groups']);
		if (preg_match_all('/(src|href)="([^"\?]+)(\?[^"\?]+)?"/si', $htmlTags, $regs)) {
			foreach($regs[2] as $k=>$file) {
				$__file=$this->processFile($file,$options['files'][$file],$options);

				if ($__file['generated']['path']!=$__file['filePath']) {
					$fileRelativePath='/'.str_replace($this->_siteFolderPath,'',$__file['generated']['path']);
					$fileRelativePath=cmfcDirectory::normalizePath($fileRelativePath);
					//echo "$this->_siteFolderPath|{$__file['generated']['path']}|$fileRelativePath<br/><br/>";
					//$fileRelativePath=cmfcDirectory::normalizePath($this->_cacheFolderPathBrowser.'/'.$fileRelativePath);
					//Support for query strings
					$fileRelativePath=$fileRelativePath.$regs[3][$key];
					
				} else {
					$fileRelativePath=$file;
				}

				$htmlTags=str_replace($file,$fileRelativePath,$htmlTags);
			}
		} else {
			return false;
		}
		
		return $htmlTags;
	}
	
	/**
	 * 
	 * @param $fileCompletePath
	 * @return string
	 */
	function getForHtml($fileCompletePath) {
		$fileRelativePath=str_replace('^%##$@'.$_rootPath,'','^%##$@'.$fileCompletePath);
		echo '<script src="'.$fileRelativePath.'" type="text/javascript"></script>';
	}
	
	
	/**
	* Minifies and cache javascript and css by removing unnecceserry chars
	*/
	function methodMinify($fileInfo) {
		#--(Begin)-->Check the possibility of minifying the file
		$canMinify = true;
			#--(Begin)-->Only can do certain formats		
			if (!in_array($fileInfo['extension'],array('js','css','xml','html','htm'))) {
				$canMinify = false;
			}
			#--(End)-->Only can do certain formats
		#--(End)-->Check the possibility of minifying the file
		

		if ($canMinify) {

			$cacheFilePath=$this->getCacheFile($fileInfo['generated']['path'],$fileInfo,'minify');
			
			if ( $this->isFileCacheExpired($cacheFilePath,$fileInfo)) {
				$content=$packed=file_get_contents($fileInfo['generated']['path']);
				
				if ($fileInfo['extension']=='css') {
					$packed=$content;
					$packed=$this->cssFixRelativeUrls($packed,$fileInfo);
					//exit;
				    // remove comments
					$packed = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $packed);
					// remove tabs, spaces, newlines, etc.
					$packed = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $packed);
					$packed = str_replace('{ ', '{', $packed);
					$packed = str_replace(' }', '}', $packed);
					$packed = str_replace('; ', ';', $packed);
					$packed = str_replace(', ', ',', $packed);
					$packed = str_replace(' {', '{', $packed);
					$packed = str_replace('} ', '}', $packed);
					$packed = str_replace(': ', ':', $packed);
					$packed = str_replace(' ,', ',', $packed);
					$packed = str_replace(' ;', ';', $packed);
				}
				if ($fileInfo['extension']=='js') {
					if (!isset($fileInfo['options']['javascriptRemoveLineBreaks'])) {
						$fileInfo['options']['javascriptRemoveLineBreaks']=$this->_javascriptRemoveLineBreaks;
					}
					if ($fileInfo['options']['javascriptRemoveLineBreaks']!==false) {
						$fastDecode=1;
						$encoding=0;
						$specialChar=0;
						$packer = new JavaScriptPacker($content, $encoding, $fastDecode, $specialChar);
						$packed = $packer->pack();
					} else {
						//Output a minified version of example.js.
						$packed=JSMin::minify($packed);
					}
				}
				$error = false;

				$r=file_put_contents($cacheFilePath,$packed);
				if (!$r) {
					chmod($cacheFilePath,0777);
					$error = true;
				}
		
				if ( !$error ) {
					$fileInfo['generated']['path']=$cacheFilePath;
					return $fileInfo;
				}
			
			} else {
				$fileInfo['generated']['path']=$cacheFilePath;
				return $fileInfo;
			}
			
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param $fileInfo
	 * @return string
	 */
	function methodCompress($fileInfo) {		
		/*
		 * List of known content types based on file extension.
		 * Note: These must be built-in somewhere...
		 */
		$knownContentTypes = array(
			"htm"  => "text/html",
			"html" => "text/html",
			"js"   => "text/javascript",
			"css"  => "text/css",
			"xml"  => "text/xml",
			"gif"  => "image/gif",
			"jpg"  => "image/jpeg",
			"jpeg" => "image/jpeg",
			"png"  => "image/png",
			"txt"  => "text/plain"
		);
		
		$contentType = $knownContentTypes[$fileInfo['extension']];
	
		$canCompress = true;
		/*
		 * Let's compress only text files...
		 */		
		$canCompress = $canCompress && ( strpos( $contentType, "text" ) !== false );
		
		/*
		* Finally, see if the client sent us the correct Accept-encoding: header value...
		*/
		$canCompress = $canCompress && ( strpos( $_SERVER["HTTP_ACCEPT_ENCODING"], "gzip" ) !== false );
		
		$canCompress = $canCompress && function_exists('gzwrite');

		if ( $canCompress ) {
			$content='';
			if ($fileInfo['extension']=='css' and !in_array('minify',$fileInfo['options']['methods'])) {
				$content=file_get_contents($fileInfo['generated']['path']);
				$content=$this->cssFixRelativeUrls($content,$fileInfo);
			}

			$cacheFilePath=$this->getCacheFile($fileInfo['generated']['path'],$fileInfo,'compress');
			if ( $this->isFileCacheExpired($cacheFilePath,$fileInfo)) {
				
				file_put_contents($cacheFilePath,$content);
				chmod($cacheFilePath,0777);
				$error = false;

				if ( $fp_out = gzopen( $cacheFilePath, "wb9" ) ) {
					if ( $fp_in = fopen( $fileInfo['generated']['path'], "rb" ) ) {
						if (empty($content)) {
							while( !feof( $fp_in ) ) {
								gzwrite( $fp_out, fread( $fp_in, 1024*512 ) );
							}
							fclose( $fp_in );
						} else {
							gzwrite( $fp_out, $content );
						}
					} else {
						$error = true;
					}
					gzclose( $fp_out );
				} else {
					$error = true;
				}
				if ( !$error ) {
					$fileInfo['generated']['path']=$cacheFilePath;
					return $fileInfo;
				}
			
			} else {
				$fileInfo['generated']['path']=$cacheFilePath;
				return $fileInfo;
			}
			
			
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param unknown_type $packed
	 * @param unknown_type $fileInfo
	 * @return unknown_type
	 */
	function cssFixRelativeUrls($packed,$fileInfo) {
		// Css url path are relative, so we should change their path.
		// because we want to use cached version which is located on another location
		if ($this->_cssFixRelativeUrls===true) {
			preg_match_all('%url\([\'"]?([^/":\'][^":\')]+)[\'"]?\)%si',$packed,$matches);
			if ($this->_debugEnabled) {
				cmfcHtml::printr($matches);
			}
			
			if ($matches) {
				foreach ($matches[1] as $__k=>$match) {
					
					#--(Begin)-->Analyze and preapre file path
					$__file=trim($match);
					$__fileInfo=array();
					$__fileInfo['cssUrl']=$__file;
					if ($__file[0]=='/') {//Absolute path
						$__fileInfo['path']=cmfcDirectory::normalizePath(dirname($fileInfo['path']).$__file);
						$__fileInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_siteFolderPathBrowser.$__file);
						$__fileInfo['pathType']='absolute';
						 
					} elseif (strpos($__file,'://')!==false) {//Remote path
						//ignore
						$__fileInfo['pathType']='remote';
			
					} else {//Relative path
						if (strpos($__file,'../')!==false) {//Support ../
							//echo realpath(dirname($fileInfo['path']).'/'.$__file);
							$__fileInfo['path']=realpath(cmfcDirectory::normalizePath(dirname($fileInfo['path']).'/'.$__file));
							$__fileInfo['pathBrowser']=cmfcDirectory::normalizePath('/'.str_replace($this->_siteFolderPath,'',$__fileInfo['path']));
							$__fileInfo['pathType']='veryRelative';
						} else {
							$__fileInfo['path']=cmfcDirectory::normalizePath(dirname($fileInfo['path']).$__file);
							$__fileInfo['pathBrowser']=cmfcDirectory::normalizePath($this->_pageFolderPathBrowser.'/'.$__file);
							$__fileInfo['pathType']='relative';				
						}
					}
					
					if (file_exists($__fileInfo['path']) and $__fileInfo['pathType']!='remote') {
						$__correctedPathBrowser=$__fileInfo['pathBrowser'];
					} elseif ($__fileInfo['pathType']=='remote') {
						$__correctedPathBrowser=$__file;
					} else {
						$__correctedPathBrowser='invalid';
					}
					$__fileInfo['pathBrowserCorrected']=$__correctedPathBrowser;
					if ($this->_debugEnabled) {
						cmfcHtml::printr($__fileInfo);
					}
					#--(End)-->Analyze and preapre file path								
					
					#--(Begin)-->Correct the path
					if (!empty($__correctedPathBrowser)) {
						if ($this->_debugEnabled) {
							//echo $this->_basePathServer.'/'.$fileRelativePath.'/'.$match."<br />";
							echo $match."|".$__relativePath."|{$__correctedPathBrowser}<br />";
						}
						$__correctedPathBrowser=str_replace($__file,$__correctedPathBrowser,$matches[0][$__k]);
						$packed=str_replace($matches[0][$__k],$__correctedPathBrowser,$packed);
					}
					#--(End)-->Correct the path
				}
			}
			
			if ($this->_debugEnabled) {
				echo $packed;
			}

			//$packed = preg_replace('%url\([\'"]?([^/][^":\')]+)[\'"]?\)%si', 'url('.$fileRelativePath.'/'.'$1)', $packed);
		} else {
		}
		return $packed;
	}
	
	/**
	 *  This function can generate custom cache file for the requested
	 *  file or content, it checks by every call if it's nessary to overwrite the
	 *  cache file
	 *  It returns an array(
	 *  	'htmlIncludeTag'=>'',
	 *  	'cacheFilePath'=>''
	 *  ) 
	 *  
	 *  @param $type //File or String or Text
	 *  @param $source //Can be file name or a text
	 *  @param $method //minify or compress
	 *  @param $format //js or css or even html
	 *  @param $name //If empty file name or md5(source) will be used as cache file name. sample:dirname(__FILE__)./.optimizer:__method__
	 *  @return array //include tag
	 */
	function optimizeCustom($type,$source,$method,$format,$name=null,$options=null) {
		
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	function pageOptimized() {
		ob_start();
		/*
		 * The mkdir function does not support the recursive
		 * parameter in the version of PHP run by Yahoo! Web
		 * Hosting. This function simulates it.
		 */
		
		function mkdir_r( $dir_name, $rights=0777 ) {
		   $dirs = explode( "/", $dir_name );
		   $dir = "";
		   foreach ( $dirs as $part ) {
			   $dir .= $part . "/";
			   if ( !is_dir( $dir ) && strlen( $dir ) > 0 )
				   mkdir( $dir, $rights );
		   }
		}
		
		/*
		 * List of known content types based on file extension.
		 * Note: These must be built-in somewhere...
		 */
		
		$known_content_types = array(
			"htm"  => "text/html",
			"html" => "text/html",
			"js"   => "text/javascript",
			"css"  => "text/css",
			"xml"  => "text/xml",
			"gif"  => "image/gif",
			"jpg"  => "image/jpeg",
			"jpeg" => "image/jpeg",
			"png"  => "image/png",
			"txt"  => "text/plain"
		);
		
		/*
		 * Get the path of the target file.
		 */
		
		if ( !isset( $_GET["uri"] ) ) {
			header( "HTTP/1.1 400 Bad Request" );
			echo( "<html><body><h1>HTTP 400 - Bad Request</h1></body></html>" );
			exit;
		}
		
		/*
		 * Verify the existence of the target file.
		 * Return HTTP 404 if needed.
		 */
		
		if (($src_uri = realpath( $_GET["uri"] )) === false) {
			/* The file does not exist */
			header( "HTTP/1.1 404 Not Found" );
			echo( "<html><body><h1>HTTP 404 - Not Found</h1></body></html>" );
			exit;
		}
		
		/*
		 * Verify the requested file is under the doc root for security reasons.
		 */
		
		$doc_root = realpath( "." );
		
		if (strpos($src_uri, $doc_root) !== 0) {
			header( "HTTP/1.1 403 Forbidden" );
			echo( "<html><body><h1>HTTP 403 - Forbidden</h1></body></html>" );
			exit;
		}
		
		/*
		 * Set the HTTP response headers that will
		 * tell the client to cache the resource.
		 */
		
		$file_last_modified = filemtime( $src_uri );
		header( "Last-Modified: " . date( "r", $file_last_modified ) );
		
		$max_age = 300 * 24 * 60 * 60; // 300 days
		
		$expires = $file_last_modified + $max_age;
		header( "Expires: " . date( "r", $expires ) );
		
		$etag = dechex( $file_last_modified );
		header( "ETag: " . $etag );
		
		$cache_control = "must-revalidate, proxy-revalidate, max-age=" . $max_age . ", s-maxage=" . $max_age;
		header( "Cache-Control: " . $cache_control );
		
		/*
		 * Check if the client should use the cached version.
		 * Return HTTP 304 if needed.
		 */
		
		if ( function_exists( "http_match_etag" ) && function_exists( "http_match_modified" ) ) {
			if ( http_match_etag( $etag ) || http_match_modified( $file_last_modified ) ) {
				header( "HTTP/1.1 304 Not Modified" );
				exit;
			}
		} else {
			error_log( "The HTTP extensions to PHP does not seem to be installed..." );
		}
		
		/*
		 * Extract the directory, file name and file
		 * extension from the "uri" parameter.
		 */
		
		$uri_dir = "";
		$file_name = "";
		$content_type = "";
		
		$uri_parts = explode( "/", $src_uri );
		
		for ( $i=0 ; $i<count( $uri_parts ) - 1 ; $i++ )
			$uri_dir .= $uri_parts[$i] . "/";
		
		$file_name = end( $uri_parts );
		
		$file_parts = explode( ".", $file_name );
		if ( count( $file_parts ) > 1 ) {
			$file_extension = end( $file_parts );
			$content_type = $known_content_types[$file_extension];
		}
		
		/*
		 * Get the target file.
		 * If the browser accepts gzip encoding, the target file
		 * will be the gzipped version of the requested file.
		 */
		
		$dst_uri = $src_uri;
		
		$compress = true;
		
		/*
		 * Let's compress only text files...
		 */
		
		$compress = $compress && ( strpos( $content_type, "text" ) !== false );
		
		/*
		 * Finally, see if the client sent us the correct Accept-encoding: header value...
		 */
		
		$compress = $compress && ( strpos( $_SERVER["HTTP_ACCEPT_ENCODING"], "gzip" ) !== false );
		
		if ( $compress ) {
			$gz_uri = "tmp/gzip/" . $src_uri . ".gz";
		
			if ( file_exists( $gz_uri ) ) {
				$src_last_modified = filemtime( $src_uri );
				$dst_last_modified = filemtime( $gz_uri );
				// The gzip version of the file exists, but it is older
				// than the source file. We need to recreate it...
				if ( $src_last_modified > $dst_last_modified ) {
					unlink( $gz_uri );
				}
			}
		
			if ( !file_exists( $gz_uri ) ) {
				if ( !file_exists( "tmp/gzip/" . $uri_dir ) ) {
					mkdir_r( "tmp/gzip/" . $uri_dir );
				}
				$error = false;
				if ( $fp_out = gzopen( $gz_uri, "wb" ) ) {
					if ( $fp_in = fopen( $src_uri, "rb" ) ) {
						while( !feof( $fp_in ) ) {
							gzwrite( $fp_out, fread( $fp_in, 1024*512 ) );
						}
						fclose( $fp_in );
					} else {
						$error = true;
					}
					gzclose( $fp_out );
				} else {
					$error = true;
				}
		
				if ( !$error ) {
					$dst_uri = $gz_uri;
					header( "Content-Encoding: gzip" );
				}
			} else {
				$dst_uri = $gz_uri;
				header( "Content-Encoding: gzip" );
			}
		}
		
		/*
		 * Output the target file and set the appropriate HTTP headers.
		 */
		
		if ( $content_type ) {
			header( "Content-Type: " . $content_type );
		}
		
		header( "Content-Length: " . filesize( $dst_uri ) );
		readfile( $dst_uri );
		
		ob_end_flush();
		
	}
	
}
