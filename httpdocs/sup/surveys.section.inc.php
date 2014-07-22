<?php
$infa=new cpInterface();
$userInfo = $userSystem->getOption('userInfo');


if(is_array($_POST['rows'])){
	foreach($_POST['rows'] as $rw){
		if(isset($rw['selected'])){
			$_POST['selected'][] = $rw['columns']['id'];
		}
	}
}
if($_REQUEST['id'] )
{
	$related = cmfcMySql::load($_cp['sectionInfo']['tableInfo']['tableName'],'id',$_REQUEST['id']);
	$related = cmfcMySql::convertColumnNames($related, $_cp['sectionInfo']['tableInfo']['columns']);
	$relatedItemId = $related['id'];
		
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

if ($userInfo['permissions']==',n,')
{
	if ($_REQUEST['action']=='delete') 
	{
		$_POST['submit_save']='save';
		$_POST['submit_action']='delete';
		$_POST['submit_mode']='single';	
		$_POST['rows'][$num]['columns']['id']=$_GET['id'];
		$_POST['rows'][$num]['action']='delete';
	}
}
if ($_POST['submit_delete']) 
{
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'delete';
}


if ($_REQUEST['action']=='accept') 
{
	$_POST['submit_save']='accept';
	$_POST['submit_action']='accept';
	$_POST['submit_mode']='single';	
	$_POST['rows'][$num]['columns']['id']=$_GET['id'];
	$_POST['rows'][$num]['action']='accept';
}

if ($_POST['submit_accept']) 
{
	$_POST['submit_save']='accept';
	$_POST['submit_action']='accept';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'accept';
}

if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='view')   {$actionTitle = wsfGetValue('view');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}
if ($_REQUEST['action']=='accept') {$actionTitle = wsfGetValue('accept');}


