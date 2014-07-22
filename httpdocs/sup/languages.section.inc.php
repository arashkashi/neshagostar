<?php
$infa=new cpInterface();

$num=1; 


if ($_POST['submit_insert']) { 
	$_GET['action'] = $_REQUEST['action'] = 'new';
}


if ($_REQUEST['action']=='delete') { 
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='single';	
	$_POST['rows'][$num]['columns']['id']=$_GET['id'];
	$_POST['rows'][$num]['action']='delete';
}

if ($_POST['submit_delete']) { 
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'delete';
}

if (!$_REQUEST['action']) $_REQUEST['action']='list';

$nextAction = '';
$secondAction = '';

if ($_POST['save_and_new']) { 
	$_POST['submit_save']='save';
	$_POST['submit_mode']='single';
	$nextAction = 'new';
	$secondAction = 'list';
}

if (isset($_POST['cancel'])){
	echo '<META http-equiv="refresh" content="0;URL=?'.cmfcUrl::excludeQueryStringVars(array('action', 'sectionName', 'pageType'),'get').'">';
	$_REQUEST['action'] = '';
}


if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('title');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}

 
$messages=array(); 


if (isset($_POST['submit_save']) and $_GET['action']!='view') {

	foreach ($_POST['rows'] as $num=>$row) {		
		$_columns=&$_POST['rows'][$num]['columns'];
		$columnsPhysicalName=$sectionInfo['tableInfo']['columns'];
		
		#--(Begin)-->prepare for multi action
		if ($_POST['submit_mode']=='multi' and $row['selected']!='true')
			continue;
		if (empty($row['action'])) $row['action']=$_POST['submit_action'];

		#--(End)-->prepare for multi action
        
		#--(Begin)-->fill and validate fields

		$columnsValues = array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		
		#--(End)-->fill and validate fields
		
		if (empty($validateResult)) {
			#--(Begin)-->save changes to database
			if ($row['action']=='delete') {
				$result=cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues['id']
				);
				$error = cmfcMySql::error();
				$msg = wsfGetValue('removeMsg');
			}
			elseif ($row['action']=='update') {
				$result=cmfcMySql::update(
					
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues,
					$_GET['id']
				);
				$error = cmfcMySql::error();
				$msg = wsfGetValue('updateMsg');
			}
			elseif ($row['action']=='insert') {
				$result=cmfcMySql::insert($_cp['sectionInfo']['tableInfo']['tableName'], $columnsValues);
				$msg= wsfGetValue('addMsg');
				$error = cmfcMySql::error();
				$_GET['id'] = cmfcMySql::insertId();
			}
			//print_r($columnsValues);
			#--(End)-->save changes to database
			
			if (PEAR::isError($result) or $result===false) {
				$messages['errors'][] = $error;
				$isErrorOccured=true;
			} else {
				$messages['messages'][] = $msg;
				cpfLog($_cp['sectionInfo']['name'], $userSystem->cvId, array('name'=>$row['action'],'rowId'=>$_GET['id']));
			}
			} else {
				foreach ($validateResult as $r) 
					$messages['errors'][]=$r->getMessage();
				$isErrorOccured=true;
			}		
		
		$actionTaken = true;
	};

	if (!PEAR::isError($result)) {
			if (!$isErrorOccured && $actionTaken) {
				#--(Begin)-->redirect to previous url if everthings is ok
				$messages['messages'][]=sprintf(
				'<META http-equiv="refresh" content="1;URL=?%s">',
				cmfcUrl::excludeQueryStringVars(array('action', 'sectionName', 'pageType'),'get').'&action='.$nextAction.'&nextAction='.$secondAction
			);
				
				#--(End)-->redirect to previous url if everthings is ok
				$saved=true;
			}			
			
			if (! $actionTaken){
				$messages['errors'][] = 'No action took place, mainly because you forgot to select a row to act on!';
			}	
		}
	}

if (!$_POST['submit_save'] and $_REQUEST['action']=='edit') {
	
	$sqlQuery=sprintf(
		"SELECT * FROM %s WHERE %s='%s'",
		$_cp['sectionInfo']['tableInfo']['tableName'],
		$_cp['sectionInfo']['tableInfo']['columns']['id'],
		$_REQUEST['id']
	);
	$row=cmfcMySql::loadCustom($sqlQuery);
	
	
	if (!empty($row)) {
		$_POST['rows'][$num]['columns'] = cmfcMySql::convertColumnNames ($row, $_cp['sectionInfo']['tableInfo']['columns']);
	}
	
		
}

