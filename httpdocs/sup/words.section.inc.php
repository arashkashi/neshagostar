<?php
$infa=new cpInterface();

if(isset($_POST['relatedItemId']) )
	$relatedItemId = $_POST['relatedItemId'];

if ($_GET['nextLang'] && ! $_POST['itemLanguage'] )
	$_POST['itemLanguage'] = $_GET['nextLang'];

if($_REQUEST['id'] ){
	$related = cmfcMySql::load($_cp['sectionInfo']['tableInfo']['tableName'],'id',$_REQUEST['id']);
	$related = cmfcMySql::convertColumnNames($related, $_cp['sectionInfo']['tableInfo']['columns']);
	$relatedItemId = $related['relatedItem'];
	
	if (!$_POST['itemLanguage'])
		$itemLanguage = cmfcMySql::load($_cp['sectionsInfo']['languages']['tableInfo']['tableName'], 'id', $related['languageId']);
	else
		$itemLanguage = cmfcMySql::load($_cp['sectionsInfo']['languages']['tableInfo']['tableName'], 'id', $_POST['itemLanguage']);
}
else{
	$itemLanguage = cmfcMySql::load(
		$_cp['sectionsInfo']['languages']['tableInfo']['tableName'],
		'id',
		$_POST['itemLanguage']
	);
}

if (!$itemLanguage){
	$itemLanguage = cmfcMySql::load(
		$_cp['sectionsInfo']['languages']['tableInfo']['tableName'],
		$_cp['sectionsInfo']['languages']['tableInfo']['columns']['id'], 
		$langInfo['id']
	);
}

if($_GET['itemLanguage'])
	$itemLanguage = cmfcMySql::load($_cp['sectionsInfo']['languages']['tableInfo']['tableName'],'id',$_GET['itemLanguage']);

if (!$relatedItemId)
	$relatedItemId = time();

$editable = cmfcMySql::loadWithMultiKeys(
	$_cp['sectionInfo']['tableInfo']['tableName'],
	array(
		$_cp['sectionInfo']['tableInfo']['columns']["languageId"] => $itemLanguage['id'],
		$_cp['sectionInfo']['tableInfo']['columns']["relatedItem"] => $relatedItemId
	)
);
if($editable) 
	$_GET['id'] = $_REQUEST['id'] = $editable['id'];
else
	$_GET['id'] = $_REQUEST['id'] = '';
	
//$overrideValues = wsfGetOverrideValues($relatedItemId);

$num=1;
$messages=array();

if ($_POST['changeLangButton']) { 
	unset($_POST['rows'][$num]['columns']);
}

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

if ($_POST['submit_delete_garbage']) {
	$deleteQuery = "DELETE FROM `".$_cp['sectionInfo']['tableInfo']['tableName']."` WHERE `".$_cp['sectionInfo']['tableInfo']['columns']['totalRequest']."`=0 OR `".$_cp['sectionInfo']['tableInfo']['columns']['totalRequest']."` IS NULL";
	$result=cmfcMySql::exec($deleteQuery);
	$error = cmfcMySql::error();
	$msg = wsfGetValue('deleteGarbageMsg');
	if (PEAR::isError($result) or $result===false) {
		$messages['errors'][] = 'error occured: '.$error;
	} else {
		$messages['messages'][] = $msg;
		$url = wsfExcludeQueryStringVars(array('pt', 'id','action', 'nextLang', 'itemLanguage', ),'get') ;
		$messages['other'][]=sprintf('<META http-equiv="refresh" content="1;URL=?%s">', $url);
	}
	$_REQUEST['action'] = 'deleteGarbage';
}

