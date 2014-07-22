<?php
$infa=new cpInterface();

$num=1; 

$_REQUEST['id']=$userSystem->cvId;
if ($_REQUEST['action']=='delete') { 
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='single';	
	$_POST['rows'][$num]['columns']['id']=$_GET['id'];
	$_POST['rows'][$num]['action']='delete';
}


if ($_POST['submit_insert']) { 
	$_GET['action'] = $_REQUEST['action'] = 'new';
}

if ($_POST['submit_delete']) { 
	$_POST['submit_save']='save';
	$_POST['submit_action']='delete';
	$_POST['submit_mode']='multi';
	$_REQUEST['action'] = 'delete';
}


if (!$_REQUEST['action']){
 $_REQUEST['action']='edit';
 }


$messages=array(); 

$fieldsValidationInfo=array(
	/*
	'rows[1][columns][email]'=>array(
		'name'=>'rows[1][columns][email]',
		'title'=>'ایمیل',
		'type'=>'email',
		'param'=>array(
			'notEmpty'=>false
		)
	),
	
	'rows[1][columns][card_number]'=>array(
		'name'=>'rows[1][columns][card_number]',
		'title'=>'کد نمایندگی',
		'type'=>'number',
		'param'=>array(
			'min'=>0,
			'max'=>1111111,
			'notEmpty'=>false
		)
	),
	*/
);


$validation->setOption('fieldsInfo',$fieldsValidationInfo);

if ($_REQUEST['action']=='list')   {$actionTitle = wsfGetValue('list');}
if ($_REQUEST['action']=='edit')   {$actionTitle = wsfGetValue('edit');}
if ($_REQUEST['action']=='new')    {$actionTitle = wsfGetValue('new'); }
if ($_REQUEST['action']=='delete') {$actionTitle = wsfGetValue('remove');}




