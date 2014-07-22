<?php
require_once(realpath(dirname(__FILE__).'/../../requirements/javascript.class.inc.php'));

/**
* @todo
*		1.adding printedParts var to monitoring printing html arrangement, it will
*			prevenintg for example printing Tree before printing its javascript.
*/
class cmfcHierarchicalSystemDbBetaDecoratorHtmlBulletListTree extends cmfcHierarchicalSystemDbBeta {
	var $_prefix='myTree';
	var $_mainId='myTreeRoot';
	var $_nodeHtmlTemplates;
	var $_columnsNames;
	var $_titleColumnName;
	var $_displayMode;
	/**
	* @desc instance of cmfcHierarchicalSystemJavscriptFunctions class
	*		for encapsulating javascript functions
	*/
	var $_js;
	/**
	* @desc more details about displaying tree such as which functionalities
	* 		should be availble in edit mode.
	* @exanple array('edit'=>'false','delete'=>'false','mode'=>true)
	*/
	var $_displayModeDetails;
	var $_images=array();
	var $_selectedRows;
	var $_showStartNode=false;
	
//	var $_jsFunctionOnSelectRow;
//	var $_jsFunctionOnSelectRows;
	
	//require in getNodeNestedPositionByRow function
	var $_prevRow;
	var $_autoCollapseAtStart=true;
	var $_startNodeId=null;
	var $_debugModeEnabled=false;
	var $_templates=array();
	var $_cssClassNames=array(
		'level'=>'Level',
		'parent'=>'Parent',
		'child'=>'Child',
	);
	var $_jsObjectInstanceName;
	
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
	
	var $_messagesValue=array(
		CMF_HiSys_DbBeta_Error=>'Unknown error'
	);
	
	
	function setOptions($options) {
		$this->_js=new cmfcHierarchicalSystemJavscriptFunctions();
		$this->_js->setPrefix($this->_prefix);
		$this->_jsObjectInstanceName=$this->_prefix.'Object';
		return parent::setOptions($options);
	}
	
	
	function setOption($name,$value) {
		$result=parent::setOption($name,$value);
		
		switch ($name) {
			case 'prefix':
				$this->_js->setPrefix($this->_prefix);
				$this->_jsObjectInstanceName=$this->_prefix.'Object';
			break;
			
			case 'templateMode':
				$modes=array(
					'onlyCustom','onlyDefault',
					'mergeCustomAndDefault','mergeCustomAndDefaultNecessaryParts','mergeCustomAndDefaultInvisibleParts'
				);
				if (!in_array($value,$modes)) {
					trigger_error("`$templateMode` is not a valid template mode (".__CLASS__."), available modes are : ".implode(' , ',$modes),E_USER_ERROR);
				}
				$this->_templateMode=$value;
			break;
			
			case 'columnsNames':
				if (!in_array($this->_colnParentId,$value))
					$value[]=$this->_colnParentId;
				$this->_columnsNames=$value;
			break;
		}
		return $result;
	}
	
