<script>
	function openNodeEditor(url) {
		return GB_showCenter('<?php echo wsfGetValue('category')?>', url, 500, 500)
	}
</script>
<?php
	
	$message ='';
	#--(Begin)-->Templates Part
	$templates['treeNodeBox']="";
	$templates['treeNodeEditBox']="
		<table>".
			/*
			<tr>
				<td>".$translation->getValue('Name')." : </td>
				<td>
					<input style='width:150px;direction:rtl' name='%{item_base_name}%[columns][name]' id='%{item_base_name}%[columns][name]' type='text' value='%column_name_value%' size='50' />
				<td/>
				
			</tr>
			*/
			"<input name='%{item_base_name}%[referringId]' value='%column_id_value%' type='hidden' />
			<input name='%{item_base_name}%[changed]' id='%{item_base_name}%[changed]' value='0' type='hidden' />
			<input name='%{item_base_name}%[columns][name]' id='%{item_base_name}%[columns][name]' value='%column_name_value%' type='hidden' />
			<tr>
				<td>
				</td>
				<td>
					<a href=\"popup.php?sn=categoryLanguages&lang=" . $_GET['lang'] . "&id=%column_id_value%&categoryId=%column_id_value%&baseName=%{item_base_name}%\" onClick = \"return openNodeEditor(this.href)\">".wsfGetValue('editTitle')."
					</a>
				</td>
			</tr>
			<tr>
				<td> لینک : </td>
				<td>
					<input style='' name='%{item_base_name}%[columns][link]' id='%{item_base_name}%[columns][link]' type='text' value='%column_link_value%' dir='ltr' />
				<td/>
			</tr>
			<tr>
				<td>نمایش در منو : </td>
				<td>
					<input style='' name='%{item_base_name}%[columns][visible]' id='%{item_base_name}%[columns][visible]' type='checkbox' value='1' %column_visible_value_checked% />
				<td/>
			</tr>
		</table>
	";
	//print_r($templates);
	
	#--(End)-->Templates Part
	/*
			<tr>
				<td>شاخه : </td>
				<td><input style="width:150px;direction:rtl" name="%{item_base_name}%[%{item_number}%][columns][parent_id]" id="%{item_base_name}%[%{item_number}%][columns][parent_id]" type="text" value="%column_parent_id_value%" size="50"/></td>
			</tr>
	*/
	
	$myTree->setImages(array(
		 'editIcon'=>array('path'=>'interface/images/page_edit.png','width'=>16,'height'=>16),
		 'saveIcon'=>array('path'=>'interface/images/page_save.png','width'=>16,'height'=>16),
		 'addIcon'=>array('path'=>'interface/images/page_add.png','width'=>16,'height'=>16),
		 'deleteIcon'=>array('path'=>'interface/images/page_delete.png','width'=>16,'height'=>16),
		 'showRelatedIcon'=>array('path'=>'interface/images/pencil_go.gif','width'=>16,'height'=>16),
		 'moveDownIcon'=>array('path'=>'interface/images/arrow_down.png','width'=>10,'height'=>16),
		 'moveUpIcon'=>array('path'=>'interface/images/arrow_up.png','width'=>10,'height'=>16)
	));
	$myTree->_strings['deleteConfirm']=wsfGetValue('areYouSure:deleteAllSubBranches');
	$myTree->setColnLink('link');
	$myTree->setColnLeftNumber('lft');
	$myTree->setColnVisible('visible');
	$myTree->setColnRightNumber('rgt');
	$myTree->setColnLevelNumber('level');
	$myTree->setProtectedNodes($_ws["Main_Tree_Protected_Nodes"]);
	$myTree->setColumnsNames(array('id','name','name_en','visible','link','link_en', 'parent_id'));
	$myTree->setTitleColumnName('name');
	$myTree->setCbFuncManipulateTitleValue('wsfGetCategoryTitleByCurrentLanguage');
	$myTree->setDisplayMode('edit');
	$myTree->setTemplates($templates);	
	
	#--(Begin)-->assigning start node id according to selected section
	if (!empty($_cp['sectionInfo']['nodeId']))
		$myTree->setStartNodeId($_cp['sectionInfo']['nodeId']);
	#--(End)-->assigning start node id according to selected section
	
	if ($_GET['action']=='rebuild') {
		//$rootNodeId=$tree->getRootNodeId();
		$myTree->rebuild();
	}
	
	if (isset($_POST['submit_save'])) {
		/*
		echo '<pre style="direction:ltr">';
		print_r($_POST['myTreeChanges']);
		echo '</pre>';
		*/
		$rootId=$myTree->getRootNodeId();
	
		$equivalentIds=array();
		// cmfcHtml::printr($_POST);
		
		// print('<br />$myTree->_prefix: '. $myTree->_prefix.'Changes' .'<br />');
		
		// if (is_array($_POST[ $myTree->_prefix.'Changes']))
		// {
			// print('<br />IT IS Array<br />');
		// }else{
		
			// print('<br />Not Array.<br />');
		// }
		if (is_array($_POST[ $myTree->_prefix.'Changes']))
		foreach ($_POST[ $myTree->_prefix.'Changes'] as $changeInfo) {
		// if (is_array($_POST[ 'myTreeRow']))
		// foreach ($_POST['myTreeRow'] as $changeInfo) {
			unset($changeInfo['columns']['each']);
			unset($changeInfo['columns']['forEach']);
			unset($changeInfo['columns']['test']);
			unset($changeInfo['columns']['copy']);

			unset($changeInfo['columns']['remove']);
			unset($changeInfo['columns']['extend']);
			unset($changeInfo['columns']['associate']);
			unset($changeInfo['columns']['containsValue']);
			
			unset($changeInfo['columns_default']['each']);
			unset($changeInfo['columns']['forEach']);
			unset($changeInfo['columns_default']['test']);
			unset($changeInfo['columns_default']['copy']);
			unset($changeInfo['columns_default']['remove']);
			unset($changeInfo['columns_default']['extend']);
			unset($changeInfo['columns_default']['associate']);
			unset($changeInfo['columns_default']['containsValue']);
			
			
			$columnsValues=$changeInfo['columns'];
			//$columnsValues['editor_id']=$editorId;
			$prevColumnsValues=$changeInfo['columns_default'];
			
			$rowInfo = $_POST[ $myTree->_prefix.'Row'];
			//cmfcHtml::printr($rowInfo);
			//die;
			
			#--(Begin)-->Replace equivalent Ids
			if (isset($equivalentIds[$columnsValues[$myTree->colnId]]))
				$columnsValues[$myTree->colnId]=$equivalentIds[$columnsValues[$myTree->colnId]];
			if (isset($equivalentIds[$columnsValues[$myTree->colnParentId]]))
				$columnsValues[$myTree->colnParentId]=$equivalentIds[$columnsValues[$myTree->colnParentId]];
			#--(End)-->Replace equivalent Ids
			
			#--(Begin)-->Defining action type
			$action='update';
			
			if ($changeInfo['action']=='delete') {
				$action='delete';
			}
			elseif (preg_match('/.*new.*/', $columnsValues[$myTree->colnId])) {
				$action='insert';
			}
			elseif (isset($columnsValues[$myTree->colnParentId])) {
				if ($columnsValues[$myTree->colnParentId]!=$prevColumnsValues[$myTree->colnParentId])
					$action='changeParent';
			}
			elseif ($changeInfo['action']=='moveUp' or $changeInfo['action']=='moveDown') {
				$action=$changeInfo['action'];
			}
			
			//print( '<br />Action['. $myTree->colnId .']: '. $action .'<br />');
			
			#--(End)-->Defining action type
			/*
			$action_types_fa=array('update'=>'به روز رسانی', 'insert'=>'افزودن', 'delete'=>'حذف','moveDown'=>'حرکت به پایین','moveUp'=>'حرکت به بالا','changeParent'=>'تغییر سر شاخه');
			$action_fa=$action_types_fa[$action];
			$message .= "درخواست $action_fa : ";
			*/
			$message[] = $translation->getValue('requestFor'). ': '.$translation->getValue($action);
			
			#--(Begin)-->fetch node and parent node path
			if ($action=='delete' or $action=='changeParent') {
				$path=wsfGetCategoryPathForDb($myTree->tableName,$columnsValues[$myTree->colnId],null,$myTree->colnId);
	
				if (empty($columnsValues[$myTree->colnParentId]))
					$columnsValues[$myTree->colnParentId]=cmfcMySql::getColumnValue($columnsValues[$myTree->colnId], $myTree->tableName, $myTree->colnId, $myTree->colnParentId);
				$parentPath=wsfGetCategoryPathForDb($myTree->tableName,$columnsValues[$myTree->colnParentId],null,$myTree->colnId);
				$newPath=$parentPath.$columnsValues[$myTree->colnId].',';
			}
			#--(End)-->fetch node and parent node path
			
			if ($action == 'insert' || $action == 'update')
			{
				//cmfcHtml::printr($columnsValues);
				//cmfcHtml::printr($changeInfo);
				//die;
			}
			
			#--(Begin)-->Insert
			if ($action=='insert') {
				
				$myColumnsValues=$columnsValues;
				$referringId = $myColumnsValues[$myTree->colnId];
				unset($myColumnsValues[$myTree->colnId]);
				
				$parentId = $columnsValues[$myTree->colnParentId];
				if (!$parentId)
					$parentId = $rowInfo[$referringId][$myTree->colnParentId];
				
				if ($equivalentIds[$parentId])
					$parentId = $equivalentIds[$parentId];
				
				if (!$parentId)
					$parentId = $rootId;
				$myColumnsValues[$myTree->colnParentId] = $parentId;
				
				//die;
				
				if ($myTree->insert($parentId, '', $myColumnsValues)!==false) {
					$newId=mysql_insert_id();
					wsfCheckForCategoryLanguageData($referringId, $newId);
					$equivalentIds[$columnsValues[$myTree->colnId]]=$newId;
					//$message .= "شاخه ".$newId.' اضافه شد <br />';
					$message[] = sprintf($translation->getValue('branchAdded:1p'), $newId);
				}
				else {
					break;
				}
			}
			#--(End)-->Insert
			
			#--(Begin)-->Delete
			if ($action=='delete') {
				if (cmfcMySql::load($myTree->tableName,$myTree->colnId,$columnsValues[$myTree->colnId]))
				if ($myTree->deleteAll($columnsValues[$myTree->colnId])) {
					//$message.="شاخه ".$columnsValues[$myTree->colnId].' حذف شد </br>';
					$message[] = sprintf($translation->getValue('branchDeleted:1p'), $columnsValues[$myTree->colnId]);
					
					cpfApplyCategoriesChangesToAllRelatedTables('delete',
						array(
							'id'=>$columnsValues[$myTree->colnId],
							'path'=>$path,
							'parentId'=>$columnsValues[$myTree->colnParentId],
							'parentPath'=>$parentPath
						)
					);
					wsfDeleteCategoryLanguageData($columnsValues[$myTree->colnId]);
				} 
				else {
					if ($myTree->errorNo==Tree_System_Err_Attempting_To_Delete_Protected_Node){
						//$message.="شاخه ای که قصد حذف آن را دارید حفاظت شده است";
						$message[] = $translation->getValue('protectedNode:unableToDelete');
					}
					break;
				}
				
			}
			#--(End)-->Delete
			
			#--(Begin)-->Move node
			if ($action=='moveDown' or $action=='moveUp') {
				$position=($action=='moveDown')?'after':'before';
				if ($myTree->changePositionAll($columnsValues[$myTree->colnId],$columnsValues['___near_node_id'],$position)) {
					//$message.="شاخه ".$columnsValues[$myTree->colnId].' با شاخه '.$columnsValues['___near_node_id'].' جابجا شد <br />';
					$message[] = sprintf(
						$translation->getValue('branchPlaceSwapped:2p'), 
						$columnsValues[$myTree->colnId], 
						$columnsValues['___near_node_id']
					);
				} else {
					break;
				}
			}
			#--(End)-->Move node
			
			#--(Begin)-->change node parent
			if ($action=='changeParent') {
				if (empty($columnsValues[$myTree->colnParentId])) $columnsValues[$myTree->colnParentId]=$rootId;
				if ($myTree->moveAll($columnsValues[$myTree->colnId], $columnsValues[$myTree->colnParentId])) {
					//$message.="شاخه ".$columnsValues[$myTree->colnId].' منتقل شد به شاخه '.$columnsValues[$myTree->colnParentId].'<br />';
					$message[] = sprintf(
						$translation->getValue('branchParentChanged:2p'), 
						$columnsValues[$myTree->colnId], 
						$columnsValues[$myTree->colnParentId]
					);
										
					cpfApplyCategoriesChangesToAllRelatedTables('changeParent',
						array('id'=>$columnsValues[$myTree->colnId],'path'=>$path),
						array('id'=>$columnsValues[$myTree->colnId],'path'=>$newPath)
					);
				} else {
					if ($myTree->errorNo==Tree_System_Err_Attempting_To_Move_Protected_Node){
						//$message.="شاخه که قصد انتقال آن را دارید حفاظت شده است";
						$message[] = $translation->getValue('protectedNode:unableToMove');
					}
					break;
				}
			}
			#--(End)-->change node parent
			
			#--(Begin)-->Update
			if ($action=='update' or $action=='changeParent') {
				$myColumnsValues=$columnsValues;
				unset($myColumnsValues[$myTree->colnId]);
				//print_r($myTree);
				foreach ($_ws['physicalTables'] as $tablesKey => $tablesValue){
					if ($tablesValue['tableName'] == $myTree->tableName)
						$tableInfo = $_ws['physicalTables'][ $tablesKey ];
				}
				$columns = array ();
				foreach ($tableInfo['columns'] as $colKey => $colName){
					if (isset($myColumnsValues[$colName]))
						$columns [$colName] = $myColumnsValues[$colName];
				}
	
				if (cmfcMySql::update($myTree->tableName,$myTree->colnId,$columns,$columnsValues[$myTree->colnId])) {
					//$message.="شاخه ".$columnsValues[$myTree->colnId].' به روز شد <br />';
					$message[] = sprintf( $translation->getValue('branchUpdated:1p'), $columnsValues[$myTree->colnId]);
				} else {
					break;
				}
				
			}
			#--(End)-->Update
		}
		wsfClearInvalidCategoryLanguageData();
		if (is_array($message)){
			echo implode('<br />', $message);
		}
		//echo $message;
	}
	?>
	
	<?php echo cpfCreateBreadCrumb($translation->getValue('tree') )?>
	<div id="waiting" style="margin-bottom:5px; color:#FF0000; text-decoration:blink">
		<!--درخت در حال بارگذاری می‌باشد ، لطفا چند لحظه صبر نمایید...-->
		<?php echo $translation->getValue('treeLoadingPleaseWait')?>
	</div>
	
    <?php 	$cats = cmfcMySql::getRowsCustom('SELECT * FROM '.$myTree->tableName.' WHERE internal_name IS NOT NULL AND id<>1 ORDER BY name ASC');
	/*
	?>
	<table  class="table" align="center" width="600" border="1" cellspacing="0" cellpadding="0" >
		<tr>
			<td nowrap="nowrap" class="table-title field-title" align="center"><b>نام شاخه</b></td>
			<td nowrap="nowrap" class="table-title field-title" align="center"><b>لینک</b></td>
		</tr>
		<?php         foreach ($cats as $key=>$value)
        {
            ?>
            <tr>
                <td nowrap="nowrap" class="field-title"  style="height: 20px;"><?php echo $value['name']?></td>
                <td nowrap="nowrap" class="field-title" dir="ltr" align="left"  style="height: 20px;"><?php echo '/sn/'.$value['internal_name'].''?></td>
            </tr>
            <?php          }
         ?>
	</table>
    <?php
	/* */
	?>
	<form method="post" action="?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName', 'pageType',),'get')?>">
		<input type="submit" name="submit_save" value="<?php echo $translation->getValue('Save')?>" class="button" />&nbsp;
		 |  &nbsp;
		 
		<a href="?<?php echo cmfcUrl::excludeQueryStringVars(array('sectionName', 'pageType', 'action'),'get')?>&action=rebuild" onclick="return <?php echo $js->jsfConfimationMessage($translation->getValue('areYouSure') )?>"
			<?php 
            /*title="در صورت عدم نمایش صحیح درخت روی این لینک کلیک کنید" 
            */
            ?>
            title="<?php echo $translation->getValue('clickIfTreeCorrupted')?>">
            <!--بازسازی درخت (نظم درخت بهم خواهد ریخت!) -->
            <?php echo $translation->getValue('organizeTree')?>
		</a>
		<br/>
		
		<br />
		<strong style="color:red">
			<!--لطفا بعد از انجام تغییرات مورد نظرتان روی دکمه ذخیره کلید کنید تا تغییرات انجام شده ذخیره شوند.-->
			<?php echo $translation->getValue('pleaseUseSaveButtonToCommitChanges')?>
		</strong>
		<hr/>
		<?php 		$myTree->printDefaultStyles();
		$myTree->printAll();
		if ($myTree->errorNo==Tree_View_Structure_Is_Corrupted) {
			?>
			<br/>
			<span style="color:red">
			<!--ظاهرا ساختار درخت به هم ریخته است ، لطفا از لینک بالا برای تعمیر آن استفاده کنید و در صورتی که مشکل حل نشد بخش فنی را مطلع کنید
			-->
			<?php echo $translation->getValue('treeMightBeCorrupted')?>
			</span>
			<?php 		}
		?>
		<script>
			document.getElementById('waiting').style.display='none';
		</script>
	</form>