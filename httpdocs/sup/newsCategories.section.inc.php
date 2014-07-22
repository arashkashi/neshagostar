<?php
$infa=new cpInterface();
$treeTableDB = $_ws['physicalTables']['categories'];

if(isset($_POST['relatedItemId']) )
	$relatedItemId = $_POST['relatedItemId'];

if ($_GET['nextLang'] && ! $_POST['itemLanguage'] )
	$_POST['itemLanguage'] = $_GET['nextLang'];

if($_REQUEST['id'] )
{
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
		$translation->languageInfo['id']
	);
}

if($_GET['itemLanguage'])
	$itemLanguage = cmfcMySql::load($_cp['sectionsInfo']['languages']['tableInfo']['tableName'],'id',$_GET['itemLanguage']);

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

if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = $translation->getValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = $translation->getValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = $translation->getValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = $translation->getValue('remove');}

if ((isset($_POST['submit_save']) and $_GET['action']!='view') || isset($_POST['saveEditNextLang'])) 
{
	foreach ($_POST['rows'] as $num=>$row) 
	{
		$_columns=&$_POST['rows'][$num]['columns'];
		$_commonColumns = &$_POST['rows'][$num]['common'];
		$columnsPhysicalName=$sectionInfo['tableInfo']['columns'];

		#--(Begin)-->prepare for multi action
		if ($_POST['submit_mode']=='multi' and $row['selected']!='true')
			continue;
		if (empty($row['action'])) $row['action']=$_POST['submit_action'];
		if (!$_GET['id'])
			$_GET['id'] = $_columns['id'];
		#--(End)-->prepare for multi action
		
		if($row['action']=='update')
		{
			$_columns['updateDatetime'] = date("Y-m-d H:i:s");
		}
		elseif($row['action']=='insert')
		{
			$userInfo = $userSystem->getOption("userInfo");			
			$_commonColumns['authorId'] = $userSystem->cvId;
			$_commonColumns['authorName'] = $userInfo['first_name']." ".$userInfo['last_name'];
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
		}
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;

		$_commonColumns['publishDatetime'] = wsfConvertDateTimeDropDownArrayToDateTimeString($_commonColumns['publishDatetime']);
		
		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename'])
		{
			$_commonColumns['photoFilename'] = wsfUploadFileAuto(
				"rows[$num][common][photoFilename]",
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
			);
		}
		/*
		if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId']){
			$_commonColumns['categoryPath'] = wsfGetCategoryPathForDb(
				$treeTableDB['tableName'], 
				$_commonColumns['categoryId'],
				$selectTitleValuePair, 
				$selectTitleValuePair['value'] 
			);
		}
		*/
		$columnsValues=array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		
		#--(End)-->fill and validate fields
		
		if (empty($validateResult)) 
		{
			#--(Begin)-->save changes to database
			if ($row['action']=='delete') {
				$result=cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues['id']
				);
				$error = cmfcMySql::error();
				$msg = $translation->getValue('removeMsg');
			}
			elseif ($row['action']=='update') {
				$result=cmfcMySql::update(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues,
					$_GET['id']
				);
				$error = cmfcMySql::error();
				$msg = $translation->getValue('updateMsg');
			}
			elseif ($row['action']=='insert') {
				$result=cmfcMySql::insert($_cp['sectionInfo']['tableInfo']['tableName'], $columnsValues);
				$_GET['id'] = cmfcMySql::insertId();
				$error = cmfcMySql::error();
				$msg = $translation->getValue('addMsg');
				
				if(!$relatedItemId)
				{
					$updateColumnsValues = array(
						$sectionInfo['tableInfo']['columns']['relatedItem'] => $_GET['id']
					);
					
					cmfcMySql::update(
						$sectionInfo['tableInfo']['tableName'],
						$sectionInfo['tableInfo']['columns']['id'],
						$updateColumnsValues,
						$_GET['id']
					);
					
					$relatedItemId = $_GET['id'];
				}
			}
			
			wsfSaveOverrideValues($_commonColumns, $relatedItemId);
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

	if (!PEAR::isError($result)) 
	{
		if (!$isErrorOccured) {
			#--(Begin)-->redirect to previous url if everthings is ok
			if(isset($_POST['saveEditNextLang'])){
				$nextLang = cpfGetNextEditableLang($itemLanguage['id']);
				$messages['messages'][]=sprintf(
					'<META http-equiv="refresh" content="1;URL=?%s">',
					wsfExcludeQueryStringVars(array('pageType','sectionName','pt', 'nextLang',),'get')."nextLang=".$nextLang
				);
			}
			else{
				$messages['messages'][]=sprintf(
					'<META http-equiv="refresh" content="1;URL=?%s">',
					wsfExcludeQueryStringVars(array('action', 'nextLang', 'itemLanguage', ),'get')
				);	
			}
			#--(End)-->redirect to previous url if everthings is ok
			$saved=true;
		}
	}
}

if (!$_POST['submit_save'])
{
	if ($editable)
	{
		$_POST['rows'][$num]['columns'] = $editable;
	}
	if ($_REQUEST['action']=='edit') {
	
		if($itemLanguage['id'] )
		{
			$sqlQuery = "SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE language_id = '".$itemLanguage['id']."' AND related_item = '".$relatedItemId."'";
			//$_POST = array();
		}
		else
		{
			$sqlQuery=sprintf(
				"SELECT * FROM %s WHERE %s='%s' ",
				$_cp['sectionInfo']['tableInfo']['tableName'],
				$_cp['sectionInfo']['tableInfo']['columns']['id'],
				$_REQUEST['id']
			);
		}
		 
		$row=cmfcMySql::loadCustom($sqlQuery);
		
		if (!empty($row)) 
		{
			$_POST['rows'][$num]['columns'] = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
		}
		else
		{
			$_REQUEST['action'] = "new";
		}
	}
}

if ($_REQUEST['action']=='list') 
{
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	#--(Begin)-->generate Sql Query
	
	if (isset($_REQUEST['submit_search'])) 
	{
		if($_REQUEST['search']['title'] != "" && $_REQUEST['search']['body'] != "")
		{
			$searchSqlWhereQuery = " AND (
				(`{td:body}` LIKE '%[body]%') AND
				(`{td:title}` LIKE '%[title]%' )
			)";
			
		}elseif($_REQUEST['search']['title'] != ""){
		
			$searchSqlWhereQuery = " AND (
				`{td:title}` LIKE '%[title]%')";
				
		}elseif($_REQUEST['search']['body']  != ""){
			
			$searchSqlWhereQuery = " AND (
				`{td:body}` LIKE '%[body]%')";
			
		}
		
		$replacements=array(
			'{td:title}'=>$_cp['sectionInfo']['tableInfo']['columns']['title'],
			'{td:body}'=>$_cp['sectionInfo']['tableInfo']['columns']['body'],
			'[title]'=>$_REQUEST['search']['title'],
			'[body]'=>$_REQUEST['search']['body'],
	
		);
		
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
		
			
	}
	if (!isset($_REQUEST['viewLangId']) )
		$_REQUEST['viewLangId'] = $itemLanguage['id'];
	
	if ($_REQUEST['viewLangId'])
		$searchSqlWhereQuery .= " AND ".$_cp['sectionInfo']['tableInfo']['columns']['languageId']." ='".$_REQUEST['viewLangId']."'";
		
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName'].
		" WHERE ".$_cp['sectionInfo']['tableInfo']['columns']['languageId']." <> '0' ".
		$searchSqlWhereQuery;
		
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
		'wordNext'=> $translation->getValue(next) ,
		'wordPrev'=> $translation->getValue(prev),
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
	//echo $sqlQuery."<br/>";
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
	
	//print_r($rows);
	//echo "<br>";
}


cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

if (is_array($messages)) {
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
		<div class="<?php echo $class?>" dir="<?php echo $translation->languageInfo['direction']?>" align="<?php echo $translation->languageInfo['align']?>" >
			<?php echo implode('<br />',$messageList)?>
		</div>
		<?php 	}
}
?>
<script language="javascript" type="text/javascript" >
//<![CDATA[
	function changeLang(lang){
		document.getElementById('itemLanguage').value = lang;
		document.forms['myForm'].changeLangButton.click();
	}
	
	function changeListLang(lang ){
		flag = true;
		var oldLoc = new String(window.location);
		var locArray = oldLoc.split('?');
		
		var newLoc = new String('');
		
		for(var i = 0; i < locArray.length; i++){
			var arg = new String(locArray[i]);
			var argArray = arg.split('&');
			for(var j = 0; j < argArray.length; j++){
				var slc = new String(argArray[j]);
				var slcArray = slc.split('=');
				if(slcArray[0] != 'itemLanguage'){	
					if(flag){
						newLoc += slcArray[0]+'?';
						flag = false;
					}else{
						newLoc += slcArray[0]+'='+slcArray[1]+'&';
					}
					
				}
			}
		}
		newLoc += 'itemLanguage='+lang;
		window.location = newLoc;
	}
	
	function selectLang(){
		document.mySearchForm.submit();
	}
