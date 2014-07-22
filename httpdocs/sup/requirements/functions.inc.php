<?php
/**
 * control panel specific functions
 * name of this functions should start with cpf like cpfGetSiteSectionId
 * 
 * @author Sina Salek
 * @package [D]/admin/requirements
 */

function cpfGetFileUploadAuto($fieldName,$value,$basePath='',$baseUrl='',$options=array()) {
	global $HTTP_POST_VARS, $lang,$_ws;
	
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

function cpfDrawBoxes($addibleItem, $templates){
	return wsfDrawBoxes($addibleItem, $templates);
}

/**
* prints require javascript for having sortable (orderable) rows
* @return string 
* @author Sina Salek
*/
function cpfOrderSortingFunctions() {?>

<script type='text/javascript'>
	var whichrow=false
	var rowLastBackground;
	
	function thisrow(x){
		if (whichrow) whichrow.style.backgroundColor=rowLastBackground;
		whichrow = x;
		rowLastBackground=whichrow.style.backgroundColor;
		whichrow.style.backgroundColor='#CFCAFF';
	}
	
	function selectRowById(id) {
		whichrow = document.getElementById(id);
	}
	
	function changeOrderNumber(rowId) {
		var orderNumberField;
		var orderNumber=0;
					
		var currentRow=document.getElementById(rowId);
		currentRow=currentRow.parentNode.firstChild;
		do {
			//alert('rows['+currentRow.id+'][orderNumber]');
			orderNumberField=document.getElementById('rows['+currentRow.id+'][orderNumber]');
			
			if (orderNumberField) {
				orderNumber++;
				orderNumberField.value=orderNumber;
			}
			
			currentRow=currentRow.nextSibling;
		} while(currentRow)
	}

	function moverow(x){
		//var whichrow=document.getElementById(id);
		var nearSibling;
		if (whichrow) {
			if (x=='up'&&whichrow.previousSibling) {
				nearSibling=getNearSiblingByTagName(whichrow,'TR','prev');
				if (nearSibling.id!='')
					whichrow.parentNode.insertBefore(whichrow,nearSibling);
			}
			else if (x=='down'&&whichrow.nextSibling) {
				nearSibling=getNearSiblingByTagName(whichrow,'TR','next');
				if (nearSibling.id!='')
					whichrow.parentNode.insertBefore(nearSibling,whichrow);
			}
			else if (x=='first') {
				whichrow.parentNode.insertBefore(whichrow,whichrow.parentNode.firstChild);
			} else if (x=='last') {
				whichrow.parentNode.insertBefore(whichrow,null);
			}
			changeOrderNumber(whichrow.id);
		} else
			alert("لطفا ابتدا ردیف مورد نظر را انتخاب نمایید!");
	}
	
	//getNearSiblingByTagName(whichrow,whichrow.tagName,'next')
	//getNearSiblingByTagName(whichrow,whichrow.tagName,'prev')
	/*
		When there is a #text between node, using only nextSibling & previousSibling is not enough,
		this function gives near Sibling by tag type.
	*/
	function getNearSiblingByTagName(elm,tagName,direction) {
		var nearSibling=elm;
		var found=false;
		do {
			if (direction=='next')
				nearSibling=nearSibling.nextSibling;
			else
				nearSibling=nearSibling.previousSibling;
			if (nearSibling && nearSibling!=elm) {
				if (nearSibling.tagName==tagName) found=true;
			} else {
				nearSibling=elm;
				found=true;
			}
		} while (found==false);
		
		return nearSibling;
	}
</script> 

<?php 
}
/**
* gets two link buttons for moving rows up and down as html
* @return string html
* @author Sina Salek
*/ 
function cpfOrderSortingButtons() {
	return '
	<a href="" onclick="moverow(\'up\');return false;">
		<img src="interface/images/arrow_up.gif" border="0"/>
	</a>
	<a href="" onClick="moverow(\'down\');return false;">
		<img src="interface/images/arrow_down.gif" border="0"/>
	</a>
	';
}

/**
* gets hidden field of row order number
* @param integer $id id of row
* @param integer $orderNumber order number of row
* @return string html
* @author Sina Salek
*/
function cpfOrderSortingField($id, $orderNumber) {
	return '<input size="5" type="hidden" name="rows['.$id.'][orderNumber]" id="rows['.$id.'][orderNumber]" value="'.$orderNumber.'" />';
}

function cpfGetImageUploadAuto($fieldName, $value, $basePath='', $baseUrl='', $width=150, $height=150) {

	return wsfGetImageUploadAuto($fieldName, $value, $basePath, $baseUrl, $width, $height);
}


function cpfUploadFileAuto($fieldName, $basePath) {
	$flatFieldName=str_replace(array('[',']'),array('_',''),$fieldName);
	
	//echo $flatFieldName;
	$name=cmfcFile::uploadFile($_FILES[$flatFieldName]['tmp_name'], $_FILES[$flatFieldName]['name'], $basePath, $_POST['old_'.$flatFieldName]);
	if (file_exists($basePath.$name) and !is_dir($basePath.$name))
		chmod($basePath.$name,0777);
	
	if (!empty($_POST['old_'.$flatFieldName])){
		//print_r($_POST);
		if ($_POST['old_'.$flatFieldName]!=$name or $_POST['delete_'.$flatFieldName])
			unlink($basePath.$_POST['old_'.$flatFieldName]);
		if ($_POST['delete_'.$flatFieldName] and $_POST['old_'.$flatFieldName]==$name)
			return '';
	}
	return $name;
}



/**
*
*/
/*
cpfLog($_cp['sectionInfo']['name'], $userSystem->cvId, $actionInfo=array('name'=>'add','rowId'=>$_columns[id]))
*/
function cpfLog($sectionName, $userId, $actionInfo) {
	global $_ws;
	global $_cp;
	$tableInfo=$_ws['physicalTables']['logs'];
	$title='ردیف به کد '.$actionInfo['rowId'].' در "'.$_cp['sectionsInfo'][$sectionName]['title'].'"';
	if ($actionInfo['name']=='update') $title.=' ویرایش شد';
	if ($actionInfo['name']=='insert') $title.=' اضافه شد';
	if ($actionInfo['name']=='delete') $title.=' حذف شد';
	$columnsValues=array(
		$tableInfo['columns']['sectionName']=>$sectionName,
		$tableInfo['columns']['userId']=>$userId,
		$tableInfo['columns']['title']=>$title,
		$tableInfo['columns']['occurrenceDateTime']=>date('Y-m-d H:i:s'),
		$tableInfo['columns']['ip']=>$_SERVER['REMOTE_ADDR'],
	);
	return cmfcMySql::insert($tableInfo['tableName'], $columnsValues);
}


function cpfApplyCategoriesChangesToAllRelatedTables($action,$prevInfo,$newInfo=null) {
	global $_cp;
 	foreach ($_cp['sectionsInfo'] as $sectionInfo) {
		if (!empty($sectionInfo['nodeId'])) {
			$_columns=$sectionInfo['tableInfo']['columns'];
			
			$sqlQuery="SELECT {t:id},{t:categoryId},{t:categoryPath} FROM {t} WHERE {t:categoryPath} LIKE '%,[categoryId],%' OR {t:categoryId}='[categoryId]'";
			$sqlQuery=cmfcString::replaceVariables(array(
				'{t}'=>$sectionInfo['tableInfo']['tableName'],
				'{t:id}'=>$_columns['id'],
				'{t:categoryPath}'=>$_columns['categoryPath'],
				'{t:categoryId}'=>$_columns['categoryId'],
				'[categoryId]'=>$prevInfo['id']
			),$sqlQuery);
			$rows=cmfcMySql::getRowsCustom($sqlQuery);
			
			if (is_array($rows))
			foreach ($rows as $row) {
				if ($action=='delete') {
					$row[$_columns['categoryId']]=$prevInfo['parentId'];
					$row[$_columns['categoryPath']]=$prevInfo['parentPath'];
					print_r($row);
					//$row[$_columns['categoryPath']]=preg_replace('/(.*,)'.$prevInfo['id'].',.*/', '$1', $row[$_columns['categoryPath']]);
					cmfcMySql::update($sectionInfo['tableInfo']['tableName'],$_columns['id'],$row,$row[$_columns['id']]);
					echo mysql_error();
				}
				if ($action=='changeParent') {
					$row[$_columns['categoryId']]=$newInfo['id'];
					$row[$_columns['categoryPath']]=$newInfo['path'];
					cmfcMySql::update($sectionInfo['tableInfo']['tableName'],$_columns['id'],$row,$row[$_columns['id']]);
				}
				echo '</pre>';
			}
		}
	}
}



function cpfDownloadFile($file){
	//First, see if the file exists
	if (!is_file($file)) { die("<b>404 File not found!</b>"); }
	//Gather relevent info about file
	$len = filesize($file);
	$filename = basename($file);
	$file_extension = strtolower(substr(strrchr($filename,"."),1));
	//This will set the Content-Type to the appropriate setting for the file
	switch( $file_extension )
	{
	case "pdf": $ctype="application/pdf"; break;
	case "exe": $ctype="application/octet-stream"; break;
	case "zip": $ctype="application/zip"; break;
	case "doc": $ctype="application/msword"; break;
	case "xls": $ctype="application/vnd.ms-excel"; break;
	case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
	case "gif": $ctype="image/gif"; break;
	case "png": $ctype="image/png"; break;
	case "jpeg":
	case "jpg": $ctype="image/jpg"; break;
	case "mp3": $ctype="audio/mpeg"; break;
	case "wav": $ctype="audio/x-wav"; break;
	case "mpeg":
	case "mpg":
	case "mpe": $ctype="video/mpeg"; break;
	case "mov": $ctype="video/quicktime"; break;
	case "avi": $ctype="video/x-msvideo"; break;
	//The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
	case "php":
	case "htm":
	case "html":
	//case "txt": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;
	default: $ctype="application/force-download";
	}
	
	//Begin writing headers
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	
	//Use the switch-generated Content-Type
	header("Content-Type: $ctype");
	//Force the download
	$header="Content-Disposition: attachment; filename=".$filename.";";
	header($header );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$len);
	@readfile($file);
	exit;
}

