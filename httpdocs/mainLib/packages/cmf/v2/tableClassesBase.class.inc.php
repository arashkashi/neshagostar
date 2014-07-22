<?php
/**
 * 
 * 
 * @package cmf
 * @subpackage beta
 * @todo should remove in favor of tableClassesBase2 as of generallib3
 * @author Sina Salek
 * @version $Id: tableClassesBase.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
 */
 
define('CMF_TableClassesBase_Ok',true);
define('CMF_TableClassesBase_Error',2);
define('CMF_TableClassesBase_Error_Invalid_Sort_Type',3);

if (!class_exists('MDB2')) {
	trigger_error('MDB2 (PEAR) package does not included , please include it first via gclConfiguration::includePearFile()',E_USER_ERROR);
}

/**
* 
*/
class cmfcTableClassesBase extends cmfcBaseClass {
	/*
	var $dbServer='localhost';
	var $dbName='';
	var $dbUser='';
	var $dbPassword='';
	var $dbType='';
	*/
	
	var $_defaultError=CMF_Database_Error;
	var $_messagesValue=array(
        CMF_TableClassesBase_Ok	=> 'no error',
        CMF_TableClassesBase_Error => 'unknown error',
        CMF_TableClassesBase_Error_Invalid_Sort_Type => 'sort type "%s" is not valid, possible values are "DESC","ASC"',
	);
	
	var $_columnValueProperyNamePrefix='cv';
	var $_columnNameProperyNamePrefix='coln';
	
	var $_sortByColumnName;
	var $_sortType='DESC';
	
	var $_rows;//array
	var $_row;//array
	
	var $_tableName='';
	var $_tableNamePrefix='cmf_';

	var $_colnId='id';
	var $_colnInsertDateTime='insert_datetime';
	var $_colnUpdateDateTime='update_datetime';

	var $cvId;
	var $cvInsertDateTime;
	var $cvUpdateDateTime;
	
	function __construct(&$_oStorage) {
		$this->setStorage($_oStorage);
	}
	
	function setColnId($value) {
		$this->_colnId=$value; 
	}
	
	function setColnInsertDateTime($value) {
		$this->_colnInsertDateTime=$value; 
	}
	
	function setColnUpdateDateTime($value) {
		$this->_colnUpdateDateTime=$value; 
	}
	
	function setTableName($value) {
		$this->_tableName=$value; 
	}
	
	function setTableNamePrefix($value) {
		$this->_tableNamePrefix=$value; 
	}
	
	function setSortByColumnName($value) {
		$this->_sortByColumnName=$value; 
	}
	
	function setSortType($value) {
		$value=strtoupper($value);
		if ($value!='DESC' and $value!='ASC' ) {
			return $this->raiseError(null,CMF_TableClassesBase_Error_Invalid_Sort_Type,
					PEAR_ERROR_RETURN,NULL, 
					array('sortType'=>$value));
		} else {
			$this->_sortType=$value;
		}
	}
	
	function setColumnValueProperyNamePrefix($value) {
		$this->_columnValueProperyNamePrefix=$value; 
	}
	
	function setColumnNameProperyNamePrefix($value) {
		$this->_columnNameProperyNamePrefix=$value; 
	}
	
	function setConfigs($configs) {
		if (isset($configs['columns']['id'])) $this->setColnId($configs['columns']['id']);
		if (isset($configs['columns']['insertDateTime'])) $this->setColnInsertDateTime($configs['columns']['insertDateTime']);
		if (isset($configs['columns']['updateDateTime'])) $this->setColnUpdateDateTime($configs['columns']['updateDateTime']);
		
		if (isset($configs['tableName'])) 		$this->setTableName($configs['tableName']);
		if (isset($configs['tableNamePrefix'])) $this->setTableNamePrefix($configs['tableNamePrefix']);
		if (isset($configs['sortByColumnName'])) $this->setSortByColumnName($configs['sortByColumnName']);
		if (isset($configs['sortType'])) 		$this->setSortType($configs['sortType']);
		if (isset($configs['columnValueProperyNamePrefix'])) $this->setColumnValueProperyNamePrefix($configs['columnValueProperyNamePrefix']);
		if (isset($configs['columnNameProperyNamePrefix'])) $this->setColumnNameProperyNamePrefix($configs['columnNameProperyNamePrefix']);
		return parent::setConfigs($configs);
	}
	

