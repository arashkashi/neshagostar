<?php
/**
 * @version $Id: userSystemV1.class.inc.php 423 2009-10-03 12:22:34Z salek $
 * @todo adding a new hook for doing some stuff during logging is very useful
*/
/*
if (!class_exists('MDB2'))
	trigger_error('"cmfcDatabase" class needs "MDB2" class, please include it first!',E_USER_ERROR); 
*/
if (!class_exists('cmfcTableClassesBase2')) {
	trigger_error('cmfcTableClassesBase2 package does not included , please include it first packages/cmf/beta/tableClassesBase2.class.inc.php',E_USER_ERROR);
}

define ('CMF_UserSystem_Ok',true);
define ('CMF_UserSystem_Error',2);
define ('CMF_UserSystem_User_Info_Is_Not_Array',3);
define ('CMF_UserSystem_Username_Is_Empty',4);
define ('CMF_UserSystem_Email_Is_Empty',5);
define ('CMF_UserSystem_Email_Is_No_Valid',6);
define ('CMF_UserSystem_Username_Is_Not_Valid',7);
define ('CMF_UserSystem_Password_Is_Not_Valid',8);
define ('CMF_UserSystem_User_Account_Does_No_Exists',9);
define ('CMF_UserSystem_Username_Or_Password_Is_Empty',10);
define ('CMF_UserSystem_Username_And_Password_Do_Not_Match',11);
define ('CMF_UserSystem_Auto_Activation_Is_Not_Enabled',12);
define ('CMF_UserSystem_Confirmation_Code_Is_Not_Valid',13);
define ('CMF_UserSystem_Activation_Failed',14);
define ('CMF_UserSystem_Incompatible_Permission_System',15);
define ('CMF_UserSystem_Password_Is_Empty',16);
define ('CMF_UserSystem_Username_Is_Duplicate',17);
define ('CMF_UserSystem_Email_Is_Duplicate',18);
define ('CMF_UserSystem_Username_Or_Email_Does_Not_Exists',19);

define ('CMF_UserSystem_Email_Temp_Registration',1);
define ('CMF_UserSystem_Email_Temp_Activation',2);
define ('CMF_UserSystem_Email_Temp_Welcome',3);
define ('CMF_UserSystem_Email_Temp_Request_New_Password',4);
define ('CMF_UserSystem_Email_Temp_New_Password_Applied',5);

$__________ssid=session_id();
if (empty($__________ssid)) {session_start();}

class cmfcUserSystemV1 extends cmfcTableClassesBase2 {
	var $_dynamicSystemEnabled;
	var $_tableName = 'users'; 
	
	var $_observers=array();
	var $_commandHandlers=array();

	var $_columnValueProperyNamePrefix='cv';
	var $_columnNameProperyNamePrefix='_coln';
	
	/**
	* array('columnName'=>'propertyName')
	*/
	var $_columnsProperty=array(
		'id'=>'id',
		'username'=>'username',
		'password'=>'password',
		'encrypted_password'=>'encryptedPassword',
		'first_name'=>'firstName',
		'last_name'=>'lastName',
		'full_name'=>'fullName',
		'email'=>'email',
		'temporary_email'=>'temporaryEmail',
		'access_level'=>'accessLevel',
		'activated'=>'activated',
		'update_datetime'=>'updateDateTime',
		'insert_datetime'=>'insertDateTime',
		'login_datetime'=>'loginDateTime',
		'last_login_datetime'=>'lastLoginDateTime',
		'register_datetime'=>'registerDateTime',
		'ip'=>'ip',
		'biography'=>'biography',
		'permissions'=>'permissions',
		'last_lock_datetime'=>'lastLockDateTime',
		'login_attempts'=>'loginAttempts',
		'lock'=>'lock',
		'invitation_code'=>'invitationCode',
		'total_invitations'=>'totalInvitations',
		'referrer_user_id'=>'referrerUserId',
		'activation_code'=>'activationCode',
		'user_group_id'=>'userGroupId'
	);
	
	var $_userInfo=array();
	
	#--(Begin)-->require objects
	var $_oPermissionSystem;//
	#--(End)-->require objects
	
