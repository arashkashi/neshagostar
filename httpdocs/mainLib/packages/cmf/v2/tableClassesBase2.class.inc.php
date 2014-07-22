<?php
/**
 * Main features are :
 * 	- 
 * 
 * @package cmf
 * @subpackage beta
 * @author Sina Salek
 * @version $Id: tableClassesBase2.class.inc.php 394 2009-09-22 11:44:02Z salek $
 */
 
define('CMF_TableClassesBase2_Ok',true);
define('CMF_TableClassesBase2_Error',2);
define('CMF_TableClassesBase2_Error_Invalid_Sort_Type',3);

if (!class_exists('cmfcClassesCore')) {
	trigger_error('cmfcClassesCore package does not included , please include it first packages/cmf/beta/classesCore.class.inc.php',E_USER_ERROR);
}

/**
* 
*/
class cmfcTableClassesBase2 extends cmfcClassesCore {
	/*
	var $dbServer='localhost';
	var $dbName='';
	var $dbUser='';
	var $dbPassword='';
	var $dbType='';
	*/
	
	var $_defaultError=CMF_Database_Error;
	var $_messagesValue=array(
        CMF_TableClassesBase2_Ok	=> 'no error',
        CMF_TableClassesBase2_Error => 'unknown error',
        CMF_TableClassesBase2_Error_Invalid_Sort_Type => 'sort type "%s" is not valid, possible values are "DESC","ASC"',
	);
	
	var $_columnValueProperyNamePrefix='cv';
	var $_columnNameProperyNamePrefix='coln';
	
	var $_sortByColumnName;
	var $_sortType='DESC';
	
	var $_rows;//array
	var $_row;//array
	
	var $_tableName='';
	var $_tableNamePrefix='cmf_';
	
	/**
	* array('columnName'=>'propertyName')
	* cvColumnName, and _colnColumnName will be created according to
	* this array
	* @var array 
	*/
	var $_columnsProperty=array(
		'id'=>'id',
		'insert_datetime'=>'insertDateTime',
		'update_datetime'=>'updateDateTime',
	);
	/**
	 * It's attached to the columnsProperty and will be updated automatically
	 * @var array
	 */
	var $_propertiesColumns=array(
		'id'=>'id',
		'insert_datetime'=>'insertDateTime',
		'update_datetime'=>'updateDateTime',
	);
	
	
	var $_previousColumnsValues;
	var $_columnsValuesChanges=array();
	
	
	function __construct($options) {
		if (!isset($options['columnsProperty']) and !isset($options['propertiesColumn'])) {
			$options['columnsProperty']=$this->getOption('columnsProperty');
		}
		return $this->setOptions($options);
	}
		
	function setSortType($value) {
		$value=strtoupper($value);
		if ($value!='DESC' and $value!='ASC' ) {
			return $this->raiseError(null,CMF_TableClassesBase2_Error_Invalid_Sort_Type,
					PEAR_ERROR_RETURN,NULL, 
					array('sortType'=>$value));
		} else {
			$this->_sortType=$value;
		}
	}
	
	function setOption($name,$value,$merge=false) {
		if ($name=='columnsProperty') {
			$this->_propertiesColumn=array_flip($value);
		} elseif ($name=='propertiesColumn') {
			$this->_columnsProperty=array_flip($value);
		}
		if ($name=='sortType') {
			$r=$this->setSortType($value);
		} if ($name=='columnsProperty') {
			parent::setOption($name,$value,$merge);
			$r=$this->setColumnsProperty($value);
		} else {
			$r=parent::setOption($name,$value,$merge);
		}
		return $r;
	}
	
	/**
	* create to property var for each column, like cvColumnPropertyName, colnColumnPropertyName
	*/
	function setColumnsProperty($columnsProperty) {
		foreach ($columnsProperty as $columnName=>$propertyName) {
			$myPropertyName=$propertyName;
			if (!empty($this->_columnNameProperyNamePrefix)) {
				$myPropertyName=$this->_columnNameProperyNamePrefix.cmfcString::manipulateChars($propertyName,'toUpper',1,1);
				$myPropertyNameOrg='coln'.cmfcString::manipulateChars($propertyName,'toUpper',1,1);
			}
			
			$this->{$myPropertyName}=$columnName;
			$this->{$myPropertyNameOrg}=&$this->{$myPropertyName};
			
			if (!empty($this->_columnValueProperyNamePrefix)) {
				$myPropertyName=$this->_columnValueProperyNamePrefix.cmfcString::manipulateChars($propertyName,'toUpper',1,1);
				$myPropertyNameOrg='cv'.cmfcString::manipulateChars($propertyName,'toUpper',1,1);
			}
			
			$this->{$myPropertyName}=null;
			$this->{$myPropertyNameOrg}=&$this->{$myPropertyName};
		}

	}
	

