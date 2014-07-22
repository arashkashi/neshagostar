<?php
//class wscTranslation{
class cmfcTranslationAlpha{

	var $wsOptions = array();
	var $autoDetect = true;
	var $languagesTable = '';
	var $wordsTable = '';
	var $pageWords = '';
	var $languageInfo = array();
	var $debugInfo = array();
	var $debugMode = false;
	var $defaultValues = array();
	var $cachedWords = array();
	var $wordCacheEnabled = false;
	var $logTotalRequest = true;
	var $insideAdmin = false;
	var $_sectionFullName = '';
	var $adminFolderName = '';
	
	function __construct($options = array() ){
		$this->wsOptions = $options;
		$translationOptions = $this->wsOptions['translation'];
		$this->setOptions($translationOptions);
		$this->setOption('insideAdmin', $this->wsOptions['insideAdmin']);
		if (!$this->defaultValues){
			$this->defaultValues = array(
				'align' => 'left',
				'direction' => 'ltr',
				'calendarType' => 'gregorian',
				'language' => ($this->insideAdmin ? $this->wsOptions['siteInfo']['defaultLanguage'] : $this->wsOptions['siteInfo']['defaultViewLanguage']),
				'adminFolderName' => 'admin',
			);
		}
		
		if ($this->autoDetect){
			$this->languageInfo['shortName'] = $_GET['lang'];
			
		if (empty($this->languageInfo['shortName']))
			$this->languageInfo['shortName'] = $this->defaultValues['language'];
		}

		if (!$this->languagesTable)
			$this->languagesTable = $this->wsOptions['physicalTables']['languages'];
		if (!$this->wordsTable)
			$this->wordsTable = $this->wsOptions['physicalTables']['words'];
		
		if (!$this->adminFolderName)
			$this->adminFolderName = $this->defaultValues['adminFolderName'];
		
		$this->languageInfo = $this->getLanguageInfo();
		$this->logTotalRequest = $this->wsOptions['debugModeEnabled'];
		//$this->logTotalRequest = false;
	}
	
	function log($functionName, $data){
		if ($this->debugMode){
			$this->debugInfo[$functionName][] = $data;
		}
	}
	
	function printLog(){
		if ($this->debugMode){
			cmfcHtml::printr($this->debugInfo);
		}
	}
	
	function wscTranslation($options = array() ){
		$this->__construct($options);
	}
	
	function setOptions($options){
		foreach ($options as $key => $value)
			$this->setOption($key, $value);
	}
	
	function setOption($name, $value)
	{
		$this->$name = $value;
		if($name == "wordCacheEnabled" && $value)
		{
			$this->setCacheEnabledOption();
		}
	}
	
	function getOption($name){
		return $this->$name;
	}
	
	function getLanguageIdByShortName($shortName = NULL){
		if (is_null($shortName))
		{
			$shortName = $this->languageInfo['shortName'];
			if($this->languageInfo['id'])
				return $this->languageInfo['id'];
		}
		
		$r = cmfcMySql::getColumnValue(
			$shortName,
			$this->languagesTable['tableName'],
			$this->languagesTable['columns']['shortName'],
			$this->languagesTable['columns']['id']
		);
		return $r;
	}
	
	function getLanguageShortNameById($id = NULL){
		if (is_null($id))
		{
			$shortName = $this->languageInfo['id'];
			if($this->languageInfo['shortName'])
				return $this->languageInfo['shortName'];
		}

		$r = cmfcMySql::getColumnValue(
			$id,
			$this->languagesTable['tableName'],
			$this->languagesTable['columns']['id'],
			$this->languagesTable['columns']['shortName']
		);
		return $r;
	}
	