if ($_REQUEST['action']=='list' or $_REQUEST['nextAction']=='list') {
	
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	
	if(!isset($_POST['box'])) $box = 0;
	else $box = $_POST['box'];
	#--(Begin)-->generate Sql Query
	
	if (isset($_REQUEST['submit_search'])) {
		$where = '';
		$OR = "";
		if (isset ($_REQUEST['search']['categoryId']) && $_REQUEST['search']['categoryId'] != ","){
			$categoryId=explode(',',$_REQUEST['search']['categoryId']);
			$where = "AND (";
			foreach ($categoryId as $value){
				if (!empty ($value) ){
					$where .= "$OR {td:catPath} LIKE '%,$value,%'";
					if(empty($OR)) $OR = "OR";
				}
			}
			$where .= ")";
		}
		
		$searchSqlWhereQuery = " AND (
			({td:title} LIKE '%[title]%') AND
			({td:body} LIKE '%[body]%' )
		)";
		
		$searchSqlWhereQuery .= $where;
		$replacements=array(
			'{td:title}'=>$_cp['sectionInfo']['tableInfo']['columns']['title'],
			'{td:body}'=>$_cp['sectionInfo']['tableInfo']['columns']['body'],
			'{td:catPath}'=>$_cp['sectionInfo']['tableInfo']['columns']['categoryPath'],
			'[title]'=>$_REQUEST['search']['title'],
			'[body]'=>$_REQUEST['search']['body'],
		);
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
	}
	
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE 1 ".$searchSqlWhereQuery;
	
	
	//echo $sqlQuery;
	//echo "<br>";
	#--(End)-->generate Sql Query
	
	//echo $sqlQuery;
	
	#--(Begin)-->Paging
	
	if (isset($_cp['sectionInfo']['listLimit']))
		$listLimit = $_cp['sectionInfo']['listLimit'];
	else
		$listLimit = 5;
	
	$paging=cmfcPaging::factory('dbV2',array(        
		'total'=>null,
		'limit'=>$listLimit,
		'sqlQuery'=>$sqlQuery,
		'wordNext'=> wsfGetValue(next) ,
		'wordPrev'=> wsfGetValue(prev),
		'link'=>'?'.cmfcUrl::excludeQueryStringVars(array('sectionName','pageType'),'get'),
		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['orderByColumnName'],
		'sortType'=>'DESC',
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	$sqlQuery=$paging->getPreparedSqlQuery();
	#--(End)-->Paging
	
	//echo $sqlQuery."<br>";
	
	#--(Begin)-->Execute Query and fetch the rows
	//echo $sqlQuery;
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
}


cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

wsfPrintMessages($messages);

if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) {
	
	
	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}

	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);

	$infa->showSeparatorRow(wsfGetValue(mainInfo));
	
	if ($_REQUEST['action'] == 'edit')
		$disabled = true;
	else
		$disabled = false;
	
	$infa->showInputRow(wsfGetValue(englishName),"rows[$num][columns][englishName]", $_POST['rows'][$num]['columns']['englishName'],'',40,'ltr', $disabled);
	
	
	if ($_POST['rows'][$num]['columns']['shortName'])
		$disabled = true;
	else
		$disabled = false;
	
	$infa->showInputRow(wsfGetValue(shortName),"rows[$num][columns][shortName]", $_POST['rows'][$num]['columns']['shortName'],wsfGetValue('pleaseEnterUnique2charLanguageName'),40,'ltr', $disabled);
	
	$infa->showInputRow(wsfGetValue(lang),"rows[$num][columns][name]", $_POST['rows'][$num]['columns']['name'],'',40,'ltr');
	
	$infa->showCustomRow(wsfGetValue('align'),
		cmfcHtml::drawDropDown("rows[$num][columns][align]",
				$_POST['rows'][$num]['columns']['align'],
				array('left','right'),
				'',
				''
		)
	, ''
	);
	
	$infa->showCustomRow(wsfGetValue('calendarType'),
		cmfcHtml::drawDropDown("rows[$num][columns][calendarType]",
				$_POST['rows'][$num]['columns']['calendarType'],
				array('gregorian','jalali'),
				'',
				''
		)
	, ''
	);
	
	$infa->showCustomRow(wsfGetValue('direction'),
		cmfcHtml::drawDropDown("rows[$num][columns][direction]",
				$_POST['rows'][$num]['columns']['direction'],
				array('ltr','rtl'),
				'',
				''
		)
	, ''
	);
	
	if ($_REQUEST['action'] == 'edit'){
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	} elseif($_REQUEST['action'] == 'new'){
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	
	$buttons = array(
	array(
		'name' => 'submit_save',
		'value' => wsfGetValue(buttonSubmit),
	),
	array(
		'name' => 'reset',
		'value' => wsfGetValue(buttonReset),
		'type' => 'reset',
	),
	array(
		'name' => 'cancel',
		'value' => wsfGetValue(buttonCancel),
	),
	array(
		'name' => 'newbutton',
		'value' => wsfGetValue('new button'),
	),
	array(
		'name' => 'save_and_new',
		'value' =>  wsfGetValue(buttonSubmitAndNew),
	),
);
	
	$infa->showFormFooterCustom($buttons);
	$_REQUEST['action'] = $_REQUEST['nextAction'];

}
if ($_REQUEST['action']=='list') { 
?>
<form name="myListForm" action="?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
<?php if (is_array($rows)) {
?>
	
		<input type="hidden" id="box" name="box" value="0" />
		<table id="listFormTable" class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7" dir="<?php echo $langInfo['htmlDir']?>">
			<tr>
				<td colspan="10" class="table-header" align="<?php echo $langInfo['htmlAlign']?>"><?php echo  $actionTitle?></td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" align="<?php echo $langInfo['htmlAlign']?>"  >
					#
				</td>
				<td class="table-title field-checkbox" width="26" align="<?php echo $langInfo['htmlAlign']?>">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo wsfGetValue(tools)?>				</td>
				
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('title','DESC')?>">
						<span style="font-family:arial">▼</span>					</a>
					<a href="<?php echo $paging->getSortingUrl('title','ASC')?>">
						<span style="font-family:arial">▲</span>					</a>
					<?php echo wsfGetValue(lang)?>				</td>
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue(englishName)?>				</td>
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue(direction)?>				</td>
				
			</tr>
			<?php 
			foreach ($rows as $key=>$row) {
				//print_r($row);
				$num=$key+1;
					
				//--(Begin)-->convert columns physical names to their internal names
				$row = cmfcMySql::convertColumnNames($row, $sectionInfo['tableInfo']['columns']);	
				//--(End)-->convert columns physical names to their internal names
			?>
			<tr class="table-row1" onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>">
					<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-checkbox" align="<?php echo $langInfo['htmlAlign']?>">
					<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				</td>
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>">
					<a href="?sn=<?php echo $_GET['sectionName']?>&action=edit&id=<?php echo $row['id']?>"><img src="interface/images/action_edit.png" width="16" height="16" border="0" alt="edit" title="view" /></a>
					<a onclick="return <?php echo $js->jsfConfimationMessage('Are you sure ?')?>" href="?sn=<?php echo $_GET['sectionName']?>&action=delete&id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" height="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" ><?php echo $row['name'];?></td>
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" ><?php echo $row['englishName'];?></td>
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" ><?php echo $row['direction'];?></td>
								
			</tr>
			<?php }?>
  </table>
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo wsfGetValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
		</div>

<?php } else { ?>
	<b><?php echo  wsfGetValue(nothingFound)?></b>
	<br />
	<input name="submit_insert" class="button" type="submit" value="<?php echo wsfGetValue(buttonNew) ?>"  />
<?php }
?>
</form>
<?php }
if ($paging and $_REQUEST['action']=='list' and $paging->getTotalPages()>1) {?>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tr>
		<td  align="<?php echo $langInfo['htmlAlign']?>">
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" align="<?php echo $langInfo['htmlAlign']?>">
						<?php echo  wsfGetValue(page)?> <?php echo $paging->getPageNumber()?> <?php echo  wsfGetValue(from)?> <?php echo $paging->getTotalPages()?>
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
						<?php echo  wsfGetValue(prevPage)?> </a></td>
					<?php }?>
					<?php if ($paging->hasNext()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getNextUrl()?>" >
						<?php echo  wsfGetValue(nextPage)?> </a></td>
					<?php }?>
				</tr>
		  </table>
		</td>
	</tr>
</table>
<?php }

?>