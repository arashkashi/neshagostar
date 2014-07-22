<?php
class cpInterface {
	var $_zebraClasses=array('table-row2','table-row1');
	var $_currentZebraClass='';
	var $_numberOfColumns = '';
	var $_commonColumns = '';
	//var $_overrideFields = '';
	//var $_overrideFieldInputs = '';
	
	
	function setOverrideFields($fields = ''){
		$this->_overrideFields = $fields;
		
		if ($this->_overrideFields){
			foreach ($this->_overrideFields as $key => $field){
				$this->_overrideFieldInputs[$key] = $this->createCustomInput($field);
			}
		}
	}
	
	function setCommonColumns($columns){
		$this->_commonColumns = $columns;
		$numColumns = count($this->_commonColumns);
		
		if ($numColumns > $this->_numberOfColumns)
			$this->_numberOfColumns = $numColumns;
	}
	
	function cpInterface($options = ''){
		if ($options['columns']){
			$this->_numberOfColumns = (int)$options['columns'];
		}
	}
	
	function resetZebraClasses() {
		reset($this->_zebraClasses);
	}
	
	function getNextZebraClass() {
		$class=next($this->_zebraClasses);
		if ($class===false) {
			$this->resetZebraClasses();
			$class=current($this->_zebraClasses);
		}
		
		return $this->_currentZebraClass=$class;
	}
	
	function drawAddibleBoxes($title, $addibleItem, $templates){
		$html = cpfDrawBoxes($addibleItem, $templates);
		$this->showSeparatorRow($title."<a href=\"javascript:cpfAddNewBox('".$addibleItem['name']."','".$addibleItem['name']."_template_box','".$addibleItem['name']."', '".$addibleItem['jsCallback']."')\"><img border='0' alt='' src='interface/images/addible.png' /></a>");
		//cmfcHtml::printr($html);
		if(is_array($html))
		{
			$this->showSingleColumnRow('',
				"<div id='".$addibleItem['name']."header'>".$html['header']."</div>"
			);
			
			$html = $html['addible'];
		}
		
		$this->showSingleColumnRow('',
			"<div id='".$addibleItem['name']."'>".$html."</div>"
		);
	}
	
	/*
		this function is better get deprecated, bacause of the writing error in it...
	*/
	function prepareAddibleBoxesTempalte($addibleItems,$templates) {
		return $this -> prepareAddibleBoxesTemplate($addibleItems,$templates);
	}
	
