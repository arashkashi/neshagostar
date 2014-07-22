<?php
/**
 * 
 * @author salek
 *
 */
interface cmfiDesignPatterns {
	
	var $_observers=array();
	var $_observeringEnabled=true;
	var $_commandHandlers=array();
	var $_commandingEnabled=true;
	
	
    //! An accessor
    /**
    * Calls the update() function using the reference to each
    * registered observer - used by children of Observable
    * @return void
    */ 
    function notifyObservers ($event,$params=null) {
    	if ($this->_observeringEnabled==true) {
    		if (is_array($this->_observers[$event])) {
        		foreach ($this->_observers[$event] as $observer) {
            		call_user_func_array($observer,array(&$this,$params));
        		}
        	}
    	}
    }
 
    //! An accessor
    /**
    * Register the reference to an object object
    * @param $observer array|string //like call_user_func first param
    * @return void
    */ 
    function addObserver($event, $observer,$parameters=null) {
       	$this->_observers[$event][]=$observer;
    }
    
    
    function prependObserver($event, $observer,$parameters=null) {
    	if (empty($this->_observers[$event])) {
    		$this->_observers[$event]=array();
    	}
    	array_unshift($this->_observers[$event],$observer);
    }
    
    function removeObservers($cmd) {
       	$this->_commandHandlers[$cmd]=array();
    }
	
	/**
	* @example
	* <code>
	* $this->runCommand('sendEmailAfterActivation',$columnsValues);
	* </code>
	*/
    function runCommand($cmd,$params=null) {
    	if ($this->_commandingEnabled==true)
    	if (is_array($this->_commandHandlers[$cmd]))
        foreach ($this->_commandHandlers[$cmd] as $commandHandler) {
			$result=call_user_func_array($commandHandler,array(&$this,$cmd,$params));
            if (!PEAR::isError($result)) {
				return $result;
			}
        }
    }
    
    function hasCommandHandler($cmd) {
       	if (is_array($this->_commandHandlers[$cmd])) {
       		if (!empty($this->_commandHandlers[$cmd])) {
       			return true;
       		}
       	}
       	return false;
    }
 
 	/**
 	* @example
 	* <code>
 	* $object->addCommandHandler('commandName','functionName')
 	* $object->addCommandHandler('commandName',array(&$myObject,'methodName'))
 	* </code>
 	*/
    function addCommandHandler ($cmd, $commandHandler,$parameters=null) {
       	$this->_commandHandlers[$cmd][]=$commandHandler;
    }
    
    
    function removeCommandHandlers ($cmd) {
       	$this->_commandHandlers[$cmd]=array();
    }
    
    function prependCommandHandler ($cmd, $commandHandler,$parameters=null) {
    	if (empty($this->_commandHandlers[$cmd])) {
    		$this->_commandHandlers[$cmd]=array();
    	}
    	array_unshift(&$this->_commandHandlers[$cmd],$commandHandler);
    }
    
    /**
    * memento design pattern
    * will clone the object for adding undo ability.
    * @todo
    * 	- should become complete 
    */
    function saveToMemento() {
    	return clone($this);
	}
	
    /**
    * memento design pattern
    * will load the object previous state
    * @todo
    * 	- should become complete
    */
    function restoreFromMemento($object) {
    	//commented duo to incopatiblility with php5
    	//$this=$object;
	}
}