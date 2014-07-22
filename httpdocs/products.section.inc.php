<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	
	
	
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	$catTableInfo = $_ws['sectionInfo']['categoriesTableInfo'];
	$catLangTableInfo = $_ws['sectionInfo']['catLangTableInfo'];
	

	$id = $_REQUEST['id'] ? $_REQUEST['id'] : FALSE;
	
	$pageTitle = '';
	$pageKeyWords = '';
	// Preparing breadcrumb
	$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>";
	// --
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}


if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
    <h1 class="title"><?php echo wsfGetValue($_GET['sn'])?></h1>
    <div class="page">
		
        <div class="pageBody">
			<?php
			$sqlQuery = " SELECT * FROM ".$tableInfo['tableName'].
						" WHERE ".$tableInfo['columns']['languageId']." = ".$translation->languageInfo['id'].
						$searchQuery.
						" ORDER BY ". $tableInfo['columns']['orderNumber']." ASC";
			$products = cmfcMySql::getRowsCustom($sqlQuery);
			if ($products)
			{
				foreach ($products as $key=>$product)
				{
					$product = cmfcMySql::convertColumnNames($product , $tableInfo['columns']);
					$link = wsfPrepareUrl('?sn='.$_GET['sn'].'&pt=full&id='.$product['relatedItem'].'&lang='.$_GET['lang']);
					?>
                    <div class="productsListItem">
						<a href="<?php echo $link?>">
							<?php
							if ($product['photoFilename'])
							{
								$result = $imageManipulator->getAsImageTag(array (
									'fileName' => $product['photoFilename'],
									'fileRelativePath' => $_ws['sectionInfo']['folderRelative'],
									'version' => 2,
									'actions' => array (
										 array (
											'subActions' => array (
												 array (
													'name' => 'resizeSmart',
													'parameters' => array (
														'width' => array (
															'min' => 250,
															'max' => 250,
															'zoomInIfRequire' => true,
															'ignoreAspectRatio' => false,
														),
														'height' => array (
															'min' => 150,
															'max' => 150,
															'zoomInIfRequire' => true,
															'ignoreAspectRatio' => false,
														),
														'priority' => array (
															 'smallerDimension',
														),
													),
												),
												 array (
													'name' => 'crop',
													'parameters' => array (
														'position' => 'center',
														'width' => '250',
														'height' => '150',
													),
												),
											),
										),
									),

								));		
								echo $result;	
							}
							?><br /><?php
							echo $product['title'];
							?>
						</a>
					</div><?php
				}
			}
			else
			{
				echo $translation->getValue('nothingFound');
			}
			?>
            <br class="clearfloat" />
    	</div>
    </div>
    <?php
}?>