function cpIsUserProfileCompleted($userInfo){ // by babak
	if($userInfo['first_name'] == "" || $userInfo['last_name'] == "" || $userInfo['tel'] == "" || $userInfo['address'] == "") return false;
	return true;
}

function cpfGetDashboardSections()
{
	global $_cp, $_ws;
	
	$sectionsInfo = $_cp['sectionsInfo'];
	
	$dashboardSections = array();
	
	foreach($sectionsInfo as $sectionName=>$sectionValue)
	{
		if(isset($sectionValue['dashboard']))
		{
			$types['statistics'] = @in_array('statistics', $sectionValue['dashboard']['type']);
			$types['lastItems']  = @in_array('lastItems', $sectionValue['dashboard']['type']);
			
			foreach($types as $typeName=>$typeValue)
			{
				if($typeValue)
				{
					$dashboardSections[$typeName][$sectionName]['sectionName'] = $sectionName;
					$dashboardSections[$typeName][$sectionName]['sectionTitle'] = $sectionsInfo[$sectionName]['title'];
					$dashboardSections[$typeName][$sectionName]['sectionTitleEn'] = $sectionsInfo[$sectionName]['titleEn'];
					$dashboardSections[$typeName][$sectionName]['sectionTableInfo'] = $sectionsInfo[$sectionName]['tableInfo'];
					$dashboardSections[$typeName][$sectionName]['sectionDashboardInfo'] = $sectionValue['dashboard'];
				}
			}
		}
	}
	
	$dashboardCount = count($dashboardSections);
	if(!$dashboardCount)
		return false;
		
	$lastItemCount = $_ws['controlPanelInfo']['homePage']['lastItemColumnNo'];
	$statisticsCount = $_ws['controlPanelInfo']['homePage']['staticticsColumnNo'];
	
	$dashboardSectionsInfo = array(
		'count' => $dashboardCount,
		'dashboardLastItemsColumnNumber' => $lastItemCount,
		'dashboardStatisticsColumnNumber' => $statisticsCount,
		'dashboardSections' => $dashboardSections
	);
	
	return $dashboardSectionsInfo;
}

function cpfGetExcelContent($filename, $columnNames=array(), $sep=array(',', "\n"), $start=1)
{
	$file_contents = file_get_contents($filename);
	$rows = explode($sep[1], $file_contents);
	foreach($rows as $key=>$row)
	{
		if($row && $key>=$start)
		{
			$columns[] = explode($sep[0], $row);
		}
	}
	if(!$columnNames)
	{
		return $columns;
	}
	else
	{
		foreach($columns as $key=>$column)
		{
			foreach($column as $itemKey=>$item)
				$result[$key][$columnNames[$itemKey]] = $item;
		}
		return $result;
	}
}


function cpfGetNextEditableLang($currLangId){
	return wsfGetNextEditableLang($currLangId);
}

function cpfCreateBreadCrumb($action, $options = array()){
	global $langInfo, $_cp;
	
	?>
	<div class="br" dir=<?php echo $translation->languageInfo['direction']?> align="<?php echo $translation->languageInfo['align']?>"  >
		<span class="br-bullet">»</span> <?php echo wsfGetValue('home')?> » 
		<?php echo wsfGetValue($_GET['sn'])?>
		<?php 		if ($options['extra']){
			?>
			» <?php echo $options['extra']?>
			<?php 		}
		if ($action){
			?>
			[<?php echo $action?>]
			<?php 		}
		?>
	</div>
	
	<div class="desc">
	<?php echo $_cp['sectionInfo']['description']?>
	
	</div>
	<?php
}

