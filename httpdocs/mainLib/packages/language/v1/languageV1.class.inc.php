<?php
/**
* @version $Id: languageV1.class.inc.php 446 2009-10-31 07:18:03Z salek $
*/
define('CMF_LanguageV1_Ok',true);
define('CMF_LanguageV1_Error',2);
define('CMF_LanguageV1_Error_Unsupported_Language',3);
define('CMF_LanguageV1_Error_Empty_Language_Name',4);
define('CMF_LanguageV1_Error_Unknown_Short_Name',5);

/**
* 
*/
class cmfcLanguageV1 extends cmfcTableClassesBase2 {
	
	/**
	 * 
	 * @var unknown_type
	 */
	var $_tableName;
	
	/**
	 * 
	 * @var unknown_type
	 */
	var $_currentLanguageShortName;
	
	/**
	 * Load the default language when the selected on was not avaiable
	 */
	var $_loadDefaultWhenUnavailable=false;
	
	/**
	 * By default it uses is_default langauge to find the selected language
	 */
	var $_defaultLanguageShortName=null;
	
	/**
	* Uses database for storing and retreving langauges info, instead of array
	*/
	var $_dbModeEnabled=false;
		
	/**
	* array('columnName'=>'propertyName')
	*/
	var $_columnsProperty=array(
		'id'=>'id',
		'name'=>'name',
		'short_name'=>'shortName',
		'english_name'=>'englishName',
		'direction'=>'direction',
		'align'=>'align',
		'encoding'=>'encoding',
		'calendar_type'=>'calendarType',
		'is_default'=>'isDefault'
	);
	

	/**
	 * 
	 * @var unknown_type
	 */
	var $_defaultError=CMF_Language_Error;
	/**
	 * 
	 * @var unknown_type
	 */
	var $_messagesValue=array(
        CMF_LanguageV1_Ok	=> 'no error',
        CMF_LanguageV1_Error_Unsupported_Language => 'there is no information available for this language',
        CMF_LanguageV1_Error_Unknown_Short_Name => 'unknown short name',
        CMF_LanguageV1_Error_Empty_Language_Name     => 'language name does not specified',
        CMF_LanguageV1_Error     => 'unknown error',
	);
	
	/**
	* This array keys should be lower case because array keys are case sensitive
	* It'll be filed from database if there was any database connection available
	*/
	var $_languagesInfo=array(
		'english'=>array(
			'id'=>1,
			'englishName'=>'English',
			'name'=>'English',
			'shortName'=>'en',
			'encoding'=>'UTF-8',
			'direction'=>'ltr',
			'calendarType'=>'gregorian',
			'isDefault'=>true
		),
		'farsi'=>array(
			'id'=>2,
			'englishName'=>'Farsi',
			'name'=>'ظپط§ط±ط³ظٹ',
			'shortName'=>'fa',
			'encoding'=>'UTF-8',
			'direction'=>'rtl',
			'calendarType'=>'jalali',
			'isDefault'=>false
		)
	);
	
	/* Generated fields */
	/*	
	var $cvHtmlAlign;
	var $cvHtmlDir;
	var $cvHtmlInverseAlign;
	var $cvHtmlInverseDir;
	var $cvDbLang;
	var $cvDbLang;
	var $cvDashLang;
	var $cvDbBigLang;
	*/
		
