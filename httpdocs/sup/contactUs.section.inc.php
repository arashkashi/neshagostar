<?php
$infa=new cpInterface();

if($_REQUEST['id'] )
{
	$related = cmfcMySql::load($_cp['sectionInfo']['tableInfo']['tableName'],'id',$_REQUEST['id']);
	$related = cmfcMySql::convertColumnNames($related, $_cp['sectionInfo']['tableInfo']['columns']);
		
	if (!$_POST['itemLanguage'])
		$itemLanguage = cmfcMySql::load($_cp['sectionsInfo']['languages']['tableInfo']['tableName'], 'id', $related['languageId']);
	else
		$itemLanguage = cmfcMySql::load($_cp['sectionsInfo']['languages']['tableInfo']['tableName'], 'id', $_POST['itemLanguage']);
}
else
{
	$itemLanguage = cmfcMySql::load(
		$_cp['sectionsInfo']['languages']['tableInfo']['tableName'],
		'id',
		$_POST['itemLanguage']
	);
}

if (!$itemLanguage)
{
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
		$_cp['sectionInfo']['tableInfo']['columns']["id"] => $_REQUEST['id']
	)
);

if($editable) 
	$_GET['id'] = $_REQUEST['id'] = $editable['id'];
else
	$_GET['id'] = $_REQUEST['id'] = '';
	

$num=1;
$messages=array();

if ($_REQUEST['action']=='delete') 
{
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='single';	
	$_POST['rows'][$num]['columns']['id']=$_GET['id'];
	$_POST['rows'][$num]['action']='delete';
}

if ($_POST['submit_delete']) 
{
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'delete';
}

/*-----------------------------------------------------------*/

if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='view')   {$actionTitle = wsfGetValue('view');}
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}


if (isset($_REQUEST['exportAsExcel']))
{
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName'];
	$rows=cmfcMySql::getRowsCustom($sqlQuery);

	$line = array(
		$translation->getValue('row'),
		$translation->getValue('firstName'),
		$translation->getValue('lastName'),
		$translation->getValue('companyName'),
		$translation->getValue('category'),
		$translation->getValue('purchaseDate'),
		$translation->getValue('address'),
		$translation->getValue('email'),
		$translation->getValue('tel'),
		$translation->getValue('description'),
		$translation->getValue('insertDatetime'),
	);
	
	$exportFileName = 'contact_us.xls';
	$excelFileName = $_ws['siteInfo']['path'].$_ws['siteInfo']['cacheFolderRPath']."/".$exportFileName;
	
	ob_clean();
	$excel=new ExcelWriter($excelFileName, 'utf-8', $translation->languageInfo['direction']);
	@chmod($excelFileName, 0777);
	
	if (!is_writable($excelFileName)) {
	   trigger_error('Export file is not writable',E_USER_ERROR);
	   exit;
	}

	//results columns names
	$excel->writeLine($line);
	$qn=0;
	
	if($rows)
	{
		$num=1;
		foreach ($rows as $key => $row) 
		{
			$mineRow = array();
			$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
			$categoryName =cmfcMysql::loadWithMultiKeys(
								$sectionInfo['categoriesTable']['tableName'],
								array(
									 $sectionInfo['categoriesTable']['columns']['relatedItem']=>$row['categoryId'],
									 $sectionInfo['categoriesTable']['columns']['languageId']=>$translation->languageInfo['id'],
									  )
								);
			$mineRow[] = $key+1;
			$mineRow[] = $row['firstName'];
			$mineRow[] = $row['lastName'];
			$mineRow[] = $row['companyName'];
			$mineRow[] = $categoryName['title'];
			$mineRow[] = wsfGetDateTime('Y/m/d', $row['purchaseDate'], $translation->languageInfo['shortName']);
			$mineRow[] = $row['address'];
			$mineRow[] = $row['email'];
			$mineRow[] = $row['tel'];
			$mineRow[] = $row['body'];
			$mineRow[] = wsfGetDateTime('Y/m/d', $row['insertDatetime'], $translation->languageInfo['shortName']);
			
			$excel->writeLine( $mineRow);
		}
	}
	
	$excel->close();
	
	cpfDownloadFile($excelFileName);
	
	//--(END)-->gather information
	
	?>
	<script language="javascript" type="text/javascript">
		window.close();
	</script>
	<?php 	
	ob_flush();
}


