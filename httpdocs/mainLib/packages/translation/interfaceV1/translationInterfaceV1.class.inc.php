<?php
/**
*
* @version $Id: translationInterfaceV1.class.inc.php 510 2010-01-24 12:29:26Z salek $
* @author sina salek
* @website http://sina.salek.ws
* @company persiantools.com
* @changelog
* 	+ Support for placeholders and smart place holders
* 	+ Integrating with language package
* @todo	
*	- Support for translating texts as well as keys, or generating keys automaticallly from texts
*	- Each word has various modes, url based, section based and generic. so the the same word can appear
*		differently on different pages.  	
* 	- Integrating php gettext, some sort of hook. so it can be used with other php package that use gettext
*/

define ('CMF_TranslationInterfaceV1_Ok',true);
define ('CMF_TranslationInterfaceV1_Error',2);


/**
 * @author salek
 * @todo
 * - Supporting packages' messages localization, method for package to plug themselves into
 *
 */
class cmfcTranslationInterfaceV1 extends cmfcTableClassesBase2 {
	
	/**
	* @var string 
	*/
	var $_tableName='strings';
	
	/**
	 * 
	 * @var integer
	 */
	var $_languageId;
	
	/**
	 * If enabled, object will monitor all words and saves how many times each wrod requested
	 * and from which pages
	 * @var unknown_type
	 */
	var $_statisticsEnabled=true;
	
	/**
	* array('columnName'=>'propertyName')
	* @var array
	*/
	var $_columnsProperty=array(
		'id'=>'id',
		'key'=>'key',
		'value'=>'value',
		'language_id'=>'languageId',
		'cross_language_id'=>'crossLanguageId',
		'total_request'=>'totalRequests',
		'section_names'=>'sectionNames'
	);
	
	/**
	 * Load all of the words in each page using only one query
	 * @var boolean
	 */
	var $_loadPageWordsAtOnceEnabled=false;
	/**
	 * 
	 * @var boolean
	 */
	var $_registerTotalRequestsEnabled=false;
	/**
	 * 
	 * @var array
	 */
	var $_pageWords=array();
	
	/**
	 * Only for backward compatibility
	 * @var unknown_type
	 */
	var $_languageObject;
	
	var $_defaultError=CMF_TranslationInterfaceV1_Error;
	var $_messagesValue=array(
        CMF_TranslationInterfaceV1_Ok	=> 'no error',
        CMF_TranslationInterfaceV1_Error=> 'unknown error',
	);
	
	/**
	* 
	*/
	function __construct($options) {
		parent::__construct($options);
		
		if ($options['loadPageWordsAtOnceEnabled']) {
			$this->loadPageWordsAtOnce();

		}
	}
	
