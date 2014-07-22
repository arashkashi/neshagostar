<?php
$infa=new cpInterface();
//$infa->setOverrideFields($_cp['sectionInfo']['overrideFields']);


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

/*-----------------------------------------------------------*/

if( $_POST[ 'submit_delete_all_languages'])
{
	$_POST['submit_save']='save';
	$_POST['submit_action']='deleteAllLangs';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'deleteAllLangs';
}

/*-------------------------------------------------------*/

if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}
if ($_REQUEST['action']=='deleteAllLangs') {$actionTitle = wsfGetValue('remove');}


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
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['relatedItem'] = $relatedItemId;

		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename'])
		{
			$_commonColumns['photoFilename'] = wsfUploadFileAuto(
				"rows[$num][common][photoFilename]",
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
			);
		}

		$columnsValues=array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		
		#--(End)-->fill and validate fields
		
		if (empty($validateResult)) 
		{
			#--(Begin)-->save changes to database
			
			/*------------------------------------*/
			/**
			* Delete From All Languages...
			*/

			if ($row['action']=='deleteAllLangs')
			{
				$_GET['id'] = intval( $_GET['id']);
				
				//<!-- Get The Related Item ID
				
					$SQL = "SELECT `{$columnsPhysicalName[ 'relatedItem']}` AS `relatedItem`
							FROM `{$_cp[ 'sectionInfo'][ 'tableInfo'][ 'tableName']}`
							WHERE `{$columnsPhysicalName[ 'id']}` = '{$_GET[ 'id']}'";
					$row = cmfcMySql::loadCustom( $SQL);
				//-->

				$SQL = "DELETE FROM `{$_cp[ 'sectionInfo'][ 'tableInfo'][ 'tableName']}`
						WHERE `{$columnsPhysicalName[ 'relatedItem']}` = {$row[ 'relatedItem']}";
				
				$result	= cmfcMySql::exec( $SQL);
				$error	= cmfcMySql::error();
				$msg	= wsfGetValue( 'removedFromAllLangs');

			/*------------------------------------*/			
			}
			elseif ($row['action']=='delete') 
			{
				$result=cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues['id']
				);
				$error = cmfcMySql::error();
				$msg = wsfGetValue('removeMsg');
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
		else 
		{
			foreach ($validateResult as $r) 
				$messages['errors'][]=$r->getMessage();
			$isErrorOccured=true;
		}
		$actionTaken = true;
	}

	if (!PEAR::isError($result)) 
	{
		if (!$isErrorOccured) 
		{
			#--(Begin)-->redirect to previous url if everthings is ok
			if(isset($_POST['saveEditNextLang']))
			{
				$nextLang = cpfGetNextEditableLang($itemLanguage['id']);
				$messages['messages'][]=sprintf(
					'<META http-equiv="refresh" content="1;URL=?%s">',
					wsfExcludeQueryStringVars(array('pageType','sectionName','pt', 'nextLang',),'get')."nextLang=".$nextLang
				);
			}
			else
			{
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
	if ($_REQUEST['action']=='edit') 
	{
	
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
	}
}

if ($_REQUEST['action']=='list') 
{
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	if ($_REQUEST['action']=='edit') $limit=3;
	#--(Begin)-->generate Sql Query
	
	//echo $_REQUEST['submit_search']."<br>";
	
	if (isset($_REQUEST['submit_search'])) 
	{
		
		if($_REQUEST['search']['title'] != "" && $_REQUEST['search']['body'] != "")
		{
			
			$searchSqlWhereQuery = " AND (
				(`{td:body}` LIKE '%[body]%') AND
				(`{td:title}` LIKE '%[title]%' )
			)";
			
		}
		elseif($_REQUEST['search']['title'] != "")
		{
		
			$searchSqlWhereQuery = " AND (
				`{td:title}` LIKE '%[title]%')";
				
		}
		elseif($_REQUEST['search']['body']  != "")
		{
			
			$searchSqlWhereQuery = " AND (
				`{td:body}` LIKE '%[body]%')";
			
			
		}
		/* */
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
		if($_REQUEST['search']['categoryId'])
		{
			$searchSqlWhereQuery .= " AND (
				`{td:categoryId}` = '[categoryId]')";
		}
		if($_REQUEST['search']['url'])
		{
			$searchSqlWhereQuery .= " AND (
				`{td:url}` LIKE '%[url]%')";
		}
		
		$replacements=array(
			'{td:title}'=>$_cp['sectionInfo']['tableInfo']['columns']['name'],
			'{td:body}'=>$_cp['sectionInfo']['tableInfo']['columns']['body'],
			'{td:categoryId}'=>$_cp['sectionInfo']['tableInfo']['columns']['categoryId'],
			'{td:url}'=>$_cp['sectionInfo']['tableInfo']['columns']['url'],
			'{td:publishDatetime}'=>$_cp['sectionInfo']['tableInfo']['columns']['publishDatetime'],
			'[startDate]'=>$_REQUEST['search']['startDate'],
			'[endDate]'=>$_REQUEST['search']['endDate'],
			'[url]'=>$_REQUEST['search']['url'],
			'[title]'=>$_REQUEST['search']['title'],
			'[body]'=>$_REQUEST['search']['body'],
			'[categoryId]'=>$_REQUEST['search']['categoryId'],
	
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
		'wordNext'=> wsfGetValue('next') ,
		'wordPrev'=> wsfGetValue('prev'),
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

cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

wsfPrintMessages($messages);
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
	wsfWysiwygLoader(array(
		'templateName'=>'fullWidthFileAndImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			//"rows[$num][columns][body]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));
	/* */
	$infa->showFormHeader(null,'myForm',true);
	$infa->showTableHeader($actionTitle);
	$infa->showSeparatorRow( wsfGetValue('commonInfo') );
	
	
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
	
	//$infa->showInputRow(wsfGetValue('url'), "rows[$num][common][url]", $_POST['rows'][$num]['common']['url'], 'مثال : http://www.persiantools.com<br /> لینک وارد شده باید حتما <span dir="ltr">http://</span> داشته باشد.',40, 'ltr'); 
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
			wsfGetValue('pleaseChoosePicture760x100px')
		);
	}
	
	$query = "SELECT * FROM ".$_cp['sectionsInfo']['languages']['tableInfo']['tableName'];
	$items = cmfcMySql::getRowsCustom($query);
	
	if($items)
	{
		$languageItemCount = count($items);
		foreach($items as $key=>$item)
		{
			$options[] = array(
				'id'=>$item['id'],
				'label'=>$item['name'],
				'href'=>"#",
				'onClick'=>"changeLang(".$item['id'].")",
			);
		}
	}
	
	$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	
	//if($languageItemCount > 1)
	//$infa->drawMultiTabs($options, $itemLanguage['id']);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
		
	$infa->showInputRow(wsfGetValue('name'), "rows[$num][columns][name]", $_POST['rows'][$num]['columns']['name'], '',40, $itemLanguage['direction']); 
	$infa->showInputRow(wsfGetValue('nameEn'), "rows[$num][columns][nameEn]", $_POST['rows'][$num]['columns']['nameEn'], '',40, 'ltr'); 
	/* */
	if ($_REQUEST['action'] == 'edit') 
	{
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	}
	elseif($_REQUEST['action'] == 'new') 
	{
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
	
	/*if($languageItemCount > 1)
	{
		$saveAndNextLang = array(
			array(
				'name' => 'saveEditNextLang',
				'value' =>  wsfGetValue(submit_and_go_next_lang),
			),
		);
		
		$buttons = cmfcPhp4::array_merge($saveAndNextLang, $buttons);
	}
	*/
	$infa->showFormFooterCustom($buttons);
}
elseif ($_REQUEST['action']=='list')
{
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
		
		
		
		<div id="searchBoxContainer" style=" <?php echo (!$_REQUEST['search'])?'display:none;':''; ?>" dir="<?php echo $langInfo['htmlDir']?>">
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
				<td class="field-subtitle" width="200"><?php echo wsfGetValue('provinceName')?> </td>
				<td class="field-insert" align="<?php echo $translation->languageInfo['align']?>" ><input name="search[title]" class="input" type="text" value="<?php echo $_REQUEST['search']['title']?>" style="width:50%" /></td>
			</tr>
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
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" style="border-color: #d4dce7" >
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
					<?php echo wsfGetValue(tools)?>
				</td>
				
				<td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue(provinceName)?>
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
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['name'];?>
				</td>
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo wsfGetValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
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
 if ($paging and $_REQUEST['action']=='list' and $paging->getTotalPages()>1) {?>
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