<?php
$infa=new cpInterface();

$num=1; 

$viewUserSystem = clone($userSystem);
$viewUserSystem->setOption('tableName', $_cp['sectionInfo']['tableInfo']['tableName']);

if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('title');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}


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
		
		if (!isset($_columns['activated'])) $_columns['activated']=0;
		
		if (!empty($_columns['password'])) {
			if ($_columns['password']==$_columns['passwordConfirmation'])
				$columnsValues['password']=$_columns['password'];
			else
				$validateResult[]=PEAR::raiseError('رمز عبور و تکرار رمز عبور مطابقت ندارند!');
		} else {
			unset($_columns['password']);
		}
		unset($_columns['passwordConfirmation']);
		
		$_columns['fullName'] = $_columns['firstName'] .' '.$_columns['lastName'];
		$_columns['photoFilename'] = wsfUploadFileAuto(
			"rows[$num][columns][photoFilename]",
			$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
		);
		
		$_columns['birthdayDate'] = wsfConvertDateTimeDropDownArrayToDateTimeString($_columns['birthdayDate'], "Y-m-d");
		
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $_cp['sectionInfo']['tableInfo']['columns']);
		//if (!isset($columnsPhysicalName[$columnName])) echo $columnName;
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
				$msg =wsfGetValue('removeMsg');
			}
			elseif ($row['action']=='update') {
				$result=$viewUserSystem->update($columnsValues,$_GET['id']);
				$error = cmfcMySql::error();
				$msg =wsfGetValue('updateMsg');
			} 
			elseif ($row['action']=='insert') {
				$result=$viewUserSystem->insert($columnsValues);
				$error = cmfcMySql::error();
				$msg =wsfGetValue('addMsg');
			}
			#--(End)-->save changes to database
			
			if (PEAR::isError($result) or $result===false) {
				if (PEAR::isError($result))
					$messages['errors'][]=$result->getMessage();
				else
					$messages['errors'][]=$error;
				
				$isErrorOccured=true;
			} 
			else {
				$messages['messages'][]=$msg;
			}
		} 
		else {
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
		"SELECT * FROM %s WHERE %s='%s'",
		$_cp['sectionInfo']['tableInfo']['tableName'],
		$_cp['sectionInfo']['tableInfo']['columns']['id'],
		$_REQUEST['id']
	);
	$row=cmfcMySql::loadCustom($sqlQuery);
	foreach ($row as $columnName=>$columnValue) {
		$_POST['rows'][$num]['columns'][$columnName]=$columnValue;
	}
	if (!empty($row)) {
		foreach ($sectionInfo['tableInfo']['columns'] as $columnName=>$columnPhysicalName) {
			$_POST['rows'][$num]['columns'][$columnName]=$row[$columnPhysicalName];
		}
	}
	//print_r($_POST);
}

