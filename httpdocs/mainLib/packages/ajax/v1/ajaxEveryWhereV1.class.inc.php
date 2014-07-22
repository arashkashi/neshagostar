<?php
/**
* Fast , safe and easy PHP Ajax backend
* Advantages :
* - Very easy to implement, almost with no effort
* - Fully automated default settings
* - Supports even very old browsers with no javascritp support
* - Very easy to debug,just enable debug and click on the link! 
* - Fully customizable
* - Support <javascript> and <style> tag as ajax result! it even understand css and js includes 
* - Fully extensible.
* - Very small footprint if used with optimizer package , including jquery 20kb (5kb+15kb) 
* @version $Id: ajaxEveryWhereV1.class.inc.php 504 2010-01-23 12:06:10Z salek $
* @author sina salek
* @website http://sina.salek.ws 
* @changelog
* 	+ Problem with including exteranl scritps on all containers fixed
* 	+ Conflict with files names function or function workd
* 	+ functions or perhaps classes included in js files or <script> does not load globally!! 
*	+ Warning if user tried to use this package for uploading files and advising him
*		to use flash uploader 
*	+ Letting user to include js files himself and preventing duplicate include while the js 
*		requirement is already include 
* 	+ Fixing IE6 font fade bug : has to fixed in application itself by setting backupgroupd-color:
* 		- http://icant.co.uk/sandbox/msieopacityissue/
*		- When element does not have background color and it's ie6, fading should be disabled  
*   + Sometimes client recieves non compeleted respond and tries to load it
* 		To prevent this either hashing or content size technique like TCP/IP should 
*   + included javascripts (requires optimizer api)
*		+ Implementing compress methods for compressing javascripts
* 		x Ajax class should be includable, so browser can cache it. using .htaccess to allow php parse .js 
* 	+ Compeleting examples : form, customize, extend, simple 
*   + Generic before and after callback Set ajax before and after call , event call back
*   	- Manu features can be implemented externally using this teqniue
*   	- Letting users to set custom actions onFail,onSuccess,onStart per call and per whole call   
*   + More advanced loading indicator: http://preloaders.net
* 		package itself should have some loaders  		
* 	+ javascript part of the class now include as external file so
* 		Browser can cache it
*   + It appears that it can't handle comments in javascript correctly  
*   + Should understand Javascript includes 
*   + Understanding CSS and <style>
* 		- http://stackoverflow.com/questions/805384/how-to-apply-inline-and-or-external-css-loaded-dynamically-with-jquery 
* 	+ simple debug mode implemented, (opens linkutf8ToHtmlEntitiess in new window and halt the action)
*   + Effects moved to client side and the issue with inaccessible newaly added elements fixed
* 	+ XML node finding performance improved
* 	+ support for php4 added
* 	+ Content loaded via ajax no longer works without ajax because of requestedByAjax. actually all the links
* 		which used excludeQueryString function will inherit it
* 	+ Fix jquery issue with elements loaded via innerHtml
* 	+ Jquery ajax does not send submit button value, it needs to be fixed
* 		- If ajax trigger attaches to submit button, that button value will be send 
* @todo 
* 	- Events for when all the containers are loaded
*	- Accepting jquery css selectors as  container : 
*		- Parsing and collecting object numbers at client side
*		- Using phpQuery (Too much overload)
*		- Simple type indicators like class: , id:, name: type:div
*     
*	- Support uploading files : 
*		User should define a global temp folder for pacakge and set handleFormsWithFileUploader=true
*       Then they can also enable and disalbe this paramters for each form by adding them call function paramters
*       In the next version , application can proide user with the upload and send button so form can be send
*       automatically after the has been uploaded.
*       It might also be cool to let user select between different upload file plugins!!
*       - very useful : http://valums.com/wp-content/uploads/ajax-upload/demo-jquery.htm
*		- http://stackoverflow.com/questions/166221/how-to-upload-file-jquery
*		- http://www.malsup.com/jquery/form/jquery.form.js?2.28
*		- http://15daysofjquery.com/multiple-file-upload-magic-with-unobtrusive-javascript/17/
*		- http://www.swfupload.org/documentation	     
*	- Fixing remaining issues with loading indicators and customizing them.
*		- Using tempaltes i think is a good idea.
*	- Throw error if user tried to output data before initilizing the package
*   - Disable trigger click till the loading finished. it should act according to trigger object
*      Prevent twice clicks by mistake. and reclicking the same link before its result comes.
*      reclicking should be on by default but it should be possible to turn it off by request. in case of referesh
* 	   funtoinality    
* 	- Resending request to server in case of fail or non complete response,
* 		and fetching the response from ajax server cache again which held for small period of time.
* 		And it might be also important to throw a non synced gui to user when There is no cache to fetch
*	- Request faild indicator techniqie, user can click on the icon to send the request again 
*	- Remove area if it wasn't found on server response, it means it's deleted
*	- Many useful methods http://www.malsup.com/jquery/form/jquery.form.js?2.28
*   - I need to make sure if it's the result cause of the problem 
*   	Recognizing or even fixing meta tag position
* 		Using this code is only necessery if you don't define html encoding meta right after <head> and like this
*		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
*		chunck the starting part of the html to look for meta tag if it was at the corrrect locatoin
*		there wil be no need to process
* 	- Ability to lock page either with displaying transparent layer or disableing all the events
*   - Ajax sholud load all the included javascript and then load included scripts
* 		I'm not sure if it's really important   
*   - Better debug and javascript error handling. using try catch and reporting which block had issue
*   	- Indicating debug level would be a good idea. result, loading, all
* 		- Implementing try catch in php5 for preventing fatal errors from fetching response 
*   - Optimzing ajax client-server comminucation, but should be optional
*   - innerHtml has serious issue with form tag in IE. it will message the page if it loads via
* 		innerHtml
*   - Back button support
*   - Add better cache support using jquery getIfModified, so if a same link clicked twice , it loads very fast! 
*   - Auto request. possibility to have automatic request funtionality by setting interval to send an sepecfic
* 	   Request again and again or in specific number of times. settings parameters for multiply request separatly
* 	   should be possible as well.
*   - This does not work because import is mixed with an ordinary style! :
* 	   Needs to be separated and include via another <style> tag
* 	   <style>
*		   #inlineStyle {
*			   text-decoration: none;
*		   }
*		   @import url("simple/simple2.css");
*	   </style>
*   - Using regular expression or XML Reader for improving performance
*      http://immike.net/blog/2007/04/06/5-regular-expressions-every-web-programmer-should-know/
*   - Better design for supporting other javascritpt frameworks like facebook js or dojo or YUI          
*/


