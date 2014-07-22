<?php
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


if ($_POST['submit_save_order']) $_REQUEST['action']='save_order';


if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}
if ($_REQUEST['action']=='order') {$actionTitle = wsfGetValue('order');}
if ($_REQUEST['action']=='save_order') {$actionTitle = wsfGetValue('save_order');}



$addibleItems = array(
	"image_box" => array(	
		'name' => 'image_box',
		'title' => wsfGetValue(VN_formImages),
		'tableInfo' => $sectionInfo['productImagesTable'],
		'destRelationColumn' => array (
			'title' => 'productId',
			'value' => ($_REQUEST['action']=='edit' || $_REQUEST['action']=='new')?$relatedItemId:'',
		),
		'columnReplacements' => array ( 
			'%column_id_value%' => 'id',
			'%column_name_value%' => 'title',
			'%column_old_image_value%' => 'image',
		),
		'replacements' => array(
			'%product_type_value%' => $productTypeId,
			'%SiteUrl%' => $_ws['siteInfo']['url'],
			'%file_path_url%' => $sectionInfo['folderRelative'],
			'%remove%'  => wsfGetValue(VN_buttonDel),
			'%name%'  => wsfGetValue(VN_Name),
			'%image%'  => wsfGetValue(VN_image),
			'%imageWidth%' => "100",
			'%imageHeight%' => "100",
			'%altMessage%' => 'photo',
			'%image_tag%'  => '<img src="imageDisplayer.php?mode=resizeByMaxSize&width=%imageWidth%&height=%imageHeight%&file=%file_path_url%%column_old_image_value%" alt="%altMessage%" /><br />'
		),
		'files' => array(
			'image',
		),
	),
	/*
	"extraFileds_box" => array(	
		'name' => 'extraFileds_box',
		'title' => wsfGetValue(VN_extraFileds_box),
		'tableInfo' => $sectionInfo['productExtraFiledsTable'],
		'destRelationColumn' => array (
			'title' => 'productId',
			'value' => ($_REQUEST['action']=='edit' || $_REQUEST['action']=='new')?$_REQUEST['id']:'',
		),
		'columnReplacements' => array ( 
			'%column_id_value%' => 'id',
			'%column_name_value%' => 'title',
			'%column_description_value%' => 'description',
		),
		'replacements' => array(
			'%product_type_value%' => $productTypeId,
			'%remove%'  => wsfGetValue(VN_buttonDel),
			'%name%'  => wsfGetValue(VN_Name),
			'%description%'  => wsfGetValue(description),
		),
		'files' => array(
		),
	),*/
);
$templates['image_box'] = array(
	'header'=>'
		<div id="%temp_name%header">
			<table class="imageBoxTable titleTh" width="100%">
				<tr>
					<td  align="'.$langInfo['htmlAlign'].'" width="50">%remove%</td>
					<td  align="'.$langInfo['htmlAlign'].'" width="200">%name%</td>
					<td  align="'.$langInfo['htmlAlign'].'" width="300">%image%</td>
					<td  align="'.$langInfo['htmlAlign'].'">&nbsp;</td>
				</tr>
			</table>
		</div>
	',
	'addible' => '
		<div id="%temp_name%[%{item_number}%]">
			<input name="%temp_name%[%{item_number}%][columns][id]" type="hidden" value="%column_id_value%"/>
			<table class="imageBoxTable" width="100%">
				<tr>
					<td  align="'.$langInfo['htmlAlign'].'" width="50">
						<input name="%temp_name%[%{item_number}%][delete]" type="checkbox"/>
					</td>
					<td  align="'.$langInfo['htmlAlign'].'" width="200" ><input name="%temp_name%[%{item_number}%][columns][title]" type="text" value="%column_name_value%" size="30" class="input" /></td>
					<td  align="'.$langInfo['htmlAlign'].'" width="300" >
						<input name="%temp_name%_%{item_number}%_columns_image" type="file"  />				
						
						<input name="old_%temp_name%_%{item_number}%_columns_image" type="hidden" value="%column_old_image_value%"  class="input" />
					</td>
					<td height=28  align="'.$langInfo['htmlAlign'].'" >
							%image_tag%
					</td>
				</tr>	
			</table>
		</div> 
	'
);
/*
$templates['extraFileds_box'] = array(
	'header'=>'
		<div id="%temp_name%header">
			<table width="100%" class="imageBoxTable titleTh">
				<tr>
					<td align="'.$langInfo['htmlAlign'].'" width="50">%remove%</td>
					<td align="'.$langInfo['htmlAlign'].'" width="200">%name%</td>
					<td align="'.$langInfo['htmlAlign'].'" width="300">%description%</td>
					<td></td>
				</tr>
			</table>
		</div>
	',
	'addible' => '
		<div id="%temp_name%[%{item_number}%]">
			<input name="%temp_name%[%{item_number}%][columns][id]" type="hidden" value="%column_id_value%"/>
			<table width="100%" class="imageBoxTable">
				<tr>
					<td align="'.$langInfo['htmlAlign'].'" width="50">
						<input name="%temp_name%[%{item_number}%][delete]" type="checkbox"/>
					</td>
					<td align="'.$langInfo['htmlAlign'].'" width="200" ><input name="%temp_name%[%{item_number}%][columns][title]" type="text" value="%column_name_value%" size="30" class="input" /></td>
					<td align="'.$langInfo['htmlAlign'].'" width="300" ><textarea name="%temp_name%[%{item_number}%][columns][description]" size="50" rows="3" class="textarea">%column_description_value%</textarea></td>
					<td></td>
				</tr>	
			</table>
		</div> 
	'
);
*/
if ($_REQUEST['action'] =='save_order' ) 
{
	//cmfcMySql::setOption("debugEnabled", true);
	
	$orderNumber = 0;
	
	$orderListArray = explode(',', $_POST['orderList']);
	
	foreach ($orderListArray as $orderId) {
		if($orderId)
		{
			$orderNumber++;
			$column = array($sectionInfo['tableInfo']['columns']['orderNumber'] => $orderNumber);
			cmfcMySql::update(
				$sectionInfo['tableInfo']['tableName'],
				$sectionInfo['tableInfo']['columns']['relatedItem'],
				$column,
				$orderId
			);
		}
	}
	//cmfcHtml::printr(cmfcMySql::getRegisteredQueries());
	//$_REQUEST['action'] = 'order';
	$messages['messages'][] = wsfGetValue("orderInListSaved");
	$messages['all'][]=sprintf(
		'<META http-equiv="refresh" content="1;URL=?%s">',
		cmfcUrl::excludeQueryStringVars(array('action', 'save_order', 'pageType', 'sectionName', 'pt'),'get')
	);
}

