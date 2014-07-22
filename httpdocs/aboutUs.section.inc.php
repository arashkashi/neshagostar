<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	
	$page = cmfcMySql::loadWithMultiKeys(
		$tableInfo['tableName'],
		array(
			  $tableInfo['columns']['internalName']=>$_GET['sn'],
			  $tableInfo['columns']['languageId']=>$translation->languageInfo['id']
		)
	);
	$pages = FALSE;
	
	$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$translation->languageInfo['sName'])."'>".$translation->getValue('home')."</a>"
					." » ".
					$page['title'];
	
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
    <h1 class="title"><?php echo $translation->getValue($_ws['sectionInfo']['name'])?></h1>
	<div class="body">
		<?php
        
        if ($page)
        {
            $page = cmfcMySql::convertColumnNames($page,$tableInfo['columns']);
            ?>
            <h2 class="title"><?php echo $page['title']?></h2>
            <div class="aboutUs">
                <?php
                if($page['photoFilename'])
                {
                     $result = $imageManipulator->getAsImageTag(
                        array(
                            'fileName'=>$page['photoFilename'],
                            'fileRelativePath'=>$sectionInfo['folderRelative'],			
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
                                'alt'=>'',
                                'class'=>'floatImg'
                            ),
                        )
                    );
                    echo $result;
                }
                ?>
                <?php echo $page['body']; ?>
            
            <br class="clearfloat" />
            </div>
            <?php
        }
        ?>
	</div>
	<?php
}?>