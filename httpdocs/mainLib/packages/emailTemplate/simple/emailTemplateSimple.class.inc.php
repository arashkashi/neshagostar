<?php
define('CMF_EmailTemplate_Ok',true);
define('CMF_EmailTemplate_Error',2);
define('CMF_EmailTemplate_Template_Does_No_Exsists',3);

/**
* @version $Id: emailTemplateSimple.class.inc.php 351 2009-09-07 07:29:58Z salek $
* 
* @todo 1.bodyHtml and BodyText instead of only body for supporting text & html mode.
*/

class cmfcEmailTemplateSimple {
	var $_dynamicSystemStatus=false;
	
	var $_tableName='email_templates';
	var $_colnId='id';
	var $_colnInternalName='internal_name';
	var $_colnName='name';
	var $_colnSubject='subject';
	var $_colnInlineSubject='inline_subject';
	var $_colnBody='body';
	var $_colnInsertDateTime='insert_datetime';
	var $_colnUpdateDateTime='update_datetime';

	var $cvId='id';
	var $cvInternalName='internal_name';
	var $cvName='name';
	var $cvInlineSubject='';
	var $cvSubject='';
	var $cvBody='';
	/**
	* @desc main format of all out going emails
	*/
	var $_bodyMainFormat='';
	var $_subjectMainFormat='';
	var $_subjectMainFormatVariableName='%subject%';
	var $_inlineSubjectMainFormatVariableName='%inline_subject%';
	var $_bodytMainFormatVariableName='%body%';
	var $_baseTemplateInternalName='';
	
	var $_subject;
	var $_body;
	
	var $_defaultError=CMF_EmailTemplate_Error;
	var $_messagesValue=array(
        CMF_EmailTemplate_Ok	=> 'no error',
        CMF_EmailTemplate_Error	=> 'unkown error',
        CMF_EmailTemplate_Template_Does_No_Exsists	=> 'template "%internalName%" does not exists',
	);
	
	
	
	function cmfcEmailTemplateSimple($configs) {
		$this->setConfigs($configs);
	}
	
	function isDynamicSystemEnabled() {
		return $this->_dynamicSystemEnabled;
	}
	
	
	function setOptions($options) {
		foreach ($options as $name=>$value) {
			$this->setOption($name,$value);
		}
	}
	
	function setOption($name,$value) {
		if ($name=='storage')
			$this->setStorage($value);
		elseif ($name=='storage')
			$this->setLog($value);
		else
			$this->{'_'.$name}=$value;
	}
	
	function getOption($name) {
		return $this->{'_'.$name};
	}
	
	function setConfigs($configs) {
		//$result=parent::setConfigs($configs);
		if (isset($configs['bodyMainFormat'])) $this->setBodyMainFormat($configs['bodyMainFormat']);
		if (isset($configs['subjectMainFormat'])) $this->setSubjectMainFormat($configs['subjectMainFormat']);
		if (isset($configs['subjectMainFormatVariableName'])) $this->setSubjectMainFormatVariableName($configs['subjectMainFormatVariableName']);
		if (isset($configs['bodyMainFormatVariableName'])) $this->setBodyMainFormatVariableName($configs['bodyMainFormatVariableName']);
		if (isset($configs['inlineSubjectMainFormatVariableName'])) $this->setInlineSubjectMainFormatVariableName($configs['inlineSubjectMainFormatVariableName']);
		if (isset($configs['baseTemplateInternalName'])) $this->setBaseTemplateInternalName($configs['baseTemplateInternalName']);
		
		if (isset($configs['columns']['internalName'])) $this->setColnInternalName($configs['columns']['internalName']);
		if (isset($configs['columns']['subject'])) $this->setColnSubject($configs['columns']['subject']);
		if (isset($configs['columns']['inline_subject'])) $this->setColnSubject($configs['columns']['inline_subject']);
		if (isset($configs['columns']['body'])) $this->setColnBody($configs['columns']['body']);
		if (isset($configs['columns']['name'])) $this->setColnBody($configs['columns']['name']);
		//return $result;
	}
	
