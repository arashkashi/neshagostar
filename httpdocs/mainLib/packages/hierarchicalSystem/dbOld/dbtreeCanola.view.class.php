<?php

require_once(dirname(__FILE__).'/dbtree.view.class.php');

define('Tree_View_Structure_Is_Corrupted',20);

class cDbTreeViewCanola extends cmfcDbTreeView {
	var $_prefix='myTree';
	var $_mainId='myTreeRoot';
	var $_nodeHtmlTemplates;
	var $_columnsNames;
	var $_titleColumnName;
	var $_displayMode;
	/**
	* @desc more details about displaying tree such as which functionalities
	* 		should be availble in edit mode.
	* @exanple array('edit'=>'false','delete'=>'false','mode'=>true)
	*/
	var $_displayModeDetails;
	var $_images=array();
	var $_selectedRows;
	var $_showStartNode=false;
	
	var $_jsFunctionOnSelectRow;
	var $_jsFunctionOnSelectRows;
	
	var $_cbFuncManipulateRowColumnsBeforeDraw;
	var $_cbFuncGetAdditionalItems;
	
	//require in getNodeNestedPositionByRow function
	var $_prevRow;
	var $_autoCollapseAtStart=true;
	var $_startNodeId=null;
	var $_debugModeEnabled=false;
	
	/*
		TreeView has some inline templates which will append some require 
		html to user template , sometimes user wants to make a very different template
		from scrach, in this case he show change this property. 
		availabe modes
		onlyCustom,
		onlyDefault,
		mergeCustomAndDefault,
		mergeCustomAndDefaultNecessaryParts,
		mergeCustomAndDefaultInvisibleParts
	*/
	var $_templateMode='mergeCustomAndDefault';
	
	/**
	* @desc with this to properties it's possible to limit the tree
	* 		to specific depth.
	*/
	var $_displayFromLevel=null;
	var $_displayToLevel=null;
	
	function errorMessages($errorNo) {
		$messages=array(
			Tree_View_Structure_Is_Corrupted => "Tree structure has problem (probably corrupted, you may use rebuild method)!",
		);
		
		return $messages[$errorNo];
	}
	
	function setAutoCollapseAtStart($autoCollapseAtStart) {
		$this->_autoCollapseAtStart=$autoCollapseAtStart;
	}
	
	function setTemplateMode($templateMode) {
		$modes=array(
			'onlyCustom','onlyDefault',
			'mergeCustomAndDefault','mergeCustomAndDefaultNecessaryParts','mergeCustomAndDefaultInvisibleParts'
		);
		if (!in_array($templateMode,$modes)) {
			trigger_error("`$templateMode` is not a valid template mode (CTreeView), available modes are : ".implode(' , ',$modes),E_USER_ERROR);
		}
		
		$this->_templateMode=$templateMode;
	}
	
	function setCbFuncManipulateRowColumnsBeforeDraw($name) {
		$this->_cbFuncManipulateRowColumnsBeforeDraw=$name;
	}
	
	function setCbFuncGetAdditionalItems($name) {
		$this->_cbFuncGetAdditionalItems=$name;
	}
	
	function setDebugModeEnabled($active) {
		$this->_debugModeEnabled=$active;
	}
	
	function setStartNodeId($startNodeId) {
		$this->_startNodeId=$startNodeId;
	}
	
	function setTitleColumnName($name) {
		$this->_titleColumnName=$name;
	}
	
	function setDisplayMode($mode) {
		$this->_displayMode=$mode;
	}
	
	function setDisplayModeDetails($mode) {
		$this->_displayModeDetails=$mode;
	}
	
	
	function setColumnsNames($columnsNames) {
		if (!in_array($this->colnParentId,$columnsNames))
			$columnsNames[]=$this->colnParentId;
		$this->_columnsNames=$columnsNames;
	}
	
	function setMainId($mainId) {
		$this->_mainId=$mainId;
	}
	
	function setShowStartNode($value) {
		$this->_showStartNode=$value;
	}
	
	function setPrefix($prefix) {
		$this->_prefix=$prefix;
	}
	
	function setJsFunctionOnSelectRow($functionName) {
		$this->_jsFunctionOnSelectRow=$functionName;
	}
	
	function setJsFunctionOnSelectRows($functionName) {
		$this->_jsFunctionOnSelectRows=$functionName;
	}
	
	function setSelectedRows($selectedRows) {
		$this->_selectedRows=$selectedRows;
	}
	
	function setImages($images) {
		$this->_images=$images;
	}
	
	function setDisplayFromLevel($value) {
		$this->_displayFromLevel=$value;
	}
	
	function setDisplayToLevel($value) {
		$this->_displayToLevel=$value;
	}
	
