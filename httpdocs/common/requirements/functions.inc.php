<?php
/**
 * control panel common functions
 * name of this functions should start with wsf like wsfGetCategoryPath
 * 
 * @author Sina Salek
 * @package [D]/admin/requirements
 */



/**
* 
* @param string $sectionName internal name of site section that is defined in site_sections table
* @return integer access point id via its internal_name that defined in database
* @package userSystem
* @subpackage authentication
* @category testme
* @author Sina Salek
*/
function wsfGetSiteSectionId($sectionName) {
	global $_ws;
	$tableInfo = $_ws['physicalTables']['siteSections'];
	if(!isset($_ws['siteSectionsAccessPointInfo']))
	{
		$siteSectionsAccessPointInfoQuery = "SELECT * FROM `".$tableInfo['tableName']."`";
		$siteSectionsAccessPointInfo = cmfcMySql::getRowsCustomWithCustomIndex($siteSectionsAccessPointInfoQuery, $tableInfo['columns']['internalName']);
		
		if($siteSectionsAccessPointInfo)
		{
			foreach($siteSectionsAccessPointInfo as $siteSectionsAccessPointKey=>$siteSectionsAccessPointItem)
			{
				$siteSectionsAccessPointId[$siteSectionsAccessPointKey] = $siteSectionsAccessPointItem['id'];
			}
		}
		
		$_ws['siteSectionsAccessPointInfo'] = $siteSectionsAccessPointId;
	}
	return $_ws['siteSectionsAccessPointInfo'][$sectionName];
}

/**
* check if user can pass the access point
* @param string $sectionName internal name of site section that is defined in site_sections table
* @return boolean
* @author Sina Salek
*/
function wsfIsAccessible($sectionName) {
	global $userSystem;
	$sectionId=wsfGetSiteSectionId($sectionName);
	//echo "sectionId:", $sectionId;
	if ($userSystem->isAccessible($sectionId)) {
		return true;
	} else {
		return false;
	}
}



/**
* check if user can pass the access point and redirect to denyAccess page if he can't
* @param string $sectionName internal name of site section that is defined in site_sections table
* @return boolean
* @author Sina Salek
*/
function wsfCheckSectionAccessibility($sectionName) {
	global $userSystem;
	$sectionId=wsfGetSiteSectionId($sectionName);
	echo $sectionId;
	if ($userSystem->isPageAccessible($sectionId)) {
		return true;
	} else {
		return false;
	}
}


/**
* check if user logged in or not (redirect if he is not logged in)
* @return boolean
* @author Sina Salek
*/
function wsfIsLoggedIn() {
	global $userSystem;
	return $userSystem->isLoggedIn();
}


/**
* If site has static pages , calling this function 
* this function accept both staticPage internal name or staticPage category internal name
* @param string $internalName internal name of static page that is defined in static_pages or its category internal name
* @return array array of static_page record columns in database
* @author Sina Salek
*/
function wsfGetStaticPage($internalName) {
	global $_ws;
	$info=cmfcMySql::load($_ws['physicalTables']['categories']['tableName'],$_ws['physicalTables']['categories']['columns']['internalName'],$internalName);
	
	if (is_array($info)) {
		$r=cmfcMySql::load($_ws['physicalTables']['staticPages']['tableName'],$_ws['physicalTables']['staticPages']['columns']['categoryId'],$info[$_ws['physicalTables']['categories']['columns']['id']]);
	} else {
		$r=cmfcMySql::load($_ws['physicalTables']['staticPages']['tableName'],$_ws['physicalTables']['staticPages']['columns']['internalName'],$internalName);
	}
	return $r;
}



/**
* if you use {@link  cpfGetFileUploadAuto} you can easily upload file
* with this function
* @see cpfGetFileUploadAuto
* @param string $fieldName name of the form upload field
* @param integer $basePath full path of the folder you want to move uploaded file to
* @return string name of the uploaded file
* @author Sina Salek
*/
function wsfUploadFileAuto($fieldName, $basePath) {
	global $_ws;
	
	$flatFieldName=str_replace(array('[',']'),array('_',''),$fieldName);
	
	$name=cmfcFile::uploadFile($_FILES[$flatFieldName]['tmp_name'],$_FILES[$flatFieldName]['name'],$basePath,$_POST['old_'.$flatFieldName]);
	if (file_exists($basePath.$name) and !is_dir($basePath.$name))
		chmod($basePath.$name,0777);
	
	if (!empty($_POST['old_'.$flatFieldName])){
		if ($_POST['old_'.$flatFieldName]!=$name or $_POST['delete_'.$flatFieldName])
			unlink($basePath.$_POST['old_'.$flatFieldName]);
	}
	if ($_POST['delete_'.$flatFieldName] and $_POST['old_'.$flatFieldName]==$name)
		return '';
	
	return $name;
}

/**
* gets html form field for uploading files. if a file uploaded already 
* it will generate delete button and also url to previous uploaded file
*
* @see cpfUploadFileAuto
* @param string $fieldName name of the form upload field 
* @param variant $value name of previous uploaded file
* @param string $basePath full path of the file [should be empty]
* @param string $baseUrl browser accessible url path of the file like : http://test.com/files/
* @param array $options other custom options :
* 		array(
*			'showDeleteButton'=>'false',
*			'showFileUrl'=>'false'
*		)
* @return string html
* @author Sina Salek
*/
function wsfGetFileUploadAuto($fieldName,$value,$basePath='',$baseUrl='',$options=array()) {
	global $HTTP_POST_VARS, $lang;
	global $_ws;
	
	if (!isset($options['showDeleteButton'])) $options['showDeleteButton']=true;
	if (!isset($options['showFileUrl'])) $options['showFileUrl']=true;
	
	$flatFieldName=str_replace(array('[',']'),array('_',''),$fieldName);
	
	$html.='<input name="'.$flatFieldName.'" value="" type="file" />';
	$dimension = "";

	if (!empty($value)) {
		$src=$baseUrl.$value;
		if ($options['showDeleteButton']===true) 
			$html.='<input name="delete_'.$flatFieldName.'" value="true" type="checkbox" style="margin-bottom:0px"/> حذف فایل فعلی</a><br/><br/>';
		if ($options['showFileUrl']===true) 
			$html.=$src;
		$html.='<input name="old_'.$flatFieldName.'" value="'.$value.'" type="hidden" />';
	}
	//echo '<pre>'. htmlspecialchars($html).'</pre>';
	return $html;
}

/**
* gets html form field for uploading image files. if a image file uploaded already 
* it will generate delete button and also thumbnail of the previous uploaded image file.
* for showing thumbnail of the image it needs {@link imageDisplayer.php}
*
* @author Sina Salek
* @see cpfUploadFileAuto
* @param string $fieldName name of the form upload image field 
* @param variant $value name of previous uploaded image file
* @param string $basePath full path of the image file [should be empty]
* @param string $baseUrl browser accessible url path of the file like : http://test.com/files/
* @param integer $width width of the thumbnail
* @param integer $height height of the thumbnail
* @return string html
*/
function wsfGetImageUploadAuto($fieldName, $value, $basePath='', $baseUrl='', $width=NULL, $height=NULL, $itemType = NULL) {
	
	
	if (is_null($width))
		$width = 150;
	if (is_null($height))
		$height = 150;
	
	if (is_null($itemType))
		$itemType = 'image';
	
	global $HTTP_POST_VARS, $lang;
	global $_ws;

	$flatFieldName=str_replace(array('[',']'),array('_',''),$fieldName);
	//$flatFieldName = $fieldName;
	
	$html.='<input name="'.$flatFieldName.'" value="" type="file" />';
	$dimension = "";
	if ($height) $dimension .= ' height="'.$height.'"';
	if ($width) $dimension .= ' width="'.$width.'"';
	

	if (!empty($value)) {
		//$src=$basePath.$value;
			
		$html.='<input name="delete_'.$flatFieldName.'" value="true" type="checkbox" style="margin-bottom:0px"/>'.wsfGetValue('deleteCurrentFile').'<br/><br/>';
		
		switch($itemType){
		default:
			$html .= "<a href='".$baseUrl.$value."'>".$baseUrl.$value."</a>";
			break;
		
		case 'image':
			$html .= wsfGetImageTag(
				array(
					'fileName'=>$value,
					'fileRelativePath'=>str_replace($_ws['siteInfo']['path'],'',$basePath),
					'width'=>$width,
					'height'=>$height,
					'mode'=>'resizeByMaxSize',
					'attributes'=>array(
						'id'=>'',
						'alt'=>'[photo('.$value.')]',
						'border'=>$border,
					)
				)
			);
			break;
			
		case 'flash':
			$html .= '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="'.$width.'" align="middle">
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="movie" value="'.$baseUrl.$value.'" />
					<param name="quality" value="high" />
					<param name="wmode" value="transparent" />
					<param name="bgcolor" value="#ffffff" />
					<embed src="'.$baseUrl.$value.'" quality="high" wmode="transparent" bgcolor="#ffffff" width="'.$width.'"  align="middle" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
				</object>';
			break;
		}
		
		$html.='<br />';
		$html.='<input name="old_'.$flatFieldName.'" value="'.$value.'" type="hidden" />';
	}
	
	//cmfcHtml::printr(htmlspecialchars($html));
	return $html;
}


function wsfGetCategoryPath($treeTableName, $categoryID, $fields =array('id','name'), $pathTitle = 'name', $sep = '&raquo ' ){
	global $dbConnLink;
	global $_ws;
	
	@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
	$db->conn=&$dbConnLink;
	//if (!isset($categoryPathTree)){
	$categoryPathTree = cmfcHierarchicalSystem::factory('dbOld',
		array(
			'tableName'=>$treeTableName,
			'dbInstance'=>&$db,
			'prefix'=> ''
		)
	);
	$categoryPath='';
	$separator='';
	$root = $categoryPathTree->getRootNodeId();
	$where = array('and'=>array("`$fields[0]`<> $root"));
	if ($category=cmfcMySql::load($treeTableName, $fields[0], $categoryID)) {
		$categoryPathTree->parents($category[$fields[0]], $fields, $where);
		while ($category=$categoryPathTree->nextRow()) {
			$categoryPath.="$separator$category[$pathTitle]";
			if (empty($separator)) $separator=$sep;
		}
	}
	return $categoryPath;				
}

function wsfGetCategoryPathForDb($treeTableName, $categoryID, $pair, $idField){
		
	$separator=',';
	$categoryPath = wsfGetCategoryPath($treeTableName, $categoryID, array($idField), $idField, ',');
	//$categoryPath = wsfGetCategoryPath($treeTableName, $categoryID, $pair, $idField, ',');
	$categoryPath = $separator.$categoryPath.$separator;
	return $categoryPath;				
}




//function cpfPictureUniqueCode($array){}
$categoryPathTree="";
function wsfCreateRelatedDropDowns($informationArray){
	global $_ws;
	$parent = '';
	$result = array();
	foreach ($informationArray as $myKey=>$myValue){
		$attribs = "";
		$myTable 		= $myValue['tableInfo'];
		$myName 		= $myValue['name'];
		$myID 			= $myValue['id'];
		$value 			= $myValue['value'];
		$myValueCol 	= $myTable['columns'][$myValue['valueColumn']];
		$myTitleCol 	= $myTable['columns'][$myValue['titleColumn']];
		$myAttribs 		= cmfcPhp4::array_merge($myValue['attributes'], array('id' => $myName));
		
		$next = next($informationArray);
		if (!empty($next)){
			
			$myAttribs['onchange'] = "onSelectParentDropDown('$myName','$next[name]')";
			$myAttribs['onkeyup'] = "onKeyPressParentDropDown('$myName','$next[name]')";
		}
		
		if (!empty($parent) ){
			$myParentTable 	= $parent['tableInfo'];
			$parentName		= $parent['name'];
			$parentValue 	= $parent['value'];
			$parentValueCol	= $myTable['columns'][$parent['valueColumn']];
			$parentTitleCol = $myTable['columns'][$parent['titleColumn']];
			$myParentCol 	= $myTable['columns'][$myValue['parentColumnName']];
		
			$result[$myKey].="<span id='".$myName."_back'>";
			$sqlQuery=cmfcString::replaceVariables(
				array(
					'{childT}'		=> $myTable['tableName'],
					'{cT:value}'	=> $myValueCol,
					'{cT:name}'		=> $myTitleCol,
					'{cT:parentId}'	=> $myParentCol,
					'{parentT}'		=> $myParentTable['tableName'],
					'{pT:name}'		=> $parentTitleCol,
					'{pT:id}'		=> $parentValueCol,
				),
				'SELECT {childT}.*,{parentT}.{pT:name} as "mainName" FROM {childT} LEFT JOIN {parentT} ON {parentT}.{pT:id}={childT}.{cT:parentId} ORDER BY {cT:parentId},{childT}.{cT:name}'
			);
		}
		else{
			$sqlQuery = 'SELECT * FROM '. $myTable['tableName'].
					' ORDER BY '.$myTable['orderByColumnName'].
					' '.$myTable['orderType'];
				
		}
		$answer = cmfcMySql::getRowsCustom($sqlQuery);
		//echo "answer: ";
		//print_r($answer);
		$result[$myKey] .= cmfcHtml::drawDropDown(
			$myName, //name
			$value, //value
			$answer, //items
			$myValueCol, //valueColumnName
			$myTitleCol, //titleColumnName
			'mainName', //groupNameColumnName
			$myParentCol, //groupIdColumnName
			'', //defaultValue
			'', //defaultTitle
			$myAttribs
		);
		if (!empty($parent))
			$result[$myKey] .= '</span>';
		
		$parent = $informationArray[$myKey];
	}
	
	return $result;
}


