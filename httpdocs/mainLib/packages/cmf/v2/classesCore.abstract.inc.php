<?php
/**
* classes core
* this classes are base classes becuase they have used inside most of other fucntions and classes
* @version $Id: classesCore.class.inc.php 187 2008-10-27 10:11:18Z sinasalek $ 
* @package cmf
* @subpackage beta
* @author Sina Salek <sina.salek.ws>
* @version $Id: classesCore.class.inc.php 187 2008-10-27 10:11:18Z sinasalek $* @company persiantools.com
* @changes
* 	- ability to enabled disabled passing events to observers and command handlers, via setOption('observingEnabled',false) and setOption('commandingEnabled',false)
* @todo 
* 	- support PEAR::isError() method
* 	- PEAR_ERROR_RETURN and other raiseError constants should become CMF_ 	
*/


/**
 * all cmf classes inherit from this class
 *
 */
abstract class cmfaClassesCore
{
	/**
	 * 
	 * @var unknown_type
	 */
	public $version='$Id: classesCore.class.inc.php 187 2008-10-27 10:11:18Z sinasalek $';
	
	/**
	 * 
	 * @var unknown_type
	 */
	public $options;	    	
	
	/**
	 * 
	 * @var unknown_type
	 */
	public $defaultErrorCode=1;
	
	/**
	 * 
	 * @var unknown_type
	 */
	public $messages=array(
		'error'=>array(
			'title'=>'Unkown error',
			'type'=>'error',
			'code'=>1
		)
	);
	
	/**
	 * 
	 * @param $options
	 * @param $merge
	 * @return unknown_type
	 */
	function setOptions($options,$merge=false) {
		foreach ($options as $name=>$value) {
			$r=$this->setOption($name,$value,$merge);
			if (PEAR::isError($r)) {
				return $r;
			}
		}
	}
	
	/**
	 * 
	 * @param $name
	 * @param $value
	 * @param $merge
	 * @return unknown_type
	 */
	function setOption($name,$value,$merge=false) {
		if (is_array($value) and $merge==true) {
			$this->{$name}=&cmfcArray::mergeRecursive($this->{'_'.$name},$value);
		} else {
			$this->{$name}=&$value;
		}
		$this->options[$name]=&$value;
		return $r;
	}
	
	/**
	* works fine in both php4 & 5. but you should use & when you call the function. $b=&$ins->getOption('property')
	*/
	function &getOption($name) {
		return $this->{$name};
	}
	
	/**
	 * 
	 * @param $msgCode
	 * @param $parameters
	 * @return unknown_type
	 */
	function getMessageValue($msgCode,$parameters=null) {
		if (isset($this->_messagesValue[$msgCode]))	{
			$message=$this->_messagesValue[$msgCode];
		} else {
			$message=$this->_messagesValue[$this->_defaultError];
		}
		if (is_array($parameters)) {
			$message=sprintf($message,$parameters);
		}
		return $message;
	}
	
	
    /**
     * conditionally includes PEAR base class and raise an error
     * @example
     * <code>
     * 		return $this->raiseError('', CMF_Language_Error_Unknown_Short_Name,
	 *						PEAR_ERROR_RETURN,NULL, 
	 *						array('shortName'=>$shortName)
	 *		);
     * </code>
     * @param string $msg  Error message
     * @param int    $code Error code
     * @access private
     */
	function raiseError($message = null, $code = null, $mode = null, $options = null,
                         $userinfo = null, $error_class = null, $skipmsg = false) {
		if (isset($this->_messagesValue[$code]) && empty($message))
			$message=$this->_messagesValue[$code];
			
		if (is_array($userinfo) && !empty($message)) {
			if (is_array($userinfo))
			foreach ($userinfo as $key=>$value) {
				$replacements['%'.$key.'%']=$value;
			}
			$message=cmfcString::replaceVariables($replacements,$message);
		}
		return PEAR:: raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
	}
	
	/**
	 * 
	 * @param $obj
	 * @param $code
	 * @return unknown_type
	 */
	function isError($obj,$code=null) {
		return PEAR::isError($obj,$code);
	}
}