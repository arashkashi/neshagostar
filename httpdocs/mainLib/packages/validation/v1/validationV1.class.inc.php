<?php
/**
* Combo validation via PHP and Javascript
* 
* @version v1@2007-10-11
* @version $Id: validationV1.class.inc.php 203 2008-12-13 08:44:47Z Salek $
* @author sina salek
* @website http://sina.salek.ws 
* @company persiantools.com
* @changelog
* 	+ package can now user optimizer package
* 	+ javascript part of the class is now in an external file
* 	+ support for custom action after sucessful validation
*	+ More php5 compatibility
*	+ defaultStylesEnabled=false for customizing all the styles
* 	+ in firefox when form defines in non w3c way, firefox cann't find the fields inside it. it should report (reported by Akbar NasrAbadi)
* 	+ getElementsByName() issue with multiRadioBoxes and multiCheckBoxes solved 
* 	+ supporting for new type "array"
* 	+ optimizing getFormFieldObject() for large forms
* 	+ if formName does not define, it will for the fields in the whole document
* 	+ support finding the field value via only field name without pattern name
* 	+ tested with PHP 4.x and PHP 5.2.x
* 	+ completely tested with IE 6.x,7.x , Firefox 2.x , Opera 9.x , Safari 3.x
* 	+ ajax support via comppletely overriding object display or validation function
* 	+ custom field type
* 	+ customizing error messages
* 	+ override display function
* 	+ custom new display modes
* 	+ overriding or extending validation functions (validate,validateAfter,validateBefore)
* 	+ limited custom validation for fields which have need regular validations like notEmpty and also some more
* 	+ chain of commands pattern support for javascript and php
* 	+ validate fields with regular expression (javascript & php)
* 
* @todo
* 	- All fields should have 500 chars length maximum by default.
* 	- Implementing Filtering
* 	- better error reporting
* 	- validation for fields and parameters
* 	- making it jquery compatible
* 	- validation should work with multipy forms in one page (by adding formName item to each fieldInfo)
* 	- single javascript class in webpage with multiply usage,validation instances should be connected
* 	- special methods for ease of validation via Ajax
* 	- support three type of messages , warning , information , error
* 	- compress javascripts
*/


define ('CMF_ValidationBeta_Ok',true);
define ('CMF_ValidationBeta_Error',2);
define ('CMF_ValidationBeta_Is_Not_Valid_Email',3);
define ('CMF_ValidationBeta_Is_Not_Valid_Url',4);
define ('CMF_ValidationBeta_Is_Not_Number',5);
define ('CMF_ValidationBeta_Is_Not_Within_Range',6);
define ('CMF_ValidationBeta_Is_Not_Within_Count_Range',7);
define ('CMF_ValidationBeta_Is_Empty',8);
define ('CMF_ValidationBeta_Is_Not_Selected',9);
define ('CMF_ValidationBeta_Is_Not_Within_Length_Range',10);
define ('CMF_ValidationBeta_Is_Not_String',11);
define ('CMF_ValidationBeta_Is_Not_Match_With_Pattern',12);

define ('CMF_ValidationV1_Ok',true);
define ('CMF_ValidationV1_Error',2);
define ('CMF_ValidationV1_Is_Not_Valid_Email',3);
define ('CMF_ValidationV1_Is_Not_Valid_Url',4);
define ('CMF_ValidationV1_Is_Not_Number',5);
define ('CMF_ValidationV1_Is_Not_Within_Range',6);
define ('CMF_ValidationV1_Is_Not_Within_Count_Range',7);
define ('CMF_ValidationV1_Is_Empty',8);
define ('CMF_ValidationV1_Is_Not_Selected',9);
define ('CMF_ValidationV1_Is_Not_Within_Length_Range',10);
define ('CMF_ValidationV1_Is_Not_String',11);
define ('CMF_ValidationV1_Is_Not_Match_With_Pattern',12);
define ('CMF_ValidationV1_Field_Does_No_Exists',13);
define ('CMF_ValidationV1_Password_And_Its_Confirmation_Are_Not_Same',14);