function wsfManipulateImage($options) {
	global $imageManipulator;
	return $imageManipulator->manipulateImage($options);
}

function wsfGetImageTag($options) {
	global $imageManipulator;
	

	
	$html = $imageManipulator->getAsImageTag($options);
	

	return $html;
}


/**
* load and initilize Xinha to page
* <code>
* ptcpLoadXinha(array('short_body','short_body_en','body','body_en'),$sectionInfo['folderRelative'],
* 				XinhaLoader_Theme_Full_Width_File_And_Image_Manager,array('short_body','body'));
* </code>
* 
* @param array $editors array of htmlarea DOM ids
* @param string $imagesFolder path to the image folder for using with builtin image manager
* @param string $theme name of predefined xinha themes (XinhaLoader_Theme_Simple_With_Image_Manager | XinhaLoader_Theme_Simple)
* @param array $rtlEditors array of htmlarea DOM ids that their content should be right to left (like in Arabic and Farsi)
* @return void
* @author Sina Salek
array(
	'imagesUrl'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
	'imagesDir'=>Site_URL.$_cp['sectionInfo']['folderRelative'],
	'baseUrl'=>$_ws['siteInfo']['path'].$_cp['sectionInfo']['folderRelative'],
	'baseDir'=>Site_URL.$_cp['sectionInfo']['folderRelative'],
	'templateName'=>'simpleWithImageManager',
	'editors'=>array(
		"rows[$num][columns][body]"=>array(
			'id'=>"rows[$num][columns][body]",
			'direction'=>'ltr',
			'loadOnDemand'=>true
		)
	)
),
*/
function wsfWysiwygLoader($options) {
	global $wysiwyg;
	

	
	$wysiwyg->setOptions($options,true);	
	
	$wysiwyg->loadCore();
	$wysiwyg->prepareEditors();
	$wysiwyg->loadOnPageLoad();
}




function wsfGetSectionPart() {
	
	
	if (preg_match('/.*\/([^\\/\\\]*)\/[^\\/\\\]*/', $_SERVER['REQUEST_URI'], $regs)) {
		$result = $regs[1];
	} else {
		$result = '';
	}
	return $result;
}

function wsfGetSubDomainName() {
	if (preg_match('/(www\\.)?([^.\\/:]*)\\.[^.]*\\.[^.]*/i', $_SERVER['HTTP_HOST'], $regs)) {
		$result = $regs[2];
		if (strtolower($result)=='www') $result='';
	} else {
		$result = "";
	}
	if ($result=='pt') $result='';
	return $result;
}

function wsfGetLangByIp($ip,$defaultLang='') {
	$longIp=ip2longFix($ip);
	$sqlQuery="SELECT * FROM ip_to_country WHERE `from`<='$longIp' AND `to`>='$longIp' LIMIT 1";
	$row=mysql_fetch_assoc(mysql_query($sqlQuery));
	if ($row['country_short_name']=='IR') $defaultLang='fa';
	return $defaultLang;
}

function wsfGetDateTime($format, $time='now', $lang = 'auto', $faNumbers = 1){
	global $translation;
	
	if ($lang == 'auto'){
		if ($translation->languageInfo['calendarType'] == 'jalali')
			$lang = 'fa';
		else
			$lang = 'en';
	}
	
	if ($time == 'now')
		$dateTime = time();
	elseif($time)
		$dateTime = strtotime($time);
		
	if ($lang == 'fa')
		return cmfcJalaliDateTime::smartGet($format, $dateTime, $faNumbers);
	else
		return date($format, $dateTime);
}

function wsfDrawAddibleBoxes($title, $name, $template, $tableInfo, $relationID, $columnReplacements, $replacements, $additionalKeys=array(), $orderBy="", $postedItems=NULL, $jsOnAddCallBack=NULL){
	global $langInfo;
	$containments = wsfDrawBoxes($name, $template, $tableInfo, $relationID, $columnReplacements, $replacements, $additionalKeys, $orderBy, $postedItems, $jsOnAddCallBack);
	$html ['title'] = $title."<a href=\"javascript:glAddNewBox('$name','".$name."_template_box','$name'".($jsOnAddCallBack ? ", '$jsOnAddCallBack'" : '').")\"><img src=\"/interface/images/add_button_".$langInfo['sName'].".gif\" border=0 alt=\"+\" /></a>";
	
	if(is_array($containments))
	{
		$html ['containerHeader'] ="<div id='$nameheader'>".$containments['header']."</div>";
		
		$containments = $containments['addible'];
	}
	
	$html ['containerRow'] ="<div id='$name'>".$containments."</div>";
	return $html;
}

function wsfDrawBoxes($addibleItem, $templates){
	
	$name = $addibleItem['name'];
	$template = $templates[$addibleItem['name']];
	$tableInfo = $addibleItem['tableInfo'];
	$relationID = $addibleItem['destRelationColumn'];
	$columnReplacements = $addibleItem['columnReplacements'];
	$replacements = $addibleItem['replacements'];
	$additionalKeys = $addibleItem['additionalCondition'];
	$orderBy = $addibleItem['orderBy'];
	$postedItems = $_POST[$name];
	$jsOnAddCallBack = $addibleItem['jsCallback'];
	$subAddibles = $addibleItem['subAddibles'];
	$files = $addibleItem['files'];
	//cmfcHtml::printr($addibleItem);
	if(is_array($template))
	{
		$box_template=$template['addible'];
		$box_header=$template['header'];
	}
	else
		$box_template=$template;
	//cmfcHtml::printr($postedItems);
	//cmfcHtml::printr($_POST);
	if(!$postedItems)
	{
		$tableName = $tableInfo['tableName'];
		$relationID['title'] = $tableInfo['columns'][$relationID['title'] ]; 
		$keys = array(
			$relationID['title']=>$relationID['value'],
		);
		$multiKeys = cmfcPhp4::array_merge($keys, $additionalKeys);
		
		$itemQuery = "SELECT * FROM `".$tableName."` WHERE (1=1)";
		
		if($multiKeys)
		{
			foreach($multiKeys as $numtiKeyName=>$multiKeyValue)
			{
				$itemQuery .= " AND `".$numtiKeyName."`=".$multiKeyValue;
			}
		}
		
		if($orderBy)
			$itemQuery .= " ORDER BY ".$tableInfo['columns'][$orderBy]." ASC";
		$items = cmfcMySql::getRowsCustom($itemQuery);
		if($items)
			foreach($items as $itemKey=>$item)
				$items[$itemKey] = cmfcMySql::convertColumnNames($item, $tableInfo['columns']);
	}
	else
	{
		foreach($postedItems as $postItemKey=>$postedItem)
		{
			if (is_array($files))
			{
				foreach ($files as $k=>$r)	
				{
					$postedItem['columns'][$r] = $_POST['old_'.$name.'_'.$postItemKey.'_columns_'.$r];
				}
			}
			if(is_numeric($postItemKey))
				$items[] = $postedItem['columns'];
		}
	}
	
	
	#Begin Create Header Template For addibleBox
	if($box_header)
	{
		$replace = array();
		if (isset($replacements) && !empty($replacements) )
		{
			foreach($replacements as $key => $value)
				$replace[$key] = $value;
		}
		$replace['%temp_name%'] = $name;
	
		$header_html = '';
		$header_html = str_replace(
			array_keys($replace),
			array_values($replace),
			$box_header
		);
		$header_html = str_replace(
			array_keys($replace),
			array_values($replace),
			$header_html
		);
	}
	#End Create Header Template For addibleBox
	
	$items_html = '';

	$item_number=0;
	if (is_array($items)){
		foreach ($items as $item) 
		{
			$replace = array();
			$item_number++;
			if (isset($columnReplacements) && !empty($columnReplacements) )
			{
				foreach($columnReplacements as $key => $value)
				{
					//$columnPhysicalName = $tableInfo['columns'][$value];
					
					$replace[$key] = $item[$value];
				}
			}
			if (isset($replacements) && !empty($replacements) )
			{
				foreach($replacements as $key => $value)
					$replace[$key] = $value;
			}
			$replace['%{item_number}%'] = $item_number;
			$replace['%temp_name%'] = $name;
			
			if($subAddibles)
			{
				foreach($subAddibles as $subAddibleKey=>$subAddible)
				{
					$subAddibleTemplate = $templates[$name.'_'.$subAddible['name']];
					if(is_array($subAddibleTemplate))
					{
						$subAddible_box_template=$subAddibleTemplate['addible'];
						$subAddible_box_header=$subAddibleTemplate['header'];
					}
					else
						$subAddible_box_template=$subAddibleTemplate;
					
						
					$subAddibleCallBackScripts = '';
					
					if(!$postedItems[$subAddible['name']][$item_number])
					{
						$tableName = $subAddible['tableInfo']['tableName'];
						$subAddible['destRelationColumn']['title'] = $subAddible['tableInfo']['columns'][$subAddible['destRelationColumn']['title'] ]; 
						$keys = array(
							$subAddible['destRelationColumn']['title']=>$item[$subAddible['destRelationColumn']['value']],
						);
						$multiKeys = cmfcPhp4::array_merge($keys, $subAddible['additionalCondition']);
						
						$itemQuery = "SELECT * FROM `".$tableName."` WHERE (1=1)";
						
						if($multiKeys)
						{
							foreach($multiKeys as $numtiKeyName=>$multiKeyValue)
							{
								$itemQuery .= " AND `".$numtiKeyName."`=".$multiKeyValue;
							}
						}
						
						if($subAddible['orderBy'])
							$itemQuery .= " ORDER BY ".$subAddible['tableInfo']['columns'][$subAddible['orderBy']]." ASC";
							
						$subAddibleItems = cmfcMySql::getRowsCustom($itemQuery);
						if($subAddibleItems)
							foreach($subAddibleItems as $subAddibleItemKey=>$subAddibleItem)
								$subAddibleItems[$subAddibleItemKey] = cmfcMySql::convertColumnNames($subAddibleItem, $subAddible['tableInfo']['columns']);
					}
					else
					{
						$subAddibleItems = array();
						foreach($postedItems[$subAddible['name']][$item_number] as $postedItem)
						{
							$subAddibleItems[] = $postedItem['columns'];
						}
					}
					
					
					#Begin Create Header Template For addibleBox
					if($subAddible_box_header)
					{
						$subAddibleReplace = array();
						if (isset($subAddible['replacements']) && !empty($subAddible['replacements']) )
						{
							foreach($subAddible['replacements'] as $subAddibleReplacementKey => $subAddibleReplacement)
								$subAddibleReplace[$subAddibleReplacementKey] = $subAddibleReplacement;
						}
						$subAddibleReplace['%temp_name%'] = $name.'['.$subAddible['name'].']['.$item_number.']';
					
						$subAddible_header_html = '';
						$subAddible_header_html = str_replace(
							array_keys($subAddibleReplace),
							array_values($subAddibleReplace),
							$subAddible_box_header
						);
						$subAddible_header_html = str_replace(
							array_keys($subAddibleReplace),
							array_values($subAddibleReplace),
							$subAddible_header_html
						);
					}
					#End Create Header Template For addibleBox
				
					$subAddible_items_html = '';
				
					$subAddible_item_number=0;
					if (is_array($subAddibleItems)){
						foreach ($subAddibleItems as $subAddibleItem) 
						{
							$subAddibleReplace = array();
							$subAddible_item_number++;
							if (isset($subAddible['columnReplacements']) && !empty($subAddible['columnReplacements']) )
							{
								foreach($subAddible['columnReplacements'] as $subAddibleReplacementKey => $subAddibleReplacement)
								{
									//$columnPhysicalName = $tableInfo['columns'][$value];
									$subAddibleReplace[$subAddibleReplacementKey] = $subAddibleItem[$subAddibleReplacement];
								}
							}
							if (isset($subAddible['replacements']) && !empty($subAddible['replacements']) )
							{
								foreach($subAddible['replacements'] as $subAddibleReplacementKey => $subAddibleReplacement)
									$subAddibleReplace[$subAddibleReplacementKey] = $subAddibleReplacement;
							}
							$subAddibleReplace['%{item_number}%'] = $subAddible_item_number;
							$subAddibleReplace['%temp_name%'] = $name.'['.$subAddible['name'].']['.$item_number.']';
					
							$subAddible_items_html.=str_replace(
								array_keys($subAddibleReplace),
								array_values($subAddibleReplace),
								$subAddible_box_template
							);
							$subAddible_items_html=str_replace(
								array_keys($subAddibleReplace),
								array_values($subAddibleReplace),
								$subAddible_items_html
							);
							
							if($subAddible['jsCallback'])
								$subAddibleCallBackScripts .= $subAddible['jsCallback']."('".$name.'['.$subAddible['name'].']'."','".$name.'_'.$subAddible['name']."_template_box','".$name.'['.$subAddible['name'].']'."', '$subAddible_item_number');\n";
						}
					}
					
					if($subAddibleCallBackScripts)
						$subAddible_items_html .= "<script type='text/javascript' language='javascript'>$subAddibleCallBackScripts</script>";
						
					$subAddibleBoxHtml = "<table class=\"subAddibleContentContainer\">
						<tr><td>".$subAddible['title']."<a href=\"javascript:cpfAddNewBox('".$name.'['.$subAddible['name'].']['.$item_number.']'."','".$name."_".$subAddible['name']."_template_box','".$name.'['.$subAddible['name'].']['.$item_number.']'."', '".$subAddible['jsCallback']."', '$item_number')\"><img border='0' alt='' src='interface/images/addible.png' /></a></td></tr>";
					if($subAddible_box_header)
						$subAddibleBoxHtml .= "	<tr><td><div id='".$name.'['.$subAddible['name'].']['.$item_number.']'."header'>".$subAddible_header_html."</div></td></tr>";
					$subAddibleBoxHtml .= "	<tr><td><div id='".$name.'['.$subAddible['name'].']['.$item_number.']'."'>".$subAddible_items_html."</div></td></tr>
					</table>";
						
					$replace['%subAddibleContainer_'.$subAddible['name'].'%'] = $subAddibleBoxHtml;
				}
			}
			
	
			$items_html.=str_replace(
				array_keys($replace),
				array_values($replace),
				$box_template
			);
			$items_html=str_replace(
				array_keys($replace),
				array_values($replace),
				$items_html
			);
			
			if($jsOnAddCallBack)
				$callBackScripts .= $jsOnAddCallBack."('$name','".$name."_template_box','$name', '$item_number');\n";
		}
	}
	
	if($callBackScripts)
		$items_html .= "<script type='text/javascript' language='javascript'>$callBackScripts</script>";
	
	if($box_header)
		return array('header'=>$header_html,'addible'=>$items_html);
	else
		return $items_html;
}