if ((isset($_POST['submit_save']) and $_GET['action']!='view') || isset($_POST['saveEditNextLang'])) 
{

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
			$query = ' SELECT MIN('.$sectionInfo['tableInfo']['columns']['orderNumber'].") as `max` FROM `".$sectionInfo['tableInfo']['tableName']."` WHERE `".$sectionInfo['tableInfo']['columns']['languageId']."=`".$_columns['languageId'];
			$max = cmfcMySql::loadCustom($query);
			$_commonColumns['orderNumber'] = (int)$max['max'] -1;
			$_columns['editorId'] = $userSystem->cvId;
			$_commonColumns['productType'] = $productTypeId;
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
		}
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;

		
		
		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename']){
			$_commonColumns['photoFilename'] = wsfUploadFileAuto(
				"rows[$num][common][photoFilename]",
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
			);
		}
		
		if ($_cp['sectionInfo']['tableInfo']['columns']['categoryPath']){
			$_commonColumns['categoryPath'] = wsfGetCategoryPathForDb(
				$treeTableDB['tableName'], 
				$_commonColumns['categoryId'],
				$selectTitleValuePair, 
				$selectTitleValuePair['value'] 
			);
		}
		
			
		$_commonColumns['price'] = str_replace(',', '', $_commonColumns['price']);
		
		if($_commonColumns['price'] != $_commonColumns['oldPrice'])
			$isPriceChanged = true;
		
		$columnsValues=array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		
		
		/*if($row['action']!='delete')
		{
			if(!preg_match('/^[a-zA-Z_0-9\-]+$/i', $_commonColumns['urlName']))
			{
				$validateResult[] = PEAR::raiseError(wsfGetValue("urlPatternMessage"));
			}
			elseif(cpfIsInternalNameExists($sectionInfo['tableInfo'], $_commonColumns['urlName'], $_GET['id']))
			{
				$validateResult[] = PEAR::raiseError(wsfGetValue("VN_internalnameExists"));
			}
		}*/
		
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
					
					$relatedItemId = $_GET['id'];
				}
			}
			wsfSaveOverrideValues($_commonColumns, $relatedItemId);
			
			
			if(in_array($row['action'] , array('insert', 'update') ))
			{
				foreach($addibleItems as $addibleItem)
					wsfSaveAddibleItems($addibleItem, $relatedItemId);
			}
			
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
		
		if (!empty($row)) 
		{
			$_POST['rows'][$num]['columns'] = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);

		}
		else
		{
			$_REQUEST['action'] = "new";
		}
		
		//$_POST['rows'][$num]['extraColumns'] = cpfLoadExtraFiledValues($productTypeId, $_REQUEST['id']);
	}
}

