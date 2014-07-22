<?php
/**
* @author Sina Salek
* @changes
* 	+ ability to add custom where conditions for each section in 
* 	  order to filter specific rows, "customSqlWhereConditions"
* 	  parameters added to section search array
* 	
* @todo
* 
* @version $Id: searchDbCombinedV1.class.inc.php 230 2009-06-30 11:03:53Z salek $
*/


define('CMF_SearchDbCombinedV1_Ok',true);
define('CMF_SearchDbCombinedV1_Error',2);
define('CMF_SearchDbCombinedV1_Does_No_Exsists',3);

if (!class_exists('cmfcClassesCore'))
	trigger_error('cmfcSearch:dbCombinedV1 needs cmfcClassesCore packages/cmf/classesCore.class.inc.php',E_USER_ERROR);
	
if (!class_exists('PEAR'))
	trigger_error('cmfcSearch:dbCombinedV1 needs PEAR package',E_USER_ERROR);

class cmfcSearchDbCombinedV1 extends cmfcClassesCore{
	var $_sessionBaseName='search';
	var $_searchKeywords;
	var $_searchQuery;
	var $_prefix='mySearch';
	var $_titleMaxChars=1000;
	var $_maxResultKeywordsPreview=6;
	/**
	* word,char
	*/
	var $_searchMode='char';
	/**
	* <code>
	* array(
	*   'openIconSrc'=>'',
	*	'closeIconSrc'=>''
	* )
	* </code>
	*/
	var $_images=array();
	
	var $_defaultError=CMF_SearchDbCombinedV1_Error;
	var $_messagesValue=array(
        CMF_SearchDbCombinedV1_Ok	=> 'no error',
        CMF_SearchDbCombinedV1_Error	=> 'unkown error',
        CMF_SearchDbCombinedV1_Does_No_Exsists	=> 'template "%internalName%" does not exists',
	);
	
	function __construct($options) {
		$this->setOptions($options);
	}
	
	var $_sectionsInfo=array();

	/*
	function setOption($name,$value) {
		switch ($name) {
			//case 'permissionSystem': $result=$this->setPermissionSystem($value);break;
			default : $this->{'_'.$name}=$value;;break;
		}
		return $result;
	}
	*/
	
	function setSectionsInfo ($info) {
		
	}
	
	
	function getSearchQuery($keywords,$searchType='and',$sectionsToSearch=array()) {
		$this->_searchQuery='';
		foreach ($this->_sectionsInfo as $sectionName=>$sectionInfo) {
			if (isset($sectionInfo['search']) and (in_array($sectionName,$sectionsToSearch) or empty($sectionsToSearch))) {
				$sectionsForSearchInfo[$sectionName]=$sectionInfo;
			}
		}
		if (!is_array($sectionsToSearch)) $sectionsToSearch=array();

		if (!empty($keywords) and !empty($sectionsForSearchInfo)) {
			$this->_searchKeywords=$searchKeywords;
			if (!@in_array($keywords,$_SESSION[$this->_sessionBaseName]['lastSearchesKeywords'])) {
				$_SESSION[$this->_sessionBaseName]['lastSearchesKeywords'][]=$keywords;
			}

			$sqlQuery='';
			$searchKeywords=cmfcString::countWordMultibyte($keywords,1);
			$this->_searchKeywords=$searchKeywords;

			foreach ($sectionsForSearchInfo as $sectionName=>$sectionInfo) {
				$tableName=$sectionInfo['tableInfo']['tableName'];
				$columnsForSearch=$sectionInfo['search']['columnsForSearch'];
				$resultColumns=$sectionInfo['search']['resultColumns'];
				//should start with OR or AND
				$customSqlWhereConditions=$sectionInfo['search']['customSqlWhereConditions'];

				$columnsForSearchSql='';
				$comma='';
				foreach ($columnsForSearch as $columnName) {
					$columnName=$sectionInfo['tableInfo']['columns'][$columnName];
					$columnsForSearchSql.="$comma`$columnName`";
					$comma=',';	
				}
				
				#--(Begin)-->generating keywords condition sql query
				$whereSqlQuery='';
				$delimiter='';
				foreach ($searchKeywords as $word) {
					$word=mysql_real_escape_string($word);
					$whereSqlQuery.="$delimiter (CONCAT($columnsForSearchSql) LIKE '%$word%')";
					if ($searchType=='or')
						$delimiter=' OR ';
					else
						$delimiter=' AND ';
				}
				#--(End)-->generating keywords condition sql query
				
				#--(Begin)-->append custom sql conditions
				if (!empty($customSqlWhereConditions)) {
					$whereSqlQuery="( $whereSqlQuery ) $customSqlWhereConditions";
				}
				#--(End)-->append custom sql conditions
				
				#--(Begin)-->for counting number of occurances
				/* this sql code counts number word occurance :
					(LENGTH(description)-LENGTH(REPLACE(description,'word','')))/LENGTH('word')
				*/
				$occuranceNumberSql='';
				$concatSql="CONCAT($columnsForSearchSql)";
				$plus='';
				foreach ($searchKeywords as $word) {
					$word=mysql_real_escape_string($word);
					$occuranceNumberSql.="$plus ( (LENGTH($concatSql)-LENGTH(REPLACE(LOWER($concatSql),LOWER('$word'),'')))/LENGTH('$word') )";
					$plus=' + ';
				}
				#--(End)-->for counting number of occurances
				
				#--(Begin)-->prepare columns which will be used in result
				$resultColumnsSql=" '$sectionName' AS 'sectionName' , $occuranceNumberSql as 'rate',$concatSql as 'fullContent'";
				$defaultResultColumns=array (
					'id'=>"id",
					'title'=>"title",
					'description'=>"description"
				);
				
				foreach ($defaultResultColumns as $key=>$defaultResultColumn) {
					$resultColumns[$key]=$sectionInfo['tableInfo']['columns'][$resultColumns[$key]];
					$defaultResultColumn=$key;
					if (isset($resultColumns[$key])) {
						$resultColumnsSql.=", `$resultColumns[$key]` AS '$defaultResultColumn'";
					} else {
						$resultColumnsSql.=", '$defaultResultColumn'";
					}
				}
				#--(End)-->prepare columns which will be used in result
				
				$sqlQuery.="$union\n"."(SELECT $resultColumnsSql FROM `$tableName` WHERE $whereSqlQuery )";
				$union=' UNION ';
			}
			
		//	echo '<pre style="overflow:auto;width:800px;direction:ltr;align:left">'.$sqlQuery.'</pre>';

			$sqlQuery.="\n ORDER BY rate DESC";
		} else {
			return false;
		}
		$this->_searchQuery=$sqlQuery;
		return $sqlQuery;
	}
	

	
	/**
	* @desc Make highlited preview of search content
	*/
	function highlightResults($columnsValues,$searchKeywords=null) {
		if (is_null($searchKeywords)) $searchKeywords=$this->_searchKeywords;
		@$keywordsStr=implode('|',$searchKeywords);
		
		$fullContent=strip_tags($columnsValues['fullContent']);
		
		if (preg_match_all("/(.{40}$keywordsStr.{40})/siu", $fullContent, $result, PREG_PATTERN_ORDER)) {
			$result=array_slice($result[1],0,$this->_maxResultKeywordsPreview);
			$fullContent=implode(' <b>...</b> ',$result);
			$fullContent = preg_replace("/($keywordsStr)/siu", '<span class="'.$this->_prefix.'searchKeywordInSearchPage">$1</span>', $fullContent);
		}
		
		$columnsValues['title'] = preg_replace("/($keywordsStr)/siu", '<span class="'.$this->_prefix.'searchKeywordInSearchPage">$1</span>', $columnsValues['title']);
		$columnsValues['fullContent']=$fullContent;
		
		return $columnsValues;
	}
	