	//con = cookie name ., sen=sesstion name
	#--(Begin)-->session and cookie name for preventing confliction
	var $_sessionBaseName='userSystem';

	var $_cookieBaseName = 'userSystemUserInfo';
	var $_cookiePath = null; 
	var $_cookieDomain = null; 
	
	var $_rememberUserEnabled=false;
	var $_rememberUserPeriod=604800; //60 * 60 * 24 * 7 in seconds
	var $_isLoggedInWithCookie=false;
	#--(End)-->session and cookie name for preventing confliction
	
	var $_isCookie;
	var $_autoSaveLogin =true;
	var $_countVisit=true;
	var $_language = "en"; // change this property to use messages in another language 
	
	#--(Begin)-->pgn = page address
	var $_pagesInfo=array(
		'default'=>'index.php',
		'login'=>'login.php',
		'activation'=>'activate_password.php',
		'denyAccess'=>'deny_access.php',
		'afterLogout'=>'',
		'afterLogin'=>'',
		'afterActivation'=>'',
		'requestNewPassword'=>''
	);

	var $_autoActivationEnabled = true;
		
	var $_passwordRules='All characters , minimum length is 4';
	var $_usernameRules='All characters , minimum length is 6';
	var $_usernameRegexValidator='/.{4,}/'; 
	var $_passwordRegexValidator='/.{6,}/';
    
    var $_emailIsRequire=true;
	
	var $_defaultAccessLevel=1;
	var $_columnsValues=array();
	
	var $_numberOfLegalLoginAttempts=3;
	var $_accountLockingPeriod=1440;//minutes 24*60
	var $_lockingSystemEnabled=false;
	
	var $_autoRedirectEnabled=true;
	var $_encryptionMethod='sha1'; //md5
	
	/**
	* @desc if enabled , when user register or change his password 
	*		the original password will be stored otherwise only encrypted 
	* 		password will be stored
	*/
	var $_keepPasswordEnabled=true;
	/**
	* @desc if enabled , and _keepPasswordEnabled is enabled user password
	*		will be clearColumnsProperties after activation
	*/
	var $_clearColumnsPropertiesPasswordAfterActivationEnabled=true;
	var $_bypassLoginCheckEnabled=false;
	
	var $_messagesValue=array(
		CMF_UserSystem_Error=>'Unknown error',
		CMF_UserSystem_User_Info_Is_Not_Array=>'User Info is not array',
		CMF_UserSystem_Username_Is_Empty=>'Username is empty',
		CMF_UserSystem_Username_Is_Not_Valid=>'Username "%username%" is not valid, use this rules : %usernameRules%',
		CMF_UserSystem_Email_Is_Empty=>'Email is empty',
		CMF_UserSystem_Email_Is_No_Valid=>'Email "%email%" is not valid',
		CMF_UserSystem_Password_Is_Not_Valid=>'Password "%password%" is not valid, use this rules : %passwordRules%',
		CMF_UserSystem_User_Account_Does_No_Exists=>'User with "%username%" user name does not exists',
		CMF_UserSystem_Username_Or_Password_Is_Empty=>'Username or password is empty.',
		CMF_UserSystem_Username_And_Password_Do_Not_Match=>'Username and password do not match',
		CMF_UserSystem_User_Account_Is_Not_Active=>'User account is not active : %username%',
		CMF_UserSystem_Auto_Activation_Is_Not_Enabled=>'Auto activation is not enabled',
		CMF_UserSystem_Confirmation_Code_Is_Not_Valid=>'Confirmaion code is not valid. confirmation code(%activationCode%) username (%username%)',
		CMF_UserSystem_Activation_Failed=>'Activating user account failed, the reason is unkown',
		CMF_UserSystem_Incompatible_Permission_System=>'Permission system "%permissionSystemName%" is not compatible with %userSystemClass%  ,select on of this : %supportedPermissionSystems%',
		CMF_UserSystem_No_Permission_System_Available=>'There is not permission system specified',
		CMF_UserSystem_Password_Is_Empty=>'Password is empty',
		CMF_UserSystem_Username_Is_Duplicate=>'Username is duplicate',
		CMF_UserSystem_Email_Is_Duplicate=>'Email is duplicate',
		CMF_UserSystem_Username_Or_Email_Does_Not_Exists=>'Username of Email does not exists',
	);
	

