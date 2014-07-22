<?php
/**
* @desc 
*/

/**
* @author Sina Salek
* @author Akbar Nasr Abadi
* @example
* <code>
* 	include('packages/url/url.class.inc.php');
*   $url=cmfcUrl::factory('friendlyV1',array(
* 		'urlCacheEnabled'=>true,
* 		'urlServeType'=>'htaccess',//'htaccess', 'db', 'combined'
* 		'updateUrlSectionsEnabled'=>true //should be disabled in admin
*   ));
* 	echo $url->prepareUrl('?sn=test');
* </code>
* @changes
*   - Porting to GeneralLib (by Sina Salek)
* 	- Encapsulating into a class (by Akbar Nase Abadi)
* 	- Cahce system (by Akbar Nase Abadi)
*   - Base code (By Sina Salek)
*/
class cmfcWebPathFriendlyV1 extends cmfcTableClassesBase2 {
	
	var $_tableName;
	
	//var $defaultValues = array();
	var $_cachedAlias = array();
	var $_urlCacheEnabled = true;
	/**
	* Available modes are : 'htaccess', 'db', 'combined'
	*/
	var $_urlServeType='htaccess';
	/**
	* If enabled, it will add new sections which have the url to url sectionNames column.
	*/
	var $_updateUrlSectionsEnabled=true;
	
	var $_qsvnSectionName='sn';
	var $_qsvnPageType='pt';
	var $_qsvnRefreshUrls='refreshUrls';

	
	/**
	* array('columnName'=>'propertyName')
	* cvColumnName, and _colnColumnName will be created according to
	* this array
	*/
	var $_columnsProperty=array(
		'id'=>'id',
		'alias'=>'alias',
		'url'=>'url',
		'section_names'=>'sectionNames',
		'alternative_urls'=>'alternativeUrls',
		'insert_datetime'=>'insertDateTime',
		'update_datetime'=>'updateDateTime',
	);
	
	/**
	* 
	*/
	function __construct($options) {
		parent::__construct($options);

		if ($this->_urlCacheEnabled===true) {
			$this->_loadFromCache();
		}
	}
	