	function getColumnsChangesDiff($currentColumnsValues) {
		$changes[]=array();

		foreach ($currentColumnsValues as $columnName=>$columnValue) {
			$oldColumnValue=$this->_previousColumnsValues[$columnName];
			if ($columnValue!=$oldColumnValue) {
				if ((is_numeric($columnValue) or empty($columnValue)) and 
					(is_numeric($oldColumnValue) or empty($oldColumnValue))) {
					$changes[$columnName]=intval($columnValue)-intval($oldColumnValue);
				} else {
					$changes[$columnName]=$columnValue;
				}
				// $columnName.'='.$changes[$columnName].'|'.$oldColumnValue.' -> '.$columnValue.'<br/>';
			}
		}
		$this->_columnsValuesChanges=$changes;
		return $changes;
	}
	
	
	function getChangedColumns($currentColumnsValues) {

		foreach ($currentColumnsValues as $columnName=>$columnValue) {
			$oldColumnValue=$this->_previousColumnsValues[$columnName];
			if ($columnValue!==$oldColumnValue) {
				$changes[$columnName]=$columnValue;
			}
		}
		return $changes;
	}
	
	
	function clearColumnsProperties()
	{
		if (is_array($this->_columnsProperty)) {
			foreach($this->_columnsProperty as $columnName=>$propertyName) {
				$this->{$this->_columnValueProperyNamePrefix.$propertyName}=null;
				$this->{'cv'.$propertyName}=&$this->{$this->_columnValueProperyNamePrefix.$propertyName};
			}
			return true;
		}
		return false;
	}
	
	function getPropertyNameByColumnName($name) {
		return $this->_columnsProperty[$name];
	}
	
	function getColumnNameByPropertyName($name) {
		return cmfcArray::getKeyByValue($this->_columnsProperty,$name);
	}
	
	function columnsValuesToProperties($columnsValues,$exceptNulls=false) {
		if (is_array($this->_columnsProperty) and is_array($columnsValues)) {
			foreach($columnsValues as $columnName=>$columnValue) {
				$propertyName=$this->getPropertyNameByColumnName($columnName);
				
				if (!empty($propertyName)) {
					if (!empty($this->_columnValueProperyNamePrefix)) {
						
						$__n=$propertyName;
						if ($this->_columnValueProperyNamePrefix!='_') {
							$__n=cmfcString::manipulateChars($propertyName,'toUpper',1,1);
						}
						
						$propertyName=$this->_columnValueProperyNamePrefix.$__n;
						$propertyNameOrg='cv'.cmfcString::manipulateChars($propertyName,'toUpper',1,1);
					}
					if ($exceptNulls==false or ($exceptNulls and !is_null($columnValue))) {
						$this->{$propertyName}=$columnValue;
						$this->{$propertyNameOrg}=&$this->{$propertyName};
					}
				}
			}
			
			return true;
		}
		return false;
	}

	function propertiesToColumnsValues($exceptNulls=false) {
		
		$columnsValues=array();
		//echo '('.$this->_tableName.$this->_tableName.')<br />';
		if (is_array($this->_columnsProperty)) {
			foreach($this->_columnsProperty as $columnName=>$propertyName) {
				//echo "$columnName:".$this->{$propertyName}."<br/>";
				if (!empty($this->_columnValueProperyNamePrefix)) {
					if ($this->_columnValueProperyNamePrefix!='_') {
						$propertyName=cmfcString::manipulateChars($propertyName,'toUpper',1,1);
					}
					$propertyName=$this->_columnValueProperyNamePrefix.$propertyName;
				}
				
				$propertyValue=$this->{$propertyName};
				
				if ($exceptNulls==false or ($exceptNulls and !is_null($propertyValue))) {
					$columnsValues[$columnName]=$propertyValue;
				}
			}
			return $columnsValues;
		}
		return false;
	}
	
	
	function load($keyColumnValue=null,$keyColumnName=null)
	{
		if (is_null($keyColumnName)) {$keyColumnName=$this->colnId;}
		if (is_null($keyColumnValue)) {$keyColumnValue=$this->cvId;}
		$this->clearColumnsProperties();
		
		$this->_row = $row = cmfcMySql::load($this->_tableName,$keyColumnName,$keyColumnValue);
		$this->_previousColumnsValues=$row;
		
		if (is_array($row)) {
			$this->columnsValuesToProperties($row);
			return $row;
		}
		return false;
	}
	
	
	function reload() {
		return $this->load();
	}
	
