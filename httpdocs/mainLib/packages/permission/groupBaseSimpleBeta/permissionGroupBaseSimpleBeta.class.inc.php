<?php
define ('CMF_UserPermissionSystem_Ok',true);
define ('CMF_UserPermissionSystem_Error',2);
define ('CMF_UserPermissionSystem_Auto_Activation_Is_Not_Enabled',3);

class cmfcPermissionGroupBaseSimpleBeta {
	var $_configs;
	var $_dynamicSystemEnabled=false;
	var $_tableName = 'user_groups'; 
	
	var $_colnId='id';
	var $_colnName='name';
	var $_colnPermissions='permissions';
	
	var $cvId=null;
	var $cvName=null;
	var $cvPermissions=',,';
	
	var $_messagesValue=array(
		CMF_UserPermissionSystem_Error=>'Unknown error',
	);
	
	function cmfcPermissionGroupBaseSimpleBeta($configs) {
		return $this->setOptions($configs);
	}

	function setTableName($value) {
		$this->_tableName=$value;
	}
	
	function setColnName($value) {
		$this->_colnName=$value;
	}
	
	function colnName($value) {
		$this->_colnName=$value;
	}
	
	function setColnAccessibleSections($value) {
		$this->_colnPermissions=$value;
	}
	
	function setConfigs($value) {
		trigger_error(__FILE__." setConfigs is discontinued please use setOptions instead",E_ERROR);
		$result=array(); 
		if (isset($value['tableName'])) $result[]=$this->setTableName($value['tableName']);
		if (isset($value['colnId'])) $result[]=$this->setOption('colnId',$value['colnId']);
		if (isset($value['colnName'])) $result[]=$this->setOption('colnName',$value['colnName']);
		if (isset($value['colnPermissions'])) $result[]=$this->setOption('colnPermissions',$value['colnPermissions']);
		
		$this->_configs=$value;
		return reset($result);
	}
	
	
	function setOptions($options) {
		foreach ($options as $name=>$value) {
			$this->setOption($name,$value);
		}
	}
	
	function setOption($name,$value) {
		switch ($name) {
			//case 'permissionSystem': $result=$this->setPermissionSystem($value);break;
			default : $this->{'_'.$name}=$value; break;
		}
		return $result;
	}
	
	
	function arrayToProperties($columnsValues) {
		if (!$this->_dynamicSystemEnabled) {
			if (isset($columnsValues[$this->_colnId])) $this->cvId=$columnsValues[$this->_colnId];
			if (isset($columnsValues[$this->_colnName])) $this->cvName=$columnsValues[$this->_colnName];
			if (isset($columnsValues[$this->_colnPermissions])) $this->cvPermissions=$columnsValues[$this->_colnPermissions];
		}
	}

	function propertiesToArray() {
		//$columnsValues=parent::propertiesToArray();

		if (!$this->_dynamicSystemEnabled) {
			$columnsValues[$this->_colnId]=$this->cvId;
			$columnsValues[$this->_colnName]=$this->cvName;
			$columnsValues[$this->_colnPermissions]=$this->cvPermissions;
		}

		return $columnsValues;
	}

	function clear() {
		//parent::clear();
		if (!$this->_dynamicSystemEnabled) {
			$this->cvId=null;
			$this->cvName=null;
			$this->cvPermissions=null;
		}
	}
	
	
	function load($id) {
		$result=cmfcMySql::load($this->_tableName,$this->_colnId,$id);
		if ($result) {
			$this->arrayToProperties($result);
			return true;
		} else {
			return $result;
		}
	}
	
	
	function update($columnsValues,$id) {
		if (isset($columnsValues[$this->_colnPermissions]))
			$columnsValues[$this->_colnPermissions]=$this->formatPermissions($columnsValues[$this->_colnPermissions]);

		if (!PEAR::isError($columnsValues)) {
			$result=cmfcMySql::update($this->_tableName,$this->_colnId,$columnsValues,$id);
			if ($result) {
				return true;
			} else {
				return $this->raiseError('', CMF_UserPermissionSystem_Error,
								PEAR_ERROR_RETURN,NULL);
			}
		}

		return $columnsValues;
	}
	
	function insert($columnsValues) {
		if (isset($columnsValues[$this->_colnPermissions]))
			$columnsValues[$this->_colnPermissions]=$this->formatPermissions($columnsValues[$this->_colnPermissions]);
		if (!PEAR::isError($columnsValues)) {
			$result=cmfcMySql::insert($this->_tableName,$columnsValues);
			if ($result) {
				return true;
			} else {
				return $this->raiseError('', CMF_UserPermissionSystem_Error,
								PEAR_ERROR_RETURN,NULL);
			}
		}
		
		return $columnsValues;
	}
	
	/**
	* @desc convert array of permissions to string format , if function
	* 		find n in array it will get ,n, and it means full access
	* @example array(1,5,6,7) //numbers are site sections id
	* @return string //,1,4,5,8,9,6,34,
	*/
	function formatPermissions($permissionsArray) {
		if (is_array($permissionsArray)) {
			if (in_array('n',$permissionsArray))
				$result=',n,';
			else
				$result=','.implode(',',$permissionsArray).',';
			return $result;
		} else {
			return $permissionsArray;
		}
	}
	
	function getSqlCondition($sectionId) {
		return $this->_tableName.'.'.$this->_colnPermissions.' LIKE ",'.$sectionId.',"';
	}
	
	function isAccessible($sectionId,$permissionsString) {
		if (strpos($permissionsString,",n,")!==false)
			return true;
			
		if (empty($sectionId)) return false;
		
		if (strpos($permissionsString,",$sectionId,")!==false) {
			return true;
		} else {
			return false;
		}
	}
}