if (version_compare(phpversion(), "5.0.0", '>=')) {
	if (!class_exists('DomDocument')) {
		trigger_error('PHP DOM extension is require for GeneralLib ajax package',E_USER_ERROR);
	}
} else {
	if (!function_exists('domxml_open_mem')) {
		trigger_error('PHP DOM extension is require for GeneralLib ajax package',E_USER_ERROR);
	}
	if (!function_exists('xslt_ create')) {
		trigger_error('PHP DOM XSLT extension is require for GeneralLib ajax package',E_USER_ERROR);
	}
}


define ('CMF_AjaxEveryWhereV1_Ok',true);
define ('CMF_AjaxEveryWhereV1_Error',2);
define ('CMF_AjaxEveryWhereV1_Incomplete_Response',3);
define ('CMF_AjaxEveryWhereV1_FileUploadNotSupported',4);

/**
 * 
 * @author salek
 */
class cmfcAjaxEveryWhereV1JsCode {
	var $code='';
	function cmfcAjaxEveryWhereV1JsCode($code) {
		$this->code=$code;
	}
}


/**
* This class is meant to simplify the way we write ajax. 
* @package ComboValidation
*/
class cmfcAjaxEveryWhereV1 extends cmfcClassesCore {
	
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
	 * Site browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPathBrowser='';
	
	/**
	 * @var string
	 */
	var $_packageFolderPath='';
	/**
	 * @var string
	 */
	var $_packageFolderPathBrowser='';

	/**
	* Javascript class and function name prefix for preventing
	* duplication
	* @notice Does not have much of a use currently because js files prefix is no dynamic
	* @var string
	*/
	var $_prefix='cmf';
    /**
    * Name of the javascrip variable that will contain ComboValidation class instance
    * @var string
    */ 
	var $_jsInstanceName='myAjax';
	/**
	* 
	* @var boolean
	*/
	var $_defaultStylesEnabled=true;
	/**
	* 
	* @var boolean
	*/
	var $_prepareOnCall=true;
	/**
	* for multilingual website you can change this messages easily
	* @notice do not include following code into class initializing array, because messages name (definitions)
	*   define after object initializing
	*/
	var $_messagesValue=array(
		CMF_AjaxEveryWhereV1_Error=>'Unknown error',
		CMF_AjaxEveryWhereV1_Incomplete_Response=>'Server response is not complete (%recievedSize% of %realSize%), please try again.',
		CMF_AjaxEveryWhereV1_FileUploadNotSupported=>'Uploading files is not supported, you can use http://swfupload.org or similar ways instead of input field'
	);
    /**
    * An array including occured errors
    * @var array
    */
	var $_errorsStack=array();
	
    /**
    * Url query string var for indicating when call to the page
	* performs via ajax
    * @var array
    */
	var $_qsvnAjaxRequest='requestedByAjax';
	/**
	 * 
	 * @var string
	 */
	var $_qsvnElementsId='elementsId';
	