	function setTemplates($nodeHtmlTemplates=null,$displayMode=null) {
		if (is_null($displayMode)) $displayMode=$this->_displayMode;
		
		#--(Begin)-->Defining Buttons Indicators
		$editSaveIndicator='...';
		if (!empty($this->_images['editIcon']['path']))
			$editSaveIndicator='<img id="%{item_base_name}%[edit_save_icon]" src="'.$this->_images['editIcon']['path'].'" alt="edit/save" border="0"/>';
		if (!empty($this->_images['deleteIcon']['path']))
			$deleteIndicator='<img id="%{item_base_name}%[delete_icon]" src="'.$this->_images['deleteIcon']['path'].'" alt="delete" border="0"/>';
		else $deleteIndicator='[X]';
		if (!empty($this->_images['addIcon']['path']))
			$addIndicator='<img id="%{item_base_name}%[add_icon]" src="'.$this->_images['addIcon']['path'].'" alt="add" border="0"/>';
		else $addIndicator='+';	
		if (!empty($this->_images['moveUpIcon']['path']))
			$moveUpIndicator='<img id="%{item_base_name}%[move_up]" src="'.$this->_images['moveUpIcon']['path'].'" alt="Move Up" border="0"/>';
		else $moveUpIndicator='U';	
		if (!empty($this->_images['moveDownIcon']['path']))
			$moveDownIndicator='<img id="%{item_base_name}%[move_down]" src="'.$this->_images['moveDownIcon']['path'].'" alt="Move Down" border="0"/>';
		else $moveDownIndicator='D';

		if (!empty($this->_images['openIcon']['path'])) {
			$openIndicator=sprintf('<img id="%s" src="%s" style="%s" alt="Open" border="0"/>',
				'%{item_base_name}%[openCloseIcon]',
				$this->_images['openIcon']['path'],
				$this->_images['openIcon']['style']
			);
		} else $openIndicator='[+]';
		$this->_openIndicator=$openIndicator.'<!--Open-->';
		if (!empty($this->_images['closeIcon']['path'])) {
			$closeIndicator=sprintf('<img id="%s" src="%s" style="%s" alt="Close" border="0"/>',
				'%{item_base_name}%[openCloseIcon]',
				$this->_images['closeIcon']['path'],
				$this->_images['closeIcon']['style']
			);
		} else $closeIndicator='[-]';
		$this->_closeIndicator=$closeIndicator.'<!--Close-->';
		#--(End)-->Defining Buttons Indicators
	
		if ($displayMode=='singleSelect' or $displayMode=='multiSelect') {
			if ($displayMode=='singleSelect') {
				$selectControl='<input style="margin-bottom:0px" name="%{item_base_name}%[selectedRow][id]" id="%{item_base_name}%[selected]" type="radio" onchange="%{prefix}%SelectRow(this)" value="%{item_number}%" %checked%>&nbsp;';
			} elseif($displayMode=='multiSelect') {
				$selectControl='<input style="margin-bottom:0px" name="%{item_base_name}%[selected]" id="%{item_base_name}%[selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" value="true" %checked%>&nbsp;';
			}

			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				<input name="%{item_base_name}%[id]" id="%{item_base_name}%[id]" type="hidden" value="%column_id_value%"/>
				<input name="%{item_base_name}%[parent_id]" id="%{item_base_name}%[parent_id]" type="hidden" value="%column_parent_id_value%"/>
				<input name="%{item_base_name}%[flag]" id="%{item_base_name}%[flag]" type="hidden" value="0"/>
				&raquo;
				<a href="?" id="%{item_base_name}%[openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[container]">
					$selectControl
					<span id="%{item_base_name}%[title]">%title%</span>
				</span>
EOT;

		} elseif ($displayMode=='edit') {
			$customHtml=$nodeHtmlTemplates['treeNodeEditBox'];
			$__pname='%column_'.$this->colnParentId.'_value%';
			$this->_nodeHtmlTemplates['treeNodeEditBox']=<<<EOT
			 	سر شاخه : <input style="width:150px;direction:rtl" name="%{item_base_name}%[columns][$this->colnParentId]" id="%{item_base_name}%[columns][$this->colnParentId]" type="hidden" value="$__pname" size="20"/>
				<input id="%{prefix}%[selectNodeNewParent]" onclick="%{prefix}%OnSelectingNodeNewParent(this)"  name="%{prefix}%[selectNodeNewParent]"  type="radio" value="%{item_number}%" style="margin-bottom:0px">&nbsp;
				<!--<input style="width:150px;direction:rtl" name="%{item_base_name}%[columns][name_fa]" id="%{item_base_name}%[columns][name_fa]" type="text" value="%column_name_fa_value%" size="50"/>-->
				$customHtml
EOT;

			$customHtml=$this->nodeHtmlTemplates['treeNodeBox'];
			//default columns
			foreach($this->_columnsNames as $columnName) {
				$defaultColumnsFields.='<input id="%{item_base_name}%[columns_default]['.$columnName.']" type="hidden" value="%column_'.$columnName.'_value%"/>'."\n";
			}
			
			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				<input name="%{item_base_name}%[id]" id="%{item_base_name}%[id]" type="hidden" value="%column_id_value%"/>
				<input name="%{item_base_name}%[parent_id]" id="%{item_base_name}%[parent_id]" type="hidden" value="%column_parent_id_value%"/>
				<input name="%{item_base_name}%[flag]" id="%{item_base_name}%[flag]" type="hidden" value="0"/>
				&raquo;
				<a href="?" id="%{item_base_name}%[openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[container]">
					<!--<input style="margin-bottom:0px" name="%{item_base_name}%[selected]" id="%{item_base_name}%[selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" %checked% value="true">&nbsp;-->
					<input style="margin-bottom:0px;display:none" name="%{prefix}%NodeNewParent" type="radio" onclick="return %{prefix}%OnSelectNodeNewParent(this)" value="%{item_number}%">
					<a href="javascript:void(0)" id="%{item_base_name}%[add]" onclick="%{prefix}%AddNewTreeItem('%column_id_value%','%{item_base_name}%[item]','%{item_base_name}%','%{prefix}%'); return false;" class="%{prefix}%TreeItem" title="Add new item" style="vertical-align:center">$addIndicator</a>
					<a href="javascript:void(0)" id="%{item_base_name}%[delete]" onclick="return %{prefix}%DeleteNode('%{item_number}%');">$deleteIndicator</a>
					<a href="javascript:void(0)" id="%{item_base_name}%[editSave]" onclick="%{prefix}%TriggerSave('%{item_number}%','%{item_base_name}%[moreFields]','%{item_base_name}%[title]','%{item_base_name}%[columns][$this->_titleColumnName]','%{item_base_name}%[edit_save_icon]');return false;" style="vertical-align:center" title="Edit Save">$editSaveIndicator</a>
					<a href="javascript:void(0)" id="%{item_base_name}%[moveUp]" onclick="return %{prefix}%MoveNode('%{item_number}%','up');" title="Move Up">$moveUpIndicator</a>
					<a href="javascript:void(0)" id="%{item_base_name}%[moveDown]" onclick="return %{prefix}%MoveNode('%{item_number}%','down');" title="Move Down">$moveDownIndicator</a>
					$defaultColumnsFields
					<span id="%{item_base_name}%[title]">%title%</span>
					<div id="%{item_base_name}%[moreFields]" style="display:none" class="%{prefix}%EditBox">
						$customHtml
					</div>
				</span>
EOT;
		} elseif ($displayMode=='viewSimple') {
			//$selectControl='<input style="margin-bottom:0px" name="%{item_base_name}%[selected]" id="%{item_base_name}%[selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" value="true" %checked%>&nbsp;';
			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				<a href="%title_link_url%">$selectControl%title%</a>
EOT;
		} else {
			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				&raquo;
				<a href="?" id="%{item_base_name}%[openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[container]">
					<a href="%title_link_url%"><span id="%{item_base_name}%[title]">%title%</span></a>
					$nodeHtmlTemplates[treeNodeBox]
				</span>
EOT;
		}
		
		if ($this->_templateMode=='onlyCustom') {
			$this->_nodeHtmlTemplates=$nodeHtmlTemplates;
		}

		return true;
	}
	