function cpfShowTreeInput($sectionName, $idName, $idValue, $selectTitleValuePair= array('value' => 'id', 'title' => 'name'), $selectType= "multiSelect", $selectTypeDetails=''){
	global $_cp, $dbConnLink;
	$attributes = "";
	$html = '';

	$sectionInfo = $_cp['sectionsInfo'][$sectionName];

	if ($selectType == "multiSelect"){
	}
	ob_start();
	include(dirname(__FILE__)."/../treeInput.inc.php");
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}



function cpfIsWordKeyExists($key, $languageId, $relatedItem)
{
	global $_ws;
	$wordsTable = $_ws['physicalTables']['words'];
	
	$keyExistsQuery = "
		SELECT *
		FROM
			`".$wordsTable['tableName']."`
		WHERE
		(
			`".$wordsTable['columns']['key']."`='$key'
			AND
			`".$wordsTable['columns']['languageId']."`=$languageId
		)
		OR
		(
			`".$wordsTable['columns']['key']."`='$key'
			AND
			`".$wordsTable['columns']['relatedItem']."`<>'$relatedItem'
		)
	";
	
	$result = cmfcMySql::loadCustom($keyExistsQuery);
	
	if($result)
		return true;
	else
		return false;
}

function cpfDrawSectionBreadCrumb($items=null) {
	global $_cp;
	global $langInfo;
	if (is_null($items)) {
		$items[]=array(
			'title'=>wsfGetValue('Home'),
			'link'=>'?'
		);
		$items[]=array(
			'title'=>wsfGetValue($_cp['sectionInfo']['name'])." [".wsfGetValue($_REQUEST['action'])."]",
			'link'=>'?sn='.$_GET['sectionName'].'&pt='.$_GET['pageType']
		);
	}
?>
	<div class="br" dir=<?php echo $langInfo['htmlDir']?> align="<?php echo $langInfo['htmlAlign']?>"  >
	<?php foreach ($items as $item) { ?>
		<?php if ($separator) {?>
		<span class="br-bullet">&raquo;</span>
		<?php }?>
		<a href="<?php echo cpfRepairUrl($item['link'])?>"><?php echo $item['title']?></a>
	<?php $separator=true;} ?>	
	</div>
<?php }