	function setOption($name,$value,$merge=false) {
		if ($name=='permissionSystem') {
			$r=$this->setPermissionSystem($value);
		} else {
			$r=parent::setOption($name,$value,$merge);
		}
		return $r;
	}	
	
	
	function encrypt($text) {
		if ($this->_encryptionMethod=='sha1') {
			return sha1($text);
		} else {
			return md5($text);
		}
	}
	
	
	function setPermissionSystem(&$value) {
		$compatiblePermissionSystems=array(strtolower('cmfcPermissionGroupBaseSimpleBeta'),strtolower('cmfcPermissionGroupBaseSimple'));
		@$className=cmfcPhp4::get_class($value);
		if (!in_array($className,$compatiblePermissionSystems)) {
			return $this->raiseError('', CMF_UserSystem_Incompatible_Permission_System,
							PEAR_ERROR_RETURN,NULL,
							array('permissionSystemName'=>$value,
								'userSystemClass'=>cmfcPhp4::get_class($value),
								'usernameRules'=>implode(' , ',$compatiblePermissionSystems))
							);
		}
		$this->_oPermissionSystem=$value;
	}
	
	
	function getUserInfo() {
		user_error('userSystem v1 getUserInfo is discontinued please use getOption("userInfo") instead',E_USER_WARNING);
		return $this->_userInfo;
	}
	
	function setConfigs($value) {
		user_error('userSystem v1 setConfigs() is discontinued please use setOptions() instead',E_USER_ERROR);
	}
	
	
	function setMessagesValue($value) {
		user_error('userSystem v1 setMessagesValue is discontinued please use setOption("messagesValue",$value) instead',E_USER_ERROR);
		return $this->setOption('messagesValue',$value);
	}
		

	function clear() {
		return $this->clearColumnsProperties();
	}
	/**
	* $delay int seconds
	*/
	function redirect($url,$delay=0) {
		if ($this->_autoRedirectEnabled and !empty($url)) {
			if ($delay<1) {
				@header("Location: ".$url);
			}
			echo '<meta http-equiv="refresh" content="'.$delay.';url='.$url.'">';
			exit;
		}
	}
	
	function isEmailValid($mailAddress) {
		if (preg_match("/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i", $mailAddress)) {
			return true;
		} else {
			return false;
		}
	}
	
	function isPasswordValid($password) {
		$result=false;
		if (!empty($this->_passwordRegexValidator)) {
			if (preg_match($this->_passwordRegexValidator, $password)) {
				$result=true;
			} else {
				$result=false;
			}
		}
		return $result;
	}
	
	
	function isUsernameValid($username) {
		$result=false;
		if (!empty($this->_usernameRegexValidator)) {
			if (preg_match($this->_usernameRegexValidator, $username)) {
				$result=true;
			} else {
				$result=false;
			}
		}
		return $result;
	}
	
	function insString($value, $type = "") {
		$value = (!get_magic_quotes_gpc()) ? addslashes($value) : $value;
		switch ($type) {
			case "int":
			$value = ($value != "") ? intval($value) : NULL;
			break;
			default:
			$value = ($value != "") ? "'" . $value . "'" : "''";
		}
		return $value;
	}
	
	function load($id) {
		$result=parent::load($id,$this->_colnId);
		if ($result) {
			$this->columnsValuesToProperties($result);
			if (is_object($this->_oPermissionSystem)) {
				if (!PEAR::isError($this->_oPermissionSystem->load($this->cvUserGroupId)))
					$this->cvPermissions=$this->_oPermissionSystem->cvPermissions;
			}
			$this->_userInfo=$result;
			return true;
		} else {
			return $result;
		}
	}
	
	function reload() {
		return $this->load($this->cvId);
	}
	
	function loadByColumn($columnName,$columnValue) {
		$result=cmfcMySql::load($this->_tableName,$columnName,$columnValue);
		if ($result) {
			return $this->load($result[$this->_colnId]);
		} else {
			return $result;
		}
	}
	
	function getIdByColumn($columnName,$columnValue) {
		$result=cmfcMySql::load($this->_tableName,$columnName,$columnValue);

		if ($result) {
			return $result[$this->_colnId];
		} else {
			return $result;
		}
	}
	
