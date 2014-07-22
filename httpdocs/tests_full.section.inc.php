<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	
	if ($_GET['id'])
	{
		$id = $_GET['id'];
		
		$test = cmfcMySql::loadWithMultiKeys(
			$tableInfo['tableName'],
			array(
				$tableInfo['columns']['relatedItem']=>$id,
				$tableInfo['columns']['languageId']=>$translation->languageInfo['id']
			)
		);
		$test = cmfcMySql::convertColumnNames($test,$tableInfo['columns']);
		
		// Preparing breadcrumb
		$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>";
		// --
	}
	else
	{
		
	}
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
    <h1 class="title"><?php echo $test['title']?></h1>
	<div class="page">
        <div class="pageBody">
                <div class="productsImg">
                <?php
				/*
                if($test['photoFilename'])
                {
                     $result = $imageManipulator->getAsImageTag(
                        array(
                            'fileName'=>$test['photoFilename'],
                            'fileRelativePath'=>$sectionInfo['folderRelative'],			
                            'version' => 2,
                            'actions' => array (
                                 array (
                                    'subActions' => array (
                                         array (
                                            'name' => 'resizeSmart',
                                            'parameters' => array (
                                                'width' => 180,
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
                                'alt'=>''
                            ),
                        )
                    );
                    ?>
                    <a href="<?php echo $_ws['sectionInfo']['folderRelative'].$test['photoFilename']?>" class="lightbox">
                    <?php echo $result;?>
                    </a><?php
                }
				*/
                ?>
                </div>
			<?php
			echo $test['body'];
			?>
               
                
        </div>
        <br class="clearfloat" />
    </div>
	<?php
}?>