	function _useTemplate($row,$hasChild) {
		$nodeHtmlTemplate=$this->_nodeHtmlTemplates['treeNodeBox'];
		$itemNumber=$row[$this->colnId];
		
		$openCloseIndicator="";
		if ($hasChild) {
			if ($this->_autoCollapseAtStart) {
				$openCloseIndicator=$this->_openIndicator;
			} else {
				$openCloseIndicator=$this->_closeIndicator;
			}
		}
		
		if (is_array($this->_selectedRows))
			if  (in_array($row[$this->colnId],$this->_selectedRows)) {
				$rowSelected='checked';
			}
		
		$titleLinkUrl='javascript:void(0)';
		if (!empty($row[$this->colnLink]))
			$titleLinkUrl=$row[$this->colnLink];
		
		#--(Begin)-->use template
		$holders=array(  
			"%title%",
			"%{prefix}%",
			"%openCloseIndicator%",
			"%{item_base_name}%",
			"%{item_number}%",
			"%checked%",
			"%column_parent_id_value%",
			"%column_id_value%",
			"%column_name_value%",
			"%title_link_url%"
		);
		
		$replacements=array(
			$row[$this->_titleColumnName],
			$this->_prefix,
			$openCloseIndicator,
			$this->_prefix.'Row['.$itemNumber.']',
			$itemNumber,
			$rowSelected,
			$row[$this->colnParentId],
			$row[$this->colnId],
			$row[$this->colnName],
			$titleLinkUrl
		);
		
		//default columns
		foreach($this->_columnsNames as $columnName) {
			$holders[]="%column_$columnName"."_value%";
			$replacements[]=$row[$columnName];
		}
		
		$itemHtml=str_replace($holders,$replacements,$nodeHtmlTemplate);
		/*
		show_custom_row('گواهی نامه ها '.'<a href="javascript:glAddNewBox(\'certificate_images\',\'template_certificate_image_box\',\'certificate_image\')">+</a>',
			'<div id="certificate_images">'.$items_html.'</div>');	*/
		#--(End)-->use template
		
		return $itemHtml;
	}
	