	/**
	* @return string $tableName
	* @param string $defaultColumnName
	* @desc Enter description here...
	*/
	function getColumnNameByLanguage($default_column_name,$table_name=null,$language)
	{
		if (empty($table_name)) { $table_name=$this->table_name; }
		$result=$this->_oStorage->get_given_language_column_name($this->_oStorage->current_database_name ,
																$table_name,$default_column_name,$this->local_language_name);
		return $result;
	}

	/**
	* @return string
	* @param string $default_column_name
	* @desc Enter description here...
	*/
	function getColumnCellValueByLanguage($default_column_name,$language)
	{
		$key=$this->getColumnNameByLanguage($default_column_name);
		if (key_exists($key,$this->attributes)) { $local_name=$this->attributes[$key]; }
		if (empty($local_name)) { $local_name=$this->attributes[$default_column_name]; }
		return $local_name;
	}


	
	function loadRandomly($filter=null){
		$sqlQuery="SELECT * FROM `$this->tableName` ORDER BY RAND()";
		$id=$this->getIdBySqlQuery($sqlQuery);
		return $this->load($id);
	}
	
	function getIdBySqlQuery($sqlQuery) {
		return cmfMysqlGetColumnValueCustom($sqlQuery,$this->_colnId);
	}
	
	function loadNewest($sortColumnName,$filter=null){
		$sqlQuery="SELECT * FROM `$this->tableName` ";
//		if ($only_showables) {$sqlQuery.=" WHERE `$this->_coln_show`=1 ";}
		$sqlQuery.=" ORDER BY `sortColumnName` DESC LIMIT 1";
		$id=$this->getIdBySqlQuery($sqlQuery);
		return $this->load($id);
	}

	function createTable() {
		$sqlQuery="
			CREATE TABLE `$this->tableName` (
				`$this->_colnId` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY
			) TYPE = MYISAM ;";
		if (mysql_query($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}

	function emptyTable() {
		$sqlQuery="DELETE FROM `$this->tableNname`;";
		if (mysql_query($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	
	function isItExists($columnName,$columnValue) {
		$sqlQuery="SELECT count(*) as 'exists' FROM `$this->table_name` 
					WHERE `$column_name` LIKE '$column_value'";
		$val=cmfMysqlGetColumnValueCustom($sqlQuery,'exists');
		if ($val>0)
			return true;
		else
		 return false;
	}

	function columnsValuesToProperties($columnsValues,$exceptNulls=false) {
		if (is_array($columnsValues)) {
			if ($this->isDynamicSystemEnabled()) {
				trigger_error("Dynamic mode is still beta version and has some bugs so don't use it or fix it",E_USER_ERROR);
				//it converts undert naming to camel case naming
				/*
				foreach($columnsValues as $columnName=>$columnValue) {
					if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*+$/',$columnName))
						@$this->{$this->columnValueProperyNamePrefix.$columnName}=$columnValue;
				}
				*/
			} else {
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnId])))
					@$this->cvId=$columnsValues[$this->_colnId];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnInsertDateTime])))
					@$this->cvInsertDateTime=$columnsValues[$this->_colnInsertDateTime];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnUpdateDateTime])))
					@$this->cvUpdateDateTime=$columnsValues[$this->_colnUpdateDateTime];
			}
			return true;
		}
		return false;
	}

	function propertiesToColumnsValues($exceptNulls=false) {
		$columnsValues=array();

		if ($this->isDynamicSystemEnabled()) {
			trigger_error("Dynamic mode is still beta version and has some bugs so don't use it or fix it",E_USER_ERROR);
			$vars=get_object_vars($this);
			foreach ($vars as $varName=>$varValue) {
				if (preg_match('/^'.$this->columnValueProperyNamePrefix.'[a-zA-Z][a-zA-Z0-9_]*+$/',$varName))
					$columns_values[$this->{$this->columnNameProperyNamePrefix.colnId}]=$varValue;;
			}
		} else {
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvId)))
				$columnsValues[$this->_colnId]=$this->cvId;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvInsertDateTime)))
				$columnsValues[$this->_colnInsertDateTime]=$this->cvInsertDateTime;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvUpdateDateTime)))
				$columnsValues[$this->_colnUpdateDateTime]=$this->cvUpdateDateTime;
		}

		return $columnsValues;
	}

	function clearColumnsProperties() {
		if ($this->isDynamicSystemEnabled()) {
			return $this->clearProperties($this->columnNameProperyNamePrefix);
		} else {
			$this->cvId=null;
			$this->cvInsertDateTime=null;
			$this->cvUpdateDateTime=null;
			return true;
		}
	}

	function update($columnsValues=null,$keyColumnValue=null,$keyColumnName=null) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToColumnsValues();}
		if (is_null($keyColumnName)) {$keyColumnName=$this->_colnId;}
		if ($keyColumnName==$this->_colnId and is_null($keyColumnValue)) {$keyColumnValue=$this->cvId;}
		if (is_null($keyColumnValue)) {$keyColumnName=$columnsValues[$keyColumnName];}
		unset($columnsValues[$keyColumnName]);
		
		return $this->_oStorage->updateRow($columnsValues,$this->tableName,$keyColumnName,$keyColumnValue);
	}


	function load($keyColumnValue=null,$keyColumnName=null) {
		if (is_null($keyColumnName)) {$keyColumnName=$this->_colnId;}
		$this->clearColumnsProperties();
		
		$row=$this->_oStorage->getRow($this->tableName,$keyColumnValue,$keyColumnValue);

		if (PEAR::isError($row)) {
			
		} else {
			$this->columnsValuesToProperties($row);
			return $row;
		}
		return $row;
	}


	function insert($columnsValues=null,$loadAfterInsert=false) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToArray();}

		$result=$this->_oStorage->insertRow($columnsValues,$this->tableName);
		
		if (PEAR::isError($result)) {
			
		} else {
			if ($loadAfterInsert) {
				$this->cvId=$this->_oStorage->lastInsertId($this->tableName,$this->_colnId);
				$this->columnsValuesToProperties($columnsValues);
			}
		}
		
		return $result;
	}

	function delete($keyColumnValue=null,$keyColumnName=null) {
		if (is_null($keyColumnName)) {$keyColumnName=$this->_colnId;}
		if ($keyColumnName==$this->_colnId and is_null($keyColumnValue)) {$keyColumnValue=$this->cvId;}
		
		return $this->_oStorage->deleteRowByUniqueKey($this->tableName,$keyColumnName,$keyColumnValue);
	}
	
