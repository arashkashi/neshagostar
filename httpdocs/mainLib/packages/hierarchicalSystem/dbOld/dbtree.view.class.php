<?php
require_once(dirname(__FILE__).'/dbtree.db.class.php');

define('Tree_View_Structure_Is_Corrupted',20);

/**
* @changes
*	- support for manipulate row before draw in edit mode
*	- adding different css class for each node make it possible to highlight path only with CSS!
* @todo
* 	- additonal css classes for each styling
* 	- using decorator pattern for simplifing
* 	- using GeneralLib latest core features for ease of use
* 	- generalizing javascript functions and using javascript package for reducing complexity
* 	- ajax implementation, it's preferable to use cmfcAjax package
* 	- support for mulilingual values (thorugh ajax i guess)
* 	- Implementing jquery tree and tree table
* 		http://www.hanpau.com/index.php?page=jqtreetable
* 		http://jquery.bassistance.de/treeview/demo/
* 		
*/

class cmfcDbTreeView extends cmfcDbTree {
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
	
	/**
	* name of the javascript function which you want to be called after
	* selecting each node checkbox
	* <code>
	* function test(object,selectedRowId,baseName,displayMode)
	* </code>
	*/
	var $_jsFunctionOnSelectRow;
	var $_jsFunctionOnSelectRows;
	
	var $_dbConnectionLink;
	
	var $_cbFuncManipulateRowColumnsBeforeDraw;
	var $_cbFuncGetAdditionalItems;
	var $_cbFuncRowTemplateProcessorBefore;
	/**
	* parameter _titleValueFunctionCallBack :
	* the following value has been added to allow multilingual TITLE definition.
	* using this feature, one can define a user-defined function which takes CATEGORY_ID and a TreeObject as parameters,
	* can calculate appropriate title for each node using that...
	*/
	var $_cbFuncManipulateTitleValue = '';
	
	//require in getNodeNestedPositionByRow function
	var $_prevRow;
	var $_autoCollapseAtStart=true;
	var $_startNodeId=null;
	var $_debugModeEnabled=false;
	var $_highlightParentNodesEnabled=false;
	var $_nodeVisibilityEnabled=false;
	var $_visibleNodeIds;
	
	/**
	* TreeView has some inline templates which will append some require 
	* html to user template , sometimes user wants to make a very different template
	* from scrach, in this case he show change this property. 
	* availabe modes
	* onlyCustom,
	* onlyDefault,
	* mergeCustomAndDefault,
	* mergeCustomAndDefaultNecessaryParts,
	* mergeCustomAndDefaultInvisibleParts
	*/
	var $_templateMode='mergeCustomAndDefault';
	var $_strings=array(
		'deleteConfirm'=>'آيا مايليد شاخه انتخاب شده با تمام زير شاخه هايش حذف شود؟',
		'parentSelection'=>'انتخاب سر شاخه',
		'selectRowFirst'=>'لطفا ابتدا ردیف مورد نظر را انتخاب نمایید!'
	);
	
	/**
	* @desc with this to properties it's possible to limit the tree
	* 		to specific depth.
	*/
	var $_displayFromLevel=null;
	var $_displayToLevel=null;
	
	
	function cmfcDbTreeView($options) {
		if (isset($options['dbConnectionLink'])) {
			$this->_dbConnectionLink=$options['dbConnectionLink'];
			@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
			$db->conn=&$this->_dbConnectionLink; 			
			return parent::cmfcDbTree($options['tableName'],$options['prefix'],$db);
			
		} elseif (isset($options['dbInstance'])) {
			$db=$options['dbInstance'];
			return parent::cmfcDbTree($options['tableName'],$options['prefix'],$db);
		}
	}
	