/**
* This class is meant to validate HTML forms via PHP and also Javascript  in the simplest possible way. 
* all you need to do is to create an array of form fields information with require parameters and then call few methods.
* @package ComboValidation
*/
class cmfcValidationV1 extends cmfcClassesCore {
	
	/**
	 * Full path of the page.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_pageFolderPath='';
	/**
	 * Page browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_pageFolderPathBrowser='';
	/**
	 * Full path of the site
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPath='';
	/**
	 * Site browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPathBrowser='';
	
	/**
	 * @var string
	 */
	var $_packageFolderPath='';
	/**
	 * @var string
	 */
	var $_packageFolderPathBrowser='';
	
	var $_optimizerObj;
	
	/**
	* javascript class and function name prefix for preventing
	* duplication
	* @var string
	*/
	var $_prefix='cmf';
	/**
	* name of the form which contains fields
    * <code> 
    * <form name="myForm"....
    * </code>
    * var string
	*/
	var $_formName;
    /**
    * Name of the javascrip variable that will contain ComboValidation class instance
    * @var string
    */
	var $_jsInstanceName='myCmfcValidationV1';
	/**
	* 
	* @var boolean
	*/
	var $_defaultStylesEnabled=true;
	/**
	* 
	* @var boolean
	*/
	var $_prepareOnCall=true;
	/**
	* for multilingual website you can change this messages easily
	* @notice do not include following code into class initializing array, because messages name (definitions)
	*   define after object initializing
	*/
	var $_messagesValue=array(
		CMF_ValidationV1_Error=>'Unknown error',
		CMF_ValidationV1_Is_Not_Valid_Email=>'"__value__" in __title__ is not valid email',
		CMF_ValidationV1_Is_Not_Valid_Url=>'"__value__" in __title__ is not valid url',
		CMF_ValidationV1_Is_Not_Number=>'"__value__" in __title__ is not number',
		CMF_ValidationV1_Is_Not_Within_Range=>'"__value__" of __title__ is not within this range (__min__,__max__)',
		CMF_ValidationV1_Is_Not_Within_Count_Range=>'"__value__" of __title__ is not within this count range (__min__,__max__)',
		CMF_ValidationV1_Is_Empty=>'__title__ value should not be empty',
		CMF_ValidationV1_Is_Not_Selected=>'__title__ is not selected',
		CMF_ValidationV1_Is_Not_Within_Length_Range=>'"__value__" of __title__ is not within this length range (__min__,__max__)',
		CMF_ValidationV1_Is_Not_String=>'__title__ is not string',
		CMF_ValidationV1_Is_Not_Match_With_Pattern=>'__title__ is not match with __desc__',
		CMF_ValidationV1_Field_Does_No_Exists=>'__title__ field "__fieldName__" does not exists',
		CMF_ValidationV1_Password_And_Its_Confirmation_Are_Not_Same=>"__title__ field and its confirmation are not the same",
		CMF_ValidationV1_Is_Not_Array=>'__title__ is not array'
	);
	