function cpfRepairUrl($url)
{
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

function cpfDrawSectionHeader() {
	global $_cp;
	global $langInfo;
?>
	<div class="desc">
	<?php echo $_cp['sectionInfo']['description']?>
	</div>
<?php }

function cpfIsPasswordExists($serialNumber, $password=NULL)
{
	global $_ws;
	$serialNumbersTable = $_ws['physicalTables']['serialNumbers'];
	
	if(!$password)
		$result = cmfcMySql::load($serialNumbersTable['tableName'], $serialNumbersTable['columns']['serialNumber'], $serialNumber);
	else
		$result = cmfcMySql::loadWithMultiKeys(
			$serialNumbersTable['tableName'], 
			array(
				$serialNumbersTable['columns']['serialNumber'] => $serialNumber,
				$serialNumbersTable['columns']['password'] => $password
			)
		);
	
	if($result)
		return true;
	else
		return false;
}
function cpfMakeRandomNumberString($minlength, $maxlength, $useupper, $usespecial, $usenumbers) {
	$charset = "0123456789";
	if ($minlength > $maxlength)
		$length = mt_rand ($maxlength, $minlength);
	else
		$length = mt_rand ($minlength, $maxlength);
	for ($i=0; $i<$length; $i++) 
		$key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
	return $key;
}
function cpfGetSmsStatus($realStatus, $status)
{
	global $_ws;
	
	foreach($_ws['virtualTables']['smsStatus']['rows'] as $smsStatusRow)
	{
		$smsStatusRow = cmfcMySql::convertColumnNames($smsStatusRow, $_ws['virtualTables']['smsStatus']['columns']);
		if(in_array($realStatus, $smsStatusRow['realStatus']))
		{
			$matchedRows[] = $smsStatusRow;
		}
	}
	
	if($matchedRows)
	{
		foreach($matchedRows as $matchedRow)
		{
			if($matchedRow['status'] == $status)
				return $matchedRow['name'];
		}
	}
	
	return NULL;
}

function cpfDrawMultiRadioBoxes($baseName,$selectedItem, $items, $valueColumnName, $titleColumnName, $options=null) {

	$sortBy = $options['sortBy'];
	if (!$sortBy)
		$sortBy = 'value';
	
	$optionType = $options['type'];
	if ($optionType=='radio' or empty($optionType)) {
		$optionType = 'radio';
	} else {
		$baseName.='[]';
	}
	
	//echo $optionType;
	
	if (!is_array($selectedItem))
		$selectedItems[] = $selectedItem;
	else
		$selectedItems = $selectedItem;
	
	$displayMode = $options['displayMode'];
	if (!$displayMode)
		$displayMode = 'table';
	
	$template['table'] = array(
		'mainWrapper' => '<table %wrapperAttributes%>%contents%</table>',
		'contents' => '<tr>%fields%</tr>',
		'fields' => '<td>%field%</td>',
		'defaultAttributes' => array(
			'border' => 0,
		),
	);
	$template['normal'] = array(
		'mainWrapper' => '<p>%contents%</p>',
		'contents' => '%fields%',
		'fields' => '%field%<br />',
		'defaultAttributes' => array(
			'border' => 0,
		),
	);
	
	if (is_array($options[$displayMode])){
		$attributes = cmfcPhp4::array_merge($template[$displayMode]['defaultAttributes'], $options[$displayMode]);
		$wrapperAttributes = cmfcHtml::attributesToHtml($attributes);
	}
	elseif (is_string($options[$displayMode]))
		$wrapperAttributes = $options[$displayMode];
	
	if (is_array($options['inputAttributes']))
		$inputAttributes = cmfcHtml::attributesToHtml($options['inputAttributes']);
	elseif (is_string($options['inputAttributes']))
		$inputAttributes = $options['inputAttributes'];
	
	$numColumns = $options['columns'];
	if (!$numColumns)
		$numColumns = 5;
	
	if (is_string($items)){
		$items = cmfcMySql::getRowsCustom($items);
	}
	
	$counter = 0;
	$contents = '';
	if (is_array($items)){
		//cmfcHtml::printr($items);
		/*switch ($sortBy){
		case 'title':
			$items = cmfcArray::sortItems($items, $titleColumnName, 'asc');
			break;
		
		case 'value':
			$items = cmfcArray::sortItems($items, $valueColumnName, 'asc');
			break;
		
		default:
			$items = cmfcArray::sortItems($items, $valueColumnName, 'asc');
		}*/
		//cmfcHtml::printr($items);
		//cmfcHtml::printr( $options);
		foreach ($items as $item) {
			//cmfcHtml::printr($item);
			//echo $numColumns;
			$counter ++;
			
			if (in_array($item[$valueColumnName], $selectedItems) )
				$checked='checked="checked"';
			else 
				$checked='';
			
			if(!isset($options['attributes'][$item[$valueColumnName]]) && isset($options['attributes']['allItemsOfList'])) $options['attributes'][$item[$valueColumnName]] = $options['attributes']['allItemsOfList'];
			if(!isset($options['attributes'][$item[$valueColumnName]]['id'])) $options['attributes'][$item[$valueColumnName]]['id'] = $baseName.$counter;
			if($options['attributes'] && array_key_exists($item[$valueColumnName], $options['attributes']))
				$specialAttributes = cmfcHtml::attributesToHtml($options['attributes'][$item[$valueColumnName]]);
			else
				$specialAttributes = '';
			
			if (in_array($optionType, array('checkbox', 'radio')) ){
				$value = $item[$valueColumnName];
				$field = '<input '.$inputAttributes.
					'name="'.$baseName.'" type="'.$optionType.'" value="'.$value.'" '.$checked.' '.$specialAttributes.'>&nbsp;<label for="'.$options['attributes'][$item[$valueColumnName]]['id'].'">';
				
				if(is_array($options['strongItems']))
				{
					if(in_array($value, $options['strongItems'])) {
						$itemTitleColumnName = "<b>".$item[$titleColumnName]."</b>";
					} else {
						$itemTitleColumnName = $item[$titleColumnName];
					}
				} else {
					$itemTitleColumnName = $item[$titleColumnName];
				}
				$field .= $itemTitleColumnName;
				$field .= '</label>';
			}
			elseif ($optionType == 'custom'){
				if ($checked)
					$tag = $options['tag']['selected'];
				else
					$tag = $options['tag']['notSelected'];
				
				$value = $item[$titleColumnName];
				$tag = str_replace('%value%', $value, $tag);
				$tag = str_replace('%inputAttributes%', $inputAttributes, $tag);
				
				$field = $tag;
			}
			
			$html .= str_replace('%field%', $field, $template[$displayMode]['fields']);
			
			if ($counter % $numColumns == 0) {
				$contents .= str_replace('%fields%', $html, $template[$displayMode]['contents']);
				$html = '';
			}
		}
		if ($html){
			$contents .= str_replace('%fields%', $html, $template[$displayMode]['contents']);
		}
		$result = str_replace('%contents%', $contents, $template[$displayMode]['mainWrapper']);
		$result = str_replace('%wrapperAttributes%', $wrapperAttributes, $result);
	}
	else{
		$result = 'no valid items';
	}
	return $result;
}

function cpfGetSmsStatusType($log)
{
	if($log['number'])
	{
		if($log['body'])
		{
			switch($log['serialNumberStatus'])
			{
				case -100:
					$type = 8;
					break;
				case -5:
					$type = 7;
					break;
				case -4:
					$type = 6;
					break;
				case -3:
					$type = 3;
					break;
				case -2:
					$type = 4;
					break;
				case -1:
					$type = 5;
					break;
				default:
					if($log['userType'] == 'newUser')
					{
						$type = 9;
					}
					else
					{
						$type = 10;
					}
					break;
			}
		}
		else
		{
			if($log['serialNumberStatus'])
			{
				$type = 3;
			}
			else
			{
				$type = 2;
			}
		}
	}
	else
	{
		$type = 1;
	}
	
	return $type;
}



function cpfGenerateBarcode($text, $options= ''){
	global $_ws;
	if ($options)
		$options = cmfcHtml::attributesToHtml($options);
	return '
<img src="'.$_ws['generalLibInfo']['url'].'dependencies/barcode/html/image.php?code=ean13&o=2&t=30&r=2&text='.$text.'&f1=Arial.ttf&f2=0&a1=&a2=" alt="'.$text.'" '.$options.' />';
}

function cpfConvertSerialNumberToBarCodeNumber($serialNumber)
{
	return '00'.$serialNumber.'0005';
}

function cpfConvertBarcodeNumberToSerialNumber($barcodeNumber)
{
	return substr($barcodeNumber, 2, 7);
}

function cpfGetSerialNumberId($barCode)
{
	global $_ws;
	$serialNumber = cpfConvertBarcodeNumberToSerialNumber($barCode);
	
	$serialInfo = cmfcMySql::load($_ws['physicalTables']['serialNumbers']['tableName'], $_ws['physicalTables']['serialNumbers']['columns']['serialNumber'], $serialNumber);
	
	return $serialInfo['id'];
}






function cpfGetProductTypeFieldsAsArray($productTypeId)
{
	global $_ws;
	
	$productTypeFieldsTable = $_ws['physicalTables']['productTypeFields'];
	
	$productTypeFields = cmfcMySql::getRows($productTypeFieldsTable['tableName'], $productTypeFieldsTable['columns']['productTypeId'], $productTypeId);
	
	if($productTypeFields)
	{
		foreach($productTypeFields as $productTypeField)
		{
			$productTypeField = cmfcMySql::convertColumnNames($productTypeField, $productTypeFieldsTable['columns']);
		
			if($productTypeField['type'] == 2)
			{
				$productTypeFieldsInfo[$productTypeField['id']] = array(
					'fieldName' => $productTypeField['name'],
					'fieldType' => 'text',
					'fieldLength'=> ''
				);
			}
			elseif($productTypeField['type'] == 1)
			{
				if($productTypeField['fieldType'])
					$productTypeFieldsInfo[$productTypeField['id']] = array(
						'fieldName' => $productTypeField['name'],
						'fieldType' => 'text',
						'fieldLength'=> ''
					);
				else
					$productTypeFieldsInfo[$productTypeField['id']] = array(
						'fieldName' => $productTypeField['name'],
						'fieldType' => 'int',
						'fieldLength'=> 11
					);
			}
		}
		$productTypeFieldsInfo[0] = array(
			'fieldName' => 'product_id',
			'fieldType' => 'int',
			'fieldLength'=> 11
		);
		$productTypeFieldsInfo['idt'] = array(
			'fieldName' => 'insert_datetime',
			'fieldType' => 'datetime',
			'fieldLength'=> ''
		);
		$productTypeFieldsInfo['udt'] = array(
			'fieldName' => 'update_datetime',
			'fieldType' => 'datetime',
			'fieldLength'=> ''
		);
	}

	return $productTypeFieldsInfo;
}

function cpfIsInternalNameExists($tableInfo, $internalName, $currentId)
{
	if($tableInfo['columns']['relatedUrls'])
	{
		$productQuery = "
			SELECT *
			FROM
				`".$tableInfo['tableName']."`
			WHERE
				`".$tableInfo['columns']['urlName']."`='".$internalName."'
				OR
				`".$tableInfo['columns']['relatedUrls']."` LIKE '%/".$internalName."/%'
		"; 
		
		$product = cmfcMySql::loadCustom($productQuery);
	}
	else
		$product = cmfcMySql::load($tableInfo['tableName'], $tableInfo['columns']['urlName'], $internalName);
		
	if($product)
	{
		if($product['id'] == $currentId)
		{
			return false;
		}
		return $product;
	}
	else
	{
		return false;
	}
}

function cpfCreateBrandProductTypesDropDown($productTypes, $brandIds)
{
	global $_ws, $langInfo;
	
	$productTypesTable = $_ws['physicalTables']['productTypes'];
	$productBrandsTable = $_ws['physicalTables']['productBrands'];
	
	
	if($productTypes)
	{
		foreach($productTypes as $productType)
		{
			if($productType)
			{
				$sqlWhereQuery .= $sep." `".$productBrandsTable['columns']['productTypes']."` LIKE '%,".$productType.",%'";
				$sep = " OR";
			}
		}
	}
	
	if(!$sqlWhereQuery)
	{
		$sqlWhereQuery = " 1=0";		
	}
	
	$sqlQuery = "SELECT * FROM `".$productBrandsTable['tableName']."` WHERE `".$productBrandsTable['columns']['languageId']."`='".$langInfo['id']."' AND (".$sqlWhereQuery.')';
	
	$brandsList = cmfcMySql::getRowsCustom($sqlQuery);
	//cmfcHtml::printr($sqlQuery );
	//die();
	if($brandsList)
	{
		return cmfcHtml::drawMultiRadioBoxes(
			"rows[1][common][brandIds]",
			$brandIds,
			$brandsList,
			$productBrandsTable['columns']['relatedItem'],
			$productBrandsTable['columns']['name'],
			array(
				'columns' => 4,
				'type' => 'checkbox',
				//'inputAttributes' => 'onclick="cpfOnProductTypesCheckBoxesChange()"',
			)
		);
	}
	else
		return '';
	
} 


function cpfCreateGuaranteeDropDown($brandId, $guaranteeId='')
{
	global $_ws, $langInfo;
	
	$productGuaranteesTable = $_ws['physicalTables']['productGuarantees'];
	
	$guaranteeItemsQuery = "
		SELECT * 
		FROM 
			`".$productGuaranteesTable['tableName']."`
		WHERE
			`".$productGuaranteesTable['columns']['languageId']."`='".$langInfo['id']."'
			AND
			`".$productGuaranteesTable['columns']['brandIds']."` LIKE '%,".$brandId.",%'
	";
	$guaranteeItems = cmfcMySql::getRowsCustom($guaranteeItemsQuery);
		//cmfcHtml::printr($sqlQuery );
	//die();
	return cmfcHtml::drawDropDown(
		"rows[1][common][guaranteeType]",
		$guaranteeId,
		$guaranteeItems,
		$productGuaranteesTable['columns']['relatedItem'],
		$productGuaranteesTable['columns']['name'],
		NULL,
		NULL,
		'',
		''
	);
	
} 


function cpfCreateProductFieldsByType($productTypeId)
{
	global $infa, $_ws, $itemLanguage;
	
	$productTypeFieldTable = $_ws['physicalTables']['productTypeFields'];
	$productTypeFieldItemsTable = $_ws['physicalTables']['productTypeFieldItems'];
	
	$num = 1;
	
	$productTypeFieldsQuery = "SELECT * FROM `".$productTypeFieldTable['tableName']."` WHERE `".$productTypeFieldTable['columns']['productTypeId']."`=$productTypeId ORDER BY `".$productTypeFieldTable['columns']['orderNumber']."` ASC";
	$productTypeFieldsByType = cmfcMySql::getRowsCustomWithCustomIndex($productTypeFieldsQuery, $productTypeFieldTable['columns']['type'], true);
	
	ksort($productTypeFieldsByType);
	$productTypeFieldNames = array(
		1=>wsfGetValue('productTypeMainFields'),
		2=>wsfGetValue('productTypeNormalFields')
	);
	//cmfcHTml::printr($productTypeFieldsByType);
	if($productTypeFieldsByType)
	{
		foreach($productTypeFieldsByType as $type=>$productTypeFields)
		{
			$infa->showSeparatorRow($productTypeFieldNames[$type]);
			foreach($productTypeFields as $productTypeField)
			{
				$productTypeField = cmfcMySql::convertColumnNames($productTypeField, $productTypeFieldTable['columns']);
				switch($productTypeField['type'])
				{
					case 2:
						switch($productTypeField['fieldType'])
						{
							case 1:
								$infa->showInputRow($productTypeField['title'],"rows[$num][extraColumns][".$productTypeField['name']."]", $_POST['rows'][$num]['extraColumns'][$productTypeField['name']],'',40,'rtl');
								break;
							case 2:
								$infa->showTextareaRow($productTypeField['title'],"rows[$num][extraColumns][".$productTypeField['name']."]",$_POST['rows'][$num]['extraColumns'][$productTypeField['name']],'','',15,'57%');
								break;
							case 3:
								$infa->showSeparatorRow($productTypeField['title']);
								break;
						}
						break;
					case 1:
						$productTypeFieldItemsQuery = "SELECT * FROM `".$productTypeFieldItemsTable['tableName']."` WHERE `".$productTypeFieldItemsTable['columns']['productTypeFieldId']."`='".$productTypeField['id']."' ORDER BY `".$productTypeFieldItemsTable['columns']['orderNumber']."` ASC";
						$productTypeFieldItems = cmfcMySql::getRowsCustom($productTypeFieldItemsQuery);
						
						if($productTypeField['fieldType'])
						{
							if($_POST['rows'][$num]['extraColumns'][$productTypeField['name']])
								$_POST['rows'][$num]['extraColumns'][$productTypeField['name']] = explode(',', $_POST['rows'][$num]['extraColumns'][$productTypeField['name']]);
							
							$productTypeFieldItemsHtml = cmfcHtml::drawMultiRadioBoxes(
								"rows[$num][extraColumns][".$productTypeField['name']."]",
								$_POST['rows'][$num]['extraColumns'][$productTypeField['name']],
								$productTypeFieldItems,
								$productTypeFieldItemsTable['columns']['id'],
								$productTypeFieldItemsTable['columns']['title'],
								array(
									'columns' => 4,
									'type' => 'checkbox',
								)
							);
						}
						else
							$productTypeFieldItemsHtml = cmfcHtml::drawDropDown(
								"rows[$num][extraColumns][".$productTypeField['name']."]",
								$_POST['rows'][$num]['extraColumns'][$productTypeField['name']],
								$productTypeFieldItems,
								$productTypeFieldItemsTable['columns']['id'],
								$productTypeFieldItemsTable['columns']['title'],
								NULL,
								NULL,
								'',
								''
							);
						
						$infa->showCustomRow($productTypeField['title'], $productTypeFieldItemsHtml, '');
						break;
				}
			}
		}
	}
}

function cpfSaveExtraProductFields($productFieldValues, $productTypeId, $productId, $action)
{
	global $_ws;

	$productTypeFieldsTable = $_ws['physicalTables']['productTypeFields'];
	$productTypesTable = $_ws['physicalTables']['productTypes'];

	//cmfcMysql::setOption("debugEnabled", true);
	if($productFieldValues)
	{
		$productFieldValuesTableName = cmfcMySql::getColumnValue(
			$productTypeId,
			$productTypesTable['tableName'],
			$productTypesTable['columns']['relatedItem'],
			$productTypesTable['columns']['fieldsTableName']
		);
		
		$oldProduct = cmfcMySql::load($productFieldValuesTableName, "product_id", $productId);
		
		if($oldProduct)
			$action = "update";
		else
			$action = "insert";
	
		$_columns = $productFieldValues;
		
		if($_columns)
			foreach($_columns as $columnKey=>$_column)
				if(is_array($_column))
					$_columns[$columnKey] = ','.implode(',', $_column).',';
				else
					$_columns[$columnKey] = $_column;
				
		$_columns["product_id"] = $productId;

		if($action == "insert")
		{
			$_columns["insert_datetime"] = date("Y-m-d H:i:s");
			$result = cmfcMySql::insert($productFieldValuesTableName, $_columns);
		}
		elseif($action == "update")
		{
			$_columns["update_datetime"] = date("Y-m-d H:i:s");
			$result = cmfcMySql::update(
				$productFieldValuesTableName,
				"product_id",
				$_columns,
				$productId
			);
		}
	}
	//cmfcHtml::printr(cmfcMySql::getRegisteredQueries());
}


function cpfLoadExtraFiledValues($productTypeId, $productId)
{
	global $_ws;

	$productTypesTable = $_ws['physicalTables']['productTypes'];

	$productFieldValuesTableName = cmfcMySql::getColumnValue(
		$productTypeId,
		$productTypesTable['tableName'],
		$productTypesTable['columns']['relatedItem'],
		$productTypesTable['columns']['fieldsTableName']
	);

	
	$productFieldValues = cmfcMySql::load($productFieldValuesTableName, 'product_id', $productId);
	unset($productFieldValues['id'], $productFieldValues['product_id'], $productFieldValues['insert_datetime'], $productFieldValues['update_datetime']);

	return $productFieldValues;
}


function cpfCreateProductRelatedHtml($post, $request)
{
	global $_ws, $langInfo;
	
	$relatedProductsTable = $_ws['physicalTables']['relatedProducts'];
	$productTypesTable = $_ws['physicalTables']['productTypes'];
	
	$num=1;
	#######
	#create Related Item to this product
	#######
	ob_start();
	cmfcMySql::setOption("debugEnabled", true);
	$productId = $request['id'];
	if($request['action']=='edit' && !$post['submit_save'])
	{
		$relatedProductsQuery = "SELECT * FROM `".$relatedProductsTable['tableName']."` WHERE `".$relatedProductsTable['columns']['productParentId']."`=".$productId;
		
		$relatedProducts = cmfcMySql::getRowsCustomWithCustomIndex($relatedProductsQuery, $relatedProductsTable['columns']['productChildType'], true);
		
		if($relatedProducts)
		{
			foreach($relatedProducts as $relatedProductKey=>$relatedProduct)
			{
				$relatedProductItemIdArray = array();
				foreach($relatedProduct as $relatedProductItem)
				{
					$relatedProductItem = cmfcMySql::convertColumnNames($relatedProductItem, $relatedProductsTable['columns']);
					$relatedProductItemIdArray[] = $relatedProductItem['productChildId'];
				}
				$relatedProductItemIdString = implode(",", $relatedProductItemIdArray);
				$post['rows'][$num]['columns']['relatedProductsOld'][$relatedProductKey] = $post['rows'][$num]['columns']['relatedProducts'][$relatedProductKey] = $relatedProductItemIdString;
			}
		}
	}

	$productTypesQuery = "SELECT * FROM `".$productTypesTable['tableName']."` WHERE `".$productTypesTable['columns']['languageId']."`='".$langInfo['id']."' AND `".$productTypesTable['columns']['isAccessory']."`=1 ORDER BY `".$productTypesTable['columns']['id']."` ASC";
	$productTypes = cmfcMySql::getRowsCustom($productTypesQuery);
	
	if($productTypes)
	{
		$rowNum = 0;
	?>
		[<a href="popup.php?sn=relatedProducts<?php echo ($request['id'] ? '&productId='.$request['id'] : '')?>" onclick="return GB_showCenter('<?php echo wsfGetValue("VN_selectRelatedProducts")?>', this.href, 600, 800)">
			<?php echo wsfGetValue("VN_selectRelatedProducts")?>
		</a>]
		
		
		<table border="0" cellspacing="0" cellpadding="10" width="100%">
	<?php 		foreach($productTypes as $productType)
		{
			
			$jsProductTypesArray .= "productType[$rowNum]=".$productType['id']."; ";
			$rowNum++;
		?>
			<tr>
				<td width="100">
					<?php echo $productType['name']." ".wsfGetValue('VN_related')?>:
				</td>
				<td>
					<input name="rows[<?php echo $num?>][columns][relatedProducts][<?php echo $productType['id']?>]" id="rows[relatedProducts][<?php echo $productType['id']?>]" type="hidden" value="<?php echo $post['rows'][$num]['columns']['relatedProducts'][$productType['id']]?>" />
					<input name="rows[<?php echo $num?>][columns][relatedProductsOld][<?php echo $productType['id']?>]" type="hidden" value="<?php echo $post['rows'][$num]['columns']['relatedProductsOld'][$productType['id']]?>" />
					<div id="rows[relatedProductsNames][<?php echo $productType['id']?>]"></div>
				</td>
			</tr>
		<?php 		}
	?>
		</table>
		<script type="text/javascript">
			var productType = new Array();
			<?php echo $jsProductTypesArray?>
			//alert(productType.length);
			
			function cpfPrepareInfoAndProductsName()
			{
				var relatedProductData = new Array();
				for(i=0; i<productType.length;i++)
				{
					relatedProductData[i] = document.getElementById("rows[relatedProducts]["+productType[i]+"]").value;
				}
				xajaxPrepareRelatedProductsName(productType, relatedProductData, 1);
			}
			cpfPrepareInfoAndProductsName();
		</script>
	<?php 	}
	?>
	
	<input type="checkbox" id="rows[relatedProductChanges]" style="display:none; visibility:hidden" onclick="cpfPrepareInfoAndProductsName()" />
	
	<?php 	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}

function cpfSaveRelatedProduct($values, $oldValues, $productInfo)
{
	$oldItemsToDelete = $oldValueArray = explode(",", $oldValues);
	if($values)
	{
		$valueArray = explode(",", $values);
		foreach($valueArray as $value)
		{
			$newItemValue[] = $value;
			if($value && !in_array($value, $oldValueArray))
			{
				$columnValues = array(
					$productInfo['relatedProductsTable']['columns']['productParentId'] => $productInfo['parentId'],
					$productInfo['relatedProductsTable']['columns']['productChildType'] => $productInfo['childType'],
					$productInfo['relatedProductsTable']['columns']['productChildId'] => $value,
					$productInfo['relatedProductsTable']['columns']['insertDatetime'] => date("Y-m-d H:i:s")
				);
				cmfcMySql::insert(
					$productInfo['relatedProductsTable']['tableName'],
					$columnValues
				);
			}
		}
		
		$oldItemsToDelete = array_diff($oldValueArray, $newItemValue);
	}
	
	if($oldItemsToDelete)
	{
		foreach($oldItemsToDelete as $oldItemToDelete)
		{
			if($oldItemToDelete)
			{
				$deleteQuery = "
					DELETE FROM `".$productInfo['relatedProductsTable']['tableName']."`
					WHERE
						`".$productInfo['relatedProductsTable']['columns']['productParentId']."`=".$productInfo['parentId']."
						AND
						`".$productInfo['relatedProductsTable']['columns']['productChildType']."`=".$productInfo['childType']."
						AND
						`".$productInfo['relatedProductsTable']['columns']['productChildId']."`=".$oldItemToDelete."
				";
				cmfcMySql::exec($deleteQuery);
			}
		}
	}
}



function cpfOnChangeOrderParams($orderId, $action)
{
	global $_ws, $emailTemplate, $emailSender;

	$ordersTable = $_ws['physicalTables']['orders'];
	$usersTable = $_ws['physicalTables']['webUsers'];
	$settingsTable = $_ws['physicalTables']['settings'];
	$smsMessagesTable = $_ws['physicalTables']['smsMessages'];
	
	$currentOrder = cmfcMySql::load($ordersTable['tableName'], $ordersTable['columns']['id'], $orderId);
	$currentOrder = cmfcMySql::convertColumnNames($currentOrder, $ordersTable['columns']);
		
	if($currentOrder['userId'])
	{
		$currentUser = cmfcMySql::load($usersTable['tableName'], $usersTable['columns']['id'], $currentOrder['userId']);
		$currentUser = cmfcMySql::convertColumnNames($currentUser, $usersTable['columns']);
		
		if($currentUser['mobile'])
		{
			if($action=='confirm')
				$smsInternalName = 'paymentConfirm';
			elseif($action=='sent')
				$smsInternalName = 'productSendUserMessage';
				
			$smsBody = cmfcMySql::getColumnValue($smsInternalName, $smsMessagesTable['tableName'], $smsMessagesTable['columns']['internalName'], $smsMessagesTable['columns']['body']);
			$replacements = array(
				'%fullName%' => $currentUser['fullName'],
				'%orderNumber%' => $currentOrder['orderNumber'],
			);
			
			$smsBody = cmfcString::replaceVariables($replacements, $smsBody);
			
			$logSmsInfo = array(
				'title' => wsfGetValue($action.'SmsMessage'),
				'userId' => $currentUser['id'],
			);
			
			wsfSendSms($currentUser['mobile'], $smsBody, $logSmsInfo);
		}
		
		if($action=='confirm')
		{
			if ($emailTemplate->loadByInternalName('paymentConfirm')!==false) 
			{
				$replacements=array(
					'%fullName%'=>$currentUser['fullName'],
					'%orderNumber%'=>$currentOrder['orderNumber'],
					'%inline_subject%' => $emailTemplate->cvInlineSubject,
					'%site_url%' => $_ws['siteInfo']['url'],
					'%header%' => '',
				);
				$emailTemplate->process($replacements);
				//define("Email_Addr_Info","babak.taraghi@gmail.com"):
				
				$emailSender->addAddress($_ws['emailsInfo']['info']);
				
				$emailSender->Subject=$emailTemplate->getSubject();
				$emailSender->Body=$emailTemplate->getBody();
				$emailSender->FromName=$_ws['siteInfo']['titleEn'];
				//cmfchtml::printr($emailSender);
				$result = $emailSender->send();
			}
		}
		elseif($action=='sent')
		{
			$adminMobile = cmfcMySql::getColumnValue(
				'adminsMobile',
				$settingsTable['tableName'],
				$settingsTable['columns']['key'],
				$settingsTable['columns']['value']
			);
			
			if($adminMobile)
			{
				//cmfcHtml::printr($adminMobile);die();
				$smsBody = cmfcMySql::getColumnValue('productSendAdminMessage', $smsMessagesTable['tableName'], $smsMessagesTable['columns']['internalName'], $smsMessagesTable['columns']['body']);
				$replacements = array(
					'%fullName%' => $currentUser['fullName'],
					'%orderNumber%' => $currentOrder['orderNumber'],
				);
				
				$smsBody = cmfcString::replaceVariables($replacements, $smsBody);
				
				/*$logSmsInfo = array(
					'title' => wsfGetValue($action.'SmsMessage'),
					'userId' => $currentUser['id'],
				);*/
				
				wsfSendSms($adminMobile, $smsBody);
			}
		}
	}
}


function cpfFindMainSections(){
	global $_cp;
	$mainSections = array();
	if (is_array($_cp['sideMenuContainers'])){
		foreach ($_cp['sideMenuContainers'] as $containerName => $containerInfo){
			if (is_array($containerInfo['childs'])){
				foreach ($containerInfo['childs'] as $subContainerName => $subContainerInfo){
					//cmfcHtml::printr($subContainerInfo);
					$mainSections[$subContainerName]['dashboard']['mainSections']['title'] = $subContainerInfo['title'];
					if (is_array($subContainerInfo['childs']) && $subContainerInfo['mainSections']){
						if (is_array($subContaierInfo['mainSections']) ){
							foreach ($subContaierInfo['mainSections'] as $columnName => $columnValue)
								$mainSections[$subContainerName]['dashboard'][$columnName] = $columnValue;
						}
						
						$mainSections[$subContainerName]['children'] = array();
						
						foreach ($subContainerInfo['childs'] as $childName => $childInfo){
							$sectionInfo = $_cp['sectionsInfo'][$childName];
							//cmfcHtml::printr($sectionInfo);
							if ($sectionInfo['dashboard']['mainSections']){
								if (!wsfIsAccessible($sectionInfo['accessPointName']) && isset($sectionInfo['accessPointName'])	)
									continue;
								$mainSections[$subContainerName]['children'][$childName] = $sectionInfo;
							}
						}
					}
					if (!$mainSections[$subContainerName]['children']){
						unset($mainSections[$subContainerName]);
					}
					else{
						if (
							$mainSections[$subContainerName]['dashboard']['directLink'] == 'auto' ||
							!isset($mainSections[$subContainerName]['dashboard']['directLink'])
						){
							if (is_array($mainSections[$subContainerName]['children']) )
								$count = count($mainSections[$subContainerName]['children']);
							
							if ($count === 1)
								$mainSections[$subContainerName]['dashboard']['directLink'] = true;
							else
								$mainSections[$subContainerName]['dashboard']['directLink'] = false;
						}
					}
				}
			}
		}
	}
	//cmfcHtml::printr($mainSections);
	return $mainSections;
}

function cpfCreateMainSectionItems($containerChildren){
	$items = array();
	foreach ($containerChildren as $msSectionName => $msSectionInfo){
		$item = cpfCreateMainSectionItem($msSectionName, $msSectionInfo);
		$items[$msSectionName] = $item;
	}
	return $items;
}
function cpfCreateMainSectionItem($msSectionName, $msSectionInfo){
	$item = array();
		
	$mainSectionInfo = $msSectionInfo['dashboard']['mainSections'];
	
	$item['icon'] = $mainSectionInfo['icon'];
	
	if ($msSectionInfo['tableInfo'] && (count($msSectionInfo['actions']) > 1)){
		$query = "SELECT COUNT(id) as items FROM ".$msSectionInfo['tableInfo']['tableName'];
		if($msSectionInfo['tableInfo']['columns']['languageId'])
			$query .= " WHERE ".$msSectionInfo['tableInfo']['columns']['languageId']." = '1'";
		$numItems = cmfcMySql::loadCustom($query);
		$item['numItems'] = $numItems['items'];
	}
	
	
	$pathPrefix = 'interface/images/icons/mainSections-';
	//echo $item['icon'];
	if(!file_exists($pathPrefix.$item['icon']))
		$item['icon'] = $msSectionName.'.png';
	
	
	if(!file_exists($pathPrefix.$item['icon']))
		$item['icon'] = 'default.png';
	//echo '<br />', $item['icon'];
	$item['directLink'] = $mainSectionInfo['directLink'];
	$item['icon'] = $pathPrefix.$item['icon'];
	$item['title'] = $msSectionInfo['title'];
	if ($mainSectionInfo['title'])
		$item['title'] = $mainSectionInfo['title'];
	
	$item['name'] = $msSectionName;
	
	return $item;
}
function cpfprovinceNameFinder($proviencId,$itemLang){
	global $_ws;
	$sql="SELECT ".$_ws['physicalTables']['provinces']['columns']['name'].
	 " , ".$_ws['physicalTables']['provinces']['columns']['nameEn']. 
	 " FROM  ".$_ws['physicalTables']['provinces']['tableName'].
	 " WHERE ".$_ws['physicalTables']['provinces']['columns']['id'].
	 " = ".$proviencId;
	if($itemLang==1)
		return cmfcMySql::getColumnValueCustom($sql,'name');
	else
		return cmfcMySql::getColumnValueCustom($sql,'name_en');
}

function cpfCreateJquploaderHtml($fieldName,$value,$basePath='',$baseUrl='',$options=array()) {
	global $HTTP_POST_VARS, $lang;
	global $_ws;
	
	if (!isset($options['showDeleteButton'])) $options['showDeleteButton']=true;
	if (!isset($options['showFileUrl'])) $options['showFileUrl']=true;
	
	$flatFieldName=str_replace(array('[',']'),array('_',''),$fieldName);

	$uploadMaxFileSize = ini_get('upload_max_filesize');
	$uploadMaxFileSizeAsInt = (int)$uploadMaxFileSize;
	$uploadMaxFileSizeInByte = $uploadMaxFileSizeAsInt*1024*1024;
	
	$sessionId = session_id();
	$sessionName = ini_get("session.name");

	ob_start();
	?>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#<?php echo $flatFieldName?>").jqUploader({
			uploadScript:	"<?php echo dirname($_SERVER['PHP_SELF'])?>/jqUploader.php?sn=<?php echo $_GET['sn']?>&jqUploader=1&<?php echo $sessionName?>=<?php echo $sessionId?>&flatFieldName=<?php echo $flatFieldName?>",
			afterScript:	'none',
			background:	"f7dea5",
			barColor:	"64A9F6",
			allowedExt:     "*.*",
			src: "interface/javascripts/jqupload/jqUploader.swf",
			hideSubmit: false
		});
	
	});
	</script>
    <fieldset>
    <legend>Upload your file</legend>

    <ol>
      <li id="<?php echo $flatFieldName?>">
        <label for="<?php echo $flatFieldName?>_field"><?php echo wsfGetValue('selectYourFile')?>(<?php echo wsfGetValue('maximumfileUpload')?> : <?php echo $uploadMaxFileSize?>)</label>
		<input name="MAX_FILE_SIZE" value="<?php echo $uploadMaxFileSizeInByte?>" type="hidden" />
        <input name="<?php echo $fieldName?>" id="<?php echo $flatFieldName?>_field"  type="file" />
      </li>
    </ol>
    </fieldset>


	<?php
	$html = ob_get_contents();
	ob_end_clean();
	$dimension = "";

	if (!empty($value)) {
		$src=$baseUrl.$value;
		if ($options['showDeleteButton']===true) 
			$html.='<input name="delete_'.$flatFieldName.'" value="true" type="checkbox" style="margin-bottom:0px"/> حذف فایل فعلی</a><br/><br/>';
		if ($options['showFileUrl']===true) 
			$html.=$src;
	}
	$html.='<input id="'.$fieldName.'" name="old_'.$flatFieldName.'" value="'.$value.'" type="hidden" />';
	return $html;
}

function cpfSaveJqUploderInfoAuto($fieldName, $basePath) {
	$flatFieldName=str_replace(array('[',']'),array('_',''),$fieldName);
	
	if($_SESSION['fileNames'][$flatFieldName])
		$name = $_SESSION['fileNames'][$flatFieldName];
	else
		$name = $_POST['old_'.$flatFieldName];
		
	if (!empty($_POST['old_'.$flatFieldName])){
		if ($_POST['old_'.$flatFieldName]!=$name or $_POST['delete_'.$flatFieldName])
			unlink($basePath.$_POST['old_'.$flatFieldName]);
		if ($_POST['delete_'.$flatFieldName] and $_POST['old_'.$flatFieldName]==$name)
			return '';
	}
	return $name;
}
?>