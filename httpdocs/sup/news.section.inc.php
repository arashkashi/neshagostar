<?php
//error_reporting(E_WARNING);
$infa=new cpInterface();
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

if ($_POST['submit_archive']) { 
	$_POST['submit_save']='save';
	$_POST['submit_action']='archive';
	$_POST['submit_mode']='multi';
	//$_REQUEST['action'] = 'archive';
}

if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}
//cmfcHtml::printr($relatedItemId);
//<!-- Addible Album Images..

	/*$AddibleItems = array(
		'image_box' => array(	
			'name' => 'image_box',
			'title' => wsfGetValue( 'VN_formImages'),
			'tableInfo' => $sectionInfo[ 'imagesTable'],
			'destRelationColumn' => array (
				'title' => 'newsId',
				'value' => ( $_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'new') ? $relatedItemId : '',
			),
			
			'columnReplacements' => array ( 
			
				'%column_id_value%' 		=>	'id',
				//'%column_name_value%' 		=>	'title',
				//'%column_name_en_value%' 	=>	'titleEn',
				'%column_old_image_value%' 	=>	'image',
			),
			
			'replacements' => array(
				//'%product_type_value%' => $productTypeId,
				'%SiteUrl%' 		=>	$_ws['siteInfo']['url'],
				'%file_path_url%' 	=>	$sectionInfo['folderRelative'],
				'%remove%'  		=>	wsfGetValue( 'VN_buttonDel'),
				'%name%'  			=>	wsfGetValue( 'VN_Name'),
				'%image%'  			=>	wsfGetValue( 'VN_image'),
				'%imageWidth%' 		=>	100,
				'%imageHeight%' 	=>	100,
				'%altMessage%' 		=>	'Photo',
				'%image_tag%'  		=>	'<img src="imageDisplayer.php?mode=resizeByMaxSize&width=%imageWidth%&height=%imageHeight%&file=%file_path_url%%column_old_image_value%" alt="%altMessage%" /><br />'
			),
			'files' => array(
				'image'
			),
		)
	);*/

	//<!-- Addible Templates . . .
	
	/*$templates['image_box'] = '
			<div id="%temp_name%[%{item_number}%]" style="border:#999999 2px solid;margin:2px; width:48%; float: right;">
				<input name="%temp_name%[%{item_number}%][columns][id]" type="hidden" value="%column_id_value%"/>
				<table width="100%">
					<tr>
						<td  align="'.$translation->languageInfo['align'].'" width="50">
							<input name="%temp_name%[%{item_number}%][delete]" type="checkbox"/>
						</td>
						<!--
						<td align="'.$translation->languageInfo['align'].'" width="300" >
							<input name="%temp_name%[%{item_number}%][columns][title]" type="text" value="%column_name_value%" size="30" class="input" /> 
							English: <input name="%temp_name%[%{item_number}%][columns][titleEn]" type="text" value="%column_name_en_value%" size="30" style="direction:ltr" class="input" />
						</td>
						-->
						<td  align="'.$translation->languageInfo['align'].'" >
							<input name="%temp_name%_%{item_number}%_columns_image" type="file" style="width:70%" />
							<input name="old_%temp_name%_%{item_number}%_columns_image" type="hidden" value="%column_old_image_value%"  class="input" />
						</td>
						<td height="28"  align="'.$translation->languageInfo['align'].'" >
								%image_tag%
						</td>
					</tr>	
				</table>
			</div> 
		';*/

	//End of Addible template-->