	function update($columnsValues=null,$id=null) {
		if (is_null($columnsValues)) $columnsValues=$this->propertiesToColumnsValues();
		if (is_null($id)) {
			$id=$this->cvId;
		} else {
			$columnsValues[$this->_colnId]=$id;
		}
		$columnsValues=$this->analyzeUserInfo($columnsValues,'update');

		if (!PEAR::isError($columnsValues)) {
			#--(Begin)-->fill require columns if they are not filled
			unset($columnsValues[$this->_colnId]);
			if (isset($columnsValues[$this->_colnActivated])) {
				$columnsValues[$this->_colnActivated]=intval($columnsValues[$this->_colnActivated]);
			}
			
			if (!isset($columnsValues[$this->_colnIp]) and !empty($this->_colnIp))
				$columnsValues[$this->_colnIp]=$_SERVER['REMOTE_ADDR'];
			
			if (isset($columnsValues[$this->_colnPassword]))
				$columnsValues[$this->_colnEncryptedPassword]=$this->encrypt($columnsValues[$this->_colnPassword]);
			$columnsValues[$this->_colnUpdateDateTime]=date('Y-m-d H:i:s');
			if (is_object($this->_oPermissionSystem)) {
				if (isset($columnsValues[$this->_colnUserGroupId])) {
					if (!PEAR::isError($this->_oPermissionSystem->load($columnsValues[$this->_colnUserGroupId])))
						$columnsValues[$this->_colnPermissions]=$this->_oPermissionSystem->cvPermissions;
				}
			}

			if (!$this->_keepPasswordEnabled) {
				$columnsValues[$this->_colnPassword]='';
			}
			#--(End)-->fill require columns if they are not filled
			$result=parent::update($columnsValues,$id,$this->_colnId);

			if ($result) {
				if ($id==$this->cvId) {
					$this->load($id);
				}
				return true;
			} else {
				return $this->raiseError('', CMF_UserSystem_Error,
								PEAR_ERROR_RETURN,NULL);
			}
		}
		return $columnsValues;
	}
	
	function insert($columnsValues) {
		$columnsValues=$this->analyzeUserInfo($columnsValues,'insert');
		if (!PEAR::isError($columnsValues)) {
			#--(Begin)-->fill require columns if they are not filled
			unset($columnsValues[$this->_colnId]);
			$columnsValues[$this->_colnActivationCode]=$this->encrypt($columnsValues[$this->_colnUsername].$columnsValues[$this->_colnPassword]);
			if (is_null($columnsValues[$this->_colnActivated])) {
				$columnsValues[$this->_colnActivated]=intval($columnsValues[$this->_colnActivated]);
			}
			if (!isset($columnsValues[$this->_colnIp]) and !empty($this->_colnIp))
				$columnsValues[$this->_colnIp]=$_SERVER['REMOTE_ADDR'];
			
			if (isset($columnsValues[$this->_colnPassword]))
				$columnsValues[$this->_colnEncryptedPassword]=$this->encrypt($columnsValues[$this->_colnPassword]);
			$columnsValues[$this->_colnInsertDateTime]=date('Y-m-d H:i:s');
			$columnsValues[$this->_colnRegisterDateTime]=date('Y-m-d H:i:s');
			if (is_object($this->_oPermissionSystem)) {
				if (isset($columnsValues[$this->_colnUserGroupId])) {
					if (!PEAR::isError($this->_oPermissionSystem->load($columnsValues[$this->_colnUserGroupId])))
						$columnsValues[$this->_colnPermissions]=$this->_oPermissionSystem->cvPermissions;
				}
			}
			if (!$this->_keepPasswordEnabled) {
				$columnsValues[$this->_colnPassword]='';
			}
			#--(End)-->fill require columns if they are not filled
			
			$result=parent::insert($columnsValues);
			$columnsValues[$this->_colnId]=cmfcMySql::insertId();
			if ($result!==false) {
				$this->runCommand('sendEmailAfterRegistration',$columnsValues);
				return true;
			} else {
				return $this->raiseError('', CMF_UserSystem_Error,
								PEAR_ERROR_RETURN,NULL);
			}
		}
		return $columnsValues;
	}
	
