<?php
/*
**	 ################################################
**	##	Copyright © 29/Avr/2008 Persian Tools		##
**	##	Package : Pt Pack							##
**	##	Version : 2.0								##
**	##	File Name : settings.section.inc.php		##
**	##	Author : Yasser Akhoondi (YAAK)				##
**	##	akhoondi@gmail.com							##
**	 ################################################
*/

$infa=new cpInterface();


if (!$_REQUEST['action']) $_REQUEST['action']='list';

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}


$messages=array();

if (isset($_POST['submit_save']) and $_GET['action']!='view') 
{
	foreach ($_POST['rows'] as $num=>$row)
	{
		$_columns=&$_POST['rows'][$num]['columns'];

		switch ($_columns['inputType'])
		{
			case 'image':
				$_columns['value'] = wsfUploadFileAuto(
					"rows[$num][columns][value]",
					$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative']
				);
			break;
			
			case 'checkbox':
				if (!isset($_columns['value'])) 
					$_columns['value'] = 0;
				else
					$_columns['value'] = 1;
			break;
			
			case 'text':
				
			break;
			
			case 'textarea':
			
			break;
		}
		$columnsValues=array();
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $_cp['sectionInfo']['tableInfo']['columns']);
		
		$result=cmfcMySql::update(
			$_cp['sectionInfo']['tableInfo']['tableName'],
			$_cp['sectionInfo']['tableInfo']['columns']['id'],
			$columnsValues,
			$num
		);

		$msg = $translation->getValue('updateMsg');

		if (empty($validateResult)) 
		{
			if (PEAR::isError($result) or $result===false) 
			{
				$messages['errors'][] = $result->getMessage();
				$messages['errors'][] = cmfcMySql::error();
				$isErrorOccured=true;
			} 
			else 
			{
				$messages['messages'][] = $msg;
				//cpfLog($_cp['sectionInfo']['name'], $userSystem->cvId, array('name'=>$row['action'],'rowId'=>$_GET['id']));
			}
		} 
		else 
		{
			foreach ($validateResult as $r) 
					$messages['errors'][]=$r->getMessage();
			$isErrorOccured=true;
		}	
		
		$actionTaken = true;
	};

	if (!PEAR::isError($result)) 
	{
		if (!$isErrorOccured && $actionTaken) 
		{
			$messages['messages'][]=sprintf(
			'<META http-equiv="refresh" content="1;URL=?%s">',
			cmfcUrl::excludeQueryStringVars(array('action', 'sectionName', 'pageType'),'get').'&action='.$nextAction.'&nextAction='.$secondAction
			);
			$saved=true;
		}			
			
		if (! $actionTaken)
		{
			$messages['errors'][] = 'No action took place, mainly because you forgot to select a row to act on!';
		}	
	}
}
	
cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

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
		<div class="<?php echo $class?>" dir="<?php echo $translation->languageInfo['direction']?>" align="<?php echo $langInfo['htmlAlign']?>" >
			<?php echo implode('<br />',$messageList)?>
		</div>
		<?
	}
}

if ($_REQUEST['action']=='list') 
{ 
	$rows = cmfcMySql::getRowsWithMultiKeys(
		$_cp['sectionInfo']['tableInfo']['tableName'],
		array(
			$_cp['sectionInfo']['tableInfo']['columns']['section']=>$_cp['sectionInfo']['name']
		)
	);
	echo mysql_error();
	
	if (is_array($rows)) 
	{
		$infa->showFormHeader(null,'myForm',true);
		$infa->showTableHeader($actionTitle);
		$infa->showCustomRow(
			$translation->getValue('formTitle'),
			$translation->getValue('value'),
			''
		);
		foreach ($rows as $key=>$row) 
		{
			//exit();
			$row = cmfcMySql::convertColumnNames($row,$_cp['sectionInfo']['tableInfo']['columns']);
			switch ($row['inputType'])
			{
				case 'checkbox':
					$infa->showCheckBoxRowCustom(
						$translation->getValue($row['key']),	// Title
						'rows['.$row['id'].'][columns][value]',	// Input Name
						$row['value'],							// Current Value
						1										// Default Value (if  = Current Value then checkbox is checked
					);
				break;
				
				case 'text':
					$infa->showInputRow(
						$translation->getValue($row['key']),	// Title
						'rows['.$row['id'].'][columns][value]',	// Input Name
						$row['value'],							// Current Value
						'',
						40, 
						$translation->languageInfo['direction']
					);
				break;
				
				case 'textarea':
					$infa->showTextAreaRow(
						$translation->getValue($row['key']),	// Title
						'rows['.$row['id'].'][columns][value]',	// Input Name
						$row['value'],							// Current Value
						'', 
						'', 
						5, 
						'90%',
						$translation->languageInfo['direction']
					);
				break;
			}
			$infa->showHiddenInput('rows['.$row['id'].'][columns][inputType]',$row['inputType'] );
		}
		$infa->showHiddenInput("action", "save");
		
		$buttons = array(
			array(
				'name' => 'submit_save',
				'value' => $translation->getValue('buttonSubmit'),
			),
			array(
				'name' => 'reset',
				'value' => $translation->getValue('buttonReset'),
				'type' => 'reset',
			),
		);
		$infa->showFormFooterCustom($buttons);
	} 
	else 
	{	?>
        <b><?php echo  wsfGetValue('nothingFound')?></b>
        <br />
        <br />
		<? 
	}
}

?>