function wsfPrepareAddibleBoxesTemplate($addibleItems,$templates) {
	$html ='<div id="template_item_box" style="display:none">';
	
	foreach($addibleItems as $key => $value){
		if(is_array($templates[$value['name']]))
			$template = $templates[$value['name']]['addible'];
		else
			$template = $templates[$value['name']];
		
		$html .='<div id="'.$value['name'].'_template_box" style="display:none">';
		$replace=$value['replacements'];				
		if (empty($replace['%temp_name%']))
			$replace['%temp_name%']=$value['name'];
		$items_html = str_replace(
			array_keys($replace),
			array_values($replace),
			$template
		);
		$html .=htmlspecialchars($items_html);

		$html .='</div>';
		}
	$html .='</div>';
	echo $html;
}

function wsfSaveAddibleItems($options, $relationId, $glue = ','){
	global $_ws;
	$returnValue = false;
	//cmfcHtml::printr($_POST);
	//cmfcHtml::printr($_FILES);
	if (is_array($_POST[ $options['name'] ]))
	{
		$count = 0;
		foreach ($_POST[ $options['name'] ] as $itemNumber=>$item) {
			if((int)$itemNumber){
				$itemColumns = '';
				$columns = array();
				$myTable=$options['tableInfo'];
				$myTableName = $myTable['tableName'];
				$itemColumns=$item['columns'];
				$myTableColumns = $myTable['columns'];
				
				$relationshipInfo = $options['destRelationColumn'];
				
				$relationshipColumn = $relationshipInfo['title'];
				
				$itemColumns[ $relationshipColumn ] = $relationId;
				
				
				$notEmtpy = false;
				foreach ($myTableColumns as $key => $optionsItem){
					if($options['files']){
						if(in_array($key , $options['files']) ){
							$files = $options['name'].'_'.$itemNumber.'_columns_'.$optionsItem.'';
							//cmfcHtml::printr($files);
							$itemColumns[ $key ] = cpfUploadFileAuto($files, $_ws['siteInfo']['path'].$options['replacements']['%file_path_url%']);
						}
					}
					if(isset($itemColumns[$key]))
						$columns[ $optionsItem ] = $itemColumns[$key];
					if (!empty($columns[ $optionsItem ] ))
						$notEmpty = true;	
				}
				
				//var_dump($notEmpty);
				//echo $_ws['siteInfo']['path'].$options['replacements']['%file_path_url%'];
				//echo $files;
				//cmfcHtml::printr($myTableColumns);
				//cmfcHtml::printr($itemColumns);
				//cmfcHtml::printr($columns);
				//cmfcHtml::printr($_FILES);
				if (!isset($item['delete']) && $notEmpty)
				{
					if (!$columns['id']){
						$columns['insert_datetime']=date('Y-m-d H:i:s');
						$result['status'][$count] = cmfcMySql::insert($myTableName,$columns);
						$columns['id'] = cmfcMySql::insertId();
						$result['errors'][$count] = cmfcMySql::error();
						$result['messages'][$count] = 'داده با شناسه ی '.$columns['id'].' به `'.$options['title'].'` افزوده شد.';
					}
					else{
						$columns['update_datetime']=date('Y-m-d H:i:s');
						$result['status'][$count] = cmfcMySql::update($myTableName, 'id', $columns, $columns['id']);
						$result['errors'][$count] = cmfcMySql::error();
						$result['messages'][$count] = 'داده با شناسه ی '.$columns['id'].' در `'.$options['title'].'` به روز شد.';
					}
					
					$itemDbId = $columns['id'];
					
					if($options['subAddibles'])
					{
						foreach($options['subAddibles'] as $subAddible)
						{
							if($_POST[$options['name']][$subAddible['name']][$itemNumber])
							{
								foreach($_POST[ $options['name']][$subAddible['name']][$itemNumber] as $subAddibleItemumber=>$subAddibleItem)
								{
									$itemColumns = '';
									$columns = array();
									$myTable=$subAddible['tableInfo'];
									$myTableName = $myTable['tableName'];
									$itemColumns=$subAddibleItem['columns'];
									$myTableColumns = $myTable['columns'];
									
									$relationshipInfo = $subAddible['destRelationColumn'];
									
									$relationshipColumn = $relationshipInfo['title'];
									
									$itemColumns[ $relationshipColumn ] = $itemDbId;
									
									
									$notEmtpy = false;
									foreach ($myTableColumns as $key => $optionsItem){
										if($subAddible['files']){
											if(in_array($key , $subAddible['files']) ){
												$files = $options['name'].'['.$subAddible['name'].']['.$itemNumber.']['.$subAddibleItemumber.'][columns]['.$optionsItem.']';
												$itemColumns[ $key ] = cpfUploadFileAuto($files, $_ws['siteInfo']['path'].$subAddible['replacements']['%file_path_url%']);
											}
										}
										if(isset($itemColumns[$key]))
											$columns[ $optionsItem ] = $itemColumns[$key];
										if (!empty($columns[ $optionsItem ] ))
											$notEmpty = true;	
									}
									
									if (!isset($subAddibleItem['delete']) && $notEmpty)
									{
										if (!$columns['id']){
											$columns['insert_datetime']=date('Y-m-d H:i:s');
											/*$result['status'][$count] = */cmfcMySql::insert($myTableName,$columns);
											$columns['id'] = cmfcMySql::insertId();
											//$result['errors'][$count] = cmfcMySql::error();
											//$result['messages'][$count] = 'داده با شناسه ی '.$columns['id'].' به `'.$options['title'].'` افزوده شد.';
										}
										else{
											$columns['update_datetime']=date('Y-m-d H:i:s');
											/*$result['status'][$count] = */cmfcMySql::update($myTableName, 'id', $columns, $columns['id']);
											//$result['errors'][$count] = cmfcMySql::error();
											//$result['messages'][$count] = 'داده با شناسه ی '.$columns['id'].' در `'.$options['title'].'` به روز شد.';
										}
										
										
										
									}
									if (isset($subAddibleItem['delete']))
									{
										/*$result['status'][$count] = */cmfcMySql::delete($myTableName, 'id', $columns['id']);
										//$result['errors'][$count] = cmfcMySql::error();
										//$result['messages'][$count] = 'داده با شناسه ی '.$columns['id'].' در `'.$options['title'].'` پاک شد.';
									}
								}
							}
						}
					}					
				}
				
				if (isset($item['delete']))
				{
					$result['status'][$count] = cmfcMySql::delete($myTableName, 'id', $columns['id']);
					$result['errors'][$count] = cmfcMySql::error();
					$result['messages'][$count] = 'داده با شناسه ی '.$columns['id'].' در `'.$options['title'].'` پاک شد.';
					
					if($options['subAddibles'])
					{
						foreach($options['subAddibles'] as $subAddible)
						{
							$deleteQuery = "DELETE FROM `".$subAddible['tableInfo']['tableName']."` WHERE `".$subAddible['tableInfo']['columns'][$subAddible['destRelationColumn']['title']]."`='".$columns['id']."'";
							cmfcMySql::exec($deleteQuery);
						}
					}
				}
				$count++;
			}
		}
				
		return $result;
	}
}

function wsfGetSectionNameByNodeId($nodeId,$nodeInfo=null) {
	global $_ws;
	$mainNodeId=0;
	foreach ($_ws["Main_Tree_Nodes_Details"] as $sectionName=>$mainNodeInfo) {
		if ($nodeInfo['lft']>=$mainNodeInfo['lft'] and $nodeInfo['rgt']<=$mainNodeInfo['rgt']) {
			$mainNodeId=$mainNodeInfo['id'];
			break;
		}
	}
	return $sectionName;
}


/**
 *for converting array returned from  cmfcHtml::drawDateTimeDropDownBeta with considering jalali or Gregorian
*/
function wsfConvertDateTimeArray($format, $value){
	$dateTime = '';
	$type = $value['type'];
	if (!$type)
		$type = 'gregorian';
	
	if ($value[0])
		$value['year'] = $value[0];
	if ($value[1])
		$value['month'] = $value[1];
	if ($value[2])
		$value['day'] = $value[2];
	if ($value[3])
		$value['hour'] = $value[3];
	if ($value[4])
		$value['minute'] = $value[4];
	if ($value[5])
		$value['second'] = $value[5];
	
	if ($value['year']){
		if (!$value['month'])
			$value['month'] = 1;
		if (!$value['day'])
			$value['day'] = 1;
	}
	
	if ($type == 'jalali' && ( $value['year'] && $value['month'] && $value['day']) )
		list($value['year'], $value['month'], $value['day']) = cmfcJalaliDateTime::jalaliToGregorian($value['year'], $value['month'], $value['day']);
	
	//cmfcHtml::printr($value);
	
	if ($value['year'])
		$dateTime .= $value['year'].'-'.$value['month'].'-'.$value['day'];
		
	if ($value['hour'])
		$dateTime .= $value['hour'].':'.$value['minute'].':'.$value['second'];
	
	//cmfcHtml::printr($dateTime);
	
	$retValue = '';
	if ($dateTime)
		$retValue = date($format, strtotime($dateTime));
	return $retValue;
}


/** by elnaz 
 *gets language info by its short name, 
 *if no short name passed it will return all defined languages
**/

function wsfGetLangInfo($shortName=''){
	global $translation;
	return $translation->getLanguageInfo($shortName);
}



/** by Sina Salek
*gets languageId by its short name, 
*/
function wsfGetLanguageIdByShortName($shortName) {
	$languageInfo = wsfGetLangInfo($shortName);
	return $languageInfo['id'];
}

function wsfIncludeSectionRelatedJavascripts() {
	global $_ws;
	global $_cp;
	global $_insideAdmin;
	if (isset($_cp['sectionsInfo'])) {
		$sectionInfo=$_cp['sectionInfo'];
	} else {
		$sectionInfo=$_ws['sectionInfo'];
	}
	
	$sectionJavascriptRPath='interface/javascripts/'.$sectionInfo['name'].'.section.inc.js';
	$sectionPhpJavascriptRPath='interface/javascripts/'.$sectionInfo['name'].'.section.inc.js.php';
	if (file_exists($_ws['siteInfo']['path'].'/'.$sectionJavascriptRPath)) {
		$html.='<script language="javascript" src="'.$sectionJavascriptRPath.'" type="text/javascript"></script>'."\n";;
	}
	if (file_exists($_ws['siteInfo']['path'].'/'.$sectionPhpJavascriptRPath)) {
		$html.='<script language="javascript" src="'.$sectionPhpJavascriptRPath.'" type="text/javascript"></script>'."\n";;
	}
	
	if (is_array($sectionInfo['javascriptFilesToInclude'])) {
		foreach ($sectionInfo['javascriptFilesToInclude'] as $jsFile) 
		{
			if ($_insideAdmin)
				$jsFile='sup/interface/javascripts/'.$jsFile;
			else
				$jsFile='interface/javascripts/'.$jsFile;
			if (file_exists($_ws['siteInfo']['path'].$jsFile)) 
			{
				$html.='<script language="javascript" src="/'.$jsFile.'" type="text/javascript"></script>'."\n";
			}
		}
	}
	
	return $html;
}

