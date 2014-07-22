<?php
$infa=new cpInterface();

if(isset($_POST['relatedItemId']))
{
	$_REQUEST['id'] = $relatedItemId = $_POST['relatedItemId'];
}

if ($_GET['nextLang'] && ! $_POST['itemLanguage'] )
	$_POST['itemLanguage'] = $_GET['nextLang'];
	
if (!$_POST['itemLanguage'])
	$_POST['itemLanguage']=$translation->languageInfo['id'];
	

if($_REQUEST['id'])
{
	$related = cmfcMySql::loadWithMultiKeys(
		$_cp['sectionInfo']['tableInfo']['tableName'],
		array(
			  	$_cp['sectionInfo']['tableInfo']['columns']['categoryId']=>	$_REQUEST['id'],
				$_cp['sectionInfo']['tableInfo']['columns']['languageId']=> $_POST['itemLanguage']	,
		)
	);
	if ($related )
	{
		$related = cmfcMySql::convertColumnNames($related, $_cp['sectionInfo']['tableInfo']['columns']);
		$relatedItemId = $related['categoryId'];
	}
	else
	{
			
	}
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
		$_cp['sectionInfo']['tableInfo']['columns']["categoryId"] => $relatedItemId
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

if (!$_REQUEST['action']) $_REQUEST['action']='edit';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}

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
			$_columns['insertDatetime'] = date("Y-m-d H:i:s");
		}
        
		$_columns['languageId'] = $_POST['itemLanguage'];
		$_columns['categoryId'] = $relatedItemId;

		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename']){
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
						$sectionInfo['tableInfo']['columns']['categoryId'] => $_GET['id']
					);
					
					cmfcMySql::update(
						$sectionInfo['tableInfo']['tableName'],
						$sectionInfo['tableInfo']['columns']['id'],
						$updateColumnsValues,
						$_GET['id']
					);
					
					$_columns['categoryId'] = $relatedItemId = $_GET['id'];
				}
			}
			
			wsfSaveOverrideValues($_commonColumns, $relatedItemId);
			#--(End)-->save changes to database
			
			if (PEAR::isError($result) or $result===false) 
			{
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
		if (!$isErrorOccured) 
		{
			#--(Begin)-->redirect to previous url if everthings is ok
			if(isset($_POST['saveEditNextLang']))
			{
				$nextLang = cpfGetNextEditableLang($itemLanguage['id']);
				$messages['messages'][]=sprintf(
					'<META http-equiv="refresh" content="1;URL=?%s">',
					wsfExcludeQueryStringVars(array('pageType','sectionName','pt', 'nextLang','lang'),'get')."nextLang=".$nextLang."&id=".$_REQUEST['relatedItemId']
				);
			}
			else
			{
				?>
				<script language="javascript">
                    itemChangedHtmlElm = parent.parent.document.getElementById('<?php echo $_REQUEST['baseName']?>[changed]');
                    <?php                     if ($_GET['lang'] == $translation->languageInfo['sName'])
                    {
                        ?>
                        if ( parent.parent.document.getElementById('<?php echo $_REQUEST['baseName']?>[columns][name]') )
                            parent.parent.document.getElementById('<?php echo $_REQUEST['baseName']?>[columns][name]').value="<?php echo $_columns['name']?>";
                        if ( parent.parent.document.getElementById('<?php echo $_REQUEST['baseName']?>[title]') )
                            parent.parent.document.getElementById('<?php echo $_REQUEST['baseName']?>[title]').innerHTML ="<?php echo $_columns['name']?>";
                        <?php                     }
                    else
                    {
                        ?>
                        if ( itemChangedHtmlElm ){
                            if (itemChangedHtmlElm .value == 0){
                                itemNameElm = parent.parent.document.getElementById('<?php echo $_REQUEST['baseName']?>[columns][name]');
                                if ( itemNameElm )
                                    itemNameElm .value = "<?php echo $_columns['name']?>";
                            }
                        }
                        <?php                     }
                    ?>
                    itemChangedHtmlElm.value ="1";
                </script>
				<script language="javascript">
				parent.parent.GB_hide();
				</script>
                <?php
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
		if($itemLanguage['id'] ){
			$sqlQuery = "SELECT * FROM ".$_cp['sectionInfo']['tableInfo']['tableName']." WHERE language_id = '".$itemLanguage['id']."' AND category_id = '".$relatedItemId."'";
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
		
		if (!empty($row)) {
			$_POST['rows'][$num]['columns'] = cmfcMySql::convertColumnNames($row, $_cp['sectionInfo']['tableInfo']['columns']);
		}
		else{
			$_REQUEST['action'] = "new";
		}
	}
}

if (is_array($messages)) 
{
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
	//cmfcHtml::printr($relatedItemId);
	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);
	
	$infa->showSeparatorRow( wsfGetValue('commonInfo') );
	
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
	
	/* */
	//$infa->showSeparatorRow(wsfGetValue('mainInfo'));
	
	if($languageItemCount > 1)
		$infa->drawMultiTabs($options, $itemLanguage['id']);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	$infa->showInputRow(wsfGetValue('title'), "rows[$num][columns][name]", $_POST['rows'][$num]['columns']['name'], '',40, $itemLanguage['direction']);
	
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
?>