<?php
/**
 * 
 * @package cmf
 * @subpackage beta
 * @todo all of its classes are not separate classes, and should be remove as of generallib3
 * @author Sina Salek
 * @version $Id: common.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
 */
require_once('base.class.inc.php');
require_once('tableClassesBase.class.inc.php');



define('CMF_UrlQueryString_Ok',true);
define('CMF_UrlQueryString_Error',2);

 /**
 *
 *
 * @todo 
 *		- ability to merge $_GET to $_POST
 *
 */

class cmfcUrlQueryString {
	function factory($name,$configs) {
		if ($name=='beta') {
			return new cmfcUrlQueryStringBeta($configs);
		}
	}
}

class cmfcUrlQueryStringBeta extends cmfcBaseClass 
{
	//qs_vars=query string variables
	var $_defaultQsVars=array();
	var $_publicQsVars=array();
	var $_originalQueryString;
	//var $query_string;
	var $_encryptionStatus=false;
	var $_encryptionQsVarName='eqs';
	var $_encryptionKey='irany';
	
	var $_defaultError=CMF_UrlQueryString_Error;
	var $_messagesValue=array(
        CMF_UrlQueryString_Ok	 => 'no error',
        CMF_UrlQueryString_Error => 'unknown error',
	);
	
	function __construct($configs)
	{
		$this->setConfigs($configs);
		$this->_originalQueryString=&$_GET;//$_SERVER['QUERY_STRING'];//
		$this->_defaultQsVars[$this->_encryptionQsVarName]=false;
		$this->decrypt();
	}
	
	function setConfigs($configs) {
		if (isset($configs['defaultQsVars'])) $this->setDefaultQsVars($configs['defaultQsVars']);
		if (isset($configs['publicQsVars'])) $this->setPublicQsVars($configs['publicQsVars']);
		if (isset($configs['originalQueryString'])) $this->setOriginalQueryString($configs['originalQueryString']);
		if (isset($configs['encryptionStatus'])) $this->setEncryptionStatus($configs['encryptionStatus']);
		if (isset($configs['encryptionQsVarName'])) $this->setEncryptionQsVarName($configs['encryptionQsVarName']);
		if (isset($configs['encryptionKey'])) $this->setEncryptionKey($configs['encryptionKey']);
		return parent::setConfigs($configs);
	}
	
	/**
	* @version $Id: common.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
	*/
	function setDefaultQsVars($value) {
		$this->_defaultQsVars=$value;
	}
	
	function setPublicQsVars($value) {
		$this->_publicQsVars=$value;
	}
	
	function setOriginalQueryString($value) {
		$this->_originalQueryString=$value;
	}
	
	function setEncryptionStatus($value) {
		$this->_encryptionStatus=$value;
	}
	
	function isEncryptionEnabled() {
		return $this->_encryptionStatus;
	}
	
	function setEncryptionQsVarName($value) {
		$this->_encryptionQsVarName=$value;
	}
	
	function setEncryptionKey($value) {
		$this->_encryptionKey=$value;
	}
	
	
	function encrypt($queryString,$encryption=null)
	{
		if (is_null($encryption)) {$encryption=$this->_encryption;}
		if ($encryption==true) {
			return cmfEncryptQueryString($queryString,$this->_encryptionKey,$this->_encryptionQsVarName);
		}
		return $query_string;
	}
	
	
	function decrypt()
	{
		if ($this->isEncryptionEnabled()) {
			if (isset($this->_originalQueryString[$this->_encryptionQsVarName]))
			{
				$qs=cmfDecryptQueryString($this->_originalQueryString[$this->_encryptionQsVarName],$this->_encryptionKey,$this->_encryptionQsVarName);
				$array=cmfQueryStringToArray($qs);
				unset($this->_originalQueryString[$this->_encryptionQsVarName]);
				$this->_originalQueryString=cmfcPhp4::array_merge($this->_originalQueryString,$array);
			}
		}
	}

	
	function getQueryString($qs_vars=null,$type=OQS_WITHOUT,$query_string=null,$encryption=null,$result_as_array=false)
	{
		$qs_vars=array_merge($this->_defaultQsVars,$this->_publicQsVars,$qs_vars);
		//echo '<pre>';print_r($qs_vars);echo '</pre>';
		if (is_null($query_string)) {$query_string=$this->_originalQueryString;}
		$query_string=cmfObtainQueryString($qs_vars,$type,$query_string,$result_as_array);
		if ($result_as_array==false)
		{
			return $this->encrypt($query_string,$encryption);
		} else {
			return $query_string;
		}
	}
}




