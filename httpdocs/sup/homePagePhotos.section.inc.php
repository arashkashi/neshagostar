<?php
$infa=new cpInterface();
//$infa->setOverrideFields($_cp['sectionInfo']['overrideFields']);

$treeTableDB = $_ws['physicalTables']['categories'];
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

/*
if (!$relatedItemId)
	$relatedItemId = time();
*/

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

//cmfcHtml::printr($itemLanguage);
//cmfcHtml::printr($editable);

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

if ((isset($_POST['submit_save']) and $_GET['action']!='view') || isset($_POST['saveEditNextLang'])) {

	foreach ($_POST['rows'] as $num=>$row) {		

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
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
		}
		
		$_commonColumns['publishDatetime'] = wsfConvertDateTimeDropDownArrayToDateTimeString($_commonColumns['publishDatetime'], 'Y-m-d', array('year', 'month'));
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;
		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename']){
			$_commonColumns['photoFilename'] = wsfUploadFileAuto(
				"rows[$num][common][photoFilename]",
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
			);
		}
		
		if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId']){
			$_commonColumns['categoryPath'] = wsfGetCategoryPathForDb(
				$treeTableDB['tableName'], 
				$_commonColumns['categoryId'],
				$selectTitleValuePair, 
				$selectTitleValuePair['value'] 
			);
		}
		if(!$_commonColumns['visible'])
			$_commonColumns['visible'] = 0;
			
		$columnsValues=array();
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
					
					$_columns['relatedItem'] = $relatedItemId = $_GET['id'];
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

	if (!PEAR::isError($result)) {
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

if (!$_POST['submit_save']){
	
	if ($editable){
		$_POST['rows'][$num]['columns'] = $editable;
	}
	if ($_REQUEST['action']=='edit') {
	
		if($itemLanguage['id'] ){
			$sqlQuery = "SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE (1=1) ";
			//." WHERE language_id = '".$itemLanguage['id']."' AND related_item = '".$relatedItemId."'";
			//$_POST = array();
		}else{
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
	
	if (isset($_REQUEST['submit_search'])) {
		
		if($_REQUEST['search']['categoryId'] != ""){
		
			$searchSqlWhereQuery = " AND (
				`{td:categoryId}` LIKE '%[categoryId]%')";
				
		}
	
		$replacements=array(
			'{td:categoryId}'=>$_cp['sectionInfo']['tableInfo']['columns']['categoryId'],
			'[categoryId]'=>$_REQUEST['search']['categoryId'],
		);
		
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
		
			
	}
	if (!isset($_REQUEST['viewLangId']) )
		$_REQUEST['viewLangId'] = $itemLanguage['id'];
	
	/*if ($_REQUEST['viewLangId'])
		$searchSqlWhereQuery .= " AND ".$_cp['sectionInfo']['tableInfo']['columns']['languageId']." ='".$_REQUEST['viewLangId']."'";*/
		
	//$searchSqlWhereQuery .= ' AND '.$_cp['sectionInfo']['tableInfo']['columns']['sectionName']." ='".$sectionName."' ";
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName'].
		" WHERE ".$_cp['sectionInfo']['tableInfo']['columns']['languageId']." <> '0' ".
		$searchSqlWhereQuery;
	#--(End)-->generate Sql Query
	
	//$sqlQuery .= " GROUP BY related_item";
	/* 
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
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
		'sortType'=>'DESC',
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	
	//echo $sqlQuery;
	$sqlQuery=$paging->getPreparedSqlQuery();
	#--(End)-->Paging
	*/
	//echo $sqlQuery."<br>";
	
	#--(Begin)-->Execute Query and fetch the rows
	//echo $sqlQuery."<br>";
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
	
	//print_r($rows);
	//echo "<br>";
}

cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();
wsfPrintMessages($messages);

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
		<div class="<?php echo $class?>" dir="<?php echo $langInfo['htmlDir']?>" align="<?php echo $langInfo['htmlAlign']?>" >
			<?php echo implode('<br />',$messageList)?>
		</div>
		<?php 	}
}
?>
<script language="javascript" type="text/javascript">
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
if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) {
	
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
		)
	));
	*/
	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);
	
	$infa->showSeparatorRow( $translation->getValue('mainInfo') );

	
	if (!$_POST['rows'][$num]['common']['publishDatetime'] && $_REQUEST['action'] == 'new')
		$_POST['rows'][$num]['common']['publishDatetime'] = date('Y-m-d');
	