	/**
	* PHP $_GET variable, it would be better if it passed as reference
	*/
	var $_get=array();
	/**
	 * 
	 * @var string
	 */
	var $_requestUri=array();
	
	/**
	* Currently only jquery has implemented
	* @var array
	*/
	var $_ajaxFramework=array(
		'name'=>'jquery',
		'version'=>'1.2.3'
	);
	/**
	 * 
	 * @var array
	 */
	var $_javascriptFramework=array(
		'name'=>'jquery',
		'version'=>'1.2.3'
	);
	
	/**
	 * The amount of time, client wait for server response
	 * @var integer
	 */
	var $_jsTimeout=0;//milliseconds
	
	/**
	 * Global loader configuration
	 * @var return
	 */
	var $_jsLoadingIndicatorGlobal=array(
		//'id'=>'',//Auto generate
		//'class'=>'myAjax_globalLoader',
		//'reportRequests'=>'',
		'enabled'=>true,
		'merge'=>true,//Set to false to ignore default parameters
		'imageUrl'=>'packageUrl:loadingIndicators/3d_global.gif',
		'imageUrlFailed'=>'packageUrl:loadingIndicators/3d_global.gif',
		'position'=>array(
			'area'=>'inside',//outside,inside
			'vertical'=>'top',
			'horizontal'=>'left',
			'type'=>'fixed'//or nothing
		)
	);
	/**
	 * Default loading configuratoin, it's overidable and also overwritable by each trigger by
	 * setting merge parameter to false
	 * For example if user defines only id and set merge to false, use will be responsible
	 * for stlying the id and program only display it
	 * @var array
	 */
	var $_jsLoadingIndicator=array(
		'trigger'=>array(
			'id'=>'',
			'enabled'=>false,
			'merge'=>true,//Set to false to ignore default parameters
			//'class'=>'myAjax_triggerLoader',
			'imageUrl'=>'packageUrl:loadingIndicators/3d_trigger.gif',
			'imageUrlFailed'=>'packageUrl:loadingIndicators/3d_trigger.gif',
			/*
			'effect'=>array(//Can be call back javascript function
				'onStart'=>array(),
				'onSuccess'=>array(),
				'onFail'=>array()
			),
			*/
			'position'=>array(
				'area'=>'outside',//outside,inside
				'vertical'=>'center',
				'horizontal'=>'right',
				'type'=>'absolute'
			)
		),
		'content'=>array(
			'enabled'=>false,
			'merge'=>true,//Set to false to ignore default parameters
			//'class'=>'myAjax_contentLoader',
			'imageUrl'=>'packageUrl:loadingIndicators/3d_content.gif',
			'imageUrlFailed'=>'packageUrl:loadingIndicators/3d_content.gif',
			/*
			'effect'=>array(
				'type'=>'fade'
			),
			*/
			'position'=>array(
				'area'=>'inside',//outside,inside
				'vertical'=>'center',
				'horizontal'=>'center',
				'type'=>'absolute'
			),
			'byId'=>array(
				'$id'=>array(
					//Just like content
				)
			)
		),
		/* If full application disabled was active, this parameter will set to hour glass
		 * by default unless user specify it.
		 */
		/*
		'mouse'=>array(
			'cursor'=>'hourGlass',
			'cursorImageUrl'=>''
		)
		*/
	);
	/**
	* For debug purpose, in seconds
	*/
	var $_delayResponse=0;
	/**
	 * 
	 * @var boolean
	 */
	var $_debugModeEnabled=false;
	/**
	 * 1.Debug server response
	 * 2.Debug internal functions
	 * @var integer
	 */
	var $_debugModeLevel=1;
	/**
	 * 
	 * @var boolean
	 */
	var $_isCalledByAjax=false;
	/**
	 * 
	 * @var string
	 * @private
	 */
	var $_elementsId;
	
	/**
	 * Optimzier packag v1, if avaialble this package can automatically
	 * Optimzie and compress include js and css files.
	 * @var Object
	 */
	var $_optimizerObj;
	
	var $_php4OutputBuffer='';
	
	
	/**
    * Construction
    */
	function __construct($options) {
		$this->_fieldsInfo=array();
		$this->setOptions($options);
		
		if ($this->_get[$this->_qsvnAjaxRequest]=='1') {
			@ob_get_clean();
			ob_start(array(&$this,'outputBufferCallback'));
			
			$this->_isCalledByAjax=true;
			$this->_elementsId=$this->_get[$this->_qsvnElementsId];
			$this->_requestUri = preg_replace("/($this->_qsvnAjaxRequest|$this->_qsvnElementsId|_)=[^=&]*&?/", '', $this->_requestUri);
			
			unset($this->_get['_']);
			unset($this->_get[$this->_qsvnAjaxRequest]);
			unset($this->_get[$this->_qsvnElementsId]);
			
			if (version_compare(phpversion(), "5.0.0", '>=')) {
				register_shutdown_function(array(&$this,'parseOutput'));
			} else {
				//register_shutdown_function('cmfcAjaxEveryWhereV1ShutDown',&$this);
				register_shutdown_function(array(&$this,'parseOutput'));
			}
		}
		//$this->addCommandHandler('validate',array(&$this,'__validate'));
	}
	