	function _prepareTemplates($displayMode=null) {
		if (is_null($displayMode)) $displayMode=$this->_displayMode;
		
		#--(Begin)-->Defining Buttons Indicators
		if (!empty($this->_images['openIcon']['path'])) {
			$this->_openIndicator=sprintf('<img id="%s" src="%s" style="%s" alt="Open" border="0"/>',
				'%{itemBaseName}%open',
				$this->_images['openIcon']['path'],
				$this->_images['openIcon']['style']
			);
		} else $this->_openIndicator='<span id="%{itemBaseName}open%">[+]</span>';
		if (!empty($this->_images['closeIcon']['path'])) {
			$this->_closeIndicatorr=sprintf('<img id="%s" src="%s" style="%s" alt="Close" border="0"/>',
				'%{itemBaseName}%close',
				$this->_images['closeIcon']['path'],
				$this->_images['closeIcon']['style']
			);
		} else $this->_closeIndicator='<span id="%{itemBaseName}%close">[-]</span>';
		#--(End)-->Defining Buttons Indicators
	
		if ($displayMode=='singleSelect' or $displayMode=='multiSelect') {
			if ($displayMode=='singleSelect') {
				$selectControl='<input style="margin-bottom:0px" name="%{itemBaseName}%[selectedRow][id]" id="%{itemBaseName}%[selected]" type="radio" onchange="%{prefix}%SelectRow(this)" value="%{itemNumber}%" %checked%>&nbsp;';
			} elseif($displayMode=='multiSelect') {
				$selectControl='<input style="margin-bottom:0px" name="%{itemBaseName}%[selected]" id="%{itemBaseName}%[selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" value="true" %checked%>&nbsp;';
			}

			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				<input name="%{itemBaseName}%[id]" id="%{itemBaseName}%[id]" type="hidden" value="%column_id_value%"/>
				<input name="%{itemBaseName}%[parent_id]" id="%{itemBaseName}%[parent_id]" type="hidden" value="%column_parent_id_value%"/>
				<input name="%{itemBaseName}%[flag]" id="%{itemBaseName}%[flag]" type="hidden" value="0"/>
				&raquo;
				<a href="?" id="%{itemBaseName}%[openCloseButton]" onclick="%{jsObjectInstanceName}%.toggleOpenCloseIndicator(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{itemBaseName}%[container]">
					$selectControl
					<span id="%{itemBaseName}%[title]">%title%</span>
				</span>
EOT;
		} elseif ($displayMode=='viewSimple') {
			//$selectControl='<input style="margin-bottom:0px" name="%{item_base_name}%[selected]" id="%{item_base_name}%[selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" value="true" %checked%>&nbsp;';
			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				<a href="%titleLinkUrl%">$selectControl%title%</a>
EOT;
		} else {
			$this->_nodeHtmlTemplates['treeNodeBox']=<<<EOT
				&raquo;
				<a href="?" id="%{itemBaseName}%[openCloseButton]" onclick="%{jsObjectInstanceName}%.toggleOpenCloseIndicator(this,['%{itemBaseName}%open','%{itemBaseName}%close']); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{itemBaseName}%[container]">
					<a href="%titleLinkUrl%"><span id="%{itemBaseName}%[title]">%title%</span></a>
					$nodeHtmlTemplates[treeNodeBox]
				</span>
EOT;
		}
		
		if ($this->_templateMode=='onlyCustom') {
			//$this->_nodeHtmlTemplates=$nodeHtmlTemplates;
		}

		return true;
	}
	