	function delete($keyColumnValue=null,$keyColumnName=null) {
		if (is_null($keyColumnName)) $keyColumnName=$this->_colnId;
		return parent::delete($keyColumnValue,$keyColumnName);
	}
	
	function analyzeUserInfo($info,$mode) {
		$result=array();
		if (!is_array($info)) 
			$result[]=$this->raiseError('', CMF_UserSystem_User_Info_Is_Not_Array,
							PEAR_ERROR_RETURN,NULL,
							array('userInfo'=>$info));
							
		if (isset($info[$this->_colnUsername]) or $mode=='insert') {
			if (empty($info[$this->_colnUsername])) 
				$result[]=$this->raiseError('', CMF_UserSystem_Username_Is_Empty,
								PEAR_ERROR_RETURN,NULL);


			if (!$this->isUsernameValid($info[$this->_colnUsername]))
				$result[]=$this->raiseError('', CMF_UserSystem_Username_Is_Not_Valid,
								PEAR_ERROR_RETURN,NULL,
								array('username'=>$info[$this->_colnUsername]
								,'usernameRules'=>$this->_usernameRules));
			
			
			if ($this->isUsernameDuplicate($info[$this->_colnUsername], $info[$this->_colnId]))
				$result[]=$this->raiseError('', CMF_UserSystem_Username_Is_Duplicate,
								PEAR_ERROR_RETURN,NULL,
								array('username'=>$info[$this->_colnUsername]
								,'usernameRules'=>$this->_usernameRules));
		}
	
		if (isset($info[$this->_colnPassword]) or $mode=='insert') {					
			if (!$this->isPasswordValid($info[$this->_colnPassword])) 
				$result[]=$this->raiseError('', CMF_UserSystem_Password_Is_Not_Valid,
								PEAR_ERROR_RETURN,NULL,
								array('password'=>$info[$this->_colnPassword]
								,'passwordRules'=>$this->_passwordRules));
								
			if (empty($info[$this->_colnPassword])) 
				$result[]=$this->raiseError('', CMF_UserSystem_Password_Is_Empty,
								PEAR_ERROR_RETURN,NULL,
								array('password'=>$info[$this->_colnPassword]
								,'passwordRules'=>$this->_passwordRules));
		}
								
		if (isset($info[$this->_colnEmail]) or $mode=='insert')	{
			if (empty($info[$this->_colnEmail]) and $this->_emailIsRequire==true) {
				$result[]=$this->raiseError('', CMF_UserSystem_Email_Is_Empty,
								PEAR_ERROR_RETURN,NULL,
								array('email'=>$info[$this->_colnEmail]));
                                
            } elseif ($this->_emailIsRequire==true) {
    
			    if (!$this->isEmailValid($info[$this->_colnEmail])) 
				    $result[]=$this->raiseError('', CMF_UserSystem_Email_Is_No_Valid,
								    PEAR_ERROR_RETURN,NULL,
								    array('email'=>$info[$this->_colnEmail]));
								    
			    if ($this->isEmailDuplicate($info[$this->_colnEmail], $info[$this->_colnId])) 
				    $result[]=$this->raiseError('', CMF_UserSystem_Email_Is_Duplicate,
								    PEAR_ERROR_RETURN,NULL,
								    array('email'=>$info[$this->_colnEmail]));
            }
            
		}
		
		$this->_errorStack=$result;
		if (PEAR::isError(reset($result)))
			return $result=reset($result);
		else
			return $info;
	}
	
	
	function activeUser($username=NULL,$activationCode=NULL){ // by babak
		
		if($username && $activationCode) {
			$tempUser = cmfcMySql::loadWithMultiKeys($this->_tableName,array($this->_colnUsername=>$username,$this->_colnActivationCode=>$activationCode));
			$userId = $tempUser[$this->_colnId];
		} else {
			$userId = $this->cvId;
		}
		if ($userId) return cmfcMySql::update($this->_tableName,$this->_colnId,array($this->_colnActivated=>"1"),$userId);
		return false;
	}
	