/*	$yearRange = (string)(wsfGetDateTime('Y', 'now', $langInfo['sName'], 0) +10).'-'.(string)(wsfGetDateTime('Y', 'now', $langInfo['sName'], 0) - 10 );
	$infa->showCustomRow(
		$translation->getValue('publishDatetime'),
		cmfcHtml::drawDateTimeDropDownBeta(
			"rows[$num][common][publishDatetime]",
			$_POST['rows'][$num]['common']['publishDatetime'],
			$langInfo['calendarType'],
			array('month', 'year', 'day'),
			array(
				'yearRange' => $yearRange
			)
		)
	, ''
	);
*/
	if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename']){
		//cmfcHtml::printr($_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']);
		$infa->showCustomRow(
			$translation->getValue('photo'),
			cpfGetImageUploadAuto(
				"rows[$num][common][photoFilename]",
				$_POST['rows'][$num]['common']['photoFilename'],
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
				$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative']
			),
			'سایز ایده آل برای عکس 892x250 پیکسل است و حجم عکس نباید بیشتر از 2 مگابایت باشد.'
		);
	}
	$infa->showCheckBoxRow(wsfGetValue('firstPage'), "rows[$num][common][visible]" , $_POST['rows'][$num]['common']['visible']);
	
	/*$query = "SELECT ".$_cp['sectionsInfo']['categoryLanguages']['tableInfo']['columns']['categoryId']." FROM ".
		$_cp['sectionsInfo']['categoryLanguages']['tableInfo']['tableName']." WHERE ".
		$_cp['sectionsInfo']['categoryLanguages']['tableInfo']['columns']['multimediaFileType']." = ".
		$_ws['virtualTables']['multimediaFileTypes']['rows'][1]['id']." OR ".
		$_cp['sectionsInfo']['categoryLanguages']['tableInfo']['columns']['multimediaFileType']." = ".
		$_ws['virtualTables']['multimediaFileTypes']['rows'][2]['id'];
		
	$excludedNodes = cmfcMySql::getRowsCustom($query);
	
	$infa->showDropDownTreeCustom(
		$translation->getValue('category'),
		"rows[$num][common][categoryId]",
		$_POST['rows'][$num]['common']['categoryId'],
		'photos',
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
	);*/
	
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
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	
	/*
	if($languageItemCount > 1)
		$infa->drawMultiTabs($options, $itemLanguage['id']);
	
		
	$infa->showInputRow($translation->getValue('name'), "rows[$num][columns][title]", $_POST['rows'][$num]['columns']['title'], '',40, $itemLanguage['direction']); 
	$infa->showTextAreaRow($translation->getValue('formBody'),"rows[$num][columns][body]", $_POST['rows'][$num]['columns']['body'],'', '', 15, '90%');
	/* */
	//$infa->showInputRow($translation->getValue('level'), "rows[$num][columns][level]", $_POST['rows'][$num]['columns']['level'], '',3, $itemLanguage['direction']); 
	
	//$infa->showHiddenInput("rows[$num][columns][sectionName]", $sectionName);
	
	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	}
	elseif($_REQUEST['action'] == 'new') {
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	
	
	
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
			'value' => $translation->getValue( 'buttonCancel'),
		),
	
	);
	
	/*
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
	*/
	$infa->showFormFooterCustom($buttons);
}
elseif ($_REQUEST['action']=='list'){
?>
	
	<form name="myListForm"  action="?<?php echo wsfExcludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
	<input type="hidden" id="listlang" name="listlang" value="" />
	<?php 	if (is_array($rows)) {
					
		$columnCount = 2;
		$columnWidth = round(100/$columnCount);
		$columnHeight = 150;
	?>
		<table id="listFormTable" class="table" width="100%" cellpadding="3" cellspacing="0">
			<tr>
				<td class="table-header" align="<?php echo $langInfo['htmlAlign']?>" colspan="9"> <?php echo $actionTitle ?>  </td>
			</tr>
			<tr class="table-title field-title">
				<td colspan="<?php echo $columnCount?>">
						<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
						 <?php echo $translation->getValue("selectAll")?>
				</td>
			</tr>
			<tr>

			<?php 			foreach ($rows as $key=>$row) {
				//print_r($row);
				$num=$key+1;
					
				//--(Begin)-->convert columns physical names to their internal names
				$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
				//--(End)-->convert columns physical names to their internal names
				
				$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName']." WHERE ".$_cp['sectionsInfo']['words']['tableInfo']['columns']['id']." = '".$row['languageId']."'";

				$res= cmfcMySql::getRowsCustom($query);

				$categoryInfo = cmfcMySql::loadWithMultiKeys(
					$_cp['sectionInfo']['categoriesTable']['tableName'],
					array(
						$_cp['sectionInfo']['categoriesTable']['columns']['relatedItem'] => $row['categoryId'],
						$_cp['sectionInfo']['categoriesTable']['columns']['languageId'] => $_REQUEST['viewLangId'],
					)
				);
				$categoryInfo=cmfcMySql::convertColumnNames($categoryInfo, $_cp['sectionInfo']['categoriesTable']['columns']);
				
				$itemLanguage = $res[0];
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
				
				$photosCountQuery = "SELECT COUNT(DISTINCT(`".$sectionInfo['photosTable']['columns']['relatedItem']."`)) AS count FROM `".$sectionInfo['photosTable']['tableName']."` WHERE `".$sectionInfo['photosTable']['columns']['categoryId']."`=".$row['relatedItem'];
				$photosCount = cmfcMySql::loadCustom($photosCountQuery);
				
				
				?>


				<td align='center' width='<?php echo $columnWidth?>%' height='<?php echo $columnHeight?>'>
				<div style="background:#eee; padding:8px;">
				<?php 				if($row['photoFilename'])
				{
					$result = $imageManipulator->getAsImageTag(
						array(
							'fileName'=>$row['photoFilename'],
							'fileRelativePath'=>$_cp['sectionInfo']['folderRelative'],			
							'width'=> 356,
							'height' => 100,
							'cropPosition' => 'center',
							'mode'=> array(
								'resizeByMinSize',
								'cropToSize'
							)
						)
					);
					echo $result;
				}
				?>	
				<p style="line-height:150%; margin:2px; padding:0px">
  					<?php echo $row['title']?>
                    <br />		
<?php /*?>							
                	بازدید : <?php echo intval($row['hit'])?>
					<br />														
<?php */?>					<?php echo cmfcJalaliDateTime::smartGet('d M Y ', $row['publishDatetime'])?>
				</p>
				<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				<a href="?<?php echo $actionsBaseUrl?>action=edit&id=<?php echo $row['id']?>"><img src="interface/images/action_edit.png" width="16" height="16" border="0" alt="edit" title="edit" /></a>
				<a onclick="return <?php echo $js->jsfConfimationMessage('Are you sure ?')?>" href="?<?php echo $actionsBaseUrl?>action=delete&id=<?php echo $row['id']?>">
					<img src="interface/images/action_delete.png" width="16" height="16" border="0" alt="delete" title="delete" />
				</a>
			<?php 				echo "</td>";
				if(!(($key+1)%$columnCount)) 
					echo "</tr><tr>";
			}
			$additionalColumnCount = ($columnCount-(($key+1)%$columnCount));
			if($additionalColumnCount == $columnCount)
			{
				$additionalColumnCount = 0;
			}
			for($i=0; $i<$additionalColumnCount;$i++)
			{
			?>
				</div>
				<td width='<?php echo $columnWidth?>%' height='<?php echo $columnHeight?>'>&nbsp;</td>
			<?php 			}
			?>
			</tr>
		</table>	

		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo $translation->getValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage($translation->getValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo $translation->getValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new&lang=<?php echo $_GET['lang']?>'" />
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
 if ($paging and $_REQUEST['action']=='list' and $paging->getTotalPages()>1) {?>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tr>
		<td  align="<?php echo $langInfo['htmlAlign']?>">
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" align="<?php echo $langInfo['htmlAlign']?>">
						<?php echo  $translation->getValue(page)?> <?php echo $paging->getPageNumber()?> <?php echo  $translation->getValue(from)?> <?php echo $paging->getTotalPages()?>
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