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

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}


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
			$_commonColumns['authorId'] = $userSystem->cvId;
			$userInfo = $userSystem->getOption("userInfo");
			$_commonColumns['authorName'] = $userInfo['first_name']." ".$userInfo['last_name'];
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
		}
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;

		/*$_commonColumns['jalaliYear'] = $_commonColumns['publishDatetime']['year'];
		$_commonColumns['jalaliMonth'] = $_commonColumns['publishDatetime']['month'];
		$_commonColumns['publishDatetime'] = wsfConvertDateTimeDropDownArrayToDateTimeString($_commonColumns['publishDatetime']);*/
		
		
		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename']){
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
				$_GET['id'] = cmfcMySql::insertId();
				$error = cmfcMySql::error();
				$msg = wsfGetValue('addMsg');
				
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

if (!$_POST['submit_save'])
{
	if ($editable)
	{
		$_POST['rows'][$num]['columns'] = $editable;
	}
	if ($_REQUEST['action']=='edit') {
	
		if($itemLanguage['id'] ){
			$sqlQuery = "SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE language_id = '".$itemLanguage['id']."' AND related_item = '".$relatedItemId."'";
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
		
		if($_REQUEST['search']['provinceId'] != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:provinceId}` = '[provinceId]')";
		}
		if($_REQUEST['search']['managerName'] != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:managerName}` LIKE '%[managerName]%')";
		}
		if($_REQUEST['search']['companyName'] != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:companyName}` LIKE '%[companyName]%')";
		}
		if($_REQUEST['search']['city'] != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:city}` LIKE '%[city]%')";
		}
		if($_REQUEST['search']['resellerId'] != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:resellerId}` = 'resellerId')";
		}
		
		$replacements=array(
			'{td:provinceId}'=>$_cp['sectionInfo']['tableInfo']['columns']['provinceId'],
			'{td:resellerId}'=>$_cp['sectionInfo']['tableInfo']['columns']['resellerId'],
			'{td:city}'=>$_cp['sectionInfo']['tableInfo']['columns']['city'],
			'{td:companyName}'=>$_cp['sectionInfo']['tableInfo']['columns']['companyName'],
			'{td:managerName}'=>$_cp['sectionInfo']['tableInfo']['columns']['managerName'],
			'[resellerId]'=>$_REQUEST['search']['resellerId'],
			'[city]'=>$_REQUEST['search']['city'],
			'[companyName]'=>$_REQUEST['search']['companyName'],
			'[managerName]'=>$_REQUEST['search']['managerName'],
			'[provinceId]'=>$_REQUEST['search']['provinceId'],
	
		);
		
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
		//cmfcHtml::printr($searchSqlWhereQuery);
			
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
		'wordNext'=> wsfGetValue(next) ,
		'wordPrev'=> wsfGetValue(prev),
		'link'=>'?'.wsfExcludeQueryStringVars(array('sectionName','pageType'),'get'),
		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['orderByColumnName'],
		'sortType'=>'DESC',
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
?>


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
?>
<script language="javascript">

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
	
	/*wsfWysiwygLoader(array(
		'templateName'=>'fullWidthFileAndImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][body]"=>array('direction'=>$itemLanguage['direction']),			
			//"rows[$num][columns][lead]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));*/
	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);
	
	$infa->showSeparatorRow( wsfGetValue('commonInfo') );
	
	$provincesTable = $_ws['physicalTables']['provinces'];
	$provinces = cmfcMySql::getRows($provincesTable['tableName']);
	//cmfcHtml::printr($_ws['physicalTables']['provinces']);
	$infa->showCustomRow(
		wsfGetValue('province'),
		cmfcHtml::drawDropDown(
			"rows[$num][common][provinceId]"
			,$_POST['rows'][$num]['common']['provinceId']
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
	$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	if($languageItemCount > 1)
		$infa->drawMultiTabs($options, $itemLanguage['id']);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	$infa->showInputRow(wsfGetValue('city'), "rows[$num][columns][city]", $_POST['rows'][$num]['columns']['city'], '',40, $itemLanguage['direction']);
	//$infa->showInputRow(wsfGetValue('resellerId'), "rows[$num][columns][resellerId]", $_POST['rows'][$num]['columns']['resellerId'], '',40, $itemLanguage['direction']);
	$infa->showInputRow(wsfGetValue('companyName'), "rows[$num][columns][companyName]", $_POST['rows'][$num]['columns']['companyName'], '',40, $itemLanguage['direction']);
	$infa->showInputRow(wsfGetValue('managerName'), "rows[$num][columns][managerName]", $_POST['rows'][$num]['columns']['managerName'], '',40, $itemLanguage['direction']);
		
	//$infa->showInputRow(wsfGetValue('sttafs'), "rows[$num][columns][sttafs]", $_POST['rows'][$num]['columns']['sttafs'], '',40, $itemLanguage['direction']);
	
	$infa->showInputRow(wsfGetValue('tel'), "rows[$num][columns][tel]", $_POST['rows'][$num]['columns']['tel'], '',40, $itemLanguage['direction']);
	$infa->showInputRow(wsfGetValue('fax'), "rows[$num][columns][fax]", $_POST['rows'][$num]['columns']['fax'], '',40, $itemLanguage['direction']);
	
	$infa->showTextAreaRow(wsfGetValue('address'),"rows[$num][columns][address]", $_POST['rows'][$num]['columns']['address'],'', '', 15, '90%');
	
	//$infa->showTextAreaRow(wsfGetValue('Description'),"rows[$num][columns][Description]", $_POST['rows'][$num]['columns']['Description'],'', '', 15, '90%');
	
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
			'value' => wsfGetValue(buttonSubmit),
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
	
	
	if($languageItemCount > 1)
	{
		$saveAndNextLang = array(
			array(
				'name' => 'saveEditNextLang',
				'value' =>  wsfGetValue(submit_and_go_next_lang),
			),
		);
		
		$buttons = cmfcPhp4::array_merge($saveAndNextLang, $buttons);
	}
	
	$infa->showFormFooterCustom($buttons);
}
elseif ($_REQUEST['action']=='list'){
	if(isset($_REQUEST['export_as_excel']))
	{
		//--(BEGIN)-->gather information
		
			$excelFileName="../".$_ws['directoriesInfo']['excelFolderRPath'].'export2.xls';
		
			ob_clean();
			$excel=new ExcelWriter($excelFileName, 'utf-8');
			@chmod($excelFileName,0777);
			
			if (!is_writable($excelFileName)) {
			   echo $excelFileName.' is not writable';
			   exit;
			}
			$excel->writeLine(
				array(
					'#',
					//wsfGetValue('sttafs'),
					wsfGetValue('companyName'),
					wsfGetValue('managerName'),
					wsfGetValue('province'),
					wsfGetValue('city'),
					wsfGetValue('address'),
					wsfGetValue('tel')
					//wsfGetValue('resellerId')
				)
			);
			$excel->writeLine(array());
			if(is_array($rows))
			{
				foreach ($rows as $key=>$row) 
				{
					$key=$key+1;
					$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
					$excel->writeLine(array(
						$key,
						//$row['sttafs'],
						$row['companyName'],
						$row['managerName'],
						cpfprovinceNameFinder($row['provinceId'],$row['languageId']),
						$row['city'],
						$row['address'],
						$row['tel'],
						//$row['resellerId']
						)
					);
				}
			}
			$excel->close();
			cpfDownloadFile($excelFileName);
		
		
		//--(END)-->gather information
		?>
		<script language="javascript" type="text/javascript">
		//window.close();
		</script>
		<?php 		ob_flush();
		exit();
	}
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" dir="<?php echo $langInfo['htmlDir']?>" > 
			<tr>
				<td align="<?php echo $langInfo['htmlAlign']?>" ></td>
				<td width="120" align="<?php echo $langInfo['htmlAlign']?>">
					<?php 					$query = "SELECT id,name FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName'];
					$items = cmfcMySql::getRowsCustom($query);
					
					if($items && count($items)>1)
					{
					?>
					<table class="option-link" border="0" cellspacing="1" cellpadding="0" >
						<tr>
							<td class="quick-search-button" align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap">
							
							<?php echo  wsfGetValue(select_language)?>&nbsp;
							
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
			    <td  align="<?php echo $langInfo['htmlNAlign']?>" >
				
				<table border="0" cellpadding="0" cellspacing="1" class="option-link"  align="<?php echo $langInfo['htmlNAlign']?>"  >
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
		?>
		" dir="<?php echo $langInfo['htmlDir']?>">
		<table id="option-buttons" width="100%" border="0" cellspacing="0" cellpadding="0"  dir="<?php echo $langInfo['htmlDir']?>" > 
			<tr>
				<td  class="option-table-buttons-spacer" width="5" align="<?php echo $langInfo['htmlAlign']?>" >&nbsp;</td>
				<td  align="<?php echo $langInfo['htmlAlign']?>" id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo wsfGetValue(advancedSearch)?>
				</td>
				<td class="option-table-buttons-spacer" align="<?php echo $langInfo['htmlAlign']?>" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			
			<?php
			//$infa->showInputRow(wsfGetValue('resellerId'), "search[resellerId]", $_REQUEST['search']['resellerId'], '',40, $itemLanguage['direction']);
			$provincesTable = $_ws['physicalTables']['provinces'];
			$provinces = cmfcMySql::getRows($provincesTable['tableName']);
			
			$infa->showCustomRow(
				wsfGetValue('province'),
				cmfcHtml::drawDropDown(
					"search[provinceId]"
					,$_REQUEST['search']['provinceId']
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
			$infa->showInputRow(wsfGetValue('companyName'), "search[companyName]", $_REQUEST['search']['companyName'], '',40, $itemLanguage['direction']);
			$infa->showInputRow(wsfGetValue('managerName'), "search[managerName]", $_REQUEST['search']['managerName'], '',40, $itemLanguage['direction']);
			$infa->showInputRow(wsfGetValue('city'), "search[city]", $_REQUEST['search']['city'], '',40, $itemLanguage['direction']);
			?>
			
			<tr class="table-row2">
				<td colspan="2" align="<?php echo $langInfo['htmlAlign']?>">
					<input class="button" type="submit" name="submit_search" value="<?php echo wsfGetValue(search)?>" />
					<input class="button" type="button" name="submit_cancel_search" value="<?php echo wsfGetValue(cancel)?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&lang=<?php echo $_GET['lang']?>'" />
				</td>
			</tr>
		</table>
		</div>
		
	</form>
	
	<form name="myListForm"  action="?<?php echo wsfExcludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
	<input type="hidden" id="listlang" name="listlang" value="" />
	<?php 	if (is_array($rows)) 
	{
	?>
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="12" class="table-header" align="<?php echo $langInfo['htmlAlign']?>" > <?php echo $actionTitle ?>  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" >
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo wsfGetValue(tools)?>
				</td>
                
                <?php /*?><td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('resellerId')?>
				</td><?php */?>
                
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('companyName')?>
				</td>
                
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('managerName')?>
				</td>
                
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('province')?>
				</td>
                
                 <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('city')?>
				</td>
				
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('tel')?>
				</td>
				
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('address')?>
				</td>
				
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('category')?>
				</td>

			</tr>
			<?php 			foreach ($rows as $key=>$row) {
				//print_r($row);
				$num=$key+1;
					
				//--(Begin)-->convert columns physical names to their internal names
				$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
				//--(End)-->convert columns physical names to their internal names
				
				$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName']." WHERE ".$_cp['sectionsInfo']['words']['tableInfo']['columns']['id']." = '".$row['languageId']."'";

				$res= cmfcMySql::getRowsCustom($query);
				
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
				?>
				<tr class="table-row1"   onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';" id="<?php echo $row['id']?>" onclick="thisrow(this)">
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" >
					<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-checkbox" align="<?php echo $langInfo['htmlAlign']?>" >
					<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				</td>
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" >
					<a href="?<?php echo $actionsBaseUrl?>&action=edit&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_edit.png" width="16" border="0" alt="edit" title="edit" />
					</a>
					 
					<a onclick="return <?php echo $js->jsfConfimationMessage( wsfGetValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&action=delete&id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				
                <?php /*?><td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['resellerId']?>
				</td><?php */?>
                
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['companyName']?>
				
                </td>
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['managerName']?>
				</td>
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php
					echo cpfprovinceNameFinder($row['provinceId'],$row['languageId']);
					?>
				</td>
				
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['city']?>
				</td>
                
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['tel']?>
				</td>
				
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo cmfcString::briefText(strip_tags($row['address']), 100);?>
				</td>
				
				<?php
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
				?>
				
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo wsfGetValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
            </form>
            <form method="post">
            	<input name="export_as_excel" class="button" type="submit" value=" <?php echo wsfGetValue('excelExport') ?> " />
            </form>

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