	function getLanguageInfo($shortName = NULL){
		//cmfcHtml::printr($this->languageInfo);
		if (is_null($shortName))
			$shortName = $this->languageInfo['shortName'];
		
		$languageInfo = cmfcMySql::load(
			$this->languagesTable['tableName'],
			$this->languagesTable['columns']['shortName'],
			$shortName
		);
		//cmfcHtml::printr(cmfcMySql::getRegisteredQueries());
		$languageInfo = cmfcMySql::convertColumnNames($languageInfo, $this->languagesTable['columns']);
		if (!$languageInfo['calendarType'])
			$languageInfo['calendarType'] = $this->defaultValues['calendarType'];
		
		if (!$languageInfo['align'])
			$languageInfo['align'] = $this->defaultValues['align'];
		
		if (!$languageInfo['direction'])
			$languageInfo['direction'] = $this->defaultValues['direction'];
		
		if (!$languageInfo['sName'])
			$languageInfo['sName'] = $languageInfo['shortName'];
		
		if (!$languageInfo['dbLang'])
			$languageInfo['dbLang'] = '_'.$languageInfo['shortName'];
		
		if (!$languageInfo['dashLang'])
			$languageInfo['dashLang'] = '-'.$languageInfo['shortName'];
		
		if (!$languageInfo['dbBigLang'])
			$languageInfo['dbBigLang'] = ucfirst($languageInfo['shortName']);
		
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
			$languageInfo['!'.$item] = $info [ $languageInfo[$item] ];
		}
		
