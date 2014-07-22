<?php
$infa=new cpInterface();

$num=1; 

if (!$_REQUEST['action']) $_REQUEST['action']='list';
if ($_REQUEST['action']=='list')   $actionTitle=wsfGetValue('list');
if ($_REQUEST['action']=='edit')   $actionTitle=wsfGetValue('edit');
if ($_REQUEST['action']=='new')    $actionTitle=wsfGetValue('new');
if ($_REQUEST['action']=='delete') $actionTitle=wsfGetValue('remove');

$messages=array(); 
/*
$fieldsValidationInfo=array(
	"rows[$num][columns][name]"=>array(
		'name'=>"rows[$num][columns][name]",
		'headName'=>'name',
		'title'=>'name',
		'type'=>'string',
		'param'=>array(
			'notEmpty'=>true
		)
	),
	
	"rows[$num][columns][nameFa]"=>array(
		'name'=>"rows[$num][columns][nameFa]",
		'headName'=>'nameFa',
		'title'=>'persian name',
		'type'=>'string',
		'param'=>array(
			'notEmpty'=>true
		)
	),
);
*/

$validation->setOption('fieldsInfo',$fieldsValidationInfo);


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
}

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
		if ($row['action']!='delete') {
			if (is_array($fieldsValidationInfo))
				$validateResult=$validation->validate($_columns, "rows[$num][columns][%s]");
			//echo'<pre style="direction:ltr;text-align:left">';print_r($columnsValues);echo'<pre>';
		}
		//echo nl2br(print_r($_columns, true));
		//echo nl2br(print_r($_cp['sectionInfo']['tableInfo'], true));
		
		if (isset($_columns['permissionsFullAccess']) && $_columns['permissionsFullAccess'] == 1)
			$_columns['permissions'] = ',n,';
		else if (!empty($_columns['permissions']))
		{
				$_columns['permissions'] = ','.implode(',', $_columns['permissions']).',';
		}
		else
		{
			$_columns['permissions'] = '';
		}
		
		$_columns['type'] = $_cp['sectionInfo']['typeName'];
		
		unset($_columns['permissionsFullAccess']);
		
		$columnsValues=array();
		foreach ($_columns as $columnName=>$columnValue) {
		
			$columnsValues[$columnsPhysicalName[$columnName]]=$columnValue;
		}
		
		if($row['action']=='delete')
		{
			$res = cmfcMysql::load( $_cp['sectionInfo']['tableInfo']['tableName'] , 'id' , $columnsValues['id']);
			if($res['internal_name'])
			{
				$validateResult[]=$validation->raiseError(wsfGetValue('protectedUserGroup'));
			}
			
		}
		
		#--(End)-->fill and validate fields
		
		if (empty($validateResult)) {
			#--(Begin)-->save changes to database
			if ($row['action']=='delete') {
				$result=cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues['id']
				);
				$msg =wsfGetValue('removeMsg');
			}
			elseif ($row['action']=='update') {
				$result=cmfcMySql::update($_cp['sectionInfo']['tableInfo']['tableName'], 'id' ,$columnsValues, $_GET['id']);
				$msg =wsfGetValue('updateMsg');
			} elseif ($row['action']=='insert') {
				$result=cmfcMySql::insert($_cp['sectionInfo']['tableInfo']['tableName'],$columnsValues);
				$msg =wsfGetValue('addMsg');
			}
			#--(End)-->save changes to database
			//echo nl2br(print_r($columnsValues, true));
			
			if (PEAR::isError($result) or $result===false) {
				if (PEAR::isError($result))
					$messages['errors'][]=$result->getMessage();
				else
					$messages['errors'][]=cmfcMySql::error();
				$isErrorOccured=true;
			} else {
				$messages['messages'][]=$msg;
			}
		} else {
			foreach ($validateResult as $r) 
				$messages['errors'][]=$r->getMessage();
			$isErrorOccured=true;
		}
	};

	if (!PEAR::isError($result)) {
		if (!$isErrorOccured) {
			#--(Begin)-->redirect to previous url if everthings is ok
			
			$messages['other'][]=sprintf(
				'<META http-equiv="refresh" content="1;URL=?%s">',
				cmfcUrl::excludeQueryStringVars(array('action'),'get')
			);
			
			#--(End)-->redirect to previous url if everthings is ok
			$saved=true;
		}
		
		//if (is_array($messages)) $messages=implode('<br/>',$messages);
	}
}

