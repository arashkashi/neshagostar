<?php
$infa=new cpInterface();

if(isset($_POST['relatedItemId']) )
	$relatedItemId = $_POST['relatedItemId'];

if ($_GET['nextLang'] && ! $_POST['itemLanguage'] )
	$_POST['itemLanguage'] = $_GET['nextLang'];

if($_GET['sn'] )
{
	$related = cmfcMySql::load(
		$_cp['sectionInfo']['tableInfo']['tableName'],
		$_cp['sectionInfo']['tableInfo']['columns']['internalName'],
		$_GET['sn']
	);
	$related = cmfcMySql::convertColumnNames($related, $_cp['sectionInfo']['tableInfo']['columns']);
	$relatedItemId = $related['relatedItem'];
	
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

if ($related)
	$relatedItemId = $related['relatedItem'];

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

$_REQUEST['action']='edit';

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
		if ($_columns['id'])
			$_GET['id'] = $_columns['id'];
		
		#--(End)-->prepare for multi action
		
		if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename'] and $_GET['sn']!='surveyPage')
		{
			$_commonColumns['photoFilename'] = wsfUploadFileAuto(
				"rows[$num][common][photoFilename]",
				$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
			);
		}
		
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
					$_GET['id']
				);
				$error = cmfcMySql::error();
				$msg = $translation->getValue('removeMsg');
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
				$msg = $translation->getValue('updateMsg');
			}
			elseif ($row['action']=='insert') 
			{
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
		
		$replacements=array(
			'{td:title}'=>$_cp['sectionInfo']['tableInfo']['columns']['title'],
			'{td:body}'=>$_cp['sectionInfo']['tableInfo']['columns']['body'],
			'{td:specific}'=>$_cp['sectionInfo']['tableInfo']['columns']['siteSpecific'],
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
	$oldSortBy=$_REQUEST['sortBy'];
	@$_REQUEST['sortBy']=$_cp['sectionInfo']['tableInfo']['columns'][$_REQUEST['sortBy']];
	
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
		'sortType'=>'DESC',
		'colnId'=>$_cp['sectionInfo']['tableInfo']['columns']['id'],
	));
	
	//echo $sqlQuery;
	$sqlQuery=$paging->getPreparedSqlQuery();
	$_REQUEST['sortBy']=$oldSortBy;
	#--(End)-->Paging
	
	//echo $sqlQuery."<br>";
	
	#--(Begin)-->Execute Query and fetch the rows
	//echo $sqlQuery;
	$rows=cmfcMySql::getRowsCustom($sqlQuery);
	echo mysql_error();
	#--(End)-->Execute Query and fetch the rows
}


cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