	var $_messagesCode=array(
		'CMF_ValidationV1_Error'=>CMF_ValidationV1_Error,
		'CMF_ValidationV1_Is_Not_Valid_Email'=>CMF_ValidationV1_Is_Not_Valid_Email,
		'CMF_ValidationV1_Is_Not_Valid_Url'=>CMF_ValidationV1_Is_Not_Valid_Url,
		'CMF_ValidationV1_Is_Not_Number'=>CMF_ValidationV1_Is_Not_Number,
		'CMF_ValidationV1_Is_Not_Within_Range'=>CMF_ValidationV1_Is_Not_Within_Range,
		'CMF_ValidationV1_Is_Not_Within_Count_Range'=>CMF_ValidationV1_Is_Not_Within_Count_Range,
		'CMF_ValidationV1_Is_Empty'=>CMF_ValidationV1_Is_Empty,
		'CMF_ValidationV1_Is_Not_Selected'=>CMF_ValidationV1_Is_Not_Selected,
		'CMF_ValidationV1_Is_Not_Within_Length_Range'=>CMF_ValidationV1_Is_Not_Within_Length_Range,
		'CMF_ValidationV1_Is_Not_String'=>CMF_ValidationV1_Is_Not_String,
		'CMF_ValidationV1_Is_Not_Match_With_Pattern'=>CMF_ValidationV1_Is_Not_Match_With_Pattern,
		'CMF_ValidationV1_Field_Does_No_Exists'=>CMF_ValidationV1_Field_Does_No_Exists,
		'CMF_ValidationV1_Password_And_Its_Confirmation_Are_Not_Same'=>CMF_ValidationV1_Password_And_Its_Confirmation_Are_Not_Same,
		'CMF_ValidationV1_Is_Not_Array'=>CMF_ValidationV1_Is_Not_Array
	);
    /**
    * An array including occured errors
    * @var array
    */
	var $_errorsStack=array();
	/**
    * Possible values : alert, div, pageCenterDiv, formCenterDiv, nearFields, customizedDiv
	* @var string 
	*/
	var $_displayMethod='alert';
	/**
	* Details information about displayMethod settings
	* @var array
	*/
	var $_displayMethodOptions=array();

	/**
	* All of the require information about fields
	* @var array
	*/
	var $_fieldsInfo=array(
		'email'=>array(
			'name'=>'row[email]',
			'title'=>'Email',
			'type'=>'email',
			/**
			* id of the elements which is responsible for showing event of the specific 
			* form field.
			* if will be used if you set errorDisplayMethod to 'nearFields'
			*/
			'jsMessageBoardId'=>'',
			'param'=>array(
				'min'=>0,
				'max'=>5,
				'notEmpty'=>true
			)
		),
	);
	
	/**
	* Custom field types array
    * @var array
	*/
	var $_fieldTypesInfo=array(
		'age'=>array(
			/**
			* methodname($obj,$fieldsValues,$fieldInfo,$patternName,$a,$b,$c)
			* array('function'=>array(&$object,'methodname','parameters'=>array($a,$b,$c))
			*/
			'validationHandler'=>array(
				'function'=>'', //name of the function or array of the object method array(&obj,'methodName')
				'parameters'=>array() //if your function has additional parameters
			),
			/**
			* methodname(obj,fieldsValue,fieldInfo,patternName,a,b,c)
			* array('function'=>array(&$object,'methodname','parameters'=>array($a,$b,$c))
			*/
			'jsValidationHandler'=>array(
				'function'=>'', //name of the function or array of the object method array(&obj,'methodName')
				'parameters'=>array() //if your function has additional parameters
			)
		)
	);
	
	
	
	
	
	/**
    * Construction
    */
	function __construct($options) {
		$this->_fieldsInfo=array();
		$this->setOptions($options);
		//$this->addCommandHandler('validate',array(&$this,'__validate'));
	}
	
