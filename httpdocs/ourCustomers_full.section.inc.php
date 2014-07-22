<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	
	if ($_GET['id'])
	{
		$id = $_GET['id'];
		
		$row = cmfcMySql::loadWithMultiKeys(
			$tableInfo['tableName'],
			array(
				$tableInfo['columns']['relatedItem']=>$id,
				$tableInfo['columns']['languageId']=>$translation->languageInfo['id']
			)
		);
		$row = cmfcMySql::convertColumnNames($row,$tableInfo['columns']);
		
		// Preparing breadcrumb
		$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>"
						." &raquo; ".
						"<a href='".wsfPrepareUrl('?sn='.$_ws['sectionInfo']['name'].'&lang='.$_GET['lang'])."'>".$translation->getValue($_ws['sectionInfo']['name'])."</a>"
						." &raquo; ".
						cmfcString::briefText($row['title'],20)
						;
		// --
	}
	
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
	<div class="page">
    	<?php
		if ($row)
		{
			?>
			<h1 class="title"><?php echo $row['title']?></h1>
			<?php
		}
		else
		{
			?>
			<h1 class="title"><?php echo $translation->getValue('error'); ?></h1>
			<?php
		}
		?>
        <div class="pageBody">
			<?php
			if ($row)
			{
				if($row['photoFilename'])
				{
					$result = $imageManipulator->getAsImageTag(array (
						'fileName' => $row['photoFilename'],
						'fileRelativePath' => $sectionInfo['folderRelative'],
						'version' => 2,
						'actions' => array (
							 array (
								'subActions' => array (
									 array (
										'name' => 'resizeSmart',
										'parameters' => array (
											'width' => 200,
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
							'class'=>'floatImage'
						),
					));		
					echo $result;
				}
				?>
                <?php 
				echo $row['body'];
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