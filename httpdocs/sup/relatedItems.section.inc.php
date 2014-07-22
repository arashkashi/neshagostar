<?php 
$configuration = cpfConvertSelectorBoxGetToArray($_GET);

if($configuration['itemTypesTableType'] == 'single')
{
	$itemTypes = array(
		array(
			'id' => $configuration['itemTypeIdColumn'],
			'title' => $configuration['itemTypeLabelColumn'],
		)
	);
	$configuration['itemTypeIdColumn'] = 'id';
	$configuration['itemTypeLabelColumn'] = 'title';
}
elseif($configuration['itemTypesTableType'] == 'multiple')
{
	foreach($configuration['itemTypeColumns'] as $itemTypeColumn)
	{
		$itemTypes[] = array(
			'id' => $itemTypeColumn['id'],
			'title' => $itemTypeColumn['title'],
		);
	}
	$configuration['itemTypeIdColumn'] = 'id';
	$configuration['itemTypeLabelColumn'] = 'title';
}
elseif($configuration['itemTypesTableType'] == 'virtual')
{
	$itemTypes = $_ws['virtualTables'][$configuration['itemTypesTable']]['rows'];
}
elseif($configuration['itemTypesTableType'] == 'physical')
{
	$itemTypesTable = $_ws['physicalTables'][$configuration['itemTypesTable']];
	$itemTypesQuery = "SELECT * FROM `".$itemTypesTable['tableName']."` ORDER BY `".$itemTypesTable['columns']['id']."` ASC";
	$itemTypes = cmfcMySql::getRowsCustom($itemTypesQuery);
}


if(!$_REQUEST['itemType'])
{
	$_REQUEST['itemType'] = $itemTypes[0][$configuration['itemTypeIdColumn']];
}

?>

