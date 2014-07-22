<?php $interface = new cpInterface();
$num = 0;

if (!$_REQUEST['action']) $_REQUEST['action']='list';
if ($_REQUEST['action']=='list') $actionTitle="لیست";
if ($_REQUEST['action']=='edit') $actionTitle="ویرایش";
if ($_REQUEST['action']=='new') $actionTitle="جدید";
if ($_REQUEST['action']=='delete') $actionTitle="حذف";

$emailTemplatesTable = $_cp['sectionInfo']['tableInfo'];
$templates = '';

$fieldsValidationInfo=array(
/*
	"rows[$num][columns][title]"=>array(
		'name'=>"rows[$num][columns][title]",
		'headName'=>'title',
		'title'=>' عنوان مقاله(فارسی)',
		'type'=>'string',
		'param'=>array(
			'notEmpty'=>true
		)
	),
*/	
	/*"rows[$num][columns][internalName]"=>array(
		'name'=>"rows[$num][columns][internalName]",
		'headName'=>'internalName',
		'title'=>' نام داخلی',
		'type'=>'string',
		'param'=>array(
			'notEmpty'=>true
		)
	),
	*/
);

$validation->setOption('fieldsInfo',$fieldsValidationInfo);
$validation->setOption('formName', 'templateForm');

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
		
		$columnsPhysicalName = $sectionInfo['tableInfo']['columns'];
		
		if ($_POST['submit_mode']=='multi' and $row['selected']!='true')
			continue;
		if (empty($row['action'])) $row['action']=$_POST['submit_action'];
		
		if($row['action']=='update')
			$_columns['updateDatetime'] = date("Y-m-d H:i:s");
		elseif($row['action']=='insert') 
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
        
		if ($row['action']!='delete') 
		{			
			if (is_array($fieldsValidationInfo))
				$validateResult=$validation->validate($_columns, "rows[$num][columns][%s]");
		}
		
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		
		if (empty($validateResult)) 
		{
			#--(Begin)-->save changes to database
			if ($row['action']=='delete') 
			{
				/* $result=cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues['id']
				); 
				$msg = wsfGetValue('removeMsg');
				*/
			}
			elseif ($row['action']=='update') 
			{
				$result=cmfcMySql::update(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues,
					$_GET['id']
				);
				$error = cmfcMySql::error();
				$msg = wsfGetValue('updateMsg');
			} 
			elseif ($row['action']=='insert') 
			{
				$result = cmfcMySql::insert($_cp['sectionInfo']['tableInfo']['tableName'], $columnsValues);
				$error = cmfcMySql::error();
				$msg = wsfGetValue('addMsg');
				$_GET['id'] = cmfcMySql::insertId();
			}
			
			if (PEAR::isError($result) or $result===false) 
			{
				$messages['errors'][] = 'error occured: '.$error;
				$isErrorOccured=true;
			} 
			else 
			{
				$messages['messages'][] = $msg;
				cpfLog($_cp['sectionInfo']['name'], $userSystem->cvId, array('name'=>$row['action'],'rowId'=>$_GET['id']));
			}
		} 
		else 
		{
			foreach ($validateResult as $r) 
				$messages['errors'][]=$r->getMessage();
			$isErrorOccured=true;
		}		
	};


	if (!PEAR::isError($result)) {
		if (!$isErrorOccured) {
			#--(Begin)-->redirect to previous url if everthings is ok
			$messages['messages'][]=sprintf(
				'<META http-equiv="refresh" content="1;URL=?%s">',
				cmfcUrl::excludeQueryStringVars(array('action'),'get')
			);
			#--(End)-->redirect to previous url if everthings is ok
			$saved=true;
		}
	}
}


cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();