	function printDefaultStyles() { ?>
		<style>
			.<?php echo $this->_prefix?>searchKeyword {
				background-color:yellow;
			}

			.<?php echo $this->_prefix?>searchKeywordInSearchPage {
				font-weight:bold;
				/*color:#FFCC33;*/
			}

			
			.<?php echo $this->_prefix?>SearchResults a{
				font-family:tahoma;
				font-weight:normal;
				text-decoration:none;
				font-size:12px;
			}
			
			.<?php echo $this->_prefix?>SearchResults .title {
				cursor:pointer;
			}
			
			.<?php echo $this->_prefix?>SearchResults .content {
				font-family:tahoma;
				font-weight:normal;
				text-decoration:none;
				font-size:12px;
				padding-right:40px;	
				text-align:justify
			}
			
			.<?php echo $this->_prefix?>SearchResults .container {
				margin-bottom:8px;	
			}
			
			.<?php echo $this->_prefix?>SearchResults .imgButton {
				margin-bottom:0px;
			}
		</style>
	<?php }
	
	function printDefaultScripts() {?>
		<script language="javascript" type="text/javascript">
		
			function <?php echo $this->_prefix?>ShowHideContent(number,mode) {
				var id='<?php echo $this->_prefix?>searchResult'+number+'-content';
				var buttonId='<?php echo $this->_prefix?>searchResult'+number+'-button';
				var element=document.getElementById(id);
				var button=document.getElementById(buttonId);
				if (element) {
					if (element.style.display=='none' && mode!='onlyHide') {
						element.style.display='';
						button.src="<?php echo $this->_images['closeIconSrc']?>";
						return true;
					}
					else if (mode!='onlyShow') {
						element.style.display='none';
						button.src="<?php echo $this->_images['openIconSrc']?>";
						return true;
					}
				}
				return false;
			}
		</script>
	<? }
	
	function printResults($rows) {
	?>
		<div class="<?php echo $this->_prefix?>SearchResults">
			<?php
			$index=0;
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$index++;
					$sectionTitle=$this->_sectionsInfo[$row['sectionName']]['title'];
					$rate=cmfcString::convertNumbersToFarsi(round($row['rate']));
					$row=$this->highlightResults($row, $this->_searchKeywords);
					
					$searchKeywords=implode(' ',$this->_searchKeywords);
					$sectionLink='?sn='.$row['sectionName'].'&id='.$row['id']."&searchKeywords=$searchKeywords";
					$customSectionLink=$this->runCommand('getLink',$row);
					if (!empty($customSectionLink)) {
						$sectionLink=$customSectionLink;
					}
				?>
				<div class="container" >
					<div class="title" >
						» <img class="imgButton" id="<?php echo $this->_prefix?>searchResult<?php echo $index?>-button" src="<?php echo $this->_images['openIconSrc']?>" border="0" onClick="<?php echo $this->_prefix?>ShowHideContent('<?php echo $index?>')" alt="detials" align="absmiddle" />
						<a href="<?php echo $sectionLink?>" >
							<?php echo cmfcString::briefText($row['title'],$this->_titleMaxChars)?> ( بخش "<?php echo $sectionTitle?>" | <?php echo $rate?> یافته )
						</a>						
					</div>
					<div class="content" id="<?php echo $this->_prefix?>searchResult<?php echo $index?>-content" style="display:none">
						<b>...</b> <?php echo $row['fullContent']?> <b>...</b>
					</div>
				</div>
				<?php
				}
			} else  {?>
				<div style="text-align:center">
					<br/>
					نتیجه ای برای نمایش وجود ندارد
				</div>
			<?php }?>
		</div>
	<?php
	}
}