	/**
	* <code>
	* return $this->raiseError('', CMF_Language_Error_Unknown_Short_Name,
	*		PEAR_ERROR_RETURN,NULL, 
	*		array('shortName'=>$shortName));
	* </code>
	*/
	function raiseError($message = null, $code = null, $mode = null, $options = null,
                         $userinfo = null, $error_class = null, $skipmsg = false) {
		if (isset($this->_messagesValue[$code]) && empty($message))
			$message=$this->_messagesValue[$code];
			
		if (is_array($userinfo) && !empty($message) ) {
			foreach ($userinfo as $key=>$value) {
				$replacements['__'.$key.'__']=$value;
			}
			$message=cmfcString::replaceVariables($replacements,$message);
		}
		return parent::raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see beta/cmfcClassesCore#setOptions($options, $merge)
	 */
	function setOptions($options,$merge=false) {
		
		if (!$merge) {
			if (!isset($options['pageFolderPath'])) {
				$options['pageFolderPath']=dirname($_SERVER['SCRIPT_FILENAME']);
			}
			if (!isset($options['pageFolderPathBrowser'])) {
				$options['pageFolderPathBrowser']=dirname($_SERVER['SCRIPT_NAME']);
			}
			if (!isset($options['siteFolderPath'])) {
				$options['siteFolderPath']=$_SERVER['DOCUMENT_ROOT'];
			} 
			if (!isset($options['siteFolderPathBrowser'])) {
				$options['siteFolderPathBrowser']='';
			}
			if (!isset($options['packageFolderPath'])) {
				$options['packageFolderPath']=dirname(__FILE__);
			}
			if (!isset($options['packageFolderPathBrowser'])) {
				$fileRelativePath='/'.str_replace($options['siteFolderPath'],'',$options['packageFolderPath']);
				$options['packageFolderPathBrowser']=cmfcDirectory::normalizePath($fileRelativePath);
			}
		}

		parent::setOptions($options,$merge);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see beta/cmfcClassesCore#setOption($name, $value, $merge)
	 */
	function setOption($name,$value,$merge=false) {
		if ($name=='pageFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
		} elseif ($name=='siteFolderPath') {
			$value=cmfcDirectory::normalizePath($value);
		}
		if ($name=='fieldsInfo' and is_array($value)) {    
			foreach ($value as $k=>$v) {
				$value[$k]['name']=$k;
			}
		}
		
		parent::setOption($name,$value,$merge);
	}
	
	
	/**
	* Convert php value to javascript
	* @param variant $var
	* @param interger $tab //number of tab chars for indent
	* @param boolean $singleLine //result as single line
	*/
	function phpParamToJs($var,$tabs = 0,$singleLine=true) {
		//if (is_string($var)) return $var;
		return cmfcHtml::phpToJavascript($var,0,true,array('alwaysUseObjectForArray'=>true));
	}
	
	/**
	* convert php values to javascript
	* @param variant $var
	*/
	function phpParamsToJs($vars) {
		foreach ($vars as $key=>$var) {
			$vars[$key]=$this->phpParamToJs($var);
		}
		return $vars;
	}

	
	/**
	* @shortName getJsCAK
	* @see getJsCAK
	*/
	function getJavascriptCompatibleArrayKey($key) {
		if (is_numeric($key)) $key="_$key";
		$key=$this->phpParamToJs($key);
		return $key;
	}
	
	/**
	* short name version of getJavascriptCompatibleArrayKey()
    * @see getJavascriptCompatibleArrayKey
	*/
	function getJsCAK($key) {
		return $this->getJavascriptCompatibleArrayKey($key);
	}
	
	/**
	* validate $fieldsValues via fieldsInfo 
	* @param string $patternName //"rows[$num][columns][%s]"
	*/
	function validate($fieldsValues,$patternName=null) {
		$result1=$this->runCommand('validateBefore',array('fieldsValues'=>$fieldsValues,'patternName'=>$patternName));
		
		if ($this->hasCommandHandler('validate'))
			$result2=$this->runCommand('validate',array('fieldsValues'=>$fieldsValues,'patternName'=>$patternName));
		else
			$result2=$this->__validate(&$this,'validate',array('fieldsValues'=>$fieldsValues,'patternName'=>$patternName));
		$result3=$this->runCommand('validateAfter',array('fieldsValues'=>$fieldsValues,'patternName'=>$patternName));
		$result=cmfcPhp4::array_merge($result1,$result2,$result3);
		return $result;
	}
	
	/**
	* Validate $fieldsValues via fieldsInfo , builtin validation handler
	* @param object //commander object which is validation
    * @param string //name of the command
    * @param array //command addtional parameters
	*/
	function __validate($obj,$cmd,$params) {
		$fieldsValues=$params['fieldsValues'];
		$patternName=$params['patternName'];

		$result=array();                           
		foreach ($this->_fieldsInfo as $fieldInfo) { 
			if ($fieldInfo['disabled']==true) continue;
			if (!empty($patternName)) {
				$fieldName=sprintf($patternName,$fieldInfo['headName']);
				$fieldValue=$fieldsValues[$fieldInfo['headName']];
			} else {
				$fieldName=$fieldInfo['name'];
				$fieldValue=cmfcArray::getValueByPath($fieldsValues,cmfcHtml::fieldNameToArrayPath($fieldName));
			}
			
			$fieldInfo=$this->_fieldsInfo[$fieldName];
			$fieldTypeInfo=$this->_fieldTypesInfo[$fieldInfo['type']];
			
			$isFieldValueEmpty=false;
			if (!$this->isError($result[$fieldName])) {
				
				if (in_array($fieldInfo['type'],array('number','email','url','string','dropDownDate','checkBox','password'))) {
					if (empty($fieldValue) and $fieldValue!==0 and $fieldValue!=='0' and $fieldValue==$fieldInfo['param']['emptyValue'])
						$isFieldValueEmpty=true;

					if ($fieldInfo['param']['notEmpty']==true and $isFieldValueEmpty) {
						$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Empty, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
					}
				}
			}
			
			if (!empty($fieldInfo['likeType'])) 
				$likeType=$fieldInfo['likeType'];
			else $likeType=$fieldInfo['type'];
				
			if (!$this->isError($result[$fieldName]) and !$isFieldValueEmpty)
				switch ($likeType) {
					case 'number' :
						if (!is_numeric($fieldValue) and $fieldValue!=='') {
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_Number, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
						}
						
						if (empty($result[$fieldName])) {
							if (isset($fieldInfo['param']['countMin']) or isset($fieldInfo['param']['countMax'])) {
								if (!isset($fieldInfo['param']['countMin'])) $fieldInfo['param']['countMin']='*';
								if (!isset($fieldInfo['param']['countMax'])) $fieldInfo['param']['countMax']='*';
								if ( !((strlen($fieldValue)>=$fieldInfo['param']['countMin'] or $fieldInfo['param']['countMin']=='*') and (strlen($fieldValue)<=$fieldInfo['param']['countMax'] or $fieldInfo['param']['countMax']=='*')) ) {
									$result[$fieldName]=$result[$fieldName]=$this->raiseError( '', CMF_ValidationV1_Is_Not_Within_Count_Range, PEAR_ERROR_RETURN, NULL,  array('title'=>$fieldInfo['title'],'value'=>$fieldValue,'min'=>$fieldInfo['param']['countMin'],'max'=>$fieldInfo['param']['countMax']));
								}
							}
						}
						
						if (empty($result[$fieldName])) {
							if (isset($fieldInfo['param']['min']) or isset($fieldInfo['param']['max'])) {
								if (!isset($fieldInfo['param']['min'])) $fieldInfo['param']['min']='*';
								if (!isset($fieldInfo['param']['max'])) $fieldInfo['param']['max']='*';
								if ( !(($fieldValue>=$fieldInfo['param']['min'] or $fieldInfo['param']['min']=='*') and ($fieldValue<=$fieldInfo['param']['max'] or $fieldInfo['param']['max']=='*')) ) {
									$result[$fieldName]=$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_Within_Range, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue,'min'=>$fieldInfo['param']['min'],'max'=>$fieldInfo['param']['max']));
								}
							}
						}
					break;
					case 'email' :
						if (!cmfcString::isEmailValid($fieldValue) and !empty($fieldValue))
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_Valid_Email, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'], 'value'=>$fieldValue));;
					break;
					case 'url' : 
						if (!cmfcUrl::isValid($fieldValue) and !empty($fieldValue)) 
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_Valid_Url, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'], 'value'=>$fieldValue));
					break;
					case 'array' :
						if (!is_array($fieldValue)) {
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_Array, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
						}
					break;
					case 'string' :
						if (!is_string($fieldValue)) {
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_String, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
						}
						if (empty($result[$fieldName])) {
							if (isset($fieldInfo['param']['lengthMin']) or isset($fieldInfo['param']['lengthMax'])) {
								if (!isset($fieldInfo['param']['lengthMin'])) $fieldInfo['param']['lengthMin']='*';
								if (!isset($fieldInfo['param']['lengthMax'])) $fieldInfo['param']['lengthMax']='*';
								if ( !((strlen($fieldValue)>=$fieldInfo['param']['lengthMin'] or $fieldInfo['param']['lengthMin']=='*') and (strlen($fieldValue)<=$fieldInfo['param']['lengthMax'] or $fieldInfo['param']['lengthMax']=='*')) ) {
									$result[$fieldName]=$result[$fieldName]=$this->raiseError( '', CMF_ValidationV1_Is_Not_Within_Length_Range, PEAR_ERROR_RETURN, NULL,  array('title'=>$fieldInfo['title'],'value'=>$fieldValue,'min'=>$fieldInfo['param']['lengthMin'],'max'=>$fieldInfo['param']['lengthMax']));
								}
							}
						}
						
						if (empty($result[$fieldName])) {	
							if (!empty($fieldInfo['param']['regexp'])) {
								if (!preg_match($fieldInfo['param']['regexp'],$fieldValue)) {
									$result[$fieldName]=$result[$fieldName]=$this->raiseError( '', CMF_ValidationV1_Is_Not_Match_With_Pattern, PEAR_ERROR_RETURN, NULL,  array('title'=>$fieldInfo['title'],'value'=>$fieldValue,'desc'=>$fieldInfo['param']['regexpDescription']));
								}
							}
						}
					break;
					case 'dropDownDate' : 
						if ($fieldInfo['param']['notEmpty']==true)
							if (empty($fieldValue['day']) or empty($fieldValue['month']) or empty($fieldValue['year'])) {
								$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Empty, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
							}
					break;
					case 'checkBox' :
						if (empty($fieldValue))
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Is_Not_Selected, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
					break;
					case 'password' :
						$myFieldsValues=$_POST;
						$confirmationFieldValue=cmfcArray::getValueByPath($myFieldsValues,cmfcHtml::fieldNameToArrayPath($fieldInfo['param']['confirmationFieldName']));
						if ($fieldValue!=$confirmationFieldValue)
							$result[$fieldName]=$this->raiseError('', CMF_ValidationV1_Password_And_Its_Confirmation_Are_Not_Same, PEAR_ERROR_RETURN, NULL, array('title'=>$fieldInfo['title'],'value'=>$fieldValue));
					break;
				}
				
				#--(Begin)-->custom type
				if (empty($result[$fieldName]))
			 	 	if (!empty($fieldTypeInfo['validationHandler'])) {
						$params=array(&$this,$fieldsValues,$fieldInfo,$patternName);
						$params=cmfcPhp4::array_merge($params,$fieldTypeInfo['validationHandler']['parameters']);
						$r=call_user_func_array($fieldTypeInfo['validationHandler']['function'],$params);

						if ($this->isError($r)) {
							$result[$fieldName]=$r;
						}
					}
				#--(End)-->custom type
		}
		
		return $result;
	}
	