function wsfIncludeSectionRelatedCss() {
	global $_ws;
	global $_cp;
	global $_insideAdmin;
	
	if (isset($_cp['sectionsInfo'])) {
		$sectionInfo=$_cp['sectionInfo'];
	} else {
		$sectionInfo=$_ws['sectionInfo'];
	}
	
	$html='';
	$sectionCssRPath='interface/css/'.$sectionInfo['name'].'.section.inc.css';
	$sectionPhpCssRPath='interface/css/'.$sectionInfo['name'].'.section.inc.css.php';
	if (file_exists($_ws['siteInfo']['path'].'/'.$sectionCssRPath)) {
		$html.='<link href="'.$sectionCssRPath.'" rel="stylesheet" type="text/css" />'."\n";
	}
	if (file_exists($_ws['siteInfo']['path'].'/'.$sectionPhpCssRPath)) {
		$html.='<link href="'.$sectionPhpCssRPath.'" rel="stylesheet" type="text/css" />'."\n";
	}
	
	if (is_array($sectionInfo['cssFilesToInclude'])) {
		foreach ($sectionInfo['cssFilesToInclude'] as $cssFile) {
			if ($_insideAdmin)
				$cssFile='sup/interface/css/'.$cssFile;
			else
				$cssFile='interface/css/'.$cssFile;
			
			if (file_exists($_ws['siteInfo']['path'].'/'.$cssFile)) {
				$html.='<link href="/'.$cssFile.'" rel="stylesheet" type="text/css" />'."\n";
			}
		}
	}
	
	return $html;
}

function wsfPrepareSectionToIncludeInternalPart($internalPartName) {
	global $_ws;
	global $_cp;
		
	if (isset($_cp['sectionsInfo'])) {
		$sectionInfo=$_cp['sectionInfo'];
		$_cp['currentSectionInternalPartName']=$internalPartName;
	} else {
		$sectionInfo=$_ws['sectionInfo'];
		$_ws['currentSectionInternalPartName']=$internalPartName;
	}
	
	if (is_array($sectionInfo['internalParts']))
		if (in_array($internalPartName,$sectionInfo['internalParts'])) {
			return true;
		}
	return false;
}



function wsfGetValue($key, $prefix = NULL) {
	global $translation;
	return $translation->getValue($key, $prefix);
}



/**
* @author elnaz mojahedi
* for convering old translaton array in translation.inc.php to words table
*/
function wsfTranslation2Words($___translations , $prefix='VN_'){

	global $_ws;
	
	foreach($___translations as $k=>$val)
	{
		$columnsValues['key'] = str_replace($prefix , '' , $k);
		$columnsValues['related_item'] = md5($columnsValues['key']);
		
		foreach($val as $k2=>$val2)
		{
			$langId = wsfGetLanguageIdByShortName($k2);
	
			$columnsValues['language_id'] = $langId;
			
			$columnsValues['value'] = $val2;
			
			//cmfcHtml::printr($_ws['physicalTables']['words']['tableName']);
			
			cmfcMySql::insert($_ws['physicalTables']['words']['tableName'], $columnsValues);
	
		}
	}
}



/**
* register log with detail info about when and where and how it registered
*/
function wsfLog($text,$fileName=null) {
	global $_ws;
	$__logFileName='log.txt';//the default file
	if (!is_null($fileName)) {
		$__logFileName=$fileName;
	}
	if ($_ws['debugModeEnabled']) {
		$f=$_ws['siteInfo']['path'].'/'.$_ws['directoriesInfo']['tempFolderRPath'].'/'.$__logFileName;
		
		//cmfcHtml::printr($_ws['siteInfo']['path'].'/'.$_ws['directoriesInfo']['tempFolderRPath'].'/'.$__logFileName);
		
		return file_put_contents($f,"*******".date('Y-m-d H:i:s').":".$_SERVER['REQUEST_URI'].":".$_SERVER['DOCUMENT_ROOT'].':'.__FILE__."\n".$text."\n",FILE_APPEND);
	}
}

/**
* clear the last defined log file
*/
function wsfLogClear($fileName=null) {
	global $_ws;
	$__logFileName='log.txt';//the default file
	if (!is_null($fileName)) {
		$__logFileName=$fileName;
	}
	if (empty($__logFileName)) $__logFileName='log.txt';
	if ($_ws['debugModeEnabled']) {
		$f=$_ws['siteInfo']['path'].'/'.$_ws['directoriesInfo']['tempFolderRPath'].'/'.$__logFileName;
		return file_put_contents($f ,'');
	}
}



/**
* @author elnaz mojahedi
*/
function wsfGetPageWords($outsideAdmin = false){
	global $translation;
	return $translation->getPageWords($outsideAdmin);
}



function wsfExcludeQueryStringVars($qsVars,$resourceType='get') {
		
		$qsVars[]= 'pageType';
		$qsVars[]= 'sectionName';
		//([^\?]*)\?(([^&=]*)=([^&=]*)&?)*
		if ($resourceType=='request_uri') {
			if (preg_match('/([^\\?]*)\\?(([^&=]*)=([^&=]*)&?)*/', $_SERVER['REQUEST_URI'], $regs)) {
				$result = $regs[1];
			} else {
				$result = "";
			}
		} elseif ($resourceType=='get') {
			$get=$_GET;
			foreach ($qsVars as $qsVar) {
				unset($get[$qsVar]);
			}
			return cmfcUrl::arrayToQueryString($get);
		}
		
		trigger_error('$resourceType parameter only accept ("get","request_uri") but you entered "'.$resourceType.'"',E_USER_ERROR);
	}
	
function wsfSetSettingVars()
{
	global $_ws,$_insideAdmin;
	$settings = cmfcMySql::getRows($_ws['physicalTables']['settings']['tableName']);
	if($settings)
	{
		foreach($settings as $setting)
		{
			if($setting['key']=="debugModeEnabled" && $_ws['configurations']['debugModeEnabled'])
				$_ws['configurations']['debugModeEnabled'] = $setting['value'];
			$_ws['settings'][$setting['key']]=$setting['value'];
		}
	}
	
	if($_ws['configurations']['debugModeEnabled'])
	{
		cmfcMySql::setOption("debugEnabled", true);
		$_ws['startPageLoadTime'] = wsfGetMicroTimeAsFloat();
	}
	if (!$_insideAdmin)
	{
		foreach($settings as $setting)
		{
			if($setting['key']=="siteKeywords")
			{
				if ($setting['value'] != NULL)
				{
					$setting['value'] = str_replace(array('،',',',';'),',',$setting['value']);
					//$_ws['siteInfo']['keywords'] = explode(',',$setting['value']);
					$_ws['siteInfo']['keywords'] = $setting['value'];
				}
			}
			if($setting['key']=="siteDescription")
			{
				if ($setting['value'] != NULL)
				{
					$_ws['siteInfo']['description'] = $setting['value'];
				}
			}
			$_ws['settings'][$setting['key']] = $setting['value'];
			
			//cmfcHtml::printr($_ws['siteInfo']['keywords']);
		}	
	}

}

function wsfGetMicroTimeAsFloat()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function wsfPageRenderingTime()
{
	global $_ws;
	if(!$_ws['configurations']['debugModeEnabled'])
	{
		return;
	}
	else
	{
		$pageLoadTime = wsfGetMicroTimeAsFloat()-$_ws['startPageLoadTime'];
?>
		<table bgcolor="#FFFFFF" id="wordsList"  dir="<?=$langInfo['htmlDir']?>" class="table" width="50%"  align="center"  border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td class="table-header" align="center" >Page Load Time</td>
			</tr>
			<tr>
				<td align="center" nowrap="nowrap" class="field-title" style="width:35px; color:#FF3333" height="30px" >
				Page Load Time:<?=$pageLoadTime?>
				</td>
			</tr>
		</table>
<?php
	}
}

function wsfGetRegisteredQueries()
{
	global $_ws;
	if(!$_ws['configurations']['debugModeEnabled'])
	{
		return;
	}
	else
	{
		$queries = cmfcMySql::getRegisteredQueries();
?>
		<table bgcolor="#FFFFFF" id="wordsList"  dir="<?=$langInfo['htmlDir']?>" class="table" width="50%"  align="center"  border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td class="table-header" align="center" >Registered Queries</td>
			</tr>
			<tr>
				<td align="center" nowrap="nowrap" class="field-title" style="width:35px; color:#FF3333" height="30px" >
				Number of Query In page:<?=count($queries)?>
				</td>
			</tr>
			<tr>
				<td align="center" nowrap="nowrap" class="field-title" style="width:35px; color:#FF3333" height="30px" >
				<? cmfcHtml::printr($queries) ?>
				</td>
			</tr>
		</table>
<?php
	}
}
	
function wsfGetSectionInfo()
{
	global $_ws, $_cp , $sectionInfo;
	if(!$_ws['configurations']['debugModeEnabled'])
	{
		return;
	}
	else
	{
?>
		<table bgcolor="#FFFFFF" id="wordsList"  dir="<?=$langInfo['htmlDir']?>" class="table" width="50%"  align="center"  border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
			<tr>
				<td class="table-header" align="center" >Current Section Info</td>
			</tr>
			<tr>
				<td align="center" nowrap="nowrap" class="field-title" style="width:35px; color:#FF3333" height="30px" ><?=cmfcHtml::printr($sectionInfo);?></td>
			</tr>
		</table>
<?php
	}
}

function wsfGetIncludedFileName()
{
	global $fileToInclude, $_ws;
	if(!$_ws['configurations']['debugModeEnabled'])
	{
		return;
	}
	else
	{
		?>
        <table bgcolor="#FFFFFF" id="wordsList"  dir="<?=$langInfo['htmlDir']?>" class="table" width="50%"  align="center"  border="1" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
            <tr>
                <td class="table-header" align="center" >Current Included File</td>
            </tr>
            <tr>
                <td align="center" nowrap="nowrap" class="field-title" style="width:35px; color:#FF3333" height="30px" ><?=$fileToInclude?></td>
            </tr>
        </table>
		<?php
	}
}

function wsfGetCategoryTitleByCurrentLanguage($id, $myTree, $findAlternateLanguageTitle = 1){
	global $_ws;
	$value = wsfGetCategoryInfoByCurrentLanguage($id, $myTree);
	//cmfcHtml::printr($value);

	$title = $value[ $myTree->_titleColumnName ];

	if (!$title && $findAlternateLanguageTitle ){
		$row = cmfcMySql::load(
			$_ws['physicalTables']['categoryLanguages']['tableName'],
			$_ws['physicalTables']['categoryLanguages']['columns']['categoryId'],
			$id
		);
		
		//echo mysql_error();
		//cmfcHtml::printr($row);
		if ($row)
			$title = $row[ $myTree->_titleColumnName ];
		
		if (!$title){
			$row = cmfcMySql::load($myTree->tableName, 'id', $id);
			$title = $row[ $myTree->_titleColumnName ];
		}
	}
	return $title;
}

function wsfGetCategoryPathByLang($treeTableName, $categoryID, $fields =array('id','name'), $pathTitle = 'name', $sep='&raquo '){
	global $dbConnLink;
	global $_ws;
	
	@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
	$db->conn=&$dbConnLink;
	//if (!isset($categoryPathTree)){
	$categoryPathTree = cmfcHierarchicalSystem::factory('dbOld',
		array(
			'tableName'=>$treeTableName,
			'dbInstance'=>&$db,
			'prefix'=> ''
		)
	);
	$categoryPath='';
	$separator='';
	$root = $categoryPathTree->getRootNodeId();
	$where = array('and'=>array("`$fields[0]`<> $root"));
	if ($category=cmfcMySql::load($treeTableName, $fields[0], $categoryID)) {
		$categoryPathTree->parents($category[$fields[0]], $fields, $where);
		while ($category=$categoryPathTree->nextRow()) {
			
			$categoryPathTree->setTitleColumnName($pathTitle);
			$title = wsfGetCategoryTitleByCurrentLanguage($category[ $fields[0] ], $categoryPathTree);
			
			$categoryPath .= "$separator$title";
			if (empty($separator)) $separator=$sep;
		}
	}
	return $categoryPath;
}

function wsfCheckForCategoryLanguageData($referringId, $categoryId){
	global $_ws;
	$categoryLanguageData = cmfcMySql::getRows(
		$_ws['physicalTables']['categoryLanguages']['tableName'],
		$_ws['physicalTables']['categoryLanguages']['columns']['referringId'],
		$referringId
	);
	//cmfcHtml::printr( array($referringId, $categoryId) );
	//cmfcHtml::printr( $categoryLanguageData);
	//echo mysql_info();
	
	if ($categoryLanguageData){
		foreach ($categoryLanguageData as $data){
			$data = cmfcMySql::convertColumnNames($data, $_ws['physicalTables']['categoryLanguages']['columns']);
			$data['categoryId'] = $categoryId;
			$dataId = $data['id'];
			unset($data['id']);
			$data = cmfcMySql::convertColumnNames($data, $_ws['physicalTables']['categoryLanguages']['columns']);
			cmfcMySql::update(
				$_ws['physicalTables']['categoryLanguages']['tableName'],
				$_ws['physicalTables']['categoryLanguages']['columns']['id'], 
				$data, 
				$dataId
			);
			//echo mysql_error();
			wsfEmptyRefferingId($dataId);
		}
	}
}

