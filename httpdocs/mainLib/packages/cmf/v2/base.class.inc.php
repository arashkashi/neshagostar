<?php
/**
 * this classes are base classes becuase they have used inside most of other fucntions and classes
 * @package cmf
 * @subpackage beta
 * @author Sina Salek
 * @todo should remove in favor of classesCore as of generallib3
 * @version $Id: base.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
 */
 
 
if (!class_exists('PEAR')) {
	trigger_error('PEAR package does not included , please include it first via gclConfiguration::includePearFile()',E_USER_ERROR);
}

define('CMF_BaseClass_Ok',true);
define('CMF_BaseClass_Error',2,true);

/**
 * all cmf classes inherit from this class
 *
 */
class cmfcBaseClass extends PEAR
{
		/**
	 * PEAR::Log object used for error logging by ErrorStack
	 *
	 * @var    Log
	 * @access public
	 */
    var $_oLog = null;
    
   /** 
   * @var cmfcStorage instance
   */
	var $_oStorage=null;

	var $_dynamicSystemStatus=false;
	var $_debugModeStatus=false;
	var $_language=CMF_Ln_English;
	var $_configs;
	var $_defaultError=CMF_Error;
	var $_messagesValue=array(
		CMF_Ok		=> 'no error',
		CMF_Error=>'unknown error'
	);
	var $_errosStack=array();
	

	/**
	 * there is no __construct function in php4 or down , so this function is solution , now it's possible 
	 * for all chid of this base class to have __construct functions
	 * 
	 */
	function cmfcBaseClass() {
		//$this->PEAR(get_class($this));
		$args = func_get_args();
		if (is_callable(array(&$this, "__construct")))
			call_user_func_array(array(&$this, "__construct"), $args);
	}
	
	
	function setConfigs($configs) {
		if (isset($configs['language'])) $this->setLanguage($configs['language']);
		if (isset($configs['messagesValue'])) $this->setMessagesValue($configs['messagesValue']);
		if (isset($configs['dynamicSystemStatus'])) $this->setDynamicSystemEnabled($configs['dynamicSystemStatus']);
		if (isset($configs['storage'])) $this->setStorage(&$configs['storage']);
		if (isset($configs['log'])) $this->setLog(&$configs['log']);
		
		$this->_configs=$config;
	}
	
	function setOptions($options) {
		foreach ($options as $name=>$value) {
			$this->setOption($name,$value);
		}
	}
	
	function setOption($name,$value) {
		if ($name=='storage')
			$this->{'_oStorage'}=$value;
		else
			$this->{'_'.$name}=$value;
	}
	
	function setStorage(&$value) {
		$this->_oStorage=&$value; 
	}
	
	function setLog(&$value) {
		//if (!empty($this->_oLog))
			//$this->_oLog=&Log::singleton('file', 'out.log', 'CreativeMindFramework');
		$this->_oLog=&$value;
	}
	
	function setLanguage($value) {
		$this->_language=$value;
	}
	
	function setDynamicSystemEnabled($value) {
		$this->_dynamicSystemStatus=$value;
	}
	
	function isDynamicSystemEnabled() {
		return $this->_dynamicSystemStatus;
	}
	
	function setDebugModeStatus($value) {
		$this->_debugModeStatus=$value;
	}
	
	function isDebugModeEnabled() {
		return $this->_debugModeStatus;
	}
	
	
	function setMessagesValue($value) {
		$this->_messagesValue=$value;
	}
	
	function getMessageValue($msgCode,$parameters=null) {
		if (isset($this->_messagesValue[$msgCode]))	
			$message=$this->_messagesValue[$msgCode];
		else
			$message=$this->_messagesValue[$this->_defaultError];
		if (is_array($parameters))
			$message=sprintf($message,$parameters);
		return $message;
	}
	