	function createTable() {
		$sqlQuery="
			CREATE TABLE `$this->_tableName` (
				`$this->_colnId` int(11) NOT NULL auto_increment,
				`$this->_colnAlias` varchar(255) character set utf8 NOT NULL default '',
				`$this->_colnUrl` varchar(255) character set utf8 default NULL,
				`$this->_colnSectionNames` text,
				`$this->_colnAlternativeUrls` text,
				`$this->_colnInsertDateTime` text,
				`$this->_colnUpdateDateTime` text,
				PRIMARY KEY  (`$this->_colnId`),
				UNIQUE KEY `alias_id` (`$this->_colnAlias`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			";
		if (cmfcMySql::exec($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}
			
	function _createSectionFullName()
	{
		return $_GET[$this->_qsvnSectionName].($_GET[$this->_qsvnPageType] ? "_".$_GET[$this->_qsvnPageType] : "");
	}
	
	/**
	* In db and dbCombined mode, all the urls related to the section will
	* be fetched at once using a single query. for new urls ,new query will be
	* execued for the first time
	*/
	function _loadFromCache()
	{
		$sectionFullName=$this->_createSectionFullName();

		$sectionUrlsQuery = "
			SELECT * 
			FROM
				`".$this->_tableName."`
			WHERE
				`".$this->_colnSectionNames."` LIKE '%,".$sectionFullName.",%'
		";

		//$sectionUrls = cmfcMySql::getRowsCustomWithCustomIndex($sectionUrlsQuery, $this->_colnUrl);
		
		$sectionUrls = cmfcMySql::getRowsCustom($sectionUrlsQuery);
		
		if($sectionUrls)
		{
			foreach($sectionUrls as $sectionUrl)
			{
				$this->_cachedAlias[$sectionUrl['url']]['alias'] = $sectionUrl['alias'];
				
				$alternativeUrls = $sectionUrl[$this->_colnAlternativeUrls];
				
				$alternativeUrls = substr($alternativeUrls, 1, strlen($alternativeUrls)-2);
				
				if($alternativeUrls)
				{
					$alternativeUrlsArray = explode(',', $alternativeUrls);
					if($alternativeUrlsArray)
					{
						foreach($alternativeUrlsArray as $alternativeUrlsItem)
						{
							$this->_cachedAlias[$alternativeUrlsItem]['alias'] = $sectionUrl['alias'];
						}
					}
				}
			}
		}
		
	}
	
	function repairAndOrderUrl($url)
	{
		$urlSections = parse_url($url);
		if($urlSections['query'])
		{
			$qsSections = explode("&", $urlSections['query']);
			if($qsSections)
			{
				foreach($qsSections as $qsSection)
				{
					if($qsSection)
					{
						$qsItemArray = explode("=", $qsSection);
						if(!empty($qsItemArray[0]) && !empty($qsItemArray[1]))
							$qsArray[$qsItemArray[0]] = $qsItemArray[1];
					}
				}
				
				if($qsArray)
					ksort($qsArray);
			}
		}
		
		if($qsArray)
		{
			foreach($qsArray as $qsItemKey=>$qsItemValue)
			{
				$newQsArray[] = $qsItemKey."=".$qsItemValue;
			}
			
			$newQs = implode("&", $newQsArray);
		}
		
		$newUrl = $urlSections['path']."?".$newQs.($urlSections['fragment'] ? "#":"").$urlSections['fragment'];
	
		return $newUrl;
		
	}
		
	function registerUrlAlias($url,$alias, $alternativeUrls = NULL) {
		$url = $this->repairAndOrderUrl($url);
		
		if ($alias[strlen($alias)-1]!='/') $alias.='/';
		if ($alias[0]!='/') $alias='/'.$alias;
		if($alternativeUrls && is_array($alternativeUrls))
		{
			foreach($alternativeUrls as $alternativeUrl)
				$convertedAlternativeUrls[] = $this->repairAndOrderUrl($alternativeUrl);
			$alternativeUrlsQuery = ", `".$this->_colnAlternativeUrls."`=',".implode(",", $convertedAlternativeUrls).",'";
		}
		$sqlQuery="INSERT INTO `".$this->$this->_tableName."` SET `".$this->_colnUrl."`='$url',`".$this->_colnAlias."`='$alias',`".$this->_colnSectionNames."`=','".$alternativeUrlsQuery;
		if (cmfcMySql::exec($sqlQuery)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
	function updateUrlAlias($url,$alias) {
		$url = $this->repairAndOrderUrl($url);
		
		if ($alias[strlen($alias)-1]!='/') $alias.='/';
		if ($alias[0]!='/') $alias='/'.$alias;
		$sqlQuery="UPDATE `".$this->$this->_tableName."` SET `".$this->_colnAlias."`='$alias' WHERE `".$this->_colnUrl."`='$url'";
		if (cmfcMySql::exec($sqlQuery)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
	function manipulateAlias($oldAliasPart, $newAliasPart) {
		if(!$oldAliasPart)
			return false;		
		if ($oldAliasPart[strlen($oldAliasPart)-1]!='/') $oldAliasPart.='/';
		if ($oldAliasPart[0]!='/') $oldAliasPart='/'.$oldAliasPart;
		if ($newAliasPart[strlen($newAliasPart)-1]!='/') $newAliasPart.='/';
		if ($newAliasPart[0]!='/') $newAliasPart='/'.$newAliasPart;
		$sqlQuery="UPDATE `".$this->$this->_tableName."` SET `".$this->_colnAlias."`=REPLACE(`".$this->_colnAlias."`, '$oldAliasPart', '$newAliasPart') WHERE `".$this->_colnAlias."` LIKE '%$oldAliasPart%'";
		if (cmfcMySql::exec($sqlQuery)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
	function deleteUrlAlias($url) {
		$url = $this->repairAndOrderUrl($url);
		
		$sqlQuery="DELETE FROM `".$this->$this->_tableName."` WHERE `".$this->_colnUrl."`='$url'";
		if (cmfcMySql::exec($sqlQuery)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function _getAliasUrl($alias) {
		$hash='%^%&^%!#@&^&(()(';
		$aliasStripped=$hash.$alias.$hash;
		$aliasStripped=str_replace(array($hash.'/','/'.$hash,$hash),'',$aliasStripped);
		$sqlQuery="SELECT * FROM `".$this->$this->_tableName."` WHERE `".$this->_colnAlias."` LIKE '$aliasStripped' OR `".$this->_colnAlias."` LIKE '/$aliasStripped/' OR `".$this->_colnAlias."` LIKE '$aliasStripped/' OR `".$this->_colnAlias."` LIKE '/$aliasStripped'";	
		$r=cmfcMySql::loadCustom($sqlQuery);
		if (is_array($r))
			return $r['url'];
		else
			return false;
	}
	
	function _getUrlAlias($url) {
		$url = $this->repairAndOrderUrl($url);
		
		if(isset($this->_cachedAlias[$url]) and $_GET[$this->_qsvnRefreshUrls]!='1')
		{
			return $this->_cachedAlias[$url]['alias'];
		}
		else
		{
			$sqlQuery="SELECT * FROM `".$this->$this->_tableName."` WHERE `".$this->_colnUrl."` LIKE '$url' OR `".$this->_colnAlternativeUrls."` LIKE '%,$url,%' ORDER BY `".$this->_colnId."` DESC";	
			$rows=cmfcMySql::getRowsCustom($sqlQuery);

			if (is_array($rows))
			{
				foreach ($rows as $r) {
					$this->_cachedAlias[$r['url']]['alias'] = $r['alias'];
					
					$alternativeUrls = $r[$this->_colnAlternativeUrls];
					
					$alternativeUrls = substr($alternativeUrls, 1, strlen($alternativeUrls)-2);
					
					if($alternativeUrls)
					{
						$alternativeUrlsArray = explode(',', $alternativeUrls);
						if($alternativeUrlsArray)
						{
							foreach($alternativeUrlsArray as $alternativeUrlsItem)
							{
								$this->_cachedAlias[$alternativeUrlsItem]['alias'] = $r['alias'];
							}
						}
					}
					
					
					if ($this->_updateUrlSectionsEnabled) {
						$sectionFullName=$this->_createSectionFullName();
						$addSectionNameToDbQuery = "UPDATE `".$this->$this->_tableName."` SET `".$this->_colnSectionNames."`=CONCAT(`".$this->_colnSectionNames."`, '".$sectionFullName.",') WHERE `".$this->_colnAlias."`='".$r['alias']."'";
						cmfcMySQl::exec($addSectionNameToDbQuery);
					}
					
					return $r['alias'];
				}
			} else {
				return false;
			}
		}
	}

	function prepareUrl($url) {

		$orgUrl=$url;
		//return $url;
		if (strpos(strtolower('http://'),strtolower($url))===false) 
		{
			if (preg_match_all('/([^?&=]+)=([^?&=]+)/', $url, $regs,PREG_SET_ORDER)) 
			{
				if (preg_match('/(^|\/)([^.\/\?]*\.[^.\/\?]*)/', $url, $myregs)) 
				{
					$scriptFile = $myregs[2];
				}
				
				if(in_array($this->_urlServeType, array("db", "combined")))
				{
					$baseUrl="";
					
					if (!empty($scriptFile)) 
					{
						$baseUrl .= $scriptFile;
					}
					
					$get=array();
					foreach ($regs as $reg) 
					{
						if($reg[1] == "pageNumber" || $reg[1] == "sortBy" || $reg[1] == "sortType" || $reg[1] == "returnTo")
							$pagingUrl .= "$reg[1]/$reg[2]/";
						else
							$get[]="$reg[1]=$reg[2]";
					}
		
					$baseUrl .= "?";
					if($get)
						$baseUrl .= implode("&",$get);
		
					$alias = $this->_getUrlAlias($baseUrl);
		
					if ($alias!==false) 
					{
						$newUrl = $alias.$pagingUrl;
						return $newUrl;
					} 
					else 
					{
						if($this->_urlServeType == "combined")
						{
							$convertedUrl="";
							
							if (!empty($scriptFile)) {
								$convertedUrl.="/cn/".$scriptFile;
							}
							
							//var_dump($regs);
							foreach ($regs as $reg) {
								if ($reg[1]!='qs') {
									if ($reg[1]=='categoryId') $reg[1]='catId';
									if ($reg[1]=='pageNumber') $reg[1]='pn';
									if ($reg[1]=='sortType') $reg[1]='st';
									$convertedUrl.="/$reg[1]/$reg[2]";
								}
							}
						}
						else
							$convertedUrl = '/'.$orgUrl;
						return $convertedUrl;
					}
				}
				else
				{
					$convertedUrl="";
					
					if (!empty($scriptFile)) {
						$convertedUrl.="/cn/".$scriptFile;
					}
					
					//var_dump($regs);
					foreach ($regs as $reg) {
						if ($reg[1]!='qs') {
							if ($reg[1]=='categoryId') $reg[1]='catId';
							if ($reg[1]=='pageNumber') $reg[1]='pn';
							if ($reg[1]=='sortType') $reg[1]='st';
							$convertedUrl.="/$reg[1]/$reg[2]";
						}
					}
					
					return $convertedUrl;
				}
			} 
			else 
			{
				return $url;
			}
		}
		return $url;
	}
	
	function pagingPrepareUrl($obj,$cmd,$params) 
	{
		return $this->prepareUrl($params['url']);
	}

	function prepareGet($get) 
	{
		$__orgGet=$get;
		if (isset($get['qs'])) 
		{
			$orgUrl = $url = $get['qs'];
			
			if(in_array($this->_urlServeType, array("db", "combined")))
			{				
				if($returnPosition = strpos($url, "/returnTo"))
				{
					$returnEndPosition = strpos($url, "/", $returnPosition+1);
					if(FALSE === ($returnEndPosition = strpos($url, "/", $returnEndPosition+1)))
					{
						$returnEndPosition = strlen($url);
					}
					$returnLength = $returnEndPosition - $returnPosition;
					$returnToUrl = substr($url, $returnPosition, $returnLength);
					
					$url = str_replace($returnToUrl, "", $url);
				}
				
				if($pagingPosition = strpos($url, "pageNumber"))
				{
					$pagingUrl = substr($url, $pagingPosition);
					$url = substr($url, 0, $pagingPosition);
				}
		
				preg_match_all('/([^?\/=]+)/', $url, $regs);
				$parts=$regs[1];
				
				$__url=$this->_getAliasUrl($url);
				if ($__url!==false) {
					if (preg_match_all('/([^?&=]+)=([^?&=]+)/', $__url, $regs,PREG_SET_ORDER)) {
						$get=array();
						foreach ($regs as $reg) {
							$get[$reg[1]]=$reg[2];
						}
					}
		
					if (preg_match_all('/([^?\/=]+)\/([^?\/=]+)/', $pagingUrl, $regs,PREG_SET_ORDER)) {
						foreach ($regs as $reg) {
							$get[$reg[1]]=$reg[2];
						}
					}
		
					if (preg_match_all('/([^?\/=]+)\/([^?\/=]+)/', $returnToUrl, $regs,PREG_SET_ORDER)) {
						foreach ($regs as $reg) {
							$get[$reg[1]]=$reg[2];
						}
					}
					
					$get=array_merge($get,$__orgGet);
					return $get;
				} else {
					if($this->_urlServeType == "combined")
					{
						$url = $orgUrl;
						if (preg_match_all('/([^?\/=]+)\/([^?\/=]+)/', $url, $regs,PREG_SET_ORDER)) {
							//cmfcHtml::printr($regs);
							foreach ($regs as $reg) {
								if (strpos($reg[1], '[') !== false){
									$key = str_replace(']', '', $reg[1]);
									$key = explode('[', $key);
									$newGet = &$get;
									foreach ($key as $keyName){
										$newGet = &$newGet[$keyName];
									}
									$newGet = $reg[2];
									continue;
								}
								if ($reg[1]=='catId') $reg[1]='categoryId';
								if ($reg[1]=='pn') $reg[1]='pageNumber';
								if ($reg[1]=='st') $reg[1]='sortType';
								//if ($reg[1]=='lang') $reg[1]='lang';
								$get[$reg[1]]=$reg[2];
							}
						}
						unset($get['qs']);
					}
					else
					{
						$get['sn'] = "pageNotFound";
						unset($get['qs']);
					}
				}
			}
			else
			{
				$url = $orgUrl;
				if (preg_match_all('/([^?\/=]+)\/([^?\/=]+)/', $url, $regs,PREG_SET_ORDER)) {
					//cmfcHtml::printr($regs);
					foreach ($regs as $reg) {
						if (strpos($reg[1], '[') !== false){
							$key = str_replace(']', '', $reg[1]);
							$key = explode('[', $key);
							$newGet = &$get;
							foreach ($key as $keyName){
								$newGet = &$newGet[$keyName];
							}
							$newGet = $reg[2];
							continue;
						}
						if ($reg[1]=='catId') $reg[1]='categoryId';
						if ($reg[1]=='pn') $reg[1]='pageNumber';
						if ($reg[1]=='st') $reg[1]='sortType';
						//if ($reg[1]=='lang') $reg[1]='lang';
						$get[$reg[1]]=$reg[2];
					}
				}
				unset($get['qs']);
			}
		}
		return $get;
		//$this->log(__FUNCTION__, array($sqlQuery, mysql_error(), $returnValue ) );
	}

}
?>