	function prepareAddibleBoxesTemplate($addibleItems,$templates) {
		$html ='<div id="template_item_box" style="display:none">';
		
		foreach($addibleItems as $key => $value)
		{
			if(is_array($templates[$value['name']]))
				$template = $templates[$value['name']]['addible'];
			else
				$template = $templates[$value['name']];
			
			$html .='<div id="'.$value['name'].'_template_box" style="display:none">';
			$replace=$value['replacements'];				
			if (empty($replace['%temp_name%']))
				$replace['%temp_name%']=$value['name'];

			if(is_array($value['subAddibles']))
			{
				foreach($value['subAddibles'] as $subAddibleKey => $subAddible)
				{
					if(is_array($templates[$value['name'].'_'.$subAddible['name']]))
						$subAddibleTemplate = $templates[$value['name'].'_'.$subAddible['name']]['addible'];
					else
						$subAddibleTemplate = $templates[$value['name'].'_'.$subAddible['name']];
					
					$subAddibleHtml .='<div id="'.$value['name'].'_'.$subAddible['name'].'_template_box" style="display:none">';
					$subAddibleReplace=$subAddible['replacements'];				
					if (empty($subAddibleReplace['%temp_name%']))
						$subAddibleReplace['%temp_name%']=$value['name'].'['.$subAddible['name'].'][%{parent_item_number}%]';
					$subAddible_items_html = str_replace(
						array_keys($subAddibleReplace),
						array_values($subAddibleReplace),
						$subAddibleTemplate
					);
					$subAddibleHtml .=htmlspecialchars($subAddible_items_html);
		
					$subAddibleHtml .='</div>';

					$subAddibleLinkAndHeaderHtml = "<table class=\"subAddibleContentContainer\">
						<tr><td colspan=\"2\">".$subAddible['title']."<a href=\"javascript:cpfAddNewBox('".$value['name'].'['.$subAddible['name'].'][%{item_number}%]'."','".$value['name']."_".$subAddible['name']."_template_box','".$value['name'].'['.$subAddible['name'].'][%{item_number}%]'."', '".$subAddible['jsCallback']."', '%{item_number}%')\"><img border='0' alt='' src='interface/images/addible.png' /></a></td></tr>";
					
					if(is_array($templates[$value['name'].'_'.$subAddible['name']]))
					{
						$subAddibleHeaderReplace = array();
						if (isset($subAddible['replacements']) && !empty($subAddible['replacements']) )
						{
							foreach($subAddible['replacements'] as $subAddibleReplacementKey => $subAddibleReplacement)
								$subAddibleHeaderReplace[$subAddibleReplacementKey] = $subAddibleReplacement;
						}
						$subAddibleHeaderReplace['%temp_name%'] = $name.'['.$subAddible['name'].']';
					
						$subAddible_header_html = '';
						$subAddible_header_html = str_replace(
							array_keys($subAddibleHeaderReplace),
							array_values($subAddibleHeaderReplace),
							$templates[$value['name'].'_'.$subAddible['name']]['header']
						);
						$subAddible_header_html = str_replace(
							array_keys($subAddibleHeaderReplace),
							array_values($subAddibleHeaderReplace),
							$subAddible_header_html
						);

						$subAddibleLinkAndHeaderHtml .= "	<tr><td><div id='".$value['name'].'['.$subAddible['name'].'][%{item_number}%]'."header'>".$subAddible_header_html."</div></td></tr>";
					}
					
					$subAddibleLinkAndHeaderHtml .= "	<tr><td><div id='".$value['name'].'['.$subAddible['name'].'][%{item_number}%]'."'></div></td></tr>
					</table>";
						
					$replace['%subAddibleContainer_'.$subAddible['name'].'%'] = $subAddibleLinkAndHeaderHtml;
				}
			}
				
			$items_html = str_replace(
				array_keys($replace),
				array_values($replace),
				$template
			);
			$html .=htmlspecialchars($items_html);

			$html .='</div>';
			
		}
		$html .= $subAddibleHtml.'</div>';
		echo $html;
	}
		
	function drawMultiTabs($items,$selected=NULL)
		{ // by babak
		ob_start();
		?>
		<tr class="<?php echo $this->getNextZebraClass()?>">
			<td colspan="2" valign="top" class="field-insert" style="padding=0px;">
				<table width='100%' border='0' cellpadding='2' cellspacing='0' style="">
					<tr>
						<?php
						foreach($items as $item){
							if ($selected == $item['id'])
								$moreStyle = 'border-bottom:none; background-color:#edf3ff';
							else
								$moreStyle = 'border-bottom:1px solid #b6c4d6; ';
							?>
							<td id="<?php echo $item['id']?>" width='50' style='padding-left:7px;padding-right:7px; border:1px solid #b6c4d6; <?php echo $moreStyle?>' align='center' >
								<a href='<?php echo $item['href']?>' onClick='<?php echo $item['onClick']?>'>
									<?php echo $item['label']?>
								</a>
							</td>
							<td width='5' style='border-bottom:1px solid #b6c4d6;' >&nbsp;</td>
							<?php
						}
						?>
						
					<td style="border-bottom:1px solid #b6c4d6;" > </td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	}
	
