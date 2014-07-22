<?php
/**
* @author Sina Shayegan Salek (sina.salek.ws)
* @version $Id: mysql.class.inc.php 499 2010-01-09 06:44:08Z salek $
* @package cmf
* @subpackage beta
* @license GPL (GNU General Public Licence - www.gnu.org/copyleft/gpl.html) 
* 
* @todo
* 	- changing all of the functions parameters order, they all start from big to small 
* 		and from names to values : tablename,columnname,keycolumnname,columnsValues,keycolumnvalue
* 	- adding support for custom connection link for popuplar methods like update, insert, getColumnValue etc
* @changes
* 	- ability to disable queries execution, so no query will become executed setOption('noQueryExecution',true)
*   - calling dymaically has been added,each instance should be able to use a different connection link (singleton)
* 	- support for magic_qoutes enable safe
* 	- support using statements in update,insert,delete
*	- query genereators for common task like insert,update etc
* 	- executed queries stack
* 	- connection functions
* 	- separating query() and exec() for insert, update, delete and select queries
* 	- using singleton method for storing connection handlers
* 	- wrapper for mysql_fetch_array, mysql_num_rows, mysql_num_fields, mysql_fetch_field, mysql_field_name, mysql_table_name
*/


/**
* 
*/
class cmfcMySqlStatement {
	var $type='statement';
	var $value;
	
	function setType($value) {
		$this->type=$value;
	}
	
	function setValue($value) {
		$this->value=$value;
	}
	
	function getValue() {
		return $this->value;
	}
	
	function getType() {
		return $this->type;
	}
}


/**
* debuging
* <code>
* 	cmfcMySql::setOption('debugEnabled',true);
* 	cmfcMySql::getUpdateSql($tableName,$keyColumnName,$columnsValues,$keyColumnValue);
* 	print_r(cmfcMySql::getRegisteredQueries());
* 	result :
* 	[1] => {
* 		['sql']=>"UPDATE ...",
* 		['result']=>
* 		['error']=>'undifined keyword'
* 	}
* </code>
* statement usage in update(),insert(),delete(),load(),loadWithMultikeys(),... :
* <code>
* 	cmfcMySql::update('articles','id',array('views'=>cmfcMySql::asStatement('views+1')),'5');
* </code>
*/
class cmfcMySql {
	
	var $_debugEnabled;
	var $_connectionLink;
	var $_queryStack;
	var $_noQueryExecution=false;
	var $_tablesInfo;
	var $_autoParseQueriesEnabled;
	
	
	function &getInstance($obj=null) {
		if (is_object($obj)) {
			$className=get_class($obj);
		} else {
			$className='';
		}
		if(strtolower($className)=='cmfcmysql') {
			return $obj;
		} elseif(is_object($GLOBALS['__CMFC_MySQL_Object'])) {
			return $GLOBALS['__CMFC_MySQL_Object'];
		} else {
			return $GLOBALS['__CMFC_MySQL_Object']=&new cmfcMySql();
		}
	}
	
	function setOption($name,$value) {
		$_this=&cmfcMySql::getInstance(&$this);
		if ($name=='autoParseQueriesEnabled') {
			$_this->_autoParseQueriesEnabled=$value;
		}
		if ($name=='tablesInfo') {
			$_this->_tablesInfo=$value;
		}
		if ($name=='debugEnabled') {
			$_this->_debugEnabled=$value;
		}
		if ($name=='connectionLink') {
			$_this->_connectionLink=$value;
		}
		if ($name=='noQueryExecution') {
			$_this->_noQueryExecution=$value;
		}
	}
	