if ((isset($_POST['submit_save']) and $_GET['action']!='view')) 
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

		$columnsValues=array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		
		#--(End)-->fill and validate fields
		
		if (empty($validateResult)) {
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
			}elseif ($row['action']=='delete') {
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
		//$_POST['rows'][$num]['columns'] = $editable;
	}
	if ($_REQUEST['action']=='view') 
	{
		$sqlQuery=sprintf(
			"SELECT * FROM %s WHERE %s='%s' ",
			$_cp['sectionInfo']['tableInfo']['tableName'],
			$_cp['sectionInfo']['tableInfo']['columns']['id'],
			$_REQUEST['id']
		);
		$row=cmfcMySql::loadCustom($sqlQuery);
		if (!empty($row))
		{
			$_POST['rows'][$num]['personalInfo'] = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
		}
	}
}

if ($_REQUEST['action']=='list') 
{
	$searchSqlWhereQuery="";
	$limit=$_cp['sectionInfo']['listLimit'];
	#--(Begin)-->generate Sql Query
	
	if (isset($_REQUEST['submit_search'])) 
	{
		
		if($_REQUEST['search']['firstName'] != "" && $_REQUEST['search']['lastName'] != "")
		{
			$searchSqlWhereQuery = " AND (
				(`{td:lastName}` LIKE '%[lastName]%') AND
				(`{td:firstName}` LIKE '%[firstName]%' )
			)";
		}
		elseif($_REQUEST['search']['firstName'] != "")
		{
		
			$searchSqlWhereQuery = " AND (
				`{td:firstName}` LIKE '%[firstName]%')";
		}
		elseif($_REQUEST['search']['lastName']  != "")
		{
			
			$searchSqlWhereQuery = " AND (
				`{td:lastName}` LIKE '%[lastName]%')";
		}
		
		if (isset($_REQUEST['search']['startDate']))
		{
			$registerDate = $_REQUEST['search']['startDate'];
			$_REQUEST['search']['startDate'] = wsfConvertDateTimeDropDownArrayToDateTimeString($registerDate, 'Y-m-d');
			if($_REQUEST['search']['startDate'])
			{
				$_REQUEST['search']['startDate'] .= " 0:0:0";
				$searchSqlWhereQuery .= " AND (
					(`{td:insertDatetime}`>='[startDate]' )
				)";
			}
		}
		if (isset($_REQUEST['search']['endDate']))
		{
			$registerDate = $_REQUEST['search']['endDate'];
			$_REQUEST['search']['endDate'] = wsfConvertDateTimeDropDownArrayToDateTimeString($registerDate, 'Y-m-d');
			if($_REQUEST['search']['endDate'])
			{
				$_REQUEST['search']['endDate'] .= " 23:59:59";
				$searchSqlWhereQuery .= " AND (
					(`{td:insertDatetime}`<='[endDate]' )
				)";
			}
		}
		
		if($_REQUEST['search']['companyName'])
		{
			$searchSqlWhereQuery .= " AND (
				`{td:companyName}` LIKE '%[companyName]%')";
		}
		
		if($_REQUEST['search']['categoryId']){
			
			$searchSqlWhereQuery .= " AND (
				`{td:categoryId}` = '[categoryId]')";
			
		}
		
		$replacements=array(
			'{td:firstName}'=>$_cp['sectionInfo']['tableInfo']['columns']['firstName'],
			'{td:lastName}'=>$_cp['sectionInfo']['tableInfo']['columns']['lastName'],
			'{td:insertDatetime}'=>$_cp['sectionInfo']['tableInfo']['columns']['insertDatetime'],
			'{td:companyName}'=>$_cp['sectionInfo']['tableInfo']['columns']['companyName'],
			'{td:nationalCode}'=>$_cp['sectionInfo']['tableInfo']['columns']['nationalCode'],
			'{td:birthPlace}'=>$_cp['sectionInfo']['tableInfo']['columns']['birthPlace'],
			'{td:categoryId}'=>$_cp['sectionInfo']['tableInfo']['columns']['categoryId'],
			'[categoryId]'=>$_REQUEST['search']['categoryId'],
			'[firstName]'=>$_REQUEST['search']['firstName'],
			'[lastName]'=>$_REQUEST['search']['lastName'],
			'[companyName]'=>$_REQUEST['search']['companyName'],
			'[startDate]'=>$_REQUEST['search']['startDate'],
			'[endDate]'=>$_REQUEST['search']['endDate'],
		);
		
		$searchSqlWhereQuery=cmfcString::replaceVariables($replacements, $searchSqlWhereQuery);
			
	}
	
	if (!isset($_REQUEST['viewLangId']) )
		$_REQUEST['viewLangId'] = $itemLanguage['id'];
	
	/*if ($_REQUEST['viewLangId'])
		$searchSqlWhereQuery .= " AND ".$_cp['sectionInfo']['tableInfo']['columns']['languageId']." ='".$_REQUEST['viewLangId']."'";
	*/	
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName'].
		" WHERE (1=1) ".
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