	/**
	* print javascript object initilizing scripts
	*/
	function printJsInstance($instanceName=NULL) {
		if (is_null($instanceName)) {
			$instanceName=$this->_jsInstanceName;
		} else { 
			$this->_jsInstanceName=$instanceName;
		}
			
		?>
		<script type="text/javascript">
		// <![CDATA[
			<?php echo $instanceName?>=new cmfcValidationV1();
			<?php echo $instanceName?>.formName="<?php echo $this->_formName?>";
			<?php echo $instanceName?>.fieldsInfo=<?php echo $this->phpParamToJs($this->_fieldsInfo,0,true)?>;
			<?php echo $instanceName?>.fieldTypesInfo=<?php echo $this->phpParamToJs($this->_fieldTypesInfo,0,true)?>;
			<?php echo $instanceName?>.displayMethod=<?php echo $this->phpParamToJs($this->_displayMethod,0,true)?>;
			<?php echo $instanceName?>.displayMethodOptions=<?php echo $this->phpParamToJs($this->_displayMethodOptions,0,true)?>;
			<?php echo $instanceName?>.defaultStylesEnabled=<?php echo $this->phpParamToJs($this->_defaultStylesEnabled,0,true)?>;
			<?php echo $instanceName?>.displayModesInfo=<?php echo $this->phpParamToJs($this->_displayModesInfo,0,true)?>;
			<?php echo $instanceName?>.messagesValue=<?php echo $this->phpParamToJs($this->_messagesValue,0,true)?>;
			<?php echo $instanceName?>.messagesCode=<?php echo $this->phpParamToJs($this->_messagesCode,0,true)?>;
			<?php if ($this->_prepareOnPrint==true) {?>
				<?php echo $instanceName?>.prepare();
			<?php } else {?>
				<?php echo $instanceName?>.prepareOnLoad();
			<?php }?>
		// ]]>
		</script>
		<?php
	}
	
