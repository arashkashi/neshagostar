<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$tableInfo = $_ws['sectionInfo']['tableInfo'];
	$catTableInfo = $_ws['sectionInfo']['categoriesTableInfo'];
	
	$sqlQuery = "SELECT * FROM ".$tableInfo['tableName'].
		" WHERE ".$tableInfo['columns']['languageId']." = ".$translation->languageInfo['id']."
		AND ".$tableInfo['columns']['archive']." = 0";
	
	if ($_GET['categoryId'])
	{
		$categoryId = $_GET['categoryId'];
		$sqlQuery .= " AND ".$tableInfo['columns']['categoryId']." = ".$categoryId;
		$sqlQuery .= " AND ".$tableInfo['columns']['archive']." = 0";
		/*$news = cmfcMySql::getRowsWithMultiKeys(
			$tableInfo['tableName'],
			array(
				$tableInfo['columns']['categoryId']=>$categoryId,
				$tableInfo['columns']['languageId']=>$translation->languageInfo['id']
			)
		);*/
		
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
						$catName['title'];
		}
		else
		{
			$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>";
		}
		// --
	}
	else
	{
		$sqlQuery .= " AND ".$tableInfo['columns']['archive']." = 0";
		$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>"
						." &raquo; ".
						$translation->getValue($_ws['sectionInfo']['name']);
	}
	
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
	$news = cmfcMySql::getRowsCustom($sqlQuery);
	
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	?>
	<div class="page">
		<h1 class="title">
			<?php 
			if ($_GET['categoryId'])
				echo $catName['title'];
			else
				echo $translation->getValue($_ws['sectionInfo']['name']);
			?>
		</h1>
        
        <div class="pageBody">
			<?php
			if ($_GET['categoryId'])
			{
				foreach ($news as $key=>$row)
				{
					$row = cmfcMySql::convertColumnNames($row,$tableInfo['columns']);
					?>
					<div class="newsItem">
						<h3 class="title">
                        	<a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&catId='.$row['categoryId'].'&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>">
							<?php echo $row['title']; ?>
                            </a>
                        </h3>
                        
                        <div class="floatImage">
                            <a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&catId='.$row['categoryId'].'&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>">
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
															'min' => 75,
															'max' => 75,
															'zoomInIfRequire' => true,
															'ignoreAspectRatio' => false,
														),
														'height' => array (
															'min' => 75,
															'max' => 75,
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
														'width' => '75',
														'height' => '75',
													),
												),
											),
										),
									),

								));		
                                echo $result;
                            }
                            ?>
                            </a>
                        </div>
                        
						<?php
						echo cmfcString::briefText(strip_tags($row['body']),300);
						?>
                        
					<br class="clearfloat" />
					</div>
					<?php
					
				}
			}
			else
			{
				if ($cats)
				{
					?>
                    <div id="cats">
						<?php
						foreach ($cats as $key=>$cat)
						{
							$cat = cmfcMySql::convertColumnNames($cat, $catTableInfo['columns']);
							$sw = 0;
							if ($news)
							{
								foreach ($news as $key=>$row)
								{
									$row = cmfcMySql::convertColumnNames($row,$tableInfo['columns']);
									if ($row['categoryId'] == $cat['relatedItem'])
									{
										if (!$sw)
										{
											?>
											<h3><a href="#"><?php echo $cat['title']?></a></h3>
											<div>
											<?php
											$sw = 1;
										}
										?>
										<div class="newsItem">
											<h3 class="title">
												<a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&catId='.$row['categoryId'].'&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>">
												<?php echo $row['title']; ?>
												</a>
											</h3>
											
											<div class="floatImage">
											<a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&catId='.$row['categoryId'].'&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>">
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
																			'min' => 75,
																			'max' => 75,
																			'zoomInIfRequire' => true,
																			'ignoreAspectRatio' => false,
																		),
																		'height' => array (
																			'min' => 75,
																			'max' => 75,
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
																		'width' => '75',
																		'height' => '75',
																	),
																),
															),
														),
													),
												));
												echo $result;
											}
											?>
											</a>
											</div>

											<?php
											echo cmfcString::briefText(strip_tags($row['body']),300);
											?>
											<br class="clearfloat" />
										</div>
										<?php
									}
								}
								if ($sw)
								{
									?>
									</div>
									<?php
								}
							}
						}
						?>
                    </div>
                    <?php
				}
				else
				{
					if ($news)
					{
						foreach ($news as $key=>$row)
						{
							$row = cmfcMySql::convertColumnNames($row,$tableInfo['columns']);
							?>
							<div class="newsItem">
								<h3 class="title">
									<a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&catId='.$row['categoryId'].'&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>">
									<?php echo $row['title']; ?>
									</a>
								</h3>
								
								<div class="floatImage">
								<a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&catId='.$row['categoryId'].'&id='.$row['relatedItem'].'&lang='.$_GET['lang'])?>">
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
																	'min' => 75,
																	'max' => 75,
																	'zoomInIfRequire' => true,
																	'ignoreAspectRatio' => false,
																),
																'height' => array (
																	'min' => 75,
																	'max' => 75,
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
																'width' => '75',
																'height' => '75',
															),
														),
													),
												),
											),
											'attributes'=>array(
												'alt'=>$news['photoFilename'],
												'class'=>'floatImage'
											),
										));
										echo $result;
									}
									?>
								</a>
								</div>

								<?php
								echo cmfcString::briefText(strip_tags($row['body']),300);
								?>
								<br class="clearfloat" />
							</div>
							<?php
						}
					}
				}
			}
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
				<?php 			}
			?>
        </div>
	</div>
    <?php
}?>