if (in_array($_REQUEST['action'],array('view'))) 
{
	//cmfcHtml::printr($_POST['rows']);
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
	
	if (!empty($fieldsValidationInfo)) 
	{
		$validation->printJsClass();
		$validation->printJsInstance();
	}
	
	$infa->showFormHeader(null,'myForm',true);
	$infa->showTableHeader($actionTitle);
	
	$infa->showSeparatorRow( wsfGetValue('personalInfo') );
	
	if ($_POST['rows'][$num]['personalInfo']['photoFilename'])
	{
		$infa->showCustomRow(
			$translation->getValue("photo"),
			$imageManipulator->getAsImageTag(
				array(
					'fileName'=>$_POST['rows'][$num]['personalInfo']['photoFilename'],
					'fileRelativePath'=>$_cp['sectionInfo']['folderRelative'],			
					'width'=> 150,
					'height' => 200,
					//'cropPosition' => 'center',
					'mode'=> array(
						'resizeByMaxSize',
						//'cropToSize'
					),
					'attributes'=>array(
						'alt'=>'',
						'class'=>''
					),
				)
			)
		);
	}
	$infa->showCustomRow($translation->getValue("firstName"),$_POST['rows'][$num]['personalInfo']['firstName']);
	$infa->showCustomRow($translation->getValue("lastName"),$_POST['rows'][$num]['personalInfo']['lastName']);
	$infa->showCustomRow($translation->getValue("companyName"),$_POST['rows'][$num]['personalInfo']['companyName']);
	$categoryName = cmfcMysql::loadWithMultiKeys(
					$sectionInfo['categoriesTable']['tableName'],
					array(
						 $sectionInfo['categoriesTable']['columns']['relatedItem']=>$_POST['rows'][$num]['personalInfo']['categoryId'],
						 $sectionInfo['categoriesTable']['columns']['languageId']=>$translation->languageInfo['id'],
						  )
					);
	$infa->showCustomRow($translation->getValue("category"),$categoryName['title']);
	$infa->showCustomRow($translation->getValue("purchaseDate"),wsfGetDateTime('d M Y', $_POST['rows'][$num]['personalInfo']['purchaseDate'], $translation->languageInfo['shortName']));
	$infa->showCustomRow($translation->getValue("address"),$_POST['rows'][$num]['personalInfo']['address']);
	$infa->showCustomRow($translation->getValue("tel"),$_POST['rows'][$num]['personalInfo']['tel']);	
	$infa->showCustomRow($translation->getValue("email"),$_POST['rows'][$num]['personalInfo']['email']);
	$infa->showCustomRow($translation->getValue("description"),$_POST['rows'][$num]['personalInfo']['body']);

	
	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	}
	elseif($_REQUEST['action'] == 'new') {
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	
	
	if (!isset($_REQUEST['print']))
	{
		$buttons = array(
			array(
				'name' => 'cancel',
				'value' => wsfGetValue('buttonReturn'),
			),
			array(
				'name' => 'print',
				'value' => wsfGetValue('buttonPrint'),
				'attributes'=> array(
					'onclick' => "cmfPopitup('popup.php?sn=".$_GET['sn']."&action=view&id=".$_REQUEST['id']."&print=1'); return false;"
				)
			),
		);
	}
	else
	{
		$buttons = array(
			array(
				'name' => 'print',
				'value' => wsfGetValue('buttonPrint'),
				'attributes'=> array(
					'onclick' => "window.print();return false;"
				)
			),
		);
	}
	
	
	$infa->showFormFooterCustom($buttons);
}
elseif ($_REQUEST['action']=='list')
{
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" dir="<?php echo $langInfo['htmlDir']?>" > 
			<tr>
			    <td  align="<?php echo $translation->languageInfo['!align']?>" >
                    <table border="0" cellpadding="0" cellspacing="1" class="option-link"  align="<?php echo $translation->languageInfo['!align']?>"  >
                        <tr>
                            <td class="quick-search-button"  align="<?php echo $translation->languageInfo['!align']?>" ><a href="javascript:void(0);" onclick="<?php echo $js->jsfToggleDisplayStyle('searchBoxContainer','auto')?>"><?php echo wsfGetValue('advancedSearch');?></a></td>
                        </tr>
                    </table>
                </td>
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
				 <?php echo wsfGetValue('advancedSearch')?>
				</td>
				<td class="option-table-buttons-spacer" align="<?php echo $translation->languageInfo['align']?>" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			<tr class="table-row1" align="<?php echo $translation->languageInfo['align']?>" >
				<td width="200"><?php echo wsfGetValue('firstName')?> </td>
				<td align="<?php echo $translation->languageInfo['align']?>" ><input name="search[firstName]" class="input" type="text" value="<?php echo $_REQUEST['search']['firstName']?>" style="width:50%" /></td>
			</tr>
            <tr class="table-row2" >
				<td align="<?php echo $translation->languageInfo['align']?>" ><?php echo wsfGetValue('lastName')?></td>
				<td  align="<?php echo $translation->languageInfo['align']?>" ><input name="search[lastName]" class="input" type="text" value="<?php echo $_REQUEST['search']['lastName']?>" style="width:50%" /></td>
			</tr>
            <tr class="table-row1" >
				<td align="<?php echo $translation->languageInfo['align']?>" ><?php echo wsfGetValue('companyName')?></td>
				<td  align="<?php echo $translation->languageInfo['align']?>" ><input name="search[companyName]" class="input" type="text" value="<?php echo $_REQUEST['search']['companyName']?>" style="width:50%" /></td>
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
						'--- No Category ---'
					),
					''
				);
			
			}
			?>
			<tr class="table-row2">
				<td colspan="2" align="<?php echo $translation->languageInfo['align']?>">
					<input class="button" type="submit" name="submit_search" value="<?php echo wsfGetValue(search)?>" />
					<input class="button" type="button" name="submit_cancel_search" value="<?php echo wsfGetValue('cancel')?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&lang=<?php echo $_GET['lang']?>'" />
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
					<a href="<?php echo $paging->getSortingUrl('first_name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('first_name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('firstName')?>
				</td>
                
                <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('last_name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('last_name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('lastName')?>
				</td>
                
                <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('companyName')?>
                </td>
                <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('category')?>
                </td>
                <td align="<?php echo $translation->languageInfo['align']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('email')?>
                </td>
			</tr>
			<?php 			foreach ($rows as $key=>$row) {
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
					<a href="?<?php echo $actionsBaseUrl?>&action=view&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_list.png" width="16" border="0" alt="edit" title="edit" />
					</a>
					<a onclick="return <?php echo $js->jsfConfimationMessage( wsfGetValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&action=delete&id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				<td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['firstName'];?>
				</td>
                <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['lastName'];?>
				</td>
                <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['companyName'];?>
				</td>
                <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php $categoryName =cmfcMysql::loadWithMultiKeys(
								$sectionInfo['categoriesTable']['tableName'],
								array(
									 $sectionInfo['categoriesTable']['columns']['relatedItem']=>$row['categoryId'],
									 $sectionInfo['categoriesTable']['columns']['languageId']=>$translation->languageInfo['id'],
									  )
								);
						echo $categoryName['title'];
						?>
                </td>
                <td class="field-title" align="<?php echo $translation->languageInfo['align']?>" > 
					<?php echo $row['email'];?>
				</td>
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
            <input name="exportAsExcel" class="submit" type="button" value="خروجی Excel" onclick="window.location='popup.php?sn=<?php echo $_GET['sn']?>&exportAsExcel=1'"/>
		</div>
	<?php 	}
	else { 
		?>
		<b><?php echo  wsfGetValue('nothingFound')?></b><?php
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
			<table border="0" align="<?php echo $translation->languageInfo['!align']?>" cellpadding="0" cellspacing="1" class="paging-nav">
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
