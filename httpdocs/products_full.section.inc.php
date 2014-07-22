<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{ 
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	$catTableInfo = $_ws['sectionInfo']['categoriesTableInfo'];
	$catLangTableInfo = $_ws['sectionInfo']['catLangTableInfo'];
	$imagesTableInfo = $_ws['sectionInfo']['imagesTableInfo'];
	
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
		
		
		// Related Images
		$imageSqlQuery = "SELECT * FROM `".$imagesTableInfo['tableName']."` 
		WHERE `".$imagesTableInfo['columns']['productId']."`= ".$id;
		
		$relatedImages = cmfcMySql::getRowsCustom($imageSqlQuery);
		// --
		
		$categoryId = $product['categoryId'];
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
                        <a href="<?php echo $_ws['sectionInfo']['folderRelative'].$product['photoFilename']?>" class="lightbox">
                        <?php echo $result;?>
                        </a><?php
                    }
                    
                    ?>
                    <div class="productsImgThumbs" ><?php
                    if ($relatedImages)
                    {
                        foreach ($relatedImages as $key=>$img)	
                        {
                            if( $img['image'])
                            {
                                $result = $imageManipulator->getAsImageTag(
                                    array(
                                        'fileName'=>$img['image'],
                                        'fileRelativePath'=>$_ws['sectionInfo']['folderRelative'],			
                                        'version' => 2,
                                        'actions' => array (
                                             array (
                                                'subActions' => array (
                                                     array (
                                                        'name' => 'resizeSmart',
                                                        'parameters' => array (
                                                            'width' => array (
                                                                'min' => 50,
                                                                'max' => 50,
                                                                'zoomInIfRequire' => true,
                                                                'ignoreAspectRatio' => false,
                                                            ),
                                                            'height' => array (
                                                                'min' => 50,
                                                                'max' => 50,
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
                                                            'width' => 50,
                                                            'height' => 50,
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
                                ?><a href="<?php echo $_ws['sectionInfo']['folderRelative'].$img['image']?>" class="lightbox"><?php echo $result;?></a><?php
                            }
                        }
                    }
                    ?><br class="clearfloat" />
                    </div>
                </div>
                <?php echo $product['generalCharacteristics'];?>	
                <br />
                <?php
				/*
                if ($product['relatedStandards'])
                {
                    $product['relatedStandards'] = explode(';',$product['relatedStandards']);
                    ?>
                    <div class="productsSideBox">
                        <h1 class="title"><?php echo $translation->getValue('relatedStandards');?></h1>
                        
                        <ul style="padding:15px;"><?php
                            foreach ($product['relatedStandards'] as $k=>$r)	
                            {
                                $pr = cmfcMySql::loadWithMultiKeys(
                                    $sectionInfo['standardsTable']['tableName'],
                                    array(
                                        $sectionInfo['standardsTable']['columns']['languageId']=>$translation->languageInfo['id'],
                                        $sectionInfo['standardsTable']['columns']['relatedItem']=>$r
                                    )
                                );
                                ?><li><a href="<?php echo wsfPrepareUrl('?sn=standards&pt=full&id='.$pr['related_item'].'&lang='.$_GET['lang'])?>" target="_blank" title="<?php echo $pr['title'];?>"><?php echo $pr['title'];?></a></li><?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
				*/
				?>
               
            <br class="clearfloat" />
            </div>
        </div>
	</div>
    <?php if($_REQUEST['action'] != 'print'){ ?>
		<script type="text/javascript">
            $(function() {
                $('a.lightbox').lightBox({
                    imageLoading: 'interface/images/lb-ico-loading.gif',
                    imageBtnClose: 'interface/images/lb-btn-close.gif',
                    imageBtnPrev: 'interface/images/lb-btn-prev.gif',
                    imageBtnNext: 'interface/images/lb-btn-next.gif'
        
                }); // Select all links with lightbox class
            });
        </script>
	<?php } ?>
	<?php
}?>