	function _useTemplate($row,$hasChild) {
		$nodeHtmlTemplate=$this->_nodeHtmlTemplates['treeNodeBox'];
		$itemNumber=$row[$this->_colnId];
		
		#--(Begin)-->trigger for opening and closing node
		$openCloseIndicator="";
		if ($hasChild) {
			if ($this->_autoCollapseAtStart) {
				$openCloseIndicator=$this->_openIndicator;
			} else {
				$openCloseIndicator=$this->_closeIndicator;
			}
		}
		#--(End)-->trigger for opening and closing node
		
		#--(Begin)-->replace variables in template with their appropriate values
		if (is_array($this->_selectedRows))
			if  (in_array($row[$this->_colnId],$this->_selectedRows)) {
				$rowSelected='checked';
			}
		
		$titleLinkUrl='javascript:void(0)';
		if (!empty($row[$this->_colnLink]))
			$titleLinkUrl=$row[$this->_colnLink];
			
		$holders=array(  
			"%title%",
			"%{prefix}%",
			"%{jsObjectInstanceName}%",
			"%openCloseIndicator%",
			"%{itemBaseName}%",
			"%{itemNumber}%",
			"%checked%",
			"%titleLinkUrl%",
			"%column_parent_id_value%",
			"%column_id_value%",
			"%column_name_value%"
		);

		$replacements=array(
			$row[$this->_titleColumnName],
			$this->_prefix,
			$this->_jsObjectInstanceName,
			$openCloseIndicator,
			$this->_prefix.'Row['.$itemNumber.']',
			$itemNumber,
			$rowSelected,
			$titleLinkUrl,
			$row[$this->_colnParentId],
			$row[$this->_colnId],
			$row[$this->_colnName]
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
		#--(End)-->replace variables in template with their appropriate values
		
		return $itemHtml;
	}
	
	function getNodeNestedPositionByRow($row) {
	/*
		$depth=$row['depth'];
		$lastDepth=$this->_prevRow['depth'];
		if (!isset($lastDepth)) $lastDepth=$depth;
		
		if ($row[$this->_colnRightNumber]-$row[$this->_colnLeftNumber]>1) {
			$status='hasChild|';
		}
		$status.='normal';
		if ($depth<$lastDepth) {//parentEnd
			$status.='parentEnd';
		}
		$this->_prevRow=$row;
	*/
	}
	
	function printJavaScripts() {
		$this->_js->printJsFunctions(array(
			'StrReplace',
			'GetElements',
			'GetElementsByName',
			'GetElementsByIdPattern',
			'ConfimationMessage',
			'ToggleBullet',
			'CollapseAll',
			'ToggleDisplayStyle',
			'ToggleTabsDisplayStyle'
		));
	?>
		  
		<script language="javascript" type="text/javascript">
			function <?=$this->_prefix?>Class () {
				this.toggleOpenCloseIndicator=function(elm,indicators) {
					<?=$this->_prefix?>ToggleTabsDisplayStyle('',indicators,'displayNextOne');
					<?=$this->_prefix?>ToggleBullet(elm);
				}
				
				this.observers=<?=$this->_js->phpParamToJs($this->_observers)?>;
				this.notifyObservers=function (myEvent,params) {
					<?=$this->_prefix?>NotifyObservers(myEvent,params,<?=$this->_prefix?>Observers);
				}
				
				this.getTreeNodePath=function (id) {
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
				
				this.getTreeNodePathAsTitle=function (id) {
					var path=<?=$this->_prefix?>GetTreeNodePath(id);
					var pathStr='';
					var comma='';
					for (var x in path) {
						pathStr=path[x]['title']+comma+pathStr;
						if (comma=='') comma=' « ';
					}
					return pathStr;
				}
				
				this.checkSelectedTreeItems=function (controlName)
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
								this.highlightParents(myitem)
							}
						}
				}
				
				this.getRowIdById=function (id) {
					var myregexp = /[^\[\]]*\[([^\[\]]*)\].*/;
					var match = myregexp.exec(id);
					if (match != null && match.length > 1) {
						return match[1];
					} else {
						return  false;
					}
				}
				
				
				this.selectRow=function (object,auto) {
					var selectedRowId=<?=$this->_prefix?>GetRowIdById(object.id);
					var baseName=object.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$1[$2]");
		
					this.onSelectRow(object,selectedRowId,baseName,'<?=$this->_displayMode?>',auto);
					
					return true;
					/*
					<?=$this->_prefix?>HighlightParents(object);
					selectedTreeItemPath
					sendSelectRow(object);
					*/
				}
		
				this.selectedRows=new Array();
		
				this.onSelectRow=function (object,selectedRowId,baseName,displayMode,auto) {
					var titleElm=document.getElementById(baseName+'[title]');
					var keyVar;
		
					if (object.checked) {
						var rowInfo=new Array();
						rowInfo['id']=selectedRowId;
						rowInfo['title']=titleElm.innerHTML;
						this.selectedRows[selectedRowId]=rowInfo;
					} else {
						for ( keyVar in  <?=$this->_prefix?>SelectedRows ) {
							if ( this.selectedRows[keyVar]['id']==selectedRowId)
								 this.selectedRows.splice(keyVar,1);
						}
					}
					
					if (!auto) {
						this.notifyObservers('onJsSelectRow',[object,selectedRowId,baseName,displayMode]);
					}
				}
				
				
				this.updateSelectedRowsArray=function (baseName)
				{
					if (baseName=='') baseName='<?=$this->_prefix?>Row';
					var all = <?=$this->_prefix?>GetElements(document);
					for (var e = 0; e < all.length; e++) {
						var elm=all[e];
						if ((elm.type=='checkbox' || elm.type=='radio') && elm.id.match(/myTreeRow\[.*/i)) 
						if (elm.checked) {
							//elm.checked=true;
							this.selectRow(elm);
						}
					}
				}
				
				/* selectedRowsId is like ",5,43,35,"*/
				this.selectRows=function (selectedRowsId) {
					selectedRowsId=','+selectedRowsId+',';
					var all = <?=$this->_prefix?>GetElements(document);
					for (var e = 0; e < all.length; e++) {
						var elm=all[e];
		
						if (elm.type=='checkbox' || elm.type=='radio') {
							rowId=this.getRowIdById(elm.id);
							
							if (selectedRowsId.indexOf(','+rowId+',')>=0) {
								elm.checked=true;
								this.selectRow(elm,true);
							}
						}
					}
				}
				
				this.load=function ()
				{
					<? if ($this->_autoCollapseAtStart) {
						echo $this->_js->jsfCollapseAll("'$this->_mainId'");
					}?>

					//check_selected_tree_items('selected_tree_items_id');
					/*window.onload=<? //$this->_prefix?>OnloadFunctions();*/
				}
			}
		</script>
		
	<?
	}
	
	
	function printOnloadJavaScript() {?>
		<script language="javascript" type="text/javascript">
			<?=$this->_jsObjectInstanceName?>=new <?=$this->_prefix?>Class;
			<?=$this->_jsObjectInstanceName?>.load();
		</script>
	<?
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
	
	function printTree() {
		if ($this->_startNodeId==null) $this->_startNodeId=$this->getRootNodeId();
		$this->_prepareTemplates();
		
		if (!empty($this->_displayFromLevel))
			$conditions['and']=array($this->_colnLevelNumber.'>='.$this->_displayFromLevel);
		if (!empty($this->_displayToLevel))
			$conditions['and']=array($this->_colnLevelNumber.'<='.$this->_displayToLevel);
		
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
		
		/*view ; singleSelection ; multiSelection ; view_simple*/
		$displayMode=$this->_displayMode;
		if (!$this->_showStartNode) {
			$this->nextRow();
		}
	?>	
		<!-- (BEGIN) : Tree -->
		<ul id="<?=$this->_mainId?>" class="<?=$this->_prefix?>">
		<?
		while ($row = $this->nextRow()) {
			$rowName=$this->_prefix."Row[".$row[$this->_colnId]."]";
			
			$depth=$row[$this->_colnLevelNumber];
			
			if ($row[$this->_colnRightNumber]-$row[$this->_colnLeftNumber]>1)
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
			echo '<li id="'.$rowName.'[item]">';

			#--(Begin)-->Manipulate row columns via callback function
			$val=$this->runCommand('manipulateRowColumnsBeforeDraw',array(&$this,$row,$hasChild));
			if (is_array($val)) $row=$val;
			#--(End)-->Manipulate row columns via callback function
			
			#--(Begin)-->Generate additional items
			$additionalItemsHtml=$this->runCommand('manipulateGetAdditionalItems',array(&$this,$row,$hasChild));
			//$additionalItemsHtml='<li><a href="">'.'سلام'.'</a></li>';
			#--(End)-->Generate additional items

			echo $this->_useTemplate($row,$hasChild);

	        echo "\n";
			echo str_repeat('    ', 1 * $depth);
		
			if ($hasChild) {			
				echo "<ul>\n";
				#--(Begin)-->Append additional items
				if (!empty($additionalItemsHtml)) {
					echo $additionalItemsHtml."\n";
				}
				#--(End)-->Append additional items
			} else {
				#--(Begin)-->Append additional items
				if (!empty($additionalItemsHtml)) {
					echo "<ul>$additionalItemsHtml</ul>"."\n";
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
		<!-- (END) : Tree -->
	<?
	}
	
	function printAll($options=array()) {
		$this->printDefaultStyles();
		$this->printJavaScripts();
		$this->printTree();
		$this->printOnloadJavaScript();
	}
}
?>