	function errorMessages($errorNo) {
		$messages=array(
			Tree_View_Structure_Is_Corrupted => "Tree structure has problem (probably corrupted, you may use rebuild method)!",
		);
		
		return $messages[$errorNo];
	}
	
	
	/**
	* this function sets a function name which can be used for calculating tree node titles...
	*/
	function setCbFuncManipulateTitleValue($functionName){
		$this->_cbFuncManipulateTitleValue = $functionName;
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

	function setHighlightParentNodesEnabled($value) {
		$this->_highlightParentNodesEnabled=$value;
	}
	
	
	function setNodeVisibilityEnabled($value) {
		$this->_nodeVisibilityEnabled=$value;
	}
	
	function setVisibleNodeIds($value) {
		$this->_visibleNodeIds=$value;
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
			$editSaveIndicator='<img id="%{item_base_name}%[edit_save_icon]" src="'.$this->_images['editIcon']['path'].'" alt="edit/save" border="0" width="'.$this->_images['editIcon']['width'].'" height="'.$this->_images['editIcon']['height'].'" />';
		if (!empty($this->_images['deleteIcon']['path']))
			$deleteIndicator='<img id="%{item_base_name}%[delete_icon]" src="'.$this->_images['deleteIcon']['path'].'" alt="delete" border="0" width="'.$this->_images['deleteIcon']['width'].'" height="'.$this->_images['deleteIcon']['height'].'" />';
		else $deleteIndicator='[X]';
		if (!empty($this->_images['addIcon']['path']))
			$addIndicator='<img id="%{item_base_name}%[add_icon]" src="'.$this->_images['addIcon']['path'].'" alt="add" border="0" width="'.$this->_images['addIcon']['width'].'" height="'.$this->_images['addIcon']['height'].'" />';
		else $addIndicator='+';	
		if (!empty($this->_images['moveUpIcon']['path']))
			$moveUpIndicator='<img id="%{item_base_name}%[move_up]" src="'.$this->_images['moveUpIcon']['path'].'" alt="Move Up" border="0" width="'.$this->_images['moveUpIcon']['width'].'" height="'.$this->_images['moveUpIcon']['height'].'" />';
		else $moveUpIndicator='U';	
		if (!empty($this->_images['moveDownIcon']['path']))
			$moveDownIndicator='<img id="%{item_base_name}%[move_down]" src="'.$this->_images['moveDownIcon']['path'].'" alt="Move Down" border="0" width="'.$this->_images['moveDownIcon']['width'].'" height="'.$this->_images['moveDownIcon']['height'].'" />';
		else $moveDownIndicator='D';

		if (!empty($this->_images['openIcon']['path'])) {
			$openIndicator=sprintf('<img id="%s" src="%s" style="%s" alt="Open" border="0" width="'.$this->_images['openIcon']['width'].'" height="'.$this->_images['openIcon']['height'].'" />',
				'%{item_base_name}%[openCloseIcon]',
				$this->_images['openIcon']['path'],
				$this->_images['openIcon']['style']
			);
		} else $openIndicator='[+]';
		$this->_openIndicator=$openIndicator.'<!--Open-->';
		if (!empty($this->_images['closeIcon']['path'])) {
			$closeIndicator=sprintf('<img id="%s" src="%s" style="%s" alt="Close" border="0" width="'.$this->_images['closeIcon']['width'].'" height="'.$this->_images['closeIcon']['height'].'" />',
				'%{item_base_name}%[openCloseIcon]',
				$this->_images['closeIcon']['path'],
				$this->_images['closeIcon']['style']
			);
		} else $closeIndicator='[-]';
		$this->_closeIndicator=$closeIndicator.'<!--Close-->';
		#--(End)-->Defining Buttons Indicators
	
		if ($displayMode=='singleSelect' or $displayMode=='multiSelect') {
			if ($displayMode=='singleSelect') {
				$selectControl='<!-- [selectControl] --><input style="margin-bottom:0px" name="%{item_base_name}%[selectedRow][id]" id="%{item_base_name}%[selected]" type="radio" onclick="%{prefix}%SelectRow(this)" onchange="%{prefix}%SelectRow(this)" value="%{item_number}%" %checked%>&nbsp;<!-- [selectControl] -->';
			} elseif($displayMode=='multiSelect') {
				$selectControl='<!-- [selectControl] --><input style="margin-bottom:0px" name="%{item_base_name}%[selected]" id="%{item_base_name}%[selected]" type="checkbox" onclick="%{prefix}%SelectRow(this)" onchange="%{prefix}%SelectRow(this)" value="true" %checked%>&nbsp;<!-- [selectControl] -->';
			}

			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				<input name="%{item_base_name}%[id]" id="%{item_base_name}%[id]" type="hidden" value="%column_id_value%"/>
				<input name="%{item_base_name}%[parent_id]" id="%{item_base_name}%[parent_id]" type="hidden" value="%column_parent_id_value%"/>
				<input name="%{item_base_name}%[flag]" id="%{item_base_name}%[flag]" type="hidden" value="0"/>
				&raquo;
				<a href="?" id="%{item_base_name}%[openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton %OpenCloseToggleButtonClass%" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[container]">
					$selectControl
					<span id="%{item_base_name}%[title]">%title%</span>
				</span>
EOT;

		} elseif ($displayMode=='edit') {
			$customHtml=$nodeHtmlTemplates['treeNodeEditBox'];
			$__pname='%column_'.$this->colnParentId.'_value%';
			$__s=$this->_strings['parentSelection'];
			$this->_nodeHtmlTemplates['treeNodeEditBox']=<<<EOT
			 	$__s : <input style="width:150px;direction:rtl" name="%{item_base_name}%[columns][$this->colnParentId]" id="%{item_base_name}%[columns][$this->colnParentId]" type="hidden" value="$__pname" size="20"/>
				<input id="%{prefix}%[selectNodeNewParent]" onclick="%{prefix}%OnSelectingNodeNewParent(this)"  name="%{prefix}%[selectNodeNewParent]"  type="radio" value="%{item_number}%" style="margin-bottom:0px">
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
				<a href="?" id="%{item_base_name}%[openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton %OpenCloseToggleButtonClass%" title="Open / Close">%openCloseIndicator%</a>
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
				<a href="?" id="%{item_base_name}%[openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton %OpenCloseToggleButtonClass%" title="Open / Close">%openCloseIndicator%</a>
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
		} else {
			$openCloseIndicator='<span style="visibility:hidden">[-]</span>';
		}
		
		if (is_array($this->_selectedRows))
			if  (in_array($row[$this->colnId],$this->_selectedRows)) {
				$rowSelected='checked';
			}
		
		$titleLinkUrl='javascript:void(0)';
		if (!empty($row[$this->colnLink]))
			$titleLinkUrl=$row[$this->colnLink];
		
		#--(Begin)-->select mode checkbox or radiobuttons remover in specific mode
		if ($this->isInDisplayModeDetails('onlyChilds') and $hasChild) {
			$nodeHtmlTemplate = preg_replace('/<!-- \\[selectControl\\] -->.*<!-- \\[selectControl\\] -->/si', '', $nodeHtmlTemplate);
		}
		#--(Begin)-->select mode checkbox or radiobuttons remover in specific mode
		
		#--(Begin)-->use user custom function to process the template
		if (is_callable($this->_cbFuncRowTemplateProcessorBefore))
			$nodeHtmlTemplate=call_user_func_array($this->_cbFuncRowTemplateProcessorBefore,array(&$this,$row,$hasChild,$nodeHtmlTemplate));
		#--(End)-->use user custom function to process the template
		
		
		/*
			if _titleValueFunctionCallBack is defined, title value is calculated using the given function.
			the callback function MUST TAKE EXACTLY 2 PARAMETERS:
				category_id and treeObject.
			added by arash dalir, 31/1/2008, project STEMCELL
		*/
		if (is_callable($this->_cbFuncManipulateTitleValue)){
			$title = call_user_func_array($this->_cbFuncManipulateTitleValue, array($row['id'], &$this) );
		}
		else{
			$title = $row[$this->_titleColumnName];
		}
	
		#--(Begin)-->use template
		$replacements=array(
			"%title%" => $title,
			"%{prefix}%" => $this->_prefix,
			"%openCloseIndicator%" => $openCloseIndicator,
			"%{item_base_name}%" => $this->_prefix.'Row['.$itemNumber.']',
			"%{item_number}%" => $itemNumber,
			"%checked%" => $rowSelected,
			"%column_parent_id_value%" => $row[$this->colnParentId],
			"%column_id_value%" => $row[$this->colnId],
			"%column_name_value%" => $row[$this->colnName],
			"%title_link_url%" => $titleLinkUrl,
			'%column_title_value%' => $title,
			"%OpenCloseToggleButtonClass%"=>($hasChild)?$this->_prefix.'ToggleButtonParentNode':$this->_prefix.'ToggleButtonChildNode'
		);
		
		//default columns
		foreach($this->_columnsNames as $columnName) {
			if (!$replacements["%column_$columnName"."_value%"])
				$replacements["%column_$columnName"."_value%"] = $row[$columnName];
		}
		
		$itemHtml=cmfcString::replaceVariables($replacements,$nodeHtmlTemplate);		
		/*
		show_custom_row('Ú¯ÙˆØ§Ù‡ÛŒ Ù†Ø§Ù…Ù‡ Ù‡Ø§ '.'<a href="javascript:glAddNewBox(\'certificate_images\',\'template_certificate_image_box\',\'certificate_image\')">+</a>',
			'<div id="certificate_images">'.$items_html.'</div>');	*/
		#--(End)-->use template
		
		return $itemHtml;
	}
	
	
	function isInDisplayModeDetails($mode) {
		if ($this->_displayModeDetails==$mode) return true;
		return false;
		
	}
	
	function printJavaScripts() {?>
		<?php if ($this->_displayMode!='viewSimple') {?>
		<script language="javascript" type="text/javascript">
			//general scripts
			function <?php echo $this->_prefix?>GetElements(doc_obj) 
			{
				if (doc_obj==null) {doc_obj=document;}
				var all = doc_obj.all ? doc_obj.all :
						doc_obj.getElementsByTagName('*');
				var elements = new Array();
				for (var e = 0; e < all.length; e++)
						elements[elements.length] = all[e];
				return elements;
			}
			
			function <?php echo $this->_prefix?>GetElementsByIdPattern(regexp,doc_obj) 
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
			
			
			function <?php echo $this->_prefix?>Replace(s, t, u) {
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
				r += <?php echo $this->_prefix?>Replace(s.substring(i + t.length, s.length), t, u);
			  return r;
			}
			
			function <?php echo $this->_prefix?>GetElementsByName(doc_obj) 
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
			
			function <?php echo $this->_prefix?>ToggleBullet(elm) {
				var simpleSliderInstance;
				try {
					simpleSliderInstance=<?php echo $this->_prefix?>SimpleSlider;
				} catch(e) {
				}
				if (simpleSliderInstance) {
					
				} else {
					<?php echo $this->_prefix?>ToggleOpenCloseIndicator(elm);
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
		
			function <?php echo $this->_prefix?>CollapseAll(id) {
				var simpleSliderInstance;
				try {
					simpleSliderInstance=<?php echo $this->_prefix?>SimpleSlider;
				} catch(e) {
				}
				if (simpleSliderInstance) {
				
					if (!id) id='<?php echo $this->_prefix?>Root';
					var e = document.getElementById(id);
					
					var lists = e.getElementsByTagName('UL');
					for (var j = 0; j < lists.length; j++) {
						lists[j].style.height = "0px";
						lists[j].style.overflow = "hidden";
					}
					lists = e.getElementsByTagName('ul');
					for (var j = 0; j < lists.length; j++) {
						lists[j].style.height = "0px";
						lists[j].style.overflow = "hidden";
					}
					
					e.style.display = "block";
				} else {
					if (!id) id='<?php echo $this->_prefix?>Root';
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
			
			function  <?php echo $this->_prefix?>ShowHideElement(id) {
				var elm=document.getElementById(id);
				if (elm.style.display=='none')
					elm.style.display='';
				else
					elm.style.display='none';
			}
			
			function <?php echo $this->_prefix?>ConfimationMessage(message) {
				if(confirm(message)) { return true; }
				else {return false;}
			}
			
			function <?php echo $this->_prefix?>CloseAll() {
				var myRegexp=/\[openCloseButton\]/;
				var triggers=<?php echo $this->_prefix?>GetElementsByIdPattern(myRegexp,document.getElementById('<?php echo $this->_prefix?>Root'));
				for (var i in triggers) {
					if (typeof(triggers[i])!='function') {
						var trigger=triggers[i];
						if (trigger.innerHTML.indexOf('--Close--')>0) {
							<?php echo $this->_prefix?>ToggleBullet(trigger);
						}
					}
				}
			}
		</script>
		
		
		<script language="javascript" type="text/javascript">
		
			function <?php echo $this->_prefix?>ToggleOpenCloseIndicator(elm) {
				//elm.innerHTML=<?php echo $this->_prefix?>Replace(elm.innerHTML,' ','');
				if (elm.innerHTML.indexOf('--Open--')>0 || elm.innerHTML.indexOf('--open--')>0)
					elm.innerHTML='<?php echo stripcslashes($this->_closeIndicator)?>';
				else
					elm.innerHTML='<?php echo stripcslashes($this->_openIndicator)?>'
			}
		
		</script>
		<?php }?>
		  
		<?php if ($this->_displayMode!='viewSimple' and $this->_displayMode!='view') {?>
		<script language="javascript" type="text/javascript">
		//special scripts
		function <?php echo $this->_prefix?>AddChanges(action,columnsValues,prevColumnsValues) {
			var changesContainer=document.getElementById('<?php echo $this->_prefix?>ChangesContainer');
			var changesBoard=document.getElementById('<?php echo $this->_prefix?>ChangesBoard');
			var html='';
			var value;
			var date = new Date();
			if (!prevColumnsValues)
				prevColumnsValues=new Array();
			var index=String(date.getFullYear())+'-'+date.getDate()+'-'+date.getMonth()+' '+date.getHours()+':'+String(date.getMinutes())+':'+date.getSeconds();
			for (columnName in columnsValues) {
				value=columnsValues[columnName];
				html=html+'<input type="hidden" name="<?php echo $this->_prefix?>Changes['+index+'][columns]['+columnName+']" value="'+value+'"/>';
				if (prevColumnsValues[columnName]) {
					prevValue=prevColumnsValues[columnName];
					html=html+'<input type="hidden" name="<?php echo $this->_prefix?>Changes['+index+'][previous_columns]['+columnName+']" value="'+prevValue+'"/>';
				}
			}
			html=html+'<input name="<?php echo $this->_prefix?>Changes['+index+'][action]" type="hidden" value="'+action+'"/>';
//			changesContainer.style.display='none';
			changesContainer.innerHTML=changesContainer.innerHTML+html+"\n";
			var more='';
			if (columnsValues['<?php echo $this->colnParentId?>']!=prevColumnsValues['<?php echo $this->colnParentId?>'] && columnsValues['<?php echo $this->colnParentId?>']) 
				more='(Moved from '+prevColumnsValues['<?php echo $this->colnParentId?>']+' to '+columnsValues['<?php echo $this->colnParentId?>']+')';
			if (changesBoard)
				changesBoard.innerHTML+=index+' : Node '+columnsValues['id']+' changed '+more+' <br/>';
		}
		
		function <?php echo $this->_prefix?>ShowEditBox(itemNumber,containerId) {
			var prefix='<?php echo $this->_prefix?>';
			var rowName=prefix+'Row['+itemNumber+']';
			var itemContainerId=rowName+'[item]';
			var defaultFields;
			var hiddenField;

			var replacements=new Array();
			replacements['%{item_base_name}%']=rowName;
			replacements['%{prefix}%']=prefix;
			replacements['%{item_number}%']=itemNumber;
			replacements['%column_<?php echo $this->colnParentId?>_value%']=document.getElementById(rowName+'[<?php echo $this->colnParentId?>]').value;

			var myregexp = new RegExp("^[^\\[\\]]*\\["+itemNumber+"]\\[columns_default]\\[([^\\[\\]]*)\\]", "i");

			defaultFields=<?php echo $this->_prefix?>GetElementsByIdPattern(myregexp,document.getElementById(itemContainerId));

			for (defaultFieldI in defaultFields) {
				defaultField=defaultFields[defaultFieldI];
				columnName=<?php echo $this->_prefix?>GetColumnNameById(defaultField.id);
				//alert(columnName);
				if (defaultField && columnName!='<?php echo $this->colnParentId?>') {
					replacements['%column_'+columnName+'_value%']=defaultField.value;
					if (defaultField.value==1 || defaultField.value==true || defaultField.value=='true' || defaultField.value=='yes')
						replacements['%column_'+columnName+'_value_checked%']='checked="checked"';
				}
			}

			var customHtml=<?php echo $this->_prefix?>ParseTemplate('','<?php echo $this->_prefix?>treeNodeEditBox',replacements);
			document.getElementById(containerId).innerHTML=customHtml;
		}
		
		function <?php echo $this->_prefix?>GetColumnNameById(id) {
			var myregexp = /^[^\[\]]*\[[^\[\]]*\]\[(columns|columns_default)]\[([^\[\]]*)\]/i;
			var match = myregexp.exec(id);
			if (match != null) {
				result = match[2];
			} else {
				result = "";
			}
			return result;
		}
		
		function <?php echo $this->_prefix?>HideEditBox(itemNumber,containerId) {
			var prefix='<?php echo $this->_prefix?>';
			var rowName=prefix+'Row['+itemNumber+']';
			var itemContainerId=rowName+'[item]';
			var columnsValues=new Array()
			var prevColumnsValues=new Array()
			var hiddenField;
			var field;
			var total=0;
			var fields;
			var columnName;
			
			var myregexp = new RegExp("^[^\\[\\]]*\\["+itemNumber+"]\\[columns]\\[([^\\[\\]]*)\\]", "i");
			fields=<?php echo $this->_prefix?>GetElementsByIdPattern(myregexp,document.getElementById(itemContainerId));
			
			for (fieldI in fields) {
				field=fields[fieldI];
				columnName=<?php echo $this->_prefix?>GetColumnNameById(field.id);
				hiddenField=document.getElementById(rowName+'[columns_default]['+columnName+']');

				//alert(field.id+'|'+hiddenField.id);

				if (hiddenField && field) {
					var f__value='';
					if (field.type.toLowerCase()=="checkbox") {
						if (field.checked) f__value=field.value; else f__value='0';
					} else {
						f__value=field.value;
					}
					
					if (hiddenField.value!=f__value) {
						total++;
						prevColumnsValues[columnName]=hiddenField.value;
						columnsValues[columnName]=f__value;
						hiddenField.value=f__value;
					}
				}
			}
			
			if (total>0) {
				columnsValues['id']=itemNumber;
				prevColumnsValues['id']=itemNumber;

				var action='none';
				//if (itemNumber.match(/new.*/)) {
				//	action='insert';
				//}
				//if (columnsValues['<?php echo $this->colnParentId?>']!=prevColumnsValues['<?php echo $this->colnParentId?>'] && columnsValues['<?php echo $this->colnParentId?>']) {
				//}

				<?php echo $this->_prefix?>AddChanges(action,columnsValues,prevColumnsValues);
			}
			
			//--(Begin)-->Move Node
			if (prevColumnsValues['<?php echo $this->colnParentId?>']!=columnsValues['<?php echo $this->colnParentId?>']) {
				if (columnsValues['<?php echo $this->colnParentId?>']=='')
					var newParentId='<?php echo $this->_mainId?>';
				else
					var newParentId=prefix+'Row['+columnsValues['<?php echo $this->colnParentId?>']+']'+'[item]';
				var newParent=document.getElementById(newParentId);
				var openCloseButton=document.getElementById(prefix+'Row['+columnsValues['<?php echo $this->colnParentId?>']+']'+'[openCloseButton]');

				var child=document.getElementById(rowName+'[item]');
				//alert('child :'+child.id+' parent : '+newParent.id);
				if (newParent && child) {
					if (newParentId=='<?php echo $this->_mainId?>')
						myUl=newParent;
					else {
						myUl=newParent.getElementsByTagName('ul');
						if (myUl[0]) {
							myUl=myUl[0];
						} else {
							var myUl=document.createElement('ul');
							newParent.appendChild(myUl);
							if (openCloseButton)
								openCloseButton.innerHTML='<?php echo $this->_closeIndicator?>';
						}
					}
					myUl.insertBefore(child,myUl.firstChild);
				}
			}
			//--(End)-->Move Node
			
			document.getElementById(containerId).innerHTML='';
		}
	
		function <?php echo $this->_prefix?>TriggerSave(itemNumber,id,titleId,valueId,imageIndicatorId) {
			
			var elm=document.getElementById(id);
			var imageIndicator=document.getElementById(imageIndicatorId);
			if (elm.style.display=='none') {
				elm.style.display='';
				if (imageIndicator)
					imageIndicator.src="<?php echo $this->_images['saveIcon']['path']?>";
				<?php echo $this->_prefix?>ShowEditBox(itemNumber,id);
			} else {
				if (imageIndicator)
					imageIndicator.src="<?php echo $this->_images['editIcon']['path']?>";
				elm.style.display='none';
				document.getElementById(titleId).innerHTML=document.getElementById(valueId).value;
				<?php echo $this->_prefix?>HideEditBox(itemNumber,id);
			}
		}
			
		
		function <?php echo $this->_prefix?>GetNodeParents(elm,resultType,includeItself,separator) {
			var selectedRowId=<?php echo $this->_prefix?>GetRowIdById(elm.id);
			var nodesBaseName=elm.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$1");
			var n=0;
			if (!separator) separator=' , ';
			if (!includeItself) includeItself=false;

			var parentsInfo=new Array;
			var pathSeparator='';
			var stringPath='';
			
			/*
			var idName=<?php echo $this->_prefix?>Replace(elm.id,'[selected]','');
			var parentId=document.getElementById(nodeBaseName+'[parent_id]').value;
			var currElm=elm;
			var selectedTreeItemsStr='';
			*/
			
			//--(Begin)-->fetch selected node objects etc
			var nodeBaseName=elm.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$1[$2]");
			var nodeParentId=elm.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$2");
			var nodeIdName=elm.id;
			//--(End)-->fetch selected node objects etc
			
			while (nodeParentId!='' & nodeParentId!='1') {
				n++;
				//--(Begin)-->fetch selected node objects etc
				var nodeBaseName=nodesBaseName+'['+nodeParentId+']';
				var nodeId=nodeParentId;
				
				var parentInfo={
					nodeId : nodeId,
					nodeBaseName : nodeBaseName,
					nodeObj : document.getElementById(nodeBaseName+'[item]'),
					nodeFlagObj : document.getElementById(nodeBaseName+'[flag]'),
					nodeContainerObj : document.getElementById(nodeBaseName+'[container]'),
					nodeTitleObj : document.getElementById(nodeBaseName+'[title]'),
					nodeCheckboxObj : document.getElementById(nodeBaseName+'[selected]'),
					nodeParentIdObj : document.getElementById(nodeBaseName+'[parent_id]')
				}

				if (parentInfo['nodeParentIdObj']) nodeParentId=parentInfo['nodeParentIdObj'].value; else nodeParentId='';
				//--(End)-->fetch selected node objects etc
				
				if (parentInfo['nodeObj'])
					if (includeItself || (!includeItself && nodeBaseName+'[selected]'!=nodeIdName)) {
						parentsInfo[n]=parentInfo;
						
						if (resultType=='stringPathIds') {
							stringPath=parentInfo['nodeId']+pathSeparator+stringPath;
							pathSeparator=separator;
						}
						if (resultType=='stringPathTitles') {
							stringPath=parentInfo['nodeTitleObj'].innerHTML+pathSeparator+stringPath;
							pathSeparator=separator;
						}
					}
			}
			
			if (resultType=='array') {
				parentsInfo=parentsInfo.reverse();
				return parentsInfo;
			} else {
				return stringPath;
			}
		}
		
		
		function <?php echo $this->_prefix?>HighlightParents(elm)
		{
			if (elm.checked) hightlight=true; else hightlight=false;
			
			var parentsInfo=<?php echo $this->_prefix?>GetNodeParents(elm,'array');
			if (parentsInfo)
				for (var i in parentsInfo) {
					var parentInfo=parentsInfo[i];
					if (parentInfo['nodeObj']) {
						if (hightlight) parentInfo['nodeFlagObj'].value++; else parentInfo['nodeFlagObj'].value--;
	
						if (parentInfo['nodeFlagObj'].value>0) {
							parentInfo['nodeContainerObj'].style.color='red';
							parentInfo['nodeContainerObj'].className='<?php echo $this->_prefix?>HighlightedItem';
						} else {
						
							parentInfo['nodeContainerObj'].style.color='';
							parentInfo['nodeContainerObj'].className='<?php echo $this->_prefix?>NormalItem';
						}
					}
				}
		}
		
		
		
		function <?php echo $this->_prefix?>EmptyTree() {
			var container=document.getElementById('<?php echo $this->_prefix?>Root');
			var inputItems=container.getElementsByTagName('input');
	
			for (var x in inputItems) {
				var inputItem=inputItems[x];
				if (typeof(inputItem)!='function') {
					if (inputItem.type=='checkbox' && inputItem.checked==true) {
						//inputItem.checked=false;
						inputItem.click();
					}
				}
			}
			<?php
			/*
			var lists = <?php echo $this->_prefix?>GetElements(document.getElementById('root'));
			var element;
			if (document.getElementById('<?php echo $this->_prefix?>SelectedTreeItems'))
				document.getElementById('<?php echo $this->_prefix?>SelectedTreeItems').innerHTML='';
			if (document.getElementById('<?php echo $this->_prefix?>SelectedTreeItemsId'))
				document.getElementById('<?php echo $this->_prefix?>SelectedTreeItemsId').value='';
			if (document.getElementById('<?php echo $this->_prefix?>SelectedTreeItemId'))	
				document.getElementById('<?php echo $this->_prefix?>SelectedTreeItemId').value='';
			for (var j = 0; j < lists.length; j++) {
				element=lists[j];
				if (element.type=='checkbox') lists[j].checked = false;
				if (element.tagName=='A') lists[j].className='<?php echo $this->_prefix?>TreeItem';
				if (element.type=='hidden' && element.id.indexOf('flag')>0)	lists[j].value='0';
			}
			*/
			?>
		}
		
		
		function <?php echo $this->_prefix?>GetTreeNodePath(id) {
			var node=document.getElementById('<?php echo $this->_prefix?>Row['+id+'][item]');
			var path=new Array();
			var curId;
			var n=0;
			do {
				if (node.tagName=='LI') {
					curId=<?php echo $this->_prefix?>GetRowIdById(node.id);
					n++;
					path[n]=new Array();
					path[n]['id']=curId;
					path[n]['title']=document.getElementById('<?php echo $this->_prefix?>Row['+curId+'][title]').innerHTML;
				}
				node=node.parentNode;
			} while (node && node.id!='<?php echo $this->_mainId?>');
			return path;
		}
		
		function <?php echo $this->_prefix?>GetTreeNodePathAsTitle(id,separator) {
			var path=<?php echo $this->_prefix?>GetTreeNodePath(id);
			var pathStr='';
			var comma='';
			if (!separator) separator=' آ« ';
			for (var x in path) {
				if (typeof(path[x])!='function') {
					pathStr=path[x]['title']+comma+pathStr;
					if (comma=='') comma=separator;
				}
			}
			return pathStr;
		}
		
		function <?php echo $this->_prefix?>CheckSelectedTreeItems(controlName)
		{
			var indexes=document.getElementById(controlName).value;
			var lastIndexes=document.getElementById(controlName).value;
			indexes=indexes.split(",");
			document.getElementById('<?php echo $this->_prefix?>SelectedTreeItems').innerHTML='';
			var myitem;
			for (var i=0; i<indexes.length; i++)
				if (indexes[i]!='') {
					myitem=document.getElementById('<?php echo $this->_prefix?>Row['+indexes[i]+'][selected]');
					if (typeof(myitem)=='object') {
						myitem.checked=true;
						<?php echo $this->_prefix?>HighlightParents(myitem)
					}
				}
		}
		
		function <?php echo $this->_prefix?>AddNewTreeItem(id,liId,baseName,prefix) {
			var elmLi=document.getElementById(liId);
			var data='';
			var rowId;
			var rowName;
			var ranUnrounded;
			var ranNumber;
			parentId=id;
			
			//--(Begin)-->show open close indicator
			var parentItemNumber=<?php echo $this->_prefix?>GetRowIdById(elmLi.id);
			var parentOpenCloseButton=document.getElementById('<?php echo $this->_prefix?>Row['+parentItemNumber+'][openCloseButton]');
			parentOpenCloseButton.innerHTML='<?php echo stripslashes($this->_closeIndicator)?>';
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
			
			data+=<?php echo $this->_prefix?>ParseTemplate('','<?php echo $this->_prefix?>treeNodeBox',replacements);
			//--(Begin)-->empty parent_id default columns values
			data = data.replace(/(\[columns_default\]\[parent_id\] *[^<>]* value=)(["'0-9a-zA-Z]*)/ig, "$1\"\"");
			//--(End)-->empty parent_id default columns values

			data+='</li>';

			if (elmLi.innerHTML.indexOf('<ul>')>=0) {
				elmLi.innerHTML=<?php echo $this->_prefix?>Replace(elmLi.innerHTML,'<ul>','<ul>'+data);
			} else {
				data='<ul>'+data+'</ul>';
				elmLi.innerHTML+=data;
			}
		}
		
		function <?php echo $this->_prefix?>ParseTemplate(boxContainerId,tempBoxId,replacements) {
			var items_borad=document.getElementById(boxContainerId);
			var template_item_box=document.getElementById(tempBoxId).innerHTML;
			var key;
			
			replacements['&lt;']='<';
			replacements['&gt;']='>';
			replacements['&nbsp;']=' ';
			
			for (key in replacements) {
				//if (document.all) mkey='"'+key+'"';
				var myregexp = new RegExp(key, "gmi");
				template_item_box=template_item_box.replace(myregexp,replacements[key]);
			}
			
			myregexp = new RegExp("%[^ %{}]*%", "gmi");
			if (document.all && 1==0) {
				template_item_box=template_item_box.replace(myregexp,'');
				template_item_box=template_item_box.replace(/(id=)([^ ><]*)/ig, "$1$2");
				template_item_box=template_item_box.replace(/(name=)([^ ><]*)/ig, "$1\"$2\"");
				template_item_box=template_item_box.replace(/(value= *)(name)/ig, "$2");
			} else {
				template_item_box=template_item_box.replace(myregexp,'');
			}
			return template_item_box;
		}
		
		function <?php echo $this->_prefix?>AddNewBox(boxContainerId,tempBoxId,baseName,prefix) {
			var item_number=0;
			var element_name;
			var element;
			
			do {
				item_number++;
				element_name=baseName+'['+item_number+'][columns][id]';
				element=document.product_form.elements[element_name];
			} while (element);
		
			template_item_box=<?php echo $this->_prefix?>ParseTemplate(item_number,boxContainerId,tempBoxId,baseName,prefix);
	
			items_borad.innerHTML=items_borad.innerHTML+template_item_box;
		}
		
		function <?php echo $this->_prefix?>DeleteNode(itemNumber) {
			if (<?php echo $this->_prefix?>ConfimationMessage('<?php echo $this->_strings['deleteConfirm']?>')) {
				var nodeItem=document.getElementById('<?php echo $this->_prefix?>Row['+itemNumber+'][item]');
				if (nodeItem) {
					var parentNode=nodeItem.parentNode;
					if (parentNode.removeChild(nodeItem)) {
						var columnsValues=new Array();
						var prevColumnsValues=new Array();
						columnsValues['<?php echo $this->colnId?>']=itemNumber;
						<?php echo $this->_prefix?>AddChanges('delete',columnsValues,null);
						if (parentNode.getElementsByTagName('li').length<1) {
							var parentItemNumber=<?php echo $this->_prefix?>GetRowIdById(parentNode.parentNode.id);
							var parentOpenCloseButton=document.getElementById('<?php echo $this->_prefix?>Row['+parentItemNumber+'][openCloseButton]');
							parentOpenCloseButton.innerHTML='';
						}
					}
				}
			}
			return false;
		}
		
		
		function <?php echo $this->_prefix?>GetRowIdById(id) {
			var myregexp = /[^\[\]]*\[([^\[\]]*)\].*/;
			var match = myregexp.exec(id);
			if (match != null && match.length > 1) {
				return match[1];
			} else {
				return  false;
			}
		}
		
		
		function <?php echo $this->_prefix?>SelectRow(object,auto) {
			var selectedRowId=<?php echo $this->_prefix?>GetRowIdById(object.id);
			var baseName=object.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$1[$2]");
			<?php echo $this->_prefix?>OnSelectRow(object,selectedRowId,baseName,'<?php echo $this->_displayMode?>',auto);
			
			<?php if ($this->_highlightParentNodesEnabled==true) { ?>
				
				<?php echo $this->_prefix?>HighlightParents(object);
				/*
				selectedTreeItemPath
				sendSelectRow(object);
				*/
			<?php }?>
			
			return true;
		}

		var <?php echo $this->_prefix?>SelectedRows=new Array();

		function <?php echo $this->_prefix?>OnSelectRow(object,selectedRowId,baseName,displayMode,auto) {
			var titleElm=document.getElementById(baseName+'[title]');
			var obj=document.getElementById(baseName+'[item]');
			var keyVar;

			if (object.checked) {
				var rowInfo=new Array();
				rowInfo['id']=selectedRowId;
				rowInfo['title']=titleElm.innerHTML;
				rowInfo['item']=obj;
				 <?php echo $this->_prefix?>SelectedRows[selectedRowId]=rowInfo;
			} else {
				for ( keyVar in  <?php echo $this->_prefix?>SelectedRows ) {
					if ( <?php echo $this->_prefix?>SelectedRows[keyVar]['id']==selectedRowId)
						 <?php echo $this->_prefix?>SelectedRows.splice(keyVar,1);
				}
			}
			
			if (!auto) {
				<?php if (!empty($this->_jsFunctionOnSelectRow)) {?>
					<?php echo $this->_jsFunctionOnSelectRow?>(object,selectedRowId,baseName,displayMode);
				<?php }?>
			}
		}
		
		
		function <?php echo $this->_prefix?>UpdateSelectedRowsArray(baseName)
		{
			if (baseName=='') baseName='<?php echo $this->_prefix?>Row';
			var all = <?php echo $this->_prefix?>GetElements(document);
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];
				if ((elm.type=='checkbox' || elm.type=='radio') && elm.id.match(/myTreeRow\[.*/i)) 
				if (elm.checked) {
					//elm.checked=true;
					<?php echo $this->_prefix?>SelectRow(elm);
				}
			}
		}
		
		/* selectedRowsId is like ",5,43,35,"*/
		function <?php echo $this->_prefix?>SelectRows(selectedRowsId) {
			selectedRowsId=','+selectedRowsId+',';
			var all = <?php echo $this->_prefix?>GetElements(document);
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];

				if (elm.type=='checkbox' || elm.type=='radio') {
					rowId=<?php echo $this->_prefix?>GetRowIdById(elm.id);
					
					if (selectedRowsId.indexOf(','+rowId+',')>=0) {
						elm.checked=true;
						<?php echo $this->_prefix?>SelectRow(elm,true);
					}
				}
			}
		}
		
		
		//--(Begin)-->changing parent id visually
		var <?php echo $this->_prefix?>SelectingNodeNewParentElm;
		
		function <?php echo $this->_prefix?>OnSelectingNodeNewParent(elm) {
			<?php echo $this->_prefix?>ToggleSelectingNodeNewParentDisplay('show');
			<?php echo $this->_prefix?>SelectingNodeNewParentElm=elm;
		}
		
		function <?php echo $this->_prefix?>OnSelectNodeNewParent(elm) {
			<?php echo $this->_prefix?>ToggleSelectingNodeNewParentDisplay('hide');
			<?php echo $this->_prefix?>SelectingNodeNewParentElm.checked=false;
			
			var itemNumber=<?php echo $this->_prefix?>GetRowIdById(<?php echo $this->_prefix?>SelectingNodeNewParentElm.parentNode.id);
			var parentIdFieldId='<?php echo $this->_prefix?>Row['+itemNumber+'][columns][<?php echo $this->colnParentId?>]';
			document.getElementById(parentIdFieldId).value=elm.value;
			<?php echo $this->_prefix?>SelectingNodeNewParentElm=null;
			elm.checked=false;
		}
		
		function <?php echo $this->_prefix?>ToggleSelectingNodeNewParentDisplay(mode) {
			elements=<?php echo $this->_prefix?>GetElements(document.getElementById('<?php echo $this->_mainId?>'));
			var radioButton;
			if (elements) {
				for (var e = 0; e < elements.length; e++) {
					elm = elements[e];
					if (elm.name=='<?php echo $this->_prefix?>NodeNewParent') {
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
		function <?php echo $this->_prefix?>MoveNode(itemNumber,x){
			var currentNode=document.getElementById('<?php echo $this->_prefix?>Row['+itemNumber+'][item]');
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
						columnsValues['___near_node_id']=<?php echo $this->_prefix?>GetRowIdById(nearNode.id);
						columnsValues['<?php echo $this->colnId?>']=itemNumber;
						if (x=='up') action=action+'Up';
						if (x=='down') action=action+'Down';
						<?php echo $this->_prefix?>AddChanges(action,columnsValues,prevColumnsValues);
					}
				//--(End)-->log change in order to save to database later
			} else
				alert('<?php echo $this->_strings['selectRowFirst']?>');
		}
		//--(End)-->Move up and down node in same level
		</script>
		<?php }?>
	<?php
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
			<?php echo $this->_prefix?>SimpleSlider=new simpleSliderClass();
			<?php
			$slidesInfo=array();
			foreach ($rows as $row) {
				$baseName=$this->_prefix.'Row['.$row[$this->colnId].']';
				$slidesInfo[$baseName.'[childs]']=array(
					'buttonId'=>array($baseName.'[openCloseButton]',$baseName.'[titleLink]'),
					'iconId'=>$baseName.'[openCloseIcon]',
					'iconCloseSrc'=>$this->_images['openIcon']['path'],
					'iconOpenSrc'=>$this->_images['closeIcon']['path'],
				);
			};
			?>
			<?php echo $this->_prefix?>SimpleSlider.slidesInfo=<?php echo cmfcHtml::phpToJavascript($slidesInfo)?>;
			<?php echo $this->_prefix?>SimpleSlider.instanceName='<?php echo $this->_prefix?>SimpleSlider';
			<?php echo $this->_prefix?>SimpleSlider.prepareOnLoad();
		</script>
	<?
	}
	
	
	function printOnloadScript() {?>
		<script language="javascript" type="text/javascript">
			function <?php echo $this->_prefix?>OnloadFunctions()
			{
				<?php if ($this->_autoCollapseAtStart) {?>
					<?php echo $this->_prefix?>CollapseAll('<?php echo $this->_mainId?>');
				<?php }?>
				<?php if (!empty($this->_jsFunctionOnSelectRows)) {?>
					<?php echo $this->_jsFunctionOnSelectRows?>();
				<?php }?>
				//check_selected_tree_items('selected_tree_items_id');
			}
			 <?php echo $this->_prefix?>OnloadFunctions();
			/*window.onload=<?php echo $this->_prefix?>OnloadFunctions();*/
		</script>
	<?php
	}

	
	function printTemplate() {
		foreach ($this->_nodeHtmlTemplates as $tempName=>$tempHtml) {
		?>
			<div id="<?php echo $this->_prefix?><?php echo $tempName?>" style="display:none">
			<?php echo htmlspecialchars($tempHtml)?>
			</div>
		<?php
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

		$displayMode=$this->_displayMode;/*view ; singleSelection ; multiSelection ; view_simple*/
		if ($displayMode!='edit' and !$this->_showStartNode) {
			$this->nextRow();
		}
		
		$invisibleRows=array();
	?>	
		<!-- (BEGIN) : Tree -->
		<div id="<?php echo $this->_prefix?>ChangesContainer" style="display:hidden"></div>
		
		<ul id="<?php echo $this->_mainId?>" class="<?php echo $this->_prefix?>">
		<?php
		while ($row = $this->nextRow()) {
			$rowName=$this->_prefix."Row[".$row[$this->colnId]."]";
			
			if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1)
				$hasChild=true; else $hasChild=false;
			
			#--(Begin)-->Show only selected nodes! and their childs
			if (!empty($this->_visibleNodeIds)) {
				$row[$this->colnVisible]=0;
				foreach ($this->_visibleNodeIds as $nodeId) {
					if ($row[$this->colnId]==$nodeId) {
						$row[$this->colnVisible]=1;
						break;
					} else{ 
					
						#--(Begin)-->check if it has any forced visible childs
						if ($hasChild) {
							if ($this->isChildOf($nodeId,$row[$this->colnId])) {
								$row[$this->colnVisible]=1;
								break;
							}
						}
						#--(End)-->check if it has any forced visible childs
						
						#--(Begin)-->check if it has any forced visible parent
						if ($this->isChildOf($row[$this->colnId],$nodeId)) {
							$row[$this->colnVisible]=1;
							break;
						}
						#--(End)-->check if it has any forced visible parent
					}
				}
			}
			#--(End)-->Show only selected nodes! and their childs
				
			#--(Begin)-->don't show invisible nodes and their childs
			$hasVisibleChilds=true;
			if ($this->_nodeVisibilityEnabled) {
				if (is_array($invisibleRows)) {

					$thisIsInvisibleChild=false;
					foreach ($invisibleRows as $invisibleRow) {
						
						if ($row[$this->colnLeftNumber]>$invisibleRow[$this->colnLeftNumber] and $row[$this->colnRightNumber]<$invisibleRow[$this->colnRightNumber]) {
							$row[$this->colnVisible]=0;
							$thisIsInvisibleChild=true;
							break;
						}
					}
				}
				if ($row[$this->colnVisible]!=1) {
					if (!$thisIsInvisibleChild and $hasChild) {
						$invisibleRows[]=$row;
					} else {
					}
					continue;
				}
				
				#--(Begin)-->check if it has any visible childs
				if ($hasChild) {
					$sqlQuery="SELECT count(*) as'total' FROM ".$this->tableName." WHERE ".$this->colnLevelNumber."=".($row[$this->colnLevelNumber]+1)." AND ".$this->colnLeftNumber.">".$row[$this->colnLeftNumber]." AND ".$this->colnRightNumber."<".$row[$this->colnRightNumber]." AND ".$this->colnVisible."=1";
					$totalChilds=cmfcMySql::getColumnValueCustom($sqlQuery,'total');
					
					if (!$totalChilds>0) {
						$hasVisibleChilds=false;
						$hasChild=false;
					}
				}
				#--(End)-->check if it has any visible childs
			}
			#--(End)-->don't show invisible nodes and their childs
			
			$depth=$row[$this->colnLevelNumber];

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
			if ($hasChild) {
				$className=$this->_prefix.'ParentNode';
			} else {
				$className=$this->_prefix.'ChildNode';
			}
			if ($depth==0 OR $depth==$this->_displayFromLevel) {
				$className.=' '.$this->_prefix.'TopNode';
			}
			echo '<li id="'.$rowName.'[item]" class="'.$className.'">';

			#--(Begin)-->Manipulate row columns via callback function
			if (is_callable($this->_cbFuncManipulateRowColumnsBeforeDraw))
				$row=call_user_func_array($this->_cbFuncManipulateRowColumnsBeforeDraw,array(&$this,$row,$hasChild));
			#--(End)-->Manipulate row columns via callback function
			
			#--(Begin)-->Generate additional items
			$addHasChild=$hasChild;
			if (is_callable($this->_cbFuncGetAdditionalItems)) {
				$additionalItemsHtml=call_user_func_array($this->_cbFuncGetAdditionalItems,array(&$this,$row,$hasChild));
				if (!empty($additionalItemsHtml)) {
					$addHasChild=true;
				}
			}
			//$additionalItemsHtml='<li><a href="">'.'Ø³Ù„Ø§Ù…'.'</a></li>';
			#--(End)-->Generate additional items

			echo $this->_useTemplate($row,$addHasChild);
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
				echo '<ul id="'.$rowName.'[childs]">'."\n";
				#--(Begin)-->Append additional items
				if (!empty($additionalItemsHtml)) {
					echo $additionalItemsHtml."\n";
				}
				#--(End)-->Append additional items
			} else {
				#--(Begin)-->Append additional items
				if (!empty($additionalItemsHtml)) {
					echo '<ul id="'.$rowName.'[childs]">'.$additionalItemsHtml.'</ul>'."\n";
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
		<?php if ($displayMode=='edit') { ?>
			<script language="javascript" type="text/javascript" defer="defer">
				document.getElementById('<?php echo $this->_prefix?>Row[<?php echo $this->_startNodeId?>][delete]').innerHTML='';
				document.getElementById('<?php echo $this->_prefix?>Row[<?php echo $this->_startNodeId?>][editSave]').innerHTML='';
				document.getElementById('<?php echo $this->_prefix?>Row[<?php echo $this->_startNodeId?>][moveUp]').innerHTML='';
				document.getElementById('<?php echo $this->_prefix?>Row[<?php echo $this->_startNodeId?>][moveDown]').innerHTML='';
			</script>
		<?php }?>
		<!-- (END) : Tree -->
	<?php
		//print_r($invisibleRows);
	}


	function printChangesBoard() {?>
		<div id="<?php echo $this->_prefix?>ChangesBoard"></div>
	<?php }
	
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
		.<?php echo $this->_prefix?>HighlightedItem span span{ 
			font-weight:bold;
			color:red;
			text-decoration:none;
		 }
		.<?php echo $this->_prefix?>NormalItem span span{ 
			font-weight:inherit;
			color:inherit;
			text-decoration:inherit;
		 }
		.<?php echo $this->_prefix?>OpenCloseButton{ 
			font-weight:normal;
			color:inherit;
			text-decoration:none;
			font-family: "Courier New", Courier, monospace;
			font-size:15px;
		 }
		 
		.<?php echo $this->_prefix?> {
			
		}
		
		.<?php echo $this->_prefix?> li {
			margin-top:8px;
		}
		
		.<?php echo $this->_prefix?>EditBox {
			padding:4px;
			margin-top:3px;
			margin-right:10px;
			border:1px dotted gray;
			width:300px
		}
		</style>
	<?php
	}
	
	/**
	* @type string //vertical,horizontalCascade
	* @lang string //fa,en
	*/
	function printViewStyles($type='vertical',$lang='fa') {?>
		<style>
		<?php if ($type='vertical') {?>
			.<?php echo $this->_prefix?>HighlightTreeItem{ 
				font-weight:bold;
				color:red;
				text-decoration:none;
			 }
			 
			.<?php echo $this->_prefix?>OpenCloseButton{ 
				font-weight:normal;
				color:inherit;
				text-decoration:none;
				font-family: "Courier New", Courier, monospace;
				font-size:15px;
			 }
			
			.<?php echo $this->_prefix?> a{
			
			}
			
			.<?php echo $this->_prefix?> li {
				padding-right:40px;
				_padding-right:inherit;
				
				padding-left:40px;
				_padding-left:inherit;
					
				margin-top:5px;
				list-style-type: none;
			
				margin-right:-50px;
				_margin-right:inherit;
			
				margin-left:-50px;
				_margin-left:inherit;
				
				font-weight:bold;
			}
			
			.<?php echo $this->_prefix?> li ul {
				margin-right:0px;
				margin-left:0px;
			}
			
			.<?php echo $this->_prefix?> li ul li {
				font-weight:normal;
			}
			
			.<?php echo $this->_prefix?>ParentNode {
				margin-top:8px;
				margin-right:-40px;
				_margin-right:0px;
				
				margin-left:-40px;
				_margin-left:0px;
				
				padding-right:10px;
				
			}
			
			.<?php echo $this->_prefix?>ChildNode {
			
				margin-top:0px;
				padding-top:3px;
				padding-bottom:3px;
				margin-right:-50px;
				padding-right:22px;
				
				margin-left:-50px;
				padding-left:22px;
			}
			
			.<?php echo $this->_prefix?>TopNode{
				font-weight:bold;
			}
				
			<?php if ($lang=='en' or $lang=='english') {?>
				.<?php echo $this->_prefix?>ParentNode {
					margin-left:-40px;
					_margin-left:0px;
					padding-left:10px;
				}
				
				.<?php echo $this->_prefix?>ChildNode {
					margin-left:-50px;
					padding-left:22px;
				}
			<?php }?>
		<?php }?>
		
		<?php if ($type='horizontalCascade') {?>
			/*Credits: Dynamic Drive CSS Library */
			/*URL: http://www.dynamicdrive.com/style/ */
			.<?php echo $this->_prefix?> {
			/*
				float:right;
				position:relative;
				padding-top:3px;
				margin-right:30px;
				_margin-right:15px;
				width:690px;
			*/
			}
			.<?php echo $this->_prefix?> ul{
				margin: 0;
				padding: 0;
				list-style-type: none;
			}
			
			/*Top level list items*/
			.<?php echo $this->_prefix?> ul li{
				position: relative;
				display: inline;
				float: right;
				background-color: transparent; /*overall menu background color*/
			}
			
			/*Top level menu link items style*/
			.<?php echo $this->_prefix?> ul li a{
				display: block;
				width: auto; /*Width of top level menu link items*/
				padding: 1px 8px;
				border-right-width: 0;
				text-decoration: none;
				color: navy;
				border-left:1px solid #ffffff;
				text-align:center;
				color:#666666;
				
			}
			
			
			.<?php echo $this->_prefix?> ul li a:hover, .<?php echo $this->_prefix?> ul li a:active{
				color:#FFFFFF;
			}
				
			/*1st sub level menu*/
			.<?php echo $this->_prefix?> ul li ul{
				right: 0;
				position: absolute;
				top: 1em; /* no need to change, as true value set by script */
				display: block;
				visibility: hidden;
				background-color: #496b98;
				border:1px solid #666666;
			/*
				opacity:.50;
				filter: alpha(opacity=50); 
				-moz-opacity: 0.5;
			*/
				padding-top:5px;
				padding-bottom:5px;	
				z-index:1;
			}
			
			
			/*Sub level menu list items (undo style from Top level List Items)*/
			.<?php echo $this->_prefix?> ul li ul li{
				display: list-item;
				float: none;
				margin-bottom:2px;
				margin-top:2px;
			}
			
			/*All subsequent sub menu levels offset after 1st level sub menu */
			.<?php echo $this->_prefix?> ul li ul li ul{ 
				right: 159px; /* no need to change, as true value set by script */
				top: 0;
			}
			
			/* Sub level menu links style */
			.<?php echo $this->_prefix?> ul li ul li a{
				display: block;
				width: 160px; /*width of sub menu levels*/
				color: navy;
				text-decoration: none;
				padding: 1px 5px;
				border:none;
				text-align:right;
				color:#000000;
			}
			
			.<?php echo $this->_prefix?> ul li ul li a:hover, .<?php echo $this->_prefix?> ul li ul li a:active{
				color:#999999;
			}
			
			
			/*Background image for top level menu list links */
			.<?php echo $this->_prefix?> .mainfoldericon{
			/*
				background-image: url(../images/menu_arrow_down.gif);
				background-position:2% 50%;
				background-repeat:no-repeat;
			*/
			}
			
			/*Background image for subsequent level menu list links */
			.<?php echo $this->_prefix?> .subfoldericon{
				background-image: url(../images/menu_arrow_left.gif);
				background-position:2% 50%;
				background-repeat:no-repeat;
			}
			
			
			* html p#iepara{ /*For a paragraph (if any) that immediately follows suckertree menu, add 1em top spacing between the two in IE*/
				padding-top: 1em;
			}
				
			/* Holly Hack for IE \*/
			* html .<?php echo $this->_prefix?> ul li { float: right; height: 1%; }
			/* End */
			
			<?php if ($lang=='en' or $lang=='english') {?>
				/*Top level list items*/
				.<?php echo $this->_prefix?> ul li{
					float: left;
				}
				
				/*Top level menu link items style*/
				.<?php echo $this->_prefix?> ul li a{
					display: block;
					width: auto; /*Width of top level menu link items*/
					padding: 1px 8px;
					border-left-width: 0;
					text-decoration: none;
					color: navy;
					border-right:1px solid #ffffff;
					text-align:center;
					color:#666666;
					
				}
				
				/*1st sub level menu*/
				.<?php echo $this->_prefix?> ul li ul{
					left: 0;
					width:170px;
				}
				
				.<?php echo $this->_prefix?> .subfoldericon{
					background-image: url(../images/menu_arrow_right.gif);
					background-position:98% 50%;
				}
				
				/*All subsequent sub menu levels offset after 1st level sub menu */
				.<?php echo $this->_prefix?> ul li ul li ul{ 
					left: 159px; /* no need to change, as true value set by script */
					top: 0;
				}
				
				/* Sub level menu links style */
				.<?php echo $this->_prefix?> ul li ul li a{
					padding: 1px 5px;
					text-align:left;
				}
				
				/* Holly Hack for IE \*/
				* html .<?php echo $this->_prefix?> ul li { float: left; height: 1%; }
				/* End */
			<?php }?>
		<?php }?>
		</style>
	<?php }
	
	function printViewJavascripts($type,$lang='fa',$prefix='hierarchical') {?>
		<script type="text/javascript">
		<?php if ($type='horizontalCascade') {?>
			function <?php echo $prefix?>isChildOf(child,parent) {
				var curElm=child;
				if (child)
				do {
					curElm=curElm.parentNode;
					if (curElm==parent && parent) return true;
				} while (curElm!=parent && curElm && parent);
				return false;
			}
			
			function <?php echo $prefix?>getMenuMainParent(child) {
				var curElm=child;
				do {
					if (curElm.tagName=='UL') {
						if (curElm.parentNode)
							if (curElm.parentNode.tagName!='LI') return curElm;
					}
					curElm=curElm.parentNode;
				} while (curElm);
			
				return false;
			}
			
			function <?php echo $prefix?>getTopMenu(child) {
				var menuMainParent=<?php echo $prefix?>getMenuMainParent(child);
				var curElm=child;
				do {
					if (curElm.parentNode==menuMainParent) return curElm;
					curElm=curElm.parentNode;
				} while (curElm);
			
				return false;
			}
			
			function <?php echo $prefix?>hideSubMenus(elm) {
				//elm=<?php echo $prefix?>getTopMenu(elm);
				var ultags=elm.getElementsByTagName("ul");
				for (var t=0; t<ultags.length; t++) {
					ultags[t].style.visibility="hidden";
					if (ultags[t].getElementsByTagName("ul")[0]) {
						ultags[t].getElementsByTagName("ul")[0].style.visibility="hidden";
					}
				}
			}
		
			function <?php echo $prefix?>arrayAppendUnique(myArray,newItem) {
				var newX=0;
				var newArray=new Array();;
				if (!myArray) myArray=new Array();
				for (x in myArray) {
					if (myArray[x]!=newItem) {
						newArray[newX]=myArray[x]
						newX++;
					}
				}
				newArray[newArray.length]=newItem;
				return newArray;
			}
			
			function <?php echo $prefix?>arrayGetLastItem(arr) {
				if (arr) {
					return arr[arr.length-1];
				}
				return false;
			}
			
			function <?php echo $prefix?>arrayGetBeforeLastItem(arr) {
				if (arr.length>1)
				if (arr) {
					return arr[arr.length-2];
				}
				return false;
			}
			
			function <?php echo $prefix?>hideElements(curElm,arr,from,to) {
				var x=0;
				var newX=0;
				var newX;
				var newArray=arr;
				for (x in arr) {
					if (x>=from-1 && x<=to-1 && arr[x])
						if (!<?php echo $prefix?>isChildOf(curElm,arr[x]) && curElm!=arr[x]) {
							arr[x].getElementsByTagName("ul")[0].style.visibility="hidden";
							<?php echo $prefix?>hideSubMenus(arr[x]);
							newArray[newX]=arr[x];
							newX++;
						}
				}
				return newArray;
			}
			
			
			function <?php echo $prefix?>addMessage(msg) {
				var elm=document.getElementById('msgBoard');
				elm.innerHTML+=msg+'<br />';
			}
			
			var <?php echo $prefix?>suckerTreeTimeoutFuncId=new Array();
			var <?php echo $prefix?>suckerTreeFirstElements=new Array();
			var <?php echo $prefix?>suckerTreeFirstEventOrderCalled=new Array();
			
			//SuckerTree Horizontal Menu (Sept 14th, 06)
			//By Dynamic Drive: http://www.dynamicdrive.com/style/
			function <?php echo $prefix?>suckerTreeBuildSubmenusHorizontal(menuids,direction){
				for (var i=0; i<menuids.length; i++){
					var ultags=document.getElementById(menuids[i]).getElementsByTagName("ul")
					for (var t=0; t<ultags.length; t++){
						if (ultags[t].parentNode.parentNode.id==menuids[i]){ //if this is a first level submenu
							ultags[t].style.top=ultags[t].parentNode.offsetHeight+"px" //dynamically position first level submenus to be height of main menu item
							ultags[t].parentNode.getElementsByTagName("a")[0].className="mainfoldericon"
						} else { //else if this is a sub level menu (ul)
							if (direction=='rtl') {
								//if (ultags[t].style.right=='') ultags[t].style.right=ultags[t].style.left;
								ultags[t].style.right=ultags[t-1].getElementsByTagName("a")[0].offsetWidth+"px" //position menu to the left of menu item that activated it
							} else {
								//if (ultags[t].style.left=='') ultags[t].style.right=ultags[t].style.right;
								ultags[t].style.left=ultags[t-1].getElementsByTagName("a")[0].offsetWidth+"px" //position menu to the right of menu item that activated it
							}
							ultags[t].parentNode.getElementsByTagName("a")[0].className="subfoldericon"
						}
						ultags[t].parentNode.onmouseover=function(e){
							//this.getElementsByTagName("ul")[0].style.visibility="visible";
							
							//--(Begin)-->clear delay hiding
							var topMenu=<?php echo $prefix?>getTopMenu(this);
							if (<?php echo $prefix?>suckerTreeTimeoutFuncId) {
								//--(Begin)-->hide previous top menu and its sub menus
								var lastOverItem=<?php echo $prefix?>arrayGetLastItem(<?php echo $prefix?>suckerTreeFirstElements);
								if (lastOverItem) {
									var lastOverItemTopMenu=<?php echo $prefix?>getTopMenu(lastOverItem);
									if (lastOverItemTopMenu!=topMenu) <?php echo $prefix?>hideSubMenus(lastOverItemTopMenu); 
								}
								//--(End)-->hide previous top menu and its sub menus
		
								clearTimeout(<?php echo $prefix?>suckerTreeTimeoutFuncId);
								<?php echo $prefix?>suckerTreeTimeoutFuncId=null;
							}
							//--(End)-->clear delay hiding
							
							//--(Begin)-->keep original event elements
							if (<?php echo $prefix?>suckerTreeFirstEventOrderCalled!=true) {
								<?php echo $prefix?>suckerTreeFirstElements=<?php echo $prefix?>arrayAppendUnique(<?php echo $prefix?>suckerTreeFirstElements,this);
								<?php echo $prefix?>suckerTreeFirstEventOrderCalled=true;
							}
							if (this==topMenu) <?php echo $prefix?>suckerTreeFirstEventOrderCalled=false;
							//--(End)-->keep original event elements
							
							//--(Begin)-->delay showing focused submenu and hiding previous foucused submenu
							timeOut=500;
							if (this==topMenu && topMenu.getElementsByTagName("ul")[0].style.visibility=="hidden") timeOut=1;
							var elm=this;
							setTimeout(function() {
								var curElm=<?php echo $prefix?>arrayGetLastItem(<?php echo $prefix?>suckerTreeFirstElements);
								var prevElm=<?php echo $prefix?>arrayGetBeforeLastItem(<?php echo $prefix?>suckerTreeFirstElements);
								
								if (curElm==elm) {
									elm.getElementsByTagName("ul")[0].style.visibility="visible";
									
									if (prevElm)
										<?php echo $prefix?>suckerTreeFirstElements=<?php echo $prefix?>hideElements(curElm,<?php echo $prefix?>suckerTreeFirstElements,1,<?php echo $prefix?>suckerTreeFirstElements.length-1);
								}
							},timeOut);
							//--(End)-->delay showing focused submenu and hiding previous foucused submenu
						}
						ultags[t].parentNode.onmouseout=function(e){
						
							//--(Begin)-->fetch require elements
							var menuMainParent=<?php echo $prefix?>getMenuMainParent(this);
							var topMenu=<?php echo $prefix?>getTopMenu(this);
							var elm=this;
							if (!e) var e = window.event;
							var relTarget = e.relatedTarget || e.toElement;
							var target = (window.event) ? e.srcElement : e.target;
							//--(End)-->fetch require elements
							
							//<?php echo $prefix?>addMessage(this.id+" : over");
							if (<?php echo $prefix?>isChildOf(relTarget,menuMainParent)) {
								if (!<?php echo $prefix?>arrayGetLastItem(<?php echo $prefix?>suckerTreeFirstElements)) {
									this.getElementsByTagName("ul")[0].style.visibility="hidden";
									<?php echo $prefix?>hideSubMenus(this);
								} else if (topMenu != <?php echo $prefix?>getTopMenu(relTarget)) {
									this.getElementsByTagName("ul")[0].style.visibility="hidden";
									<?php echo $prefix?>hideSubMenus(this);
									<?php echo $prefix?>suckerTreeFirstElements=<?php echo $prefix?>hideElements(this,<?php echo $prefix?>suckerTreeFirstElements,1,<?php echo $prefix?>suckerTreeFirstElements.length-1);
								}
							} else {
								if (<?php echo $prefix?>suckerTreeTimeoutFuncId)
									clearTimeout(<?php echo $prefix?>suckerTreeTimeoutFuncId);
								<?php echo $prefix?>suckerTreeTimeoutFuncId=setTimeout(function() {
									<?php echo $prefix?>hideSubMenus(elm);
									<?php echo $prefix?>suckerTreeFirstElements=<?php echo $prefix?>hideElements(elm,<?php echo $prefix?>suckerTreeFirstElements,1,<?php echo $prefix?>suckerTreeFirstElements.length-1);	
								},500);
							}
							
						};
					}
				  }
			}
		
			
			function <?php echo $prefix?>suckerTreeBuildOnLoad(menuids,direction) {
				if (window.addEventListener)
					window.addEventListener("load", function() { <?php echo $prefix?>suckerTreeBuildSubmenusHorizontal(menuids,direction);}, false)
				else if (window.attachEvent)
					window.attachEvent("onload", function() { <?php echo $prefix?>suckerTreeBuildSubmenusHorizontal(menuids,direction);})
			}
		<?php }?>	
		</script>
	<?php }
	
	/**
	* @$ids //array('myTreeMenu'=>array('lang'=>'fa'))
	*/
	function printPrepareViewJavascripts($type,$ids,$prefix='hierarchical') {?>
		<script type="text/javascript">
			<?php if ($type='horizontalCascade') {
				foreach ($ids as $id=>$idInfo) {
					$lang=$idInfo['lang'];
				}
			?>
			var _____menuids=["<?php echo $id?>"] //Enter id(s) of SuckerTree UL menus, separated by commas
			var _____menuDirection='<?php echo ($lang=='fa')?'rtl':'ltr'?>';
			<?php echo $prefix?>suckerTreeBuildOnLoad(_____menuids,_____menuDirection);
			<?php  }?>
		</script>
	<?php }
}