	/** 
	* If You Want to have just variables of end class, not variables of end class and its parents, this function is your solution	
	* @return array


	function getObjectVars()
	{
		$parent_object_vars=get_class_vars(get_parent_class($this));
		$object_vars=get_object_vars($this);
		foreach ($parent_object_vars as $key=>$value)
			foreach ($object_vars as $_key=>$_value)
				if ($key==$_key) { unset($object_vars[$_key]);  break; }
		return $object_vars;
	}
	
	/**
	*	fill all of object variables with their default values except $base_properties
	*	$base_properties=array('local_language_name','db','event_system','configurations','table_name_prefix');
	*   <BENEFITS> : increases object creation speed in loop, in fact there is no need to create class anymore, just create it at first time and then call this function for furture uses
	* 	@param $base_properties array
	*/
	function resetProperties($baseBroperties=null,$prefix=null)
	{
		$classVars=get_class_vars(cmfcPhp4::get_class($this));
		foreach ($classVars as $varName=>$defaultValue) {
			if (!in_array($varName,$baseBroperties)) {
				if (is_integer($defaultValue) or is_float($defaultValue))
					$this->{$prefix.$varName}=$defaultValue;
				else
					$this->{$prefix.$varName}=$defaultValue;
			}
		}
	}
	
	
	function arrayToProperties($propertiesValues,$exceptNulls=false,$prefix=null) {
		if (is_array($propertiesValues)) {
			if ($this->isDynamicSystemEnabled()) {
				//$code='';
				foreach($propertiesValues as $propertyName=>$propertyValue) {
					/* // The Old Way Via Eval
						if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*+$/',$columnName)>0)
							$code.='@$this->'.$this->columnValueProperyNamePrefix.$columnName.'=$columnValue';
					*/
					// The new way via $this->{}
					
					$this->{$prefix.$propertyName}=$propertyValue;
				}
				//eval($code);
			} else {
				// only for sample :
				/*
				if ($exceptNulls==false or ($exceptNulls and !is_null($propertiesValues[$this->colnId])))
					@$this->cvId=$propertiesValues[$this->colnId];
				*/
			}
			return true;
		}
		return false;
	}

	function propertiesToArray($exceptNulls=false,$prefix=null) {
		$propertiesValues=array();

		if ($this->isDynamicSystemEnabled()) {
			// The old way using "eval" : 
			/*
			$vars=get_object_vars($this);
			$code='';
			foreach ($vars as $varName=>$varValue) {
				if (preg_match('/^'.$this->columnValueProperyNamePrefix.'[a-zA-Z][a-zA-Z0-9_]*+$/',$varName)>0)
					$code.='$columns_values[$this->'.$this->columnNameProperyNamePrefix.colnId.']=$varValue;';
			}
			eval($code);
			*/
			
			$vars=get_object_vars($this);
			foreach ($vars as $varName=>$varValue) {
				if (preg_match('/^'.$prefix.'.*/',$varName) or is_null($prefix))
					$propertiesValues[$varName]=$varValue;
			}

		} else {
			// only for sample :
			/*
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvId)))
				$propertiesValues[$this->colnId]=$this->cvId;
			*/
		}

		return $propertiesValues;
	}

	function clearProperties($prefix=null)
	{
		if ($this->isDynamicSystemEnabled()) {
			// The old way using "eval" : 
			/*
			$vars=get_object_vars($this);
			$code='';
			foreach ($vars as $varName=>$varValue) {
				if (preg_match('/^'.$this->columnValueProperyNamePrefix.'[a-zA-Z][a-zA-Z0-9_]*+$/',$var_name)>0)
					$code.='@$this->'.$this->columnNameProperyNamePrefix.$varName.'=null;';
			}
			eval($code);
			*/
			
			$vars=get_object_vars($this);
			foreach ($vars as $varName=>$varValue) {
				if (preg_match('/^'.$prefix.'.*/',$varName) or is_null($prefix))
					$this->{$varName}=null;
			}
		} else {
			// only for sample :
			/*
			$this->_language=null;
			*/
		}
	}
	

    // }}}
    // {{{ raiseError()

    /**
     * conditionally includes PEAR base class and raise an error
     *
     * @param string $msg  Error message
     * @param int    $code Error code
     * @access private
     */
	function raiseError($message = null, $code = null, $mode = null, $options = null,
                         $userinfo = null, $error_class = null, $skipmsg = false) {
		if (isset($this->_messagesValue[$code]) && empty($message))
			$message=$this->_messagesValue[$code];
			
		if (is_array($userinfo) && !empty($message) ) {
			foreach ($userinfo as $key=>$value) {
				$replacements['%'.$key.'%']=$value;
			}
			$message=replace_variables($replacements,$message);
		}
		return PEAR:: raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
	}
}

?>