		return $languageInfo;
	}
	
	function _createSectionFullName()
	{
		$this->_sectionFullName = ($this->insideAdmin ? "admin_":"").$_GET['sn'].($_GET['pt'] ? "_".$_GET['pt'] : "");
	}
	
	function setCacheEnabledOption()
	{
		$this->_createSectionFullName();

		$sectionWordsQuery = "
			SELECT * 
			FROM
				`".$this->wordsTable['tableName']."`
			WHERE
				`".$this->wordsTable['columns']['sectionNames']."` LIKE '%,".$this->_sectionFullName.",%'
				AND
				`".$this->wordsTable['columns']['languageId']."`='".$this->languageInfo['id']."'
		";

		$sectionWords = cmfcMySql::getRowsCustomWithCustomIndex($sectionWordsQuery, $this->wordsTable['columns']['key']);
		
		$this->pageWords = $sectionWords;
	}
		
	/**
	* Modified By Mojtaba Eskandari on 2008-12-03
	* Ex:
	* wsfGetValue( 'test');
	* wsfGetValue( 'test', 
	*	array( 
	*		'%age%' => 10, 
	*		'%name%' => 'Ahmad Ebrahimi'
	*		)
	*	);
	*/
	function getValue( $word, $template = NULL /*$options = NULL*/)
	{
		$returnValue = '';
		
		$_insideAdmin = $this->insideAdmin;
		
		$word = str_replace("VN_", "", $word);
		$completeWord = $word;
		
		if (!$this->pageWords[$completeWord])
		{
			$sqlQuery = "
				SELECT * 
				FROM `".$this->wordsTable['tableName']."` 
				WHERE 
					`".$this->wordsTable['columns']['languageId']."`='".$this->languageInfo['id']."'
					AND 
					`".$this->wordsTable['columns']['key']."`='".$word."'
			";

			$wordInfo = cmfcMySql::loadCustom($sqlQuery);	
			$wordInfo = cmfcMySql::convertColumnNames($wordInfo, $this->wordsTable['columns']);
			
			if($wordInfo)
			{
				$this->_createSectionFullName();
				if(false === strpos($wordInfo['sectionNames'], ",".$this->_sectionFullName.","))
				{
					$addSectionNameToDbQuery = "
						UPDATE `".$this->wordsTable['tableName']."` 
						SET 
							`".$this->wordsTable['columns']['sectionNames']."`=CONCAT(`".$this->wordsTable['columns']['sectionNames']."`, '".$this->_sectionFullName.",') 
						WHERE 
							`".$this->wordsTable['columns']['id']."`='".$wordInfo['id']."'";
							
					cmfcMySql::exec($addSectionNameToDbQuery);
					$this->log(__FUNCTION__, array($sqlQuery , mysql_error()) );
				}
				
				$this->pageWords[$completeWord] = array(
					'key' => $wordInfo['key'],
					'id' => $wordInfo['id'],
					'value' => $wordInfo['value'],
				);
				$returnValue = $wordInfo['value'];
			}			
			else
			{
				$this->pageWords[$completeWord] = array(
					'key' => $word,
					'value' => "VN_".$completeWord,
				);
				$returnValue =  $this->pageWords[$completeWord]['value'];
			}
		}
		else
		{
			$returnValue = $this->pageWords[$completeWord]['value'];
		}
		
		$this->log(__FUNCTION__, array($sqlQuery, mysql_error(), $returnValue ) );
		
		
		if($this->pageWords[$completeWord]['id'])
		{
			if($this->logTotalRequest)
			{
				$sqlQuery = "
					UPDATE `".$this->wordsTable['tableName']."` 
					SET 
						`".$this->wordsTable['columns']['totalRequest']."` = `".$this->wordsTable['columns']['totalRequest']."`+ 1
					WHERE 
						`".$this->wordsTable['columns']['id']."`='".$this->pageWords[$completeWord]['id']."'
				";
				cmfcMySql::exec($sqlQuery);
				$this->log(__FUNCTION__, array($sqlQuery , mysql_error()) );
			}
		}
		
		if( is_array( $template))
		{
			$returnValue = str_replace( array_keys( $template), $template, $returnValue);
		}

		return $returnValue;
	}
	
	function getPageWords(){
		//cmfcHtml::printr($this);
		if(!$this->mode)
			return '';
		?>
		<table bgcolor="#FFFFFF" id="wordsList" dir="<?=$this->languageInfo['dir']?>" class="table" width="50%"  align="center"  border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
				<tr>
					<td colspan="10" class="table-header" align="<?=$this->languageInfo['align']?>" >
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
			<?		
			array_multisort($this->pageWords);
			
			if (!$this->insideAdmin){
				//$returnAddress = '&returnAddress='.urlencode($_SERVER['REQUEST_URI']);
				$urlPrefix = '/'.$this->adminFolderName.'/';
			}
			else{
				$returnAddress = '';
				$urlPrefix = '';
			}
			$this->log(__FUNCTION__, $this->pageWords);

			foreach($this->pageWords as $key => $value){
				?>
				<tr>
					<td align="ltr" nowrap="nowrap" class="field-title" style="width:35px" >
						<?=$key?>
					</td>
					
					<td nowrap="nowrap" class="field-title" >
						<?=$value['value']?>
					</td>
	
					
					<td nowrap="nowrap" class="field-title" >
					<?
					if($value['id']){	
						$link = $urlPrefix.'?sn=words&langId='.$this->languageInfo['id'].'&lang='.$this->languageInfo['shortName'].'&action=edit'.$returnAddress.'&id='.$value['id'];
						$title = 'EDIT';
					}		
					else{
						$sqlQuery = "
							SELECT * 
							FROM `".$this->wordsTable['tableName']."` 
							WHERE 
								`".$this->wordsTable['columns']['key']."`='".$key."'
						";
						$wordInfo = cmfcMySql::loadCustom($sqlQuery);	
						$wordInfo = cmfcMySql::convertColumnNames($wordInfo, $this->wordsTable['columns']);
						
						if($wordInfo)
							$link = $urlPrefix.'?sn=words&langId='.$this->languageInfo['id'].'&lang='.$this->languageInfo['shortName'].'&action=edit'.$returnAddress.'&id='.$wordInfo['id'];
						else
							$link = $urlPrefix.'?sn=words&langId='.$this->languageInfo['id'].'&lang='.$this->languageInfo['shortName'].'&action=new'.$returnAddress.'&key='.$key;
						$title = 'CREATE';
					}
					?>
					<a href='<?=$link?>' target='_blank' ><?=$title?></a>
					</td>
				</tr>
				<?
			}
		?>		
		</table>
		<?
	}
	
	function getAllLanguages(){
		$query = "SELECT * FROM ".$this->languagesTable['tableName'];
		$items = cmfcMySql::getRowsCustom($query);
		if (is_array($items)){
			foreach ($items as $key=>$item){
				$items[$key] = cmfcMySql::convertColumnNames($item, $this->languagesTable['columns']);
			}
		}
		return $items;
	}
	
	function createTraditionalLangInfo(){
		$langInfo = $this->languageInfo;
		$langInfo['htmlAlign'] = $this->languageInfo['align'];
		$langInfo['htmlDir'] = $this->languageInfo['direction'];
		$langInfo['htmlNAlign'] = $this->languageInfo['!align'];
		$langInfo['htmlNDir'] = $this->languageInfo['!direction'];
		return $langInfo;
	}
	
	function getNextLanguage($id){
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
?>