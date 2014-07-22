<?php
/**
 * @version v2 $Id: pagingDbV2.class.inc.php 264 2009-08-04 12:05:19Z salek $
 * @author Sina Salek
 * 
 * @changes
 * 	- should use cmfcClassesCore
 * 	- it's possible now to use paging for integer indexed arrays
 * 	- _from and _to will be fill automatically according to page number
 * @todo
 *
*/

define('CMF_RichPaging_Ok',true);
define('CMF_RichPaging_Error',2);
define('CMF_RichPaging_Error_Invalid_Sort_Type',3);
define('CMF_RichPaging_Error_Invalid_Boolean_Value',4);

define('CMF_PagingDbV2_Ok',true);
define('CMF_PagingDbV2_Error',2);
define('CMF_PagingDbV2_Error_Invalid_Sort_Type',3);
define('CMF_PagingDbV2_Error_Invalid_Boolean_Value',4);

class cmfcPagingDbV2 extends cmfcClassesCore
{
	var $_defaultError=CMF_PagingDbV2_Error;
	var $_messagesValue=array(
        CMF_PagingDbV2_Ok		=> 'no error',
        CMF_PagingDbV2_Error	=> 'unknown error',
        CMF_PagingDbV2_Error_Invalid_Sort_Type => 'sort type "__value__" is not valid, possible values are "DESC","ASC"',
        CMF_PagingDbV2_Error_Invalid_Boolean_Value => 'value "__value__" is not valid, possible values are true , false',
	);

	var $_prefix='';
	
	var $_qsvnId='pid';
	var $_qsvnFrom='from';
	var $_qsvnTo='to';
	var $_qsvnNext='next';
	var $_qsvnPrev='prev';
	var $_qsvnSortBy='sortBy';
	var $_qsvnSortType='sortType';
    var $_qsvnPageNumber='pageNumber';
    
    var $_sortBySortTypeSeparator='|';

	var $_wordNext='Next';
	var $_wordPrev='Previous';
	
	var $_total;
	var $_from=0;
	var $_to;
	var $_limit=40;
	var $_link;
	var $_beginValue=1;
	var $_sqlQuery;
	
	var $_firstPageNumber=1;
	var $_totalPages;
	var $_pageNumber;
	var $_id;
	var $_defaultPageNumber=1;
	
	/**
	* automatically fill require properties via page query string
	*/
	var $_autoRecognizeEnabled=true;
	/**
	* make name of query strings an their values as short as possible
	*/
	var $_compactModeEnabled=true;
	/**
	* When limit is 1 package will include a unique identifier
	* to each query string to make the page bookmarkable
	*/
	var $_staticLinkEnabled=false;
	/**
	* sql query unique column name
	*/
	var $_colnId='id';

	var $_prevFrom;
	var $_prevTo;
    var $_prevPageNumber;
	var $_prevId;
	var $_nextFrom;
	var $_nextTo;
    var $_nextPageNumber;
	var $_nextId;

	var $_hasNext;
	var $_hasPrev;

	var $_sortBy='id';
	var $_sortType='ASC';
	var $_sortingEnabled=false;
	
	function __construct($options)
	{
		$this->setOptions($options);
		$this->prepare();
	}
	
	
	
	function _getTotalRowsNumberViaSqlQuery($sqlQuery) {
        if (is_object($this->_oStorage))
            return $this->_oStorage->getTotalRowsNumberViaSqlQuery($sqlQuery);
		else
            return cmfcMySql::getTotalRowsNumberViaSqlQuery($sqlQuery);
	}
	
	function _getLimitedSqlQuery($sqlQuery,$from,$length) {
        if (is_object($this->_oStorage))
		    return $this->_oStorage->getLimitedSqlQuery($sqlQuery,$from,$length);
        else
            return cmfcMySql::getLimitedQuery($sqlQuery,$from,$length);
	}
	 
	function _getSortedSqlQuery($sqlQuery,$byColumnName,$sortType='asc') {
		if(!is_array($sortType))
			$sortType=explode($this->_sortBySortTypeSeparator,$sortType);
		if(!is_array($byColumnName))
			$byColumnName=explode($this->_sortBySortTypeSeparator,$byColumnName);

        if (is_object($this->_oStorage))
		    return $this->_oStorage->getSortedSqlQuery($sqlQuery,$byColumnName,$sortType);
        else
            return cmfcMySql::getSortedQuery($sqlQuery,$byColumnName,$sortType);
	}
	
	
	