	/**
	* disable/enable verification of specific field on the fly in javascript mode
	*/
	function jsfChangeFieldVerificationDisabled($name,$value) {
		echo $this->_jsInstanceName.'.changeFieldVerificationDisabled('.$this->phpParamToJs($name).','.$this->phpParamToJs($value).')';
	}
	
	/**
	* assign a javascript function for handling a command
	*/
	function jsfAddCommandHandler($cmd,$handler,$parameters=null) {
		echo $this->_jsInstanceName.'.addCommandHandler('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}
	
	/**
	* assign a javascript function for handling a command
	*/	
	function jsfPrependCommandHandler($cmd,$handler,$parameters=null) {
		echo $this->_jsInstanceName.'.prependCommandHandler('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($handler).')';
	}

	/**
	* Run a javascript command
	*/
	function jsfRunCommand($cmd,$params=array()) {
		echo $this->_jsInstanceName.'.runCommand('.$this->phpParamToJs($cmd).','.$this->phpParamToJs($params).')';
	}
	
	/**
	* Disable/enable verification of specific field on the fly in php mode
	*/
	function changeFieldVerificationDisabled($name,$value) {
		$this->_fieldsInfo[$name]=$value;
	}

	/**
	* @param string|object //form name or instance of a form tag
	*/
	function printJsHookFormFields($form) {?>
		<script type="text/javascript">
			<?php echo $this->_jsInstanceName?>.hookFormFields(<?php echo $form?>);
		</script>
		<?php
	}

	
	/**
	* include require javascript classes
	*/
	function printJsClass($options=array()) {
		$htmlTags='
		<script src="'.$this->_packageFolderPathBrowser.'/validationV1.class.inc.js" type="text/javascript"></script>
		';
		
		if (!isset($options['alreadyIncluded'])) {
			$options['alreadyIncluded']=array();
		}
		if (!in_array('jquery.browser',$options['alreadyIncluded'])) {
			//$htmlTags.="\n".'<script src="'.$this->_packageFolderPathBrowser.'/lib/jquery.browser.js" type="text/javascript"></script>';
		}

		
		if (is_object($this->_optimizerObj)) {
			echo $this->_optimizerObj->getTagsOptimizedVersion($htmlTags,array());
		} else {
			if (!AjaxEveryWhereV1JsFilesAreAlreadyIncluded or 1==1) {
				define('AjaxEveryWhereV1JsFilesAreAlreadyIncluded',true);
				echo $htmlTags;
			}
		}
	}
}