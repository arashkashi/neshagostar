<?php
define ('CMF_UserSystem_Max_Valid_Parallel_Logins_Exceed',20);

class cmfcUserSystemBetaParallel extends cmfcUserSystemV1{
	
	/**
	* if enable package can apply some limitations to parallel logged in users
	*/
	var $_parallelLoginControlEnabled=false;
	/**
	* if null will use each user specific setting via totalValidParallelLogins column value
	* - Null : means don't override users specific setting
	* - 0 : means don't override users specific setting with unlimited
	* - more than 1 means overrides users specific setting
	*/
	var $_totalValidParallelLogins=1;
	
	/**
	* array('columnName'=>'propertyName')
	*/
	var $_columnsPropertyAdditional=array(
		/**
		* @desc 0 means unlimited and null means apply default limitation
		*/
		'total_valid_parallel_logins'=>'totalValidParallelLogins'
	);
	
	var $_messagesValueAdditional=array(
		CMF_UserSystem_Max_Valid_Parallel_Logins_Exceed=>'User maximum valid parallel logins exceed'
	);
	
	function __construct($options) {
		$this->_columnsProperty=cmfcArray::mergeRecursive($this->_columnsProperty,$this->_columnsPropertyAdditional);
		$this->_messagesValue=cmfcArray::mergeRecursive($this->_messagesValue,$this->_messagesValueAdditional);
		return parent::__construct($options);
	}
	
	function setOption($name,$value,$merge=false) {
		if (isset($options['permissionSystem'])) {
			$r=$this->setSortType($options['permissionSystem']);
		} else {
			$r=parent::setOption($name,$value,$merge);
		}
		return $r;
	}
	
	
	function login($username, $password=null ,$encryptedPassword=null) {
		if (empty($encryptedPassword) and !empty($password))
			$encryptedPassword=md5($password);
	    $result=true;
		$userId=$this->getIdByColumn($this->_colnUsername,$username);
		if (!PEAR::isError($userId)) {
			if (!empty($username) and !empty($encryptedPassword)) {
				if ($this->isUsernameAndPasswordMatch($username, $encryptedPassword)) {
					if ($this->isUserAccountActive($userId)) {
						//--(Begin)-->
						if ($this->load($userId)) {
							if (!$this->isTotalValidParallelLoginsExceed()) {
								$this->saveLogin();
								//echo $this->_pagesInfo['afterLogin']; exit;
								$this->redirect($this->_pagesInfo['afterLogin']);
								//print_r($this->_pagesInfo['afterLogin']);
							} else {
								$result = $this->raiseError('', CMF_UserSystem_Max_Valid_Parallel_Logins_Exceed,
										PEAR_ERROR_RETURN,NULL,
										null);
							}
						}
						//--(End)-->
					} else {
						$result = $this->raiseError('', CMF_UserSystem_User_Account_Is_Not_Active,
										PEAR_ERROR_RETURN,NULL,
										array('username'=>$username, 'password'=>$password));
					}
				} else {
					$result = $this->raiseError('', CMF_UserSystem_Username_And_Password_Do_Not_Match,
									PEAR_ERROR_RETURN,NULL,
									array('username'=>$username, 'password'=>$password));
				}
			} else {
				$result = $this->raiseError('', CMF_UserSystem_Username_Or_Password_Is_Empty,
								PEAR_ERROR_RETURN,NULL,
								array('username'=>$username, 'password'=>$password));
			}
		} else {
			$result = $this->raiseError('', CMF_UserSystem_User_Account_Does_No_Exists,
							PEAR_ERROR_RETURN,NULL,
							array('username'=>$username, 'password'=>$password));
		}
		return $result;
	}
	
	
	function arrayToProperties($columnsValues) {
		if (!$this->_dynamicSystemEnabled) {
			if (isset($columnsValues[$this->_colnTotalValidParallelLogins])) $this->cvTotalValidParallelLogins=$columnsValues[$this->_colnTotalValidParallelLogins];
		}
		return parent::arrayToProperties($columnsValues);
	}

	function propertiesToArray() {
		if (!$this->_dynamicSystemEnabled) {
			$columnsValues[$this->_colnTotalValidParallelLogins]=$this->cvTotalValidParallelLogins;
		}
		parent::propertiesToArray();
	}
	
	
	function isTotalValidParallelLoginsExceed($userId=null) {
		if ($this->_parallelLoginControlEnabled) {
			$loggedinTotal=$this->runCommand('getNumberOfCurrentParallelLogins',array('username'=>$this->cvUsername));
			$validTotal=$this->cvTotalValidParallelLogins;
			//var_dump($validTotal);
			if ($validTotal=='') $validTotal=$this->_totalValidParallelLogins;

			if ($loggedinTotal>=$validTotal and $validTotal!='0')
				return true;
		}
		return false;
	}
}