	function setOption($name,$value) {
		switch ($name) {
			case 'link': $result=$this->setLink($value); break;
			case 'sortType': $result=$this->setSortType($value); break;
			case 'sortingEnabled': $result=$this->setSortingEnabled($value); break;
			default : $result=parent::setOption($name,$value); break;
		}
		return $result;
	}
	
	function setLink($value) {
		$str="$this->_qsvnId|$this->_qsvnFrom|$this->_qsvnTo|$this->_qsvnNext|$this->_qsvnPrev|$this->_qsvnSortBy|$this->_qsvnSortType|$this->_qsvnPageNumber";
		$value = preg_replace('/(^|[&\?])('.$str.')=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnId.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnFrom.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnTo.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnNext.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnPrev.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnSortBy.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnSortType.'=[^=&]*/', '', $value);
		//$value = preg_replace('/(^|[&\?])'.$this->_qsvnPageNumber.'=[^=&]*/', '', $value);
		$this->_link=$value;
	}
	
	function setSortType($value) {
		$this->_sortType=$value;
		return true;
		if (!is_array($value)){
			$value=strtoupper($value);
			if ($value!='DESC' and $value!='ASC' ) {
				return $this->raiseError(null,CMF_PagingDbV2_Error_Invalid_Sort_Type,
						PEAR_ERROR_RETURN,NULL, 
						array('sortType'=>$value));
			} else {
				$this->_sortType=$value;
			}
			$this->_sortType=$value;
		}
		else
		{
			$this->_sortType=$value;
		}
	}
	
	function setSortingEnabled($value) {
		if ($value!==true and $value!==false ) {
			return $this->raiseError(null,CMF_PagingDbV2_Error_Invalid_Boolean_Value,
					PEAR_ERROR_RETURN,NULL, 
					array('sortingEnabled'=>$value));
		} else {
			$this->_sortingEnabled=$value;
		}
	}
	
	function prepare() {
		if (!empty($this->_sqlQuery) and empty($this->_total)) {
			$this->_total=$this->_getTotalRowsNumberViaSqlQuery($this->_sqlQuery);
		}
		
		if ($this->_autoRecognizeEnabled) {
			if (!empty($_REQUEST[$this->_qsvnSortType])) {
				$this->_sortType=$_REQUEST[$this->_qsvnSortType];
			} elseif (empty($this->_sortType)) {
				$this->_sortType='ASC';
			}
				
			if (!empty($_REQUEST[$this->_qsvnSortBy])) {
				$this->_sortBy=$_REQUEST[$this->_qsvnSortBy];
			}
		
			if (!empty($_REQUEST[$this->_qsvnId])) {
				$this->_id=$_REQUEST[$this->_qsvnId];
			}
				
			if (empty($_REQUEST[$this->_qsvnPageNumber])) {	
				$this->_from=$_REQUEST[$this->_qsvnFrom];
				$this->_to=$_REQUEST[$this->_qsvnTo];
			} else {
				$this->_pageNumber=$_REQUEST[$this->_qsvnPageNumber];
			}
		}
		
		#--(Begin)-->calculations
		$this->_totalPages=$this->getNumberOfPages();
		if (empty($this->_pageNumber) and !empty($this->_from)) {
			$this->_pageNumber=$this->getPageNumberByFrom($this->_from);
		}
		if (empty($this->_pageNumber)) {
			$this->_pageNumber=$this->_defaultPageNumber;
		}
		
		if (!empty($this->_id) and $this->_staticLinkEnabled and $this->_limit==1) {
			$this->_pageNumber=$this->getPageNumberById($this->_id);
		}
			
		if ($this->_pageNumber<$this->_totalPages) {
			$this->_hasNext=true;
			$this->_nextPageNumber=$this->_pageNumber+1;
		}
		
		if ($this->_pageNumber>1) {
			$this->_hasPrev=true;
			$this->_prevPageNumber=$this->_pageNumber-1;
		}
		
		list($this->_from,$this->_to)=$this->getPageRange($this->_pageNumber);
		#--(End)-->calculations
	}
	

	function hasNext() {
		return $this->_hasNext;
	}

	function hasPrev() {
		return $this->_hasPrev;
	}
	
	function getTotalPages() {
		return $this->_totalPages;
	}

	function getPageNumber() {
		return $this->_pageNumber;
	}


	function getNumberOfPages() {
		return ceil($this->_total/$this->_limit);
	}
	
	function getFirstRowNumber() {
		return ($this->getPageNumber()-1)*$this->_limit;
	}
	
	function getRowNumber($num) {
		return ($this->getPageNumber()-1)*$this->_limit + $num;
	}
	
	/**
	* its result is array(0=>'from',1=>'to');
	* @changes
	* 	- wrong calculation of last page fixed by Akbar NasrAbadi
	*/
	function getPageRange($pageNumber=null) {
		if (is_null($pageNumber)) $pageNumber=$this->_pageNumber;
		if ($pageNumber<$this->_totalPages) {
			$result[0]=$pageNumber*$this->_limit-$this->_limit+$this->_beginValue;
			$result[1]=$result[0]+$this->_limit-1;
		} elseif($this->_totalPages>0) {
			$result=array((($this->_totalPages-1)*$this->_limit)+1,$this->_total);
		} else {
			$result=array(0,0);
		}
		return $result;
	}
	
	/**
	* its result is array('from','to');
	*/
	/*
	function getPageNumberByFrom($from=null) {
		if (is_null($from)) {$from=$this->_from;}
		$pageNumber=floor(($from+$this->_limit+1)/$this->_limit);
		if ($pageNumber<$this->_firstPageNumber) {$pageNumber=1;}
		return $pageNumber;
	}
	*/

	
	function getPreparedSqlQuery($pageNumber=null) {
		if (is_null($pageNumber)) $pageNumber=$this->_pageNumber;
		if (is_null($sqlQuery)) {$sqlQuery=$this->_sqlQuery;}

		if ($this->_sortingEnabled)
			$sqlQuery=$this->getSortedSqlQuery($sqlQuery);
		$sqlQuery=$this->getLimitedSqlQuery($pageNumber, $sqlQuery);
		return $sqlQuery;
	}
	
	
	function getLimitedSqlQuery($pageNumber=null,$sqlQuery=null)
	{
		if (is_null($pageNumber)) $pageNumber=$this->_pageNumber;
		$sqlQuery=$this->getCustomLimitedSqlQuery($pageNumber, $sqlQuery);
		return $sqlQuery;
	}

	function getSortedSqlQuery($sqlQuery=null)
	{
		if (is_null($sqlQuery)) {$sqlQuery=$this->_sqlQuery;}
		if (!empty($this->_sortBy))
			return $this->_getSortedSqlQuery($sqlQuery,$this->_sortBy,$this->_sortType);
		else
			return $sqlQuery;
	}

	function getNextLimitedSqlQuery() {
		return $this->getPreparedSqlQuery($this->_pageNumber+1,$this->_sqlQuery);
	}

	function getPrevLimitedSqlQuery() {
		return $this->getPreparedSqlQuery($this->_pageNumber-1,$this->_sqlQuery);
	}

	function getCustomLimitedSqlQuery($pageNumber, $sqlQuery=null) {
		if (is_null($sqlQuery)) {$sqlQuery=$this->_sqlQuery;}
		list($from,$to)=$this->getPageRange($pageNumber);
		$from=$from-$this->_beginValue;
		if ($from<0) $from=0;
		
		return $this->_getLimitedSqlQuery($sqlQuery,$from,$this->_limit);
	}
	
	
	function getIdByPageNumber($pageNumber) {
		$sqlQuery=$this->getPreparedSqlQuery($pageNumber);
		$row=cmfcMySql::loadCustom($sqlQuery);
		if (is_array($row))
			 return $row[$this->_colnId];
		return false;
	}
	
	function getPageNumberById($id) {
		if ($this->_sortingEnabled)
			$sqlQuery=$this->getSortedSqlQuery($this->_sqlQuery);
		else
			$sqlQuery=$this->_sqlQuery;
		return cmfcMySql::getRowNumber($sqlQuery,$this->_colnId,$id);
	}


	function getUrl($pageNumber=null, $sortBy=null , $sortType=null , $link=null)
	{
		if (is_null($sortBy)) {$sortBy=$this->_sortBy;}
		if (is_null($sortType)) {$sortType=$this->_sortType;}
		if (strtoupper($sortType)=='ASC'){$sortType='ASC';}
		if (strtoupper($sortType)=='DESC'){$sortType='DESC';}		
		if (is_null($link)) {$link=$this->_link;}
		if (is_null($pageNumber)) {$pageNumber=$this->_pageNumber;}

		$result=$link;
		if (strpos($link,'?')===false) {$result.='?';} else {$result.='&';}
		$result.="$this->_qsvnPageNumber=$pageNumber";
		
		if ($this->_sortingEnabled==true)
			$result.="&$this->_qsvnSortBy=".urlencode($sortBy)."&$this->_qsvnSortType=".urlencode($sortType);
			
		if ($this->_staticLinkEnabled==true and $this->_limit==1) {
			$id=$this->getIdByPageNumber($pageNumber);
			$result.="&$this->_qsvnId=".$id;
		}
		
		if ($this->_commandHandlers['rewriteUrl']) {
			$result=$this->runCommand('rewriteUrl',array('url'=>$result));
		}
		
		return $result;
	}
	
	function getSortingUrl($sortType=null , $sortBy=null) {
		return $this->getUrl(null,$sortType,$sortBy);
	}

	function getNextUrl()
	{
		return $this->getUrl($this->_nextPageNumber);
	}

	function getPrevUrl()
	{
		return $this->getUrl($this->_prevPageNumber);
	}



	/**
	* nInCenterWithJumps : |<< << < 1 2 3 [4] 5 6 7 ... > >> >>|
	* FirstCurrentLastGroup : 1,2,3,...,10,[11],12,...,98,99,100
	*/
	function getLimitedListOfPages($name,$options) {
		if ($name=='nInCenterWithJumps')
			return $this->getLimitedListOfPagesNInCenterWithJumps($options['numberOfPageToShow']);
		if ($name=='firstCurrentLastGroup')
			return $this->getLimitedListOfPagesFirstCurrentLastGroup($options);
	}
	
	function show($name,$options) {
		if ($name=='nInCenterWithJumps')
			return $this->showAsLimitedListOfPagesNInCenterWithJumps($options['numberOfPageToShow'],$options['prefix'],$options['pageLinkOnClick']);
		if ($name=='nInCenterWithJumpsFormatted')
			return $this->showAsLimitedListOfPagesNInCenterWithJumpsFormatted($options['numberOfPageToShow'],$options['prefix'],$options);
		if ($name=='firstCurrentLastGroup')
			return $this->showAsLimitedListOfPagesFirstCurrentLastGroup($options);
	}
	
	
	/* sample result :
		[jumptToFirstPage]
			[from]= //from
			[to]= //to
			[link]
		[jumptToPrevPages]
			[from]= //from
			[to]= //to
			[link]
		[jumptToPrevPage]
			[from]= //from
			[to]= //to
			[link]
		[hasPrevPage]=true // or false
		[pages]
			[0]
				[pageNumber]=1
				[link]=
				[selected]=true
			[1]
				[pageNumber]=1
				[link]=
				[selected]=true
		[hasNextPage]=true // or false
		[jumptToNextPage]
			[from]= //from
			[to]= //to
			[link]
		[jumptToNextPages]
			[from]= //from
			[to]= //to
			[link]
		[jumptToLastPage]
			[from]= //from
			[to]= //to
			[link]
	*/
	function getLimitedListOfPagesNInCenterWithJumps($numberOfPageToShow=10) {
		if (empty($numberOfPageToShow)) $numberOfPageToShow=10;
		$result='';
		$result['pages']=array();
		if ($this->_totalPages < $numberOfPageToShow) {$numberOfPageToShow=$this->_totalPages;}
		
		$pageNumber=$this->_pageNumber;

		if ($pageNumber>$numberOfPageToShow+$this->_firstPageNumber) {
			/* |<< jump to the first page */
			if ($pageNumber>$this->_firstPageNumber) {
				$myPageNumber=$this->_firstPageNumber;
				$result['jumptToFirstPage']=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
			}

			/* << jump to prev pages */
			if ($pageNumber-$numberOfPageToShow>=$this->_firstPageNumber)  {
				$myPageNumber=$pageNumber-$numberOfPageToShow;
				$result['jumptToPrevPages']=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
			}


			/* show that there is more next page */
			if ($pageNumber>$this->_firstPageNumber) {
				$result['hasPrevPage']=true;
			} else $result['hasPrevPage']=false;
		}

		/* < jump to prev page */
		if ($pageNumber-1>=$this->_firstPageNumber)  {
			$myPageNumber=$pageNumber-1;
			$result['jumptToPrevPage']=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
		}

		if ($pageNumber<$this->_firstPageNumber+$numberOfPageToShow)
			{$beginPage=$this->_firstPageNumber;} else {$beginPage=$pageNumber-$numberOfPageToShow;}
		foreach (range($beginPage, $numberOfPageToShow+$pageNumber) as $pn) {
			if ($pn<=$this->_totalPages) {
				if ($pageNumber==$pn) {$selected=true;} else {$selected=false;}
				$myPageNumber=$pn;
				$result['pages'][]=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber , 'selected'=>$selected);
			}
		}

		/* > jump to next page */
		if ($pageNumber+1<=$this->_totalPages-$this->_firstPageNumber)  {
			$myPageNumber=$pageNumber+1;
			$result['jumptToNextPage']=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
		}
		/* show that there is more next page */
		if ($pageNumber<$this->_totalPages-$numberOfPageToShow+$this->_firstPageNumber) {
			if ($numberOfPageToShow+$pageNumber<$this->_totalPages)  {
				$result['hasNextPage']=true;
			} else $result['hasNextPage']=false;


			/* >> jump to next pages */
			if ($pageNumber+$numberOfPageToShow<=$this->_totalPages)  {
				$myPageNumber=$pageNumber+$numberOfPageToShow;
				$result['jumptToNextPages']=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
			}

			/* >>| jump to the last page */
			if ($numberOfPageToShow+$pageNumber<$this->_totalPages)  {
				$myPageNumber=$this->_totalPages;
				$result['jumptToLastPage']=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
			}
		}
		return $result;
	}
	
	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> 
	*	css style :
	*		pagesNumbersList,
	*		currentPageNumber,pageNumber,
	*		jumpToFirstPage,jumpToPrevPages,jumpToPrevPage,hasPrevPage
	*		hasNextPage,jumpToNextPage,jumpToNextPages,jumpToLastPage
	* 
	*    'pageLinkOnClick'=>"return goToPage('%url%')"
	*
	* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> *
	*/
	function showAsLimitedListOfPagesNInCenterWithJumps($numberOfPageToShow=10,$prefix='paging',$jsPageLinkOnClick='') {
		if (empty($numberOfPageToShow)) $numberOfPageToShow=10;
		if (empty($prefix)) $prefix='paging';
		$pagesInfo=$this->getLimitedListOfPages('nInCenterWithJumps',array('numberOfPageToShow'=>$numberOfPageToShow));
		//print_r($pagesInfo);
		$result='<span class="'.$prefix.'PagesNumbersList">'."\n";		
		if ($pagesInfo['jumptToFirstPage']) {
			$jsFunc=str_replace('%url%',$pagesInfo['jumptToFirstPage']['link'],$jsPageLinkOnClick);
			$result.= "<span class=\"jumpToFirstPage\"><a onclick=\"$jsFunc\" href='".$pagesInfo['jumptToFirstPage']['link']."' title=\"Jump to first page\"> |&lt;&lt; </a> </span>";
		}
		if ($pagesInfo['jumptToPrevPages']) {
			$jsFunc=str_replace('%url%',$pagesInfo['jumptToPrevPages']['link'],$jsPageLinkOnClick);
			$result.= "<span class=\"jumpToPrevPages\"><a onclick=\"$jsFunc\" href='".$pagesInfo['jumptToPrevPages']['link']."' title=\"Jump to previous pages\"> &lt;&lt; </a> </span>";
		}
		if ($pagesInfo['jumptToPrevPage']) {
			$jsFunc=str_replace('%url%',$pagesInfo['jumptToPrevPage']['link'],$jsPageLinkOnClick);
			$result.= "<span class=\"jumpToPrevPage\"><a onclick=\"$jsFunc\" href='".$pagesInfo['jumptToPrevPage']['link']."' title=\"Jump to previous page\"> &lt; </a> </span>";
		}
		if ($pagesInfo['hasPrevPage']) $result.="<span class=\"hasPrevPage\"> ... </span>";

		foreach ($pagesInfo['pages'] as $page) {
			$jsFunc=str_replace('%url%',$page['link'],$jsPageLinkOnClick);
			if ($page['selected']==true) {
				$result.= "<span class=\"currentPageNumber\"><a onclick=\"$jsFunc\" href='$page[link]'>"."\n";
				$result.="[$page[pageNumber]]";
			} else {
				$result.= "<span class=\"pageNumber\"><a onclick=\"$jsFunc\" href='$page[link]'>";
				$result.="$page[pageNumber]";
			}
			$result.="</a>&nbsp;</span>"."\n";
		}

		if ($pagesInfo['hasNextPage']) $result.="<span class=\"hasNextPage\"> ... </span>";
		if ($pagesInfo['jumptToNextPage']) {
			$jsFunc=str_replace('%url%',$pagesInfo['jumptToNextPage']['link'],$jsPageLinkOnClick);
			$result.= "<span class=\"jumpToNextPage\"><a onclick=\"$jsFunc\" href='".$pagesInfo['jumptToNextPage']['link']."' title=\"Jump to next page\"> &gt; </a> </span>";
		}
		if ($pagesInfo['jumptToNextPages']) {
			$jsFunc=str_replace('%url%',$pagesInfo['jumptToNextPages']['link'],$jsPageLinkOnClick);
			$result.= "<span class=\"jumpToNextPages\"><a onclick=\"$jsFunc\" href='".$pagesInfo['jumptToNextPages']['link']."' title=\"Jump to next pages\"> &gt;&gt; </a> </span>";
		}
		if ($pagesInfo['jumptToNextPages']) {
			$jsFunc=str_replace('%url%',$pagesInfo['jumptToNextPages']['link'],$jsPageLinkOnClick);	
			$result.= "<span class=\"jumpToLastPage\"><a onclick=\"$jsFunc\" href='".$pagesInfo['jumptToLastPage']['link']."' title=\"Jump to last page\"> &gt;&gt;| </a> </span>";
		}
		$result.='</span>';
		return $result;
	}
	
	
	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> 
		css style :
			pagingPagesNumbersList,
			currentPageNumber,pageNumber,
			jumpToFirstPage,jumpToPrevPages,jumpToPrevPage,hasPrevPage
			hasNextPage,jumpToNextPage,jumpToNextPages,jumpToLastPage
	*/
	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> 
		css style :
			pagingPagesNumbersList,
			currentPageNumber,pageNumber,
			jumpToFirstPage,jumpToPrevPages,jumpToPrevPage,hasPrevPage
			hasNextPage,jumpToNextPage,jumpToNextPages,jumpToLastPage
	*/
	function showAsLimitedListOfPagesNInCenterWithJumpsFormatted($numberOfPageToShow=10,$prefix='paging',$options=array()) {
		$pagesInfo=$this->getLimitedListOfPages('nInCenterWithJumps',array('numberOfPageToShow'=>$numberOfPageToShow));
		if (!isset($options['toShow'])) $options['toShow']=array('jumptToFirstPage','jumptToPrevPages','jumptToPrevPage','hasPrevPage','hasNextPage','jumptToNextPage','jumptToNextPages','jumptToLastPage');
		if (empty($prefix)) $prefix='paging';
		//print_r($pagesInfo);
		$result='<div class="'.$prefix.'">'."\n";
		if (in_array('jumptToFirstPage',$options['toShow']))
			if ($pagesInfo['jumptToFirstPage']) $result.= "<div class=\"jumpToFirstPage\"><a href='".$pagesInfo['jumptToFirstPage']['link']."'> |&lt;&lt; </a></div>"."\n";
		if (in_array('jumptToPrevPages',$options['toShow']))
			if ($pagesInfo['jumptToPrevPages']) $result.= "<div class=\"jumpToPrevPages\"><a href='".$pagesInfo['jumptToPrevPages']['link']."'> &lt;&lt; </a></div>"."\n";
		if (in_array('jumptToPrevPage',$options['toShow']))
			if ($pagesInfo['jumptToPrevPage']) $result.= "<div class=\"jumpToPrevPage\"><a href='".$pagesInfo['jumptToPrevPage']['link']."'> &lt; </a></div>"."\n";
		if (in_array('hasPrevPage',$options['toShow']))
			if ($pagesInfo['hasPrevPage']) $result.="<div class=\"hasPrevPage\">...</div>"."\n";

		foreach ($pagesInfo['pages'] as $page) {
			if ($page['selected']==true) {
				$result.= "<div class=\"currentPageNumber\"><a href='$page[link]'>"."\n";
				$result.="$page[pageNumber]";
			} else {
				$result.= "<div class=\"pageNumber\"><a href='$page[link]'>";
				$result.="$page[pageNumber]";
			}
			$result.="</a></div>"."\n";
		}
		
		if (in_array('hasNextPage',$options['toShow']))
			if ($pagesInfo['hasNextPage']) $result.="<div class=\"hasNextPage\">...</div>"."\n";
		if (in_array('jumptToNextPage',$options['toShow']))
			if ($pagesInfo['jumptToNextPage']) $result.= "<div class=\"jumpToNextPage\"><a href='".$pagesInfo['jumptToNextPage']['link']."'> &gt; </a></div>"."\n";
		if (in_array('jumptToNextPages',$options['toShow']))
			if ($pagesInfo['jumptToNextPages']) $result.= "<div class=\"jumpToNextPages\"><a href='".$pagesInfo['jumptToNextPages']['link']."'> &gt;&gt; </a></div>"."\n";
		if (in_array('jumptToLastPage',$options['toShow']))
			if ($pagesInfo['jumptToLastPage'])	$result.= "<div class=\"jumpToLastPage\"><a href='".$pagesInfo['jumptToLastPage']['link']."'> &gt;&gt;| </a></div>"."\n";
		$result.='</div>';
		return $result;
	}
	
	
	/* sample result :
		[firstPages]
			[0]
				[pageNumber]=1
				[link]=
			[1]
				[pageNumber]=2
				[link]=
			[2]
				[pageNumber]=3
				[link]=
		[pages]
			[0]
				[pageNumber]=9
				[link]=
				[selected]=false
			[1]
				[pageNumber]=10
				[link]=
				[selected]=true
			[2]
				[pageNumber]=11
				[link]=
				[selected]=false
		[lastPages]
			[0]
				[pageNumber]=12
				[link]=
			[1]
				[pageNumber]=13
				[link]=
			[2]
				[pageNumber]=14
				[link]
	*/
	function getLimitedListOfPagesFirstCurrentLastGroup($options) {
		if (!isset($options['firstGroupTotal'])) $options['firstGroupTotal']=3;
		if (!isset($options['currentGroupTotal'])) $options['currentGroupTotal']=3;
		if (!isset($options['lastGroupTotal'])) $options['lastGroupTotal']=3;
		$result='';
		$result['pages']=array();
		if ($this->_totalPages < $numberOfPageToShow) {$numberOfPageToShow=$this->_totalPages;}
		
		$pageNumber=$this->_pageNumber;
		
		$pnFrom=$pageNumber-floor($options['currentGroupTotal']/2);
		$pnTo=$pageNumber+floor($options['currentGroupTotal']/2);
		if ($pnFrom<$this->_firstPageNumber) { 
			$pnFrom=$this->_firstPageNumber=1;
			$pnTo=$options['currentGroupTotal'];
		}

		#--(Begin)-->First Group
		if ($pageNumber>$this->_firstPageNumber)  {
			foreach (range($this->_firstPageNumber, $options['firstGroupTotal']) as $pn) {
				if ($pn<$pnFrom) {
					$myPageNumber=$pn;
					$result['firstPages'][]=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
				}
			}
		}
		#--(End)-->First Group
		
		#--(Begin)-->Current Group
		//$d=$options['currentGroupTotal'];
		foreach (range($pnFrom, $pnTo) as $pn) {
			if ($pn<=$this->_totalPages) {
				if ($pageNumber==$pn) {$selected=true;} else {$selected=false;}
				$myPageNumber=$pn;
				$result['pages'][]=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber , 'selected'=>$selected);
			}
		}
		#--(End)-->Current Group
		