wsfPrintMessages($messages);
if (!$_POST['submit_save'] and $_REQUEST['action']=='edit') 
{
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

if ( in_array( $_REQUEST['action'], array('new', 'edit') ) && !isset($_POST['submit_save']) ){
	
	
	wsfWysiwygLoader(array(
		'templateName'=>'fullWidthFileAndImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][body]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));
	
	$interface->showFormHeader(null,'templateForm',true);
	
	$interface->showTableHeader($actionTitle);
	
	$interface->showSeparatorRow('مشخصات ایمیل');
	
	$interface->showInputRow('عنوان',"rows[$num][columns][name]", $_POST['rows'][$num]['columns']['name'],'',40,'rtl');
			
	$interface->showInputRow('موضوع',"rows[$num][columns][subject]", $_POST['rows'][$num]['columns']['subject'],'',40,'ltr');
	$interface->showInputRow('موضوع جزئی',"rows[$num][columns][inlineSubject]", $_POST['rows'][$num]['columns']['inlineSubject'],'',40,'ltr');
		
	$interface->showTextareaRow('متن ایمیل',"rows[$num][columns][body]",$_POST['rows'][$num]['columns']['body'],'','',10,'90%');
	
	if ($_REQUEST['action'] == 'edit') {
		$interface->showHiddenInput("rows[$num][action]", "update");
		$interface->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	} elseif($_REQUEST['action'] == 'new') {
		$interface->showHiddenInput("rows[$num][action]", "insert");
	}
	
	$interface->showFormFooter();
}

if ($_REQUEST['action']=='list') {
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	#--(Begin)-->generate Sql Query
	
	//echo $_REQUEST['submit_search']."<br>";
	
	if (isset($_REQUEST['submit_search'])) {
		$where = '';
		$OR = "";
		
		/*$searchSqlWhereQuery = " AND (
			({td:title} LIKE '%[title]%') AND
			({td:body} LIKE '%[body]%' )
		)";*/
		
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
	
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE (1=1) ".$searchSqlWhereQuery;
	#--(End)-->generate Sql Query
	
	//echo $sqlQuery;
	
	#--(Begin)-->Paging
	$oldSortBy=$_REQUEST['sortBy'];
	@$_REQUEST['sortBy']=$_cp['sectionInfo']['tableInfo']['columns'][$_REQUEST['sortBy']];
	
	if (isset($_cp['sectionInfo']['listLimit']))
		$listLimit = $_cp['sectionInfo']['listLimit'];
	else
		$listLimit = 5;
	
	$paging=cmfcPaging::factory('dbV2',array(  'total'=>null,
		'limit'=>$listLimit,
		'sqlQuery'=>$sqlQuery,
		'wordNext'=>'بعدی',
		'wordPrev'=>'قبلی',
		'link'=>'?'.cmfcUrl::excludeQueryStringVars(array('sectionName','pageType'),'get'),

		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['orderByColumnName'],
		'sortType'=>'DESC',
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	$sqlQuery=$paging->getPreparedSqlQuery();
	$_REQUEST['sortBy']=$oldSortBy;
	#--(End)-->Paging
	
	//echo $sqlQuery."<br>";
	
	#--(Begin)-->Execute Query and fetch the rows
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
	
	//print_r($rows);
	//echo "<br>";

	if (is_array($rows)) {
	?>
	<form name="myListForm" action="?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
		
		<table id="listFormTable" class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="10" class="table-header"> فهرست  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px">
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td class="table-title field-title" style="width:35px">
					ابزار
				</td>
				
				<td class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					عنوان
				</td>
				<td class="table-title field-title">
					موضوع
				</td>
				<td class="table-title field-title">
					متن
				</td>
				<td class="table-title field-title">
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
					<a href="?sn=<?php echo $_GET['sectionName']?>&action=edit&id=<?php echo $row['id']?>">
					<img src="interface/images/action_edit.png" width="16" height="16" border="0" alt="edit" title="edit" />
					</a>
					
					<a onclick="return <?php echo $js->jsfConfimationMessage('Are you sure ?')?>" href="?sn=<?php echo $_GET['sectionName']?>&action=delete&id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" height="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				
				<td class="field-title"><?php echo $row['name']?></td>
				<td class="field-title" dir="ltr">
				<?php echo $row['subject']?>
				</td>
				<td class="field-title"><?php echo cmfcString::briefText(strip_tags($row['body']), 100);?></td>
				<td class="field-title">
					[<a href="javascript:void(0)" onclick="<?php echo $js->jsfPopitup('popup.php?sn=emailTemplatePreview&internalName='.$row['internalName'],600,500,'fixWithScrollbars')?>">نمونه</a>]
				</td>
				
			</tr>
			<?php }?>
		</table>
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" حذف " onclick="return <?php echo $js->jsfConfimationMessage('آیا مطمئن هستید ؟')?>" />
			<!--input name="submit_insert" class="button" type="button" value=" جدید " onclick="window.location='?sn=<?php $_GET['sectionName']?>&action=new'" /-->
		</div>

	</form>
	<?php 	}
	else { ?>
		<b>هيچ رکوردي موجود نيست</b>
		<br />
		<!--input name="submit_insert" class="button" type="button" value=" جدید " onclick="window.location='?sn=<?php $_GET['sectionName']?>&action=new'" /-->
		<?php 
	}
}

if ($paging and $paging->getTotalPages()>1) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
	<tr>
		<td>
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body">
						صفحه <?php echo $paging->getPageNumber()?> از <?php echo $paging->getTotalPages()?>
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
					<td class="paging-nav-body"><a href="<?php echo $paging->getPrevUrl()?>">صفحه قبلی</a></td>
					<?php }?>
					<?php if ($paging->hasNext()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getNextUrl()?>">صفحه بعدی</a></td>
					<?php }?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php }
?>