	function setBodyMainFormat($value) {
		$this->_bodyMainFormat=$value;
	}
	
	function setBaseTemplateInternalName($value) {
		$this->_baseTemplateInternalName=$value;
	}

	function setSubjectMainFormat($value) {
		$this->_subjectMainFormat=$value;
	}
	
	function setSubjectMainFormatVariableName($value) {
		$this->_subjectMainFormatVariableName=$value;
	}
	
	function setBodyMainFormatVariableName($value) {
		$this->_bodyMainFormatVariableName=$value;
	}
	
	function setInlineSubjectMainFormatVariableName($value) {
		$this->_inlineSubjectMainFormatVariableName=$value;
	}
	
	function setColnInternalName($value) {
		$this->_colnInternalName=$value;
	}
	
	function setColnName($value) {
		$this->_colnName=$value;
	}
	
	function setColnSubject($value) {
		$this->_colnSubject=$value;
	}
	
	function setColnInlineSubject($value) {
		$this->_colnInlineSubject=$value;
	}
	
	function setColnBody($value) {
		$this->_colnBody=$value;
	}
	
    function createTable() {
        $sqlQuery="
			CREATE TABLE `%s` (
			  `%s` int(11) NOT NULL auto_increment,
			  `%s` varchar(255) default NULL,
			  `%s` text,
			  `%s` text,
			  `%s` text,
			  `%s` text,
			  `%s` datetime default NULL,
			  `%s` datetime default NULL,
			  PRIMARY KEY  (`%s`),
			  UNIQUE KEY `%s` (`%s`)
			) ENGINE=MyISAM";
		$sqlQuery=sprintf($sqlQuery,
			$this->_tableName,
			$this->_colnId,
			$this->_colnInternalName,
			$this->_colnName,
			$this->_colnSubject,
			$this->_colnInlineSubject,
			$this->_colnBody,
			$this->_colnInsertDateTime,
			$this->_colnUpdateDateTime,
			$this->_colnId,
			$this->_colnInternalName,
			$this->_colnInternalName
		);
		return cmfcMySql::exec($sqlQuery);
	}
	
	
	function columnsValuesToProperties($columnsValues,$exceptNulls=false) {
		if (is_array($columnsValues)) {
			if ($this->isDynamicSystemEnabled()) {
				trigger_error("Dynamic mode is still beta version and has some bugs so don't use it or fix it",E_USER_ERROR);
			} else {
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnId])))
					@$this->cvId=$columnsValues[$this->_colnId];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnInsertDateTime])))
					@$this->cvInsertDateTime=$columnsValues[$this->_colnInsertDateTime];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnUpdateDateTime])))
					@$this->cvUpdateDateTime=$columnsValues[$this->_colnUpdateDateTime];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnInternalName])))
					@$this->cvInternalName=$columnsValues[$this->_colnInternalName];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnSubject])))
					@$this->cvSubject=$columnsValues[$this->_colnSubject];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnInlineSubject])))
					@$this->cvInlineSubject=$columnsValues[$this->_colnInlineSubject];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnBody])))
					@$this->cvBody=$columnsValues[$this->_colnBody];
				if ($exceptNulls==false or ($exceptNulls and !is_null($columnsValues[$this->_colnName])))
					@$this->cvName=$columnsValues[$this->_colnName];
			}
			return true;
		}
		return false;
	}

	function propertiesToColumnsValues($exceptNulls=false) {
		$columnsValues=array();

		if ($this->isDynamicSystemEnabled()) {
			trigger_error("Dynamic mode is still beta version and has some bugs so don't use it or fix it",E_USER_ERROR);
		} else {
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvId)))
				$columnsValues[$this->_colnId]=$this->cvId;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvInsertDateTime)))
				$columnsValues[$this->_colnInsertDateTime]=$this->cvInsertDateTime;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvUpdateDateTime)))
				$columnsValues[$this->_colnUpdateDateTime]=$this->cvUpdateDateTime;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvInternalName)))
				$columnsValues[$this->_colnInternalName]=$this->cvInternalName;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvName)))
				$columnsValues[$this->_colnName]=$this->cvName;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvSubject)))
				$columnsValues[$this->_colnSubject]=$this->cvSubject;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvInlineSubject)))
				$columnsValues[$this->_colnInlineSubject]=$this->cvInlineSubject;
			if ($exceptNulls==false or ($exceptNulls and !is_null($this->cvBody)))
				$columnsValues[$this->_colnBody]=$this->cvBody;
		}

		return $columnsValues;
	}

	function clearColumnsProperties() {
		if ($this->isDynamicSystemEnabled()) {
			return $this->clearProperties($this->columnNameProperyNamePrefix);
		} else {
			$this->cvId=null;
			$this->cvInsertDateTime=null;
			$this->cvUpdateDateTime=null;
			$this->cvInternalName=null;
			$this->cvName=null;
			$this->cvSubject=null;
			$this->cvInlineSubject=null;
			$this->cvBody=null;
			return true;
		}
	}
	
	function load($keyColumnValue=null,$keyColumnName=null) {
		if (is_null($keyColumnName)) {$keyColumnName=$this->_colnId;}
		$this->clearColumnsProperties();

		$row=cmfcMySql::load($this->_tableName,$keyColumnName,$keyColumnValue);
		
		if ($row!==false) {
			$this->columnsValuesToProperties($row);
			return true;
		} else {
			return false;
		}
	}
	
	function loadByInternalName($value) {
		return $this->load($value,$this->_colnInternalName);
	}
	
    function replaceVariables($text,$replacements)
    {
        foreach ($replacements as $needle=>$replacement) {
        	$text=str_replace($needle,$replacement,$text);
        }
        return $text;
    }

	function getSubject() {
		return $this->_subject;
	}
	
	function getBody() {
		return $this->_body;
	}
	
	function process($replacements) {
		//--(Begin)--> load main format
		$emailTemplate=clone($this);
		if (!empty($this->_baseTemplateInternalName))
		if ($emailTemplate->loadByInternalName($this->_baseTemplateInternalName)) {
			$this->_subjectMainFormat=$emailTemplate->cvSubject;
			$this->_bodyMainFormat=$emailTemplate->cvBody;
		}
		//--(End)--> load main format
		//--(Begin)--> merging with main format
		if ($this->_baseTemplateInternalName!=$this->cvInternalName) {
			if (!empty($this->_subjectMainFormat))
				$this->_subject=$this->replaceVariables($this->_subjectMainFormat,array($this->_subjectMainFormatVariableName=>$this->cvSubject));
			if (!empty($this->_bodyMainFormat))
				$this->_body=$this->replaceVariables($this->_bodyMainFormat,array($this->_bodyMainFormatVariableName=>$this->cvBody));
		} else {
			$this->_subject=$this->_subjectMainFormat;
			$this->_body=$this->_bodyMainFormat;
		}
		//--(End)--> merging with main format
		if (!isset($replacements[$this->_inlineSubjectMainFormatVariableName]))	{
			$this->cvInlineSubject=$this->replaceVariables($this->cvInlineSubject,$replacements);
			$replacements[$this->_inlineSubjectMainFormatVariableName]=$this->cvInlineSubject;
		}
		$this->_subject=$this->replaceVariables($this->_subject,$replacements);
		$this->_body=$this->replaceVariables($this->_body,$replacements);
	}
	
	function onCommand(&$commander,$command,$params) {
		if (strtolower(get_class($commander)==strtolower('cmfcUserSystemBeta'))) {
			
		}
	}
	
	
	/*
		return $this->raiseError(null,CMF_Language_Error_Unknown_Short_Name,
							PEAR_ERROR_RETURN,NULL, 
							array('shortName'=>$shortName));

	*/
}