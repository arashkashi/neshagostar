<?php
/**
* @version $Id: extensionManager.class.inc.php 197 2008-11-13 08:40:59Z sinasalek $
*/

define('CMF_ImageManipulatorV1_ExtensionDoesNotExists',3,true);
define('CMF_ImageManipulatorV1_ExtensionAlreadyAdded',4,true);
define('CMF_ImageManipulatorV1_ExtensionFunctionDoesNotExists',5,true);

class cmfcImageManipulatorV1ExtensionManager extends cmfcClassesCore {
	var $_extensions=array();
	var $_extensionsFolderPath='extensions';
	/**
	* @desc something like mainClassNameExtensionName
	*/
	var $_extensionsNamePrefix='cmfcImageManipulatorV1Ext';
	
	var $_extensionsFunctions=array();
	var $_extensionsVariables=array();
	
	
 	/**
 	* @example
 	* <code>
 	* $object->addExtension('commandName','functionName')
 	* $object->addExtension('commandName',array(&$myObject,'methodName'))
 	* </code>
 	*/
    function loadExtension ($name) {
    	$extensionFilePath=dirname(__FILE__).'/'.$this->_extensionsFolderPath.'/'.$name.'/'.$name.'.class.inc.php';
    	
    	if (isset($this->_extensions[$name])) {
    		return $this->raiseError('', CMF_ClassesCore_ExtensionAlreadyAdded, PEAR_ERROR_RETURN,NULL);
		}
    	
    	if (file_exists($extensionFilePath)) {
    		include($extensionFilePath);
			
			//$this->_extensions[$name]=eval("new ".$this->_extensionsNamePrefix.ucfirst($name).'();');
			if ($name=='watermarkBeta') {
				$this->_extensions[$name]=&new cmfcImageManipulatorV1ExtWatermarkBeta;
			}
    		
    		$this->_extensions[$name]->_extensionParent=$this;
			#--(Begin)-->Workaround for stupid php4 bug
			$this->_extensions[$name]->_extensionParent->_imgOrig=&$this->_imgOrig;
			$this->_extensions[$name]->_extensionParent->_imgFinal=&$this->_imgFinal;
			#--(End)-->Workaround for stupid php4 bug

    		$functionsList=$this->_extensions[$name]->getFunctionsListForExtensionParent();
    		foreach ($functionsList as $forParentName=>$realName) {
    			$this->_extensionsFunctions[$forParentName]=array(
    				'extensionName'=>$name,
    				'name'=>$realName
    			);
			}
			
    		$variablesList=$this->_extensions[$name]->getVariablesListForExtensionParent();
    		foreach ($variablesList as $forParentName=>$realName) {
    			$this->_extensionsVariables[$forParentName]=array(
    				'extensionName'=>$name,
    				'name'=>$realName
    			);
			}    		
			
		} else {
			return $this->raiseError('', CMF_ClassesCore_ExtensionDoesNotExists, PEAR_ERROR_RETURN,NULL);
		}
    }
    
    function callMethod($methodName) {
    	#--(Begin)-->Find method extension
    	if (isset($this->_extensionsFunctions[$methodName])) {
    		$extName=$this->_extensionsFunctions[$methodName]['extensionName'];
    		$extMethodName=$this->_extensionsFunctions[$methodName]['name'];
		}

    	#--(End)-->Find method extension
    	if (isset($this->_extensions[$extName])) {
			$args = func_get_args();

			unset($args[0]);
			if (is_callable(array(&$this->_extensions[$extName], $extMethodName))) {
				call_user_func_array(array(&$this->_extensions[$extName], $extMethodName), $args);
			} else {
				return $this->raiseError('', CMF_ClassesCore_ExtensionFunctionDoesNotExists, PEAR_ERROR_RETURN,NULL);
			}
		} else {
			return $this->raiseError('', CMF_ClassesCore_ExtensionDoesNotExists, PEAR_ERROR_RETURN,NULL);
		}    	
	}
    
    function callExtensionFunction($extName,$methodName) {
    	if (isset($this->_extensions[$extName])) {
			$args = func_get_args();
			unset($args[0]);
			unset($args[1]);
			if (is_callable(array(&$this->_extensions[$extName], $methodName)))
				call_user_func_array(array(&$this->_extensions[$extName], $methodName), $args);
			else
				return $this->raiseError('', CMF_ClassesCore_ExtensionFunctionDoesNotExists, PEAR_ERROR_RETURN,NULL);
		} else {
			return $this->raiseError('', CMF_ClassesCore_ExtensionDoesNotExists, PEAR_ERROR_RETURN,NULL);
		}
	}
	
		
	function setOption($name,$value,$merge=false) {
		if ($name=='storage') {
			$r=&$this->setStorage(&$value);
		} elseif ($name=='storage') {
			$r=&$this->setLog(&$value);
		} elseif (is_array($value) and $merge==true) {
			$this->{'_'.$name}=&cmfcArray::mergeRecursive($this->{'_'.$name},$value);
		} else {
			$this->{'_'.$name}=&$value;
		}
		$this->_options[$name]=&$value;

		if (isset($this->_extensionsVariables['_'.$name])) {
    		#--(Begin)-->Find method extension
   			$extName=$this->_extensionsVariables['_'.$name]['extensionName'];
   			$extVarName=$this->_extensionsVariables['_'.$name]['name'];
    		#--(End)-->Find method extension			
    		
    		if (!empty($extVarName)) {
    			if (is_array($value) and $merge==true) {
					$this->_extensions[$extName]->{$extVarName}=&cmfcArray::mergeRecursive($this->_extensions[$extName]->{$extVarName},$value);
				} else {
					$this->_extensions[$extName]->{$extVarName}=&$value;
				}
				$this->_extensions[$extName]->_options[$extVarName]=&$value;
			}
		}
		return $r;
	}
}