	function printJavaScripts() {?>
		<? if ($this->_displayMode!='viewSimple') {?>
		<script language="javascript" type="text/javascript">
		//general scripts
		function <?=$this->_prefix?>GetElements(doc_obj) 
		{
			if (doc_obj==null) {doc_obj=document;}
			var all = doc_obj.all ? doc_obj.all :
					doc_obj.getElementsByTagName('*');
			var elements = new Array();
			for (var e = 0; e < all.length; e++)
					elements[elements.length] = all[e];
			return elements;
		}
		
		function <?=$this->_prefix?>GetElementsByIdPattern(regexp,doc_obj) 
		{
			if (doc_obj==null) {doc_obj=document;}
			var all = doc_obj.all ? doc_obj.all :
					doc_obj.getElementsByTagName('*');
			var elements = new Array();
			var str;
			var elm;
			for (var e = 0; e < all.length; e++) {
				elm=all[e];
				if (elm.id) {
					var match=regexp.exec(elm.id);
					if (match!=null) {
						elements[elements.length] = elm;
					}
				}
			}
			
			return elements;
		}
		
		
		function <?=$this->_prefix?>Replace(s, t, u) {
		  /*
		  **  Replace a token in a string
		  **    s  string to be processed
		  **    t  token to be found and removed
		  **    u  token to be inserted
		  **  returns new String
		  */
		  i = s.indexOf(t);
		  r = "";
		  if (i == -1) return s;
		  r += s.substring(0,i) + u;
		  if ( i + t.length < s.length)
			r += <?=$this->_prefix?>Replace(s.substring(i + t.length, s.length), t, u);
		  return r;
		}
		
		function <?=$this->_prefix?>GetElementsByName(doc_obj) 
		{
			if (doc_obj==null) {doc_obj=document;}
			var all = doc_obj.all ? doc_obj.all :
					doc_obj.getElementsByTagName('*');
			var elements = new Array();
			for (var e = 0; e < all.length; e++)
			{
				elements[all[e].name] = all[e];
			}
			return elements;
		}
		
		function <?=$this->_prefix?>ToggleBullet(elm) {
			if (<?=$this->_prefix?>SimpleSlider) {
				
			} else {
				<?=$this->_prefix?>ToggleOpenCloseIndicator(elm);
				var newDisplay = "none";
				var e = elm.nextSibling;
				while (e != null) {
					if (e.tagName == "UL" || e.tagName == "ul") {
						if (e.style.display == "none") newDisplay = "block";
						break;
					}
					e = e.nextSibling;
				}
				while (e != null) {
					if (e.tagName == "UL" || e.tagName == "ul") e.style.display = newDisplay;
					e = e.nextSibling;
				}
			}
		}
		
		function <?=$this->_prefix?>CollapseAll(id) {
			if (<?=$this->_prefix?>SimpleSlider) {
				if (id=='') id='root';
				var e = document.getElementById(id);
				
				var lists = e.getElementsByTagName('UL');
				for (var j = 0; j < lists.length; j++) 
					lists[j].style.height = "0px";
				lists = e.getElementsByTagName('ul');
				for (var j = 0; j < lists.length; j++) 
					lists[j].style.height = "0px";
				
				e.style.display = "block";
			} else {
				if (id=='') id='root';
				var e = document.getElementById(id);
				
				var lists = e.getElementsByTagName('UL');
				for (var j = 0; j < lists.length; j++) 
					lists[j].style.display = "none";
				lists = e.getElementsByTagName('ul');
				for (var j = 0; j < lists.length; j++) 
					lists[j].style.display = "none";
				
				e.style.display = "block";
			}
		}
		
		function  <?=$this->_prefix?>ShowHideElement(id) {
			var elm=document.getElementById(id);
			if (elm.style.display=='none')
				elm.style.display='';
			else
				elm.style.display='none';
		}
		
		function <?=$this->_prefix?>ConfimationMessage(message) {
			if(confirm(message)) { return true; }
			else {return false;}
		}
		</script>
		<? }?>
		
		<? if ($this->_displayMode!='viewSimple' and $this->_displayMode!='view') {?>
		<script language="javascript" type="text/javascript">
		//special scripts
		function <?=$this->_prefix?>AddChanges(action,columnsValues,prevColumnsValues) {
			var changesContainer=document.getElementById('<?=$this->_prefix?>ChangesContainer');
			var changesBoard=document.getElementById('<?=$this->_prefix?>ChangesBoard');
			var html='';
			var value;
			var date = new Date();
			if (!prevColumnsValues)
				prevColumnsValues=new Array();
			var index=String(date.getFullYear())+'-'+date.getDate()+'-'+date.getMonth()+' '+date.getHours()+':'+String(date.getMinutes())+':'+date.getSeconds();
			for (columnName in columnsValues) {
				value=columnsValues[columnName];
				html=html+'<input type="hidden" name="<?=$this->_prefix?>Changes['+index+'][columns]['+columnName+']" value="'+value+'"/>';
				if (prevColumnsValues[columnName]) {
					prevValue=prevColumnsValues[columnName];
					html=html+'<input type="hidden" name="<?=$this->_prefix?>Changes['+index+'][previous_columns]['+columnName+']" value="'+prevValue+'"/>';
				}
			}
			html=html+'<input name="<?=$this->_prefix?>Changes['+index+'][action]" type="hidden" value="'+action+'"/>';
//			changesContainer.style.display='none';
			changesContainer.innerHTML=changesContainer.innerHTML+html+"\n";
			var more='';
			if (columnsValues['<?=$this->colnParentId?>']!=prevColumnsValues['<?=$this->colnParentId?>'] && columnsValues['<?=$this->colnParentId?>']) 
				more='(Moved from '+prevColumnsValues['<?=$this->colnParentId?>']+' to '+columnsValues['<?=$this->colnParentId?>']+')';
			if (changesBoard)
				changesBoard.innerHTML+=index+' : Node '+columnsValues['id']+' changed '+more+' <br/>';
		}
		
		function <?=$this->_prefix?>ShowEditBox(itemNumber,containerId) {
			var prefix='<?=$this->_prefix?>';
			var rowName=prefix+'Row['+itemNumber+']';
			var defaultFields;
			var hiddenField;

			var replacements=new Array();
			replacements['%{item_base_name}%']=rowName;
			replacements['%{prefix}%']=prefix;
			replacements['%{item_number}%']=itemNumber;
			replacements['%column_<?=$this->colnParentId?>_value%']=document.getElementById(rowName+'[<?=$this->colnParentId?>]').value;

			var myregexp = new RegExp("^[^\\[\\]]*\\["+itemNumber+"]\\[columns_default]\\[([^\\[\\]]*)\\]", "i");
			defaultFields=<?=$this->_prefix?>GetElementsByIdPattern(myregexp,null);

			for (defaultFieldI in defaultFields) {
				defaultField=defaultFields[defaultFieldI];
				columnName=<?=$this->_prefix?>GetColumnNameById(defaultField.id);
				//alert(columnName);
				if (defaultField && columnName!='<?=$this->colnParentId?>')
					replacements['%column_'+columnName+'_value%']=defaultField.value;
			}
			
			var customHtml=<?=$this->_prefix?>ParseTemplate('','<?=$this->_prefix?>treeNodeEditBox',replacements);
			document.getElementById(containerId).innerHTML=customHtml;
		}
		
		function <?=$this->_prefix?>GetColumnNameById(id) {
			var myregexp = /^[^\[\]]*\[[^\[\]]*\]\[(columns|columns_default)]\[([^\[\]]*)\]/i;
			var match = myregexp.exec(id);
			if (match != null) {
				result = match[2];
			} else {
				result = "";
			}
			return result;
		}
		
		function <?=$this->_prefix?>HideEditBox(itemNumber,containerId) {
			var prefix='<?=$this->_prefix?>';
			var rowName=prefix+'Row['+itemNumber+']';
			var columnsValues=new Array()
			var prevColumnsValues=new Array()
			var hiddenField;
			var field;
			var total=0;
			var fields;
			var columnName;
			
			var myregexp = new RegExp("^[^\\[\\]]*\\["+itemNumber+"]\\[columns]\\[([^\\[\\]]*)\\]", "i");
			fields=<?=$this->_prefix?>GetElementsByIdPattern(myregexp,null);
			
			for (fieldI in fields) {
				field=fields[fieldI];
				columnName=<?=$this->_prefix?>GetColumnNameById(field.id);
				hiddenField=document.getElementById(rowName+'[columns_default]['+columnName+']');

				//alert(field.id+'|'+hiddenField.id);
				if (hiddenField && field)
					if (hiddenField.value!=field.value) {
						total++;
						prevColumnsValues[columnName]=hiddenField.value;
						columnsValues[columnName]=field.value;
						hiddenField.value=field.value;
					}
			}
			
			if (total>0) {
				columnsValues['id']=itemNumber;
				prevColumnsValues['id']=itemNumber;

				var action='none';
				//if (itemNumber.match(/new.*/)) {
				//	action='insert';
				//}
				//if (columnsValues['<?=$this->colnParentId?>']!=prevColumnsValues['<?=$this->colnParentId?>'] && columnsValues['<?=$this->colnParentId?>']) {
				//}
				<?=$this->_prefix?>AddChanges(action,columnsValues,prevColumnsValues);
			}
			
			//--(Begin)-->Move Node
			if (prevColumnsValues['<?=$this->colnParentId?>']!=columnsValues['<?=$this->colnParentId?>']) {
				if (columnsValues['<?=$this->colnParentId?>']=='')
					var newParentId='<?=$this->_mainId?>';
				else
					var newParentId=prefix+'Row['+columnsValues['<?=$this->colnParentId?>']+']'+'[item]';
				var newParent=document.getElementById(newParentId);
				var openCloseButton=document.getElementById(prefix+'Row['+columnsValues['<?=$this->colnParentId?>']+']'+'[openCloseButton]');

				var child=document.getElementById(rowName+'[item]');
				//alert('child :'+child.id+' parent : '+newParent.id);
				if (newParent && child) {
					if (newParentId=='<?=$this->_mainId?>')
						myUl=newParent;
					else {
						myUl=newParent.getElementsByTagName('ul');
						if (myUl[0]) {
							myUl=myUl[0];
						} else {
							var myUl=document.createElement('ul');
							newParent.appendChild(myUl);
							if (openCloseButton)
								openCloseButton.innerHTML='<?=$this->_closeIndicator?>';
						}
					}
					myUl.insertBefore(child,myUl.firstChild);
				}
			}
			//--(End)-->Move Node
			
			document.getElementById(containerId).innerHTML='';
		}
	
		function <?=$this->_prefix?>TriggerSave(itemNumber,id,titleId,valueId,imageIndicatorId) {
			var elm=document.getElementById(id);
			var imageIndicator=document.getElementById(imageIndicatorId);
			if (elm.style.display=='none') {
				elm.style.display='';
				if (imageIndicator)
					imageIndicator.src="<?=$this->_images['saveIcon']['path']?>";
				<?=$this->_prefix?>ShowEditBox(itemNumber,id);
			} else {
				if (imageIndicator)
					imageIndicator.src="<?=$this->_images['editIcon']['path']?>";
				elm.style.display='none';
				document.getElementById(titleId).innerHTML=document.getElementById(valueId).value;
				<?=$this->_prefix?>HideEditBox(itemNumber,id);
			}
		}
		
		function <?=$this->_prefix?>ToggleOpenCloseIndicator(elm) {
			//elm.innerHTML=<?=$this->_prefix?>Replace(elm.innerHTML,' ','');
			if (elm.innerHTML.indexOf('--Open--')>0 || elm.innerHTML.indexOf('--open--')>0)
				elm.innerHTML='<?=stripcslashes($this->_closeIndicator)?>';
			else
				elm.innerHTML='<?=stripcslashes($this->_openIndicator)?>'
		}
		
		/* Not Tested */
		function <?=$this->_prefix?>HighlightParents(elm)
		{
			$hightlight=false;
			if (elm.checked) {$hightlight=true;}
			var idName=<?=$this->_prefix?>Replace(elm.id,'[selected]','');
			var parentId=document.getElementById(idName+'[<?=$this->colnParentId?>]').value;
			var currElm=elm;
			var selectedTreeItemsStr='';
		
			while (parentId!='' & parentId!='1') {
				mainId='row['+parentId+']';
				currElm=document.getElementById(mainId);
				if ($hightlight) {color='red';} else {color='';}
				if ($hightlight)
					document.getElementById(mainId+'[flag]').value++;
				else 
					document.getElementById(mainId+'[flag]').value--;
				if (document.getElementById(mainId+'[flag]').value>0)
					document.getElementById(mainId).className='<?=$this->_prefix?>HighlightTreeItem';
				else 
					document.getElementById(mainId).className='<?=$this->_prefix?>TreeItem';
				id=mainId+'[<?=$this->colnParentId?>]';
				parentId=document.getElementById(id).value;
				
				var parentElement=document.getElementById('row'+parentId);
				if (typeof(parentElement)!='object')
				{
					parentId='';
				}
			}
			
			var selectedTreeItemsId=document.getElementById('<?=$this->_prefix?>SelectedTreeItemsId');
			var selectedTreeItems=document.getElementById('<?=$this->_prefix?>SelectedTreeItems');
		
			var id=<?=$this->_prefix?>Replace(idName,'<?=$this->_prefix?>[','');
			id=<?=$this->_prefix?>Replace(id,']','');
			
			document.getElementById('<?=$this->_prefix?>SelectedTreeItemId').value=id;
			document.getElementById('<?=$this->_prefix?>SelectedTreeItemPath').innerHTML=document.getElementById('<?=$this->_prefix?>Row['+id+'][item]').innerHTML;
			
			if (selectedTreeItemsId.value=='') {selectedTreeItemsId.value=',';}
			if (elm.checked) {
				if (selectedTreeItemsId.value.indexOf(','+id+',')<0)
					{ selectedTreeItemsId.value+=id+','; }
				selectedTreeItems.innerHTML+=document.getElementById(idName).innerHTML+' | ';
			} else {
				selectedTreeItemsId.value=<?=$this->_prefix?>Replace(selectedTreeItemsId.value,<?=$this->_prefix?>Replace(idName,'<?=$this->_prefix?>Row','')+',','');
				selectedTreeItems.innerHTML=<?=$this->_prefix?>Replace(selectedTreeItems.innerHTML,document.getElementById(idName).innerHTML+' | ','');
			}
		}
		
		
		
		/* Not Tested */
		function <?=$this->_prefix?>EmptyTree()
		{
			var lists = <?=$this->_prefix?>GetElements(document.getElementById('root'));
			var element;
			if (document.getElementById('<?=$this->_prefix?>SelectedTreeItems'))
				document.getElementById('<?=$this->_prefix?>SelectedTreeItems').innerHTML='';
			if (document.getElementById('<?=$this->_prefix?>SelectedTreeItemsId'))
				document.getElementById('<?=$this->_prefix?>SelectedTreeItemsId').value='';
			if (document.getElementById('<?=$this->_prefix?>SelectedTreeItemId'))	
				document.getElementById('<?=$this->_prefix?>SelectedTreeItemId').value='';
			for (var j = 0; j < lists.length; j++) {
				element=lists[j];
				if (element.type=='checkbox') lists[j].checked = false;
				if (element.tagName=='A') lists[j].className='<?=$this->_prefix?>TreeItem';
				if (element.type=='hidden' && element.id.indexOf('flag')>0)	lists[j].value='0';
			}
		}
		
		function <?=$this->_prefix?>GetTreeNodePath(id) {
			var node=document.getElementById('<?=$this->_prefix?>Row['+id+'][item]');
			var path=new Array();
			var curId;
			var n=0;
			do {
				if (node.tagName=='LI') {
					curId=<?=$this->_prefix?>GetRowIdById(node.id);
					n++;
					path[n]=new Array();
					path[n]['id']=curId;
					path[n]['title']=document.getElementById('<?=$this->_prefix?>Row['+curId+'][title]').innerHTML;
				}
				node=node.parentNode;
			} while (node && node.id!='<?=$this->_mainId?>');
			return path;
		}
		
		function <?=$this->_prefix?>GetTreeNodePathAsTitle(id) {
			var path=<?=$this->_prefix?>GetTreeNodePath(id);
			var pathStr='';
			var comma='';
			for (var x in path) {
				pathStr=path[x]['title']+comma+pathStr;
				if (comma=='') comma=' « ';
			}
			return pathStr;
		}
		
		function <?=$this->_prefix?>CheckSelectedTreeItems(controlName)
		{
			var indexes=document.getElementById(controlName).value;
			var lastIndexes=document.getElementById(controlName).value;
			indexes=indexes.split(",");
			document.getElementById('<?=$this->_prefix?>SelectedTreeItems').innerHTML='';
			var myitem;
			for (var i=0; i<indexes.length; i++)
				if (indexes[i]!='') {
					myitem=document.getElementById('<?=$this->_prefix?>Row['+indexes[i]+'][selected]');
					if (typeof(myitem)=='object') {
						myitem.checked=true;
						<?=$this->_prefix?>HighlightParents(myitem)
					}
				}
		}
		
		function <?=$this->_prefix?>AddNewTreeItem(id,liId,baseName,prefix) {
			var elmLi=document.getElementById(liId);
			var data='';
			var rowId;
			var rowName;
			var ranUnrounded;
			var ranNumber;
			parentId=id;
			
			//--(Begin)-->show open close indicator
			var parentItemNumber=<?=$this->_prefix?>GetRowIdById(elmLi.id);
			var parentOpenCloseButton=document.getElementById('<?=$this->_prefix?>Row['+parentItemNumber+'][openCloseButton]');
			parentOpenCloseButton.innerHTML='<?=stripslashes($this->_closeIndicator)?>';
			//--(End)-->show open close indicator

			do {
				ranUnrounded=Math.random()*45234634563;
				ranNumber=Math.round(ranUnrounded);
				id='new___'+ranNumber;
				rowName=prefix+'Row['+id+']';
				itemId=rowName+'[item]';
			} while (document.getElementById(itemId));
			var item_number=id;
			data='<li id="'+itemId+'">';

			var replacements=new Array();
			replacements['%{item_base_name}%']=rowName;
			replacements['%{prefix}%']=prefix;
			replacements['%column_id_value%']=item_number;
			replacements['%column_parent_id_value%']=parentId;
			replacements['%{item_number}%']=item_number;
			replacements['%addIndicator%']='+';
			
			data+=<?=$this->_prefix?>ParseTemplate('','<?=$this->_prefix?>treeNodeBox',replacements);
			//--(Begin)-->empty parent_id default columns values
			data = data.replace(/(\[columns_default\]\[parent_id\] *[^<>]* value=)(["'0-9a-zA-Z]*)/ig, "$1\"\"");
			//--(End)-->empty parent_id default columns values

			data+='</li>';

			if (elmLi.innerHTML.indexOf('<ul>')>=0) {
				elmLi.innerHTML=<?=$this->_prefix?>Replace(elmLi.innerHTML,'<ul>','<ul>'+data);
			} else {
				data='<ul>'+data+'</ul>';
				elmLi.innerHTML+=data;
			}
		}
		
		function <?=$this->_prefix?>ParseTemplate(boxContainerId,tempBoxId,replacements) {
			var items_borad=document.getElementById(boxContainerId);
			var template_item_box=document.getElementById(tempBoxId).innerHTML;
			var key;
			
			for (key in replacements) {
				//if (document.all) mkey='"'+key+'"';
				var myregexp = new RegExp(key, "gmi");
				template_item_box=template_item_box.replace(myregexp,replacements[key]);
			}
			
			myregexp = new RegExp("%[^ %{}]*%", "gmi");
			if (document.all) {
				template_item_box=template_item_box.replace(myregexp,'');
				template_item_box=template_item_box.replace(/(id=)([^ ><]*)/ig, "$1\"$2\"");
				template_item_box=template_item_box.replace(/(name=)([^ ><]*)/ig, "$1\"$2\"");
				template_item_box=template_item_box.replace(/(value= *)(name)/ig, "$2");
			} else {
				template_item_box=template_item_box.replace(myregexp,'');
			}

			return template_item_box;
		}
		
		function <?=$this->_prefix?>AddNewBox(boxContainerId,tempBoxId,baseName,prefix) {
			var item_number=0;
			var element_name;
			var element;
			
			do {
				item_number++;
				element_name=baseName+'['+item_number+'][columns][id]';
				element=document.product_form.elements[element_name];
			} while (element);
		
			template_item_box=<?=$this->_prefix?>ParseTemplate(item_number,boxContainerId,tempBoxId,baseName,prefix);
	
			items_borad.innerHTML=items_borad.innerHTML+template_item_box;
		}
		
		function <?=$this->_prefix?>DeleteNode(itemNumber) {
			if (<?=$this->_prefix?>ConfimationMessage('آیا مایلید شاخه انتخاب شده با تمام زیر شاخه هایش حذف شد؟')) {
				var nodeItem=document.getElementById('<?=$this->_prefix?>Row['+itemNumber+'][item]');
				if (nodeItem) {
					var parentNode=nodeItem.parentNode;
					if (parentNode.removeChild(nodeItem)) {
						var columnsValues=new Array();
						var prevColumnsValues=new Array();
						columnsValues['<?=$this->colnId?>']=itemNumber;
						<?=$this->_prefix?>AddChanges('delete',columnsValues,null);
						if (parentNode.getElementsByTagName('li').length<1) {
							var parentItemNumber=<?=$this->_prefix?>GetRowIdById(parentNode.parentNode.id);
							var parentOpenCloseButton=document.getElementById('<?=$this->_prefix?>Row['+parentItemNumber+'][openCloseButton]');
							parentOpenCloseButton.innerHTML='';
						}
					}
				}
			}
			return false;
		}
		
		
		function <?=$this->_prefix?>GetRowIdById(id) {
			var myregexp = /[^\[\]]*\[([^\[\]]*)\].*/;
			var match = myregexp.exec(id);
			if (match != null && match.length > 1) {
				return match[1];
			} else {
				return  false;
			}
		}
		
		
		function <?=$this->_prefix?>SelectRow(object,auto) {
			var selectedRowId=<?=$this->_prefix?>GetRowIdById(object.id);
			var baseName=object.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$1[$2]");
			<?=$this->_prefix?>OnSelectRow(object,selectedRowId,baseName,'<?=$this->_displayMode?>',auto);
			
			return true;
			/*
			<?=$this->_prefix?>HighlightParents(object);
			selectedTreeItemPath
			sendSelectRow(object);
			*/
		}

		var <?=$this->_prefix?>SelectedRows=new Array();

		function <?=$this->_prefix?>OnSelectRow(object,selectedRowId,baseName,displayMode,auto) {
			var titleElm=document.getElementById(baseName+'[title]');
			var keyVar;

			if (object.checked) {
				var rowInfo=new Array();
				rowInfo['id']=selectedRowId;
				rowInfo['title']=titleElm.innerHTML;
				 <?=$this->_prefix?>SelectedRows[selectedRowId]=rowInfo;
			} else {
				for ( keyVar in  <?=$this->_prefix?>SelectedRows ) {
					if ( <?=$this->_prefix?>SelectedRows[keyVar]['id']==selectedRowId)
						 <?=$this->_prefix?>SelectedRows.splice(keyVar,1);
				}
			}
			
			if (!auto) {
				<? if (!empty($this->_jsFunctionOnSelectRow)) {?>
					<?=$this->_jsFunctionOnSelectRow?>(object,selectedRowId,baseName,displayMode);
				<? }?>
			}
		}
		
		
		function <?=$this->_prefix?>UpdateSelectedRowsArray(baseName)
		{
			if (baseName=='') baseName='<?=$this->_prefix?>Row';
			var all = <?=$this->_prefix?>GetElements(document);
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];
				if ((elm.type=='checkbox' || elm.type=='radio') && elm.id.match(/myTreeRow\[.*/i)) 
				if (elm.checked) {
					//elm.checked=true;
					<?=$this->_prefix?>SelectRow(elm);
				}
			}
		}
		
		/* selectedRowsId is like ",5,43,35,"*/
		function <?=$this->_prefix?>SelectRows(selectedRowsId) {
			selectedRowsId=','+selectedRowsId+',';
			var all = <?=$this->_prefix?>GetElements(document);
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];

				if (elm.type=='checkbox' || elm.type=='radio') {
					rowId=<?=$this->_prefix?>GetRowIdById(elm.id);
					
					if (selectedRowsId.indexOf(','+rowId+',')>=0) {
						elm.checked=true;
						<?=$this->_prefix?>SelectRow(elm,true);
					}
				}
			}
		}
		
		
		//--(Begin)-->changing parent id visually
		var <?=$this->_prefix?>SelectingNodeNewParentElm;
		
		function <?=$this->_prefix?>OnSelectingNodeNewParent(elm) {
			<?=$this->_prefix?>ToggleSelectingNodeNewParentDisplay('show');
			<?=$this->_prefix?>SelectingNodeNewParentElm=elm;
		}
		
		function <?=$this->_prefix?>OnSelectNodeNewParent(elm) {
			<?=$this->_prefix?>ToggleSelectingNodeNewParentDisplay('hide');
			<?=$this->_prefix?>SelectingNodeNewParentElm.checked=false;
			
			var itemNumber=<?=$this->_prefix?>GetRowIdById(<?=$this->_prefix?>SelectingNodeNewParentElm.parentNode.id);
			var parentIdFieldId='<?=$this->_prefix?>Row['+itemNumber+'][columns][<?=$this->colnParentId?>]';
			document.getElementById(parentIdFieldId).value=elm.value;
			<?=$this->_prefix?>SelectingNodeNewParentElm=null;
			elm.checked=false;
		}
		
		function <?=$this->_prefix?>ToggleSelectingNodeNewParentDisplay(mode) {
			elements=<?=$this->_prefix?>GetElements(document.getElementById('<?=$this->_mainId?>'));
			var radioButton;
			if (elements) {
				for (var e = 0; e < elements.length; e++) {
					elm = elements[e];
					if (elm.name=='<?=$this->_prefix?>NodeNewParent') {
						if (mode=='show') elm.style.display=''; else elm.style.display='none';
					}
					
					if (elm.name)
					if (elm.name.match(/^.*\[select\]$/i)) {
						if (mode=='show') elm.style.display='none'; else elm.style.display='';
					}
				}
			}
		}
		//--(End)-->changing parent id visually
		
		//--(Begin)-->Move up and down node in same level
		function <?=$this->_prefix?>MoveNode(itemNumber,x){
			var currentNode=document.getElementById('<?=$this->_prefix?>Row['+itemNumber+'][item]');
			var nearNode;
			var searchNext=true;
			nearNode=currentNode;
			if (currentNode) {
				do {
					if (x=='up') nearNode=nearNode.previousSibling;
					if (x=='down') nearNode=nearNode.nextSibling;

					if (nearNode) { 
						if (nearNode.tagName==currentNode.tagName) searchNext=false;
					} else searchNext=false;
				} while (searchNext!=false);

				if (x=='up' && nearNode) {
					currentNode.parentNode.insertBefore(currentNode,nearNode);
				}
				else if (x=='down' && nearNode) {
					currentNode.parentNode.insertBefore(nearNode,currentNode);				
				}
				else if (x=='first') {
					currentNode.parentNode.insertBefore(currentNode,currentNode.parentNode.firstChild);
				} else if (x=='last') {
					currentNode.parentNode.insertBefore(currentNode,null);
				}
				
				//--(Begin)-->log change in order to save to database later
				if (nearNode)
					if (nearNode!=currentNode && nearNode.tagName==currentNode.tagName) {
						var columnsValues=new Array();
						var prevColumnsValues=new Array();
						var action='move';
						columnsValues['___near_node_id']=<?=$this->_prefix?>GetRowIdById(nearNode.id);
						columnsValues['<?=$this->colnId?>']=itemNumber;
						if (x=='up') action=action+'Up';
						if (x=='down') action=action+'Down';
						<?=$this->_prefix?>AddChanges(action,columnsValues,prevColumnsValues);
					}
				//--(End)-->log change in order to save to database later
			} else
				alert("لطفا ابتدا ردیف مورد نظر را انتخاب نمایید!");
		}
		//--(End)-->Move up and down node in same level
		</script>
		<? }?>
	<?
	}
	
	
	function prepareSimpleSlider() {
		$sqlQuery="SELECT * FROM `$this->tableName` WHERE `$this->colnRightNumber`-`$this->colnLeftNumber`>1";
//		if (!empty($this->_displayFromLevel)) {
	//		$sqlQuery.=' AND '.$this->colnLevelNumber.'<'.$this->_displayFromLevel;
		if (!empty($this->_displayToLevel)) {
			$sqlQuery.=' AND '.$this->colnLevelNumber.'<'.$this->_displayToLevel;
		}
		
		$rows=cmfcMySql::getRowsCustom($sqlQuery);
	?>
		<script language="javascript" type="text/javascript">
			<?=$this->_prefix?>SimpleSlider=new simpleSliderClass();
			<?=$this->_prefix?>SimpleSlider.slidesInfo={
			<? foreach ($rows as $row) { ?>
				<?=$comma?>'<?=$this->_prefix?>Row[<?=$row[$this->colnId]?>][childs]':{
					//buttonId:'myTree1Row[22][openCloseButton]',
					buttonId:'<?=$this->_prefix?>Row[<?=$row[$this->colnId]?>][openCloseButton]',
					buttonId1:'<?=$this->_prefix?>Row[<?=$row[$this->colnId]?>][titleLink]',
					iconId:'<?=$this->_prefix?>Row[<?=$row[$this->colnId]?>][openCloseIcon]',
					iconCloseSrc:'<?=$this->_images['openIcon']['path']?>',
					iconOpenSrc:'<?=$this->_images['closeIcon']['path']?>'
				}
			<? $comma=',';}?>
			};
			<?=$this->_prefix?>SimpleSlider.instanceName='<?=$this->_prefix?>SimpleSlider';
			<?=$this->_prefix?>SimpleSlider.prepareOnLoad();
		</script>
	<?
	}
	
	function printOnloadScript() {?>
		<script language="javascript" type="text/javascript">
			function <?=$this->_prefix?>OnloadFunctions()
			{
				<? if ($this->_autoCollapseAtStart) {?>
					<?=$this->_prefix?>CollapseAll('<?=$this->_mainId?>');
				<? }?>
				<? if (!empty($this->_jsFunctionOnSelectRows)) {?>
					<?=$this->_jsFunctionOnSelectRows?>();
				<? }?>
				//check_selected_tree_items('selected_tree_items_id');
			}
			 <?=$this->_prefix?>OnloadFunctions();
			/*window.onload=<?=$this->_prefix?>OnloadFunctions();*/
		</script>
	<?
	}

	
	function printTemplate() {
		foreach ($this->_nodeHtmlTemplates as $tempName=>$tempHtml) {
		?>
			<div id="<?=$this->_prefix?><?=$tempName?>" style="display:none">
				<?=$tempHtml?>
			</div>
		<?
		}
	}
	
	function getNodeNestedPositionByRow($row) {
	/*
		$depth=$row['depth'];
		$lastDepth=$this->_prevRow['depth'];
		if (!isset($lastDepth)) $lastDepth=$depth;
		
		if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1) {
			$status='hasChild|';
		}
		$status.='normal';
		if ($depth<$lastDepth) {//parentEnd
			$status.='parentEnd';
		}
		$this->_prevRow=$row;
	*/
	}
	
	function printTree() {
		if ($this->_startNodeId==null) $this->_startNodeId=$this->getRootNodeId();
		
		if (!empty($this->_displayFromLevel))
			$conditions['and']=array($this->colnLevelNumber.'>='.$this->_displayFromLevel);
		if (!empty($this->_displayToLevel))
			$conditions['and']=array($this->colnLevelNumber.'<='.$this->_displayToLevel);
		
		$this->branch((int)$this->_startNodeId, '*',$conditions);
		
		if (!empty($this->ERRORS_MES)) {
			echo 'DB Tree Error!';
			echo '<pre>';
			print_r($this->ERRORS_MES);
			if (!empty($this->ERRORS)) {
				print_r($this->ERRORS);
			}
			echo '</pre>';
			return false;
		}

		$displayMode=$this->_displayMode; /*view ; singleSelection ; multiSelection ; view_simple*/
		if ($displayMode!='edit' and !$this->_showStartNode) {
			$this->nextRow();
		}
	?>	
		<!-- (BEGIN) : Tree -->
		<div id="<?=$this->_prefix?>ChangesContainer" style="display:hidden"></div>
		
		<ul id="<?=$this->_mainId?>" class="<?=$this->_prefix?>">
		<?
		while ($row = $this->nextRow()) {
			$rowName=$this->_prefix."Row[".$row[$this->colnId]."]";
			
			$depth=$row[$this->colnLevelNumber];
			
			if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1)
				$hasChild=true; else $hasChild=false;
			if (!isset($lastDepth)) $lastDepth=$depth;
			
			#--(Begin)-->limit number of levels to show
			if (!empty($this->_displayToLevel)) {
				if ($depth>=$this->_displayToLevel) $hasChild=false;
				if ($depth>$this->_displayToLevel) continue;
			}
			#--(End)-->limit number of levels to show
			
			if ($depth<$lastDepth) {/*parentEnd*/
				echo "\n";
				echo str_repeat('    ', 1 * $depth);
				echo str_repeat("</ul>\n".str_repeat('    ', 1 * $depth)."</li>\n",$lastDepth-$depth);
			}

			echo "\n";
			echo str_repeat('    ', 1 * $depth);
			
			if ($hasChild) $className=$this->_prefix.'ParentNode'; else $className=$this->_prefix.'ChildNode';
			if (!isset($minDepth)) $minDepth=$depth; 
			if ($depth<=$minDepth) $className=$this->_prefix.'TopNode'.' '.$className;
			echo '<li id="'.$rowName.'[item]" class="'.$className.'">';
			


			#--(Begin)-->Manipulate row columns via callback function
			if (is_callable($this->_cbFuncManipulateRowColumnsBeforeDraw))
				$row=call_user_func_array($this->_cbFuncManipulateRowColumnsBeforeDraw,array(&$this,$row,$hasChild));
			#--(End)-->Manipulate row columns via callback function
			
			#--(Begin)-->Generate additional items
			if (is_callable($this->_cbFuncGetAdditionalItems))
				$additionalItemsHtml=call_user_func_array($this->_cbFuncGetAdditionalItems,array(&$this,$row,$hasChild));
			//$additionalItemsHtml='<li><a href="">'.'سلام'.'</a></li>';
			#--(End)-->Generate additional items

			echo $this->_useTemplate($row,$hasChild);
			/*
			if ($displayMode=='edit') {
				echo $this->_useTemplate($row);
			} elseif ($displayMode=='singleSelect' or $displayMode=='multiSelect') {
				echo $this->_useTemplate($row);
			} else{
				echo $this->_useTemplate($row);
			}*/
	        echo "\n";
			echo str_repeat('    ', 1 * $depth);
		
			if ($hasChild) {			
				echo '<ul id="'.$rowName.'[childs]" style="height:0px;overflow:hidden">'."\n";
				#--(Begin)-->Append additional items
				if (!empty($additionalItemsHtml)) {
					echo $additionalItemsHtml."\n";
				}
				#--(End)-->Append additional items
			} else {
				#--(Begin)-->Append additional items
				if (!empty($additionalItemsHtml)) {
					echo "<ul id=\"".$rowName."[childs]\" style=\"height:0px;overflow:hidden\">$additionalItemsHtml</ul>"."\n";
				}
				#--(End)-->Append additional items
				echo "</li>\n";
			}
			$lastDepth=$depth;
		} 
		echo "\n";
		echo str_repeat('    ', 1 * $depth);
		echo @str_repeat("</ul>\n".str_repeat('    ', 1 * $depth)."</li>\n",$depth-3);
		?>
		</ul>
		<? if ($displayMode=='edit') { ?>
			<script language="javascript" type="text/javascript" defer="defer">
				document.getElementById('<?=$this->_prefix?>Row[<?=$this->_startNodeId?>][delete]').innerHTML='';
				document.getElementById('<?=$this->_prefix?>Row[<?=$this->_startNodeId?>][editSave]').innerHTML='';
				document.getElementById('<?=$this->_prefix?>Row[<?=$this->_startNodeId?>][moveUp]').innerHTML='';
				document.getElementById('<?=$this->_prefix?>Row[<?=$this->_startNodeId?>][moveDown]').innerHTML='';
			</script>
		<? }?>
		<!-- (END) : Tree -->
	<?
	}


	function printChangesBoard() {?>
		<div id="<?=$this->_prefix?>ChangesBoard"></div>
	<? }
	
	function printAll() {
		$this->printJavaScripts();
		if ($this->_displayMode=='edit')
			$this->printTemplate();
		//$this->printChangesBoard();
		$this->printTree();
		$this->printOnloadScript();
	}
	
	
	function printDefaultStyles() {?>
		<style>
			.<?=$this->_prefix?>HighlightTreeItem{ 
				font-weight:bold;
				color:red;
				text-decoration:none;
			 }
			.<?=$this->_prefix?>OpenCloseButton{ 
				font-weight:normal;
				color:inherit;
				text-decoration:none;
				font-family: "Courier New", Courier, monospace;
				font-size:15px;
			 }
			 
			.<?=$this->_prefix?>ChildNode {
			}
			
			.<?=$this->_prefix?>TopNode {
			}
			 
			.<?=$this->_prefix?> {
				
			}
			
			.<?=$this->_prefix?> li {
				margin-top:8px;
			}
			
			.<?=$this->_prefix?>EditBox {
				padding:4px;
				margin-top:3px;
				margin-right:10px;
				border:1px dotted gray;
				width:300px
			}
		</style>
	<?
	}
}
?>