<script type="text/javascript" language="javascript">
	
	
	/*
	create related Item element on page load
	*/
	var configurations = <?php echo cmfcHtml::phpToJavascript($configuration)?>;
	var relatedItemsId = new Array();
	var relatedItemsName = new Array();
	var relatedItemsType = new Array();
	
	<?php 	if($itemTypes)
	{
		$rowNum = 0;
		foreach($itemTypes as $itemType)
		{
		?>
		relatedItemsId[<?php echo $rowNum?>] = new Array();
		relatedItemsName[<?php echo $rowNum?>] = new Array();
		relatedItemsType[<?php echo $rowNum?>] = <?php echo $itemType[$configuration['itemTypeIdColumn']]?>;
		<?php 			$rowNum++;
		}
	}
	?>

	function cpfPrepareVariableAndName()
	{
		var relatedItemsNameTemp= new Array();
		var relatedItemsValueTemp= new Array();
		var relatedItemsValue;
		var relatedItemsNames;
		<?php 		if($itemTypes)
		{
			$rowNum = 0;
			foreach($itemTypes as $itemType)
			{
			?>
			relatedItemsValue = document.getElementById('rows[relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]').value;
			relatedItemsNames = document.getElementById('rows[relatedItemsNames][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]').value;
			if(relatedItemsNames)
				relatedItemsNameTemp[<?php echo $rowNum?>] = relatedItemsNames.split(',');
			if(relatedItemsValue)
			{
				relatedItemsValueTemp[<?php echo $rowNum?>] = relatedItemsValue.split(',');
				for(var i=0; i<relatedItemsValueTemp[<?php echo $rowNum?>].length;i++)
				{
					relatedItemsId[<?php echo $rowNum?>][relatedItemsValueTemp[<?php echo $rowNum?>][i]] = relatedItemsValueTemp[<?php echo $rowNum?>][i];
					relatedItemsName[<?php echo $rowNum?>][relatedItemsValueTemp[<?php echo $rowNum?>][i]] = relatedItemsNameTemp[<?php echo $rowNum?>][i];
				}
			}
			<?php 				$rowNum++;
			}
		}
		?>
	}
	
	function cpfDrawRelatedItemsList(itemType)
	{
		var innerHtml = "";
		for(var i=0; i<relatedItemsId[itemType].length;i++)
		{
			if(relatedItemsId[itemType][i])
			{
				innerHtml += "<li><a href='javascript:void(0)' onclick='cpfDeleteRelatedItem("+itemType+", "+relatedItemsId[itemType][i]+")'><img border='0' src='interface/images/min_on.gif'></a>&nbsp;"+relatedItemsName[itemType][i]+"</li>";
			}
		}
		
		if(!innerHtml)
			innerHtml = "<?php echo wsfGetValue(VN_nothingFound)?>";
		
		document.getElementById("relatedItemsList["+relatedItemsType[itemType]+"]").innerHTML = innerHtml;
	}
	
	function cpfDeleteRelatedItem(itemType, itemId)
	{
		relatedItemsId[itemType][itemId]=null;
		relatedItemsName[itemType][itemId]=null;
		
		document.getElementById('rows[relatedItems]['+relatedItemsType[itemType]+']').value = cpfJoinArrayWithDelimiter(relatedItemsId[itemType], ",");
		
		cpfDrawRelatedItemsList(itemType);
	}
	
	function cpfAddRelatedItem(itemType, itemId, itemName)
	{
		relatedItemsId[itemType][itemId]=itemId;
		relatedItemsName[itemType][itemId]=itemName;
		
		document.getElementById('rows[relatedItems]['+relatedItemsType[itemType]+']').value = cpfJoinArrayWithDelimiter(relatedItemsId[itemType], ",");
		
		cpfDrawRelatedItemsList(itemType); 
	}
	
	function cpfAddMultiSelection()
	{
		var itemTypeId = document.getElementById('itemType').value;
		
		var itemType = cpfGetItemTypeIndexWithId(itemTypeId);
		
		var inputs = document.getElementsByTagName('INPUT');
		for(var i=0; i<inputs.length; i++)		
		{
			if(inputs[i].type=="<?php echo $configuration['showType']?>" && inputs[i].name!="checkall")
			{
				if(inputs[i].checked)
				{
					<?php 					if($configuration['showType'] == 'radio')
					{
					?>
					relatedItemsId[itemType]=new Array();
					relatedItemsName[itemType]=new Array();
					<?php 					}
					?>
					var itemName = document.getElementById('rows['+inputs[i].value+'][name]').value;
					cpfAddRelatedItem(itemType, inputs[i].value, itemName)
				}
			}
		}
	}
	
	function cpfGetItemTypeIndexWithId(itemTypeId)
	{
		for(var i=0; i<relatedItemsType.length; i++)		
		{
			if(relatedItemsType[i] == itemTypeId)
			{
				return i;
			}
		}
		
		return 0;
	}
	
	function cpfJoinArrayWithDelimiter(itemArray, delimiter)
	{
		var stringArray = "";
		for(var i=0; i<itemArray.length;i++)
		{
			if(itemArray[i])
			{
				stringArray += itemArray[i];
				if(i != itemArray.length-1)
					stringArray += delimiter;
			}
		}
		
		if(stringArray.charAt(stringArray.length-1)==',')
		{
			stringArray = stringArray.substring(0, stringArray.length-1)
		}

		return stringArray;
	}
	
	function cpfSaveRelatedItemChanges()
	{
		<?php 		if($itemTypes)
		{
			$rowNum = 0;
			foreach($itemTypes as $itemType)
			{
			?>
			parent.parent.document.getElementById('rows[<?php echo $configuration['name']?>][relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]').value = document.getElementById('rows[relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]').value;
			<?php 				$rowNum++;
			}
		}
		?>

		parent.parent.document.getElementById('rows[<?php echo $configuration['name']?>][relatedItemChanges]').click();
	
		parent.parent.GB_hide();
	}
	
	function cpfPrepareVariableFromParent()
	{
		<?php 		if($itemTypes)
		{
			$rowNum = 0;
			foreach($itemTypes as $itemType)
			{
			?>
			document.getElementById('rows[relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]').value = parent.parent.document.getElementById('rows[<?php echo $configuration['name']?>][relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]').value;
			<?php 				$rowNum++;
			}
		}
		?>
	}
	
	function cpfPrepareRelatedItemsName()
	{
		var relatedItemData = new Array();
		for(var i=0; i<relatedItemsType.length;i++)
		{
			relatedItemData[i] = document.getElementById("rows[relatedItems]["+relatedItemsType[i]+"]").value;
		}
		xajaxPrepareRelatedItemsName(relatedItemsType, relatedItemData, 0, configurations);
	}
	
	function cpfOnPageLoadAfterAjaxResponse()
	{
		cpfPrepareVariableAndName();
		<?php 		if($itemTypes)
		{
			$rowNum = 0;
			foreach($itemTypes as $itemType)
			{
			?>
			cpfDrawRelatedItemsList(<?php echo $rowNum?>);
			<?php 				$rowNum++;
			}
		}
		?>
	}