function wsfEmptyRefferingId($dataId){
	global $_ws;
	$query = "UPDATE ".$_ws['physicalTables']['categoryLanguages']['tableName']." SET ".
		"`".$_ws['physicalTables']['categoryLanguages']['columns']['referringId']."` = NULL ".
		" WHERE `".$_ws['physicalTables']['categoryLanguages']['columns']['id']."` = '".$dataId."'";
	cmfcMySql::exec($query);
}

function wsfClearInvalidCategoryLanguageData(){
	global $_ws;
	$query = "SELECT * FROM ".$_ws['physicalTables']['categoryLanguages']['tableName'].
		" WHERE `".$_ws['physicalTables']['categoryLanguages']['columns']['referringId']."` IS NOT NULL ";
	
	$invalidData = cmfcMySql::getRowsCustom($query);
	//echo $query , '<br />', mysql_error(), '<br />';
	
	if ($invalidData){
		foreach ($invalidData as $data){
			$data = cmfcMySql::convertColumnNames($data, $_ws['physicalTables']['categoryLanguages']['columns']);
			$dataId = $data['id'];
			if ($data['referringId'] == $data['categoryId']){
				wsfEmptyRefferingId($dataId);
				continue;
			}
			$dataId = $data[ $_ws['physicalTables']['categoryLanguages']['columns']['id'] ];
			cmfcMySql::delete(
				$_ws['physicalTables']['categoryLanguages']['tableName'],
				$_ws['physicalTables']['categoryLanguages']['columns']['id'], 
				$dataId
			);
			
			echo "data with ID: ".$dataId." deleted from language data <br />";
		}
	}
}

function wsfDeleteCategoryLanguageData($id){
	global $_ws;
	return cmfcMySql::delete(
		$_ws['physicalTables']['categoryLanguages']['tableName'],
		$_ws['physicalTables']['categoryLanguages']['columns']['categoryId'],
		$id,
		NULL
	);
}

function wsfGetCategoryInfoByCurrentLanguage($id, $myTree){
	global $_ws, $translation;
	$langInfo = $translation->languageInfo;
	
	$originalCategoryInfo = cmfcMySql::load($myTree->tableName, 'id', $id);
	
	$query = "SELECT * FROM ".$_ws['physicalTables']['categoryLanguages']['tableName']." WHERE ".
		$_ws['physicalTables']['categoryLanguages']['columns']['categoryId']." = '".$id."' AND ".
		$_ws['physicalTables']['categoryLanguages']['columns']['languageId']." = '".$langInfo['id']."'";
	$value = cmfcMySql::loadCustom($query);
	//cmfcHtml::printr($value);
	$value = cmfcMySql::convertColumnNames($value, $_ws['physicalTables']['categoryLanguages']['columns']);
	
	if ($value){
		unset ($value['id']);
		foreach ($value as $key=>$val){
			$originalCategoryInfo[$key] = $val;
		}
	}
	
	return $originalCategoryInfo;
}

function wsfGetOverrideValues($uniqeId){
	global $_ws;
	$overrideQuery = "SELECT * FROM ".$_ws['physicalTables']['overrideValues']['tableName'].
		" WHERE `".$_ws['physicalTables']['overrideValues']['columns']['key']."` = '".$uniqeId."' ";
	
	$values = cmfcMySql::getRowsCustom($overrideQuery);
	
	$overrideValues = '';
	if ($values){
		foreach ($values as $value){
			$overrideValues [$value['title'] ] = $value;
		}
	}
	//cmfcHtml::printr($overrideValues);
	return $overrideValues;
}

function wsfConvertOverrideValuesForEditAction($values){
	$editValues = '';
	if ($values){
		foreach ($values as $fieldName => $value){
			$editValues[ $fieldName ] = $value['value'];
		}
	}
	//cmfcHtml::printr($editValues);
	return $editValues;
}

function wsfSaveOverrideValues($_commonColumns, $uniqeId){
	global $_ws, $_cp;
	
	$overrideColumnsValues = '';
	$emptyColumnsValues = '';
	$overrideActions = 0;
	//cmfcMySql::setOption('debugEnabled', true);
	//cmfcMySql::clearRegisteredQueries();
	
	$newColumnsValues = cmfcMySql::convertColumnNames(
		$_commonColumns, 
		$_cp['sectionInfo']['tableInfo']['columns']
	);
	
	

	if ($newColumnsValues){
		cmfcMySql::update(
			$_cp['sectionInfo']['tableInfo']['tableName'],
			$_cp['sectionInfo']['tableInfo']['columns']['relatedItem'],
			$newColumnsValues,
			$uniqeId
		);
	}
	
}
/*
function wsfDeleteOverrideValues($uniqeId, $sectionName){
	global $_cp;
	$tableInfo = $_cp['sectionsInfo'][$sectionName]['tableInfo'];
	$result=cmfcMySql::deleteWithMultiKeys(
		$tableInfo['tableName'],
		array(
			$tableInfo['columns']['relatedItem'] => $uniqeId,
			$tableInfo['columns']['sectionName'] => $sectionName,
		),
		NULL
	);
}
*/
function wsfGetNextEditableLang($currLangId){
	global $_ws;
	$query = "SELECT id FROM ".$_ws['physicalTables']['languages']['tableName'];
	$items = cmfcMySql::getRowsCustom($query);
	if($items){
		foreach($items as $key=>$item){
			if($currLangId == $item['id']){
				if(isset($items[$key+1])){
					$nextLang = $items[$key+1]['id'];
				}else{
					$nextLang = $items[0]['id'];
				}
			}
		}
	}
	return $nextLang;
}

function wsfPrintMessages($messages)
{
	global $translation;
	?> <div id="pageMessages"> <?php
	if (is_array($messages)) 
	{
		foreach ($messages as $type => $messageList)
		{
			
			$class = '';
			if ($type == 'errors'){
				$class = 'errorBox';
				//$class = 'err';
			}
			elseif ($type == 'messages'){
				$class = 'messageBox';
				//$class = 'ok';
			}
			elseif ($type == 'alert'){
				$class = 'alert';
				//$class = 'info';
			}
			else{
				$class = '';
			}
			?>
            <div class="<?=$class?>" dir="<?=$translation->languageInfo['direction']?>" align="<?=$translation->languageInfo['align']?>">
				 <?php 
                echo implode('<br />',$messageList);
                ?>
            </div> <?php
		}
	}
	?></div> <?php 
}

function wsfPrintChangeDateFunction(){
	if (!defined(wsfjChangeDate)){
		define(wsfjChangeDate, 1);
		?>
		<script language="javascript" >
		var nowjalali = new Array();
		var nowgregorian = new Array();
		nowjalali[1] = '<?=cmfcJalaliDateTime::smartGet('Y', 'now', 0);?>';
		nowjalali[2] = '<?=cmfcJalaliDateTime::smartGet('m', 'now', 0);?>';
		nowjalali[3] = '<?=cmfcJalaliDateTime::smartGet('d', 'now', 0);?>';
		nowjalali[4] = '<?=cmfcJalaliDateTime::smartGet('H', 'now', 0);?>';
		nowjalali[5] = '<?=cmfcJalaliDateTime::smartGet('i', 'now', 0);?>';
		nowjalali[6] = '<?=cmfcJalaliDateTime::smartGet('s', 'now', 0);?>';
		
		
		nowgregorian[1] = '<?=date('Y');?>';
		nowgregorian[2] = '<?=date('m');?>';
		nowgregorian[3] = '<?=date('d');?>';
		nowgregorian[4] = '<?=date('H');?>';
		nowgregorian[5] = '<?=date('i');?>';
		nowgregorian[6] = '<?=date('s');?>';
		
		function wsfjChangeDate(baseInputName, dateTime){
			yearElement = document.getElementById(baseInputName+'[year]');
			monthElement = document.getElementById(baseInputName+'[month]');
			dayElement = document.getElementById(baseInputName+'[day]');
			hourElement = document.getElementById(baseInputName+'[hour]');
			minuteElement = document.getElementById(baseInputName+'[minute]');
			secondElement = document.getElementById(baseInputName+'[second]');
			typeElement = document.getElementById(baseInputName+'[type]');
			
			if (dateTime == 'now'){
				//alert('now'+typeElement.value);
				nowArray 		= eval('now'+typeElement.value);
				currentYear 	= nowArray[1];
				currentMonth 	= nowArray[2];
				currentDay 		= nowArray[3];
				currentHour 	= nowArray[4];
				currentMinute 	= nowArray[5];
				currentSecond 	= nowArray[6];
			}
			if (dateTime == 'reset'){
				currentYear 	= '';
				currentMonth 	= '';
				currentDay 		= '';
				currentHour 	= '';
				currentMinute 	= '';
				currentSecond 	= '';
			}
			//alert(document.getElementById(baseInputName+'[year]') );
			
			if (yearElement){
				SelectByValue(yearElement, currentYear);
			}
			if (monthElement){
				SelectByValue(monthElement, currentMonth);
			}
			if (dayElement){
				SelectByValue(dayElement, currentDay);
			}
			if (hourElement){
				SelectByValue(hourElement, currentHour);
			}
			if (minuteElement){
				SelectByValue(minuteElement, currentMinute);
			}
			if (secondElement){
				SelectByValue(secondElement, currentSecond);
			}
		}
		
		function SelectByValue(element, value){
			for (var i=0; i < element.length; i++){
				if (element.options[i].value == value){
					element.selectedIndex = i;
				}
			}
		}
		</script>
		 <?php 
	}
}

function wsfConvertPostedDataToStandardPersianCharacters($postedData)
{
	if(is_array($postedData))
	{
		foreach($postedData as $key=>$item)
		{
			$convertedData[$key] = wsfConvertPostedDataToStandardPersianCharacters($item);
		}
		return $convertedData;
	}
	else
	{
		return cmfcString::convertToStandardPersianCharacters($postedData);
	}
}
#ADDED BY MEHDI KAMARI FOR REPAIR####################\
function wsfGetForTheme($fileRelativePath) {
	global $_ws;
	
	$fileAddress=$fileRelativePath;
	if ($_ws['siteInfo']['themeName']!='default' and $_ws['siteInfo']['themeName']!='') {
		$fileAddress='';
		$pathInfo=pathinfo($fileRelativePath);
		$pathInfo['dirname']=str_replace('interface/','',$pathInfo['dirname']);
		$fileAddress='interface/themes/'.$_ws['siteInfo']['themeName'].'/'.$pathInfo['dirname'].'/'.$pathInfo['basename'];
		if (!file_exists($fileAddress))
			$fileAddress=$fileRelativePath;
	}
	
	return $fileAddress;
}



function wsfPagingPrepareUrl($obj,$cmd,$params) {
	return wsfPrepareUrl($params['url']);
}

################################################
/*function wsfLoadXinha($myEditors,$imagesFolder,$theme=null) 
	{
		foreach ($myEditors as $id=>$editor) 
			{
				if (!empty($id)) 
					{
						$editors[]=$id;
						if ($editor['direction']=='rtl') $rtlEditors[]=$id;
					}
			}
	}

*/




function wsfGetSectionNameByNodeIdVer2($nodeInfo=null) {
	global $_ws;
	
	if ($nodeInfo['id']==1)
		return NULL;
	
	if ($nodeInfo['internal_name']!='' && $nodeInfo['internal_name']!=NULL)
		return $nodeInfo['internal_name'];
	else
	{
		foreach ($_ws["Main_Tree_Nodes_Details"] as $sectionName=>$mainNodeInfo) 
		{
			if ($mainNodeInfo['id']==$nodeInfo['parent_id'])
			{
				return wsfGetSectionNameByNodeIdVer2($mainNodeInfo);
				break;
			}
		}
	}
}


function wsfConvertDateTimeDropDownArrayToDateTimeString($dateTimeArray, $format="Y-m-d H:i:s", $forcibleItems=array('year'))
{
	foreach($forcibleItems as $forcibleItem)
	{
		if(!$dateTimeArray[$forcibleItem])
		{
			return NULL;
		}
	}
	
	$sortedDateArray = array(
		'year' => $dateTimeArray['year'],
		'month' => $dateTimeArray['month'],
		'day' => $dateTimeArray['day'],
		'type' => $dateTimeArray['type'],
	);
	
	foreach($sortedDateArray as $key=>$dateItem)
	{
		if($key != 'type')
		{
			if($dateItem)
			{
				$dateString .= $dateItem."-";
			}
			else
			{
				if(in_array($key, array('month', 'day')))
				{
					$dateString .= "1-";
				}
				else
				{
					$dateString .= "0-";
				}
			}
		}
	}
	$dateString = substr($dateString, 0, strlen($dateString)-1);
	
	
	if(isset($dateTimeArray['hour']))
	{
		$sortedTimeArray = array(
			'hour' => $dateTimeArray['hour'],
			'minute' => $dateTimeArray['minute'],
			'second' => $dateTimeArray['second'],
		);

		foreach($sortedTimeArray as $key=>$timeItem)
		{
			if($key != 'type')
			{
				if($timeItem)
				{
					$timeString .= $timeItem.":";
				}
				else
				{
					if(in_array($key, array('minute', 'second')))
					{
						$timeString .= "00:";
					}
				}
			}
		}
		$timeString = " ".substr($timeString, 0, strlen($timeString)-1);
	}
	
	$dateTimeString = $dateString.$timeString;

	if($dateTimeArray['type']=='jalali')
	{
		$dateTimeResult = date($format, cmfcJalaliDateTime::strtotime($dateTimeString));
	}
	else
	{
		$dateTimeResult = date($format, strtotime($dateTimeString));
	}

	return $dateTimeResult;
}



