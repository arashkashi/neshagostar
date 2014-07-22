<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	$catTableInfo = $_ws['sectionInfo']['categoriesTableInfo'];
	$catLangTableInfo = $_ws['sectionInfo']['catLangTableInfo'];
	$topTree = cmfcHierarchicalSystem::factory('dbOld',
		array(
				'tableName'=>$_ws['physicalTables']['categories']['tableName'],
				'dbInstance'=>$db,
				'prefix'=> '' 
			)
		);
	
	$table = $_ws['physicalTables']['news'];
	$sqlQuery = "SELECT `title`,`body`,`related_item` AS 'relatedItem', `category_id` AS 'categoryId' FROM ".$table['tableName'].
				" WHERE ".$table['columns']['archive']." = 0 ".
				" AND ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" ORDER BY ".$table['columns']['publishDatetime']." DESC LIMIT 4";
	$latestNews = cmfcMySql::getRowsCustom($sqlQuery);
	
	$table = $_ws['physicalTables']['ourCustomers'];
	$sqlQuery = "SELECT * FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" ORDER BY ".$table['columns']['id']." DESC LIMIT 3";
	$ourCustomers = cmfcMySql::getRowsCustom($sqlQuery);
	
	/*$table = $_ws['physicalTables']['standards'];
	$sqlQuery = "SELECT * FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" ORDER BY RAND()";
	$standard = cmfcMySql::loadCustom($sqlQuery);
	$standard = cmfcMysql::convertColumnNames($standard,$table['columns']);
	/*
	$table = $_ws['physicalTables']['products'];
	$sqlQuery = "SELECT `title`,`general_characteristics` AS 'generalCharacteristics',`photo_filename` AS 'photoFilename',`related_item` AS 'relatedItem', `category_id` AS 'categoryId' FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" ORDER BY RAND() DESC LIMIT 3";
	$randomProducts = cmfcMySql::getRowsCustom($sqlQuery);
	
	$table = $_ws['physicalTables']['relatedSites'];
	$sqlQuery = "SELECT `title`,`body`,`url`,`related_item` AS 'relatedItem' FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" ORDER BY ".$table['columns']['insertDatetime']." DESC LIMIT 3";
	$latestLinks = cmfcMySql::getRowsCustom($sqlQuery);*/
	
	$table = $_ws['physicalTables']['staticPages'];
	$sqlQuery = "SELECT * FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" AND ".$table['columns']['internalName']." = 'aboutUs'";
	$aboutUs = cmfcMySql::loadCustom($sqlQuery);
	
	$table = $_ws['physicalTables']['staticPages'];
	$sqlQuery = "SELECT * FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" AND ".$table['columns']['internalName']." = 'qualityControl'";
	$qualityControl = cmfcMySql::loadCustom($sqlQuery);
	/*
	$table = $_ws['physicalTables']['staticPages'];
	$sqlQuery = "SELECT `title`,`body`,`related_item` AS 'relatedItem' FROM ".$table['tableName'].
				" WHERE ".$table['columns']['languageId']." = ".$translation->languageInfo['id'].
				" AND ".$table['columns']['internalName']." = 'licenses'";
	$licenses = cmfcMySql::loadCustom($sqlQuery);
	*/
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
    <div class="homeBox">
    	<?php
		if ($aboutUs)
		{
			$aboutUs = cmfcMySql::convertColumnNames($aboutUs,$_ws['physicalTables']['staticPages']['columns']);
			?>
			<div class="row1">
				<h1 class="title"><?php echo $aboutUs['title']?></h1>
				<div class="body">
                	<?php
					if ($aboutUs['photoFilename'])
					{
						$result = $imageManipulator->getAsImageTag(array (
							'fileName' => $aboutUs['photoFilename'],
							'fileRelativePath' => $_ws['sectionsInfo']['aboutUs']['folderRelative'],
							'version' => 2,
							'actions' => array (
								 array (
									'subActions' => array (
										 array (
											'name' => 'resizeSmart',
											'parameters' => array (
												'width' => array (
													'min' => 167,
													'max' => 167,
													'zoomInIfRequire' => true,
													'ignoreAspectRatio' => false,
												),
												'height' => array (
													'min' => 115,
													'max' => 115,
													'zoomInIfRequire' => true,
													'ignoreAspectRatio' => false,
												),
												'priority' => array (
													 'biggerDimension',
												),
											),
										),
										 array (
											'name' => 'crop',
											'parameters' => array (
												'position' => 'center',
												'width' => 167,
												'height' => 115,
											),
										),
									),
								),
							),
							'attributes'=>array(
								'alt'=>$aboutUs['photoFilename'],
								'class'=>'floatImg'
							),
						));		
						echo $result;
					}
					echo cmfcString::briefText(strip_tags($aboutUs['body']),500);
					?><a href="<?php echo wsfPrepareUrl('?sn=aboutUs&lang='.$_GET['lang']);?>"><?php echo wsfGetValue('more');?></a>
				<br class="clearfloat" />
				</div>
			</div>
			<?php
		}
		?>
        <div class="row2">
        	<?php
			if ($ourCustomers)
			{
				?>
				<div class="right">
					<h1 class="title"><?php echo wsfGetValue('ourCustomers')?></h1>
					<div class="body">
                    	<?php
						foreach ($ourCustomers as $key=>$row)
						{
							$row = cmfcMySql::convertColumnNames($row,$_ws['physicalTables']['ourCustomers']['columns']);
							if ($row['photoFilename'])
							{
								$result = $imageManipulator->getAsImageTag(array (
									'fileName' => $row['photoFilename'],
									'fileRelativePath' => $_ws['sectionsInfo']['ourCustomers']['folderRelative'],
									'version' => 2,
									'actions' => array (
										 array (
											'subActions' => array (
												 array (
													'name' => 'resizeSmart',
													'parameters' => array (
														'width' => 108,
														'height' => 'auto',
														'priority' => array (
															 'width',
															 'height',
														),
													),
												),
											),
										),
									),
									'attributes'=>array(
										'alt'=>$row['photoFilename'],
										//'class'=>'floatImg'
									),
								));	
								?><a href="<?php echo wsfPrepareUrl('?sn=ourCustomers&pt=full&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>"><?php echo $result;?></a><?php
							}
						}?>
					</div>
				</div>
				<?php
			}
			if ($qualityControl)
			{?>
                <div class="left">
                    <h1 class="title"><?php echo $qualityControl['title']?></h1>
                    <div class="body">
                    	<?php
						if ($qualityControl['photoFilename'])
						{
							if ($standard['photoFilename'])
							{
								$result = $imageManipulator->getAsImageTag(array (
									'fileName' => $qualityControl['photoFilename'],
									'fileRelativePath' => $_ws['sectionsInfo']['staticPages']['folderRelative'],
									'version' => 2,
									'actions' => array (
										 array (
											'subActions' => array (
												 array (
													'name' => 'resizeSmart',
													'parameters' => array (
														'width' => array (
															'min' => 176,
															'max' => 176,
															'zoomInIfRequire' => true,
															'ignoreAspectRatio' => false,
														),
														'height' => array (
															'min' => 123,
															'max' => 123,
															'zoomInIfRequire' => true,
															'ignoreAspectRatio' => false,
														),
														'priority' => array (
															 'biggerDimension',
														),
													),
												),
												 array (
													'name' => 'crop',
													'parameters' => array (
														'position' => 'center',
														'width' => 176,
														'height' => 123,
													),
												),
											),
										),
									),
									'attributes'=>array(
										'alt'=>$qualityControl['title'],
										'class'=>'floatImg'
									),
								));	
								?><a href="<?php echo wsfPrepareUrl('?sn=standards&lang='.$_GET['lang'])?>"><?php echo $result;?></a><?php
							}
						}
                        echo cmfcString::briefText(strip_tags($qualityControl['body']),150);
						?> <a href="<?php echo wsfPrepareUrl('?sn=qualityControl&lang='.$_GET['lang'])?>"> <?php echo wsfGetValue('more');?> </a>
                        
                    </div>
                </div>
                <?php
			}?>
        <br class="clearfloat" />
        </div>
        
    </div>
    <?php
}
?>