if (!$_POST['submit_save'] and $_REQUEST['action']=='edit') {
	
	$sqlQuery=sprintf(
		"SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
		$_cp['sectionInfo']['tableInfo']['tableName'],
		$_cp['sectionInfo']['tableInfo']['columns']['id'],
		$_REQUEST['id'],
		$_cp['sectionInfo']['tableInfo']['columns']['type'],
		$_cp['sectionInfo']['typeName']
	);
	$row=cmfcMySql::loadCustom($sqlQuery);
	/*foreach ($row as $columnName=>$columnValue) {
		$_POST['rows'][$num]['columns'][$columnName]=$columnValue;
	}*/
	if (!empty($row)) {
		foreach ($sectionInfo['tableInfo']['columns'] as $columnName=>$columnPhysicalName) {
			$_POST['rows'][$num]['columns'][$columnName]=$row[$columnPhysicalName];
		}
	}
	//print_r($sqlQuery);
}

if ($_REQUEST['action']=='list'/* or $_REQUEST['action']=='edit'*/) {
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	
	#--(Begin)-->generate Sql Query
	if (isset($_REQUEST['submit_search'])) {
	
	
		if ($_REQUEST['search']['firstName'])
			$searchSqlWhereQuery .= " AND ({td:firstName} LIKE '%[firstName]%' )";
		
		if ($_REQUEST['search']['lastName'])
			$searchSqlWhereQuery .= " AND ({td:lastName} LIKE '%[lastName]%' )";
		
		if ($_REQUEST['search']['lastName'])
			$searchSqlWhereQuery .= " AND ({td:username} LIKE '[username]' )";
		
		$replacements=array(
			'{td:username}'=>$sectionInfo['tableInfo']['columns']['username'],
			'{td:firstName}'=>$sectionInfo['tableInfo']['columns']['firstName'],
			'{td:lastName}'=>$sectionInfo['tableInfo']['columns']['lastName'],
			'[username]'=>$_REQUEST['search']['username'],
			'[firstName]'=>$_REQUEST['search']['firstName'],
			'[lastName]'=>$_REQUEST['search']['lastName']
		);
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
	}
	
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE (1 = 1) AND `".$_cp['sectionInfo']['tableInfo']['columns']['type']."`='".$_cp['sectionInfo']['typeName']."' ".$searchSqlWhereQuery;
	#--(End)-->generate Sql Query
	
	#--(Begin)-->Paging
	if (isset($_cp['sectionInfo']['listLimit']))
		$listLimit = $_cp['sectionInfo']['listLimit'];
	else
		$listLimit = 5;
	$paging=cmfcPaging::factory('dvb2',array(        
		'total'=>null,
		'limit'=>$listLimit,
		'sqlQuery'=>$sqlQuery,
		'wordNext'=>wsfGetValue('next'),
		'wordPrev'=>wsfGetValue('prev'),
		'link'=>'?'.cmfcUrl::excludeQueryStringVars(array('sectionName','pageType'),'get'),
		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['orderByColumnName'],
		'sortType'=>'DESC',
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	$sqlQuery=$paging->getPreparedSqlQuery();
	#--(End)-->Paging
	
	#--(Begin)-->Execute Query and fetch the rows
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
	
	//print_r($rows);
}

cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

wsfPrintMessages($messages);

if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) {
/*
	wsfLoadXinha(
		array(
			"rows[$num][columns][biography]"=>array('direction'=>'rtl')
		),
		$_cp['sectionInfo']['folderRelative'], 
		XinhaLoader_Theme_Simple_With_Image_Manager
	);
*/

	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}

	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);

	$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	$infa->showInputRow(wsfGetValue('groupName'),"rows[$num][columns][name]", $_POST['rows'][$num]['columns']['name'],'',40,'rtl');
	
	

 
	$siteSectionsDB = $_cp['sectionInfo']['siteSectionsTable'];
	
	if (strpos($_POST['rows'][$num]['columns']['permissions'],',n,')!==false) $fullAccessChecked='checked="checked"';
	
	$items = cmfcMySql::getRowsCustom('SELECT * FROM '.$siteSectionsDB['tableName'].' WHERE `'.$siteSectionsDB['columns']['type'].'`="'.$_cp['sectionInfo']['siteSectionsType'].'" ORDER BY `'.$siteSectionsDB['columns']['id'].'`');
	if($items)
	{
		foreach($items as $key=>$item)
		{
			$items[$key]['name'] = wsfGetValue($item['name']);
		}
	}

	$infa->showCustomRow(wsfGetValue('accessLevel'),
			'<div id="fullAccess" ><input id="fullAccessCheckBox"  name="rows['.$num.'][columns][permissionsFullAccess]" type="checkbox" value="1" '.$fullAccessChecked.'/> '.wsfGetValue('fullAccess').'<br /> </div>'.
			($items ?
			cmfcHtml::drawMultiCheckBoxes(
				"rows[$num][columns][permissions]",
				$items,
				explode(',',$_POST['rows'][$num]['columns']['permissions']),
				'table',
				array(
					'max_columns'=>6,
					'sort_type'=>'horizontal',
					'value'=>'1',
					'title_key_name'=> $siteSectionsDB['columns']['name'],
					'unique_key_name'=>$siteSectionsDB['columns']['id'],
					'value_type'=>'unique_key'
				)
			 ):'')
		,''
		);

	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	} elseif($_REQUEST['action'] == 'new') {
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
	
	);
	$infa->showFormFooterCustom($buttons);