if ($_POST['submit_reset']) {
	$updateQuery = "UPDATE `".$_cp['sectionInfo']['tableInfo']['tableName']."` SET `".$_cp['sectionInfo']['tableInfo']['columns']['totalRequest']."`=0, `".$_cp['sectionInfo']['tableInfo']['columns']['sectionNames']."`=','";
	$result=cmfcMySql::exec($updateQuery);
	$error = cmfcMySql::error();
	$msg = wsfGetValue('resetMsg');
	if (PEAR::isError($result) or $result===false) {
		$messages['errors'][] = 'error occured: '.$error;
	} else {
		$messages['messages'][] = $msg;
		$url = wsfExcludeQueryStringVars(array('pt', 'id','action', 'nextLang', 'itemLanguage', ),'get') ;
		$messages['other'][]=sprintf('<META http-equiv="refresh" content="1;URL=?%s">', $url);
	}
	$_REQUEST['action'] = 'reset';
}



if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}

if (isset($_POST['cancel'])){
	echo '<META http-equiv="refresh" content="0;URL=?'.cmfcUrl::excludeQueryStringVars(array('action', 'sectionName', 'pageType', 'pt', 'key', 'langId', 'id'),'get').'">';
	$_REQUEST['action'] = '';
}

if ((isset($_POST['submit_save']) and $_GET['action']!='view') || isset($_POST['saveEditNextLang'])) {

	foreach ($_POST['rows'] as $num=>$row) {		
		$_columns=&$_POST['rows'][$num]['columns'];
		$columnsPhysicalName=$sectionInfo['tableInfo']['columns'];
		
		
		#--(Begin)-->prepare for multi action
		if ($_POST['submit_mode']=='multi' and $row['selected']!='true')
			continue;
		if (empty($row['action'])) $row['action']=$_POST['submit_action'];
		if ($_columns['id'])
			$_GET['id'] = $_columns['id'];
				
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;

		if($row['action'] == "insert")
		{
			$_columns['sectionNames'] = ",";
			if(cpfIsWordKeyExists($_columns['key'], $_columns['languageId'], $_columns['relatedItem']))
			{
				$validateResult[] = PEAR::raiseError(wsfGetValue('keyExists'));
			}
		}	
			
		#--(End)-->prepare for multi action
		//cmfcHtml::printr($_columns);
		//die;
		
		
		$columnsValues=array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		//cmfcHtml::printr($columnsValues);
		//cmfcHtml::printr($_columns);
			
		#--(End)-->fill and validate fields
		
		if (empty($validateResult)) {
			#--(Begin)-->save changes to database
			if ($row['action']=='delete') {
				$result=cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$_GET['id']
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
				$_GET['id'] = cmfcMySql::insertId();
				$error = cmfcMySql::error();
				$msg = wsfGetValue('addMsg');
			}
			//cmfcHtml::printr($columnsValues);
			#--(End)-->save changes to database
			
			if (PEAR::isError($result) or $result===false) {
				//$messages['errors'][] = $result->getMessage();
				$messages['errors'][] = 'error occured: '.$error;
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
	}

	if (!PEAR::isError($result)) {
		if (!$isErrorOccured) {
			#--(Begin)-->redirect to previous url if everthings is ok
			if(isset($_POST['saveEditNextLang'])){
				$nextLang = cpfGetNextEditableLang($itemLanguage['id']);
				$url = wsfExcludeQueryStringVars(array('pageType','sectionName','pt', 'nextLang',),'get')."nextLang=".$nextLang ;
			}
			else{
				$url = wsfExcludeQueryStringVars(array('action', 'nextLang', 'itemLanguage', ),'get') ;
			}
			$messages['other'][]=sprintf('<META http-equiv="refresh" content="1;URL=?%s">', $url);
			#--(End)-->redirect to previous url if everthings is ok
			$saved=true;
		}
	}
	
}

if (!$_POST['submit_save'] and $_REQUEST['action']=='edit') {
	
	if ($editable){
		$_POST['rows'][$num]['columns'] = $editable;
	}
	if ($_REQUEST['action']=='edit') {
	
		if($itemLanguage['id'] ){
			$sqlQuery = "SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE language_id = '".$itemLanguage['id']."' AND related_item = '".$relatedItemId."'";
			$_POST = array();
		}
		else{
			$sqlQuery=sprintf(
				"SELECT * FROM %s WHERE %s='%s' ",
				$_cp['sectionInfo']['tableInfo']['tableName'],
				$_cp['sectionInfo']['tableInfo']['columns']['id'],
				$_REQUEST['id']
			);
		}
		//echo $sqlQuery;
		 
		$row=cmfcMySql::loadCustom($sqlQuery);
		
		if (!empty($row)) {
			$_POST['rows'][$num]['columns'] = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
		}
		else{
			$_REQUEST['action'] = "new";
		}
	}
}

if ($_REQUEST['action']=='list') {
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	#--(Begin)-->generate Sql Query
	
	//echo $_REQUEST['submit_search']."<br>";
	
	
	if (!isset($_REQUEST['viewLangId']) )
		$_REQUEST['viewLangId'] = $itemLanguage['id'];

if (isset($_REQUEST['submit_search'])) {
		
		if($_REQUEST['search']['key'] != ""){
			$searchSqlWhereQuery = " AND (
				`{td:key}` LIKE '%[key]%')";		
		}
		if($_REQUEST['search']['value']  != ""){
			$searchSqlWhereQuery = " AND (
				`{td:value}` LIKE '%[value]%')";	
		}
		

		if($_REQUEST['search']['specific'])
		{
			$searchSqlWhereQuery .= " AND (
				`{td:specific}` = 1)";
		}
		
		$replacements=array(
			'{td:key}'=>$_cp['sectionInfo']['tableInfo']['columns']['key'],
			'{td:value}'=>$_cp['sectionInfo']['tableInfo']['columns']['value'],
			'{td:specific}'=>$_cp['sectionInfo']['tableInfo']['columns']['siteSpecific'],
			'[key]'=>$_REQUEST['search']['key'],
			'[value]'=>$_REQUEST['search']['value'],
	
		);
		
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
		
		unset($_REQUEST['viewLangId']);	
	}
	
	if ($_REQUEST['viewLangId'])
		$searchSqlWhereQuery .= " AND ".$_cp['sectionInfo']['tableInfo']['columns']['languageId']." ='".$_REQUEST['viewLangId']."'";
		
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE (1=1) ".
		$searchSqlWhereQuery;
		
	$sqlQueryCount = "SELECT count(`id`) AS count FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE (1=1) ".
		$searchSqlWhereQuery;
		
	$wordCount = cmfcMySql::loadCustom($sqlQueryCount);
		
	#--(End)-->generate Sql Query
	
	//$sqlQuery .= " GROUP BY related_item";
	
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
		'link'=>'?'.wsfExcludeQueryStringVars(array('sectionName','pageType'),'get'),
		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['orderByColumnName'],
		'sortType'=>$_cp['sectionInfo']['tableInfo']['orderType'],
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	
	//echo $sqlQuery;
	$sqlQuery=$paging->getPreparedSqlQuery();
	#--(End)-->Paging
	
	//echo $sqlQuery."<br>";
	
	#--(Begin)-->Execute Query and fetch the rows
	//echo $sqlQuery;
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
	
	//print_r($rows);
	//echo "<br>";
}