</script>

<?php
$infa=new cpInterface();

$num=1; 

if (!$_REQUEST['action']){
 $_REQUEST['action']='list';
}

if ($_POST['submit_cancel_search']) { 
	unset($_POST['search']);
	unset($_REQUEST['search']);
}

if ($_POST['submit_save_changes']) { 
	$_POST['submit_save']='save';
	$_POST['submit_action']='update';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'update';
}

$nextAction = '';
$secondAction = '';

if (isset($_POST['cancel'])){
	echo '<META http-equiv="refresh" content="0;URL=?'.wsfExcludeQueryStringVars(array('action', 'sectionName', 'pageType'),'get').'">';
	$_REQUEST['action'] = '';
}

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('VN_list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('VN_edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('VN_new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('VN_remove');}

$messages=array(); 

if($configuration['itemsTables'][$_REQUEST['itemType']]['itemsTableType'] == 'virtual')
{
	$tableType = 'virtual';
	$tableInfo = $_ws['virtualTables'][$configuration['itemsTables'][$_REQUEST['itemType']]['itemsTable']];
}
elseif($configuration['itemsTables'][$_REQUEST['itemType']]['itemsTableType'] == 'physical')
{
	$tableType = 'physical';
	$tableInfo = $_ws['physicalTables'][$configuration['itemsTables'][$_REQUEST['itemType']]['itemsTable']];
}



if ($_REQUEST['action']=='list'  or $_REQUEST['nextAction']=='list' ) {

	if($tableType == 'physical')
	{
		$searchSqlWhereQuery="";
		$limit=$_cp['sectionInfo']['listLimit'];
		#--(Begin)-->generate Sql Query
		
		//echo $_REQUEST['submit_search']."<br>";
		
		if (isset($_REQUEST['submit_search'])) {
			$where = '';
			$OR = "";
			
			$searchSqlWhereQuery = " AND (
				{td:name} LIKE '%[name]%'
			)";
			
			$searchSqlWhereQuery .= $where;
			$replacements=array(
				'{td:name}'=>$configuration['itemsTables'][$_REQUEST['itemType']]['itemLabelColumn'],
				'[name]'=>$_REQUEST['search']['name'],
			);
			$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
		}
		
		/*if($_GET['itemId'])
			$filterWhereClause = " AND `".$configuration['itemsTables'][$_REQUEST['itemType']]['itemIdColumn']."`<>".$_GET['itemId'];*/

		if($tableInfo['columns']['languageId'])
			$filterWhereClause = " AND `".$tableInfo['columns']['languageId']."`='".$langInfo['id']."'";
		
		$sqlQuery="SELECT * FROM ".$tableInfo['tableName']." WHERE (1=1)".$configuration['itemsTables'][$_REQUEST['itemType']]['customWhereClause'].$filterWhereClause.$searchSqlWhereQuery;
		#--(End)-->generate Sql Query
		
		//echo $sqlQuery;
		
		#--(Begin)-->Paging
		
		if (isset($_cp['sectionInfo']['listLimit']))
			$listLimit = $_cp['sectionInfo']['listLimit'];
		else
			$listLimit = 10;
	
		$paging=cmfcPaging::factory('dbV2',array(        
			'total'=>null,
			'limit'=>$listLimit,
			'sqlQuery'=>$sqlQuery,
			'wordNext'=> wsfGetValue(VN_next) ,
			'wordPrev'=> wsfGetValue(VN_prev),
			'link'=>'?'.wsfExcludeQueryStringVars(array('sectionName','pageType'),'get'),
			'sortingEnabled'=>true,
			'staticLinkEnabled'=>true,
			'sortBy'=>$tableInfo['orderByColumnName'],
			'sortType'=>'DESC',
			'colnId'=>$tableInfo['columns']['id'],
		));
		$sqlQuery=$paging->getPreparedSqlQuery();
		
	
		#--(End)-->Paging
		
		//echo $sqlQuery."<br>";
		
		#--(Begin)-->Execute Query and fetch the rows
		$rows=cmfcMySql::getRowsCustom($sqlQuery);	
	
		echo mysql_error();
		#--(End)-->Execute Query and fetch the rows
		//print_r($rows);
		//echo "<br>";
	}
	elseif($tableType == 'virtual')
	{
		$rows = $tableInfo['rows'];
	}
}
?>
<div class="br" dir=<?php echo $langInfo['htmlDir']?> align="<?php echo $langInfo['htmlAlign']?>"  >
	<span class="br-bullet">»</span> <?php echo wsfGetValue(VN_Home)?> » 
	<?php echo $_cp['sectionInfo']['title'.$langInfo['dbBigLang']]?> [<?php echo $actionTitle?>]
</div>

<div class="desc">
<?php echo $_cp['sectionInfo']['description']?>

</div>


<?php if (is_array($messages)) {
	foreach ($messages as $type => $messageList){
		$class = '';
		if ($type == 'errors'){
			$class = 'errorBox';
		}
		elseif ($type == 'messages'){
			$class = 'messageBox';
		}
		else{
			$class = '';
		}
		?>
		<div class="<?php echo $class?>" dir="<?php echo $langInfo['htmlDir']?>" align="<?php echo $langInfo['htmlAlign']?>" >
			<?php echo implode('<br />',$messageList)?>
		</div>
		<?php 	}
}
if ($_REQUEST['action']=='list' or $_REQUEST['nextAction']=='list') 
{ 
?>
<form name="myListForm" id="myListForm" action="?<?php echo wsfExcludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
		
        <?php 
		/*
		?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" dir="<?php echo $langInfo['htmlDir']?>" > 
			<tr>
				<td align="<?php echo $langInfo['htmlAlign']?>" ></td>
				<td  align="<?php echo $langInfo['htmlAlign']?>" >
				
				<table border="0" cellpadding="0" cellspacing="1" class="option-link"  align="<?php echo $langInfo['htmlAlign']?>"  >
                  <tr>
                    <td class="quick-search-button"  align="<?php echo $langInfo['htmlNAlign']?>" ><a href="javascript:void(0);" onclick="<?php echo $js->jsfToggleDisplayStyle('searchBoxContainer','auto')?>"><?php echo wsfGetValue(VN_advancedSearch);?></a></td>
                  </tr>
                </table></td>
			</tr>
		</table>
		
		<div id="searchBoxContainer" style="display:none;" dir="<?php echo $langInfo['htmlDir']?>">
		<table id="option-buttons" width="100%" border="0" cellspacing="0" cellpadding="0"  dir="<?php echo $langInfo['htmlDir']?>" > 
			<tr>
				<td  class="option-table-buttons-spacer" width="5" align="<?php echo $langInfo['htmlAlign']?>" >&nbsp;</td>
				<td  align="<?php echo $langInfo['htmlAlign']?>" id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo wsfGetValue(VN_advancedSearch)?>
				</td>
				<td class="option-table-buttons-spacer" align="<?php echo $langInfo['htmlAlign']?>" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			
			<tr class="table-row1" align="<?php echo $langInfo['htmlAlign']?>" >
				<td width="200"><?php echo wsfGetValue(VN_formName)?> </td>
				<td align="<?php echo $langInfo['htmlAlign']?>" ><input name="search[name]" class="input" type="text" value="<?php echo $_REQUEST['search']['name']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row1">
				<td colspan="2" align="<?php echo $langInfo['htmlAlign']?>">
					<input class="button" type="submit" name="submit_search" value="<?php echo wsfGetValue(VN_search)?>" />
					<input class="button" type="submit" name="submit_cancel_search" value="<?php echo wsfGetValue(VN_cancelSearch)?>" />
				</td>
			</tr>
		</table>
		</div>
		<?php 
		/* */
		?>
		
        
        
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;"> 
	<tr>
		<td>
		</td>
		<td>
			<table border="0" cellpadding="0" cellspacing="1" class="option-link">
			  <tr>
				<td class="quick-search-button">
					<?php 					echo cmfcHtml::drawDropDown(
						"itemType", //name
						$_REQUEST['itemType'], //value
						$itemTypes, //items
						$configuration['itemTypeIdColumn'], //valueColumnName
						$configuration['itemTypeLabelColumn'], //titleColumnName
						null, //groupNameColumnName
						null, //groupIdColumnName
						null, //defaultValue
						null, //defaultTitle
						array(//more attributes
							'id'=>'itemType',
							'class'=>'select',
							'style' => 'direction:rtl',
							'onchange' => 'cpfOnChangeItemTypeDropDown()'
						)
					);
					?>
				</td>
			  </tr>
			</table>
		</td>
	</tr>
</table>
<?php $itemId = $_GET['itemId'];
	
if (is_array($rows)) 
{
?>
<table id="listFormTable" class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
	<tr>
		<td colspan="10" class="table-header" align="<?php echo $langInfo['htmlAlign']?>" ><?php echo $actionTitle?> </td>
	</tr>
	<tr>
		<td class="table-title field-title" style="width:30px" align="<?php echo $langInfo['htmlAlign']?>" >
			#
		</td>
		
		<td class="table-title field-checkbox" width="26" align="<?php echo $langInfo['htmlAlign']?>" >
		<?php 		if($configuration['showType'] == 'checkbox')
		{
		?>
			<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
		<?php 		}
		?>
		</td>
		
		<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
			<a href="<?php echo $paging->getSortingUrl('name','DESC')?>">
				<span style="font-family:arial">▼</span>
			</a>
			<a href="<?php echo $paging->getSortingUrl('name','ASC')?>">
				<span style="font-family:arial">▲</span>
			</a>
			<?php echo wsfGetValue($configuration['itemsTables'][$_REQUEST['itemType']]['itemLabelColumn'])?>
		</td>
	</tr>
	<?php 
	foreach ($rows as $key=>$row) {
		$num=$key+1;
			
		//--(Begin)-->convert columns physical names to their internal names				
		//$row = cmfcMySql::convertColumnNames($row, $tableInfo['columns']);
		
	//cmfcHtml::printr(array($row, $configuration));
		//--(End)-->convert columns physical names to their internal names
	?>
	<tr class="table-row1" onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
		<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>">
			<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
			<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row[$configuration['itemsTables'][$_REQUEST['itemType']]['itemIdColumn']]?>" />
		</td>
		
		<td class="field-checkbox" align="<?php echo $langInfo['htmlAlign']?>" >
			<?php 			$inputName = ($configuration['showType']=='checkbox' ? "rows[$num][selected]":'rows[selected]');
			?>
			<input type="<?php echo $configuration['showType']?>" name="<?php echo $inputName?>"  value="<?php echo $row[$configuration['itemsTables'][$_REQUEST['itemType']]['itemIdColumn']]?>" />
			<input name="rows[<?php echo $row[$configuration['itemsTables'][$_REQUEST['itemType']]['itemIdColumn']]?>][name]" 
            		id="rows[<?php echo $row[$configuration['itemsTables'][$_REQUEST['itemType']]['itemIdColumn']]?>][name]" 
                    type="hidden" 
                    value="<?php echo $row[$configuration['itemsTables'][$_REQUEST['itemType']]['itemLabelColumn']]?>" />
		</td>
		<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" >
			<?php echo $row[$configuration['itemsTables'][$_REQUEST['itemType']]['itemLabelColumn']]?>
        </td>

	</tr>
	<?php }?>
</table>
<div style="text-align:center">

	<input name="submit_save_changes" class="button" type="button" value=" <?php echo wsfGetValue('saveChanges') ?> " onclick="cpfSaveRelatedItemChanges()" />
	<input name="submit_add" class="button" type="button" value=" <?php echo wsfGetValue('add')?> " onclick="cpfAddMultiSelection()" />
</div>

<?php } else { ?>
	<b><?php echo  wsfGetValue(VN_nothingFound)?></b>
	<br />
<?php 
}
?>
<br />
<br />

<?php 	if($itemTypes)
	{
	?>
	<table border="1" cellpadding="0" cellspacing="1" class="option-link" style="border-collapse:collapse" width="100%" align="center">
		<tr>
	<?php 		foreach($itemTypes as $itemType)
		{
		?>
			<td class="quick-search-button" valign="top" width="20%">
				<input name="rows[relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]" id="rows[relatedItems][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]" type="hidden" value="<?php echo $_POST['rows']['relatedItems'][$itemType[$configuration['itemTypeIdColumn']]]?>" />
				<input id="rows[relatedItemsNames][<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]" type="hidden" value="" />
				<div style="font-size:9pt">
					<?php echo $itemType[$configuration['itemTypeLabelColumn']]." ".wsfGetValue('VN_related')?>
				</div>
				<ul id="relatedItemsList[<?php echo $itemType[$configuration['itemTypeIdColumn']]?>]">
				</ul>
			</td>
		<?php 		}
	?>
		</tr>
	</table>
	<?php 	}
}
?>
<?php if ($paging) {?>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tr>
		<td  align="<?php echo $langInfo['htmlAlign']?>">
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" align="<?php echo $langInfo['htmlAlign']?>">
						<?php echo  wsfGetValue(VN_page)?> <?php echo $paging->getPageNumber()?> <?php echo  wsfGetValue(VN_from)?> <?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		</td>
		<td align="<?php echo $langInfo['htmlAlign']?>">
			<table border="0" align="<?php echo $langInfo['htmlNAlign']?>" cellpadding="0" cellspacing="1" class="paging-nav">
				<tr>
					<?php if ($paging->hasPrev()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getPrevUrl()?>" >
						<?php echo  wsfGetValue(VN_prevPage)?> </a></td>
					<?php }?>
					<?php if ($paging->hasNext()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getNextUrl()?>" >
						<?php echo  wsfGetValue(VN_nextPage)?> </a></td>
					<?php }?>
				</tr>
		  </table>
		</td>
	</tr>
</table>


<?php }
?>
</form>
<script language="javascript" type="text/javascript">
	<?php 	if(!$_POST['rows'])
	{
	?>
		cpfPrepareVariableFromParent();
	<?php 	}
	?>
	cpfPrepareRelatedItemsName();
</script>