if ($_REQUEST['action']=='list' or $_REQUEST['action']=='order') 
{
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
		
		if($_REQUEST['search']['name'] != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:name}` LIKE '%[name]%')";
		}
				
		if($_REQUEST['search']['summary']  != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:summary}` LIKE '%[summary]%')";
		}
				
		if($_REQUEST['search']['specification']  != ""){
			$searchSqlWhereQuery .= " AND (
				`{td:specification}` LIKE '%[specification]%')";
		}
		
		if($_REQUEST['search']['brandId'])
		{
			$searchSqlWhereQuery.=" AND (
				(`{td:brandId}`=[brandId])
			)";
		}
		
		if($_REQUEST['search']['guaranteeType'])
		{
			$searchSqlWhereQuery.=" AND (
				(`{td:guaranteeType}`=[guaranteeType])
			)";
		}
		
		$replacements=array(
			'{td:name}'=>$_cp['sectionInfo']['tableInfo']['columns']['name'],
			'{td:summary}'=>$_cp['sectionInfo']['tableInfo']['columns']['summary'],
			'{td:specification}'=>$_cp['sectionInfo']['tableInfo']['columns']['specification'],
			'{td:brandId}'=>$_cp['sectionInfo']['tableInfo']['columns']['brandId'],
			'{td:guaranteeType}'=>$_cp['sectionInfo']['tableInfo']['columns']['guaranteeType'],
			'[name]'=>$_REQUEST['search']['name'],
			'[summary]'=>$_REQUEST['search']['summary'],
			'[specification]'=>$_REQUEST['search']['specification'],
			'[brandId]'=>$_REQUEST['search']['brandId'],
			'[guaranteeType]'=>$_REQUEST['search']['guaranteeType'],
		
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
	
	if($_REQUEST['action'] == 'list')
	{
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
			'sortBy'=>$_cp['sectionInfo']['tableInfo']['columns']['orderNumber'],
			'sortType'=>'ASC',
			'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
		));
		
		//echo $sqlQuery;
		$sqlQuery=$paging->getPreparedSqlQuery();
		#--(End)-->Paging
	}
	else
		$sqlQuery .= " ORDER BY `".$_cp['sectionInfo']['tableInfo']['columns']['orderNumber']."` ASC";
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
	function changeVideoInput(value)
	{
		$('.videoTypeContainer').css('display', 'none');
		//alert('#videoTypeInput_'+value);
		$('#videoTypeInput_'+value).css('display', 'inline');
	}
	
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
if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) 
{
	
	if($relatedItemId)
	{
		if($_POST['rows'][$num]['common'])
		{
			$commonColumns = cmfcMySql::load($sectionInfo['tableInfo']['tableName'], $sectionInfo['tableInfo']['columns']['relatedItem'], $relatedItemId);
			$_POST['rows'][$num]['common']['photoFilename'] = $commonColumns['photo_filename'];
			$_POST['rows'][$num]['common']['fileGeneralCatalog'] = $commonColumns['file_general_catalog'];
			$_POST['rows'][$num]['common']['fileCatalog'] = $commonColumns['file_catalog'];
			$_POST['rows'][$num]['common']['fileManual'] = $commonColumns['file_manual'];
			$_POST['rows'][$num]['common']['relatedStandards'] = $commonColumns['related_standards'];
		}
		else
		{
			$commonColumns = cmfcMySql::load($sectionInfo['tableInfo']['tableName'], $sectionInfo['tableInfo']['columns']['relatedItem'], $relatedItemId);
			if (!is_null($commonColumns['usability']))
				$commonColumns['usability'] = explode(',',$commonColumns['usability']);
				
			$_POST['rows'][$num]['common'] = cmfcMySql::convertColumnNames($commonColumns, $sectionInfo['tableInfo']['columns']);
			
		}
	}
	
	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}
	wsfWysiwygLoader(array(
		'templateName'=>'fullWidthFileAndImageManager',
		//'templateName'=>'simple',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][generalCharacteristics]"=>array('direction'=>$itemLanguage['direction']),			
			//"rows[$num][columns][specification]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));
	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);
	$infa->showSeparatorRow(wsfGetValue('commonInfo'));
	
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
			'از ارسال فایل هایی با حجمی بالاتر از 1 مگابایت خودداری کنید!'
		);
	}
	/*
	if ($_POST['rows'][$num]['common']['relatedStandards'])
	{
		$relatedStandards = explode(';',$_POST['rows'][$num]['common']['relatedStandards']);
		
		foreach ($relatedStandards as $key1=>$row1)
		{
			 $tempQuery = 'SELECT `title`  AS title, `related_item` AS relatedItem FROM '.$_cp['sectionInfo']['standardsTable']['tableName'].
				' WHERE '.$_cp['sectionInfo']['standardsTable']['columns']['relatedItem']." = ".$row1.
				' AND '.$_cp['sectionInfo']['standardsTable']['columns']['languageId']." = ".$translation->languageInfo['id'];
			 $temp = cmfcMySql::loadCustom($tempQuery);
			 
			$newRelatedStandards[$key1]['title'] = $temp['title'];
			$newRelatedStandards[$key1]['id'] = $temp['relatedItem'];
		}
		if ($newRelatedStandards)
		{
			$relatedStandardsHtml = '<ul id="relatedStandardsList">';
			foreach ($newRelatedStandards as $key1=>$row1)
			{
				$relatedStandardsHtml .= '<li><img src="interface/images/min_on.gif" onclick="$(this).parent().remove();removeRelatedStandard('.$row1['id'].');">&nbsp;'.$row1['title'].'</li>';
			}
			$relatedStandardsHtml .= '</ul>';
		}
	}
	else
	{
		$relatedStandardsHtml = '<ul id="relatedStandardsList"></ul>';	
	}
	$infa->showCustomRow(wsfGetValue('relatedStandards'), 
		'<input type="text" dir="rtl" value="" size="40" class="input" id="autocomplete_'.$num.'_relatedStandards" name="rows['.$num.'][common][relatedStandards]"/>'
		.$relatedStandardsHtml
		.'<input type="hidden" id="rows_'.$num.'_common_relatedStandards" name="rows['.$num.'][common][relatedStandards]" value="'.$_POST['rows'][$num]['common']['relatedStandards'].'" />',
		'چند حرف از نام محصول مورد نظر را تایپ کنید و از لیستی که ظاهر میشود روی محصول مورد نظر کلیک کنید'
	);
	*/
	
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
		
		
	$infa->showInputRow(wsfGetValue('title'), "rows[$num][columns][title]", $_POST['rows'][$num]['columns']['title'], '',40, $itemLanguage['direction']); 
	/*
	$items = cmfcMySql::getRowsWithMultiKeys(
		$_cp['sectionInfo']['categoriesTable']['tableName'],
		array(
			$_cp['sectionInfo']['categoriesTable']['columns']['internalName'] => 'products',
		)
	);
	
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
	/* */
	
	
	
	//$infa->showInputRow(wsfGetValue('guarantySiteUrl'), "rows[$num][columns][url]", $_POST['rows'][$num]['columns']['url'], 'مثال : <span dir="ltr">http://www.persiantools.com</span> لینک وارد شده باید حتما <span dir="ltr">"http://"</span> داشته باشد.',40, 'ltr'); 
	//$infa->showCheckBoxRow(wsfGetValue('orderFlag'),"rows[$num][common][orderFlag]",$_POST['rows'][$num]['common']['orderFlag']);

	$infa->showTableFooter();
	$infa->showTableHeader($translation->getValue('generalCharacteristics'));
	$infa->showTextAreaRow(wsfGetValue('generalCharacteristics'),"rows[$num][columns][generalCharacteristics]", $_POST['rows'][$num]['columns']['generalCharacteristics'],'', '', 15, '90%');
	
	
	
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
		
	
	foreach($addibleItems as $key=>$value){
		$infa -> drawAddibleBoxes(
			$value['title'],
			$value,
			$templates
		);
	}
	
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
		
		/*array(
			'name' => 'changeLangButton',
			'value' => '1',
			'type' => 'submit',
			'attributes' => array(
				'style' => 'display:none',
			),
		),*/
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
				'value' =>  wsfGetValue('submit_and_go_next_lang'),
			),
		);
		
		$buttons = cmfcPhp4::array_merge($saveAndNextLang, $buttons);
	}
	?><div style="display:none;"><input type="submit" style="display:none;" value="1" class="button" name="changeLangButton" /></div><?php
	
	$infa->showFormFooterCustom($buttons);
	$infa->prepareAddibleBoxesTempalte($addibleItems,$templates);
	?>
    <script type="text/javascript">
		function removeRelatedStandard(standardId)
		{
			var currentProducts = ";"+$("#rows_<?php echo $num?>_common_relatedStandards").val()+";";
			var reg = new RegExp("[;]{1}[" + standardId + "]{1}[;]{1}");
			currentProducts = currentProducts.replace(reg,";");
			currentProducts = currentProducts.slice(1,currentProducts.length-1);
			$("#rows_<?php echo $num?>_common_relatedStandards").val(currentProducts);
		}
		$(document).ready(function(){
			//var data = "Core Selectors Attributes Traversing Manipulation CSS Events Effects Ajax Utilities".split(" ");
			$("#autocomplete_<?php echo $num?>_relatedStandards").autocomplete('standardsList.php',{
				width: 300,
				multiple: true,
				matchContains: true,
				formatItem: formatItem,
				formatResult: formatResult
			});
			
			$("#autocomplete_<?php echo $num?>_relatedStandards").result(function(event, data, formatted) {
				if (data)
				{
					//$(this).parent().next().find("input").val(data[1]);
					var hidden = $("#rows_<?php echo $num?>_common_relatedStandards");
					hidden.val( (hidden.val() ? hidden.val() + ";" : hidden.val()) + data[1]);
					$(this).val('');
					//hidden.val(data[1]);

					$("<li>").html(!data ? "No match!" : "<img src='interface/images/min_on.gif' onclick='$(this).parent().remove();removeRelatedStandard("+data[1]+");'>&nbsp;"+formatted).appendTo("#relatedStandardsList");
				}
			});

		});
		
		function formatItem(row) {
			return row[0] ;
		}
		
		function formatResult(row) {
			return row[0].replace(/(<.+?>)/gi, '');
		}
	</script>

    <?php }