		#--(Begin)-->Last Group
		if ($pageNumber<$this->_totalPages)  {
			foreach (range($this->_totalPages-$options['lastGroupTotal']+1, $this->_totalPages) as $pn) {
				if ($pn>$pnTo) {
					$myPageNumber=$pn;
					$result['lastPages'][]=array('link'=>$this->getUrl($myPageNumber), 'pageNumber'=>$myPageNumber);
				}
			}
		}
		#--(End)-->Last Group

		return $result;
	}


	function replaceWithPaginInfo($pageNumber,$text) {
		return cmfcString::replaceVariables(array(
			'%pageNumber%'=>$pageNumber,
			'%sortBy%'=>$this->_sortBy,
			'%sortType%'=>$this->_sortType,
			'%id%'=>$this->_id
		),$text);
	}	
	
	
	/* 
	* like : 1,2,3,...,4,5,6,...,8,9,10
	* 
	* function goToPage(pageNumber) {
	*	//$().
	*	document.searchForm.pageNumber.value=pageNumber;
	*	document.searchForm.submit_goToPage.click();
	*	return false;
	* }
	* 
	* 
	* $paging->show('firstCurrentLastGroup',array(
	*    'pageLinkOnClick'=>"return goToPage('%pageNumber%','%sortBy%','%sortType%','%id%')"
	* ))
	*/
	function showAsLimitedListOfPagesFirstCurrentLastGroup($options) {
		if (!isset($options['prefix'])) $options['prefix']='paging';
		$pagesInfo=$this->getLimitedListOfPages('firstCurrentLastGroup',$options);
		
		
		//print_r($pagesInfo);
		$result='<span class="'.$prefix.'PagesNumbersList">'."\n";
		
		if (!empty($pagesInfo['firstPages'])) {
			foreach ($pagesInfo['firstPages'] as $page) {
				$jsFunc=$this->replaceWithPaginInfo($page['pageNumber'],$options['pageLinkOnClick']);
				$result.= "<span class=\"pageNumber\"><a href='$page[link]' onclick=\"$jsFunc\">";
				$result.="$page[pageNumber]";
				$result.="</a>,</span>"."\n";
			}
			$result.="<span class=\"hasPrevPage\"> ... </span> , ";
		}

		foreach ($pagesInfo['pages'] as $page) {
			$jsFunc=$this->replaceWithPaginInfo($page['pageNumber'],$options['pageLinkOnClick']);
			$result.=$comma;
			if ($page['selected']==true) {
				$result.= "<span class=\"currentPageNumber\"><a href='$page[link]' onclick=\"$jsFunc\">"."\n";
				$result.="[$page[pageNumber]]";
			} else {
				$result.= "<span class=\"pageNumber\"><a href='$page[link]' onclick=\"$jsFunc\">";
				$result.="$page[pageNumber]";
			}
			$result.="</a></span>"."\n";
			$comma=',';
		}
		
		if (!empty($pagesInfo['lastPages'])) {
			$result.=" , <span class=\"hasNextPage\"> ... </span>";
			foreach ($pagesInfo['lastPages'] as $page) {
				$jsFunc=$this->replaceWithPaginInfo($page['pageNumber'],$options['pageLinkOnClick']);
				$result.= ",<span class=\"pageNumber\"><a href='$page[link]' onclick=\"$jsFunc\">";
				$result.="$page[pageNumber]";
				$result.="</a></span>"."\n";
			}
		}

		$result.='</span>';
		return $result;
	}



	function showAsFormFields($addFormTag=false,$action=null,$name=null,$additionalFields=null)
	{
		$total=$this->_total;//+$this->_beginValue;
		if ($total > $this->_limit)
		{
			$prev_html='';
			$nextHtml='';
			$this->setVariables();

			if ($this->_from > $this->_beginValue)
			{
				$prev_html="<input type=\"submit\" name=\"$this->_qsvnPrev\" value=\"$this->_wordPrev\"/>";
			}

			if ($this->_to < $total)
			{
				$nextHtml="<input type=\"submit\" name=\"$this->_qsvnNext\" value=\"$this->_wordNext\"/>";

			}
			if (is_null($action)) {$action=$this->_link;}
			if ($addFormTag==true) {$result="<form $name $action method=\"POST\">";}
			$result.="$prev_html&nbsp;<input type=\"text\" size=\"2\" name=\"$this->_qsvnFrom\" value=\"$this->_from\"/>-<input type=\"text\" size=\"2\" name=\"$this->_qsvnTo\" value=\"$this->_to\"/> / $total &nbsp;$nextHtml";
			if ($addFormTag=true) {$result.="$additionalFields</form>";}
		}
		return $result;
	}
	
	
	/**
	* like : 1,2,3,4,5,
	*/
	function showAsListOfPages() {
		$totalPages=$this->getNumberOfPages();
		for ($pn=1;$pn<$totalPages;$pn++) {
			list($from,$to)=$this->getPageRange($pn);
			echo "<a href='$this->_link&$this->_qsvnFrom=$from&$this->_qsvnTo=$to'>$pn</a> , ";
		}
	}


}