?>

<?php } elseif ($_REQUEST['action']=='list') { ?>

<?php 
if (is_array($rows)) {
?>
	<form name="myListForm" action="?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">

		<table id="listFormTable" class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="10" class="table-header"> <?php echo $actionTitle?>  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px">
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>

				<td class="table-title field-title" style="width:35px;text-align:center">
					<?php echo wsfGetValue('tools')?>
				</td>
				<td class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('groupName')?>
				</td>
			</tr>
			<?php 			foreach ($rows as $key=>$row) 
			{
				$num=$key+1;
				?>
				<tr class="table-row1" onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
					<td class="field-title">
						<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
						<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
					</td>
					
					<td class="field-checkbox">
						<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
	
					</td>
					<td class="field-title">
						<a href="?sn=<?php echo $_GET['sectionName']?>&action=edit&id=<?php echo $row['id']?>"><img src="interface/images/action_edit.png" width="16" height="16" border="0" alt="edit" title="edit" /></a>
						
						<a onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue('areYouSure'))?>" href="?sn=<?php echo $_GET['sectionName']?>&action=delete&id=<?php echo $row['id']?>">
							<img src="interface/images/action_delete.png" width="16" height="16" border="0" alt="delete" title="delete" />
						</a>
					
					</td>
					
					<td class="field-title"><?php echo $row['name']?></td>
				</tr>
			<?php
			}?>
		</table>
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue('delete')?> " onclick="return <?php echo $js->jsfConfimationMessage('آیا مطمئن هستید ؟')?>" />
            <?php
			if ($_GET['sn']!='viewUserGroups')
			{
				?>
				<input name="submit_insert" class="button" type="button" value=" <?php echo wsfGetValue('new')?> " onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
				<?php
			}
			?>
		</div>

	</form>

<?php } else { ?>
	<b><?php echo wsfGetValue('nothingFound')?></b>
	<br />
	<input name="submit_insert" class="button" type="button" value=" <?php echo wsfGetValue('new')?> " onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
<?php }
}
?>
<?php if ($paging and $paging->getTotalPages()>1) {?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
	<tr>
		<td>
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body">
						<?php echo wsfGetValue('page')?> <?php echo $paging->getPageNumber()?> <?php echo wsfGetValue('from')?> <?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		</td>
		<td align="left">
			<table class="paging-nav" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<?php if ($paging->hasPrev()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getPrevUrl()?>"><?php echo wsfGetValue('prevPage')?></a></td>
					<?php }?>
					<?php if ($paging->hasNext()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getNextUrl()?>"><?php echo wsfGetValue('nextPage')?></a></td>
					<?php }?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php }?>