	/**
	 * 
	 * @param $options
	 * @return unknown_type
	 */
	function __construct($options) {
		parent::__construct($options);
		$this->fetchAllFromDatabase();
		$this->loadAuto();
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see v2/cmfcTableClassesBase2#createTable()
	 */
	function createTable() {
		$sqlQuery="
			CREATE TABLE `behin2_languages` (
				`$this->colnId` int(11) NOT NULL auto_increment,
				`$this->colnName` text NOT NULL,
				`$this->colnShortName` varchar(255) default NULL,
				`$this->colnEnglishName` text NOT NULL,
				`$this->colnDirection` text NOT NULL,
				`$this->colnAlign` text,
				`$this->colnCalendarType` text,
				`$this->colnIsDefault` int(1),
			PRIMARY KEY  (`$this->_colnId`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			";
		if (cmfcMySql::exec($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	function upgradeTable() {
		
	}
	
	
	function fetchAllFromDatabase() {
		
		if ($this->_dbModeEnabled===true) {
			$query = "SELECT * FROM ".$this->_tableName;
			$this->_languagesInfo=array();
			$items = cmfcMySql::getRowsCustom($query);

			if (is_array($items)){
				foreach ($items as $key=>$item){
					$item = cmfcMySql::convertColumnNames($item, $this->_propertiesColumn);
					$this->_languagesInfo[strtolower($item['englishName'])] = $item;
				}
			}
		} else {
			//Non db mode
		}
	}
	
	/**
	 * 
	 */
	function getKeyByColumnName($columnName,$columnValue) {
		$name=cmfcMySql::getVirtualColumnValueLike($columnValue,$this->_languagesInfo,$columnName,'englishName');
		
		if ($name!==false) {
			$name=strtolower($name);
			$result=$name;
		} else {
			$result=false;
		}
		return $result;
	}
	
	/**
	 * This is the base function of all load functions
	 */
	function loadByColumnName($columnName,$columnValue) {
		$key=$this->getKeyByColumnName($columnName,$columnValue);

		if ($key!==false) {
			$l=cmfcMySql::convertColumnNames($this->_languagesInfo[$key],$this->_propertiesColumn);
			$this->columnsValuesToProperties($l);
			$this->generateAdditionalParameters();
			$result=true;
		} else {
			$result=false;
		}
		return $result;
	}
	
	
	/**
	 * 
	 * @param $shortName
	 * @return unknown_type
	 */
	function loadByShortName($shortName) {
		return $this->loadByColumnName('shortName',$shortName);
	}
	
	/**
	 * 
	 */
	function loadDefaultLanguage() {
		if (!empty($this->_defaultLanguageShortName)) {
			$result=$this->loadByShortName($this->_defaultLanguageShortName);
		} else {
			$result=$this->loadByColumnName('isDefault',1);
		}
		
		return $result;
	}
	
	/**
	 *
	 */
	function loadAuto() {
		$this->loadDefaultLanguage();
		if (empty($this->_currentLanguageShortName)) {
			$this->_currentLanguageShortName=$this->cvShortName;
		}
		$this->clearColumnsProperties();
		$r=$this->loadByShortName($this->_currentLanguageShortName);

		if ($r===false and $this->_loadDefaultWhenUnavailable) {
			$this->loadDefaultLanguage();
			$this->_currentLanguageShortName=$this->cvShortName;
			$result=true;
		} else {
			$result=true;
		}

		return $result;
		//
	}
	

	
	/**
	* @previousNames getLanguageIdByShortName
	*/
	function getIdByShortName($shortName){
		$key=$this->getKeyByColumnName('shortName',$shortName);
		if ($key!==false) {
			$result=$this->_languagesInfo[$key]['id'];
		} else {
			$result=false;
		}
		return $result;
	}
	
	/**
	* @previousNames getLanguageShortNameById
	*/
	function getShortNameById($id){
		$key=$this->getKeyByColumnName('id',$shortName);
		if ($key!==false) {
			$result=$this->_languagesInfo[$key]['shortName'];
		} else {
			$result=false;
		}
		return $result;
	}	
	
	function getNameByShortName($shortName) {
		$key=$this->getKeyByColumnName('shortName',$shortName);
		if ($key!==false) {
			$result=$this->_languagesInfo[$key]['name'];
		} else {
			$result=false;
		}
		return $result;
	}
	
	/**
	* Generate additional parameters according to base parameters. like making nAlign 
	* out of Align which is mirror of Align.
	*/
	function generateAdditionalParameters(){
		/* Generated fields */	
		$this->cvHtmlAlign=$this->cvAlign;
		$this->cvHtmlDir=$this->cvDirection;
		$this->cvHtmlInverseAlign=($this->cvAlign=='left')?'right':'left';
		$this->cvHtmlInverseDir=($this->cvDirection=='ltr')?'rtl':'ltr';
		$this->cvDbLang='_'.$this->cvShortName;
		$this->cvDashLang='_'.$this->cvShortName;
		$this->cvDbBigLang=ucfirst($this->cvShortName);
	}
	
	
	function getAllLanguages() {
		if ($this->_dbModeEnabled===true) {
			
			$query = "SELECT * FROM `".$this->_tableName.'`';
			$items = cmfcMySql::getRowsCustom($query);
			$columnsProperty=array_flip($this->_columnsProperty);
			if (is_array($items)){
				foreach ($items as $key=>$item){
					$items[$key] = cmfcMySql::convertColumnNames($item, $columnsProperty);
				}
			}
			
		} else {
			//Non db mode
		}
		return $items;
	}
	
	/**
	 * 
	 * @return array
	 */
	function getLanguageInfo() {
		$langInfo=array();
		foreach ($this->_columnsProperty as $properyName) {
			$langInfo[$properyName]=$this->{'cv'.ucfirst($properyName)};
		}
		
		#--(Begin)--> For backward compatibility
		$reverseItems = array(
			'align' => array(
				'left' => 'right',
				'right' => 'left',
			), 
			'direction' => array(
				'ltr' => 'rtl',
				'rtl' => 'ltr',
			),
		);
		foreach ($reverseItems as $item => $info){
			$langInfo['!'.$item] = $info [ $langInfo[$item] ];
		}
		$langInfo['htmlAlign'] = $this->cvHtmlAlign;
		$langInfo['htmlDir'] = $this->cvHtmlDir;
		$langInfo['htmlNAlign'] = $this->cvHtmlInverseAlign;
		$langInfo['htmlNDir'] = $this->cvHtmlInverseDir;
		$langInfo['htmlInverseAlign']=$this->cvHtmlInverseAlign;
		$langInfo['HtmlInverseDir']=$this->cvHtmlInverseDir;
		$langInfo['dbLang']=$this->cvDbLang;
		$langInfo['dashLang']=$this->cvDashLang;
		$langInfo['dbBigLang']=$this->cvDbBigLang;
		$langInfo['sName']=$this->cvShortName;
		#--(End)--> For backward compatibility
		
		return $langInfo;
	}
	
	/**
	 * 
	 * @param $id
	 * @return string
	 */
	function getNextLanguage($id) {
		$items = $this->getAllLanguages();
		if($items){
			foreach($items as $key=>$item){
				if($id == $item['id']){
					if(isset($items[$key+1])){
						$nextLang = $items[$key+1]['shortName'];
					}else{
						$nextLang = $items[0]['shortName'];
					}
				}
			}
		}
		return $nextLang;
	}
}