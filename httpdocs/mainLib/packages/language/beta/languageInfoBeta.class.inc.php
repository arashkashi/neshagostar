<?php
/**
* @version $Id: languageInfoBeta.class.inc.php 379 2009-09-14 05:40:44Z salek $
*/

define('CMF_LanguageInfoBeta_Ok',true);
define('CMF_LanguageInfoBeta_Error',2);
define('CMF_LanguageInfoBeta_Error_Unsupported_Language',3);
define('CMF_LanguageInfoBeta_Error_Empty_Language_Name',4);
define('CMF_LanguageInfoBeta_Error_Unknown_Short_Name',5);


class cmfcLanguageInfoBeta extends cmfcClassesCore {
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
        CMF_LanguageInfoBeta_Ok	=> 'no error',
        CMF_LanguageInfoBeta_Error_Unsupported_Language => 'there is no information available for this language',
        CMF_LanguageInfoBeta_Error_Unknown_Short_Name => 'unknown short name',
        CMF_LanguageInfoBeta_Error_Empty_Language_Name     => 'language name does not specified',
        CMF_LanguageInfoBeta_Error     => 'unknown error',
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
				'nativeName'=>'ظپط§ط±ط³ظٹ',
				'shortName'=>'fa',
				'encoding'=>'UTF-8',
				'direction'=>'Right To Left',
			)
		);
	
	function __construct($options) {
		$this->setOptions($options);
		//return $this->set($languageName);
	}
	
	function setOptions($options) {
		if (isset($options['languagesInfo'])) $this->setLanguagesInfo($options['languagesInfo']);
		return parent::setConfigs($options);
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