if ($_REQUEST['action']=='list'/* or $_REQUEST['action']=='edit'*/) {
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	
	#--(Begin)-->generate Sql Query
	if (isset($_REQUEST['submit_search'])) {
	
		$searchSqlWhereQuery=" AND (
			({td:firstName} LIKE '%[firstName]%' OR '[firstName]'='') AND
			({td:lastName} LIKE '%[lastName]%' OR '[lastName]'='') AND
			({td:username} LIKE '[username]' OR '[username]'='')
		)";
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
	
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE (1=1) ".$searchSqlWhereQuery;
	#--(End)-->generate Sql Query
	
	#--(Begin)-->Paging
	if (isset($_cp['sectionInfo']['listLimit']))
		$listLimit = $_cp['sectionInfo']['listLimit'];
	else
		$listLimit = 5;
	$paging=cmfcPaging::factory(
		'dbV2',array(        
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
	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}
	
	wsfWysiwygLoader(array(
		'templateName'=>'fullWidthFileAndImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][resume]"=>array('direction'=>'rtl'),			
			"rows[$num][columns][activities]"=>array('direction'=>'rtl'),			
		)
	));

	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);

	$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	$infa->showInputRow(wsfGetValue('username'),"rows[$num][columns][username]", $_POST['rows'][$num]['columns']['username'],'نام کاربري',40,'ltr');
	$infa->showInputPasswordRow(wsfGetValue('password'),"rows[$num][columns][password]", $_POST['rows'][$num]['columns']['password'],'رمز عبور',40,'ltr');
	$infa->showInputPasswordRow(wsfGetValue('repeatPassword'),"rows[$num][columns][passwordConfirmation]", $_POST['rows'][$num]['columns']['passwordConfirmation'],'تکرار رمز عبور',40,'ltr');
	$infa->showInputRow(wsfGetValue('email'),"rows[$num][columns][email]", $_POST['rows'][$num]['columns']['email'],'ايميل',40,'ltr');
	$infa->showCheckBoxRow(wsfGetValue('active'),"rows[$num][columns][activated]",$_POST['rows'][$num]['columns']['activated']);
	
	$__tableInfo=$_ws['physicalTables']['userGroups'];
	$infa->showCustomRow(wsfGetValue('userGroup'),
		cmfcHtml::drawDropDown(
			"rows[$num][columns][userGroupId]", //name
			$_POST['rows'][$num]['columns']['userGroupId'], //value
			cmfcMySql::getRows($__tableInfo['tableName'], $__tableInfo['columns']['type'], $_cp['sectionInfo']['userGroupTypeName']), //items
			$__tableInfo['columns']['id'], //valueColumnName
			$__tableInfo['columns']['name'], //titleColumnName
			NULL, //groupNameColumnName
			NULL, //groupIdColumnName
			NULL, //defaultValue
			NULL, //defaultTitle
			array(//more attributes
				'class'=>'select'
			) 
		)
	,'');
	/* */
	$infa->showSeparatorRow(wsfGetValue('importantInfo'));
	$infa->showInputRow(wsfGetValue('firstName'),"rows[$num][columns][firstName]", $_POST['rows'][$num]['columns']['firstName'],'نامتان را وارد کنيد');
	$infa->showInputRow(wsfGetValue('lastName'),"rows[$num][columns][lastName]", $_POST['rows'][$num]['columns']['lastName'],'نام خانوادگيتان را وارد کنيد.');
	
	/*$infa->showCustomRow(
		wsfGetValue('photo'),
		cpfGetImageUploadAuto(
			"rows[$num][columns][photoFilename]",
			$_POST['rows'][$num]['columns']['photoFilename'],
			$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
			$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative']
		),
		''
	);*/
	/*
	$infa->showSeparatorRow(wsfGetValue('otherInfo'));
	$__tableInfo=$_ws['virtualTables']['genderTypes'];
	$infa->showCustomRow(wsfGetValue('gender'),
		cmfcHtml::drawDropDown(
			"rows[$num][columns][gender]", //name
			$_POST['rows'][$num]['columns']['gender'], //value
			$__tableInfo['rows'], //items
			$__tableInfo['columns']['id'], //valueColumnName
			$__tableInfo['columns']['name'], //titleColumnName
			null, //groupNameColumnName
			null, //groupIdColumnName
			'', //defaultValue
			'', //defaultTitle
			array(//more attributes
				'class'=>'select'
			) 
		)
	,'');
	*/
	/*
	$currentYear = wsfGetDateTime('Y', 'now', $langInfo['sName'], 0);
	$yearRange = (($currentYear)-10)."-".(($currentYear)-100);
	$infa->showCustomRow(wsfGetValue('birthday'),
		cmfcHtml::drawDateTimeDropDownBeta(
			"rows[$num][columns][birthdayDate]",
			$_POST['rows'][$num]['columns']['birthdayDate'],
			'jalali',
			array('day','month','year'),
			array(
				'yearRange'=>$yearRange
			)
		)
	);
	/* */
	$infa->showInputRow(wsfGetValue('tel'),"rows[$num][columns][tel]", $_POST['rows'][$num]['columns']['tel'],'','','ltr');
	$infa->showInputRow(wsfGetValue('fax'),"rows[$num][columns][fax]", $_POST['rows'][$num]['columns']['fax'],'','','ltr');

	$infa->showInputRow(wsfGetValue('mobile'),"rows[$num][columns][mobile]", $_POST['rows'][$num]['columns']['mobile'],'','','ltr');
	//$infa->showInputRow(wsfGetValue('website'),"rows[$num][columns][website]", $_POST['rows'][$num]['columns']['website'],'','','ltr');
	
	$provincesTable = $_ws['physicalTables']['provinces'];
	$provinces = cmfcMySql::getRows($provincesTable['tableName']);
	//cmfcHtml::printr($_ws['physicalTables']['provinces']);
	$infa->showCustomRow(
		wsfGetValue('province'),
		cmfcHtml::drawDropDown(
			"rows[$num][columns][provinceId]"
			,$_POST['rows'][$num]['columns']['provinceId']
			,$provinces
			,'id'
			,'name'
			,NULL
			,NULL
			,''
			,wsfGetValue('no-province')
		)
		,''
	);
	
	$infa->showTextareaRow(wsfGetValue('address'),"rows[$num][columns][address]",$_POST['rows'][$num]['columns']['address'],'','',5,'97%');
	// $infa->showTextareaRow(wsfGetValue('resume'),"rows[$num][columns][resume]",$_POST['rows'][$num]['columns']['resume'],'','',5,'97%');
	// $infa->showTextareaRow(wsfGetValue('activities'),"rows[$num][columns][activities]",$_POST['rows'][$num]['columns']['activities'],'','',5,'97%');
	
	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	} elseif($_REQUEST['action'] == 'new') {
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	
	$infa->showFormFooter();
?>

<?php } elseif ($_REQUEST['action']=='list') { ?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( cmfcUrl::excludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search'),'get') )?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
			<tr>
				<td></td>
				<!--
    			<td align="left">
					<input class="quick-search-input" name="quick-search" type="text" value="qs" />
				</td>
				-->
				<td align="left" width="120">
					<table class="option-link" border="0" cellspacing="1" cellpadding="0">
						<tr>
							<td class="quick-search-button"><a href="javascript:void(0);" onClick="<?php echo $js->jsfToggleDisplayStyle('searchBoxContainer','auto')?>">جستجوی پیشرفته</a></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<div id="searchBoxContainer" style="display:none;">
		<table id="option-buttons" width="100%" border="0" cellspacing="0" cellpadding="0"> 
			<tr>
				<td  class="option-table-buttons-spacer" width="5">&nbsp;</td>
				<td id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
					<?php echo wsfGetValue('advancedSearch')?>
				</td>
				<td class="option-table-buttons-spacer" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			
			<tr class="table-row1">
				<td width="200"><?php echo wsfGetValue('username')?> : </td>
				<td ><input name="search[username]" class="input" type="text" value="<?php echo $_REQUEST['search']['username']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row2">
				<td ><?php echo wsfGetValue('firstName')?> :</td>
				<td ><input name="search[firstName]" class="input" type="text" value="<?php echo $_REQUEST['search']['firstName']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row1">
				<td ><?php echo wsfGetValue('lastName')?> : </td>
				<td ><input name="search[lastName]" class="input" type="text" value="<?php echo $_REQUEST['search']['lastName']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row2">
				<td colspan="2">
					<input class="button" type="submit" name="submit_search" value=" <?php echo wsfGetValue('search')?> " />
					<input class="button" type="button" name="submit_cancel_search" value=" <?php echo wsfGetValue('cancelSearch')?> " onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=<?php echo $_GET['action']?>'" />
				</td>
			</tr>
		</table>
		</div>
	</form>
<?php if (is_array($rows)) {
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
					<a href="<?php echo $paging->getSortingUrl('first_name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('first_name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('firstName')?>
				</td>
				<td class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('last_name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('last_name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('lastName')?>
				</td>
				<td class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('username','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('username','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('username')?>
				</td>
				<td class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('activated','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('activated','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('status')?>
				</td>
				<td class="table-title field-title">
					<?php echo wsfGetValue('userGroup')?>
				</td>
			</tr>
			<?php 			foreach ($rows as $key=>$row) {
				$num=$key+1;
					
				//--(Begin)-->convert columns physical names to their internal names
				$myRow=array();
				foreach ($sectionInfo['tableInfo']['columns'] as $columnName=>$columnPhysicalName) {
					$myRow[$columnName]=$row[$columnPhysicalName];
				}
				$row=$myRow;
				//--(End)-->convert columns physical names to their internal names
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
				
				<td class="field-title"><?php echo $row['firstName']?></td>
				<td class="field-title"><?php echo $row['lastName']?></td>
				<td class="field-title"><?php echo $row['username']?></td>
				<td class="field-title">
					<?php 					$tableInfo=$_ws['virtualTables']['userActiveStatus'];
					echo cmfcMySql::getVirtualColumnValue(
						$row['activated'],
						$tableInfo['rows'],
						$tableInfo['columns']['id'],
						$tableInfo['columns']['name']
					);?>
				</td>
				<td class="field-title">
					<?php 					$tableInfo=$_ws['physicalTables']['userGroups'];
					$userGroup = cmfcMySql::loadWithMultiKeys(
						$tableInfo['tableName'],
						array(
							$tableInfo['columns']['id'] => $row['userGroupId'],
							$tableInfo['columns']['type'] => 'viewUserGroup',
						)
					);
					echo $userGroup['name'];
					?>
				</td>
			</tr>
			<?php }?>
		</table>
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue('delete')?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value=" <?php echo wsfGetValue('new')?> " onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
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