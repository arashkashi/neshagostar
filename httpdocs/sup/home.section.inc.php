<table width="100%" border="0" cellspacing="5" cellpadding="0" style="border:2px solid #d7e7ff; ">
	<tr>
		<td style="border-bottom:1px solid #d7e7ff; background:#f7faff;">
			<div style="padding:5px; margin:5px;" >
				خوش آمدید.
			</div>
		</td>
	</tr>
	
	<tr>
		<td style="border:1px solid #d7e7ff;" valign="middle">
			<div style="padding:5px; margin:5px; cursor:pointer;" align="center" >
				<input id="adminSectionsContentBoxButton" style="background:transparent; border:0px; cursor:pointer;" value="بخش های سایت" type="button" class="button"/>
				<?php 				$jsHomeSections['adminSectionsContentBox']=array(
					'buttonId'=>'adminSectionsContentBoxButton',
					//iconId:'HomeSectionContentBox1Icon',
					//iconCloseSrc:'interface/images/sidebuttons_home_products_off.png',
					//iconOpenSrc:'interface/images/sidebuttons_home_products_on.png'
				);
				?>
			</div>
			<style type="text/css">
			.mainSections-links a{
				text-decoration:none;
				border:0px;
			}
			</style>
			<div id="adminSectionsContentBox" style="height:auto;overflow:auto;">
				<table width="100%" border="0" cellspacing="5" cellpadding="0" style="background-color:#f7faff;">
					<tr>
						<td>
						<?php 						$mainSections = cpfFindMainSections();
						//cmfcHtml::printr($mainSections);
						//die;
						foreach ($mainSections as $containerName => $containerInfo){
							
							$directLink = $containerInfo['dashboard']['directLink'];
							if ($containerInfo['children']){
								$items = cpfCreateMainSectionItems($containerInfo['children']);
								
								foreach ($items as $item){
									ob_start();
									?>
									<div align="center" style="float:right; padding:5px; text-align:center; height:150px; width:150px;">
										<a href="?sn=<?php echo $item['name']?>" class="mainSections-links">
											<img src="<?php echo $item['icon']?>" width="64px" height="64px" title="<?php echo $item['title']?>" alt="<?php echo $item['title']?>" border="0px;" />
											<br />
											<?php echo $item['title']?>
                                            <?php if($item['numItems'] > 0){ ?>
											<div align="center" >
												<strong>(<?php echo number_format($item['numItems'])?>)</strong>
											</div>
                                            <?php } ?>
										</a>
									</div>
									<?php 									$html = ob_get_contents();
									ob_end_clean();
									if ($directLink || $item['directLink'])
										echo $html;
									else
										$hiddenMenus[$containerName]['html'][$item['name']] = $html;
								}
								if ($hiddenMenus[$containerName]){
									$containerItem = cpfCreateMainSectionItem($containerName, $containerInfo);
									$hiddenMenus[$containerName]['name']= $containerItem['name'];
									$hiddenMenus[$containerName]['title']= $containerItem['title'];
									?>
									<div align="center" style="float:right; padding:5px; text-align:center; height:150px; width:150px;">
										
										<button id="<?php echo $containerItem['name']?>ContentBoxButton" style="background:transparent; border:0px; cursor:pointer;" class="button" type="button" >
											<img src="<?php echo $containerItem['icon']?>" width="64px" height="64px" title="<?php echo $containerItem['title']?>" alt="<?php echo $containerItem['title']?>" border="0px;" />
											<br />
											<?php echo $containerItem['title']?>
										</button>
										<?php 										$jsHomeSections[$containerItem['name'].'ContentBox']=array(
											'buttonId' => $containerItem['name'].'ContentBoxButton',
											//iconId:'HomeSectionContentBox1Icon',
											//iconCloseSrc:'interface/images/sidebuttons_home_products_off.png',
											//iconOpenSrc:'interface/images/sidebuttons_home_products_on.png'
										);
										?>
									</div>
									<?php 								}
							}
						}
						?>
						</td>
					</tr>
				</table>
			</div>
			<?php 			//cmfcHtml::printr($hiddenMenus);
			if ($hiddenMenus){
				foreach ($hiddenMenus as $containerName => $containerInfo){
					
					?>
						<div id="<?php echo $containerInfo['name']?>ContentBox" style="height:0px;overflow:auto;">
							<table width="100%" border="0" cellspacing="0" cellpadding="5" style="margin:5px; border:1px solid #006699;">
								<tr style="padding:5px; margin:5px; ">
									<td align="center">
										<?php echo $containerInfo['title']?>
									</td>
								</tr>
								<tr style="background-color:#f7fadd;">
									<td>
									<?php 									foreach ($containerInfo['html'] as $html)
										echo $html;
									?>
									</td>
								</tr>
							</table>
						</div>	
						<?php 					}
				}
			?>
		</td>
	</tr>
	
	<?php /*?><tr>
		<td style="border:1px solid #d7e7ff;" valign="middle">
			<div style="padding:5px; margin:5px; cursor:pointer;" align="center" id="statisticsContentBoxButton"  >
				<?php 				$jsHomeSections['statisticsContentBox']=array(
					'buttonId'=>'statisticsContentBoxButton',
					//iconId:'HomeSectionContentBox1Icon',
					//iconCloseSrc:'interface/images/sidebuttons_home_products_off.png',
					//iconOpenSrc:'interface/images/sidebuttons_home_products_on.png'
				);
				?>
				اطلاعات و آمار
			</div>
			<div id="statisticsContentBox" style="height:0px;overflow:hidden;">
            	<table>
                	<tr>
                    	<td>
                        	<?php echo wsfGetValue('totalNews')?>
                        </td>
                        <td>
                        	<?php 								echo wsfGetTotalRowsByTableName($_ws['physicalTables']['news']['tableName'], 'id');
							?>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                        	<?php echo wsfGetValue('totalPhotos')?>
                        </td>
                        <td>
                        	<?php 								echo wsfGetTotalRowsByTableName($_ws['physicalTables']['photos']['tableName'], 'id');
							?>
                        </td>
                    </tr>
                    <tr>
                    	<td>
                        	<?php echo wsfGetValue('totalMovies')?>
                        </td>
                        <td>
                        	<?php 								echo wsfGetTotalRowsByTableName($_ws['physicalTables']['movies']['tableName'], 'id');
							?>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                        	<?php echo wsfGetValue('numSiteVisit')?>
                        </td>
                        <td>
                        	<?php 								//echo wsfGetTotalRowsByTableName($_ws['physicalTables']['counter']);
							?>
                        </td>
                    </tr>
                    
                </table>
			</div>
		</td>
	</tr><?php */?>
</table>

<script>  
	var simpleSliderHomeSections = new simpleSliderClass();
	simpleSliderHomeSections.slidesInfo=<?php echo cmfcHtml::phpToJavascript($jsHomeSections)?>;
	simpleSliderHomeSections.instanceName = 'simpleSliderHomeSections';
	simpleSliderHomeSections.mode='auto';
	simpleSliderHomeSections.prepareOnLoad();
</script>