	/**
	* 
	*/
	function createTable() {
		$sqlQuery="			
			CREATE TABLE `$this->_tableName` (
			  `$this->colnId` int(11) NOT NULL auto_increment,
			  `$this->colnKey` varchar(255) NOT NULL default '',
			  `$this->colnValue` text NOT NULL,
			  `$this->colnLanguageId` int(11) NOT NULL default '0',
			  `$this->colnCrossLanguageId` int(11) default NULL,
			  `$this->colnTotalRequests` int(11) default '0',
			  `$this->colnSectionNames` text,
			  PRIMARY KEY  (`$this->_colnId`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			";
		if (cmfcMySql::exec($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *
	 */
	function setOption($name,$value,$merge=false) {
		
		if ($name=='loadPageWordsAtOnceEnabled' and $value==true) {
			$this->loadPageWordsAtOnce();
		}
		if ($name=='languageObject' and is_object($value)) {
			
		}
		
		$r=parent::setOption($name,$value,$merge);

		return $r;
	}

	/**
	 *
	 */
	function _createSectionFullName()
	{
		$this->_sectionFullName = ($this->insideAdmin ? "admin_":"").$_GET['sn'].($_GET['pt'] ? "_".$_GET['pt'] : "");
	}
	
	/**
	* Get the key value according to the section , page type and the page url
	* Keys defined in $replacements do not contain indicator characters.
	* <code>
	* getValue( 'test');
	* getValue( 'test', 
	*	array( 
	*		'%age%' => 10, 
	*		'%name%' => 'Ahmad Ebrahimi'
	*		)
	* );
	* </code> 
	* @param $key
	* @param $replacements //replaces textes like {%varName%} with specified value
	*/
	function getValue($word,$replacements=array()) {
		$returnValue = '';
		
		$word = str_replace("VN_", "", $word);
		$completeWord = $word;
		
		if (!$this->_pageWords[$completeWord])
		{
			$sqlQuery = "
				SELECT * 
				FROM `".$this->_tableName."` 
				WHERE 
					`".$this->colnLanguageId."`='".$this->_languageId."'
					AND 
					`".$this->colnKey."`='".$word."'
			";

			$wordInfo = cmfcMySql::loadCustom($sqlQuery);	
			$wordInfo = cmfcMySql::convertColumnNames($wordInfo, $this->_propertiesColumn);
			
			if($wordInfo)
			{
				$this->_createSectionFullName();
				if(false === strpos($wordInfo[$this->_columnsProperty[$this->colnSectionNames]], ",".$this->_sectionFullName.","))	
				{
					$addSectionNameToDbQuery = "
						UPDATE `".$this->_tableName."` 
						SET 
							`".$this->colnSectionNames."`=CONCAT(`".$this->colnSectionNames."`, '".$this->_sectionFullName.",') 
						WHERE 
							`".$this->colnId."`='".$wordInfo['id']."'";
												
					cmfcMySql::exec($addSectionNameToDbQuery);
					//$this->log(__FUNCTION__, array($sqlQuery , mysql_error()) );
				}
				
				$this->_pageWords[$completeWord] = array(
					'key' => $wordInfo['key'],
					'id' => $wordInfo['id'],
					'value' => $wordInfo['value'],
				);
				$returnValue = $wordInfo['value'];
			}			
			else
			{
				$this->_pageWords[$completeWord] = array(
					'key' => $word,
					'value' => "VN_".$completeWord,
				);
				$returnValue =  $this->_pageWords[$completeWord]['value'];
			}
		}
		else
		{
			$returnValue = $this->_pageWords[$completeWord]['value'];
		}
		
		//$this->log(__FUNCTION__, array($sqlQuery, mysql_error(), $returnValue ) );
		
		
		if($this->_pageWords[$completeWord]['id'])
		{
			if($this->_registerTotalRequestsEnabled)
			{
				$sqlQuery = "
					UPDATE `".$this->_tableName."` 
					SET 
						`".$this->colnTotalRequests."` = `".$this->colnTotalRequests."`+ 1
					WHERE 
						`".$this->colnId."`='".$this->_pageWords[$completeWord]['id']."'
				";
				cmfcMySql::exec($sqlQuery);
				//$this->log(__FUNCTION__, array($sqlQuery , mysql_error()) );
			}
		}
		
		if( is_array( $template))
		{
			$returnValue = str_replace( array_keys( $template), $template, $returnValue);
		}

		return $returnValue;
	}
	
	
	/**
	 *
	 *
	 */
	function loadPageWordsAtOnce()
	{
		$this->_createSectionFullName();

		$sectionWordsQuery = "
			SELECT * 
			FROM
				`".$this->_tableName."`
			WHERE
				`".$this->colnSectionNames."` LIKE '%,".$this->_sectionFullName.",%'
				AND
				`".$this->colnLanguageId."`='".$this->_languageId."'
		";

		$sectionWords = cmfcMySql::getRowsCustomWithCustomIndex($sectionWordsQuery, $this->colnKey);
		$this->_pageWords = $sectionWords;
	}
	
	/**
	 * Removes all the occurances, regarding of langauge
	 * @param $key
	 * @return unknown_type
	 */
	function deleteByKey($key,$languageId=null) {
		
	}
	
	/**
	 *
	 */
	function addForNextLanguage() {
		
	}
	
	/**
	 * Only for backward compatibility
	 * @return unknown_type
	 */
	function getAllLanguages() {
		return $this->_languageObject->getAllLanguages();
	}
	
	function printPageWords($languageInfo,$urlPrefix=null) {
		?>
		<table bgcolor="#FFFFFF" id="wordsList" dir="<?php echo $languageInfo['direction']?>" class="table" width="50%"  align="center"  border="1" cellspacing="0" cellpadding="0" style="border-color:#d4dce7;">
				<tr>
					<td colspan="10" class="table-header" align="<?php echo $languageInfo['align']?>" >
						Current page words
					</td>
				</tr>
				
				<tr>
					<td nowrap="nowrap" class="table-title field-title" style="width:35px" >
						key
					</td>
					
					<td nowrap="nowrap" class="table-title field-title" >
						value
					</td>
					
					<td nowrap="nowrap" class="table-title field-title" >
					action
					</td>
				</tr>
			<?php		
			array_multisort($this->_pageWords);
			/*
			if (!$this->insideAdmin){
				//$returnAddress = '&returnAddress='.urlencode($_SERVER['REQUEST_URI']);
				$urlPrefix = '/'.$this->adminFolderName.'/';
			} else {
				$returnAddress = '';
				$urlPrefix = '';
			}
			*/
			//$this->log(__FUNCTION__, $this->pageWords);

			foreach($this->_pageWords as $key => $value){
				?>
				<tr>
					<td align="ltr" nowrap="nowrap" class="field-title" style="width:35px" >
						<?php echo $key?>
					</td>
					
					<td nowrap="nowrap" class="field-title" >
						<?php echo $value['value']?>
					</td>
	
					
					<td nowrap="nowrap" class="field-title" >
					<?php
					if($value['id']){	
						$link = $urlPrefix.'?sn=words&langId='.$languageInfo['id'].'&lang='.$languageInfo['shortName'].'&action=edit'.$returnAddress.'&id='.$value['id'];
						$title = 'EDIT';
						
					} else {
						$sqlQuery = "
							SELECT * 
							FROM `".$this->_tableName."` 
							WHERE 
								`".$this->colnKey."`='".$key."'
						";
						$wordInfo = cmfcMySql::loadCustom($sqlQuery);	
						$wordInfo = cmfcMySql::convertColumnNames($wordInfo, $this->_propertiesColumn);
						
						if ($wordInfo) {
							$link = $urlPrefix.'?sn=words&langId='.$languageInfo['id'].'&lang='.$languageInfo['shortName'].'&action=edit'.$returnAddress.'&id='.$wordInfo['id'];
						} else {
							$link = $urlPrefix.'?sn=words&langId='.$languageInfo['id'].'&lang='.$languageInfo['shortName'].'&action=new'.$returnAddress.'&key='.$key;
						}
						$title = 'CREATE';
					}
					?>
					<a href='<?php echo $link?>' target='_blank' ><?php echo $title?></a>
					</td>
				</tr>
				<?php
			}
		?>		
		</table>
		<?php
	}
			
}