function wsfGetAliasUrl($alias) {
	global $urlObject;
	return $urlObject->getAliasUrl($alias);
}

function wsfGetUrlAlias($url) {
	global $urlObject;
	return $urlObject->getUrlAlias($url);
}
function wsfRegisterUrlAlias($url,$alias, $alternativeUrls = NULL) {
	global $urlObject;
	return $urlObject->registerUrlAlias($url,$alias, $alternativeUrls);
}

function wsfDeleteUrl($url) {
	global $urlObject;
	return $urlObject->deleteUrlAlias($url);
}

function wsfUpdateUrlAlias($url, $alias) {
	global $urlObject;
	return $urlObject->updateUrlAlias($url, $alias);
}

function wsfManipulateAlias($oldAliasPart, $newAliasPart) {
	global $urlObject;
	return $urlObject->manipulateAlias($oldAliasPart, $newAliasPart);
}

/*
function wsfPrepareUrl($url) {
	global $urlObject;
	return $urlObject->prepareUrl($url);
}
function wsfRepairUrl($url)
{
	global $urlObject;
	return $urlObject->repairAndOrderUrl($url);
}

function wsfPrepareGet($get) {
	global $urlObject;
	return $urlObject->prepareGet($get);
}
*/

function wsfRepairUrl($url)
{
	global $urlObject;
	return $urlObject->repairAndOrderUrl($url);
		
	$urlSections = parse_url($url);
	if($urlSections['query'])
	{
		$qsSections = explode("&", $urlSections['query']);
		if($qsSections)
		{
			foreach($qsSections as $qsSection)
			{
				if($qsSection)
				{
					$qsItemArray = explode("=", $qsSection);
					if(!empty($qsItemArray[0]) && !empty($qsItemArray[1]))
						$qsArray[$qsItemArray[0]] = $qsItemArray[1];
				}
			}
		}
	}
	
	if($qsArray)
	{
		foreach($qsArray as $qsItemKey=>$qsItemValue)
		{
			$newQsArray[] = $qsItemKey."=".$qsItemValue;
		}
		
		$newQs = implode("&", $newQsArray);
	}
	
	$newUrl = $urlSections['path']."?".$newQs.($urlSections['fragment'] ? "#":"").$urlSections['fragment'];

	return $newUrl;
}

function wsfPrepareUrl($url) {
	global $urlObject;
	return $urlObject->prepareUrl($url);
}