	function outputBufferCallback($output) {
		$this->_php4OutputBuffer.=$output;
		return '';
	}

	/**
	 * (non-PHPdoc)
	 * @see beta/cmfcClassesCore#setOptions($options, $merge)
	 */
	function setOptions($options,$merge=false) {
		
		if (!$merge) {
			if (!isset($options['pageFolderPath'])) {
				$options['pageFolderPath']=cmfcDirectory::normalizePath(dirname($_SERVER['SCRIPT_FILENAME']));
			}
			if (!isset($options['pageFolderPathBrowser'])) {
				$options['pageFolderPathBrowser']=cmfcDirectory::normalizePath(dirname($_SERVER['SCRIPT_NAME']));
			}
			if (!isset($options['siteFolderPath'])) {
				$options['siteFolderPath']=$_SERVER['DOCUMENT_ROOT'];
			} 
			if (!isset($options['siteFolderPathBrowser'])) {
				$options['siteFolderPathBrowser']='';
			}
			if (!isset($options['packageFolderPath'])) {
				$options['packageFolderPath']=cmfcDirectory::normalizePath(dirname(__FILE__));
			}
			if (!isset($options['packageFolderPathBrowser'])) {
				
				$fileRelativePath='/'.str_replace($options['siteFolderPath'],'',$options['packageFolderPath']);
				$options['packageFolderPathBrowser']=$options['siteFolderPathBrowser'].cmfcDirectory::normalizePath($fileRelativePath);
			}
		}

		return parent::setOptionsByReference($options,$merge);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see beta/cmfcClassesCore#setOption($name, $value, $merge)
	 */
	function setOption($name,$value,$merge=false) {
		if ($name=='pageFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
		} elseif ($name=='siteFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
		}
		
		return parent::setOptionByReference($name,$value,$merge);
	}
	
	
	/**
	 * This function is responsible for generating server side response
	 * to client request
	 * @return string
	 */
	function parseOutput() {
		if (!empty($this->_delayResponse)) {
			sleep($this->_delayResponse);
		}
		$elementsId=$this->_elementsId;
		$elementsId=explode(',',$elementsId);
		if (!empty($this->_php4OutputBuffer)) {
			$pageHtml=$this->_php4OutputBuffer;
		} else {
			$pageHtml=ob_get_clean();
		}
		
		$areas=$this->getChangeAreasInfo($pageHtml,$elementsId);
		
		$result='';
		#--(Begin)-->fetch elements html
		foreach ($areas as $elementId=>$area) {
			$scripts=$area['scripts'];
			$styles=$area['styles'];
			$scriptsInclude=$area['scriptsInclude'];
			$elementHtml=$area['html'];
				
			$elementHtml=cmfcHtml::phpToJavascript($elementHtml);
			
			$result.=$this->_jsInstanceName.".doEffects(['$elementId'],'fadeOut',{'targetOpacity':0},function () {\n";
				//echo "\$('#$elementId').queue(function(){ \$(this).empty();\$(this).html($elementHtml);\$(this).dequeue();})\n";
				//echo "\$('#$elementId').empty();\n";
				$result.="\$('#$elementId').html($elementHtml);\n";
				
				if (!empty($styles)) {
					foreach ($styles as $v) {
						$result.="\$(".cmfcHtml::phpToJavascript($v).").appendTo(\"head\");\n";
					}
				}
				
				if (!empty($scriptsInclude)) {
					foreach ($scriptsInclude as $v) {
						$result.="\$.getScript('$v',function () {});\n";
					}
				}
				
				//echo "\$('#$elementId').show();";
				if (!empty($scripts)) {
					//$result.=$scripts;
					$result.="\$.globalEval(".cmfcHtml::phpToJavascript($scripts).");\n";
					//$result.="\$.globalEval('function iamafunction2() { alert(\'asdfa\'); \$(\'#functionIncludeViaScriptTag\').css(\'text-decoration\',\'none\');}');\n";
					//$result.="globalEval(".cmfcHtml::phpToJavascript($scripts).");\n";
					//$result.="Function(".cmfcHtml::phpToJavascript($scripts).");\n";
				}
				//$result.="\n$this->_jsInstanceName.doEffects(['$elementId'],'fadeIn');\n";
			$result.="});";
		}
		//echo 'alert("yes");';
		#--(End)-->fetch elements html
		
		#--(Begin)-->Analyze and take action for meta tag redirect or header redirect
		//<meta http-equiv="refresh" content="5;url=http://test.com">
		if (preg_match('/<meta +http-equiv="refresh" +content="([^";]*);url=([^";]+)">/s', $pageHtml, $regs)) {
			$delay = $regs[1]*1000;
			$url = $regs[2];
			$genericScripts.="
				setTimeout(function() {
					window.location='$url';
				},$delay);
			\n";
		} else {
			$genericScripts = "";
		}
		$result.=$genericScripts;
		#--(End)-->Analyze and take action for meta tag redirect or header redirect
		
		#--(Begin)-->Add hash code at the first line
		$result='//-_-_(SIZE:'.(UTF8::strlen($result)+1).')_-_-;'."\n".$result;
		#--(End)-->Add hash code at the first line
		
		if ($this->_debugModeEnabled) {
			echo '<strong>Server Response : </strong><br /><pre>';
		}
		echo $result;
		if ($this->_debugModeEnabled) {
			echo '</pre>';
		}
		
	}
	
	/**
	 * Fetch information each target content
	 * @param $html
	 * @param $elementsId
	 * @return array
	 */
	function getChangeAreasInfo($html,$elementsId) {
		if (version_compare(phpversion(), "5.0.0", '>=')) {
			return $this->getChangeAreasInfoPhp5($html,$elementsId);
		} else {
			return $this->getChangeAreasInfoPhp4($html,$elementsId);
		}
	}
	
	/**
	 * 
	 * @param $html
	 * @param $elementsId
	 * @return unknown_type
	 */
	function getChangeAreasInfoPhp5($html,$elementsId) {
		$result=array();
		$pageHtml=&$html;

		#--(Begin)-->Fetch prepate content
		/*
		Using this code is only necessery if you don't define html encoding meta right after <head> and like this
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		if (function_exists('mb_convert_encoding')) {
			$pageHtml = mb_convert_encoding($pageHtml, 'HTML-ENTITIES', "UTF-8");
		} else {
			$pageHtml=cmfcHtml::utf8ToHtmlEntities($pageHtml);
		}
		*/
		//$pageHtml = cmfcString::removeBom($pageHtml);
		//$pageHtml = cmfcString::convertToUnixFormat($pageHtml);
		#--(End)-->Fetch prepate content
		
		$pageDom = new DomDocument('1.0','UTF-8');
		//$pageDom->preserveWhiteSpace=true;
		//$pageDom->resolveExternals = true;

		@$pageDom->loadHTML($pageHtml);
        
		$xpath = new domxpath($pageDom);
		$xpath ->registerNamespace("html", "http://www.w3.org/1999/xhtml");
		
		if ($this->_debugModeEnabled) {
			echo '<strong>Server Response Details : </strong><br />';
		}
		
		#--(Begin)-->fetch elements html
		foreach ($elementsId as $elementId) {
			if (empty($elementId)) continue;
			$nodes = $xpath->query("//*[@id='$elementId']");

			foreach ($nodes as $node) {
				$scripts='';
				$scriptsInclude='';
				if ($this->_debugModeEnabled) {
					echo cmfcDom::getNodeXPath($node).'/descendant-or-self::script';
				}
				$subNodes=$xpath->query(cmfcDom::getNodeXPath($node).'/descendant-or-self::script');
				foreach ($subNodes as $subNode) {
					
					if ($subNode->getAttribute('src')!='') {
						$scriptsInclude[]=$subNode->getAttribute('src');
					} else {
						#--(Begin)-->Fetch javascript codes
						$cn=$subNode->childNodes;
						foreach ($cn as $cnv) {
							$scripts.="\n".$cnv->nodeValue;
						}
						#--(End)-->Fetch javascript codes
					}
					$subNode->parentNode->removeChild($subNode);
				}
				#--(End)-->Fetch javascript codes
				
				#--(Begin)-->Fetch CSS codes
				$styles=array();
				if ($this->_debugModeEnabled) {
					echo cmfcDom::getNodeXPath($node).'/descendant-or-self::style';
				}
				$subNodes=$xpath->query(cmfcDom::getNodeXPath($node).'/descendant-or-self::style');
				foreach ($subNodes as $subNode) {
					$cn=$subNode->childNodes;
					//$styles.="\n".cmfcDom::getOuterXml($subNode);
					#--(Begin)-->Convert node attributes to html				
					if ($subNode->hasAttributes()) {
						$attributes='';
						foreach ($subNode->attributes as $__attr) {
							$attributes.= " $__attr->name=\"$__attr->value\"";							
						}
					}
					#--(End)-->Convert node attributes to html
					
					#--(Begin)-->Create node html tag
					$__s='<style'.$attributes.'>';
					foreach ($cn as $cnv) {
						$__t=$cnv->nodeValue;
						$__t=str_replace(array('/*<![CDATA[*/','/*]]>*/'),'',$__t);
						$__s.="\n".$__t;
						//$styles.="\n".cmfcDom::getOuterXml($cnv);
					}
					$__s.='</style>';

						#--(Begin)-->Remove and create new entry for any @import inside the style
						//$result = preg_replace('/@import *url *\([\'"][^()]*[\'"]\);?/', '', $subject);
						/*
						foreach () {
							@import url("simple/simple2.css");
						}*/
						#--(End)-->Remove and create new entry for any @import inside the style

					$styles[]=$__s;
					#--(End)-->Create node html tag
					
					$subNode->parentNode->removeChild($subNode);
				}
				#--(End)-->Fetch CSS codes

				#--(Begin)-->Remove script tags
				$elementHtml=cmfcDom::getInnerXml($node);
				#--(End)-->Remove script tags
								
				$result[$elementId]=array(
					'scripts'=>$scripts,
					'scriptsInclude'=>$scriptsInclude,
					'styles'=>$styles,
					'html'=>$elementHtml
				);
				
				if ($this->_debugModeEnabled) {
					$__r=$result[$elementId]=$result[$elementId];
					$__r['html']=htmlspecialchars($__r['html']);
					$__r['scripts']=htmlspecialchars($__r['scripts']);
					cmfcHtml::printr($__r);
				}
			}
		}
		//echo 'alert("yes");';
		#--(End)-->fetch elements html
		//var_dump($result);exit;
		
		return $result;
	}
	
	/**
	 * 
	 * @param $html
	 * @param $elementsId
	 * @return unknown_type
	 */
	function getChangeAreasInfoPhp4($html,$elementsId) {
		$result=array();
		$pageHtml=&$html;
		#--(Begin)-->Fetch prepate content
		/*
		Using this code is only necessery if you don't define html encoding meta right after <head> and like this
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		if (function_exists('mb_convert_encoding')) {
			$pageHtml = mb_convert_encoding($pageHtml, 'HTML-ENTITIES', "UTF-8");
		} else {
			$pageHtml=cmfcHtml::utf8ToHtmlEntities($pageHtml);
		}
		*/
		//$pageHtml = cmfcString::removeBom($pageHtml);
		//$pageHtml = cmfcString::convertToUnixFormat($pageHtml);
		#--(End)-->Fetch prepate content
		$pageDom = @domxml_open_mem($pageHtml,DOMXML_LOAD_PARSING,&$error);
		if ($this->_debugModeEnabled) {
			echo '<strong>Server Response Details : </strong><br />';
			cmfcHtml::printr($error);
		}

		#--(Begin)-->fetch elements html
		foreach ($elementsId as $elementId) {
			if (empty($elementId)) continue;
			$nodes[]=$pageDom->get_element_by_id($elementId);
			

			foreach ($nodes as $node) {
				$elementHtml=$pageDom->dump_node($node);
				
				#--(Begin)-->Fetch javascript codes
				$scripts='';
				$xpathObj=$pageDom->xpath_new_context();
				if ($xpathObj = @$ctx->xpath_eval("//script")) {
					foreach ($xpathObj->nodeset as $subNode) {
						//$cn=$subNode->childNodes;
						//$scripts.="\n".$pageDom->dump_node($node);
						$scripts.="\n".$subNode->get_content();
						//$subNode->parentNode->removeChild($subNode);//Does not work!
					}
					
					$scripts=str_replace(
						array(
							"//<![CDATA[",
							"//]]>"
						),'',$scripts);
				}
				#--(End)-->Fetch javascript codes
				
				
				#--(Begin)-->Fetch css styles
				$scripts='';
				$xpathObj=$pageDom->xpath_new_context();
				if ($xpathObj = @$ctx->xpath_eval("//style")) {
					foreach ($xpathObj->nodeset as $subNode) {
						//$cn=$subNode->childNodes;
						//$scripts.="\n".$pageDom->dump_node($node);
						$scripts.="\n".$subNode->get_content();
						//$subNode->parentNode->removeChild($subNode);//Does not work!
					}
					
					$scripts=str_replace(
						array(
							"//<![CDATA[",
							"//]]>"
						),'',$scripts);
				}
				#--(End)-->Fetch css styles

				#--(Begin)-->Remove script tags
				$elementHtml=preg_replace('%<[^<>]*script *[^<>]*>.*?<[^<>]*\/[^<>]*script *[^<>]*>%si','',$elementHtml);
				#--(End)-->Remove script tags
								
				$result[$elementId]=array(
					'scripts'=>$scripts,
					'html'=>$elementHtml
				);
				
				if ($this->_debugModeEnabled) {
					$__r=$result[$elementId]=$result[$elementId];
					$__r['html']=htmlspecialchars($__r['html']);
					$__r['scripts']=htmlspecialchars($__r['scripts']);
					cmfcHtml::printr($__r);
				}
			}
		}
		//echo 'alert("yes");';
		#--(End)-->fetch elements html
		return $result;
	}
	
	/**
	* Useful when you want to run specific javascript or generate html when
	* page is called via ajax, right near the original code
	*/
	function isCalledByAjax() {
		if ($this->_isCalledByAjax===true) {
			return true;
		}
		return false;
	}
    
    /**
    * The main purpose of this function is optmization, so user program
    * can only parse the part of code which is actually requested by the client
    * It returns allway true when the page is not called via ajax
    */
    function isIdRequestedByAjax($id) {
        $ids=explode(',',$this->_elementsId);
        if (in_array($id,$ids) or !$this->isCalledByAjax()) {
            return true;
        }
        return false;
    }
	
	/**
	* @desc import the patameter to javascript as a javascript code
	*/
	function asJsCode($str) {
		return new cmfcAjaxEveryWhereV1JsCode($str);
	}
	
	/**
	 * 
	 * @param $value
	 * @return boolean
	 */
	function isJsCode($value) {
		if (strtolower(get_class($value))==strtolower('cmfcAjaxEveryWhereV1JsCode')) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Convert php value to javascript
	* @param variant $var
	* @param interger $tab //number of tab chars for indent
	* @param boolean $singleLine //result as single line
	*/
	function phpParamToJs($var,$tabs = 0,$singleLine=true) {
		//if (is_string($var)) return $var;
		if ($this->isJsCode($var)) {
			return $var->code;
		} else {
			return cmfcHtml::phpToJavascript($var,0,true);
		}
	}
	
	/**
	* convert php values to javascript
	* @param variant $var
	*/
	function phpParamsToJs($vars) {
		foreach ($vars as $key=>$var) {
			$vars[$key]=$this->phpParamToJs($var);
		}
		return $vars;
	}
	
	/**
	* fixing javascript incompatibility with number as array key
	* @todo : should be recursive
	* @param string $array
	*/
	function getJavascriptCompatibleArray($array) {
		$result=array();
		foreach ($array as $key=>$value) {
			$key="_$key";
			$result[$key]=$value;
		}
		
		return $result;
	}
	
	/**
	* @shortName getJsCAK
	* @see getJsCAK
	*/
	function getJavascriptCompatibleArrayKey($key) {
		if (is_numeric($key)) $key="_$key";
		$key=$this->phpParamToJs($key);
		return $key;
	}
	
	/**
	* 
	* @param $url //url or formId
	* @param $elementsId
	* @param events
	* @return string 
	*/
	function jsfSimpleCall($url,$elementsId,$options=array()) {
		//$options['jsLoadingIndicator']['global']=$this->_jsLoadingIndicatorGlobal;
		//$options['loadingIndicator']=cmfcArray::mergeRecursive($options['jsLoadingIndicator'],$this->_jsLoadingIndicator);
		$options['loadingIndicator']=$options['jsLoadingIndicator'];
		unset($options['jsLoadingIndicator']);
		
		$options['loadingIndicatorEnabled']=$options['jsLoadingIndicatorEnabled'];
		unset($options['jsLoadingIndicator']);
		
		return $this->_jsInstanceName.'.simpleCall(this,'.$this->phpParamToJs($url).','.$this->phpParamToJs($elementsId).','.$this->phpParamToJs($options).')';
	}
	
	/**
	 * 
	 * @param $elementsId
	 * @param $events
	 * @return string
	 */
	function jsfSimpleCallForATagOnClick($elementsId,$options=array()) {
		return 'return '.$this->jsfSimpleCall($this->asJsCode('this'),$elementsId,$options);
	}
	
	/**
	* assign a javascript function for handling a command
	*/
	function jsfAddObserver($cmd,$handler,$parameters=null) {
		echo $this->_jsInstanceName.'.addObserver('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}
	
	/**
	* assign a javascript function for handling a command
	*/
	function jsfRemoveObserver($cmd,$handler) {
		echo $this->_jsInstanceName.'.removeObserver('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}
	
	/**
	* assign a javascript function for handling a command
	*/
	function jsfRemoveCommand($cmd,$handler) {
		echo $this->_jsInstanceName.'.removeCommandHandler('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}
	
	/**
	* assign a javascript function for handling a command
	*/
	function jsfAddCommandHandler($cmd,$handler,$parameters=null) {
		echo $this->_jsInstanceName.'.addCommandHandler('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}
	
	/**
	* assign a javascript function for handling a command
	*/	
	function jsfPrependCommandHandler($cmd,$handler,$parameters=null) {
		echo $this->_jsInstanceName.'.prependCommandHandler('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}

	/**
	* Run a javascript command
	*/
	function jsfRunCommand($cmd,$params=array()) {
		echo $this->_jsInstanceName.'.runCommand('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($params).')';
	}
    
    
    /**
    * short name version of getJavascriptCompatibleArrayKey()
    * @see getJavascriptCompatibleArrayKey
    */
    function getJsCAK($key) {
        return $this->getJavascriptCompatibleArrayKey($key);
    }
    
    /**
    * Reutrns and array of javascript files & source for better integration with
    * Other softwares
    */
    function getRequirements($options=array()) {
        if (empty($options['instanceName'])) {
            $instanceName=$this->_jsInstanceName;
        } else {
            $instanceName=$this->_jsInstanceName=$options['instanceName'];
        }
        
        $result=array();
        
        if (!isset($options['alreadyIncluded'])) {
            $options['alreadyIncluded']=array();
        }
        if (!in_array('jquery',$options['alreadyIncluded'])) {
            $result['files'][]=array('path'=>$this->_packageFolderPathBrowser.'/lib/jquery-1.2.6.min.js');
        }
        if (!in_array('jquery.browser',$options['alreadyIncluded'])) {
            $result['files'][]=array('path'=>$this->_packageFolderPathBrowser.'/lib/jquery.browser.js');
        }
        
        $result['files'][]=array('path'=>$this->_packageFolderPathBrowser.'/lib/lib.inc.js');
        $result['files'][]=array('path'=>$this->_packageFolderPathBrowser.'/ajaxEveryWhereV1.class.inc.js');
        
        $result['sources'][]=array('source'=>"
            $instanceName=new cmfAjaxEverWhereV1();
            $instanceName.qsvnAjaxRequest=".$this->phpParamToJs($this->_qsvnAjaxRequest,0,true).";
            $instanceName.qsvnElementsId=".$this->phpParamToJs($this->_qsvnElementsId,0,true).";
            $instanceName.loadingIndicatorGlobal=".$this->phpParamToJs($this->_jsLoadingIndicatorGlobal,0,true).";
            $instanceName.loadingIndicatorGlobalEnabled=".$this->phpParamToJs($this->_jsLoadingIndicatorGlobalEnabled,0,true).";
            $instanceName.loadingIndicator=".$this->phpParamToJs($this->_jsLoadingIndicator,0,true).";
            $instanceName.loadingIndicatorEnabled=".$this->phpParamToJs($this->_jsLoadingIndicatorEnabled,0,true).";
            $instanceName.messagesValue=".$this->phpParamToJs($this->getJavascriptCompatibleArray($this->_messagesValue),0,true).";
            $instanceName.packageFolderPathBrowser=".$this->phpParamToJs($this->_packageFolderPathBrowser,0,true).";
            $instanceName.debugModeLevel=".$this->phpParamToJs($this->_debugModeLevel,0,true).";
            $instanceName.timeout=".$this->phpParamToJs($this->_jsTimeout,0,true).";
            $instanceName.debugModeEnabled=".$this->phpParamToJs($this->_debugModeEnabled,0,true).";
            $instanceName.instanceName=".$this->phpParamToJs($instanceName,0,true).";
            ".(($this->_prepareOnPrint==true)?"$instanceName.prepare();":"$instanceName.prepareOnLoad();")
        );
                
        return $result;
    }
    
    /**
    * Print javascript object initilizing scripts
    */
    function printJsInstance($instanceName=NULL) {
        $r=$this->getRequirements(array(
            'instanceName'=>$instanceName
        ));
        $html='';
        foreach ($r['sources'] as $item) {
            $html.="\n".'<script type="text/javascript">'."\n".
                '// <![CDATA['."\n".
                $item['source']."\n".
                '// ]]>'."\n".
                '</script>'."\n";
        }
        echo $html;
    }

	/**
	* include require javascript classes
	*/
	function printJsClass($options=array()) {
        $htmlTags='';
        $r=$this->getRequirements($options);
        foreach ($r['files'] as $file) {
            $htmlTags.="\n".'<script src="'.$file['path'].'" type="text/javascript"></script>';
        }
		
		if (is_object($this->_optimizerObj)) {
			echo $this->_optimizerObj->getTagsOptimizedVersion($htmlTags,array());
		} else {
            echo $htmlTags;
		}
	}
	
    /**
    * 
    */
	function printRequirements($options) {
		$this->printJsClass($options);
		$this->printJsInstance();
	}
}