	function getOption($name) {
		$_this=&cmfcMySql::getInstance(&$this);
		if ($name=='autoParseQueriesEnabled') {
			return $_this->_autoParseQueriesEnabled;
		}
		if ($name=='tablesInfo') {
			return $_this->_tablesInfo;
		}
		if ($name=='debugEnabled') {
			return $_this->_debugEnabled;
		}
		if ($name=='connectionLink') {
			return $_this->_connectionLink;
		}
		if ($name=='noQueryExecution') {
			return $_this->_noQueryExecution;
		}
	}
	
	
	/**
	* set a connection link for the all functions of the class
	* so it will be possible to connect this class to an active
	* mysql connection link
	*/
	function setConnectionLink($link) {
		$_this=&cmfcMySql::getInstance(&$this);
		$_this->_connectionLink=$link;
	}
	
	
	/**
	* set a connection link for the all functions of the class
	* so it will be possible to connect this class to an active
	* mysql connection link
	*/
	function getConnectionLink() {
		$_this=&cmfcMySql::getInstance(&$this);
		return $_this->_connectionLink;
	}
	
	
	/**
	* check if entered value is an sql statement or it's just an string
	*/
	function isStatement($value) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_object($value)) {
			if (strtolower(cmfcPhp4::get_class($value))==strtolower('cmfcMySqlStatement'))
				if ($value->getType()=='statement') {
					return true;
				}
		}
		return false;
	}

	/**
	* 
	*/
	function asStatement($value) {
		$o=new cmfcMySqlStatement();
		$o->setType('statement');
		$o->setValue($value);
		
		return $o;
	}
	
	
	function __startTimer() {
		global $______starttime;
		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$______starttime = $mtime;
	}

	function __stopTimer() {
		global $______starttime;
		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = $endtime - $______starttime;
		return $totaltime;
	}
	
	function registerQuery($sql,$result,$error) {
		$_this=&cmfcMySql::getInstance(&$this);
		
		if ($_this->getOption('debugEnabled')==true) {
			$time=$this->__stopTimer();
			$_this->_queryStack[]=array(
				'sql'=>$sql,
				'result'=>$result,
				'error'=>$error,
				'executionTime'=>$time
			);
		}
	}
	
	function getRegisteredQueries() {
		$_this=&cmfcMySql::getInstance(&$this);
		return $_this->_queryStack;
	}
	
	function clearRegisteredQueries() {
		$_this=&cmfcMySql::getInstance(&$this);
		$_this->_queryStack=array();
	}
	
	
	/**
	* <code>
	* $query="
	* 		SELECT
	* 			O.*,
	* 			SUM( {orders.finalTotal} ) as 'finalTotal',
	* 			{customers.companyTitle},
	* 			(SELECT SUM({receipts.amount}) FROM {receipts} WHERE {receipts.orderNumber}={orders.id}) as 'receipt'
	* 		FROM {orders} as O
	* 			INNER JOIN {customers} as C ON {customers.id}={orders.customer} 
	* 		$whereClause [fdsafa]
	* 		GROUP BY {orders.orderNumber}
	* 		[x]
	* 	";
	* 	echo $query=cmfcMySql::parseQuery($query,array(
	* 		'tablesAlias'=>array(
	* 			'orders'=>'O',
	* 			'customers'=>'C'
	* 		),
	* 		'parameters'=>array(
	* 			'x'=>5,
	* 			'fdsafa'=>90
	* 		)
	* 	));
	* </code>
	* Result :
	* <code>
	* SELECT
	* 	O.*,
	* 	SUM( O.`final_total` ) as 'finalTotal',
	* 	C.`company_title`,
	* 	(SELECT SUM(`zdbd2_receipts`.`amount`)
	* 		FROM `zdbd2_receipts`
	* 		WHERE `zdbd2_receipts`.`orderNumber`=O.`id`
	* 	) as 'receipt'
	* 	FROM `zdbd2_orders` as O
	* 		INNER JOIN `zdbd2_customers` as C ON C.`id`=O.`customer`
	* 	WHERE C.currency = '1111111121' AND O.paid = 1 90
	* 	GROUP BY O.`order_number` 5
	*
	*/
	function parseQuery($query, $options=array()) {
		$_this=&cmfcMySql::getInstance(&$this);
		$tablesInfo=&$_this->_tablesInfo;
		if (preg_match_all('/\{([^"\'.{}]+)(\.([^"\'.{}]+))?\}/sim', $query, $regs,PREG_SET_ORDER)) {

			foreach ($regs as $reg) {
				$name='';
				if (isset($reg[1])) { //table name
					if (isset($tablesInfo[$reg[1]]))
						$name='`'.$tablesInfo[$reg[1]]['tableName'].'`';
				}
				if (isset($reg[1]) and isset($reg[3]) and isset($options['tablesAlias'][$reg[1]])) { //table name replace with alias
					$name=$options['tablesAlias'][$reg[1]];
				}
				if (isset($reg[3])) {//field name
					if (isset($tablesInfo[$reg[1]]['columns'][$reg[3]]))
						$name=$name.'.'.'`'.$tablesInfo[$reg[1]]['columns'][$reg[3]].'`';
				}
				if (!empty($name)) {
					$query=str_replace($reg[0],$name,$query);
				}
			}
		}
		
		if (isset($options['parameters']))
		if (preg_match_all('/(\[([^[\]\'"]+)\])/sim', $query, $regs,PREG_SET_ORDER)) {
			foreach ($regs as $reg) {
				$name='';
				if (isset($options['parameters'][$reg[2]])) { //parameter value
					$name=$options['parameters'][$reg[2]];
				}
				if (!empty($name)) {
					$query=str_replace($reg[0],$name,$query);
				}
			}

		}
		return $query;
	}
	
	
	/**
	* another solution is to user getRegisteredQueries() and the call clearRegisteredQueries()
	* @author arash dalir
	*/
	function dumpRegisteredQueries(){
		$_this=&cmfcMySql::getInstance(&$this);
		$queries = $_this->_queryStack;
		$_this->clearRegisteredQueries();
		return $queries;
	}
	
	/**
	* @desc
	* @previousNames db_get_row_number
	* @todo SET @row = 0; 
	* 		SELECT @row := @row + 1 AS row_number FROM `photos` WHERE `gallery_id`='6'   ORDER BY `order_number`,id DESC
	*/
	function getRowNumber($sqlQuery,$keyColumnName,$keyColumnValue) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQueryResult=$_this->query($sqlQuery);
		if (@$_this->numRows($sqlQueryResult)) {
			$rowNumber=0;
			while ($row=$_this->fetchArray($sqlQueryResult,MYSQL_BOTH)) {
				$rowNumber++;
				if ($row[$keyColumnName]==$keyColumnValue)
					return $rowNumber;
			}
		}
		
		return false;
	}
	
	
	/**
	* @desc 
	* @example
	* <code>
	* 	$_this->getLimitedQuery('SELECT * FROM table',1,3)
	* 	-->"SELECT * FROM table LIMIT 1,3"
	* </code>
	* @previousNames get_limited_query
	*/
	function getLimitedQuery($sqlQuery,$from,$length)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		//$sqlQuery=strtolower($sqlQuery);
		//	if (strpos($sqlQuery,'limit')>-1) {
		
		//	$sqlQuery=substr_replace($sqlQuery,'limit',strpos($sqlQuery,'limit')-strlen('limit'),strlen($sqlQuery));
		//	}
		$sqlQuery = preg_replace('/LIMIT[1-9, ]*$/si', '', $sqlQuery);
		$sqlQuery.=" LIMIT $from,$length";
		return $sqlQuery;
	}
	
	
	
	/**
	* optimize select query on large tables (table should have atleast one indexed column)
	*/
	function getLimitedQueryOptimized($sqlQuery,$indexColumnName,$limitFrom,$limitLength) {
		$_this=&cmfcMySql::getInstance(&$this);
		if ($limitFrom<1000) {
			$sqlQueryL=$sqlQuery;
			$sqlQueryL = preg_replace('/(SELECT)( .* )(FROM .*)/i', '$1 '.$indexColumnName.' $3', $sqlQueryL);
			$sqlQueryL = preg_replace('/LIMIT[1-9, ]*$/si', '', $sqlQuery);
			$sqlQueryL.=" LIMIT $limitFrom,$limitLength";
			$rows=$_this->getRowsCustom($sqlQueryL);
			$ids=array();
			if (is_array($rows)) {
				foreach ($rows as $row) {
					$ids[]=$row[$indexColumnName];
				}
			}
			$ids=implode(',',$ids);
			
			$sqlQueryP=$sqlQuery;
			$sqlQuery = preg_replace('/(.* FROM [^ ]*)( WHERE )(.*)/i', '$1 WHERE '.$indexColumnName.' IN ('.$ids.') AND $3', $sqlQuery);
			if ($sqlQueryP==$sqlQuery) {
				$sqlQuery = preg_replace('/(.* FROM [^ ]*)( )(.*)/i', '$1 WHERE '.$indexColumnName.' IN ('.$ids.') $3', $sqlQuery);
			}
		} else {
			$sqlQuery=$_this->getLimitedQuery($sqlQuery,$limitFrom,$limitLength);
		}
		
		return $sqlQuery;
	}
	
	

	/**
	* Enter description here...
	* 
	* @param string $sqlQuery
	* @return array ->sample:array(0=>'table1',1=>'table2')
	* @previousNames  fetch_tables_name_from_sql_query
	*/
	function fetchTablesNameFromSqlQuery($sqlQuery)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		//$pattern='/.*[^`\'"]?from[^`\'"]? +([`"\' ]?[,]?([^`"\',]+)[`"\' $]?[,]?)+/i';
		//$pattern='/from *(.*) *(where|order|limit|$)/i';
		//$sqlQuery="select * from `table1`,`table2`";
		//$sqlQuery="from `table1`,`table2`";
		if (preg_match_all('/from *(.*) *(where|order|limit|$)/i',$sqlQuery,$matches,PREG_PATTERN_ORDER))
		if (preg_match_all('/([,]?[`"\' ]?([^`"\',]+)[`"\' $]?[,]?)/i',$matches[1][0],$matches,PREG_PATTERN_ORDER))
		{
			return $matches[2];
		}
	}
	
	
	function getRows($tableName,$filter_column_name=null,$filter_column_value=null,$limit=null,$sortByColumnName=null,$sortType=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT * FROM `$tableName`";
		if (!is_null($filter_column_name)) $sqlQuery.=" WHERE `$filter_column_name`='$filter_column_value' ";
		if (!is_null($sortByColumnName)) $sqlQuery.=" ORDER BY `$sortByColumnName` ";
		if (!is_null($sortType)) $sqlQuery.=" $sortType ";
		if (!is_null($limit)) $sqlQuery.=" LIMIT $limit";
	
		return $_this->getRowsCustom($sqlQuery);
	}
	
	/**
	* @author akbar nasr abadi
	*/
	function getRowsWithCustomIndex($tableName,$filter_column_name=null,$filter_column_value=null,$keyColumnName=null,$multiMode=false)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT * FROM `$tableName`";
		if (!is_null($filter_column_name)) $sqlQuery.=" WHERE `$filter_column_name`='$filter_column_value' ";
		$sqlQueryResult = $_this->query($sqlQuery);

		$result=false;
		if ($sqlQueryResult!==false)
			while ($row = $_this->fetchArray($sqlQueryResult,MYSQL_ASSOC)) {
				if (!is_null($keyColumnName)) {
					if ($multiMode) {
						$result[$row[$keyColumnName]][]=$row;
					} else {
						$result[$row[$keyColumnName]]=$row;
					}
				} else
					$result[]=$row;
			}
		return $result;
	}
	
	
	/**
	* like get rows but accept single dimensional array of columns and values as condition
	*/
	function getRowsWithMultiKeys($tableName,$keyColumnsValues) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT * FROM `$tableName` WHERE (1=1) ";
		
		if (is_array($keyColumnsValues)){
			foreach ($keyColumnsValues as $keyColumnName=>$keyColumnValue) {
				$comma = ' AND ';
				if ($_this->isStatement($keyColumnValue)) {
					$sqlQuery.="$comma `$keyColumnName`=".$keyColumnValue->getValue()."";
				} else {
					$keyColumnValue=$_this->smartEscapeString($keyColumnValue);
					$sqlQuery.="$comma `$keyColumnName`='$keyColumnValue' ";
				}
			}
       	}
		return $_this->getRowsCustom($sqlQuery);
	}
	
	/**
	* execute give query and return it's result as array
	* @param string
	* @return array|boolean
	*/
	function getRowsCustom($sqlQuery)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQueryResult = $_this->query($sqlQuery);
	
		$result=false;
		if ($sqlQueryResult!==false)
			while ($row = $_this->fetchArray($sqlQueryResult,MYSQL_ASSOC)) {
				$result[]=$row;
			}
		return $result;
	}	
	
	/**
	* if $multiMode was true, each item may contains muliply rows with same $keyColumn value
	* otherwise each item only contains one row and if more than one rows have same $keyColumn value
	* the last will override the others
	* 
	* @todo should be remove in favor of getRowsAsArrayCustom as of generallib 3
	* @see $_this->getRowsCustomAsCustomIndexedArray
	* @param string
	* @param string
	* @param boolean
	* @return array
	*/
	function getRowsAsArrayCustom($sqlQuery,$keyColumnName=null,$multiMode=false)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		return $_this->getRowsCustomWithCustomIndex($sqlQuery,$keyColumnName,$multiMode);
	}
	
	/**
	* if $multiMode was true, each item may contains muliply rows with same $keyColumn value
	* otherwise each item only contains one row and if more than one rows have same $keyColumn value
	* the last will override the others
	* @previousNames getRowsAsArrayCustom
	* @param string
	* @param string
	* @param boolean
	* @return array
	*/
	function getRowsCustomWithCustomIndex($sqlQuery,$keyColumnName=null,$multiMode=false)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQueryResult = $_this->query($sqlQuery);

		$result=false;
		if ($sqlQueryResult!==false)
			while ($row = $_this->fetchArray($sqlQueryResult,MYSQL_ASSOC)) {
				if (!is_null($keyColumnName)) {
					if ($multiMode) {
						$result[$row[$keyColumnName]][]=$row;
					} else {
						$result[$row[$keyColumnName]]=$row;
					}
				} else
					$result[]=$row;
			}
		return $result;
	}
	

	function getColumnValueCustom($sqlQuery,$valueFieldName)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$row=$_this->loadCustom($sqlQuery);
		if (is_array($row))
			return $row[$valueFieldName];
		else
			return false;
	}
	
	/**
	* virtual tables are some sort of array with table like structure
	* this function helps do a search in its rows and and extract specific column value
	*/
	function getVirtualColumnValue($keyColumnValue, $virtualTableRows, $keyColumnName, $valueColumnName) {
		$_this=&cmfcMySql::getInstance(&$this);
		$key=cmfcArray::getKeyByValue($virtualTableRows, array('key'=>$keyColumnName,'value'=>$keyColumnValue));
		return $virtualTableRows[$key][$valueColumnName];
	}
	
	/**
	* virtual tables are some sort of array with table like structure
	* this function helps do a search in its rows and and extract specific column value
	*/
	function getVirtualColumnValueLike($keyColumnValue, $virtualTableRows, $keyColumnName, $valueColumnName) {
		$_this=&cmfcMySql::getInstance(&$this);
		$key=cmfcArray::getKeyByValueWildcard($virtualTableRows, array('key'=>$keyColumnName,'value'=>$keyColumnValue));
		return $virtualTableRows[$key][$valueColumnName];
	}
	
	
	function getVirtualColumnRowsLike($keyColumnValue, $virtualTableRows, $keyColumnName, $valueColumnName) {
		$_this=&cmfcMySql::getInstance(&$this);
		$keys=cmfcArray::getKeyByValueWildcard($virtualTableRows, array('key'=>$keyColumnName,'value'=>$keyColumnValue,true), true);
		if($keys)
		{
			foreach($keys as $key)
			{
				$matchedRows[] = $virtualTableRows[$key][$valueColumnName];
			}
		}
		return $matchedRows;
	}


	/**
	* @changes
	* 	- issue with using reserved words as field name in $keyFieldName value fixed (patch by Akbar NasrAbadi)
	*/
	function getColumnValue($keyFieldValue,$tableName,$keyFieldName,$valueFieldName)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT `$valueFieldName`,`$keyFieldName` FROM `$tableName` WHERE `$keyFieldName`='$keyFieldValue' LIMIT 1";
		return $_this->getColumnValueCustom($sqlQuery,$valueFieldName);
	}
	
	/**
	* $_this->getColumnValueLike('id','camera_sub','name',$exif['IFD0']['Model']);
	* @previousNames get_value_from_database_like
	*/
	function getColumnValueLike($keyFieldValue,$tableName,$keyFieldName,$valueFieldName)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT $valueFieldName,$keyFieldName FROM `$tableName` WHERE $keyFieldName LIKE '$keyFieldValue' LIMIT 1";
		return cmfcMySQLGetColumnValueCustom($sqlQuery,$valueFieldName);
	}
	
	
	function checkRowExistenceByValue($table,$fieldName,$fieldValue,$caseSensitive=true,$binary=true)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		if ($binary) $binary=" BINARY";
		if ($caseSensitive)
			$sqlQuery="SELECT `$fieldName` FROM `$table` WHERE $binary `$fieldName`='$fieldValue'";
		else
			$sqlQuery="SELECT `$fieldName` FROM `$table` WHERE $binary `$fieldName` LIKE '$fieldValue'";
		$sqlQueryResult=$_this->query($sqlQuery);
		$total = $_this->numRows($sqlQueryResult);
		if ($total>0) {
			return true;
		} else { return false; }
		return false;
	}
	
	function checkRowExistenceCustom($sqlQuery) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery=$_this->query($sqlQuery);
		$total = $_this->numRows($sqlQuery);
		if ($total>0) {
			return true;
		} else { return false; }
	}
	
		

	/**
	* when your database isn't normalize completely and you want to use something like ",1,5,6," instead of 
	* a middle table, this function will help you to fetch value of id(s) from table and combine them in 
	* a string or array.
	* @example
	* <code>
	* 	$users_email=multi_id_value_to_value(',1,4,5','user','uid','name',',');
	* 	//result is an array -> array("reza","asiyeh","sonia")
	* </code>
	*/
	function multiIdValueToValue($value,$table,$keyFieldName,$valueFieldName,$separator=',')
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$values=explode($separator,$value);
		$result=array();
		foreach ($values as $itemKey)
		if (!empty($itemKey))
		{
			$itemValue=$_this->getColumnValue($itemKey,$table,$keyFieldName,$valueFieldName);
			if (!empty($itemValue)) {$result[$itemKey]=$itemValue;}
		}
		return $result;
	}
	
	/**
	* @return string|boolean # id IN (1,5,47,95,9) or false
	*/
	function multiIdValueToSqlCondition($value,$keyColumnName,$separator=',') {
		$_this=&cmfcMySql::getInstance(&$this);
		$values=explode($separator,$value);
		$listIds='';
		foreach ($values as $itemKey)
			if (!empty($itemKey)) {
				$listIds.=$comma.$itemKey;
				if (empty($comma)) $comma=',';
			}
		if (!empty($listIds)) {
			return "$keyColumnName IN ($listIds)";
		}
		return false;
	}
	
		
	function simpleQuery($tableName,$sqlWhere=null,$sqlOrderBy=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT * FROM `$tableName`";
		if (!is_null($sqlWhere)) {$sqlQuery.=" WHERE $sqlWhere";}
		if (!is_null($sqlOrderBy)) {$sqlQuery.=" ORDER BY $sqlOrderBy";}
		return $_this->query($sqlQuery);
	}
	
	function simpleWhereQuery($tableName,$fieldName,$fieldValue,$sqlOrderBy=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		return cmfcMySQLSimpleQuery($tableName,"`$fieldName`='$fieldValue'",$sqlOrderBy);
	}
	
	/**
	* @example 
	* <code>
	* define('SSG_COMPARISON_SYMBOL','SSG_COMPARISON_SYMBOL');
	* define('SSG_VALUE','SSG_VALUE');
	* /
	* 	$where_string_or_array :
	* 	['field_name']=[SSG_VALUE]='5'
	* 	['field_name']=[SSG_COMPARISON_SYMBOL]='>='
	* /
	* function simple_search_query($tableName,$where_string_or_array,$sqlOrderBy=null)
	* {
	* 	$sqlQuery="SELECT * FROM $tableName WHERE ";
	* 	foreach ($where_string_or_array)
	* }
	* </code>
	*/
	function simpleNavigationBar($sqlQueryOrResult,$start,$limit,$link,$nextWord = "Next",$previousWord = "Previous")
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$next_link = '';
		$prev_link = '';
		$st_next = $start + $limit;
		$limit2= $limit+1;
		
		if (is_string($sqlQueryOrResult)) {
			//$sqlQuery = "SELECT * FROM `$table` $where ORDER BY $by DESC LIMIT $start,$limit";
			$sqlQueryOrResult = $_this->query($sqlQueryOrResult) or die($_this->error());
		}
		$total = $_this->numRows($sqlQueryOrResult);
		
		if ( $total >= $limit ) {
			$next_link = '<a href="'.$link.$st_next.'">'.$nextWord.'&gt;</a>';
		}
		$a = $start - $limit;
		if ($a > -1) {
			$prev_link = '<a href="'.$link.$a.'">&lt;'.$previousWord.'</a>';
		}
		$aa['prev'] = $prev_link;
		$aa['next'] = $next_link;
		$aa['num'] = $num;
		return $aa;
	}
	
	
	
	
	/**
	* @example 
	* $tableName='articles';
	* $fields=array('title_farsi'=>'title_farsi_sorted');
	* cmfcMySQLCorrectSorting($tableName,$fields);
	* @todo should merge with correctSorting_
	* @previousNames cmfcMySQLCorrectSorting 
	*/
	function correctSorting($tableName,$fields)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$number=0;
		$total_failed=0;
		$sqlQuery="SELECT * FROM `Books`";
		$sqlResult = $_this->query($sqlQuery);
		$total = $_this->numRows($sqlResult);
		if ($total > 0)
		while ($row = $_this->fetchArray($sqlResult))
		{
			$number++;
			$encoded=fa_encode($row['MainBookName']);
			$update_sql_query="UPDATE `BooksPlus` SET `MainBookNameSortFarsi`='".$encoded."' WHERE `BookCode`='$row[BookCode]' LIMIT 1";
			$update_sql_result=$_this->query($update_sql_query);
			if (!$update_sql_result or 1)
			{
				$insert_sql_query = "INSERT INTO `BooksPlus` (`BookCode`,`MainBookNameSortFarsi`) VALUES ('$row[BookCode]','$encoded')";
				$insert_sql_result=$_this->query($insert_sql_query);
				//echo $_this->error($insert_sql_query);
				//if (!$insert_sql_result) { $total_failed++;}
			}
			if (is_integer($number/500)) {echo "$number of $total -> $total_failed failed until now<br/>"; flush();}
			//echo $_this->error($insert_sql_query);
			//echo "$number of $total <br/>";
			//if ($number>1000) {break;}
		}
		if ($number==$total) {echo "$number of $total -> 100%";} else {echo "$number of $total -> کاملا انجام نشد";}
	}
	
	/**
	* @todo should merge with correctSorting
	*/
	function correctSorting_($tableName,$main_field_name,$sort_field_name)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$number=0;
		$total_failed=0;
		$sqlQuery="SELECT * FROM `$tableName`";
		$sqlResult = $_this->query($sqlQuery);
		$total = $_this->numRows($sqlResult);
		if ($total > 0)
		while ($row = $_this->fetchArray($sqlResult))
		{
			$number++;
			$encoded=fa_encode($row[$main_field_name]);
			$update_sql_query="UPDATE `$tableName` SET `$sort_field_name`='".$encoded."' WHERE `$main_field_name`='$row[$main_field_name]' LIMIT 1";
			$update_sql_result=$_this->query($update_sql_query);
	
			if (is_integer($number/500)) {echo "$number of $total -> $total_failed failed until now<br/>"; flush();}
			//echo $_this->error($insert_sql_query);
			//echo "$number of $total <br/>";
			//if ($number>1000) {break;}
		}
		if ($number==$total) {echo "$number of $total -> 100%";} else {echo "$number of $total -> کاملا انجام نشد";}
	}
	
	
	function getUpdateSql($tableName,$keyColumnName,$columnsValues,$keyColumnValue) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="UPDATE `$tableName` SET ";
	
		foreach ($columnsValues  as $columnName=>$columnValue) {
			if ($_this->isStatement($columnValue)) {
				$sqlQuery.="$comma `$columnName`=".$columnValue->getValue()."";
			} else {
				$columnValue=$_this->smartEscapeString($columnValue);
				if ($columnValue===NULL) {$columnValue='NULL';} else {$columnValue="'$columnValue'";}
				$sqlQuery.="$comma `$columnName`=$columnValue";
			}
			
			if (!isset($comma)) {$comma=',';}
		}
	
		$sqlQuery.=" WHERE `$keyColumnName`='$keyColumnValue'";
		return $sqlQuery;
	}
	
	/**
	* @desc 
	* @param string $sqlWhere //contains sql where condition including WHERE keyword itself
	*/
	function getUpdateSqlCustom($tableName,$columnsValues,$sqlWhere=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="UPDATE `$tableName` SET ";
	
		foreach ($columnsValues  as $columnName=>$columnValue) {
			if ($_this->isStatement($columnValue)) {
				$sqlQuery.="$comma `$columnName`=".$columnValue->getValue()."";
			} else {
				$columnValue=$_this->smartEscapeString($columnValue);
				if ($columnValue===NULL) {$columnValue='NULL';} else {$columnValue="'$columnValue'";}
				$sqlQuery.="$comma `$columnName`=$columnValue";
			}
			
			if (!isset($comma)) {$comma=',';}
		}
	
		$sqlQuery.=" $sqlWhere";
		return $sqlQuery;
	}
	
	/**
	* Sample :
	* <code>
	* cmfcMySql::updateCustom('tableName',array('age'=>'5'),"WHERE id=5");
	* </code>
	* @desc 
	* @param string $sqlWhere //contains sql where condition including WHERE keyword itself
	*/
	function updateCustom($tableName,$columnsValues,$sqlWhere=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery=$_this->getUpdateSqlCustom($tableName,$columnsValues,$sqlWhere);
	
		$sqlQueryResult=$_this->exec($sqlQuery);

		if ($sqlQueryResult!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Sample :
	* <code>
	* cmfcMySql::update('tableName','id',array('age'=>'5'),5);
	* </code>
	*/
	function update($tableName,$keyColumnName,$columnsValues,$keyColumnValue) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery=$_this->getUpdateSql($tableName,$keyColumnName,$columnsValues,$keyColumnValue);
	
		$sqlQueryResult=$_this->exec($sqlQuery);

		if ($sqlQueryResult!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	
	function getLoadSql($tableName,$keyColumnName,$keyColumnValue) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT * FROM `$tableName` WHERE ";
		
		if ($_this->isStatement($keyColumnValue)) {
			$sqlQuery.="`$keyColumnName`=".$keyColumnValue->getValue()." ";
		} else {
			$keyColumnValue=$_this->smartEscapeString($keyColumnValue);
			$sqlQuery.="`$keyColumnName`='$keyColumnValue' ";
		}
	
		$sqlQuery.="LIMIT 1";
		return $sqlQuery;
	}
	
	function load($tableName,$keyColumnName,$keyColumnValue) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery=$_this->getLoadSql($tableName,$keyColumnName,$keyColumnValue);
		//if ($tableName=='email_templates') echo $sqlQuery;
		$sqlResult=$_this->query($sqlQuery);
		if ($sqlResult)
		if ($_this->numRows($sqlResult)>0) {
			$row=$_this->fetchArray($sqlResult,MYSQL_ASSOC);
			return $row;
		}
		return false;
	}
	
	
	/**
	* @desc 
	* @example
	* <code>
	*   $keyColumnsValues = array(
	* 	  "username"=>$_POST['forumUsername'],
	* 	  "password"=>cmfcMySql::asStatement("MD5(CONCAT(MD5('".$_POST['forumPassword']."'),`salt`))")
	*   );
	*   $columnsValues = array(
	* 	  "username"=>$userSystem->cvUsername,
	* 	  "password"=>$vb->getInsertablePassword($_POST['sitePassword']),
	* 	  "salt"=>$vb->salt
	*   );
	*   cmfcMySql::updateWithMultiKeys($vb->userTableName,$keyColumnsValues,$columnsValues);
	* </code>
	*/
	function updateWithMultiKeys($tableName,$keyColumnsValues,$columnsValues) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="UPDATE `$tableName` SET ";
		$sqlWhere=' WHERE ';
		$comma='';

		foreach ($keyColumnsValues as $keyColumnName=>$keyColumnValue) {
			//unset($columnsValues[$keyColumnName]);
			
			if ($_this->isStatement($keyColumnValue)) {
				$sqlWhere.="$comma `$keyColumnName`=".$keyColumnValue->getValue()."";
			} else {
				$keyColumnValue=$_this->smartEscapeString($keyColumnValue);
				$sqlWhere.="$comma `$keyColumnName`='$keyColumnValue' ";
			}
			
			$comma=' AND ';
		}
		$comma='';
		foreach ($columnsValues  as $columnName=>$columnValue) {
			if ($_this->isStatement($columnValue)) {
				$sqlQuery.="$comma `$columnName`=".$columnValue->getValue()."";
			} else {
				$columnValue=$_this->smartEscapeString($columnValue);
				if ($columnValue===NULL) {$columnValue='NULL';} else {$columnValue="'$columnValue'";}
				$sqlQuery.="$comma `$columnName`=$columnValue";
			}
			if (empty($comma)) {$comma=',';}
		}
		$sqlQuery.=$sqlWhere;
		$sqlQueryResult=$_this->exec($sqlQuery);

		if ($sqlQueryResult!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	* @desc 
	* @example
	* <code>
	*   $keyColumnsValues = array(
	* 	  "username"=>$_POST['forumUsername'],
	* 	  "password"=>cmfcMySql::asStatement("MD5(CONCAT(MD5('".$_POST['forumPassword']."'),`salt`))")
	*   );
	*   cmfcMySql::deleteWithMultiKeys($vb->userTableName,$keyColumnsValues,$columnsValues);
	* </code>
	*/
	function deleteWithMultiKeys($tableName,$keyColumnsValues) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="DELETE FROM `$tableName` ";
		$sqlWhere=' WHERE ';
		$comma='';

		foreach ($keyColumnsValues as $keyColumnName=>$keyColumnValue) {
			//unset($columnsValues[$keyColumnName]);
			
			if ($_this->isStatement($keyColumnValue)) {
				$sqlWhere.="$comma `$keyColumnName`=".$keyColumnValue->getValue()."";
			} else {
				$keyColumnValue=$_this->smartEscapeString($keyColumnValue);
				$sqlWhere.="$comma `$keyColumnName`='$keyColumnValue' ";
			}
			
			$comma=' AND ';
		}
		$sqlQuery.=$sqlWhere;
		$sqlQueryResult=$_this->exec($sqlQuery);

		if ($sqlQueryResult!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	
	function loadWithMultiKeys($tableName,$keyColumnsValues) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT * FROM `$tableName` WHERE ";
		
		foreach ($keyColumnsValues as $keyColumnName=>$keyColumnValue) {
			
			if ($_this->isStatement($keyColumnValue)) {
				$sqlQuery.="$comma `$keyColumnName`=".$keyColumnValue->getValue()."";
			} else {
				$keyColumnValue=$_this->smartEscapeString($keyColumnValue);
				$sqlQuery.="$comma `$keyColumnName`='$keyColumnValue' ";
			}
			
			$comma=' AND ';
		}

		$sqlResult=$_this->query($sqlQuery);
		if ($sqlResult)
		if ($_this->numRows($sqlResult)>0)
		{
			$row=$_this->fetchArray($sqlResult,MYSQL_ASSOC);
			return $row;
		}
		return false;
	}
	
	
	function loadCustom($sqlQuery) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlResult=$_this->query($sqlQuery);
		if ($sqlResult)
		if ($_this->numRows($sqlResult)>0)
		{
			$row=$_this->fetchArray($sqlResult,MYSQL_ASSOC);
			return $row;
		}
		return false;
	}
	
	
	function getInsertSql($tableName,$columnsValues) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="INSERT INTO `$tableName` SET ";
	
		foreach ($columnsValues as $columnName=>$columnValue) {
			if ($_this->isStatement($columnValue)) {
				$sqlQuery.="$comma `$columnName`=".$columnValue->getValue()."";
			} else {
				$columnValue=$_this->smartEscapeString($columnValue);
				if ($columnValue===NULL) {$columnValue='NULL';} else {$columnValue="'$columnValue'";}
				$sqlQuery.="$comma `$columnName`=$columnValue";
			}
			if (!isset($comma)) {$comma=',';}
		}
		return $sqlQuery;
	}
	
	function insert($tableName,$columnsValues) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery=$_this->getInsertSql($tableName,$columnsValues);
	
		//$sqlQuery.=" WHERE `$this->coln_id`='".$columnsValues[$this->coln_id]."' LIMIT 1";
		/*
		$ccc=@file_get_contents(dirname(__FILE__).'/sql.log');
		$ccc=$ccc."\n".$sqlQuery;
		file_put_contents(dirname(__FILE__).'/sql.log',$ccc);
		*/
		
		$sqlQueryResult=$_this->exec($sqlQuery);
		
		if ($sqlQueryResult!==false)
			return true;
		else
			return false;
	}
	
	
	function getDeleteSql($tableName,$keyColumnName,$keyColumnValue,$limit=1) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (!is_null($limit))
			$limitClause = "LIMIT $limit";
		else
			$limitClause = '';
		$keyColumnValue=$_this->smartEscapeString($keyColumnValue);
		if ($_this->isStatement($keyColumnValue)) {
			$sqlQuery="DELETE FROM `$tableName` WHERE `$keyColumnName`=".$keyColumnValue->getValue()." $limitClause";
		} else {
			$sqlQuery="DELETE FROM `$tableName` WHERE `$keyColumnName`='$keyColumnValue' $limitClause";
		}
		return $sqlQuery;
	}
	
	
	function delete($tableName,$keyColumnName,$keyColumnValue,$limit=1) {
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery=$_this->getDeleteSql($tableName,$keyColumnName,$keyColumnValue,$limit);
		$sqlQueryResult=$_this->exec($sqlQuery);
		
		if ($sqlQueryResult!==false)
			return true;
		else
			return false;
	}
	
	
	
	function getNextRow($tableName,$sortType,$sortByColumnName,$sortByColumnValue,$sqlWhere,$limit=1) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (strtolower($sortType)=='desc') {$sortType='asc';$operator='>';} else {$sortType='desc';$operator='<';}
		if (!is_null($sqlWhere)) {$sqlWhere="AND ($sqlWhere)";}
		$sqlQuery="SELECT * FROM `$tableName` 
					WHERE `$sortByColumnName` $operator '$sortByColumnValue' $sqlWhere
					ORDER BY `$sortByColumnName` $sortType LIMIT $limit";
		if ($limit>1)
			return $_this->getQueryResultAsArray($sqlQuery,null,false);
		else
			return $_this->getQueryResultAsArray($sqlQuery);
	}
	
	function getPrevRow($tableName,$sortType,$sortByColumnName,$sortByColumnValue,$sqlWhere,$limit=1) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (strtolower($sortType)=='desc') {$operator='<';} else {$operator='>';}
		if (!is_null($sqlWhere)) {$sqlWhere="AND ($sqlWhere)";}
		$sqlQuery="SELECT * FROM `$tableName` 
					WHERE `$sortByColumnName` $operator '$sortByColumnValue' $sqlWhere
					ORDER BY `$sortByColumnName` $sortType LIMIT $limit";
		if ($limit>1)
			return $_this->getQueryResultAsArray($sqlQuery,null,false);
		else
			return $_this->getQueryResultAsArray($sqlQuery);
	}
	
	function getLatestRow($tableName,$sortType,$sortByColumnName,$sqlWhere,$limit=1) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (!is_null($sqlWhere)) {$sqlWhere="WHERE ($sqlWhere)";}
		$sqlQuery="SELECT * FROM `$tableName` $sqlWhere 
					ORDER BY `$sortByColumnName` $sortType LIMIT $limit";
		if ($limit>1)
			return $_this->getQueryResultAsArray($sqlQuery,null,false);
		else
			return $_this->getQueryResultAsArray($sqlQuery);
	}
	
	/**
	* Find the next non used number in the table,
	* For example if you want to add 10 records and let them to only use the numbers between 1-10 and keep this
	* condition even when one of the deleted and new one added. so new record can automatically have the deleted record
	* number
	* There are two algorithem :
	* 	- incremental : fast
	* 	- division : very fast but not implemented. it divies rows to 2 then the one which my have the missing number to 2 again an so on
	* 
	* @param $tableName string
	* @param $numberColumnName string
	* @param $defaultNumber interger
	* @param $method string incremental/division
	*/
	function getNextNumber($tableName,$numberColumnName,$defaultNumber,$method='incremental')
	{
		$sqlQuery="SELECT COUNT(*) AS 'totalRows', max(`$numberColumnName`) AS 'maxCode' FROM `$tableName`";
		$row=cmfcMySql::loadCustom($sqlQuery);

		if ($row['maxCode']<$defaultNumber) {
			$row['maxCode']=$defaultNumber;
			
		} elseif ($row['totalRows']==$row['maxCode']) {
			$i=$row['maxCode']+1;
			
		} else {
			$limitLength=500;
			$steps=round($row['maxCode']/$limitLength);
			//echo '<pre style="direction:ltr;text-align:left">';
			$i=$defaultNumber;
			for ($step=0;$step<=$steps;$step++) {
				
				$limitStart=$step*$limitLength;
				$limitEnd=$limitStart+$limitLength;
				
				if ($limitStart>=$i) {
					//echo "select doctorCode as 'maxCode' from doctor ORDER By doctorCode ASC LIMIT $limitStart,$limitLength";
					$sqlQuery="SELECT COUNT(`$numberColumnName`) AS 'totalRows' FROM `$tableName` WHERE `$numberColumnName`>=$limitStart AND `$numberColumnName`<$limitEnd";
					$__row = cmfcMySql::loadCustom($sqlQuery);
					//echo " | {$step}-($limitStart to $limitEnd)-{$__row['totalRows']}-".($limitEnd-$limitStart);
					
					if ($__row['totalRows']!=$limitEnd-$limitStart) {
						$__result = cmfcMySql::query("SELECT `$numberColumnName` FROM `$tableName` WHERE `$numberColumnName`>=$limitStart AND `$numberColumnName`<$limitEnd ORDER BY `$numberColumnName` ASC");
						$i=$limitStart-1;
						while ($__row = cmfcMySql::fetchArray($__result, MYSQL_ASSOC)) {
							$i++;
							//echo "<br />$i={$__row['doctorCode']}";
							if ($i!=$__row['doctorCode']) {
								$found=true;
								break;
							}
						}
					}
					if ($found==true) {
						break;
					}
					//echo '<br />';
				}
				
			}
			//echo '</pre>';
		}

		
		closeConnection($link);
		
		return $i;
	}
	
	
	function getChildPath($nodeId,$rowInfo=false,$tableName,$idColumnName,$parentIdColumnName) {
		$_this=&cmfcMySql::getInstance(&$this);
		// look up the parent of this node
		$sqlQuery="SELECT * FROM `$tableName` "."WHERE `$idColumnName`='$nodeId'";
		$result = $_this->query($sqlQuery);
		$row = $_this->fetchArray($result);
		
		// save the path in this array [5]
		$path = array();
		
		// only continue if this $node isn't the root node
		// (that's the node with no parent)
		if ($row[$parentIdColumnName]!='') {
			// the last part of the path to $node, is the name
			// of the parent of $node
			if ($rowInfo==false)
				$path[] = $row[$parentIdColumnName];
			else
				$path[] = $row;
			// we should add the path to the parent of this node
			// to the path
			$path = cmfcPhp4::array_merge($_this->getChildPath($row[$parentIdColumnName],$rowInfo,$tableName,$idColumnName,$parentIdColumnName), $path);
		} else {
	
		}
		
		// return the path
		return $path;
	}
	
	
	
	
	function getRowNumberCustom($tableName, $uniqueColumnName,$uniqueColumnValue,$sqlOrder=null,$sqlWhere=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (strtolower($sortType)=='desc') {$sortType='asc';$operator='>';} 
			else {$sortType='desc';$operator='<';}
		if (!is_null($sqlWhere)) {$sqlWhere="AND ($sqlWhere)";}
		$sqlQuery="SELECT count(*) as 'row_number' FROM `$tableName` 
					WHERE `$sortByColumnName` $operator '$sortByColumnValue' $sqlWhere
					ORDER BY $sqlOrder";
		$rowNumber=$_this->getColumnValueCustom($sqlQuery,'row_number');
		
		$sqlQuery="SELECT count(*) as 'row_number' FROM `$tableName` 
					WHERE `$sortByColumnName` = '$sortByColumnValue' AND 
					`$uniqueColumnName`<='$uniqueColumnValue' 
					$sqlWhere
					ORDER BY `$uniqueColumnName` asc";
		$rowNumber=$_this->getColumnValueCustom($sqlQuery,'row_number');
		
		return $rowNumber;
	}
	
	


	/**
	* make sql query sortable via adding ORDER BY to appropiriate place
	* below regex will match signle qoutes stings
	* '('{2})*([^']*)('{2})*([^']*)('{2})*'
	* @example 
	* 	<code>
	* 		$_this->getSortedQuery('SELECT * FROM table','name','DESC')
	* 	</code>
	* 	result whould be "SELECT * FROM table ORDER BY `name` DESC"
	* @todo
	* 	+ accepts multi fields for sorting
	* 	- using regexp for adding "Order By"
	* @previousNames get_sorted_query
	* @param $sqlQuery string
	* @param $byFieldName array|string
	* @param $sortType array|string
	* @return string
	*/
	function getSortedQuery($sqlQuery,$byFieldName,$sortType='ASC')
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$orderByQuery=" ORDER BY ";
		if (is_array($byFieldName)) {
			$comma='';
			foreach ($byFieldName as $key=>$myFieldName) {
				if (is_array($sortType))
					$mySortType=$sortType[$key];
				else
					$mySortType=$sortType;
				$orderByQuery.=" $comma `$myFieldName` $mySortType";
				$comma=',';
			}
		} else {
			$orderByQuery.="`$byFieldName` $sortType";
		}
		$__sqlQuery = preg_replace('/(.*)(LIMIT [0-9,]* *$)/i', '$1 '.$orderByQuery.' $2', $sqlQuery);
		if ($__sqlQuery!=$sqlQuery) 
			$sqlQuery=$__sqlQuery;
		else
			$sqlQuery.=$orderByQuery;

		return $sqlQuery;
	}
	
	
	/**
	* @previousNames get_count_sql_query
	*/
	function getCountSqlQuery($sqlQuery)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		//SELECT *(,?([^,]*),?)* *FROM.*
	}
	
	
	/**
	* @previousNames get_total_rows_number_via_sql_query, countSqlQueryRows
	*/
	function getTotalRowsNumberViaSqlQuery($sqlQuery)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		if (preg_match('/^select (.*) from .*/sim', $sqlQuery,$matches,PREG_OFFSET_CAPTURE)) {
			$sqlQuery=substr_replace($sqlQuery,'count(*) as "rrrrrrrrtotalrrrrrrrrrr"',$matches[1][1],strlen($matches[1][0]));
			$r=$_this->getColumnValueCustom($sqlQuery,'rrrrrrrrtotalrrrrrrrrrr');
			return $r;
		} else {
			$sqlQueryResult=$_this->query($sqlQuery);
			return @$_this->numRows($sqlQueryResult);
		}
		return false;
	}
	
	
	/**
	* Enter description here...
	*
	* @param string $db_name
	* @param string $tableName
	* @param string $defaultColumnName
	* @param string:const $languageName
	* @param string(1) $seperator
	* @return string
	* @previousNames get_given_language_column_name, cmfcMySQLGetGivenLanguageColumnName
	*/
	function getGivenLanguageColumnName($tableName,$defaultColumnName,$languageName=LN_ENGLISH,$seperator="|")
	{
		$_this=&cmfcMySql::getInstance(&$this);
		if (empty($languageName)) {$languageName=LN_ENGLISH;}
		if (!empty($defaultColumnName) and !empty($tableName) and !empty($db_name) and !empty($languageName))
		{

			$columnName=$defaultColumnName;
			if ($languageName!='' and $languageName!=LN_ENGLISH)
			{
				$columnName=$defaultColumnName.$seperator.$languageName;
				if ($_this->isColumnExist($db_name,$tableName,$columnName)!=$columnName)
					{ $columnName=$defaultColumnName; };
			}
			return $columnName;
		}
	}
	
	/**
	* @previousNames change_table
	*/
	function changeTable($name,$newName,$newType=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$database_name=$_this->currentDatabaseName();
		$sqlQuery="ALTER TABLE '$database_name'.'$name' RENAME TO '$newName';";
		return $_this->exec($sqlQuery);
	}
	
	
	
	
	
	/**
	* Check and see if value of specific column is unique or not, it also
	* accept index column value to check uniquness in edit mode
	* @todo IsCellUnique or IsValueUnique are better names for this function
	* @param string $tableName
	* @param string $columnName
	* @param string $columnValue
	* @param string $idColumnName
	* @param string $idColumnValue
	* @return boolean
	* @previousNames is_row_column_unique
	*/
	function isRowColumnUnique($tableName,$columnName,$columnValue,$idColumnName,$idColumnValue)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SELECT `$columnName` FROM `$tableName` WHERE (`$columnName`='$columnValue') ";
		if ($idColumnValue!=null or $idColumnValue!='')
		{
			$sqlQuery=$sqlQuery."AND (`$idColumnName`<>'$idColumnValue')";
		}
		if ($_this->numRows($_this->query($sqlQuery))>0) { return false; } else { return true; }
	}
	
	
	
	
	
	/**
	* Enter description here...
	*
	* @param string $tableName
	* @param string $columnName
	* @param $sqlQueryResult
	* @param integer $columnIndex
	*/
	function getColumnsMetaBySqlResult($sqlQueryResult=null,$columnIndex=null,$columnName=null,$tableName=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		if (!is_null($tableName) and empty($sqlQueryResult))
		{
			$sqlQueryResult=$_this->execute_sql("SELECT * FROM `$tableName`");
		}
		$result='';
		$columnMeta=array();
		$columnsMeta=array();
		$tableColumnsMeta=array();
		$num_columns=$_this->numFields($sqlQueryResult);
		$i=-1;
		while ($i < $num_columns-1)
		{
			$i++;
			//if ($columnIndex!=$i and is_null($columnIndex)) {continue;}
			$columnMeta=array();
			$columnObject=$_this->fetchField($sqlQueryResult,$i);
			//echo $column_flags=mysql_field_flags($sqlQueryResult,$i);
			//if ($columnObject->name!=$columnName and is_null($columnName)) {continue;}
			
			//if (strpos($column_flags,'auto_increment')!=false) { $columnMeta['auto_increment']=true;} else {$columnMeta['auto_increment']=false;}
			$columnMeta['name']=$columnObject->name;
			$columnMeta['table']=$columnObject->table;
			$columnMeta['type']=$_this->typeToStandardType($columnObject->type);
			$columnMeta['length']=$columnObject->max_length;
			$columnMeta['null']=!$columnObject->not_null;
			$columnMeta['primary_key']=$columnObject->primary_key;
			$columnMeta['unique_key']=$columnObject->unique_key;
			$columnMeta['multiple_key']=$columnObject->multiple_key;
			$columnMeta['unsigned']=$columnObject->unsigned;
			$columnMeta['zerofill']=$columnObject->zerofill;
			$columnMeta['numeric']=$columnObject->numeric;
			$columnMeta['multiple_key']=$columnObject->multiple_key;
			$columnMeta['unique_key']=$columnObject->unique_key;
			$columnMeta['blob']=$columnObject->blob;
			$columnMeta['binary']=null;
			$columnMeta['enum']=null;
			$columnMeta['timestamp']=null;
			
			if (!empty($columnObject->table))
			if (!isset($tableColumnsMeta[$columnObject->table]))
			{
				$tableColumnsMeta[$columnObject->table]=$_this->getColumnsMeta($columnObject->table);
			}
			
			if (isset($tableColumnsMeta[$columnObject->table]))
				if (isset($tableColumnsMeta[$columnObject->table][$columnObject->name]))
					$columnMeta=cmfcPhp4::array_merge($columnMeta,$tableColumnsMeta[$columnObject->table][$columnObject->name]);
			
			$columnsMeta[$columnMeta['name']]=$columnMeta;
			if ($i==$columnIndex or $columnMeta['name']==$columnName) {$result=$columnsMeta[$columnMeta['name']];};
		}
		if (is_null($columnName) and is_null($columnIndex)) {return $columnsMeta;} else {return $result;}
	}
	
	
	
	/**
	* @desc 
	* @previousNames mysql_type_to_standard_type
	*/
	function typeToStandardType($type)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		switch ($type) {
			case 'int'		:$result=PCT_INTEGER; break;
			case 'text'		:$result=PCT_TEXT; break;
			case 'date'		:$result=PCT_DATE; break;
			case 'time'		:$result=PCT_TIME; break;
			case 'datetime'	:$result=PCT_DATETIME; break;
			case 'varchar'	:$result=PCT_STRING; break;
			case 'blob'		:$result=PCT_BLOB; break;
			case 'timestamp':$result=PCT_TIMESTAMP; break;
			default:$result=$type;
		}
		return $result;
	}
	
	
	
	
	/**
	* Enter description here...
	*
	* @param string $tableName
	* @param string $columnName
	* @return array
	* @previousNames get_columns_meta
	*/
	function getColumnsMeta($tableName,$columnName=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$columnsMeta=array();
		
		//--(BEGIN)-->when i use UNION sql keyword some of column details like "primary key","auto_increment" doesn't detect by above code!		
		$sqlQuery="SHOW COLUMNS FROM `$tableName`";
		if (!is_null($columnName)) {$sqlQuery.=" LIKE `$columnName`";}
		$array=$_this->executeSqlScriptAsArray($sqlQuery,true,null,false,true);
		if (is_array($array))
		{
			foreach ($array as $row)
			{
				$columnMeta['default']=$row['Default'];
				$columnMeta['table']=$tableName;
				$columnMeta['name']=$row['Field'];
			
				preg_match("/([^() ]*)(\(([0-9]+)\))?([^() ]*)/i",$row['Type'],$matches);
				$columnMeta['type']=$matches[1];
				$columnMeta['length']=$matches[3];
			
				if ($row['Null']=='YES') { $columnMeta['null']=true;} else {$columnMeta['null']=false;}
				if (strpos($row['Key'],'PRI')!==false) { $columnMeta['primary_key']=true;} else {$columnMeta['primary_key']=false;}
				if (strpos($row['Extra'],'auto_increment')!==false) { $columnMeta['auto_increment']=true;} else {$columnMeta['auto_increment']=false;}
				if (strpos($row['Type'],'zerofill')!==false) { $columnMeta['zerofill']=true;} else {$columnMeta['zerofill']=false;}
				if (strpos($row['Type'],'unsigned')!==false) { $columnMeta['unsigned']=true;} else {$columnMeta['unsigned']=false;}
			
				$columnMeta['type']=$_this->typeToStandardType($columnMeta['type']);
				$columnsMeta[$columnMeta['name']]=$columnMeta;
			}
			if (is_null($columnName)) {return $columnsMeta;} else {return $columnMeta;}
		} else {return false;}
		//--(END)-->when i using UNION sql keyword some of column details like primary keys doesn't detect by above code!
		
		
		//SHOW KEYS FROM test
		if (0)
		{
			$row = $_this->executeSqlScriptAsArray("SHOW COLUMNS FROM `$tableName` LIKE `$columnName`",false,false,false,true);
			$result=array();
			if (!empty($row))
			{
				$string_create_table=$row[1];
				$pattern='/CREATE *TABLE *`[^`]*` *\((.*)\)(?!CREATE *TABLE *`[^`]*` *)(.*)/is';
				if (preg_match_all($pattern,$string_create_table,$matches))
				{
					$string_columns_meta=$matches[1];
					$pattern='/([^\r\n]*)/is';
					if (preg_match_all($pattern,$string_columns_meta,$matches))
					{
						foreach ($matches[1] as $string_column_meta)
						{
							
						}
					}
				}
			}
		}
		
		/*
		([^,\"'`\(\\)]+(?=,|$))|\"[^\"]*\"|\([^\(]*\)|'[^']*'|`[^`]*`
		matches : sdafasd,'',(,,),",fasf",`fdsa,`,
		
		"([^"](?:\\.|[^\\"]*)*)"
		escape string match : "This is a \"string\"."
		*/
	}
	
	/**
	* add "COUNT" keyword to desired sql query
	*
	* @param string $sqlQuery
	* @return string
	* @previousNames add_count_to_sql_query
	*/
	function addCountToSqlQuery($sqlQuery="SELECT * FROM `table_name`")
	{
		$_this=&cmfcMySql::getInstance(&$this);
		if (preg_match('/^select (.*) from .*/sim', $sqlQuery,$matches,PREG_OFFSET_CAPTURE)) {
			return substr_replace($sqlQuery,'count(*)',$matches[1][1],strlen($matches[1][0]));
		}
		return false;
		//return preg_replace('','count(*)',$sqlQuery);
	}
	
	
	/**
	* Enter description here... 
	* @param string $sqlQuery
	* @return integer
	* @previousNames count_sql_query_rows,
	* @depricated getTotalRowsNumberViaSqlQuery
	*/
	function countSqlQueryRows($sqlQuery)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		return $this->getTotalRowsNumberViaSqlQuery($sqlQuery); 
	}
	
	
	
	/**
	* Enter description here...
	*
	* @param string $db_name
	* @param string $tb_name
	* @param string $co_name
	* @return boolean
	* @previousNames is_column_exist
	*/
	function isColumnExist($db_name,$tb_name,$co_name)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		//i test this code and it worked but i comment it for some reasons
		/*
		$qr_query="select $fd_name from $tb_name";
		$qr_result=$_this->query($qr_query);
		if ($qr_result)
		if (mysql_num_columns($qr_result)>=0)
		if (mysql_column_name($qr_result,0)==$fd_name)
		{ return $fd_name; }
		*/
		$currentDatabaseConnection=$_this->currentDatabaseConnection();
		//$db_name=$_this->connectionContainer[$_this->currentAlias]['database'];
		if ($_this->isTableExist($db_name,$tb_name))
		{
			if (!empty($db_name) and !empty($tb_name) and !empty($currentDatabaseConnection))
			{
				$columns = $_this->listFields($db_name,$tb_name,$currentDatabaseConnection);
				$total = $_this->numFields($columns);
	
				for ($i = 0; $i < $total; $i++)
				{
					if ($_this->fieldName($columns, $i)==$co_name) { return $co_name; }
				}
			}
		} else { $_this->raiseError("table '$tb_name' does not exist in database '$db_name'");}
		return false;
	}
		
		
	/**
	* i tested this code and worked but i comment it for some reasons
	*
	* @param string $db_name
	* @param string $tb_name
	* @return boolean
	* @previousNames is_table_exist
	*/
	function isTableExist($db_name,$tb_name)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		//$currentDatabaseConnection=$_this->currentDatabaseConnection;
		//$db_name=$_this->connectionContainer[$_this->currentAlias]['database'];
		if (!empty($db_name) and !empty($tb_name) and !empty($currentDatabaseConnection))
		{
			$tables = $_this->listTables($db_name,$currentDatabaseConnection);
			$total = $_this->numRows($tables);
			for ($i = 0; $i < $total; $i++)
			{
				if ($_this->tableName($tables, $i)==$tb_name) { return true; }
			}
		}
		return false;
	}
	
	
	function getTablesList($myDbName=null,$prefix=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SHOW TABLES";
		if (!is_null($myDbName)) 
			$sqlQuery.=" FROM $myDbName ";
		if (!is_null($prefix)) 
			$sqlQuery.=' LIKE "'.$prefix.'%"';
		$patternName=" ($prefix%)";
		
		$result=$_this->getRowsCustom($sqlQuery);
		$mresult=array();
		
		if (is_array($result))
		foreach ($result as $tableInfo) {
			foreach ($tableInfo as $dbName=>$tableName) {
				$dbName=str_replace('Tables_in_','',$dbName);
				$dbName=str_replace($patternName,'',$dbName);
				if (!is_null($myDbName) and $myDbName==$dbName) {
					$mresult[]=$tableName;
				} else {
					$mresult[$dbName][]=$tableName;
				}
			}
		}
		return $mresult;
	}
	
	
	function getColumnsList($myTableName=null,$myDbName=null)
	{
		$_this=&cmfcMySql::getInstance(&$this);
		$sqlQuery="SHOW COLUMNS";
		if (!is_null($myTableName)) 
			$sqlQuery.=" FROM $myTableName ";
		if (!is_null($myDbName)) 
			$sqlQuery.=" FROM $myDbName ";
		/*
		if (!is_null($prefix)) 
			$sqlQuery.=' LIKE "'.$prefix.'%"';
		
		$patternName=" ($prefix%)";
		*/
		$result=$_this->getRowsCustom($sqlQuery);
		$mresult=array();
		if (is_array($result))
		foreach ($result as $columnInfo) {
			$mresult[]=array(
				'name'=>$columnInfo['Field']
			);
		}
		return $mresult;
	}
	
	/**
	* convert query row result physical column name to virtaul column names and vise versa
	* @example
	* <code>
	* $columnsValues=array(
	* 	'id'=>5
	* 	'internal_name'=>12
	* )
	* $columnsNames=array(
	* 	'id'=>'id',
	* 	'internalName'=>'internal_name'
	* )
	* $columnsValues=$$_this->convertColumnNames($columnsValues,$columnsNames);
	* </code>
	* result would be :
	* <code>
	* $columnsValues=array(
	* 	'id'=>5
	* 	'internalName'=>12
	* )
	* </code>
	* @param array
	* @param array
	* @param string
	* @return array
	*/
	function convertColumnNames($columnsValues, $columnsNames, $mode=''){
		$_this=&cmfcMySql::getInstance(&$this);

		if (is_array($columnsValues) && is_array($columnsNames)){
			$convertedColumns = '';
			foreach($columnsNames as $columnName => $columnPhysicalName){
				if (array_key_exists($columnPhysicalName,$columnsValues)) {
					$convertedColumns[$columnName] = $columnsValues[$columnPhysicalName];
				} elseif (array_key_exists($columnName,$columnsValues)) {
					$convertedColumns[$columnPhysicalName] = $columnsValues[$columnName];
				}
			}
			return $convertedColumns;
		}
		return false;
	}
	
	
	/**
	 * Safely explode sql queries by the defined delimiter and returns
	 * an array containing the queries
	 * @notice This function is slow, only use it when performance is not important
	 * or when there is no other way
	 * <code>
	 * $sql="
	 *    SELECT * FROM table2 WHERE id=5;
	 *    SELECT * FROM table2 WHERE id='5';
	 *    SELECT (SELECT b FROM test WHEN content=\"ali;dali\") FROM table2 WHERE id='5';
	 *    UPDATE test2 SET `desc`=';;asdfdsaf;\;asdf'
	 *    SELECT * FROM table2 WHERE id=5;
	 * ";
	 * $r=multyQuery($sql);
	 * cmfcHtml::printr($r); 
	 * </code>
	 * @todo
	 * - Fast mode , only works when there is no comment inside the sql or when delimited is unique
	 * @param $queryBlock
	 * @param $delimiter
	 * @return array
	 */
	function explodeSqlQueries ( $queryBlock, $delimiter = ';' ,$options=array()) {
		$inString = false;
		$inStringType=null;
		$inComment = false;
		$commentType=null;
		$escaped = false;
		$notYet=false;
		$sqlBlockLen=strlen($queryBlock);
		//$sqlBlockLen=1000;
		$queries=array();
		$endOfQuery=false;
		$query='';
		$queryComment='';
		$previousQueryPos=array();
		for ( $i = 0; $i < $sqlBlockLen; $i++ ) {
			$notYet=false;
			$charCurrent=$queryBlock[$i];
			if ($i>0) {
				$charBehind=$queryBlock[$i-1];
			} else {
				$charBehind=null;
			}
			if ($i<$sqlBlockLen) {
				$charForward=$queryBlock[$i+1];
			} else {
				$charForward=null;
			}
			if ($i<$sqlBlockLen-1) {
				$charDblForward=$queryBlock[$i+2];
			} else {
				$charDblForward=null;
			}
			
			if ($charBehind=='\\' and !$inComment) {
				$escaped=true;
			} else {
				$escaped=false;
			}
		    
			if (($inString!=true or $inStringType!='\'') and $charCurrent=='"' and !$escaped and !$inComment) {
				if ($inString==true) {
					$inString=false;
					$inStringType!=null;
				} else {
					$inString=true;
					$inStringType='"';
				}
			}
			if (($inString!=true or $inStringType!='"') and $charCurrent=='\'' and !$escaped and !$inComment) {
				if ($inString==true) {
					$inString=false;
					$inStringType!=null;
				} else {
					$inString=true;
					$inStringType='\'';
				}
			}
			
			
			if (($inComment!=true or $commentType!='-- ') and !$inString) {
				if ($charCurrent=='/' and $charForward=='*') {
					$commentType='/**/';
					$inComment=true;
				}
				if ($inComment==true and $charBehind=='*' and $charCurrent=='/') {
					$commentType=null;
					$inComment=false;
					$queryComment='';
					$notYet=true;
				}
			}

			if (($inComment!=true or $commentType!='/**/') and !$inString) {
				if ($charCurrent=='-' and $charForward=='-' and $charDblForward=' ') {
					$commentType='-- ';
					$inComment=true;					
				}
				if ($inComment==true and ($charBehind=="\r" or $charBehind=="\n") and ($charCurrent=="\n" or $charCurrent=="\r")) {
					$commentType=null;
					$inComment=false;
					$queryComment='';
					$notYet=true;
				}
			}
						
			if ($inComment!=true and $notYet!=true) {
				$query.=$charCurrent;
			} else {
				$queryComment.=$charCurrent;
			}
			
			if ($charCurrent==$delimiter and $inString!=true and $escaped!=true and $inComment!=true) {
				$endOfQuery=true;
			} else {
				$endOfQuery=false;
			}
			/*
			$debug=array(
				'$charNumber'=>$i,
				'$charBehind'=>$charBehind,
				'$charCurrent'=>$charCurrent,
				'$charForward'=>$charForward,
				'$charDblForward'=>$charDblForward,
				'$inString'=>$inString,
				'$inStringType'=>$inStringType,
				'$inComment'=>$inComment,
				'$commentType'=>$commentType,
				'$escaped'=>$escaped,
				'$endOfQuery'=>$endOfQuery,
				'$queryComment'=>$queryComment,
				'$query'=>$query,
			);
			cmfcHtml::printr($debug);
			*/
			
			if ($endOfQuery) {
				$queries[]=$query;
				$query='';
			}
		}
		//exit;
		return $queries; 
	}
	
	/**
	 * Safely explode sql queries by the defined delimiter and returns
	 * an array containing the queries
	 * @notice This function is slow, only use it when performance is not important
	 * or when there is no other way
	 * <code>
	 * $sql="
	 *    SELECT * FROM table2 WHERE id=5;
	 *    SELECT * FROM table2 WHERE id='5';
	 *    SELECT (SELECT b FROM test WHEN content=\"ali;dali\") FROM table2 WHERE id='5';
	 *    UPDATE test2 SET `desc`=';;asdfdsaf;\;asdf'
	 *    SELECT * FROM table2 WHERE id=5;
	 * ";
	 * $r=multyQuery($sql);
	 * cmfcHtml::printr($r); 
	 * </code>
	 * @author http://php4every1.com/tutorials/multi-query-function/
	 * @param $queryBlock
	 * @param $delimiter
	 * @return array
	 */
	function explodeSqlQueriesComplete ( $queryBlock, $delimiter = ';' ) {
		$inString = false;
		$escChar = false;
		$sql = '';
		$stringChar = '';
		$queryLine = array();
		$sqlRows = split ( "\n", $queryBlock );
		$delimiterLen = strlen ( $delimiter );
		do {
			$sqlRow = current ( $sqlRows ) . "\n";
			$sqlRowLen = strlen ( $sqlRow );
			for ( $i = 0; $i < $sqlRowLen; $i++ ) {
				if ( ( substr ( ltrim ( $sqlRow ), $i, 2 ) === '--' || substr ( ltrim ( $sqlRow ), $i, 1 ) === '#' ) && !$inString ) {
					break;
				}
				$znak = substr ( $sqlRow, $i, 1 );
				if ( $znak === '\'' || $znak === '"' ) {
					if ( $inString ) {
						if ( !$escChar && $znak === $stringChar ) {
							$inString = false;
						}
					}
					else {
						$stringChar = $znak;
						$inString = true;
					}
				}
				if ( $znak === '\\' && substr ( $sqlRow, $i - 1, 2 ) !== '\\\\' ) {
					$escChar = !$escChar;
				}
				else {
					$escChar = false;
				}
				if ( substr ( $sqlRow, $i, $delimiterLen ) === $delimiter ) {
					if ( !$inString ) {
						$sql = trim ( $sql );
						$delimiterMatch = array();
						if ( preg_match ( '/^DELIMITER[[:space:]]*([^[:space:]]+)$/i', $sql, $delimiterMatch ) ) {
							$delimiter = $delimiterMatch [1];
							$delimiterLen = strlen ( $delimiter );
						}
						else {
							$queryLine [] = $sql;
						}
						$sql = '';
						continue;
					}
				}
				$sql .= $znak;
			}
		} while ( next( $sqlRows ) !== false );
	
		return $queryLine;
	}
	
	
	function query($sqlQuery,$linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		$_this->__startTimer();
		
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		
		if ($_this->_noQueryExecution) return true;
		
		if ($_this->_autoParseQueriesEnabled===true) {
			$sqlQuery=$_this->parseQuery($sqlQuery);
		}
		
		$invalidKeyWords=array(
			'INSERT','UPDATE','DELETE','DROP','ALTER','CREATE','INDEX','REFERENCES'
		);
		foreach ($invalidKeyWords as $invalidKeyWord) {
			if (preg_match('/^ *'.$invalidKeyWord.' .*/si', $sqlQuery))
				$_this->raiseError('Using $_this->query for INSERT or UPDATE or DELETE or any executable STATEMENT does not allowed');
		}
		
		if (is_null($linkIdentifier)) {
			$result=mysql_query($sqlQuery);
			$_this->registerQuery($sqlQuery,$result,$_this->error($linkIdentifier));
		} else {
			$result=mysql_query($sqlQuery, $linkIdentifier);
			$_this->registerQuery($sqlQuery,$result,$_this->error($linkIdentifier));
		}
		return $result;
	}
	
	function exec($sqlQuery,$linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		
		$_this->__startTimer();
		
		if ($_this->_noQueryExecution) return true;
		
		if ($_this->_autoParseQueriesEnabled===true) {
			$sqlQuery=$_this->parseQuery($sqlQuery);
		}
		
		$invalidKeyWords=array(
			'SELECT'
		);
		foreach ($invalidKeyWords as $invalidKeyWord) {
			if (preg_match('/^ *'.$invalidKeyWord.' .*/si', $sqlQuery))
				$_this->raiseError('Using $_this->exec for SELECT does not allowed');
		}

		if (is_null($linkIdentifier)) {
			$result=mysql_query($sqlQuery);
			$_this->registerQuery($sqlQuery,$result,$_this->error($linkIdentifier));
		} else {
			$result=mysql_query($sqlQuery, $linkIdentifier);
			$_this->registerQuery($sqlQuery,$result,$_this->error($linkIdentifier));
		}
		return $result;
	}
	
	function insertId($linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return mysql_insert_id();
		else
			return mysql_insert_id($linkIdentifier);
	}
	
	function error($linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return mysql_error();
		else
			return mysql_error($linkIdentifier);
	}
	
	function listFields($dbName, $tableName, $linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return mysql_list_fields($dbName, $tableName);

		else
			return mysql_list_fields($dbName, $tableName, $linkIdentifier);
	}

	function listTables($dbName, $linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return mysql_list_tables($dbName);
		else
			return mysql_list_tables($dbName, $linkIdentifier);
	}
	
	function connect($server='localhost:3306', $username='', $password='',$newLink=false, $client_flags=0) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_connect($server, $username, $password, $newLink,$client_flags);
	}
	
	function pconnect($server='localhost:3306', $username='', $password='', $client_flags=0) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_pconnect($server, $username, $password, $client_flags);
	}
	
	function close($linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return mysql_close();
		else
			return mysql_close($linkIdentifier);
	}
	
	function selectDb($databaseName,$linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return mysql_select_db($databaseName);
		else
			return mysql_select_db($databaseName, $linkIdentifier);
	}
	
	function numRows($result) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_num_rows($result);
	}
	
	function fetchArray($result,$resultType=MYSQL_BOTH) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_fetch_array($result,$resultType);
	}
	
	function fetchRow($result) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_fetch_row($result);
	}
	
	function fetchAssoc($result) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_fetch_assoc($result);
	}
	
	function numFields($result) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_num_fields($result);
	}
	
	function fetchField($result,$field_offset=0) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_fetch_field($result,$field_offset);
	}
	
	function fieldName($result,$field_offset=0) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_field_name($result,$field_offset);
	}
	
	function tableName($result,$i) {
		$_this=&cmfcMySql::getInstance(&$this);
		return mysql_tablename($result,$i);
	}
	
	function raiseError($msg) {
		$_this=&cmfcMySql::getInstance(&$this);
		trigger_error($msg);
	}
	
	function affectedRows($linkIdentifier) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		return mysql_affected_rows($linkIdentifier);
	}
	
	function getTableColumns($tableName, $linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			return $_this->getRowsCustom("SHOW COLUMNS FROM $tableName");
		else
			return $_this->getRowsCustom("SHOW COLUMNS FROM $tableName");
	}

	function getTableColumnsName($tableName, $linkIdentifier=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (is_null($linkIdentifier)) $linkIdentifier=$_this->_connectionLink;
		if (is_null($linkIdentifier))
			$columns=$_this->getRowsCustom("SHOW COLUMNS FROM $tableName");
		else
			$columns=$_this->getRowsCustom("SHOW COLUMNS FROM $tableName");
		
		$columnsName=array();	
		if (is_array($columns)) {
			foreach ($columns as $columnInfo) {
				$columnsName[]=$columnInfo['Field'];
			}
		}
		
		return $columnsName;
	}
	
	function smartEscapeString($string) {
		$_this=&cmfcMySql::getInstance(&$this);
		
		if ($string===NULL) return $string;
		
		if (get_magic_quotes_gpc()==1) {
			$string=stripcslashes($string);
		}
		$string=mysql_real_escape_string($string);
		return $string;
	}
	
	function realEscapeString($string) {
		$_this=&cmfcMySql::getInstance(&$this);		
		if (get_magic_quotes_gpc()==1) {
			$string=stripcslashes($string);
		}
		$string=mysql_real_escape_string($string);
		return $string;
	}
	
	/**
	* @todo should remove or convert to something useful as of generallib 3
	*/
	function __getRowNumber($tableName,
							$sortType,$sortByColumnName,$sortByColumnValue,
							$uniqueColumnName,$uniqueColumnValue,$sqlWhere=null) {
		$_this=&cmfcMySql::getInstance(&$this);
		if (strtolower($sortType)=='desc') {$sortType='asc';$operator='>';} else {$sortType='desc';$operator='<';}
		if (!is_null($sqlWhere)) {$sqlWhere="AND ($sqlWhere)";}
		$sqlQuery="SELECT count(*) as 'row_number' FROM `$tableName` 
					WHERE `$sortByColumnName` $operator '$sortByColumnValue' $sqlWhere
					ORDER BY `$sortByColumnName` $sortType";
		$rowNumber=$_this->getColumnValueCustom($sqlQuery,'row_number');
		
		$sqlQuery="SELECT count(*) as 'row_number' FROM `$tableName` 
					WHERE `$sortByColumnName` = '$sortByColumnValue' AND 
					`$uniqueColumnName`<='$uniqueColumnValue' 
					$sqlWhere
					ORDER BY `$uniqueColumnName` asc";
		$rowNumber+=$_this->getColumnValueCustom($sqlQuery,'row_number');
		
		return $rowNumber;
	}
	
	/**
	* Find Number Of Days In This Month
	* 
	* @author Arash Dalir
	*/
	function createQueryDateTime($dateTime, $ctype = 'gregorian'){
		$_this=&cmfcMySql::getInstance(&$this);
		//echo $ctype;
		list($y, $m, $d) = $dateTime;
		//echo '<br />', $y, $m, $d, '<br />';
		if ($ctype == 'jalali'){
			list($y, $m, $d) = cmfcJalaliDateTime::jalaliToGregorian($y, $m, $d);
		}
		//echo '<br />', $y, $m, $d, '<br />';
		return $y.'-'.$m.'-'.$d;
	}
	
	
	/**
	* this function helps to avoid SQL-Injections
	* the passed string will be cleaned up. this is
	* meant for the following values:
	* - input from users
	* - parameters from URLs
	* - Values from cookies
	*
	* @access public
	* @param string $value
	* @return string
	* @previousNames cleanup_value
	*/

	function cleanupValue($value)
	{
		return (eregi_replace("[\'\"\/\\\;\`\n\r\n]","",$value));
	}

	
}
?>