//]]>
</script>
<?php 
if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) 
{
	if($relatedItemId)
	{
		if($_POST['rows'][$num]['common'])
		{
			$commonColumns = cmfcMySql::load($sectionInfo['tableInfo']['tableName'], $sectionInfo['tableInfo']['columns']['relatedItem'], $relatedItemId);
			$_POST['rows'][$num]['common']['photoFilename'] = $commonColumns['photo_filename'];
		}
		else
		{
			$commonColumns = cmfcMySql::load($sectionInfo['tableInfo']['tableName'], $sectionInfo['tableInfo']['columns']['relatedItem'], $relatedItemId);
			$_POST['rows'][$num]['common'] = cmfcMySql::convertColumnNames($commonColumns, $sectionInfo['tableInfo']['columns']);
		}
	}
	
	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}
	/*
	wsfWysiwygLoader(array(
		'templateName'=>'fullWidthFileAndImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][body]"=>array('direction'=>$itemLanguage['direction']),			
			"rows[$num][columns][lead]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));
	/* */
	$infa->showFormHeader(null,'myForm',true);

	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);

	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	}
	elseif($_REQUEST['action'] == 'new') {
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	
	$infa->showTableHeader($actionTitle);
	// $infa->showSeparatorRow( $translation->getValue('commonInfo') );
	
	$items = cmfcMySql::getRowsWithMultiKeys(
		$_cp['sectionInfo']['categoriesTable']['tableName'],
		array(
			$_cp['sectionInfo']['categoriesTable']['columns']['languageId'] => $translation->languageInfo['id'],
		)
	);
	$excludeNodes = array();
	/*
	$infa->showDropDownTreeCustom(
		$translation->getValue('category'),
		"rows[$num][common][categoryId]",
		$_POST['rows'][$num]['common']['categoryId'],
		$_cp['sectionInfo']['name'],
		'',
		array(
			'langId'=>$langInfo['id'],
			'selectParents' => false,
			'excludeInfo' => array(
						'excludeBy'=>'category_id',
						'excludeValues'=>$excludedNodes,
						'deleteExcludedNodes'=>TRUE
			)
		)
	);
	/* */
	if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
	{
		$infa->showCustomRow(
			$translation->getValue('category'),
			cmfcHtml::drawDropDown(
				"rows[$num][common][categoryId]",
				$_POST['rows'][$num]['common']['categoryId'],
				$items,
				$_cp['sectionInfo']['categoriesTable']['columns']['relatedItem'],
				$_cp['sectionInfo']['categoriesTable']['columns']['title'],
				NULL,
				NULL,
				'',
				'--- No Categories ---'
			),
			''
		);
	}
	/* */
	if (!$_POST['rows'][$num]['common']['publishDatetime'])
		$_POST['rows'][$num]['common']['publishDatetime'] = date('Y-m-d');
	
	if ($_cp['sectionInfo']['tableInfo']['columns']['publishDateTime'])
	{
		$yearRange = (string)(wsfGetDateTime('Y', 'now', $translation->languageInfo['sName'], 0) +10).'-'.(string)(wsfGetDateTime('Y', 'now', $translation->languageInfo['sName'], 0) - 10 );
		$infa->showCustomRow(
			$translation->getValue('publishDateTime'),
			cmfcHtml::drawDateTimeDropDownBeta(
				"rows[$num][common][publishDatetime]",
				$_POST['rows'][$num]['common']['publishDatetime'],
				$translation->languageInfo['calendarType'],
				array('year', 'month', 'day'),
				array(
					'yearRange' => $yearRange
				)
			)
		, ''
		);
	}
	/*
	if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename'])
	{
		$infa->showCustomRow(
			$translation->getValue('photo'),
			cpfGetImageUploadAuto(
				"rows[$num][common][photoFilename]",
				$_POST['rows'][$num]['common']['photoFilename'],
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
				$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative']
			),
			''
		);
	}
	/* */
	$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName'];
	$items = cmfcMySql::getRowsCustom($query);
	
	if($items){
		$languageItemCount = count($items);
		foreach($items as $key=>$item){
			$options[] = array(
				'id'=>$item['id'],
				'label'=>$item['name'],
				'href'=>"#",
				'onClick'=>"changeLang(".$item['id'].")",
			);
		}
	}
	
	$infa->showSeparatorRow($translation->getValue('mainInfo'));
	if($languageItemCount > 1)
		$infa->drawMultiTabs($options, $itemLanguage['id']);
	
		
	$infa->showInputRow($translation->getValue('title'), "rows[$num][columns][title]", $_POST['rows'][$num]['columns']['title'], '',40, $itemLanguage['direction']); 
	/*
	if ($_cp['sectionInfo']['tableInfo']['columns']['body'])
	{
		$infa->showTextAreaRow($translation->getValue('body'),"rows[$num][columns][body]", $_POST['rows'][$num]['columns']['body'],'', '', 15, '90%',$itemLanguage['direction']);
	}
	*/
	$buttons = array(
		array(
			'name' => 'submit_save',
			'value' => $translation->getValue(buttonSubmit),
		),
		array(
			'name' => 'reset',
			'value' => $translation->getValue(buttonReset),
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
			'value' => $translation->getValue(buttonCancel),
		),
	
	);
	
	
	if($languageItemCount > 1)
	{
		$saveAndNextLang = array(
			array(
				'name' => 'saveEditNextLang',
				'value' =>  $translation->getValue(submit_and_go_next_lang),
			),
		);
		$buttons = cmfcPhp4::array_merge($saveAndNextLang, $buttons);
	}
	
	$infa->showFormFooterCustom($buttons);
}
elseif ($_REQUEST['action']=='list')
{
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" dir="<?php echo $translation->languageInfo['direction']?>" > 
			<tr>
				<td align="<?php echo $translation->languageInfo['align']?>" ></td>
				<td width="120" align="<?php echo $translation->languageInfo['align']?>">
					<?php 					$query = "SELECT id,name FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName'];
					$items = cmfcMySql::getRowsCustom($query);
					
					if($items && count($items)>1)
					{
					?>
					<table class="option-link" border="0" cellspacing="1" cellpadding="0" >
						<tr>
							<td class="quick-search-button" align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap">
							
							<?php echo  $translation->getValue(select_language)?>&nbsp;
							
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
					<?php 					}
					?>
				</td>
				
				<td></td>
			    <td  align="<?php echo $translation->languageInfo['!align']?>" >
				
				<table border="0" cellpadding="0" cellspacing="1" class="option-link"  align="<?php echo $translation->languageInfo['!align']?>"  >
                  <tr>
                    <td class="quick-search-button"  align="<?php echo $translation->languageInfo['!align']?>" ><a href="javascript:void(0);" onclick="<?php echo $js->jsfToggleDisplayStyle('searchBoxContainer','auto')?>"><?php echo $translation->getValue(advancedSearch);?></a></td>
                  </tr>
                </table></td>
			</tr>
		</table>
		
		<div id="searchBoxContainer" style="
		<?php 		if (!$_REQUEST['search']){
			?>
			display:none;
			<?php 		}
		?>
		" dir="<?php echo $translation->languageInfo['direction']?>">
		<table id="option-buttons" width="100%" border="0" cellspacing="0" cellpadding="0"  dir="<?php echo $translation->languageInfo['direction']?>" > 
			<tr>
				<td  class="option-table-buttons-spacer" width="5" align="<?php echo $translation->languageInfo['align']?>" >&nbsp;</td>
				<td  align="<?php echo $translation->languageInfo['align']?>" id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo $translation->getValue(advancedSearch)?>
				</td>
				<td class="option-table-buttons-spacer" align="<?php echo $translation->languageInfo['align']?>" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			<?php
            if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
            {
                $infa->showDropDownTreeCustom(
                    $translation->getValue('category'),
                    "search[categoryId]",
                    $_POST['search']['categoryId'],
                    wsfGetSectionNameByNodeId($_cp['sectionInfo']['nodeId']),
                    '',
                    array(
                        'langId'=>$translation->languageInfo['id'],
                        'selectParents' => false,
                        'excludeInfo' => array(
                            'excludeBy'=>'category_id',
                            'excludeValues'=>$excludedNodes,
                            'deleteExcludedNodes'=>TRUE
                        )
                    ),
                    $treeTableDB
                );
            }
			?>
			<tr class="table-row2" align="<?php echo $translation->languageInfo['align']?>" >
				<td width="200" class="field-subtitle"><?php echo $translation->getValue('title')?> </td>
				<td align="<?php echo $translation->languageInfo['align']?>" class="field-insert"><input name="search[title]" class="input" type="text" value="<?php echo $_REQUEST['search']['title']?>" style="width:50%" /></td>
			</tr>
			<?php /*?><tr class="table-row1" >
				<td align="<?php echo $translation->languageInfo['align']?>" class="field-subtitle"><?php echo $translation->getValue('formBody')?></td>
				<td  align="<?php echo $translation->languageInfo['align']?>" class="field-insert"><input name="search[body]" class="input" type="text" value="<?php echo $_REQUEST['search']['body']?>" style="width:50%" /></td>
			</tr><?php */?>
			
			<tr class="table-row2">
				<td colspan="2" align="<?php echo $translation->languageInfo['align']?>">
					<input class="button" type="submit" name="submit_search" value="<?php echo $translation->getValue(search)?>" />
					<input class="button" type="button" name="submit_cancel_search" value="<?php echo $translation->getValue(cancel)?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&amp;lang=<?php echo $_GET['lang']?>'" />
				</td>
			</tr>
		</table>
		</div>
		
	</form>
	
	<form name="myListForm"  action="?<?php echo htmlentities(wsfExcludeQueryStringVars(array('sectionName'),'get'))?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
	<input type="hidden" id="listlang" name="listlang" value="" />
	<?php 	if (is_array($rows)) 
	{
	?>
		<table id="listFormTable" dir="<?php echo $translation->languageInfo['direction']?>"  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" style="border-color:#d4dce7;">
			<tr>
				<td colspan="10" class="table-header" align="<?php echo $translation->languageInfo['align']?>" > <?php echo $actionTitle ?>  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" >
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo $translation->getValue('tools')?>
				</td>
				
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo htmlentities($paging->getSortingUrl('title','DESC'))?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo htmlentities($paging->getSortingUrl('title','ASC'))?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo $translation->getValue('title')?>
				</td>
                <?php
				/*
				if ($_cp['sectionInfo']['tableInfo']['columns']['body'])
				{
				?>
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
					<a href="<?php echo htmlentities($paging->getSortingUrl('body','DESC'))?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo htmlentities($paging->getSortingUrl('body','ASC'))?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo $translation->getValue('body')?>
				</td>
                <?php
				}
				
				if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
				{
				?>
                    <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
                        <?php echo $translation->getValue('category')?>
                    </td>
                <?php
				}
				if ($_cp['sectionInfo']['tableInfo']['columns']['publishDatetime'])
				{
				?>
                    <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
                        <a href="<?php echo $paging->getSortingUrl('publish_datetime','DESC')?>">
                            <span style="font-family:arial">▼</span>
                        </a>
                        <a href="<?php echo $paging->getSortingUrl('publish_datetime','ASC')?>">
                            <span style="font-family:arial">▲</span>
                        </a>
                        <?php echo $translation->getValue('publishDateTime')?>
                    </td>
                <?php
				}/* */
				?>
				
			</tr>
			<?php 			foreach ($rows as $key=>$row) 
			{
				$num=$key+1;
					
				//--(Begin)-->convert columns physical names to their internal names
				$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
				//--(End)-->convert columns physical names to their internal names
				
				$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName']." WHERE ".$_cp['sectionsInfo']['words']['tableInfo']['columns']['id']." = '".$row['languageId']."'";

				$res= cmfcMySql::getRowsCustom($query);
				
				$itemLanguage = $res[0];
				$actionsBaseUrl = htmlentities(cmfcUrl::excludeQueryStringVars(
					array(
						'sectionName',
						'pageType',
						'action',
						'id',
						'nextLang',
						'itemLanguage'
					),
					'get'
				));
				?>
				<tr class="table-row1"   onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" >
					<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-checkbox" align="<?php echo $translation->languageInfo['align']?>" >
					<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				</td>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" >
					<a href="?<?php echo $actionsBaseUrl?>&amp;action=edit&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_edit.png" width="16" border="0" alt="edit" title="edit" />
					</a>
					 
					<a onclick="return <?php echo $js->jsfConfimationMessage( $translation->getValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&amp;action=delete&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['title'];?>
				</td>
				<?php
				/*
				if ($_cp['sectionInfo']['tableInfo']['columns']['body'])
				{
				?>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo cmfcString::briefText(strip_tags($row['body']), 100);?>
				</td>
                <?php
				}
				
				if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
				{
				?>

				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php 					$categoryInfo = cmfcMySql::loadWithMultiKeys(
						$_cp['sectionInfo']['categoriesTable']['tableName'],
						array(
							$_cp['sectionInfo']['categoriesTable']['columns']['relatedItem'] => $row['categoryId'],
							$_cp['sectionInfo']['categoriesTable']['columns']['languageId'] => $_REQUEST['viewLangId'],
						)
					);
					$categoryInfo=cmfcMySql::convertColumnNames($categoryInfo, $_cp['sectionInfo']['categoriesTable']['columns']);
					echo $categoryInfo['title'];
					?>
				</td>
				<?php 
				
				}
				if ($_cp['sectionInfo']['tableInfo']['columns']['publishDatetime'])
				{
				?>
                    <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
                        <?php echo wsfGetDateTime('d M Y', $row['publishDatetime'], $translation->languageInfo['sName']);?>
                    </td>
                <?php
				}/* */
				if($sectionInfo['tableInfo']['columns']['authorId'])
				{
				?>
                    <td align="<?php echo $translation->languageInfo['align']?>" class="field-title" >
                    <a href="?sn=comments&amp;lang=<?php echo $GET['lang']?>&amp;section=blog&amp;itemId=<?php echo $row['relatedItem']?>"><?php echo $translation->getValue('comments')?></a>
                    </td>
				<?php 				}
				?>
				
			</tr>
			<?php }?>
	  </table>
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo $translation->getValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage($translation->getValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo $translation->getValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&amp;action=new'" />
		</div>
	<?php 	}
	else { 
		?>
		<b><?php echo  $translation->getValue('nothingFound')?></b>
		<br />
		<input name="submit_insert" class="button" type="submit" value="<?php echo $translation->getValue(buttonNew) ?>"  />
		<?php 
	}
	?>
	</form>
<?php }
 if ($paging and $_REQUEST['action']=='list' and $paging->getTotalPages()>1) 
{?>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tr>
		<td  align="<?php echo $translation->languageInfo['align']?>">
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" align="<?php echo $translation->languageInfo['align']?>">
						<?php echo  $translation->getValue(page)?> <?php echo $paging->getPageNumber()?> <?php echo  $translation->getValue(from)?> <?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		</td>
		<td align="<?php echo $translation->languageInfo['align']?>">
			<table border="0" align="<?php echo $translation->languageInfo['!align']?>" cellpadding="0" cellspacing="1" class="paging-nav">
				<tr>
					<?php if ($paging->hasPrev()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getPrevUrl()?>" >
						<?php echo  $translation->getValue(prevPage)?> </a></td>
					<?php }?>
					<?php if ($paging->hasNext()) {?>
					<td class="paging-nav-body"><a href="<?php echo $paging->getNextUrl()?>" >
						<?php echo  $translation->getValue(nextPage)?> </a></td>
					<?php }?>
				</tr>
		  </table>
		</td>
	</tr>
</table>

<?php }

?>