cpfCreateBreadCrumb($actionTitle);
wsfPrintMessages($messages);

if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) {
	
	if ($overrideValues){
		$editValues = wsfConvertOverrideValuesForEditAction($overrideValues);
		foreach ($editValues as $fieldName => $fieldValue){
			if (!$_POST['rows'][$num]['common'][$fieldName])
				$_POST['rows'][$num]['common'][$fieldName] = $fieldValue;
		}
	}
	
	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}

	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader( $actionTitle);
	
	$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName'];
	$items = cmfcMySql::getRowsCustom($query);
	
	if($items){
		foreach($items as $key=>$item){
			$options[] = array(
				'id'=>$item['id'],
				'label'=>$item['name'],
				'href'=>"#",
				'onClick'=>"changeLang(".$item['id'].")",
			);
		}
	}

	$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	$infa->drawMultiTabs($options, $itemLanguage['id']);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	
	$a = cmfcMySql::load($_cp['sectionInfo']['tableInfo']['tableName'],"related_item",$relatedItemId);

	if($a)
		$_POST['rows'][$num]['columns']['key'] = $a['key'];
	
	if(isset($_GET['key'])) $_POST['rows'][$num]['columns']['key'] = $_GET['key'];
	
	if ($_REQUEST['action'] == 'edit')
		$disabled = true;
	else
		$disabled = false;
	
	$infa->showInputRow(wsfGetValue(key) .' ('. wsfGetValue(noChange) .') ',"rows[$num][columns][key]", $_POST['rows'][$num]['columns']['key'],'Don\'t use  prefix',40, 'ltr', $disabled);
	$infa->showHiddenInput("keyOverride", "1");
	
	$infa->showInputRow(wsfGetValue(value),"rows[$num][columns][value]", $_POST['rows'][$num]['columns']['value'],'',40,$itemLanguage['direction']);
	
	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
		//$infa->showHiddenInput("rows[$num][columns][prefix]", $_POST['rows'][$num]['columns']['prefix']);
	}
	elseif($_REQUEST['action'] == 'new') {
		$infa->showHiddenInput("rows[$num][action]", "insert");
		//$infa->showHiddenInput("rows[$num][columns][prefix]", $_REQUEST['prefix']);
	}
	
	
	
	$buttons = array(
		array(
			'name' => 'submit_save',
			'value' => wsfGetValue(buttonSubmit),
		),
		
		array(
			'name' => 'saveEditNextLang',
			'value' =>  wsfGetValue(submit_and_go_next_lang),
		),
		
		array(
			'name' => 'reset',
			'value' => wsfGetValue(buttonReset),
			'type' => 'reset',
		),
		
		array(
			'name' => 'changeLangButton',
			'value' => '1',
			'type' => 'submit',
			'attributes' => array(
				'style' => 'display:none',
			),
		),
		array(
			'name' => 'cancel',
			'value' => wsfGetValue(buttonCancel),
		),
	
	);
	$infa->showFormFooterCustom($buttons);
}
elseif ($_REQUEST['action']=='list'){
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" >
			<tr>
				<td ></td>
				<td width="120">
					<table class="option-link" border="0" cellspacing="1" cellpadding="0" >
						<tr>
							<td class="quick-search-button" nowrap="nowrap">
							<?php 							$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName'];
							$items = cmfcMySql::getRowsCustom($query);
							?>
							
							<?php echo  wsfGetValue('select_language')?>&nbsp;
							
							<?php echo cmfcHtml::drawDropDown(
								'viewLangId', 
								$_REQUEST['viewLangId'],
								$items,
								'id',
								'name',
								null,
								null,
								null,
								null,
								array('onChange'=>"selectLang()")
							);
							?>
						</td>
						</tr>
					</table>
				</td>
				
				<td></td>
			    <td align="<?php echo $langInfo['htmlNAlign']?>" >
				
				<table border="0" cellpadding="0" cellspacing="1" class="option-link" align="<?php echo $langInfo['htmlNAlign']?>"  >
                  <tr>
                    <td class="quick-search-button"  align="<?php echo $langInfo['htmlNAlign']?>" ><a href="javascript:void(0);" onclick="<?php echo $js->jsfToggleDisplayStyle('searchBoxContainer','auto')?>"><?php echo wsfGetValue(advancedSearch);?></a></td>
                  </tr>
                </table></td>
			</tr>
		</table>
		
		<div id="searchBoxContainer" style="
		<?php 		if (!$_REQUEST['search']){
			?>
			display:none;
			<?php 		}
		?>">
		<table id="option-buttons" width="100%" border="0" cellspacing="0" cellpadding="0"> 
			<tr>
				<td  class="option-table-buttons-spacer" width="5"  >&nbsp;</td>
				<td   id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo wsfGetValue(advancedSearch)?>
				</td>
				<td class="option-table-buttons-spacer"  >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			
			<tr class="table-row1"  >
				<td width="200"><?php echo wsfGetValue(key)?> </td>
				<td  ><input name="search[key]" class="input" type="text" value="<?php echo $_REQUEST['search']['key']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row2" >
				<td  ><?php echo wsfGetValue(value)?></td>
				<td   ><input name="search[value]" class="input" type="text" value="<?php echo $_REQUEST['search']['value']?>" style="width:50%" /></td>
			</tr>
			
			
			<tr class="table-row1">
				<td colspan="2" >
					<input class="button" type="submit" name="submit_search" value="<?php echo wsfGetValue('search')?>" />
					<input class="button" type="button" name="submit_cancel_search" value="<?php echo wsfGetValue('cancelSearch')?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=<?php echo $_GET['action']?>'" />
				</td>
			</tr>
		</table>
		</div>
		
	</form>
	
	<form name="myListForm"  action="?<?php echo wsfExcludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
	<input type="hidden" id="listlang" name="listlang" value="" />
	<?php 	if (is_array($rows)) {
		$wordLangs = cmfcMySql::getRowsWithCustomIndex($_cp['sectionsInfo']['languages']['tableInfo']['tableName'], NULL, NULL, $_cp['sectionsInfo']['languages']['tableInfo']['columns']['id']);
		
	?>
		<table id="listFormTable" class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="10" class="table-header"  > <?php echo $actionTitle ?> (<?php echo $wordCount['count']?> مورد يافت شد) </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" >
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td  nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo wsfGetValue(tools)?>
				</td>
				
				<td  nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('key','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('key','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue(key)?>
				</td>
				<td  nowrap="nowrap" class="table-title field-title" >
					
					<a href="<?php echo $paging->getSortingUrl('value','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('value','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					
					<?php echo wsfGetValue(value)?>
				</td>
				<td  nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue(lang)?>
				</td>
				
				<td  nowrap="nowrap" class="table-title field-title" >
					
					<a href="<?php echo $paging->getSortingUrl('total_request','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('total_request','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					
					<?php echo wsfGetValue(totalRequest)?>
				</td>
				
			</tr>
			<?php 			foreach ($rows as $key=>$row) {
				//print_r($row);
				$num=$key+1;
					
				//--(Begin)-->convert columns physical names to their internal names
				$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
				//--(End)-->convert columns physical names to their internal names
				$actionsBaseUrl = cmfcUrl::excludeQueryStringVars(
					array(
						'sectionName',
						'pageType',
						'action',
						'id',
						'nextLang',
						'itemLanguage'
					),
					'get'
				);
				?>
				<tr class="table-row1"   onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
				<td class="field-title" >
					<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-checkbox" >
					<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				</td>
				<td class="field-title" >
					<a href="?<?php echo $actionsBaseUrl?>&action=edit&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_edit.png" width="16" border="0" alt="edit" title="edit" />
					</a>
					 
					<a onclick="return <?php echo $js->jsfConfimationMessage( wsfGetValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&action=delete&id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				
				<td class="field-title"  > <?php echo $row['key'];?></td>
				<td class="field-title"  ><?php echo $row['value'];?></td>
				<td class="field-title"  ><?php echo $wordLangs[$row['languageId']]['name'];?></td>
				<td class="field-title"  ><?php echo $row['totalRequest'];?></td>
				
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="submit" value="<?php echo wsfGetValue(buttonNew) ?>"  />
			<?php 			if($_ws['configurations']['debugModeEnabled'])
			{
			?>
			<input name="submit_reset" class="button" type="submit" value=" <?php echo wsfGetValue(resetTotalRequest) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_delete_garbage" class="button" type="submit" value=" <?php echo wsfGetValue(deleteGarbage) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<?php 			}
			?>
		</div>
	<?php 	}
	else { 
		?>
		<b><?php echo  wsfGetValue('nothingFound')?></b>
		<br />
		<input name="submit_insert" class="button" type="submit" value="<?php echo wsfGetValue(buttonNew) ?>"  />
		<?php 
	}
	?>
	</form>
<?php }
 if ($paging and $_REQUEST['action']=='list') {?>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tr>
		<td  >
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" >
						<?php echo  wsfGetValue(page)?> <?php echo $paging->getPageNumber()?> <?php echo  wsfGetValue(from)?> <?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		</td>
		<td >
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