	function isUsernameAndPasswordMatch($username,$encryptedPassword) {
		if ($this->_bypassLoginCheckEnabled!=true) {
			$sqlQuery="SELECT * FROM `$this->_tableName` WHERE 
				`$this->_colnUsername`=%s AND `$this->_colnEncryptedPassword`=%s LIMIT 1";
			$sqlQuery=sprintf($sqlQuery,
				$this->insString($username,'string'),
				$this->insString($encryptedPassword,'string'));
	
			return cmfcMySQL::checkRowExistenceCustom($sqlQuery);
		} else {
			return true;
		}
	}
	
	function isUserAccountActive($userId) {
		if ($this->_bypassLoginCheckEnabled!=true) {
			$sqlQuery="SELECT * FROM `$this->_tableName` WHERE `$this->_colnActivated`='1' AND ";
			$sqlQuery.="`$this->_colnId`='$userId'";
			return cmfcMySQL::checkRowExistenceCustom($sqlQuery);
		} else {
			return true;
		}
	}
	
	function isUsernameDuplicate($username,$id=null) {
		$sqlQuery="SELECT * FROM %s WHERE %s=%s AND (%s<>%s) ";
		$sqlQuery=sprintf($sqlQuery,
			$this->_tableName,
			$this->_colnUsername,
			$this->insString($username,'string'),
			$this->_colnId,
			$this->insString($id,'string')
		);
		if (cmfcMySql::checkRowExistenceCustom($sqlQuery))
			return true;
		else
			return false;
	}
	
	function isEmailDuplicate($email,$id=null) {
		$sqlQuery="SELECT * FROM %s WHERE %s=%s AND (%s<>%s) ";
		$sqlQuery=sprintf($sqlQuery,
			$this->_tableName,
			$this->_colnEmail,
			$this->insString($email,'string'),
			$this->_colnId,
			$this->insString($id,'string')
		);
		if (cmfcMySql::checkRowExistenceCustom($sqlQuery))
			return true;
		else
			return false;
	}
	
	function saveLogin() {
		#--(Begin)-->save user information to session
		$_SESSION[$this->_sessionBaseName]=array(
            'id'=>$this->cvId,
			'username'=>$this->cvUsername,
			'encryptedPassword'=>$this->cvEncryptedPassword,
			'loggedIn'=>true
		);
		#--(End)-->save user information to session
		
		#--(Begin)-->Update last login for user
		cmfcMySql::update($this->_tableName, $this->_colnId, array($this->_colnLoginDateTime => date("Y-m-d H:i:s"), $this->_colnLastLoginDateTime => $this->cvLoginDateTime),$this->cvId);
		#--(End)-->Update last login for user
		
		#--(Begin)-->save user information to cookie
		if ($this->_rememberUserEnabled) {
			if (empty($this->_cookieDomain)) {
				$this->_cookieDomain=$_SERVER['HTTP_HOST'];
			}			
			$expire = time()+$this->_rememberUserPeriod;
            setcookie($this->_cookieBaseName.'[id]', $this->cvId, $expire, $this->_cookiePath, $this->_cookieDomain);
			setcookie($this->_cookieBaseName.'[username]', $this->cvUsername, $expire, $this->_cookiePath, $this->_cookieDomain);
			setcookie($this->_cookieBaseName.'[encryptedPassword]', $this->cvEncryptedPassword, $expire, $this->_cookiePath, $this->_cookieDomain);
		} else {
			if (isset($_COOKIE[$this->_cookieBaseName])) {
				/*
				$expire = time()-3600;
				setcookie($this->_cookieBaseName.'[username]', '', $expire, $this->_cookiePath);
				setcookie($this->_cookieBaseName.'[password]', '', $expire, $this->_cookiePath);
				*/
			}
		}		
		#--(End)-->save user information to cookie
	}
	
	function restoreLogin() {
		if (isset($_COOKIE[$this->_cookieBaseName]) and !isset($_SESSION[$this->_sessionBaseName])) {
			#--(Begin)-->restore username and password from cookie
            $this->cvId = $_COOKIE[$this->_cookieBaseName]['id'];
			$this->cvUsername = $_COOKIE[$this->_cookieBaseName]['username'];
			$this->cvEncryptedPassword = $_COOKIE[$this->_cookieBaseName]['encryptedPassword'];
			#--(End)-->restore username and password from cookie
			
			if ($this->isUsernameAndPasswordMatch($this->cvUsername,$this->cvEncryptedPassword)) {
				#--(Begin)-->save user information to session
				$_SESSION[$this->_sessionBaseName]=array(
                    'id'=>$this->cvId,
					'username'=>$this->cvUsername,
					'encryptedPassword'=>$this->cvEncryptedPassword
				);
				#--(End)-->save user information to session
				
				$this->_isLoggedInWithCookie=true;
			}
		}
	}
	