/*
	function getRows($filterColumn=null,$filterValue=null,$limit=null,$sortColumnName=null,$sortType='DESC') {
		$sql_query="SELECT * FROM `$this->tableName` ";
		if (!is_null($filterColumn)) {$sqlQuery.="WHERE `$filterColumn`='$filterValue' ";}
		if (!is_null($sortColumnName)) {$sqlQuery.="ORDER BY `$sortColumnName` $sortType ";}
		if (!is_null($limit)) {$sqlQuery.="LIMIT $limit ";}
		return cmfMysqlGetQueryResultAsArray($sqlQuery,null,false);
	}

	function getRow($id) {
		$sqlQuery="SELECT * FROM `$this->tableName` WHERE `$this->_colnId`='$id'";
		return cmfMysqlGetQueryResultAsArray($sqlQuery);
	}
	
	function getByColumnValue($columnName,$filterColumnName,$filterColumnValue) {
		return cmfMysqlGetColumnValue($filterColumnValue,$this->tableName,$filterColumnName,$columnName);
	}
	
	
	function loadNext($sortByColumnValue,$sqlWhere=null) {
		$row=cmfMysqlGetNextRow($this->tableName,$this->sortType,$this->sortByColumnName,$sortByColumnValue,$sqlWhere);
		return $this->load($row[$this->_colnId]);
	}

	function loadPrev($sortByColumnValue,$sqlWhere=null) {
		$row=cmfMysqlGetPrevRow($this->tableName,$this->sortType,$this->sortByColumnName,$sortByColumnValue,$sqlWhere);
		return $this->load($row[$this->_colnId]);
	}	
	
	function loadLatest($sqlWhere=null) {
		$row=cmfMysqlGetLatestRow($this->tableName,$this->sortType,$this->sortByColumnName,$sqlWhere);
		return $this->load($row[$this->_colnId]);
	}
	
	function getNextRows($sortByColumnValue,$limit=5,$sqlWhere=null) {
		$rows=cmfMysqlGetNextRow($this->tableName,$this->sortType,$this->sortByColumnName,$sortByColumnValue,$sqlWhere,$limit=5);
		return $rows;
	}

	function getPrevRows($sortByColumnValue,$limit=5,$sqlWhere=null) {
		$rows=cmfMysqlGetPrevRow($this->tableName,$this->sortType,$this->sortByColumnName,$sortByColumnValue,$sqlWhere,$limit=5);
		return $rows;
	}	
	
	function getLatestRows($limit=5,$sqlWhere=null) {
		$rows=cmfMysqlGetLatestRow($this->tableName,$this->sortType,$this->sortByColumnName,$sqlWhere,$limit=5);
		return $rows;
	}
*/
}
?>
