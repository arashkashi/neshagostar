<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	
	if ($_GET['id'])
	{
		$id = $_GET['id'];
		
		$product = cmfcMySql::loadWithMultiKeys(
			$tableInfo['tableName'],
			array(
				$tableInfo['columns']['relatedItem']=>$id,
				$tableInfo['columns']['languageId']=>$translation->languageInfo['id']
			)
		);
		$product = cmfcMySql::convertColumnNames($product,$tableInfo['columns']);
		
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
    <h1 class="title"><?php echo $product['title']?></h1>
	<div class="page">
      <div class="pageBody">
        
            <div class="productsFull">
                
                 <div class="productsImg">
                    <?php
                    if($product['photoFilename'])
                    {
                         $result = $imageManipulator->getAsImageTag(
                            array(
                                'fileName'=>$product['photoFilename'],
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
                                    'alt'=>'',
                                    //'class'=>'floatImg'
                                ),
                            )
                        );
                        ?>
                        <?php echo $result;
                    }
                    ?>
                </div>
                <?php echo $product['body'];?>	
                <br />
               
            <br class="clearfloat" />
            </div>
        </div>
	</div>
	<?php
}?>