	/**
	* @desc for some sort of performance boost
	*/
	function loadFromArray($columnsValues) {
		$this->clearColumnsProperties();
		
		$this->_row = $row = $columnsValues;
		$this->_previousColumnsValues=$row;
		
		if (is_array($row)) {
			$this->columnsValuesToProperties($row);
			return $row;
		}
		return false;
	}
	
	
	
	function loadWithMultiKeys($keyColumnsValues)
	{
		$this->clearColumnsProperties();
		
		$this->_row = $row = cmfcMySql::loadWithMultiKeys($this->_tableName,$keyColumnsValues);
		$this->_previousColumnsValues=$row;

		if (is_array($row)) {
			$this->columnsValuesToProperties($row);
			return $row;
		}
		return false;
	}
	
	
	function update($columnsValues=null,$keyColumnValue=null,$keyColumnName=null) {

		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToColumnsValues(true);}
		if (is_null($keyColumnName)) {$keyColumnName=$this->colnId;}
		if ($keyColumnName==$this->colnId and is_null($keyColumnValue)) {$keyColumnValue=$this->cvId;}

		$this->getColumnsChangesDiff($columnsValues);

		$this->runCommand('processBeforeUpdate',&$columnsValues);
		
		if (cmfcMySql::update($this->_tableName,$keyColumnName,$columnsValues,$keyColumnValue)) {
			$this->runCommand('processAfterUpdate',$columnsValues); 
			return true;
		} else {
			return false;
		}
	}
	
	
	function updateOnlyChanges($columnsValues=null,$keyColumnValue=null,$keyColumnName=null) {

		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToColumnsValues(true);}
		$columnsValues=$this->getChangedColumns($columnsValues);
		
		return $this->update($columnsValues,$keyColumnValue,$keyColumnName);
	}


	function insert($columnsValues=null,$loadAfterInsert=false) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToColumnsValues();}

		$this->runCommand('processBeforeInsert',&$columnsValues);
		$result=cmfcMySql::insert($this->_tableName,$columnsValues);
		
		if ($result) {
			$this->runCommand('processAfterInsert',$columnsValues);
			return true;
		} else {
			if ($loadAfterInsert) {
				$this->columnsValuesToProperties($columnsValues);
				$this->cvId=cmfcMySQL::insertId();
			}
		}
		
		return $result;
	}

	function delete($keyColumnValue=null,$keyColumnName=null) {
		if ($keyColumnName==$this->colnId and is_null($keyColumnValue)) {$keyColumnValue=$this->cvId;}
		$columnsValues=array($keyColumnName=>$keyColumnValue);
		$this->runCommand('processBeforeDelete',&$columnsValues);

		$r=cmfcMySql::delete($this->_tableName,$keyColumnName,$keyColumnValue);
		if ($r) {
			$this->runCommand('processAfterDelete',$columnsValues);
		}

		return $r;
	}
	
	
	function createTable() {
		$sqlQuery="
			CREATE TABLE `$this->_tableName` (
				`$this->colnId` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY
			) TYPE = MYISAM ;";
		if (cmfcMySql::exec($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}

	function emptyTable() {
		$sqlQuery="DELETE FROM `$this->tableNname`;";
		if (cmfcMySql::exec($sqlQuery)!==false) {
			return true;
		} else {
			return false;
		}
	}
	
	
	function isItExists($columnName,$columnValue) {
		$sqlQuery="SELECT count(*) as 'exists' FROM `$this->_tableName` 
					WHERE `$columnName` LIKE '$columnValue'";
		$val=cmfMySql::getColumnValueCustom($sqlQuery,'exists');
		if ($val>0)
			return true;
		else
			return false;
	}
}