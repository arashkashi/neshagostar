<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	$catTableInfo = $_ws['sectionInfo']['categoriesTableInfo'];
	
	if ($_GET['id'])
	{
		$categoryId = $_GET['categoryId'];
		$id = $_GET['id'];
		
		$news = cmfcMySql::loadWithMultiKeys(
			$tableInfo['tableName'],
			array(
				//$tableInfo['columns']['categoryId']=>$categoryId,
				$tableInfo['columns']['relatedItem']=>$id,
				$tableInfo['columns']['languageId']=>$translation->languageInfo['id']
			)
		);
		$news = cmfcMySql::convertColumnNames($news,$tableInfo['columns']);
		
		// Preparing breadcrumb
		$catName = "SELECT `".
					$catTableInfo['columns']['title']."` as title".
					" FROM ".$catTableInfo['tableName'].
					" WHERE ".$catTableInfo['columns']['relatedItem']." = ".$categoryId.
					" AND ".$catTableInfo['columns']['languageId']." = ".$translation->languageInfo['id'];
		$catName = cmfcMySql::loadCustom($catName);
		if ($catName)
		{
			$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>"
						." &raquo; ".
						"<a href='".wsfPrepareUrl('?sn='.$_ws['sectionInfo']['name'].'&lang='.$_GET['lang'])."'>".$translation->getValue($_ws['sectionInfo']['name'])."</a>"
						." &raquo; ".
						"<a href='".wsfPrepareUrl('?sn='.$_ws['sectionInfo']['name'].'&catId='.$categoryId.'&lang='.$_GET['lang'])."'>".$catName['title']."</a>"
						." &raquo; ".
						cmfcString::briefText($news['title'],20)
						;
		}
		else
		{
			$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>";
		}
		// --
	}
	
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
    <h1 class="title"><?php echo wsfGetValue($_GET['sn'])?></h1>
	<div class="page">
        <h2 class="title"><?php echo $news['title'];?></h2>
        <div class="newsBody">
			<?php
            if ($news)
            {
                if($news['photoFilename'])
                {
                    $result = $imageManipulator->getAsImageTag(array (
                        'fileName' => $news['photoFilename'],
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
                            'alt'=>$news['photoFilename'],
                            'class'=>'floatImg'
                        ),
                    ));		
                    echo $result;
                }
                echo $news['body'];
            }
            else
            {
                echo $translation->getValue('nothingFound');
            }
            ?>
        <br class="clearfloat" />
        <div class="newsDate"><?php echo wsfGetDateTime('d M Y', $news['publishDatetime'], $translation->languageInfo['shortName']);?></div>
        </div>
	</div>
    <?php
}?>