if (isset($_REQUEST['exportAsExcel']))
{
	$sqlQuery="SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName'];
	$rows=cmfcMySql::getRowsCustom($sqlQuery);

	$line = array(
		$translation->getValue('row'),
		//$translation->getValue('agencyName'),
		//$translation->getValue('sellingAgent'),
		$translation->getValue('receipantFullName'),
		$translation->getValue('receipantAddress'),
		$translation->getValue('receipantTel'),
		$translation->getValue('insertDatetime'),
		$translation->getValue('confirmed'),
	);
	
	$exportFileName = 'orders.xls';
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
			$agencyInfo = cmfcMysql::load($_cp['sectionInfo']['customersTable']['tableName'], $_cp['sectionInfo']['customersTable']['columns']['id'], $row['customerId']);
			$sellingAgentInfo = cmfcMysql::load($_cp['sectionInfo']['sellingAgentsTable']['tableName'], $_cp['sectionInfo']['sellingAgentsTable']['columns']['id'], $row['sellingAgent']);
			if($row['confirmed'])
				$confirmed = wsfGetValue('accepted');
			else
				$confirmed = wsfGetValue('notAccepted');
			$mineRow[] = $key+1;
			//$mineRow[] = $agencyInfo['full_name'];
			//$mineRow[] = $sellingAgentInfo['full_name'];
			$mineRow[] = $row['receipantFullName'];
			$mineRow[] = $row['receipantAddress'];
			$mineRow[] = $row['receipantTel'];
			$mineRow[] = wsfGetDateTime('d M Y (H:i:s)', $row['insertDatetime'], $translation->languageInfo['sName']);;
			$mineRow[] = $confirmed;
			
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

if ((isset($_POST['submit_save']) and $_GET['action']!='view' )) 
{
	foreach ($_POST['rows'] as $num=>$row)
	{
		$_columns=&$_POST['rows'][$num]['columns'];
		$_commonColumns = &$_POST['rows'][$num]['common'];
		$columnsPhysicalName=$sectionInfo['tableInfo']['columns'];
		if ($_POST['submit_mode']=='multi' and $row['selected']!='true')
			continue;
		if (empty($row['action'])) $row['action']=$_POST['submit_action'];
		if (!$_GET['id'])
			$_GET['id'] = $_columns['id'];
			
		if ($row['action']=='delete') 
		{
			if ($userInfo['permissions']==',n,')
			{
				$columnsValues=array();
				$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
				$result = cmfcMySql::delete(
					$_cp['sectionInfo']['tableInfo']['tableName'],
					$columnsPhysicalName['id'],
					$columnsValues['id'],
					NULL
				);
				if (PEAR::isError($result) or $result===false) 
				{
					$messages['errors'][] = 'error occured: '.$error;
					$isErrorOccured=true;
				} 
				else
				{
					$messages['messages'][] = 'اطلاعات نظرسنجی پاک شد.';
				}
				$result = cmfcMySql::delete(
					$_cp['sectionInfo']['surveyDetails']['tableName'],
					$_cp['sectionInfo']['surveyDetails']['columns']['surveyId'],
					$columnsValues['id'],
					NULL
				);
			}
			if (PEAR::isError($result) or $result===false) 
			{
				$messages['errors'][] = 'error occured: '.$error;
				$isErrorOccured=true;
			} 
			else
			{
				$messages['messages'][] = 'اطلاعات پاسخ های نظرسنجی پاک شد';
			}
		}
		else
		{
			if($row['action']=='update')
				$_columns['updateDatetime'] = date("Y-m-d H:i:s");
				
			if($row['action']=='insert')
				$_columns['insertDatetime'] = date("Y-m-d H:i:s");
			
			$_columns['languageId'] = $_POST['itemLanguage'];
			$_columns['relatedItem'] = $relatedItemId;
			
			$columnsValues=array();
			$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
			
			if (empty($validateResult)) 
			{
				#--(Begin)-->save changes to database
				if ($row['action']=='delete') 
				{
					$result=cmfcMySql::delete(
						$_cp['sectionInfo']['tableInfo']['tableName'],
						$columnsPhysicalName['id'],
						$columnsValues['id']
					);
					$error = cmfcMySql::error();
					$msg = wsfGetValue('removeMsg');
				}
				elseif ($row['action']=='insert') 
				{
					$surveyHash = md5($_commonColumns['receipantFullName'].$_commonColumns['receipantEmail'].$_columns['insertDatetime'].time());
					$_commonColumns['surveyHash'] = $surveyHash;
					
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
					if ($row['action']=='insert') 
					{
						$link = trim($_ws['siteInfo']['url'],'/').wsfPrepareUrl('?sn=survey&surveyKey='.$surveyHash);
						$emailHtml = cmfcMySql::loadWithMultiKeys(
							$_ws['physicalTables']['staticPages']['tableName'],
							array(
								  $_ws['physicalTables']['staticPages']['columns']['languageId']=>$translation->languageInfo['id'],
								  $_ws['physicalTables']['staticPages']['columns']['internalName']=>'surveyPage'
							)
						);
						if ($emailHtml)
							$emailHtml = $emailHtml['body'];
						if ($emailTemplate->loadByInternalName('survey')!==false)
						{
							$replacements=array(
								'%surveyLink%'=>$link,
								'%body%'=>$emailHtml,
								'%name%'=>$_commonColumns['receipantFullName'],
								'%email%'=>$_columns['email'],
								'%subject%'=>'A Survey From Neshgostar Pardis ['.date("Y-m-d H:i:s")."]",
								'%inline_subject%' => 'Order',
								'%site_url%' => $_ws['siteInfo']['url'],
								'%header%' => '',
							);
							$emailTemplate->process($replacements);
							
							$emailSender->addAddress($_commonColumns['receipantEmail']);
							
							$emailSender->Subject=$emailTemplate->getSubject();
							$emailSender->Body=$emailTemplate->getBody();
							
							$emailSender->FromName=$_ws['siteInfo']['titleEn'];
							$emailSender->From = $_ws['emailsInfo']['info'];
							
							$result = $emailSender->send();
						}
					}
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
		
		if (!(PEAR::isError($result) or $result===false))
		{
			$messages['messages'][]=sprintf(
				'<META http-equiv="refresh" content="1;URL=?%s">',
				wsfExcludeQueryStringVars(array('action', 'nextLang', 'itemLanguage', ),'get')
			);	
			$saved=true;
			cpfLog($_cp['sectionInfo']['name'], $userSystem->cvId, array('name'=>$row['action'],'rowId'=>$_GET['id']));
		}
		else
		{
			$messages['errors'][] = 'error occured: '.$error;
				$isErrorOccured=true;	
		}
	}
}

if (!$_POST['submit_save'])
{
	if ($editable)
	{
		//$_POST['rows'][$num]['columns'] = $editable;
	}
	if ($_REQUEST['action']=='view' or $_REQUEST['action']=='edit' or $_REQUEST['action']=='print') 
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
			foreach ($_cp['sectionInfo']['relatedTables'] as $key=>$table)
			{
				$temp=cmfcMySql::getRowsWithMultiKeys(
					$table['tableName'],
					array(
						$table['columns']['surveyId']=>$_POST['rows'][$num]['personalInfo']['id']
					)
				);
				if (!empty($temp))
				{
					if (is_array($temp))
					{
						foreach($temp as $k=>$t)
						{
							$_POST['rows'][$num][$key.'Info'][$k] = cmfcMySql::convertColumnNames($t, $table['columns']);
						}
					}
					else
					{
						$_POST['rows'][$num][$key.'Info'] = cmfcMySql::convertColumnNames($temp, $table['columns']);
					}
				}
			}
			// --
		}
		// --
		//cmfcHtml::printr();
	}
}

if ($_REQUEST['action']=='list') 
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
					$table['columns']['id'],
					array($table['columns'][$_GET['fn']]=>0),
					$targetItemId
				);
				$error = mysql_error();
			}
			else
			{
				$result = cmfcMySql::update(
					$table['tableName'],
					$table['columns']['id'],
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
	#--(Begin)-->generate Sql Query
	
	if (isset($_REQUEST['submit_search'])) 
	{
		if($_REQUEST['search']['confirmed'] != "")
		{
		
			$searchSqlWhereQuery .= " AND (
				`{td:confirmed}` = [confirmed]-1)";
		}
		
		if($_REQUEST['search']['receipantFullName']  != "")
		{
			
			$searchSqlWhereQuery .= " AND (
				`{td:receipantFullName}` LIKE '%[receipantFullName]%')";
		}
		
		if($_REQUEST['search']['receipantEmail']  != "")
		{
			
			$searchSqlWhereQuery .= " AND (
				`{td:receipantTel}` LIKE '%[receipantEmail]%')";
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
		
		
		$replacements=array(
			'{td:customerId}'=>$_cp['sectionInfo']['tableInfo']['columns']['customerId'],
			'{td:sellingAgent}'=>$_cp['sectionInfo']['tableInfo']['columns']['sellingAgent'],
			'{td:insertDatetime}'=>$_cp['sectionInfo']['tableInfo']['columns']['insertDatetime'],
			'{td:receipantFullName}'=>$_cp['sectionInfo']['tableInfo']['columns']['receipantFullName'],
			'{td:receipantAddress}'=>$_cp['sectionInfo']['tableInfo']['columns']['receipantAddress'],
			'{td:confirmed}'=>$_cp['sectionInfo']['tableInfo']['columns']['confirmed'],
			'{td:receipantEmail}'=>$_cp['sectionInfo']['tableInfo']['columns']['receipantEmail'],
			'[customerId]'=>$_REQUEST['search']['customerId'],
			'[sellingAgent]'=>$_REQUEST['search']['sellingAgent'],
			'[receipantFullName]'=>$_REQUEST['search']['receipantFullName'],
			'[startDate]'=>$_REQUEST['search']['startDate'],
			'[endDate]'=>$_REQUEST['search']['endDate'],
			'[receipantAddress]'=>$_REQUEST['search']['receipantAddress'],
			'[receipantTel]'=>$_REQUEST['search']['receipantTel'],
			'[confirmed]'=>$_REQUEST['search']['confirmed'],
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
	$infa->showFormHeader(null,'myForm',true);
	$infa->showTableHeader($actionTitle);
	$infa->showSeparatorRow( wsfGetValue('recipientInfo') );
	
	$infa->showInputRow(wsfGetValue('receipantFullName'), "rows[$num][common][receipantFullName]", $_POST['rows'][$num]['common']['receipantFullName'], '',40, $itemLanguage['direction']); 
	$infa->showInputRow(wsfGetValue('receipantEmail'), "rows[$num][common][receipantEmail]", $_POST['rows'][$num]['common']['receipantEmail'], '',40, $itemLanguage['direction']); 
	
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
	
	//$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	//if($languageItemCount > 1)
		//$infa->drawMultiTabs($options, $itemLanguage['id']);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	
	//$infa->showTextAreaRow(wsfGetValue('formBody'),"rows[$num][columns][body]", $_POST['rows'][$num]['columns']['body'],'', '', 15, '90%');
	
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
	
	/*
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
	*/
	$infa->showFormFooterCustom($buttons);
	//$infa -> prepareAddibleBoxesTempalte( $AddibleItems, $templates);

}

if (in_array($_REQUEST['action'],array('view'))) 
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
	
	if (!empty($fieldsValidationInfo)) 
	{
		$validation->printJsClass();
		$validation->printJsInstance();
	}
	
	$infa->showFormHeader(null,'myForm',true);
	$infa->showTableHeader($actionTitle);
	
	$infa->showSeparatorRow( wsfGetValue('cutomerInformation') );

	$infa->showCustomRow($translation->getValue("receipantFullName"),$_POST['rows'][$num]['personalInfo']['receipantFullName']);
	$infa->showCustomRow($translation->getValue("receipantEmail"),$_POST['rows'][$num]['personalInfo']['receipantEmail']);
	$infa->showCustomRow($translation->getValue("insertDatetime"),wsfGetDateTime('d M Y (H:i:s)', $_POST['rows'][$num]['personalInfo']['insertDatetime'], $translation->languageInfo['sName']));
	
	
	$iimg = ($_POST['rows'][$num]['personalInfo']['confirmed'])?'tick.png':'bullet_red.png';
	$html = '<img src="interface/images/'.$iimg.'" style="border:0px;" />';
                
	$infa->showCustomRow($translation->getValue("answered"),$html);
	$infa->showCustomRow($translation->getValue("answerDatetime"),wsfGetDateTime('d M Y (H:i:s)', $_POST['rows'][$num]['personalInfo']['updateDatetime'], $translation->languageInfo['sName']));
	
	if ($_POST['rows'][$num]['surveyDetailsInfo'])
	{
		$infa->showSeparatorRow( wsfGetValue('surveyDetailsInfo') );
		
		ob_start();
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
        	<tr>
                <th width="75%">سوالات</th>
                <?php 
				foreach ($_ws['virtualTables']['surveyOptions']['rows'] as $k=>$r)
				{
					?><th align="center" width="5%"><?php echo $r[$_ws['virtualTables']['surveyOptions']['columns']['name']]?></th><?php
				}
				?>
				
            </tr>
        </table>
        <?php
		$html = ob_get_contents();
		ob_clean();
		$infa->showCustomRow('ردیف',$html);
		foreach ($_POST['rows'][$num]['surveyDetailsInfo'] as $key=>$row)
		{
			if ($row['questionId'])
			{
				$tempQuery = 'SELECT * FROM '.$_cp['sectionInfo']['surveyQuestionsTable']['tableName'].
						" WHERE ".$_cp['sectionInfo']['surveyQuestionsTable']['columns']['relatedItem']." = ".$row['questionId'].
						" AND ".$_cp['sectionInfo']['surveyQuestionsTable']['columns']['languageId']." = ".$translation->languageInfo['id'];
				$productName = cmfcMySql::loadCustom($tempQuery);
				$productName = $productName['title'];
			}
			ob_start();
			?>
            <table width="100%">
                <tr>
                    <td width="75%"><?php echo $productName?></td>
                    <?php 
                    foreach ($_ws['virtualTables']['surveyOptions']['rows'] as $k=>$r)
                    {
                        if ($row['answer']==$r[$_ws['virtualTables']['surveyOptions']['columns']['id']])
                        {
                            ?><td align="center" width="5%"><img src="interface/images/tick.png" alt="" style="border:0px;" /></td><?php
                        }
                        else
                        {
                            ?><td width="5%">&nbsp;</td><?php
                        }
                    }
                    ?>
                </tr>
            </table>
			<?php
			$html = ob_get_contents();
			ob_clean();
			$infa->showCustomRow($key+1,$html);
		}
	}
	
	/* */
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
			/*array(
				'name' => 'submit_save',
				'value' => wsfGetValue('buttonSubmit'),
			),
			array(
				'name' => 'submit_accept',
				'value' => wsfGetValue('buttonAccept'),
				'attributes'=> array(
					'onclick' => "window.location.href = '?sn=".$_GET['sn']."&action=accept&id=".$_REQUEST['id']."'; return false;"
				)
			),*/
			array(
				'name' => 'print',
				'value' => wsfGetValue('buttonPrint'),
				'attributes'=> array(
					'onclick' => "cmfPopitup('print.php?sn=".$_GET['sn']."&action=print&id=".$_REQUEST['id']."&print=1'); return false;"
				)
			),
			array(
				'name' => 'cancel',
				'value' => wsfGetValue('buttonCancel'),
			),
			
		);
	}
	else
	{
		$buttons = NULL;	
	}
	
	
	$infa->showFormFooterCustom($buttons);
}
elseif (in_array($_REQUEST['action'],array('edit')) and $saved!=true)
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
	
	if (!empty($fieldsValidationInfo)) 
	{
		$validation->printJsClass();
		$validation->printJsInstance();
	}
	
	$infa->showFormHeader(null,'myForm',true);
	$infa->showTableHeader($actionTitle);
	
	$infa->showSeparatorRow( wsfGetValue('cutomerInformation') );

	$infa->showInputRow(wsfGetValue('receipantFullName'), "rows[$num][personalInfo][receipantFullName]", $_POST['rows'][$num]['personalInfo']['receipantFullName'], '',40, $itemLanguage['direction']); 
	$infa->showInputRow(wsfGetValue('receipantAddress'), "rows[$num][personalInfo][receipantAddress]", $_POST['rows'][$num]['personalInfo']['receipantAddress'], '',40, $itemLanguage['direction']); 
	$infa->showInputRow(wsfGetValue('receipantTel'), "rows[$num][personalInfo][receipantTel]", $_POST['rows'][$num]['personalInfo']['receipantTel'], '',40, 'ltr'); 	
	
	$infa->showCustomRow($translation->getValue("insertDatetime"),wsfGetDateTime('d M Y (H:i:s)', $_POST['rows'][$num]['personalInfo']['insertDatetime'], $translation->languageInfo['sName']));
	
	$infa->showSeparatorRow(wsfGetValue('requests'));
	//cmfcHtml::printr($_POST['rows'][$num]['orderDetailsInfo']);
	if ($_POST['rows'][$num]['orderDetailsInfo'])
	{
		
		foreach ($_POST['rows'][$num]['orderDetailsInfo'] as $key=>$row)
		{
			if ($row['productId'])
			{
				$tempQuery = 'SELECT * FROM '.$_cp['sectionInfo']['productsTable']['tableName'].
						" WHERE ".$_cp['sectionInfo']['productsTable']['columns']['relatedItem']." = ".$row['productId'].
						" AND ".$_cp['sectionInfo']['productsTable']['columns']['languageId']." = ".$translation->languageInfo['id'];
				$productName = cmfcMySql::loadCustom($tempQuery);
				$productName = $productName['title'];
			}
			$infa->showCustomRow($translation->getValue("product"),$productName);
			$infa->showCustomRow($translation->getValue("orderDescription"),$row['orderDescription']);
		}
	}
	
	/* */
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
				'name' => 'submit_save',
				'value' => wsfGetValue('buttonSubmit'),
			),
			array(
				'name' => 'cancel',
				'value' => wsfGetValue('buttonCancel'),
			),
			array(
				'name' => 'print',
				'value' => wsfGetValue('buttonPrint'),
				'attributes'=> array(
					'onclick' => "cmfPopitup('print.php?sn=".$_GET['sn']."&action=print&id=".$_REQUEST['id']."&print=1'); return false;"
				)
			),
		);
	}
	else
	{
		$buttons = NULL;	
	}
	
	
	$infa->showFormFooterCustom($buttons);
}
elseif( $_REQUEST['action'] == 'print' && isset( $_GET['id']))
{
	$iimg = ($_POST['rows'][$num]['personalInfo']['confirmed'])?'tick.png':'bullet_red.png';
	$html = '<img src="interface/images/'.$iimg.'" style="border:0px;" />';
	?>
	<table style='width:100%;'>
        <tr>
            <th><?php echo wsfGetValue('receipantFullName')?></th>
            <td>
                <?php echo $_POST['rows'][$num]['personalInfo']['receipantFullName']?>
            </td>
        </tr>
        <tr>
            <th><?php echo wsfGetValue('receipantEmail')?></th>
            <td>
                <?php echo $_POST['rows'][$num]['personalInfo']['receipantEmail']?>
            </td>
        </tr>
        <tr>
            <th><?php echo wsfGetValue('insertDatetime')?></th>
            <td>
                <?php echo wsfGetDateTime('d M Y (H:i:s)', $_POST['rows'][$num]['personalInfo']['insertDatetime'], $translation->languageInfo['sName']);?>
            </td>
        </tr>
        
        <tr>
            <th><?php echo wsfGetValue('answeres')?></th>
            <td>
                <?php echo $html?>
            </td>
        </tr>
        <?php 		if ($_POST['rows'][$num]['surveyDetailsInfo'])
		{
			foreach ($_POST['rows'][$num]['surveyDetailsInfo'] as $key=>$row)
			{
				$row = cmfcMySql::convertColumnNames($row,$sectionInfo['surveyDetailsTable']['columns']);
				if ($row['questionId'])
				{
					$tempQuery = 'SELECT * FROM '.$_cp['sectionInfo']['surveyQuestionsTable']['tableName'].
							" WHERE ".$_cp['sectionInfo']['surveyQuestionsTable']['columns']['relatedItem']." = ".$row['questionId'].
							" AND ".$_cp['sectionInfo']['surveyQuestionsTable']['columns']['languageId']." = ".$translation->languageInfo['id'];
					$productName = cmfcMySql::loadCustom($tempQuery);
					$productName = $productName['title'];
				}
				?>
                <tr>
                    <th><?php echo wsfGetValue('product')?></th>
                    <td>
                	    <?php echo $productName?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo wsfGetValue('orderDescription')?></th>
                    <td>
                	    <?php echo $row['orderDescription']?>
                    </td>
                </tr>
				<?php 			}	
		}
		?></table><?php
}
elseif( $_REQUEST['action'] == 'printSelected' && is_array( $_POST['selected']))//Print All Factors...
{

	foreach( $_POST['selected'] as $ordrId)
	{
		$order = cmfcMySql::load($sectionInfo['tableInfo']['tableName'], $sectionInfo['tableInfo']['columns']['id'], $ordrId);
		$order = cmfcMySql::convertColumnNames($order, $sectionInfo['tableInfo']['columns']);
		$orderDetails = cmfcMysql::getRowsWithMultiKeys(
								$sectionInfo['surveyDetailsTable']['tableName'],
								array(
									$sectionInfo['surveyDetailsTable']['columns']['relatedOrder']=>$order['id']
								)
							);
			$agencyInfo = cmfcMySql::load($sectionInfo['customersTable']['tableName'], $sectionInfo['customersTable']['columns']['id'], $order['customerId']);
			$sellingAgent = cmfcMySql::load($sectionInfo['sellingAgentsTable']['tableName'], $sectionInfo['sellingAgentsTable']['columns']['id'], $order['sellingAgent']);
			if($order['confirmed'] == 1)
				$confirmed = wsfGetValue('accepted');
			else
				$confirmed = wsfGetValue('notAccepted');
			?>
			<table style='width:100%;' class='maintable'>
					<tr>
						<th><?php echo wsfGetValue('receipantFullName')?></th>
						<td>
							<?php echo $order['receipantFullName']?>
						</td>
					</tr>
					<tr>
						<th><?php echo wsfGetValue('receipantAddress')?></th>
						<td>
							<?php echo $order['receipantAddress']?>
						</td>
					</tr>
					<tr>
						<th><?php echo wsfGetValue('receipantTel')?></th>
						<td>
							<?php echo $order['receipantTel']?>
						</td>
					</tr>
					<tr>
						<th><?php echo wsfGetValue('insertDatetime')?></th>
						<td>
							<?php echo wsfGetDateTime('d M Y (H:i:s)', $order['insertDatetime'], $translation->languageInfo['sName']);?>
						</td>
					</tr>
					<tr>
						<th><?php echo wsfGetValue('orderStatus')?></th>
						<td>
							<?php echo $confirmed?>
						</td>
					</tr>
			<?php 			if($orderDetails){
				foreach ($orderDetails as $key=>$row)
				{
					$row = cmfcMySql::convertColumnNames($row,$sectionInfo['surveyDetailsTable']['columns']);
					if ($row['productId'])
					{
						$tempQuery = 'SELECT * FROM '.$_cp['sectionInfo']['productsTable']['tableName'].
								" WHERE ".$_cp['sectionInfo']['productsTable']['columns']['relatedItem']." = ".$row['productId'].
								" AND ".$_cp['sectionInfo']['productsTable']['columns']['languageId']." = ".$translation->languageInfo['id'];
						$productName = cmfcMySql::loadCustom($tempQuery);
						$productName = $productName['title'];
					}
					?>
                    <tr>
                        <th><?php echo wsfGetValue('product')?></th>
                        <td>
                            <?php echo $productName?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo wsfGetValue('orderDescription')?></th>
                        <td>
                            <?php echo $row['orderDescription']?>
                        </td>
                    </tr>
				<?php 				}	
			}
		
		?></table>
			<br style="clear:both;" />

		<?php
	
	}

}
elseif ($_REQUEST['action']=='list')
{
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;" dir="<?php echo $langInfo['htmlDir']?>" > 
			<tr>
			    <td  align="<?php echo $langInfo['htmlNAlign']?>" >
                    <table border="0" cellpadding="0" cellspacing="1" class="option-link"  align="<?php echo $langInfo['htmlNAlign']?>"  >
                        <tr>
                            <td class="quick-search-button"  align="<?php echo $langInfo['htmlNAlign']?>" ><a href="javascript:void(0);" onclick="<?php echo $js->jsfToggleDisplayStyle('searchBoxContainer','auto')?>"><?php echo wsfGetValue('advancedSearch');?></a></td>
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
				<td  class="option-table-buttons-spacer" width="5" align="<?php echo $langInfo['htmlAlign']?>" >&nbsp;</td>
				<td  align="<?php echo $langInfo['htmlAlign']?>" id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo wsfGetValue('advancedSearch')?>
				</td>
				<td class="option-table-buttons-spacer" align="<?php echo $langInfo['htmlAlign']?>" >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
            <tr class="table-row" align="<?php echo $langInfo['htmlAlign']?>" >
				<td width="200"><?php echo wsfGetValue('surveyStatus')?> </td>
				<td align="<?php echo $langInfo['htmlAlign']?>" >
                <?php 					$items = array(
								array(
									'id'=>1,
									'title'=>wsfGetValue('notAnswered')
								),
								array(
									'id'=>2,
									'title'=>wsfGetValue('answered')
								)
							);
					echo cmfcHtml::drawDropDown(
						"search[confirmed]",
						$_REQUEST['search']['confirmed'],
						$items,
						'id',
						'title',
						NULL,
						NULL,
						'',
						'------'
					);
				?>
                </td>
			</tr>
            <tr class="table-row2" >
				<td align="<?php echo $langInfo['htmlAlign']?>" ><?php echo wsfGetValue('receipantFullName')?></td>
				<td  align="<?php echo $langInfo['htmlAlign']?>" ><input name="search[receipantFullName]" class="input" type="text" value="<?php echo $_REQUEST['search']['receipantFullName']?>" style="width:50%" /></td>
			</tr>
            <tr class="table-row1" >
				<td align="<?php echo $langInfo['htmlAlign']?>" ><?php echo wsfGetValue('receipantEmail')?></td>
				<td  align="<?php echo $langInfo['htmlAlign']?>" ><input name="search[receipantEmail]" class="input" type="text" value="<?php echo $_REQUEST['search']['receipantEmail']?>" style="width:50%" /></td>
			</tr>
            <tr class="table-row1" >
				<td align="<?php echo $translation->languageInfo['align']?>" ><?php echo wsfGetValue('insertDatetime')?></td>
				<td  align="<?php echo $translation->languageInfo['align']?>" >
					از <span dir='ltr'>
					<?php 					$currentYear = wsfGetDateTime('Y', 'now', $langInfo['sName'], false);
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
		<table id="listFormTable" dir=<?php echo $langInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" style="border-color: #d4dce7" >
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
				<td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" style="width:55px">
					<?php echo wsfGetValue(tools)?>
				</td>
				
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('receipant_full_name','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('receipant_full_name','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('receipantFullName')?>
				</td>
                
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
					<?php echo wsfGetValue('receipantEmail')?>
                </td>
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title">
                	<a href="<?php echo $paging->getSortingUrl('insert_datetime','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('insert_datetime','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo wsfGetValue('insertDatetime')?>
                </td>
                <td align="<?php echo $langInfo['htmlAlign']?>" nowrap="nowrap" class="table-title field-title" >
					<?php echo wsfGetValue('answered')?>
				</td>
			</tr>
			<?php 			foreach ($rows as $key=>$row) {
				$num=$key+1;
					
				$row = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
				
				
				if ($row['customerId'])
				{
					$tempQuery = 'SELECT * FROM '.$_cp['sectionInfo']['customersTable']['tableName'].
							" WHERE ".$_cp['sectionInfo']['customersTable']['columns']['id']." = ".$row['customerId'];
					$agencyName = cmfcMySql::loadCustom($tempQuery);
					$agencyName = $agencyName['full_name'];
				}
				
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
					<a href="?<?php echo $actionsBaseUrl?>&action=view&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_list.png" width="16" border="0" alt="view" title="view" />
					</a>
                    <?php 
					
					if ($userInfo['permissions']==',n,')
					{
						?>
						<a onclick="return <?php echo $js->jsfConfimationMessage( wsfGetValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&action=delete&id=<?php echo $row['id']?>">
							<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
						</a>
						<?php
					}
					?>
				</td>
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['receipantFullName'];?>
				</td>
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" > 
					<?php echo $row['receipantEmail'];?>
				</td>
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" >
	                <?php echo wsfGetDateTime('d M Y (H:i:s)', $row['insertDatetime'], $translation->languageInfo['sName']);?>
				</td>
                <td class="field-title" align="<?php echo $langInfo['htmlAlign']?>" id="confirmedFlag<?php echo $row['id']?>"> 
                    <?php $iimg = ($row['confirmed'])?'tick.png':'bullet_red.png';?>                    
                    <img src="interface/images/<?php echo $iimg;?>" id="confirmed<?php echo $row['id']?>" alt="<?php echo $translation->getValue('confirmed')?>" style="border:0px;" />
                </td>
			</tr>
			<?php }?>
	  </table>                                                                                        
		
		
		<div style="text-align:center">
        	<?php
			if ($userInfo['permissions']==',n,')
			{?>
                <input name="submit_delete" class="button" type="submit" value=" <?php echo wsfGetValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage(wsfGetValue(areYouSure))?>" />
                <?php
			}
			?>
            <input name="submit_accept" class="button" type="submit" value=" <?php echo wsfGetValue(buttonAccept) ?> " />
			<?php /*?><input name="submit_insert" class="button" type="button" value="<?php echo wsfGetValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" /><?php */?>
            <input name="exportAsExcel" class="submit" type="button" value="خروجی Excel" onclick="cmfPopitup('popup.php?sn=<?php echo $_GET['sn']?>&exportAsExcel=1'); return false;"/>
		</div>
        <div class="prnBtns">

			<?php /*?>[ <a href="print.php?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName', 'pageType', 'pt', 'action', 'action'=>'print'), 'get')?>" target="_blank">

				<?php echo wsfGetValue('printList') ?>
			</a> ]<?php */?>
		
			<script>
				function printAll(F)
				{
					F.target='_blank';
					var ac=F.action;
					F.action='print.php?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName','action'),'get')?>&action=printSelected';
					F.submit();
					//Back to normal
					F.target='';
					F.action=ac;
					return false;
				}
			</script>
			<input type="button" value="   <?php print( wsfGetValue( 'printSelectedOrdersFactor'));?>   " name="submit_print_orders" onclick="return printAll(this.form);" />

		</div>
	<?php 	}
	else { 
		?>
		<b><?php echo  wsfGetValue('nothingFound')?></b>
		<?php /*?><br />
		<input name="submit_insert" class="button" type="submit" value="<?php echo wsfGetValue(buttonNew) ?>"  /><?php */?>
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