	function showDropDownTreeCustom($title, $name, $value = "", $sectionName="", $comment='', $options = array() ){
		$direction = $options['direction'];
		$disabled = $options['disabled'];
		if ($options['langId'])
			$langId = $options['langId'];
		//cmfcHtml::printr($langId);
		
		global $_cp;
		global $_ws;
		global $dbConnLink;
		
		#--(Begin)-->Website main tree object initializing
		@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
		$db->conn=&$dbConnLink;
		$mainTree = cmfcHierarchicalSystem::factory('dbOld',
			array(
				'tableName'=>$_ws['physicalTables']['categories']['tableName'],
				'dbInstance'=>$db,
				'prefix'=> '' 
			)
		);
		$mainTree->setColnLink($_ws['physicalTables']['categories']['columns']['link']);
		$mainTree->setColnLeftNumber('lft');
		$mainTree->setColnRightNumber('rgt');
		$mainTree->setColnLevelNumber($_ws['physicalTables']['categories']['columns']['level']);
		#--(End)-->Website main tree object initializing
		
		
		$items = '';
		if ($options['query']){
			$items = cmfcMySql::getRowsCustom($options['query']);
		}
		else{
			$items = $mainTree->branchAsArray(
				$_cp['sectionsInfo'][$sectionName]['nodeId'],
				array('*')
			);
		}
		
		$excludes = array();
		if ($options['excludeInfo']){
			$excludeKey = $options['excludeInfo']['excludeBy'];
			$excludeValues = $options['excludeInfo']['excludeValues'];
			if (is_array($excludeValues)){
				foreach ($excludeValues as $excludeData){
					$excludes[] = $excludeData[ $excludeKey];
				}
			}
		}
		
		if ($options['excludedNodes'])
		{
			$excludes = $options['excludedNodes'];
		}
		
		if ($langId){
			$langItems = '';
			if ($items){
				foreach ($items as $item){
					//cmfcHtml::printr($item);
					//$item = cmfcMySql::convertColumnNames($item, $_ws['physicalTables']['categories']['columns']);
					$langItem = wsfGetCategoryInfoByCurrentLanguage($item['id'], $mainTree);
					
					if ($langItem){
						if ( in_array($item['id'], $excludes) && $item['id'] !== $value )
						{
							$disabled = true;
						}
						else
						{
							$disabled = false;
						}
						
						//cmfcHtml::printr(in_array($item['id'], $excludes) );
						
						$langItems[] = array(
							$_ws['physicalTables']['categories']['columns']['id'] => $item['id'],
							$_ws['physicalTables']['categories']['columns']['name'] => $langItem['name'],
							$_ws['physicalTables']['categories']['columns']['level'] => $item['level'],
							'disabled' => $disabled,
							'hasChild' => $item['hasChild'],
						);
					}
				}
				
				//cmfcHtml::printr($langItems);
			}
			$items = $langItems;
		}
		
		return $this->showCustomRow($title, 
			cmfcHtml::drawDropDownCustom(
				array(
					'controlName'=>$name,
					'orgValue'=>$value,
					'items'=>$items,
					'valueColumnName'=>$_ws['physicalTables']['categories']['columns']['id'],
					'titleColumnName'=>$_ws['physicalTables']['categories']['columns']['name'],
					'levelColumnName'=>$_ws['physicalTables']['categories']['columns']['level'],
					'hasChildColumnName'=>'hasChild',
					'isParentsSelectable'=>$options['selectParents'],
					'defaultValue'=>'',
					'defaultTitle'=>'',
					'attributes'=>array('style'=>'direction:'.$direction),
					'interface'=>array(
						'direction'=>($direction=='ltr')?'leftToRight':'rightToLeft',
						'isIe'=>true,
					)
				)
			),
			$comment
		);
		
	}
	
