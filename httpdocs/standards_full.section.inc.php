<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	
	if ($_GET['id'])
	{
		$id = $_GET['id'];
		
		$standard = cmfcMySql::loadWithMultiKeys(
			$tableInfo['tableName'],
			array(
				$tableInfo['columns']['relatedItem']=>$id,
				$tableInfo['columns']['languageId']=>$translation->languageInfo['id']
			)
		);
		$standard = cmfcMySql::convertColumnNames($standard,$tableInfo['columns']);
		
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
    <h1 class="title"><?php echo $standard['title']?></h1>
	<div class="page">
        <div class="pageBody">
            <div class="productsFull">
                <div class="productsImg">
                <?php
				/*
                if($standard['photoFilename'])
                {
                     $result = $imageManipulator->getAsImageTag(
                        array(
                            'fileName'=>$standard['photoFilename'],
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
                    <a href="<?php echo $_ws['sectionInfo']['folderRelative'].$standard['photoFilename']?>" class="lightbox">
                    <?php echo $result;?>
                    </a><?php
                }
				*/
				?>
                </div>
                <?php echo $standard['body']; ?>
                <?php
                if ($standard['relatedTests'])
                {
                    $standard['relatedTests'] = explode(';',$standard['relatedTests']);
                    ?>
                    <div class="productsSideBox">
                        <h1 class="title"><?php echo $translation->getValue('relatedTests');?></h1>
                        
                        <ul style="padding:15px;"><?php
                            foreach ($standard['relatedTests'] as $k=>$r)	
                            {
                                $pr = cmfcMySql::loadWithMultiKeys(
                                    $sectionInfo['testsTable']['tableName'],
                                    array(
                                        $sectionInfo['testsTable']['columns']['languageId']=>$translation->languageInfo['id'],
                                        $sectionInfo['testsTable']['columns']['relatedItem']=>$r
                                    )
                                );
                                ?><li><a href="<?php echo wsfPrepareUrl('?sn=tests&pt=full&id='.$pr['related_item'].'&lang='.$_GET['lang'])?>" target="_blank" title="<?php echo $pr['title'];?>"><?php echo $pr['title'];?></a></li><?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>
                <br class="clearfloat" />
            </div>
        </div>
    </div>
	<?php
}?>