<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	
	$sqlQuery = "SELECT * FROM ".$tableInfo['tableName'].
		" WHERE ".$tableInfo['columns']['languageId']." = ".$translation->languageInfo['id'];
	
	$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>"
					." &raquo; ".
					$translation->getValue($_ws['sectionInfo']['name']);
	/*
	if (isset($_ws['sectionInfo']['listLimit']))
		$listLimit = $_ws['sectionInfo']['listLimit'];
	else
		$listLimit = 10;
	
	$paging=cmfcPaging::factory('dbV2',array(        
		'total'=>null,
		'limit'=>$listLimit,
		'sqlQuery'=>$sqlQuery,
		'wordNext'=> $translation->getValue("next") ,
		'wordPrev'=> $translation->getValue("prev"),
		'link'=>'?'.wsfExcludeQueryStringVars(array('sectionName','pageType'),'get'),
		'sortingEnabled'=>true,
		'staticLinkEnabled'=>true,
		'sortBy'=>$_ws['sectionInfo']['tableInfo']['columns']['publishDatetime'],
		'sortType'=>'DESC',
		'colnId'=>$_ws['sectionInfo']['tableInfo']['columns']['id'],
	));
	
	$paging->addCommandHandler('rewriteUrl','wsfPagingPrepareUrl');
	$sqlQuery = $paging->getPreparedSqlQuery();
	//echo $sqlQuery;
	*/
	$rows = cmfcMySql::getRowsCustom($sqlQuery);
	
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
			if ($rows)
			{
				foreach ($rows as $key=>$row)
				{
					$row = cmfcMySql::convertColumnNames($row,$tableInfo['columns']);
					?>
					<div class="productsListItem">
						<?php
                        if($row['photoFilename'])
                        {
                             $result = $imageManipulator->getAsImageTag(array (
                                'fileName' => $row['photoFilename'],
                                'fileRelativePath' => $_ws['sectionInfo']['folderRelative'],
                                'version' => 2,
                                'actions' => array (
                                     array (
                                        'subActions' => array (
                                             array (
                                                'name' => 'resizeSmart',
                                                'parameters' => array (
                                                    'width' => array (
                                                        'min' => 100,
                                                        'max' => 100,
                                                        'zoomInIfRequire' => true,
                                                        'ignoreAspectRatio' => false,
                                                    ),
                                                    'height' => array (
                                                        'min' => 100,
                                                        'max' => 100,
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
                                                    'width' => '100',
                                                    'height' => '100',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                                'attributes'=>array(
                                    'alt'=>$row['title'],
                                    //'class'=>'floatImg'
                                ),
                            ));
                            echo $result;
                        }
                        ?>
                        <br />
						<?php echo $row['title']; ?>
						
					</div>
					<?php
				}
			}
			/*
			if($paging )//and $paging->getTotalPages()>1)
			{
				?>
				<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style=" padding:3px;"  >
					<tr>
						<td>
							<table width="100%" border="0" align="center" class="paging-nav"  cellspacing="1" cellpadding="0">
								<tr>
									<td width="25%" class="paging-nav-body" align="<?php echo $langInfo['htmlAlign']?>">&nbsp;
									<?php if ($paging->hasPrev()) {?>
										<a href="<?php echo wsfPrepareUrl($paging->getPrevUrl())?>" class="paging-body" title="<?php echo $translation->getValue("prevPage")?>">
										&lt;&lt; </a>
									<?php }?>
									</td>
									<td width="50%" class="paging-body" align="center">
										<?php echo  $translation->getValue("page")?> <?php echo $paging->getPageNumber()?> <?php echo  $translation->getValue("from")?> <?php echo $paging->getTotalPages()?>
										|
										<?php echo $paging->show('nInCenterWithJumps',array())?>
									</td>
									<td width="25%" class="paging-nav-body" align="<?php echo $langInfo['htmlNAlign']?>">&nbsp;
									<?php if ($paging->hasNext()) {?>
										<a href="<?php echo wsfPrepareUrl($paging->getNextUrl())?>" class="paging-body" title="<?php echo $translation->getValue("nextPage")?>">
										&gt;&gt;</a>
									<?php }?>
									</td>
								</tr>
						  </table>
						</td>
					</tr>
				</table>
				<?php
			}
			*/
			?>
        <br class="clearfloat" />    
        </div>
	</div>
    <?php
}?>