	function showDropDownTree($title, $name, $value = "", $sectionName="", $comment='', $direction = "",$disabled=false) {
		global $_cp;
		global $_ws;
		global $dbConnLink;
		#--(Begin)-->Website main tree object initializing
		@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
		$db->conn=&$dbConnLink;
		$mainTree = cmfcHierarchicalSystem::factory('dbOld',
			array(
				'tableName'=>$_ws['physicalTables']['categories']['tableName'],
				'dbInstance'=>$db,
				'prefix'=> '' 
			)
		);
		$mainTree->setColnLink($_ws['physicalTables']['categories']['columns']['link']);
		$mainTree->setColnLeftNumber('lft');
		$mainTree->setColnRightNumber('rgt');
		$mainTree->setColnLevelNumber($_ws['physicalTables']['categories']['columns']['level']);
		#--(End)-->Website main tree object initializing
		
		return $this->showCustomRow($title, 
			cmfcHtml::drawDropDownCustom(array(
					'controlName'=>$name,
					'orgValue'=>$value,
					'items'=>$mainTree->branchAsArray($_cp['sectionsInfo'][$sectionName]['nodeId'],array('*')),
					'valueColumnName'=>$_ws['physicalTables']['categories']['columns']['id'],
					'titleColumnName'=>$_ws['physicalTables']['categories']['columns']['name'],
					'levelColumnName'=>$_ws['physicalTables']['categories']['columns']['level'],
					'hasChildColumnName'=>'hasChild',
					'defaultValue'=>'',
					'defaultTitle'=>'',
					'attributes'=>array('style'=>'direction:'.$direction),
					'interface'=>array(
						'direction'=>($direction=='ltr')?'leftToRight':'rightToLeft',
						'isIe'=>true
					)
				)
			),
			$comment
		);
		
	}
	
	function showPopupTreeInput(
		$title, 
		$sectionName, 
		$idFieldName, 
		$idFieldValue, 
		$pathFieldName, 
		$pathFieldValue, 
		$selectTitleValuePair= array('value' => 'id', 'title' => 'name'), 
		$selectType= "singleSelect", 
		$selectTypeDetails='', 
		$pageSettings = array ("width" => '500', "height" => '500' ,'scrollbars'=>'1'),
		$buttonTitle = '[انتخاب]',
		$options = ''
	){
		if (!is_array($options) && !empty($options) ){
			$extraHtml = $options;
		}
		else{
			if (is_array($options['html']))
				$extraHtml = implode('<br />', $options['html']);
		}
		
		global $_cp;
		$attributes = "";
		$html = '';
		
		if (is_array($pageSettings)){
			foreach ($pageSettings as $key => $value){
				$attributes .= "$comma $key=$value";
				if (!isset($comma) ) $comma = ',';
			}
		}
		$sectionInfo = $_cp['sectionsInfo'][$sectionName];
		$pageTitle = $sectionInfo['title'];
		$categoryPath = wsfGetCategoryPathByLang(
			$sectionInfo['tableInfo']['tableName'],
			$idFieldValue
		);
		if ($selectType == "multiSelect"){
			$categoryPath = "";
			/*
			$category= cmfcMySql::multiIdValueToValue(
					$idFieldValue, 
					$sectionInfo['tableInfo']['tableName'],
					$selectTitleValuePair['value'],
					$selectTitleValuePair['title']
			);
			foreach ($category as $value){
				$categoryPath .= $sep. $value;
				if (empty($sep)) $sep = ', ';
			}
			*/
			
			$categoryPath .= wsfGetCategoryPathByLang(
				$sectionInfo['tableInfo']['tableName'],
				$idFieldValue
			);
			$categoryPath .= ', ';
		}
		
		$pageType = 'popup';
		
		if ($options['functions']){
			foreach ($options['functions'] as $function => $arguement){
				$extraParams = '&functions['.$function.']='.$arguement;
			}
		}
		
		$title .= "<A onclick = \"window.open('popup.php?sn=".$sectionName."&id=".$idFieldName."&path=".$pathFieldName."&pt=".$pageType."&st=".$selectType."&std=".$selectTypeDetails."&lang=".$_GET['lang'].$extraParams."', 'new','".$attributes."')\" href='javascript:void(0)'> ".$buttonTitle." </A>";
		$html .= "<span id='".$pathFieldName."'/>"
			.$categoryPath 
			."</span>";
		$html .= "<input type='hidden' name='".$idFieldName."' id='".$idFieldName."' value='".$idFieldValue."'/>";
		$this->showCustomRow($title, $html.$extraHtml, '');
	}
	