if (isset($_POST['submit_save']) and $_GET['action']!='view') {

	foreach ($_POST['rows'] as $num=>$row) {		
		$_columns=&$_POST['rows'][$num]['columns'];
		
		#--(Begin)-->prepare for multi action
		if ($_POST['submit_mode']=='multi' and $row['selected']!='true')
			continue;
		if (empty($row['action'])) $row['action']=$_POST['submit_action'];

		#--(End)-->prepare for multi action
        
		#--(Begin)-->fill and validate fields
		//echo'<pre style="direction:ltr;text-align:left">';print_r($columnsValues);echo'<pre>';
		if (is_array($fieldsValidationInfo))
			$validateResult=$validation->validate($_columns, "rows[$num][columns][%s]");
		$columnsPhysicalName=$sectionInfo['tableInfo']['columns'];
				
		if (!empty($_columns['password'])) {
			if ($_columns['password']==$_columns['passwordConfirmation'])
				unset($_columns['passwordConfirmation']);
			else
				$validateResult[]=PEAR::raiseError(wsfGetValue(Verification_Msg_Password_And_Confirmation_Password_Are_No_Equal));
		} else {
			unset($_columns['passwordConfirmation']);
			unset($_columns['password']);
		}
		$_columns['fullName']=$_columns['firstName'].' '.$_columns['lastName'];
				
		if ($row['action']=='update') {
			$_columns['updateDatetime']=date('Y-m-d H:i:s');
		} elseif($row['action']=='insert') {
			$_columns['insertDatetime']=date('Y-m-d H:i:s');
		}
		
		$columnsValues=array();
		
		$columnsValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
		

		#--(End)-->fill and validate fields

		if (empty($validateResult)) {
			//print_r($columnsValues);
			//print_r($_columns);
			#--(Begin)-->save changes to database
			if ($row['action']=='update') {
				$result=$userSystem->update($columnsValues,$userSystem->cvId);
				
				if (!empty($columnsValues[$userSystem->_colnPassword]))
					$columnsValues[$userSystem->_colnEncryptedPassword]=sha1($columnsValues[$userSystem->_colnPassword]);				
							
				if ($userSystem->cvId==$_ws["Main_Admin_Reserved_Id"]) {
					function updateMainAdminUser($portalInfo, $dbConnLink) {
						global $columnsValues;
						global $userSystem;
						global $_ws;

						$tableName=$portalInfo['Database_Info']['tablesPrefix'].str_replace($_ws['databaseInfo']['tablesPrefix'],'',$userSystem->_tableName);
						cmfcMySql::update($tableName,$userSystem->_colnId,$columnsValues,$userSystem->cvId);
					}
				
					cpfPortalsLoopThrough('updateMainAdminUser');
				}
				
				$msg=wsfGetValue(updateMsg);
			}
			#--(End)-->save changes to database
			
			
			
			
			if (PEAR::isError($result) or $result===false) {
				$messages['errors'][] = $result->getMessage();
				$messages['errors'][] = cmfcMySql::error();
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
		};

		if (!PEAR::isError($result)) {
		if (!$isErrorOccured && $actionTaken) {
			#--(Begin)-->redirect to previous url if everthings is ok
			$messages['messages'][]=sprintf(
				'<META http-equiv="refresh" content="1;URL=?%s">',
				cmfcUrl::excludeQueryStringVars(array('action'),'get')
			);
			
			#--(End)-->redirect to previous url if everthings is ok
			$saved=true;
		}			
		
		if (! $actionTaken){
			$messages['errors'][] = 'No action took place, mainly because you forgot to select a row to act on!';
		}	
	}
}



if (!$_POST['submit_save'] and $_REQUEST['action']=='edit') {
	
	$sqlQuery=sprintf(
		"SELECT * FROM %s WHERE %s='%s'",
		$_cp['sectionInfo']['tableInfo']['tableName'],
		$_cp['sectionInfo']['tableInfo']['columns']['id'],
		$_REQUEST['id']
	);
	
	$row=cmfcMySql::loadCustom($sqlQuery);
	if (!empty($row)) {
		$_POST['rows'][$num]['columns'] = cmfcMySql::convertColumnNames ($row, $_cp['sectionInfo']['tableInfo']['columns']);
	}
	
	
	//print_r($_POST);
}


cpfDrawSectionBreadCrumb();
cpfDrawSectionHeader();

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


if (in_array($_REQUEST['action'],array('new','edit')) and $saved!=true) {
	/*
	wsfLoadXinha(
		array(
			"rows[$num][columns][biography]"=>array('direction'=>'rtl')
		),
		$_cp['sectionInfo']['folderRelative'], 
		XinhaLoader_Theme_Full_Width_File_And_Image_Manager
	);
	*/

	if (!empty($fieldsValidationInfo)) {
		$validation->printJsClass();
		$validation->printJsInstance();
	}

	$infa->showFormHeader(null,'myForm',true);
	
	$infa->showTableHeader($actionTitle);

	$infa->showSeparatorRow(wsfGetValue(mainInfo));
	$infa->showCustomRow(wsfGetValue(Username),$userSystem->cvUsername);
	$infa->showInputPasswordRow(wsfGetValue(Password),"rows[$num][columns][password]", '',wsfGetValue(Password),40,'ltr');
	$infa->showInputPasswordRow(wsfGetValue(repeatPassword),"rows[$num][columns][passwordConfirmation]", '',wsfGetValue(repeatPassword),40,'ltr');
	$infa->showInputRow(wsfGetValue(Email),"rows[$num][columns][email]", $_POST['rows'][$num]['columns']['email'],wsfGetValue(Email),40,'ltr');
	
	$infa->showSeparatorRow(wsfGetValue(mainIssues));
	$infa->showInputRow(wsfGetValue(Name),"rows[$num][columns][firstName]", $_POST['rows'][$num]['columns']['firstName']);
	$infa->showInputRow(wsfGetValue(familyName),"rows[$num][columns][lastName]", $_POST['rows'][$num]['columns']['lastName']);
	
	//$infa->showTextareaRow('بيوگرافي',"rows[$num][columns][biography]",$_POST['rows'][$num]['columns']['biography'],'لطفا شرح حال خود را اينجا بنويسيد','',5,'97%');

	if ($_REQUEST['action'] == 'edit') {
		$infa->showHiddenInput("rows[$num][action]", "update");
		$infa->showHiddenInput("rows[$num][id]", "$_REQUEST[id]");
	} elseif($_REQUEST['action'] == 'new') {
		$infa->showHiddenInput("rows[$num][action]", "insert");
	}
	$infa->showFormFooter();
}
?>