wsfPrintMessages($messages);

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
	//cmfcHtml::printr($_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']);
	wsfWysiwygLoader(array(
		//'templateName'=>'articleWriting',
		'templateName'=>'fullWidthFileAndImageManager',
		'imagesUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'imagesDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'baseUrl'=>$_ws['siteInfo']['url'].$_cp['sectionInfo']['folderRelative'],
		'baseDir'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
		'editors'=>array(
			"rows[$num][columns][body]"=>array('direction'=>$itemLanguage['direction']),			
		)
	));
	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showHiddenInput("itemLanguage", $itemLanguage['id']);
	$infa->showHiddenInput("relatedItemId", $relatedItemId);
	$infa->showHiddenInput("rows[$num][columns][internalName]", $_REQUEST['sn']);
	
	if ($_REQUEST['action'] == 'edit') 
	{
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", $_POST['rows'][$num]['columns']['id']);
	}
	elseif($_REQUEST['action'] == 'new') 
	{
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	
	$infa->showTableHeader($actionTitle);
	
	if ($_GET['sn']!='surveyPage')
		$infa->showSeparatorRow( $translation->getValue('commonInfo') );

	if ($_cp['sectionInfo']['tableInfo']['columns']['photoFilename'] and $_GET['sn']!='surveyPage')
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

	$items = $translation->getAllLanguages();
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
	
	
	$infa->showInputRow(wsfGetValue('title'), "rows[$num][columns][title]", $_POST['rows'][$num]['columns']['title'], '',40, $itemLanguage['direction']); 
	
	$infa->showTextAreaRow($translation->getValue('formBody'),"rows[$num][columns][body]", $_POST['rows'][$num]['columns']['body'],'', '', 15, '90%');
	
	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", $_POST['rows'][$num]['columns']['id']);
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
elseif ($_REQUEST['action']=='list')
{
?>
	<form name="mySearchForm" action="?" method="get" style="margin:0px;padding:0px" enctype="text/plain">
		<?php echo cmfcUrl::quesryStringToHiddenFields( wsfExcludeQueryStringVars(array('sectionName','from','to','search','submit_search','submit_cancel_search', 'pageType', 'viewLangId', 'id', 'action'),'get') )?>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;"> 
			<tr>
				<td  ></td>
				<td width="120" >
					<table class="option-link" border="0" cellspacing="1" cellpadding="0" >
						<tr>
							<td class="quick-search-button"  nowrap="nowrap">
							<?php echo  $translation->getValue(select_language)?>&nbsp;
							
							<?php echo cmfcHtml::drawDropDown(
								'viewLangId', 
								$_REQUEST['viewLangId'],
								$translation->getAllLanguages(),
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
			    <td  align="<?php echo $translation->languageInfo['!align']?>" >
				
				<table border="0" cellpadding="0" cellspacing="1" class="option-link"  align="<?php echo $translation->languageInfo['htmlNAlign']?>"  >
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
		" dir="<?php echo $translation->languageInfo['htmlDir']?>">
		<table id="option-buttons" width="100%" border="0" cellspacing="0" cellpadding="0"  dir="<?php echo $translation->languageInfo['htmlDir']?>" > 
			<tr>
				<td  class="option-table-buttons-spacer" width="5"  >&nbsp;</td>
				<td   id="option-button-1" class="option-table-buttons" width="100" style="width=70px" onmouseover="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons-hover';}" onmouseout="if(this.className!='option-table-buttons-select'){this.className='option-table-buttons';}" onclick="option(1);">
				 <?php echo $translation->getValue(advancedSearch)?>
				</td>
				<td class="option-table-buttons-spacer"  >&nbsp;</td>
			</tr>
		</table>
		<table id="option-1" class="option-table" width="100%" border="0" cellspacing="1" cellpadding="0">
			
			<tr class="table-row1"  >
				<td width="200"><?php echo $translation->getValue('title')?> </td>
				<td  ><input name="search[title]" class="input" type="text" value="<?php echo $_REQUEST['search']['title']?>" style="width:50%" /></td>
			</tr>
			<tr class="table-row2" >
				<td  ><?php echo $translation->getValue('formBody')?></td>
				<td   ><input name="search[body]" class="input" type="text" value="<?php echo $_REQUEST['search']['body']?>" style="width:50%" /></td>
			</tr>
			
			<tr class="table-row2">
				<td colspan="2" >
					<input class="button" type="submit" name="submit_search" value="<?php echo $translation->getValue(search)?>" />
					<input class="button" type="button" name="submit_cancel_search" value="<?php echo $translation->getValue(cancel)?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&lang=<?php echo $_GET['lang']?>'" />
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
		<table id="listFormTable" dir=<?php echo $translation->languageInfo['htmlDir']?>  class="table" width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td colspan="10" class="table-header"  > <?php echo $actionTitle ?>  </td>
			</tr>
			<tr>
				<td class="table-title field-title" style="width:30px" >
					#
				</td>
				<td class="table-title field-checkbox" width="26">
					<input class="checkbox" name="checkall" type="checkbox" value="" onclick="cpfToggleCheckBoxes(this,'listFormTable')" />
				</td>
				<td  nowrap="nowrap" class="table-title field-title" style="width:35px">
					<?php echo $translation->getValue(tools)?>
				</td>
				
				<td  nowrap="nowrap" class="table-title field-title">
					<a href="<?php echo $paging->getSortingUrl('key','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('key','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo $translation->getValue(title)?>
				</td>
				<td  nowrap="nowrap" class="table-title field-title" >
					<a href="<?php echo $paging->getSortingUrl('value','DESC')?>">
						<span style="font-family:arial">▼</span>
					</a>
					<a href="<?php echo $paging->getSortingUrl('value','ASC')?>">
						<span style="font-family:arial">▲</span>
					</a>
					<?php echo $translation->getValue(formBody)?>
				</td>
				<td  nowrap="nowrap" class="table-title field-title" >
					<?php echo $translation->getValue('category')?>
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
				<td class="field-title"  >
					<?php echo ($paging->getPageNumber()-1)*$listLimit + $num?>.
					<input name="rows[<?php echo $num?>][columns][id]" type="hidden" value="<?php echo $row['id']?>" />
				</td>
				<td class="field-checkbox"  >
					<input name="rows[<?php echo $num?>][selected]" type="checkbox" value="true" />
				</td>
				<td class="field-title"  >
					<a href="?<?php echo $actionsBaseUrl?>&amp;action=edit&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_edit.png" width="16" border="0" alt="edit" title="edit" />
					</a>
					 
					<a onclick="return <?php echo $js->jsfConfimationMessage( $translation->getValue('areYouSure') )?>" href="?<?php echo $actionsBaseUrl?>&amp;action=delete&amp;id=<?php echo $row['id']?>">
						<img src="interface/images/action_delete.png" width="16" border="0" alt="delete" title="delete" />
					</a>
				</td>
				
				<td class="field-title"  > 
					<?php echo $row['title'];?>
				</td>
				<td class="field-title"  > 
					<?php echo cmfcString::briefText(strip_tags($row['body']), 100);?>
				</td>
				<td class="field-title"  > 
					<?php 					$myTree->setTableName($_cp['sectionInfo']['categoriesTable']['tableName']);
					$myTree->setColumnsNames(array('id','name','name_en','visible','left_visible','link','link_en'));
					$myTree->setTitleColumnName('name');
					echo wsfGetCategoryPathByLang(
						$treeTableDB['tableName'],
						$row['categoryId']
					)?>
				</td>
			</tr>
			<?php }?>
	  </table>
		
		
		<div style="text-align:center">
			<input name="submit_delete" class="button" type="submit" value=" <?php echo $translation->getValue(buttonDel) ?> " onclick="return <?php echo $js->jsfConfimationMessage($translation->getValue(areYouSure))?>" />
			<input name="submit_insert" class="button" type="button" value="<?php echo $translation->getValue(buttonNew) ?>" onclick="window.location='?sn=<?php echo $_GET['sectionName']?>&action=new'" />
		</div>
	<?php 	}
	else 
	{ 
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
		<td  >
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td class="paging-body" >
						<?php echo  $translation->getValue(page)?> <?php echo $paging->getPageNumber()?> <?php echo  $translation->getValue(from)?> <?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		</td>
		<td >
			<table border="0" align="<?php echo $translation->languageInfo['htmlNAlign']?>" cellpadding="0" cellspacing="1" class="paging-nav">
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
//cmfcHtml::printr(cmfcMySql::getRegisteredQueries() );
?>