	function showSingleColumnRow($title='',$content='',$comment='', $id='', $options = '') {
		$idAttrib = '';
		if (!empty($id))
			$idAttrib = ' id="'.$id.'" ';
		echo '
		<tr class="'.$this->getNextZebraClass().'">
			<td valign="top" colspan="2" class="field-insert" '.$idAttrib.' >
				'.$content.'
			</td>
		</tr>
		';
	}
	
	function showCustomRow($title='',$content='',$comment='', $options = '') {
		$numberOfCreatedColumns = 0;
		$trAttributes = cmfcHtml::attributesToHtml($options['attributes']);
		echo '<tr class="'.$this->getNextZebraClass().'" '.$trAttributes.'>';
		
		if ($this->_commonColumns){
			foreach ($this->commonColumns as $columns){
				$attributes = cmfcHtml::attributesToHtml($columns['attributes']);
				echo '
					<td valign="top" class="field-subtitle" >
						<input name="'.$columns['name'].'" id="'.$columns['id'].'" value="'.$columns['defaultValue'].'" class="'.$columns['class'].'" type="'.$columns['type'].'" alt="'.$columns['commont'].'" '.$attributes.' />
					</td>';
			}
		}
		
		if($this->_overrideFieldInputs ){
			foreach ($this->_overrideFieldInputs as $key => $value){
				if (strpos($content, $key) !== false)
					$content .= $value;
			}
		}
		
		echo '
			<td valign="top" class="field-subtitle">
				'.$title.'
				<br />
				<span class="comment">'.$comment.'</span>
			</td>';
		$numberOfCreatedColumns ++;
		
		$colspan = $this->numberOfColumns - $numberOfCreatedColumns;
		echo '
			<td valign="top" class="field-insert" colspan= "'.$colspan.'">
				'.$content.'
			</td>
		</tr>
		';
	}
	