	function login($username, $password=null ,$encryptedPassword=null) {
		if (empty($encryptedPassword) and !empty($password))
			$encryptedPassword=$this->encrypt($password);
		$result=true;

		$userId=$this->getIdByColumn($this->_colnUsername,$username);

		if (!PEAR::isError($userId)) {
			if (!empty($username) and !empty($encryptedPassword)) {			
				if ($this->isUsernameAndPasswordMatch($username, $encryptedPassword)) {

					if ($this->isUserAccountActive($userId)) {
						#--(Begin)-->
						if ($this->load($userId)) {

							$this->saveLogin();
	
							//echo $this->_pagesInfo['afterLogin']; exit;
							$this->redirect($this->_pagesInfo['afterLogin']);
							//print_r($this->_pagesInfo['afterLogin']);
						}
						#--(End)-->
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
	
	function logout() {
		unset($_SESSION[$this->_sessionBaseName]);
		
		if (isset($_COOKIE[$this->_cookieBaseName])) {
			$expire = time()-3600;
			if (empty($this->_cookieDomain)) {
				$this->_cookieDomain=$_SERVER['HTTP_HOST'];
			}
            setcookie($this->_cookieBaseName.'[id]', '', $expire, $this->_cookiePath , $this->_cookieDomain);
			setcookie($this->_cookieBaseName.'[username]', '', $expire, $this->_cookiePath , $this->_cookieDomain);
			setcookie($this->_cookieBaseName.'[encryptedPassword]', '', $expire, $this->_cookiePath , $this->_cookieDomain);
		}
		
		$this->redirect($this->_pagesInfo['afterLogout']);
	}
	
	function readLogin() {
		if (!$this->isLoggedIn(false)) {
			$this->restoreLogin();

			$username=$_SESSION[$this->_sessionBaseName]['username'];
			$encryptedPassword=$_SESSION[$this->_sessionBaseName]['encryptedPassword'];

			#--(Begin)-->
			$autoRedirectEnabled=$this->_autoRedirectEnabled;$this->_autoRedirectEnabled=false;
			unset($_SESSION[$this->_sessionBaseName]['loggedIn']);

			$result=$this->login($username,null,$encryptedPassword);

			$this->_autoRedirectEnabled=$autoRedirectEnabled;
			#--(End)-->
			return $result;
		} else {
			if (!$this->load($_SESSION[$this->_sessionBaseName]['id'])) {
				$this->logout();
			}
		}
	}
	
	function isLoggedIn($redirect=false) {
		if ($_SESSION[$this->_sessionBaseName]['loggedIn']==true) return true;
		if ($redirect) $this->redirect($this->_pagesInfo['login']);
		return false;
	}
	
	function activateUserAccount($username,$activationCode) {
		$sqlQuery="SELECT * FROM `%s` WHERE `%s`=%s AND `%s`=%s AND (`%s`<>1 OR `%s` IS NULL) LIMIT 1 ";
		$sqlQuery=sprintf($sqlQuery,
			$this->_tableName,
			$this->_colnUsername,
			$this->insString($username,'string'), 
			$this->_colnActivationCode,
			$this->insString($activationCode,'string'),
			$this->_colnActivated,
			$this->_colnActivated
		);
		if ($this->_autoActivationEnabled) {
			if (cmfcMySql::checkRowExistenceCustom($sqlQuery)) {
				
				#--(Begin)-->fetch user info
				$userId=$this->getIdByColumn($this->_colnUsername,$username);
				$columnsValues=cmfcMySql::load($this->_tableName,$this->_colnId,$userId);
				#--(End)-->fetch user info
				
				$myColumnsValues=array($this->_colnActivated=>1);
				if ($this->_clearColumnsPropertiesPasswordAfterActivationEnabled and !$this->_keepPasswordEnabled)
					$myColumnsValues[$this->_colnPassword]='';
					
				$r=$this->update($myColumnsValues, $userId ,$this->_colnId);
				if (!PEAR::isError($r)) {
					#--(Begin)-->send email
					$columnsValues[$this->_colnActivated]=1;
					$this->runCommand('sendEmailAfterActivation',$columnsValues);
					#--(End)-->send email
					$this->redirect($this->_pagesInfo['afterActivation']);
					return true;
				} else {
					return $this->raiseError('', CMF_UserSystem_Activation_Failed,
							PEAR_ERROR_RETURN,NULL,
							array('username'=>$username, 'activationCode'=>$activationCode));
				}
			} else {
				return $this->raiseError('', CMF_UserSystem_Confirmation_Code_Is_Not_Valid,
								PEAR_ERROR_RETURN,NULL,
								array('username'=>$username, 'activationCode'=>$activationCode));
			}
		} else {
			return $this->raiseError('', CMF_UserSystem_Auto_Activation_Is_Not_Enabled,
							PEAR_ERROR_RETURN,NULL,
							NULL);
		}
	}
	
	function requestNewPassword($username,$email=null) {
		$sqlQuery="SELECT * FROM $this->_tableName WHERE  ";
		if (!is_null($username)) {
			$username=mysql_real_escape_string($username);
			$sqlQuery.=" $this->_colnUsername='$username' ";
		}
		if (!is_null($email)) {
			$email=mysql_real_escape_string($email);
			if (!is_null($username)) $sqlQuery.=" AND ";
			$sqlQuery.=" $this->_colnEmail='$email' ";
		}

		$columnsValues=cmfcMySql::loadCustom($sqlQuery);
		
		if (is_array($columnsValues)) {
			$columnsValues[$this->_colnActivationCode]=$this->encrypt($columnsValues[$this->_colnEmail].'*&^2requestNew|Password65'.date('Y-m-d'));
			$this->runCommand('sendEmailRequestNewPassword',$columnsValues);
		} else {
			return $this->raiseError('', CMF_UserSystem_Username_Or_Email_Does_Not_Exists,
								PEAR_ERROR_RETURN,NULL,
								NULL);
		}
	}
	
	function applyNewPassword($email,$activationCode,$password=null) {
		if ($activationCode==$this->encrypt($email.'*&^2requestNew|Password65'.date('Y-m-d'))) {
			$columnsValues=cmfcMySql::load($this->_tableName,$this->_colnEmail,$email);
			
			#--(Begin)-->preparing new password
			if (is_null($password)) {
				$password=cmfcString::makeRandomString(6, 10 , true, false, true);
			}
			$columnsValues[$this->_colnPassword]=$password;
			$columnsValuesToUpdate=array(
				$this->_colnPassword=>$password,
				//$this->_colnActivationCode=>''
			);
			#--(End)-->preparing new password
			
			
			$result=$this->update($columnsValuesToUpdate,$columnsValues[$this->_colnId]);
			if (!PEAR::isError($result)) {
				$this->runCommand('sendEmailNewPasswordApplied',$columnsValues);
			} else {
				return $result;
			}
		} else {
			return $this->raiseError('', CMF_UserSystem_Confirmation_Code_Is_Not_Valid,
							PEAR_ERROR_RETURN,NULL,
							NULL);
		}
	}
	
	function isAccessible($sectionId,$permissionsString=null) {
		if (is_null($permissionsString)) $permissionsString=$this->cvPermissions;
		
		if (is_object($this->_oPermissionSystem)) {
			if ($this->_oPermissionSystem->isAccessible($sectionId,$permissionsString))
				return true;
		} else {
			return $this->raiseError('', CMF_UserSystem_No_Permission_System_Available,
				PEAR_ERROR_RETURN, NULL, NULL);
		}
		return false;
	}
	
	/**
	* @desc redirects to deny access page if its not accessible
	*/
	function isPageAccessible($sectionId,$permissionsString=null) {
		if ($this->isAccessible($sectionId,$permissionsString)) {
			return true;
		} else {
			$this->redirect($this->_pagesInfo['denyAccess']); 
		}
		return false;
	}
	
}