/**
* Description: calculates the micro time
*/
class cmfcMicroTimer {
	function factory($name,$configs=null) {
		if ($name=='easy') {
			return new cmfcMicroTimerEasy();
		}
	}
}


class cmfcMicroTimerEasy
{
	
	function start() {
		global $starttime;
		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;
	}

	function stop() {
		global $starttime;
		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round (($endtime - $starttime), 5);
		return $totaltime;
	}
}

define('CMF_Language_Ok',true);
define('CMF_Language_Error',2);
define('CMF_Language_Error_Unsupported_Language',3);
define('CMF_Language_Error_Empty_Language_Name',4);
define('CMF_Language_Error_Unknown_Short_Name',5);

class cmfcLanguage {
	function factory($name,$configs) {
		if ($name=='beta') {
			return new cmfcLanguageBeta($configs);
		}
	}
}

class cmfcLanguageBeta extends cmfcBaseClass {
	var $_dynamicSystemStatus=true;
	
	var $htmlAlign;
	var $htmlDir;
	var $htmlInvAlign; //it means Inverse
	var $htmlInvDir;
	var $encoding;
	var $shortName;
	var $dbLang;
	var $name;
	var $nativeName;
	var $direction;
	var $_defaultError=CMF_Language_Error;
	var $_messagesValue=array(
        CMF_Language_Ok	=> 'no error',
        CMF_Language_Error_Unsupported_Language => 'there is no information available for this language',
        CMF_Language_Error_Unknown_Short_Name => 'unknown short name',
        CMF_Language_Error_Empty_Language_Name     => 'language name does not specified',
        CMF_Language_Error     => 'unknown error',
	);
	
	var $_rtlLanguages=array('farsi','arabic','hebrew');
	/* this array keys should be lower case because array keys are case sensitive*/
	var $_languagesInfo=array(
			'english'=>array(
				'name'=>'English',
				'nativeName'=>'English',
				'shortName'=>'en',
				'encoding'=>'UTF-8',
				'direction'=>'Left To Right',
			),
			'farsi'=>array(
				'name'=>'Farsi',
				'nativeName'=>'فارسي',
				'shortName'=>'fa',
				'encoding'=>'UTF-8',
				'direction'=>'Right To Left',
			)
		);
	
	function __construct($configs) {
		$this->setConfigs($configs);
		//return $this->set($languageName);
	}
	
	function setConfigs($configs) {
		if (isset($configs['languagesInfo'])) $this->setLanguagesInfo($configs['languagesInfo']);
		return parent::setConfigs($configs);
	}
	
	function setLanguagesInfo($value) {
		$this->_languagesInfo=$value;
	}
	
	function getNameByShortName($shortName) {
		foreach ($this->_languagesInfo as $languageInfo) {
			if ($languageInfo['shortName']==$shortName)
				return $languageInfo['name'];
		}
		
		return $this->raiseError(null,CMF_Language_Error_Unknown_Short_Name,
							PEAR_ERROR_RETURN,NULL, 
							array('shortName'=>$shortName));
	}
	
	function set($languageName) {
		
		if (array_key_exists(strtolower($languageName),$this->_languagesInfo)) {
			$languageInfo=$this->_languagesInfo[strtolower($languageName)];

			$this->arrayToProperties($languageInfo);
			
			if ($this->direction=='Right To Left') {
				$this->htmlAlign='right';
				$this->htmlInvAlign='left';
				$this->htmlDir='rtl';
				$this->htmlInvDir='ltr';
			} else {
				$this->htmlAlign='left';
				$this->htmlInvAlign='right';
				$this->htmlDir='ltr';
				$this->htmlInvDir='rtl';
			}
			return true;
		} else {
			if (!empty($languageName)) {
				return $this->raiseError(null,CMF_Language_Error_Unsupported_Language,
								PEAR_ERROR_RETURN,NULL, 
								array('languageName'=>$languageName));
			} else {
				return $this->raiseError(null,CMF_Language_Error_Empty_Language_Name,
								PEAR_ERROR_RETURN,NULL, 
								array('languageName'=>$languageName));
			}
		}
	}
}








?>