//-->



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
			$query = ' SELECT MIN('.$columnsPhysicalName['orderNumber'].") as `max` FROM ".$_cp['sectionInfo']['tableInfo']['tableName'];
			$max = cmfcMySql::loadCustom($query);
			$_columns['orderNumber'] = (int) ($max['max']-1);

			$_commonColumns['authorId'] = $userSystem->cvId;
			$userInfo = $userSystem->getOption("userInfo");
			$_commonColumns['authorName'] = $userInfo['first_name']." ".$userInfo['last_name'];
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
	

		}
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;

		$_commonColumns['jalaliYear'] = $_commonColumns['publishDatetime']['year'];
		$_commonColumns['jalaliMonth'] = $_commonColumns['publishDatetime']['month'];
		$_commonColumns['publishDatetime'] = wsfConvertDateTimeDropDownArrayToDateTimeString($_commonColumns['publishDatetime']);
		
		
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
		if($_columns['relatedNewsId'])
			$_columns['relatedNewsId'] = ','.$_columns['relatedNewsId'].',';
		if(!$_commonColumns['firstPage'])
			$_commonColumns['firstPage'] = 0;
		if(!$_commonColumns['important'])
			$_commonColumns['important'] = 0;
		if(!$_commonColumns['sendNewsletter'])
			$_commonColumns['sendNewsletter'] = 0;

		
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
			elseif ($row['action']=='insert') 
			{
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
			elseif ($row['action']=='archive') 
			{
				//$_columns['firstPage']
				$result=cmfcMySql::update(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					array(
						$columnsPhysicalName['firstPage'] => 0,
					),
					$columnsValues[$columnsPhysicalName['id'] ]
				);
				
				
				$msg= 'خبر '.$columnsValues[$columnsPhysicalName['id'] ]. ' آرشیو شد';
			}

			//<!-- Save Addible Items

				$ImgsRslts = array();
				
				//foreach( $AddibleItems as $value)
				//	$ImgsRslts[] = wsfSaveAddibleItems( $value, $_columns['relatedItem']);

			//-->

			wsfSaveOverrideValues($_commonColumns, $relatedItemId);
			#--(End)-->save changes to database
			
			if (PEAR::isError($result) or $result===false) {
				//$messages['errors'][] = $result->getMessage();
				$messages['errors'][] = 'error occured: '.$error;
				$isErrorOccured=true;
			} else {
				$messages['messages'][] = $msg;
				//<!-- Add Addible messages...

					//$messages['messages'] = cmfcPhp4::array_merge( $messages['messages'], $ImgsRslts['messages']);
					if( is_array( $ImgsRslts['messages']))
					{
						foreach( $ImgsRslts['messages'] as $Msg)
						{
							$messages['messages'][] = $Msg;
						}
					}

				//-->
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
			$sqlQuery = "SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE language_id = '".$itemLanguage['id']."' AND related_item = '".$relatedItemId."'";
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
	if ($_GET['fn'])
	{
		$table = $_cp['sectionInfo']['tableInfo'];
		$current = $_GET['currentValue'];
		$targetItemId = $_GET['targetItemId'];
		
		if ($targetItemId and is_array($table))
		{
			if ($current)
			{
				$result = cmfcMySql::update(
					$table['tableName'],
					$table['columns']['relatedItem'],
					array($table['columns'][$_GET['fn']]=>0),
					$targetItemId
				);
				$error = mysql_error();
			}
			else
			{
				$result = cmfcMySql::update(
					$table['tableName'],
					$table['columns']['relatedItem'],
					array($table['columns'][$_GET['fn']]=>1),
					$targetItemId
				);
				$error = mysql_error();
			}
			$msg = $translation->getValue('updated');
		}
		
		if (PEAR::isError($result) or $result===false) 
		{
			//$messages['errors'][] = $result->getMessage();
			$messages['errors'][] = 'error occured: '.$error;
			$isErrorOccured=true;
		} 
		else 
		{
			$messages['messages'][] = $msg;
			cpfLog($_cp['sectionInfo']['name'], $userSystem->cvId, array('name'=>$row['action'],'rowId'=>$_GET['id']));
		}
	
	}
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	#--(Begin)-->generate Sql Query
	
	//echo $_REQUEST['submit_search']."<br>";
	
	if (isset($_REQUEST['submit_search'])) {
		
		if($_REQUEST['search']['title'] != "" && $_REQUEST['search']['body'] != ""){
			
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
		
		if (isset($_REQUEST['search']['startDate'])){
			$registerDate = $_REQUEST['search']['startDate'];
			$_REQUEST['search']['startDate'] = wsfConvertDateTimeDropDownArrayToDateTimeString($registerDate, 'Y-m-d');
			if($_REQUEST['search']['startDate'])
			{
				$_REQUEST['search']['startDate'] .= " 0:0:0";
				$searchSqlWhereQuery .= " AND (
					(`{td:publishDatetime}`>='[startDate]' )
				)";
			}
		}
		if (isset($_REQUEST['search']['endDate'])){
			$registerDate = $_REQUEST['search']['endDate'];
			$_REQUEST['search']['endDate'] = wsfConvertDateTimeDropDownArrayToDateTimeString($registerDate, 'Y-m-d');
			if($_REQUEST['search']['endDate'])
			{
				$_REQUEST['search']['endDate'] .= " 23:59:59";
				$searchSqlWhereQuery .= " AND (
					(`{td:publishDatetime}`<='[endDate]' )
				)";
			}
		}
		if($_REQUEST['search']['categoryId']){
			
			$searchSqlWhereQuery .= " AND (
				`{td:categoryId}` = '[categoryId]')";
			
		}
		
		$replacements=array(
			'{td:title}'=>$_cp['sectionInfo']['tableInfo']['columns']['title'],
			'{td:body}'=>$_cp['sectionInfo']['tableInfo']['columns']['body'],
			'{td:publishDatetime}'=>$_cp['sectionInfo']['tableInfo']['columns']['publishDatetime'],
			'{td:categoryId}'=>$_cp['sectionInfo']['tableInfo']['columns']['categoryId'],
			'[categoryId]'=>$_REQUEST['search']['categoryId'],
			'[startDate]'=>$_REQUEST['search']['startDate'],
			'[endDate]'=>$_REQUEST['search']['endDate'],
			'[body]'=>$_REQUEST['search']['body'],
			'[title]'=>$_REQUEST['search']['title'],
	
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
		'wordNext'=> wsfGetValue(next) ,
		'wordPrev'=> wsfGetValue(prev),
		'link'=>'?'.wsfExcludeQueryStringVars(array('sectionName','pageType'),'get'),
		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_cp['sectionInfo']['tableInfo']['orderByColumnName'],
		'sortType'=>'DESC',
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	
	// echo $sqlQuery;
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

cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

wsfPrintMessages($messages);
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
	
	
	/*
	if ($overrideValues){
		$editValues = wsfConvertOverrideValuesForEditAction($overrideValues);
		foreach ($editValues as $fieldName => $fieldValue){
			if (!$_POST['rows'][$num]['common'][$fieldName])
				$_POST['rows'][$num]['common'][$fieldName] = $fieldValue;
		}
	}
	*/
	
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
	//cmfcHtml::printr($_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative']);
	wsfWysiwygLoader(array(
		'templateName'=>'simpleWithImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][body]"=>array('direction'=>$itemLanguage['direction']),			
			//"rows[$num][columns][lead]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));
	$infa->showFormHeader(null,'myForm',true);
	$infa->showTableHeader($actionTitle);
	$infa->showSeparatorRow( wsfGetValue('commonInfo') );
	
	
	if (!$_POST['rows'][$num]['common']['publishDatetime'] && $_REQUEST['action'] == 'new')
		$_POST['rows'][$num]['common']['publishDatetime'] = date('Y-m-d H:i:s');
	
	$yearRange = (string)(wsfGetDateTime('Y', 'now', $translation->languageInfo['shortName'], 0) +10).'-'.(string)(wsfGetDateTime('Y', 'now', $translation->languageInfo['shortName'], 0) - 10 );
	$infa->showCustomRow(
		wsfGetValue('publishDateTime'),
		cmfcHtml::drawDateTimeDropDownBeta(
			"rows[$num][common][publishDatetime]",
			$_POST['rows'][$num]['common']['publishDatetime'],
			$langInfo['calendarType'],
			array('day', 'month','year'),//, 'minute','hour'),
			array(
				'yearRange' => $yearRange
			)
		)
	, ''
	);
	/*
	if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
	{
		$items = cmfcMySql::getRowsWithMultiKeys(
			$_cp['sectionInfo']['categoriesTable']['tableName'],
			array(
				$_cp['sectionInfo']['categoriesTable']['columns']['languageId'] => $translation->languageInfo['id'],
			)
		);
		if ($_cp['sectionInfo']['tableInfo']['columns']['categoryPath'])
		{
			$excludeNodes = array();
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
		}
		else
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
	}
	/* */
	
	//$infa->showCheckBoxRow(wsfGetValue('firstPage'), "rows[$num][common][firstPage]" , $_POST['rows'][$num]['common']['firstPage']);
	
	//$infa->showCheckBoxRow(wsfGetValue('important'), "rows[$num][common][important]" , $_POST['rows'][$num]['common']['important']);
	
	//$infa->showCheckBoxRow(wsfGetValue('sendNewslletter'), "rows[$num][common][sendNewsletter]" , $_POST['rows'][$num]['common']['sendNewsletter']);
	
	if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename'])
	{
		$infa->showCustomRow(
			wsfGetValue('photo'),
			cpfGetImageUploadAuto(
				"rows[$num][common][photoFilename]",
				$_POST['rows'][$num]['common']['photoFilename'],
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
				$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative']
			),
			''
		);
	}
	$infa->showCheckBoxRow(wsfGetValue('archive'), "rows[$num][common][archive]" , $_POST['rows'][$num]['common']['archive']);
	
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
	
	$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	if($languageItemCount > 1)
		$infa->drawMultiTabs($options, $itemLanguage['id']);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	//$infa->showInputRow(wsfGetValue('overTitle'), "rows[$num][columns][overTitle]", $_POST['rows'][$num]['columns']['overTitle'], '',40, $itemLanguage['direction']); 	
	$infa->showInputRow(wsfGetValue('title'), "rows[$num][columns][title]", $_POST['rows'][$num]['columns']['title'], '',40, $itemLanguage['direction']); 
	
	/*$configs = array(
		'name' => 'News',
		'itemTypeIdColumn' => 4,
		'itemTypeLabelColumn' => $translation->getValue('news'),//'اخبار',
		'destinationTable' => 'news',
		'itemsTables' => array(
			'4' => array(
				'itemsTable' => 'news',
				'itemIdColumn' => 'related_item',
				'itemLabelColumn' => 'title',
				'relatedDestinationColumnName' => 'related_news_id'
			),
		)
	);
	
	$infa->showCustomRow(wsfGetValue('relatedNews'), cpfCreateItemRelatedHtml("rows[$num][columns]", $_POST, $_REQUEST, $configs),'');
	*/
	//$infa->showTextAreaRow(wsfGetValue('brief'),"rows[$num][columns][briefBody]", $_POST['rows'][$num]['columns']['briefBody'],'', '', 15, '90%');
	$infa->showTextAreaRow(wsfGetValue('formBody'),"rows[$num][columns][body]", $_POST['rows'][$num]['columns']['body'],'', '', 15, '90%');
	
	
	//<!-- Album Images ...
		//$infa -> showSeparatorRow( wsfGetValue('gameImages'));
		
		/*foreach( $AddibleItems as $key => $value)
		{
			$infa -> drawAddibleBoxes(
				$value['title'],
				$value['name'],
				$templates[ $value['name'] ],
				$value['tableInfo'],
				$value['destRelationColumn'],
				$value['columnReplacements'],
				$value['replacements'],
				$value['additionalCondition']
			);
		}*/

	//-->	
	
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
	//$infa -> prepareAddibleBoxesTempalte( $AddibleItems, $templates);

}
elseif ($_REQUEST['action']=='list'){
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" dir="<?php echo $langInfo['htmlDir']?>" > 
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
				<td  class="option-table-buttons-spacer" width="5" align="<?php echo $translation->languageInfo['align']?>" >&nbsp;</td>
				<td  align="<?php echo $translation->languageInfo['align']?>" id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo wsfGetValue(advancedSearch)?>
				</td>
				<td class="option-table-buttons-spacer" align="<?php echo $translation->languageInfo['align']?>" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			
			<tr class="table-row1" align="<?php echo $translation->languageInfo['align']?>" >
				<td width="200"><?php echo wsfGetValue('title')?> </td>
				<td align="<?php echo $translation->languageInfo['align']?>" ><input name="search[title]" class="input" type="text" value="<?php echo $_REQUEST['search']['title']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row2" >
				<td align="<?php echo $translation->languageInfo['align']?>" ><?php echo wsfGetValue('formBody')?></td>
				<td  align="<?php echo $translation->languageInfo['align']?>" ><input name="search[body]" class="input" type="text" value="<?php echo $_REQUEST['search']['body']?>" style="width:50%" /></td>
			</tr>
			<?php  
			if ($_cp['sectionInfo']['tableInfo']['columns']['publishDatetime'])
			{
				?>
				<tr class="table-row1" >
					<td align="<?php echo $translation->languageInfo['align']?>" ><?php echo wsfGetValue(VN_date)?></td>
					<td  align="<?php echo $translation->languageInfo['align']?>" >
						از <span dir='ltr'>
						<?php 						$currentYear = wsfGetDateTime('Y', 'now', $translation->languageInfo['shortName'], false);
						$yearRange = ($currentYear)."-".($currentYear-5);
						echo cmfcHtml::drawDateTimeDropDownBeta(
							"search[startDate]",
							$_REQUEST['search']['startDate'],
							$langInfo['calendarType'],
							array('year','month','day'),
							array(
								'yearRange'=>$yearRange
							)
						)
						?>
						</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; تا &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<span dir='ltr'>
						<?php echo cmfcHtml::drawDateTimeDropDownBeta(
							"search[endDate]",
							$_REQUEST['search']['endDate'],
							$langInfo['calendarType'],
							array('year','month','day'),
							array(
								'yearRange'=>$yearRange
							)
						)?>
						</span>
					</td>
				</tr>
				<?php
			}
			/*
			if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
			{
				$items = cmfcMySql::getRowsWithMultiKeys(
					$_cp['sectionInfo']['categoriesTable']['tableName'],
					array(
						$_cp['sectionInfo']['categoriesTable']['columns']['languageId'] => $translation->languageInfo['id'],
					)
				);
				
				if ($_cp['sectionInfo']['tableInfo']['columns']['categoryPath'])
				{
					$excludeNodes = array();
					$infa->showDropDownTreeCustom(
						$translation->getValue('category'),
						"search[categoryId]",
						$_REQUEST['search']['categoryId'],
						$_cp['sectionInfo']['name'],
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
				else
				{
					$infa->showCustomRow(
					$translation->getValue('category'),
					cmfcHtml::drawDropDown(
							"search[categoryId]",
							$_REQUEST['search']['categoryId'],
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
			}
			/* */
			?>

			<tr class="table-row2">
				<td colspan="2" align="<?php echo $translation->languageInfo['align']?>">
					<input class="button" type="submit" name="submit_search" value="<?php echo wsfGetValue(search)?>" />
					<input class="button" type="button" name="submit_cancel_search" value="<?php echo wsfGetValue(cancel)?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&lang=<?php echo $_GET['lang']?>'" />
				</td>
			</tr>
		</table>
		</div>
		
	</form>
	
	<form name="myListForm"  action="?<?php echo wsfExcludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
	<input type="hidden" id="listlang" name="listlang" value="" />
	<?php 	if (is_array($rows)) {
	?>
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="11" class="table-header" align="<?php echo $translation->languageInfo['align']?>" > <?php echo $actionTitle ?>  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" >
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo wsfGetValue(tools)?>
				</td>
				<?php /*?><td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('overTitle','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('overTitle','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('overTitle')?>
				</td><?php */?>
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('title','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('title','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue(title)?>
				</td>
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
					<a href="<?php echo $paging->getSortingUrl('body','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('body','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue(formBody)?>
				</td>
                <?php /*?><td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('firstPage')?>
				</td>
                <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('important')?>
				</td>
                <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('sendNewsletter')?>
				</td><?php */?>
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
					<a href="<?php echo $paging->getSortingUrl('publish_datetime','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('publish_datetime','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('publishDateTime')?>
				</td>
				<?php
                if ($_cp['sectionInfo']['tableInfo']['columns']['archive'])
				{
					?>
					<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title" >
						<?php echo wsfGetValue('archive')?>
					</td>
                 <?php
				}?>
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
				<tr class="table-row1"   onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" >
					<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-checkbox" align="<?php echo $translation->languageInfo['align']?>" >
					<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				</td>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" >
					<a href="?<?php echo $actionsBaseUrl?>&action=edit&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_edit.png" width="16" border="0" alt="edit" title="edit" />
					</a>
					 
					<a onclick="return <?php echo $js->jsfConfimationMessage( wsfGetValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&action=delete&id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				<?php /*?><td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['overTitle'];?>
				</td><?php */?>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo cmfcString::briefText(strip_tags($row['title']), 30);?>
				</td>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo cmfcString::briefText(strip_tags($row['body']), 80);?>
				</td>
				<?php /*?><td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php if($row['firstPage']==1) echo "<img src='interface/images/tick.jpg' width='20' height='20'>";?>
				</td>
                <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php if($row['important']==1) echo "<img src='interface/images/tick.jpg' width='20' height='20'>";?>
				</td>
                <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php if($row['sendNewsletter']==1) echo "<img src='interface/images/tick.jpg' width='20' height='20'>";?>
				</td><?php */?>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo wsfGetDateTime('d M Y (H:i)', $row['publishDatetime'], $translation->languageInfo['shortName']);?>
				</td>
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" id="archiveFlag<?php echo $row['id']?>"> 
                    <?php 
                    if ($row['archive'])
                    {?>
                        <a href="<?php echo $_SERVER['REQUEST_URI']?>&fn=archive&targetItemId=<?php echo $row['relatedItem']?>&currentValue=<?php echo $row['archive']?>"
                        <?php 
                        if ($ajax)
                        {?>
                            onclick="<?php echo $ajax->jsfSimpleCallForATagOnClick(array('archiveFlag'.$row['id'],'pageMessages'))?>"
                            <?php }?>
                        >
                            <img src="interface/images/tick.png" id="archive<?php echo $row['id']?>" alt="<?php echo $translation->getValue('archive')?>" style="cursor:pointer; border:0px;" />
                        </a>
                     <?php
                     }
                     else
                     {?>
                        <a href="<?php echo $_SERVER['REQUEST_URI']?>&fn=archive&targetItemId=<?php echo $row['relatedItem']?>&currentValue=<?php echo $row['archive']?>"
                        <?php 
                        if ($ajax)
                        {?>
                            onclick="<?php echo $ajax->jsfSimpleCallForATagOnClick(array('archiveFlag'.$row['id'],'pageMessages'))?>"
                        <?php }?>
                        >
                            <img src="interface/images/bullet_red.png" id="archive<?php echo $row['id']?>" alt="<?php echo $translation->getValue('archive')?>" style="cursor:pointer; border:0px;" />
                        </a>
                     <?php }
                     //cmfcHtml::printr($_SERVER['REQUEST_URI']);
                     ?>
                </td>
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo wsfGetValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
            <input name="submit_archive" class="button" type="submit" value="آرشیو" />
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
 if ($paging and $_REQUEST['action']=='list' & $paging->getTotalPages()>1) {?>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tr>
		<td  align="<?php echo $translation->languageInfo['align']?>">
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" align="<?php echo $translation->languageInfo['align']?>">
						<?php echo  wsfGetValue(page)?> <?php echo $paging->getPageNumber()?> <?php echo  wsfGetValue(from)?> <?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		</td>
		<td align="<?php echo $translation->languageInfo['align']?>">
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