function wsfPrepareUrlCustom($url) {
	global $isLanguageSet, $_ws, $langInfo;
	
	$faqCategoriesTable = $_ws['physicalTables']['faqCategories'];
	$introductionsTable = $_ws['physicalTables']['introductions'];
	$guaranteesTable = $_ws['physicalTables']['productGuarantees'];
	$faqsTable = $_ws['physicalTables']['faq'];
	$productTypesTable = $_ws['physicalTables']['productTypes'];
	$productsTable = $_ws['physicalTables']['products'];
	
	//cmfcMySql::clearRegisteredQueries();
	//cmfcMySql::setOption('debugEnabled', true);
	//return $url;
	$orgUrl=$url;
	//return $url;
	
	if (strpos(strtolower($url), strtolower('http://'))===false) 
	{
		if (preg_match_all('/([^?&=]+)=([^?&=]+)/', $url, $regs,PREG_SET_ORDER)) 
		{
			if (preg_match('/(^|\/)([^.\/\?]*\.[^.\/\?]*)/', $url, $myregs)) 
			{
				$scriptFile = $myregs[2];
			}
			$convertedUrl="";
			
			if (!empty($scriptFile)) {
				$convertedUrl.="/cn/".$scriptFile;
			}
			
			if(preg_match('/id=([^?&=]+)/', $url, $myRegs2))
			{
				$idSet = true;
			}
			
			foreach ($regs as $reg) {
				if ($reg[1]!='qs') {
					if ($reg[1]=='sn') 
					{
						if($reg[2] != 'home')
						{
							if(in_array($reg[2], array('advancedSearch')))
								$qsvn[1] = 'advancedSearch';
							elseif($reg[2]!='products')
								$qsvn[0] = $reg[2];
						}
							
						if($isLanguageSet)
							if((!$_ws['siteInfo']['subdomainName'] || $_ws['siteInfo']['subdomainName']=='design') && (in_array($reg[2], array('home', 'aboutUs', 'contactUs', 'features', 'order', 'portfolio'))))
								$qsvn[-1] = $_GET['lang'];
					}	
					elseif($reg[1]=='categoryId') 
					{
						$category = cmfcMySql::loadWithMultiKeys(
							$faqCategoriesTable['tableName'],
							array(
								$faqCategoriesTable['columns']['relatedItem'] => $reg[2],
								$faqCategoriesTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[1] = $category['url_name'];
					}	
					elseif($reg[1]=='introId') 
					{
						$intro = cmfcMySql::loadWithMultiKeys(
							$introductionsTable['tableName'],
							array(
								$introductionsTable['columns']['relatedItem'] => $reg[2],
								$introductionsTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[1] = $intro['url_name'];
					}	
					elseif($reg[1]=='in') 
					{
						$staticPage = cmfcMySql::loadWithMultiKeys(
							$staticPagesTable['tableName'],
							array(
								$staticPagesTable['columns']['internalName'] => $reg[2],
								$staticPagesTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[1] = $staticPage['url_name'];
					}	
					elseif($reg[1]=='staticId') 
					{
						$staticPage = cmfcMySql::loadWithMultiKeys(
							$staticPagesTable['tableName'],
							array(
								$staticPagesTable['columns']['relatedItem'] => $reg[2],
								$staticPagesTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[1] = $staticPage['url_name'];
					}	
					elseif($reg[1]=='guaranteeId') 
					{
						/*$guarantee = cmfcMySql::loadWithMultiKeys(
							$guaranteesTable['tableName'],
							array(
								$guaranteesTable['columns']['relatedItem'] => $reg[2],
								$guaranteesTable['columns']['languageId'] => $langInfo['id'],
							)
						);*/
						$qsvn[1] = $reg[2];
					}	
					elseif($reg[1]=='orderId') 
					{
						/*$guarantee = cmfcMySql::loadWithMultiKeys(
							$guaranteesTable['tableName'],
							array(
								$guaranteesTable['columns']['relatedItem'] => $reg[2],
								$guaranteesTable['columns']['languageId'] => $langInfo['id'],
							)
						);*/
						$qsvn[1] = $reg[2];
					}	
					elseif($reg[1]=='faqId') 
					{
						$faq = cmfcMySql::loadWithMultiKeys(
							$faqsTable['tableName'],
							array(
								$faqsTable['columns']['relatedItem'] => $reg[2],
								$faqsTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[2] = $faq['url_name'];
					}	
					elseif($reg[1]=='returnTo') 
					{
						$qsvn[1] = $reg[2];
					}	
					elseif($reg[1]=='email') 
					{
						$qsvn[1] = $reg[2];
					}	
					elseif($reg[1]=='username') 
					{
						$qsvn[1] = $reg[2];
					}	
					elseif($reg[1]=='activation_code') 
					{
						$qsvn[2] = $reg[2];
					}	
					elseif($reg[1]=='action') 
					{
						if($reg[2]=='logout')
							$qsvn[2] = $reg[2];
						elseif(in_array($reg[2], array('delete', 'increase', 'decrease', 'emptyBasket', 'addToBasket')))
							$qsvn[6] = 'basketAction/'.$reg[2];
						else
							$qsvn[6] = 'action/'.$reg[2];
					}	
					elseif($reg[1]=='basketProductId') 
					{
						$product = cmfcMySql::loadWithMultiKeys(
							$productsTable['tableName'],
							array(
								$productsTable['columns']['relatedItem'] => $reg[2],
								$productsTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[7] = $product['url_name'];
					}	
					elseif($reg[1]=='pt') 
					{
						if(in_array($reg[2], array('images', 'film', 'image', 'compare')))
							$qsvn[3] = $reg[2];
					}	
					elseif($reg[1]=='baseProduct') 
					{
						$product = cmfcMySql::loadWithMultiKeys(
							$productsTable['tableName'],
							array(
								$productsTable['columns']['relatedItem'] => $reg[2],
								$productsTable['columns']['languageId'] => $langInfo['id'],
							)

						);
						$qsvn[4] = 'base/'.$product['url_name'];
					}	
					elseif($reg[1]=='orderKey') 
					{
						$qsvn[1] = $reg[2];
					}	
					elseif($reg[1]=='productTypeId') 
					{
						$productType = cmfcMySql::loadWithMultiKeys(
							$productTypesTable['tableName'],
							array(
								$productTypesTable['columns']['relatedItem'] => $reg[2],
								$productTypesTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						
						$qsvn[0] = $productType['url_name'];
					}	
					elseif($reg[1]=='productId') 
					{
						$product = cmfcMySql::loadWithMultiKeys(
							$productsTable['tableName'],
							array(
								$productsTable['columns']['relatedItem'] => $reg[2],
								$productsTable['columns']['languageId'] => $langInfo['id'],
							)
						);
						$qsvn[1] = $product['url_name'];
					}	
					elseif($reg[1]=='type') 
					{
						$qsvn[2] = $reg[2];
					}	
					elseif($reg[1]=='viewMode') 
					{
						if($reg[2] != 'listView')
							$qsvn[2] = $reg[2];
					}	
					elseif($reg[1]=='sortOption') 
					{
						$qsvn[3] = $reg[2];
					}	
					elseif($reg[1]=='itemPerPages') 
					{
						$qsvn[4] = $reg[2];
					}	
					elseif($reg[1]=='id') 
					{
						$qsvn[4] = $reg[2];
					}	
					elseif($reg[1]=='lang') 
					{
						$qsvn[-1] = $reg[2];
					}	
					elseif($reg[1]=='pageNumber' && !$idSet)
					{
						$qsvn[5] = 'plist';
						$qsvn[6] = $reg[2];
					}
					elseif(strpos($reg[1], 'search')!==FALSE)
					{
						$qsvn2[] = $reg[1].'='.$reg[2];
					}
				}
			}
			//cmfcHtml::printr(cmfcMySql::getRegisteredQueries());
			//cmfcHtml::printr($qsvn);
			if(is_array($qsvn))
			{
				ksort($qsvn);
				$convertedUrl .= '/'.implode('/', $qsvn);
			}
			if(is_array($qsvn2))
			{
				$convertedUrl .= '?'.implode('&', $qsvn2);
			}
			
			if($convertedUrl == "?" || !$convertedUrl) $convertedUrl='/';
						
			return $convertedUrl;
		} 
		else 
		{
			if($url == "?") $url='/';
			return $url;
		}
	}
	return $url;
}

function wsfPrepareGet($get) {
	global $urlObject;
	return $urlObject->prepareGet($get);
}





function wsfFormatCurrency($number, $decimal='0'){
	$formatted = number_format($number, $decimal, '.', ',');
	$formatted = ereg_replace("[.]0+$", '', $formatted);
	if(strpos($formatted, '.')!==FALSE){
		$formatted = ereg_replace("0+$", '', $formatted);
	}
	return $formatted;
}

function wsfGetProductPriceForShow($number, $decimal='0')
{
	global $langInfo;
	$formattedPrice = wsfFormatCurrency($number, $decimal);
	if($langInfo['sName'] == 'fa')
		return cmfcString::convertNumbersToFarsi($formattedPrice);
	else
		return $formattedPrice;
}






########## BASKET AND ORDER FUNCTIONS(BEGIN) ###################>

function wsfGetOrderPrice($orderId, $userId)
{
	global $_ws;
	
	if(!isset($_ws['orderDetail'][$orderId]))
	{
		$orderDetailsTable = $_ws['physicalTables']['orderDetails'];
		$orderDetails = cmfcMySql::getRows($orderDetailsTable['tableName'], $orderDetailsTable['columns']['orderId'], $orderId);
		
		$totalPrice = 0;
		$orderWeight = 0;
		if($orderDetails)
		{
			foreach($orderDetails as $orderDetail)
			{
				
				$orderDetail = cmfcMySql::convertColumnNames($orderDetail, $orderDetailsTable['columns']);
				$orderPrice = wsfGetOrderDetailPrice($orderDetail['productId']);
				$totalPrice += $orderPrice['price'] * $orderDetail['quantity'];
				$orderWeight += $orderPrice['weight'] * $orderDetail['quantity'];
				$_ws['orderDetail'][$orderId]['items'][$orderDetail['id']] = array(
					'price' => $orderPrice['price'],
					'quantity' => $orderDetail['quantity'],
					'totalPrice' => $orderPrice['price'] * $orderDetail['quantity']
				);
			}
		}
		
		if(!is_array($userId))
		{
			$usersTable = $_ws['physicalTables']['webUsers'];
			$userInfo = cmfcMySql::load($usersTable['tableName'], $usersTable['columns']['id'], $userId);
		}
		else
			$userInfo = $userId;
			
		$_ws['orderDetail'][$orderId]['totalPrice'] = $totalPrice;
		$_ws['orderDetail'][$orderId]['postPrice'] = wsfGetPostPrice($orderWeight, $userInfo);
	}
	
	return $_ws['orderDetail'][$orderId];
}

function wsfGetOrderDetailPrice($productId)
{
	global $_ws;
	
	if(!isset($_ws['productPrice'][$productId]))
	{
		$productsTable = $_ws['physicalTables']['products'];
		
		$product = cmfcMySql::load($productsTable['tableName'], $productsTable['columns']['relatedItem'], $productId);
		$product = cmfcMySql::convertColumnNames($product, $productsTable['columns']);

		
		$_ws['productPrice'][$productId] = array('price' => $product['price'], 'weight' => $product['weight']);
	}
	
	return $_ws['productPrice'][$productId];
}

function wsfGetPostPrice($weight, $userInfo)
{
	global $_ws;
	
	$settingsTable = $_ws['physicalTables']['settings'];
	
	$settings = cmfcMySql::getRowsWithCustomIndex($settingsTable['tableName'], NULL, NULL, $settingsTable['columns']['key']);
	
	if($userInfo['inTehran'])
		$postPrice = $settings['postPriceTehran']['value'];
	elseif($weight<6)
	{
		$postPrice = $settings['postPriceUnder6Base']['value']+(($weight-1)*$settings['postPriceUnder6Unit']['value']);
	}
	else
	{
		$postPrice = $settings['postPriceUp6Base']['value']+(($weight-6)*$settings['postPriceUp6Unit']['value']);
	}
		
	$postPriceForUser = $postPrice*$_ws['siteSettingInfo']['customerUnit']/100;
	
	return array('postPriceCustomer' => $postPriceForUser, 'postPriceAll'=>$postPrice, 'customerUnit'=>$_ws['siteSettingInfo']['customerUnit']);
}

function wsfGetAllAvailablePAymentMethods(){
	global $_ws, $paymentInternalGateway;
	
	$onlineMethods = $paymentInternalGateway->getAvailableMethods();
	$sep = '';
	$methodsInternalNames = '';
	
	if ($onlineMethods){
		foreach ($onlineMethods as $key => $method){
			$allMethods[$key] = $method;
			$methodsInternalNames[] = $key;
			$sep = ', ';
		}
	}
	
	foreach ($_ws['virtualTables']['paymentTypes']['rows'] as $row){
		$row = cmfcMySql::convertColumnNames($row, $_ws['virtualTables']['paymentTypes']['columns']);
		
		if (!$row['onlineAccountId']){
			$allMethods[ $row['internalName'] ] = $row['name'];
			$methodsInternalNames[] = $row['internalName'];
			$sep = ', ';
		}
	}
	//cmfcHtml::printr( explode(',', $methodsInternalNames) );
	$methodsDetails = wsfGetVirtualRows(
		$_ws['virtualTables']['paymentTypes']['rows'],
		$_ws['virtualTables']['paymentTypes']['columns']['internalName'],
		$methodsInternalNames,
		'single',
		$_ws['virtualTables']['paymentTypes']['columns']['internalName']
	);
	
	return array($allMethods, $methodsDetails);
}

function wsfGetOrderTableForPrint($order, $orderDetails)
{
	global $_ws, $orderStatus, $langInfo;
	
	ob_start();
	if($orderDetails)
	{
	?>
	<table class="table" width="95%" cellpadding="2" cellspacing="0" style="border-collapse:collapse; font-size:11px;">
		<tr style="border:1px #666666 solid;background-color:#ddd;">
			<td colspan="3"><strong><?=wsfGetValue('VN_name')?></strong>: <?=$order['customerName']?></td>
			<td></td>
			<td><strong><?=wsfGetValue('VN_tel')?></strong>: <?=$order['customerTel']?></td>
		</tr>
		<tr style="border:1px #666666 solid;background-color:#ddd;">
			<td colspan="2"><strong><?=wsfGetValue('VN_email')?></strong>: <?=$order['customerEmail']?></td>
			<td colspan="2"><strong><?=wsfGetValue('VN_address')?></strong>: <?=$order['customerAddress']?></td>
			<td><strong><?=wsfGetValue('VN_date')?></strong>: <?=cmfcJalaliDateTime::smartGet('d M Y H:i', $order['registerDate'])?></td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr style="background-color:#CCCCCC;">
			<td style="border:1px #666666 solid;padding:3px;">#</td>
			<td style="border:1px #666666 solid;padding:3px;"><?=wsfGetValue('VN_productName')?></td>
			<td style="border:1px #666666 solid;padding:3px;"><?=wsfGetValue('VN_formPrice')?></td>
			<td style="border:1px #666666 solid;padding:3px;"><?=wsfGetValue('VN_count')?></td>
			<td style="border:1px #666666 solid;padding:3px;"><?=wsfGetValue('VN_totalPrice')?></td>
		</tr>
	<?php
		foreach($orderDetails as $orderDetailKey=>$orderDetailValue)
		{
			if(!$orderDetailValue['productId'])
				$orderDetailValue = cmfcMySql::convertColumnNames($orderDetailValue, $_ws['physicalTables']['orderDetails']['columns']);

			if(wsfIsOrderNotPaid($order))
			{
				$orderDetailPriceInfo = wsfGetOrderDetailPrice($orderDetailValue['productId']);
				$orderDetailValue['price'] = $orderDetailPriceInfo['price'];
				$orderDetailValue['totalPrice'] = $orderDetailValue['price'] * $orderDetailValue['quantity'];
			}

			$productTable = $_ws['physicalTables']['products'];
			//$productTable = $productsTable[$orderDetailValue['productType']];
			
			$orderProduct = cmfcMySql::loadWithMultiKeys(
				$productTable['tableName'], 
				array(
					$productTable['columns']['relatedItem'] => $orderDetailValue['productId'], 
					$productTable['columns']['languageId'] => $langInfo['id']
				)
			);
			$orderProduct = cmfcMySql::convertColumnNames($orderProduct, $productTable['columns']);
			
			?>
		<tr>
			<td style="border:1px #666666 solid;padding:3px;"><?=$orderDetailKey+1?></td>
			<td style="border:1px #666666 solid;padding:3px;"><?=$orderProduct['name']?></td>
			<td style="border:1px #666666 solid;padding:3px;">
				 <?php  
				if($order['discount']) 
				{
				 ?> 
					<span style="text-decoration: line-through; display:block; color:#AAA;"> <?php echo wsfGetProductPriceForShow($orderDetailValue['price']) ?> </span>
					<span><?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($orderDetailValue['price'], $order['discount'])) ?></span>
				 <?php  
				}
				else
				{
					echo wsfGetProductPriceForShow($orderDetailValue['price']);
				}
				?>
			</td>
			<td style="border:1px #666666 solid;padding:3px; text-align:center;">
				<?=cmfcString::convertNumbersToFarsi($orderDetailValue['quantity'])?>
			</td>
			<td style="border:1px #666666 solid;padding:3px;">
				 <?php  
				if($order['discount']) 
				{
				 ?> 
					<span style="text-decoration: line-through; display:block; color:#AAA;"> <?php echo wsfGetProductPriceForShow($orderDetailValue['totalPrice']) ?></span>
					<span> <?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($orderDetailValue['totalPrice'], $order['discount'])) ?></span>
				 <?php 
				}
				else
				{
					echo wsfGetProductPriceForShow($orderDetailValue['totalPrice']);
				}
				?>
			</td>
		</tr>
			<?php
		}
	?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="left" nowrap="nowrap"><?=wsfGetValue('VN_totalPrice')?></td>
			<td style="border:1px #666666 solid;padding:3px;">
				<strong>
				<?php 
				if($order['discount']) 
				{
				 ?> 
					<span style="text-decoration: line-through; display:block; color:#AAA;"> <?php echo wsfGetProductPriceForShow($order['totalPrice']) ?> </span>
					<span> <?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($order['totalPrice'], $order['discount'])) ?></span>
				 <?php  
				}
				else
				{
					echo wsfGetProductPriceForShow($order['totalPrice']);
				}
				?>
				</strong>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="left" nowrap="nowrap"><?=wsfGetValue('VN_postPrice')?></td>
			<td style="border:1px #666666 solid;padding:3px;">
				<strong>
					<span style="text-decoration: line-through; display:block; color:#AAA;"> <?php echo wsfGetProductPriceForShow($order['postPrice']) ?> </span>
					<span> <?php echo wsfGetProductPriceForShow($order['postCustomerUnitPrice']) ?> </span>
				</strong>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="left" nowrap="nowrap"><?=wsfGetValue('totalPrice')?></td>
			<td style="border:1px #666666 solid;padding:3px;">
				<strong>
					<span style="text-decoration: line-through; display:block; color:#AAA;"><?php echo wsfGetProductPriceForShow($order['totalPrice']+$order['postPrice']) ?></span>
					<span><?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($order['totalPrice'], $order['discount'])+$order['postCustomerUnitPrice']) ?></span>
				</strong>
			</td>
		</tr>
	</table>
	<?php
	}
	else
	{
		echo wsfGetValue('VN_basketIsEmpty');
	}
	$basketItemsTable = ob_get_contents();
	ob_end_clean();
	
	return $basketItemsTable;


}

function wsfGetOrderTableForPdf($order, $orderDetails)
{
	global $_ws, $orderStatus, $langInfo;
	
	ob_start();
	if($orderDetails)
	{
	?>
	<table class="table" width="500" cellpadding="2" cellspacing="0" style="border-collapse:collapse; font-size:11px;">
		
		<tr>
			<td colspan="5">
				<div style="font-size:16px;">
					<table width="100%">
						
						<tr style="background-color:#f2f2f2;"><td></td><td colspan="3"></td><td></td></tr>
						
						<tr style="background-color:#f2f2f2;">
							<td></td>
							<td><strong><?=wsfGetValue('VN_name')?>:</strong> <?=$order['customerName']?></td>
							<td><strong><?=wsfGetValue('VN_tel')?>:</strong> <?=$order['customerTel']?></td>
							<td><strong><?=wsfGetValue('VN_email')?>:</strong> <?=$order['customerEmail']?></td>
							<td></td>
						</tr>
						
						<tr style="background-color:#f2f2f2;"><td></td><td colspan="3"></td><td></td></tr>
						
						<tr style="background-color:#f2f2f2;">
							<td></td>
							<td colspan="2"><strong><?=wsfGetValue('VN_address')?></strong>: <?=$order['customerAddress']?></td>
							<td><strong><?=wsfGetValue('VN_date')?></strong>: <?=cmfcJalaliDateTime::smartGet('d M Y H:i', $order['registerDate'])?></td>
							<td></td>
						</tr>
						
						<tr style="background-color:#f2f2f2;"><td></td><td colspan="3"></td><td></td></tr>
					
					</table>
				</div>
			</td>
		</tr>
		
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr style="background-color:#555;font-size:22px; text-align:center;">
			<td style="color:#FFF;">#</td>
			<td style="color:#FFF;"><?=wsfGetValue('VN_productName')?></td>
			<td style="color:#FFF;"><?=wsfGetValue('VN_formPrice')?></td>
			<td style="color:#FFF;"><?=wsfGetValue('VN_count')?></td>
			<td style="color:#FFF;"><?=wsfGetValue('VN_totalPrice')?></td>
		</tr>
	<?php
		foreach($orderDetails as $orderDetailKey=>$orderDetailValue)
		{
			if(!$orderDetailValue['productId'])
				$orderDetailValue = cmfcMySql::convertColumnNames($orderDetailValue, $_ws['physicalTables']['orderDetails']['columns']);

			if(wsfIsOrderNotPaid($order))
			{
				$orderDetailPriceInfo = wsfGetOrderDetailPrice($orderDetailValue['productId']);
				$orderDetailValue['price'] = $orderDetailPriceInfo['price'];
				$orderDetailValue['totalPrice'] = $orderDetailValue['price'] * $orderDetailValue['quantity'];
			}

			$productTable = $_ws['physicalTables']['products'];
			//$productTable = $productsTable[$orderDetailValue['productType']];
			
			$orderProduct = cmfcMySql::loadWithMultiKeys(
				$productTable['tableName'], 
				array(
					$productTable['columns']['relatedItem'] => $orderDetailValue['productId'], 
					$productTable['columns']['languageId'] => $langInfo['id']
				)
			);
			$orderProduct = cmfcMySql::convertColumnNames($orderProduct, $productTable['columns']);
			
			?>
		<tr style="font-size:22px; text-align:center;">
			<td colspan="5">
		
			<table width="100%;">
				<tr>
					<td style="padding:5px;background-color:#f1f1f1;" bordercolor="#990066"><?=$orderDetailKey+1?></td>
					<td style="padding:5px;background-color:#f1f1f1;"><?=$orderProduct['name']?></td>
					<td style="padding:5px;background-color:#f1f1f1;">
						<?php 
						if($order['discount']) 
						{
						?>
							<span style="text-decoration: line-through; display:block; color:#AAA;"><?php echo wsfGetProductPriceForShow($orderDetailValue['price']) ?></span>
							<span><?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($orderDetailValue['price'], $order['discount'])) ?></span>
						<?php 
						}
						else
						{
							echo wsfGetProductPriceForShow($orderDetailValue['price']);
						}
						?>
					</td>
					<td style="padding:5px;background-color:#f1f1f1; text-align:center;">
						<?=cmfcString::convertNumbersToFarsi($orderDetailValue['quantity'])?>
					</td>
					<td style="padding:5px;background-color:#f1f1f1;">
						<?php 
						if($order['discount']) 
						{
						?>
							<span style="text-decoration: line-through; display:block; color:#AAA;"><?php echo wsfGetProductPriceForShow($orderDetailValue['totalPrice']) ?></span>
							<span><?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($orderDetailValue['totalPrice'], $order['discount'])) ?></span>
						<?php 
						}
						else
						{
							echo wsfGetProductPriceForShow($orderDetailValue['totalPrice']);
						}
						?>
					</td>
				</tr>
			</table>
			
			</td>
		</tr>
			<?php
		}
	?>
		<tr style="font-size:22px;">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="left" nowrap="nowrap"><?=wsfGetValue('VN_totalPrice')?></td>
			<td style="padding:3px; text-align:center;background-color:#EEE;">
				<strong>
				<?php 
				if($order['discount']) 
				{
				?>
					<span style="text-decoration: line-through; display:block; color:#AAA;"><?php echo wsfGetProductPriceForShow($order['totalPrice']) ?></span><br />
					<?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($order['totalPrice'], $order['discount'])) ?>
				<?php 
				}
				else
				{
					echo wsfGetProductPriceForShow($order['totalPrice']);
				}
				?>
				</strong>
			</td>
		</tr>
		<tr>
			<td colspan="5"></td>
		</tr>
		<tr style="font-size:22px;">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="left" nowrap="nowrap"><?=wsfGetValue('VN_postPrice')?></td>
			<td style="padding:3px; text-align:center; background-color:#EEE;">
				<strong>
					<span style="text-decoration: line-through; display:block; color:#AAA;"><?php echo wsfGetProductPriceForShow($order['postPrice']) ?></span><br />
					<?php echo wsfGetProductPriceForShow($order['postCustomerUnitPrice']) ?>
				</strong>
			</td>
		</tr>
		<tr>
			<td colspan="5"></td>
		</tr>
		<tr style="font-size:22px;">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td align="left" nowrap="nowrap"><?=wsfGetValue('totalPrice')?></td>
			<td style="padding:3px; text-align:center; background-color:#EEE;">
				<strong>
					<span style="text-decoration: line-through; display:block; color:#AAA;"><?php echo wsfGetProductPriceForShow($order['totalPrice']+$order['postPrice']) ?></span><br />
					<?php echo wsfGetProductPriceForShow(wsfGetPriceWithDiscount($order['totalPrice'], $order['discount'])+$order['postCustomerUnitPrice']) ?>
				</strong>
			</td>
		</tr>
	</table>
	<?php
	}
	else
	{
		echo wsfGetValue('VN_basketIsEmpty');
	}
	$basketItemsTable = ob_get_contents();
	ob_end_clean();
	
	return $basketItemsTable;


}

function wsfGetVirtualRows($rows, $key, $value, $mode = NULL, $indexBy = NULL){
	$result = false;
	if (is_null($mode))
		$mode = 'multi';
	
	if (!is_array($value))
		$values = array($value);
	else
		$values = $value;
	
	if ($rows){
		foreach ($rows as $row)
		{
			//var_dump($row[$key]);
			//var_dump($values);
			//cmfcHtml::printr(in_array( $row[$key], $values));
			if (in_array( $row[$key], $values) ){
				
				if ($indexBy)
				{
					//cmfcHtml::printr($indexBy);
					$result[ $row[$indexBy] ][] = $row;
				}
				else{
					$result[] = $row;
				}
			}
		}
	}
	if ($result ){
		if ($mode == 'single'){
			if ($indexBy){
				foreach($result as $index => $rows){
					$result[$index] = $rows[0];
				}
			}
			else{
				$result = $result[0];
			}
		}
	}
	//cmfcHtml::printr($result);
	
	return $result;
}

function wsfCreateBanksRadioBox($methodsDetails, $type)
{
	$typeArray = array(0=>"offline", 1=>"online");
	foreach($methodsDetails as $methodsDetailKey=>$methodsDetail)
	{
		if($typeArray[$methodsDetail['type']] != $type)
		{
			unset($methodsDetails[$methodsDetailKey]);
		}
	}
	
	?>
	<table border="0" width="100%" cellspacing="0" cellpadding="10">
		<tr>
		<?php
		foreach($methodsDetails as $methodDetails)
		{
			$key++;
			$checked = "";			
			if(!isset($_POST['payment']['paymentMethodType'][$type]) && $key==1)
				$checked = "checked";
			elseif($_POST['payment']['paymentMethodType'][$type] == $methodDetails['internal_name'])
			{
				$checked = "checked";
			}
			
			$methodDetails['description'] = preg_replace('/[0-9\-]+/', '<span dir="ltr">$0</span>', $methodDetails['description']);
			?>
			<td width="50%">
				<label for="payment[paymentMethodType][<?=$type.$key?>]"><img src="/interface/images/<?=$methodDetails['logo']?>" /></label>
				<br />
				<input type="radio" name="payment[paymentMethodType][<?=$type?>]" id="payment[paymentMethodType][<?=$type.$key?>]" value="<?=$methodDetails['internal_name']?>" <?=$checked?> />
				<label for="payment[paymentMethodType][<?=$type.$key?>]"><?=$methodDetails['name']?></label>
				<br />
				<?=$methodDetails['description']?>
			</td>
			<?php
			if($key%2 == 0)
			{
				echo "</tr><tr>";
			}
		}
		?>
		</tr>
	</table>
	<?php
}
########## BASKET AND ORDER FUNCTIONS(END) ###################>





function wsfGetUserMileages($userId)
{
	global $_ws;
	
	$ordersTable = $_ws['physicalTables']['orders'];
	$userPrizesTable = $_ws['physicalTables']['webUserPrizes'];
	
	$userPrizesQuery = "SELECT SUM(`".$userPrizesTable['columns']['prizeCredit']."`) AS userPrizes FROM `".$userPrizesTable['tableName']."` WHERE `".$userPrizesTable['columns']['userId']."`='".$userId."' AND `".$userPrizesTable['columns']['confirmed']."`='1'";
	$userPrizes = cmfcMySql::getColumnValueCustom($userPrizesQuery, 'userPrizes');
	
	$mileageRatio = $_ws['siteSettingInfo']['mileageRatio'];
	if(!$mileageRatio)
		$mileageRatio = 1000;
	
	$ordersQuery = "SELECT SUM(`".$ordersTable['columns']['totalPrice']."`)/(10*$mileageRatio) AS mileages FROM `".$ordersTable['tableName']."` WHERE `".$ordersTable['columns']['userId']."`='".$userId."'";
	$mileage = cmfcMySql::getColumnValueCustom($ordersQuery, 'mileages');

	return $mileage-$userPrizes; 
}


function wsfGetCloserPrizeForUser($userId, $mileage=NULL)
{
	global $_ws, $langInfo;
	
	$prizesTable = $_ws['physicalTables']['prizes'];
	
	if(!$mileage)
		$mileage = wsfGetUserMileages($userId);
		
	$prizesInfoAfter = "
		SELECT 
			*,
			ABS(`".$prizesTable['columns']['credit']."`-".$mileage.") AS prizeDiff,
			IF((`".$prizesTable['columns']['credit']."`-".$mileage.")<0, 1, 2) AS prizeSign
		FROM 
			`".$prizesTable['tableName']."`
		WHERE 
			`".$prizesTable['columns']['credit']."`-".$mileage.">=0
		ORDER BY prizeSign DESC, prizeDiff ASC
	";
	$prizesInfoBefore = "
		SELECT 
			*,
			ABS(`".$prizesTable['columns']['credit']."`-".$mileage.") AS prizeDiff,
			IF((`".$prizesTable['columns']['credit']."`-".$mileage.")<0, 1, 2) AS prizeSign
		FROM 
			`".$prizesTable['tableName']."`
		WHERE 
			`".$prizesTable['columns']['credit']."`-".$mileage."<0
		ORDER BY prizeSign ASC, prizeDiff ASC
	";
	
	//cmfcHTml::printr($prizesInfo);
	
	$prizesInfoAfter = cmfcMySql::loadCustom($prizesInfoAfter);
	$prizesInfoBefore = cmfcMySql::loadCustom($prizesInfoBefore);
	$prizesTable['columns']['prizeDiff'] = 'prizeDiff';
	$prizesTable['columns']['prizeSign'] = 'prizeSign';
	
	$prizesInfoAfter = cmfcMySql::convertColumnNames($prizesInfoAfter, $prizesTable['columns']);
	$prizesInfoBefore = cmfcMySql::convertColumnNames($prizesInfoBefore, $prizesTable['columns']);
	
	$prizesInfo = array(
		'before' => $prizesInfoBefore,
		'after' => $prizesInfoAfter 
	);
	return $prizesInfo;
	
}

function wsfPrepareProductUrl($productInfo)
{
	return ;
}

function wsfIsOrderNotPaid($orderInfo)
{
	$currentDate = getdate(strtotime('now'));
	$threeDaysAgo = date('Y-m-d H:i:s', mktime(0, 0, 0, $currentDate['mon'], $currentDate['mday']-2, $currentDate['year']));
	//cmfcHtml::printr(strtotime($threeDaysAgo));
	if(!$orderInfo['confirm'] && strtotime($orderInfo['registerDate'])<strtotime($threeDaysAgo))
		return true;
	else
		return false;
}


function wsfSendSms($mobileNumber, $smsBody, $logSms = NULL)
{
	global $smsServer, $_ws;
	
	$smsServer->connect();
	
	$senderNumber = array(
		$smsServer->senderPrefixNumber
	);
	
	if($mobileNumber[0]=='0')
		$mobileNumber = '98'.substr($mobileNumber, 1);
	
	$reciverNumbers = array(
		$mobileNumber
	);
	
	
	$smsResult = $smsServer->send($senderNumber, $reciverNumbers, $smsBody);

	if($logSms)
	{
		$smsInformationsTable = $_ws['physicalTables']['smsInformations'];
		$smsRecipientsTable = $_ws['physicalTables']['smsRecipients'];
		$smsStatusTable = $_ws['virtualTables']['smsStatus'];
		
		$_smsInformationColumns = array(
			'title' => $logSms['title'],
			'body' => $smsBody,
			'createDatetime' => date('Y-m-d H:i:s'),
			'recipientCount' => 1,
		);
		$smsInformationColumnsValues = cmfcMySql::convertColumnNames($_smsInformationColumns, $smsInformationsTable['columns']);
		$smsInformationResult = cmfcMySql::insert($smsInformationsTable['tableName'], $smsInformationColumnsValues);
		$smsInformationId = cmfcMySql::insertId();
		
		if($smsInformationResult)
		{
			if($smsResult[0] > 1000)
			{
				$inProccessRealStatus = cmfcMySql::getVirtualColumnValue(
					'inProccess',
					$smsStatusTable['rows'],
					$smsStatusTable['columns']['internalName'],
					$smsStatusTable['columns']['realStatus']
				);
			}
			else
			{
				$inProccessRealStatus = cmfcMySql::getVirtualColumnValue(
					'notSend',
					$smsStatusTable['rows'],
					$smsStatusTable['columns']['internalName'],
					$smsStatusTable['columns']['realStatus']
				);
			}
			
			$_smsRecipientColumns = array(
				'userId' => $logSms['userId'],
				'number' => $mobileNumber,
				'realStatus' => $inProccessRealStatus[0],
				'smsInformationId' => $smsInformationId,
				'messageId' => $smsResult[0],
				'insertDatetime' => date('Y-m-d H:i:s'),
				'sendDatetime' => date('Y-m-d H:i:s'),
			);
			$smsRecipientColumnsValues = cmfcMySql::convertColumnNames($_smsRecipientColumns, $smsRecipientsTable['columns']);
			
			$smsRecipientResult = cmfcMySql::insert($smsRecipientsTable['tableName'], $smsRecipientColumnsValues);
		}
	}
}


function wsfGetPriceWithDiscount($price, $discount)
{
	return ((100-$discount)*$price)/100;
}

function wsfGetCategoryTitleByCurrentLanguageV2($categoryId){
	global $_ws, $langInfo;
	
	$catInfo = cmfcMySql::loadWithMultiKeys(
		$_ws['physicalTables']['categoryLanguages']['tableName'],
		array(
			$_ws['physicalTables']['categoryLanguages']['columns']['categoryId']=>$categoryId,
			$_ws['physicalTables']['categoryLanguages']['columns']['languageId']=>$langInfo['id']
		)
	);
	return $catInfo['name'];
}