	function showInputRow($title, $name, $value = "", $comment='', $size = "40", $direction = "",$disabled=false, $readOnly=false, $attributes=array()) {
		if ($disabled==true) $disabled='disabled="disabled"';
		if ($readOnly==true) $readOnly='readonly="readonly"';
		if($attributes)
			$attributesHtml = cmfcHtml::attributesToHtml($attributes);

		$value = str_replace('"', '&quot;', $value);
		
		$html='<input name="'.$name.'" id="'.$name.'" type="text" class="input" size="'.$size.'" value="'.$value.'" dir="'.$direction.'" '.$disabled.' '.$readOnly.' '.$attributesHtml.' />';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showInputRowCustom($title, $name, $value = "", $comment='', $options = '') {
		 
		$size = "40";
		if ($options['size'])
			$size = $options['size'];
		
		$direction = "";
		if ($options['direction'])
			$direction = $options['direction'];
		
		$disabled=false;
		if ($options['disabled'])
			$disabled = $options['disabled'];
		
		if ($disabled==true)
			$disabled='disabled="disabled"';
		
		$html='<input name="'.$name.'" id="'.$name.'" type="text" class="input" size="'.$size.'" value="'.$value.'" dir="'.$direction.'" '.$disabled.' />';
		
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showInputPasswordRow($title, $name, $value = "", $comment='', $size = "40", $direction = "",$disabled=false) {
		if ($disabled==true) $disabled='disabled="disabled"';
		
		$html='<input name="'.$name.'" id="'.$name.'" type="password" class="input" size="'.$size.'" value="'.$value.'" dir="'.$direction.'" '.$disabled.' />';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showInputPasswordRowCustom($title, $name, $value = "", $comment='', $options = array() ) {
		
		$size = "40";
		if ($options['size'])
			$size = $options['size'];
		
		$direction = "";
		if ($options['direction'])
			$direction = $options['direction'];
		
		$disabled=false;
		if ($options['disabled'])
			$disabled = $options['disabled'];
		
		if ($disabled==true)
			$disabled='disabled="disabled"';
		
		if ($disabled==true) $disabled='disabled="disabled"';
		
		$html='<input name="'.$name.'" id="'.$name.'" type="password" class="input" size="'.$size.'" value="'.$value.'" dir="'.$direction.'" '.$disabled.' />';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showSeparatorRow($title, $direction = "") {
		$this->resetZebraClasses();
		echo '
		<tr class="table-sub-header">
			<td colspan="4" class="table-sub-header" >'.$title.'</td>
		</tr>';
	} 
	
	function showTextareaRowCustom($title, $name, $value = "", $comment='', $options = '') { 
		$cols = "";
		$rows = 10;
		$width=550;
		$direction='';
		$disabled='';
		
		if ($options['columns'])
			$cols = $options['columns'];
		
		if ($options['cols'])
			$cols = $options['cols'];
		
		if ($options['rows'])
			$rows = $options['rows'];
		
		if ($options['width'])
			$width = $options['width'];
		
		if ($options['direction'])
			$direction = $options['direction'];
		
		if ($options['disabled'])
			$disabled = $options['disabled'];
		
		if ($disabled==true) $disabled='disabled="disabled"';
		$html='<textarea name="'.$name.'" id="'.$name.'" type="text" class="textarea" cols="'.$cols.'" rows="'.$rows.'" dir="'.$direction.'" style="width:'.$width.'" '.$disabled.' >'.$value.'</textarea>';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showTextareaRow($title, $name, $value = "", $comment='', $cols = "", $rows = 10, $width=550, $direction='', $disabled='') { 
		if ($disabled==true) $disabled='disabled="disabled"';
		$html='<textarea name="'.$name.'" id="'.$name.'" type="text" class="textarea" cols="'.$cols.'" rows="'.$rows.'" dir="'.$direction.'" style="width:'.$width.'" '.$disabled.' >'.$value.'</textarea>';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showCheckBoxRowCustom($title, $name , $value="", $defaultValue= "1", $options = array() ) {
		$disabled=false;
		if ($options['disabled'])
			$disabled = $options['disabled'];
		
		if ($value==$defaultValue) $checked='checked="checked"';
		
		if ($disabled==true) $disabled='disabled="disabled"';
		
		$html='<input name="'.$name.'" id="'.$name.'" type="checkbox" class="checkbox" value="'.$defaultValue.'" '.$disabled.' '.$checked.' />';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showCheckBoxRow($title, $name , $value="", $defaultValue= "1",$disabled=false) {  
		if ($value==$defaultValue) $checked='checked="checked"';
		if ($disabled==true) $disabled='disabled="disabled"';
		$html='<input name="'.$name.'" id="'.$name.'" type="checkbox" class="checkbox" value="'.$defaultValue.'" '.$disabled.' '.$checked.' />';
		$this->showCustomRow($title,$html,$comment);
	}
	
	function showHiddenInput($name, $value = "") {
		echo '<input type="hidden" id="'.$name.'" name="'.$name.'" value="'.$value.'">'."\n";
	}
	
	function showTableHeader($title, $colspan = 4, $anchor = "",$id="",$style="") {
		$this->_numberOfColumns = $colspan;
		echo '
		<table id="'.$id.'" class="table" width="100%" border="1" style="'.$style.'" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
  		<tr>
  		  	<td colspan="'.$colspan.'" class="table-header" >
  		  		
				<a name="'.$anchor.'">
  		  		'.$title.' 
  		  		</a>
				
  		  	</td>
  		</tr>
  		';
	}
	
	function showTableHeaderCustom($title, $options = array() ) {
		$colspan = 4;
		$anchor = "";
		$id="";
		$style="";
		
		if ($options['colspan'])
			$colspan = $options['colspan'];
		
		$this->_numberOfColumns = $colspan;
		
		if ($options['anchor'])
			$anchor = $options['anchor'];
		
		if ($options['id'])
			$id = $options['id'];
		
		if ($options['style'])
			$style = $options['style'];
		
		echo '
		<table id="'.$id.'" class="table" width="100%" border="1" style="'.$style.'" cellspacing="0" cellpadding="0" bordercolor="#d4dce7">
  		<tr>
  		  	<td colspan="'.$colspan.'" class="table-header" >
  		  		
				<a name="'.$anchor.'">
  		  		'.$title.' 
  		  		</a>
				
  		  	</td>
  		</tr>
  		';
		
		if ($options['titles']){
			echo '<tr>';
			foreach ($options['titles'] as $title){
				echo '<td class="table-sub-header" >'.$title.'</td>';
				$numColumns ++;
			}
			if ($numColumns < $this->_numberOfColumns){
				$colspan = $this-> _numberOfColumns - $numColumns;
				echo '<td class="table-sub-header" colspan="'.$colspan.'">&nbsp;</td>';
			}
			echo '</tr>';
		}
	}
	
	function showTableFooter() {
		echo '</table>';
	}
	
	function showFormHeader($formAction=null, $name = "myForm", $uploadform = false) {
	
		if ($formAction==null) 
			$formAction='?'.cmfcUrl::excludeQueryStringVars(array('sectionName', 'pageType'), 'get');
		
		if ($uploadform=true) 
			$enctype='multipart/form-data';
		else 
			$enctype='application/x-www-form-urlencoded';
		
		echo '<form name="'.$name.'" id="'.$name.'" action="'.$formAction.'" method="post" style="margin:0px;padding:0px" enctype="'.$enctype.'">';
	}
	
	function showFormFooterCustom($buttonsInfo) {
		$buttons = '';
		if (is_array($buttonsInfo)){
			foreach ($buttonsInfo as $info){
				$name = $info['name'];
				$type = $info['type'];
				$value = $info['value'];
				$attributes = cmfcHtml::attributesToHtml($info['attributes']);
				if (!$attributes)
					$attributes = ' style="cursor:pointer" ';
				if (empty($type))
					$type = 'submit';
				
				$buttons[] =
				'<input name="'.$name.'" class="button" type="'.$type.'" value="'.$value.'" '.$attributes.'/>';
			}
		}
		$buttonsList = '';
		if (!empty($buttons)){
			foreach ($buttons as $button)
				$buttonsList .=$button;
			
			echo '
			<tr class="table-row2">
				<td colspan="'.$this->_numberOfColumns.'" align="center" >'.$buttonsList.'</td>
			</tr>';	
		}
		$this->showTableFooter();
		echo '</form>';
	}
	
	function showFormFooter($javascript = "",$additional=null,$options=null, $createTableFooter = true) {
		$colspan = $this->_numberOfColumns;
		
		if ($options['colspan'])
			$colspan = $options['colspan'];
		
		if (!$colspan)
			$colspan = 2;
		
		$buttons='
		<input name="submit_save" class="button" type="submit"  value=" '.wsfGetValue(buttonSubmit).' "  style="cursor:pointer" />
		<input name="submit_cancel" class="button" type="submit"  value=" '.wsfGetValue(buttonCancel).' "  style="cursor:pointer" />
		<input name="Reset" class="button" type="reset" value=" '.wsfGetValue(buttonReset).' "   style="cursor:pointer" />
		';
		echo '
			<tr class="table-row2">
    			<td colspan="'.$colspan.'" valign="top" class="field-submit">
    				'.$javascript.'
    				'.$buttons.'
					'.$additional.'
				</td>
    		</tr>';
		
		if ($createTableFooter == true)
			$this->showTableFooter();
		
		echo '</form>';
	}
	
	function showMultipleColumnsRow($title, $columns ='', $head = false, $attributes = ''){
		if (!$head)
			$class = 'field-insert';
		else
			$class = 'field-subtitle';
		
		if (is_array($attributes)){
			$comma = '';
			foreach ($attributes as $key => $value){
				$attributesList .= "$comma $key=$value";
				$comma = ',';
			}
		}
		if (is_array($columns)){
			?>
			<tr class="<?php echo $this->getNextZebraClass()?>">
				<td valign="top" class="field-subtitle">
				<?php echo $title?>
				</td>					
				<?php
				foreach ($columns as $column){
					?>
					<td valign="top" class="<?php echo $class?>" <?php echo $attributesList?> >
					<?php echo $column?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
		}
	}
	
	function createCustomInput($options){
		$html = '';
		//cmfcHtml::printr($options);
		if (is_array($options)){
			$name = $options['name'];
			
			$id = $options['id'];
			if (!$id)
				$id = $name;
			
			$value = $options['value'];
			
			$type = $options['type'];
			
			$template = $options['template'];
			if (!$template)
				$template = '%s';
			
			unset($options['name'], $options['id'], $options['type'], $options['value'], $options['template']);
			
			$attributes = cmfcHtml::attributesToHtml($options);
			$html = sprintf(
				$template,
				'<input name="'.$name.'" id="'.$id.'" value="'.$value.'" type="'.$type.'" '.$attributes.' />'
			);
		}
		return $html;
	}
	function showInlineEditRow($title, $fieldName, $groupNumber, $value, $comments = '', $direction = 'rtl', $size = '50%'){
		ob_start();
		?>
		<div id="inlineEdits[<?php echo $fieldName?>][<?php echo $groupNumber?>][view]" style="width:100%;" onclick="cpfChangeEditableItemTag('<?php echo $fieldName?>', '<?php echo $groupNumber?>');">
		<?php echo $value?>&nbsp;
		</div>
		
		<div id="inlineEdits[<?php echo $fieldName?>][<?php echo $groupNumber?>][edit]" style="width:100%; display:none" >
			<input name="rows[<?php echo $groupNumber?>][columns][<?php echo $fieldName?>]" id="rows[<?php echo $groupNumber?>][columns][<?php echo $fieldName?>]" class="input" dir="<?php echo $direction?>" onblur="cpfChangeEditableItemTag('<?php echo $fieldName?>', '<?php echo $groupNumber?>');" size="<?php echo $size?>" value="<?php echo $value?>" />
		</div>
		<?php
		if (!defined('cpfChangeEditableItemTag') ){
			?>
			<script language="javascript">
			function cpfChangeEditableItemTag(field, num){
				view = document.getElementById('inlineEdits['+field+']['+num+'][view]');
				edit = document.getElementById('inlineEdits['+field+']['+num+'][edit]');
				input = document.getElementById('rows['+num+'][columns]['+field+']');
				checkbox = document.getElementById('rows['+num+'][selected]');
				//alert(input.value);
				//alert(edit);
				if (checkbox){
					if (checkbox.disabled == true){
						return;
					}
				}
				if (view){
					if (view.style.display == 'none'){
						view.style.display = '';
						view.innerHTML = input.value+'&nbsp;';
					}
					else
						view.style.display = 'none';
				}
				if (edit){
					if (edit.style.display == 'none'){
						if (checkbox)
							checkbox.checked = true;
						
						edit.style.display = '';
						input.focus();
					}
					else
						edit.style.display = 'none';
				}
			}
			</script>
			<?php
			define ('cpfChangeEditableItemTag', 1);
		}
		$html = ob_get_contents();
		ob_end_clean();
		$this->showCustomRow($title, $html, $comments);
	}
}


function startInnerTable($title, $colspan = 4, $anchor = "",$id="",$style="") {
		?>
		<tr class="<?php echo $this->getNextZebraClass()?>">
		<td colspan="<?php echo $colspan?>" valign="top">
		<?php
		$this->showTableHeader($title, $colspan, $anchor, $id, $style);
	}
	
function endInnerTable(){
	$this->showTableFooter();
	?>
	</td>
	</tr>
	<?php
}
?>