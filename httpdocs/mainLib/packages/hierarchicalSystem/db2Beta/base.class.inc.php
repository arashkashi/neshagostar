<?php
class cmfcHierarchicalSystemDbBetaBase {
	var $_dynamicSystemEnabled=false;
	
	var $_messagesValue=array(
		CMF_WatermarkBeta_Error=>'Unknown error',
	);
	
	var $_errosStack=array();
	var $_options=array();
	
	var $_observers=array();
	var $_subordinates=array();
	
	
	function cmfcHierarchicalSystemDbBetaBase($options=array()) {
		$this->setOptions($options);
	}
	

	
	function decorator($name, $options=array()) {
		
	}
	/*
		return $this->raiseError('', CMF_Language_Error_Unknown_Short_Name,
							PEAR_ERROR_RETURN,NULL, 
							array('shortName'=>$shortName));
	*/
							
	function raiseError($message = null, $code = null, $mode = null, $options = null,
                         $userinfo = null, $error_class = null, $skipmsg = false) {
		if (isset($this->_messagesValue[$code]) && empty($message))
			$message=$this->_messagesValue[$code];
			
		if (is_array($userinfo) && !empty($message) ) {
			foreach ($userinfo as $key=>$value) {
				$replacements['%'.$key.'%']=$value;
			}
			$message=cmfcString::replaceVariables($replacements,$message);
		}
		return PEAR::raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
	}
	
	function setOptions($options) {
		foreach ($options as $name=>$value) {
			$this->setOption($name,$value);
		}
	}
	

	function setOption($name,$value) {
		switch ($name) {
			default : $this->{'_'.$name}=$value;;break;
		}
		return $result;
	}
	
	/**
	* $desc when using decorator pattern or method which expand the object
	*		in runtime, reinstancing will be possible with this functions,
	*		otherwise an $this->_instance var is require for all methos callings
	*/
	function getVars() {
		$rVars=get_object_vars($this);
		return $rVars;
	}
	
	/**
	* $desc when using decorator pattern or method which expand the object
	*		in runtime, reinstancing will be possible with this functions,
	*		otherwise an $this->_instance var is require for all methos callings
	*/	
	function setVars($vars) {
		foreach ($vars as $name=>$value) {
			$this->{$name}=$value;
		}
	}
	
	
    /**
    * Calls the update() function using the reference to each
    * registered observer - used by children of Observable
    * @return void
    */ 
    function notifyObservers ($event,$params=null) {
    	if (is_array($this->_observers[$event]))
        foreach ($this->_observers[$event] as $observer) {
            call_user_func_array($observer,array(&$this,$param));
        }
    }
 
    /**
    * Register the reference to an object object
    * @param $observer array|string //like call_user_func first param
    * @return void
    */ 
    function addObserver ($event, $observer) {
       	$this->_observers[$event]=$observer;
    }
	

    function runCommand($cmd,$params=null) {
    	if (is_array($this->_subordinates[$cmd]))
        foreach ($this->_subordinates[$cmd] as $subordinate) {
			$result=call_user_func_array($subordinate,array(&$this,$cmd,$params));
            if (!PEAR::isError($result)) {
				return $result;
			}
        }
    }
 
    function addSubordinate ($cmd, $handler) {
       	$this->_subordinates[$cmd][]=$handler;
    }
}