elseif ($_REQUEST['action']=='list')
{
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
			
			<tr class="table-row1" align="<?php echo $langInfo['htmlAlign']?>" >
				<td width="200"><?php echo wsfGetValue('name')?> </td>
				<td align="<?php echo $langInfo['htmlAlign']?>" ><input name="search[name]" class="input" type="text" value="<?php echo $_REQUEST['search']['name']?>" style="width:50%" /></td>
			</tr>
            <?php
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
			}?>
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
	<?php 	if (is_array($rows)) {
	?>
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="10" class="table-header" align="<?php echo $langInfo['htmlAlign']?>" > <?php echo $actionTitle ?>  </td>
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
				
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('title','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('title','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('title')?>
				</td>
                <?php /*?><td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('category')?>
				</td>
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo wsfGetValue('orderFlag')?>
				</td><?php */?>
				
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
				
				if($row['brandId'] && !isset($_ws['brands'][$row['brandId']]))
				{
					$brand = cmfcMySql::loadWithMultiKeys(
						$sectionInfo['productBrandsTable']['tableName'],
						array(
							$sectionInfo['productBrandsTable']['columns']['relatedItem'] => $row['brandId'],
							$sectionInfo['productBrandsTable']['columns']['languageId'] => $langInfo['id'],
						)
					);
					$_ws['brands'][$row['brandId']] =  $brand['name'];
				}
				
				if($row['guaranteeType'] && !isset($_ws['guarantees'][$row['guaranteeType']]))
				{
					$guarantee = cmfcMySql::loadWithMultiKeys(
						$sectionInfo['productGuaranteesTable']['tableName'],
						array(
							$sectionInfo['productGuaranteesTable']['columns']['relatedItem'] => $row['guaranteeType'],
							$sectionInfo['productGuaranteesTable']['columns']['languageId'] => $langInfo['id'],
						)
					);
					$_ws['guarantees'][$row['guaranteeType']] =  $guarantee['name'];
				}
				
				?>
				<tr class="table-row1" onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';">
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
				
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['title'];?>
				</td>
                <?php
				/*
                if ($_cp['sectionInfo']['tableInfo']['columns']['categoryId'])
				{
				?>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo wsfGetCategoryPathByLang(
						$treeTableDB['tableName'],
						$row['categoryId']);
					?>
				</td>
				<?php
				}
				?>
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" id="orderFlagFlag<?php echo $row['id']?>"> 
                    <?php $iimg = ($row['orderFlag'])?'tick.png':'bullet_red.png';?>
                    <a href="<?php echo $_SERVER['REQUEST_URI']?>&fn=orderFlag&targetItemId=<?php echo $row['relatedItem']?>&currentValue=<?php echo $row['orderFlag']?>"
                    <?php 
                    if ($ajax)
                    {?>onclick="<?php echo $ajax->jsfSimpleCallForATagOnClick(array('orderFlagFlag'.$row['id'],'pageMessages'))?>"<?php }?>
                    >
                        <img src="interface/images/<?php echo $iimg;?>" id="orderFlag<?php echo $row['id']?>" alt="<?php echo $translation->getValue('orderFlag')?>" style="cursor:pointer; border:0px;" />
                    </a>
                </td>
                <?php */?>
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo wsfGetValue(buttonNew) ?>" onclick="window.location='?<?php echo $actionsBaseUrl?>&action=new'" />
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
elseif ($_REQUEST['action']=='order'){
	
	
	
	?>
	<style type="text/css">
	#product-list { list-style-type: none; margin: 0; padding: 0; width: 100%; direction: ltr;}
	#product-list li { margin:3px; padding:3px 3px 3px 25px; background:url(interface/images/bgProduct.gif) repeat-y; height: 120px;border:1px solid #777; float: left; width: 100px; cursor:move;}
	#product-list li span { position: absolute; margin-left: -1.3em; }
	.imageCat{ height:64px; padding:10px 0 10px 0;}
    </style>
	<script type="text/javascript"> 
	// When the document is ready set up our sortable with it's inherant function(s) 
	$(document).ready(function() { 
	  $("#product-list").sortable(); 
	}); 
	</script>	
	
	<form name="myListForm"  action="?<?php echo wsfExcludeQueryStringVars(array('sectionName'),'get')?>" method="post" style="margin:0px;padding:0px" enctype="multipart/form-data">
	<input type="hidden" id="orderList" name="orderList" value="" />
	<?php 	if (is_array($rows)) {
		/*
		cpfOrderSortingFunctions();
	?>
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="10" class="table-header" align="<?php echo $langInfo['htmlAlign']?>" > <?php echo $actionTitle ?>  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" >
					#
				</td>
				
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('order')?>
					<?php echo cpfOrderSortingButtons()?>
				</td>
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue(name)?>
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
				<tr class="table-row1"   onmouseover="this.className='table-row-on';" onmouseout="this.className='table-row1';" onclick="thisrow(this)" id="<?php echo $row['id']?>">
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" >
					<?php echo $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-title" > 
					انتخاب سطر
					<?php 					echo cpfOrderSortingField($row['id'], $row['orderNumber'])
					?>
				</td>
				
				<td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['name'];?>
				</td>
			</tr>
			<?php }?>
	  </table>
		
		<?php */?>
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td>
					<ul id="product-list">
					<?php 					foreach ($rows as $key=>$row) 
					{
						//print_r($row);
						$num = $key+1;
						//--(Begin)-->convert columns physical names to their internal names
						$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
						//--(End)-->convert columns physical names to their internal names
						?>
						
						<li class="ui-state-default" id="<?php echo $row['relatedItem']?>">
                            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
							<?php /*?><img src="interface/images/arrow2.png" alt="move" width="16" height="16" class="handle" /><?php */?>
							<div class="imageCat">
							<?php 							if ($row['photoFilename'])
							{
								echo $imageManipulator->getAsImageTag(array (
									'fileName' => $row['photoFilename'],
									'fileRelativePath' => $sectionInfo['folderRelative'],
									'version' => 2,
									'actions' => array (
										 array (
											'subActions' => array (
												 array (
													'name' => 'resizeSmart',
													'parameters' => array (
														'width' => 64,
														'height' => 64,
														'priority' => array (
															 'width',
															 'height',
														),
													),
												),
											),
										),
									),
								));
							}
							?>
                            </div>
							<strong><?php echo $row['title']?></strong> 
						</li>
						
						<?php 
					}
					?>
					</ul>
				</td>
				
			</tr>
		</table>
		<div style="text-align:center">
			<input name="submit_save_order" onclick="$('#orderList').val($('#product-list').sortable('toArray')); " class="button" type="submit" value="<?php echo wsfGetValue(saveOrder) ?>" />
		</div>
	<?php 	}
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