<?php
function farsiCompare($a, $b) {
	$a=cmfcString::encodeFarsi($a['title']);
	$b=cmfcString::encodeFarsi($b['title']);
	return strcmp($a,$b);
}

#--(Begin)-->Save key of each item or child item as its name parameters
foreach($_cp['sideMenuContainers'] as $index => $row)
{	
	$_cp['sideMenuContainers'][$index]['name'] = $index;
	
	foreach($row as $k=>$val)
	{	
		if(is_array($row['childs']))
		{
			foreach($row['childs'] as $k2 => $val2)				
			{
				//$row['childs'][$k2]['name'] =  $k2;
				$_cp['sideMenuContainers'][$index]['childs'][$k2]['name'] = $k2;
			}
		}						
	}
}
#--(End)-->Save key of each item or child item as its name parameters


#--(Begin)-->Add sections to their related menu containers
foreach ($_cp['sectionsInfo'] as $mySectionInfo) {
	if (!empty($mySectionInfo['sideMenuAddress'])) {
		$containers=$_cp['sideMenuContainers'];
		$containerName=$mySectionInfo['sideMenuAddress'][0];

		if (isset($containers[$containerName])) {
			$subContainers=$containers[$containerName]['childs'];
			$subContainerName=$mySectionInfo['sideMenuAddress'][1];
			
			if (isset($subContainers[$subContainerName]) and (wsfIsAccessible($mySectionInfo['accessPointName']) or !isset($mySectionInfo['accessPointName']))) {

				if($mySectionInfo['name']==$_cp['sectionInfo']['name'] and !$currentSectionSetWithLink)
				{
					$currentSectionInfo = array(
						'containerName' => $containerName,
						'subContainerName' => $subContainerName,
						'sectionName' => $mySectionInfo['name'],
					);
				}
				if(($mySectionInfo['link'] and strpos($_SERVER['REQUEST_URI'], $mySectionInfo['link'])!==FALSE))
				{
					$currentSectionInfo = array(
						'containerName' => $containerName,
						'subContainerName' => $subContainerName,
						'sectionName' => $mySectionInfo['name'],
					);
					
					$currentSectionSetWithLink = true;
				}

				$_cp['sideMenuContainers'][$containerName]['childs'][$subContainerName]['childs'][$mySectionInfo['name']]=array(
					'name'=>$mySectionInfo['name'],
					//'title'=>wsfGetValue($mySectionInfo['name']),
					'actions'=>$mySectionInfo['actions'],
					'link'=>(empty($mySectionInfo['link']))?'?sn='.$mySectionInfo['name'].'&lang='.$langInfo['sName']:$mySectionInfo['link']
				);
				
				//--(Begin)-->Active Selected Tab Via Section Name
				if (!empty($_cp['sectionInfo']['name'])) {
					if ($_cp['sectionInfo']['name']==$mySectionInfo['name'])
						$status='open'; else $status='close';
					if ($_cp['sideMenuContainers'][$containerName]['childs'][$subContainerName]['status']!='open'
						or !isset($_cp['sideMenuContainers'][$containerName]['childs'][$subContainerName]['childs'][$_cp['sectionInfo']['name']]) )
						$_cp['sideMenuContainers'][$containerName]['childs'][$subContainerName]['status']=$status;
					
				}
				//--(End)-->Active Selected Tab Via Section Name
			}
		}
	}
}

if($currentSectionInfo)
{
	$_cp['sideMenuContainers'][$currentSectionInfo['containerName']]['childs'][$currentSectionInfo['subContainerName']]['childs'][$currentSectionInfo['sectionName']]['bold'] = true;
}
#--(End)-->Add sections to their related menu containers

//print_r($_cp['sideMenuContainers']);

//foreach ($_cp['sideMenuContainers'] as $sideMenuGroupName=>$sideMenuGroupInfo)
//$sideMenuGroupName='common';
//$sideMenuGroupInfo=$_cp['sideMenuContainers'][$sideMenuGroupName];
foreach ($_cp['sideMenuContainers'] as $sideMenuGroupName=>$sideMenuGroupInfo) {
	$jsSideMenus=array();
	
	if(is_array($sideMenuGroupInfo['childs'])) {
		$hasSubChilds=true;
		foreach ($sideMenuGroupInfo['childs'] as $sideMenuName=>$sideMenuInfo) { 
			if (!empty($sideMenuInfo['childs'])) $hasSubChilds=true;
		
			if ($sideMenuInfo['status']=='open')
				$sideMenus[$sideMenuName]['containerStyle']='height:auto;overflow:auto;';
			else
				$sideMenus[$sideMenuName]['containerStyle']='height:0px;overflow:hidden;';
			
			$_cp['sideMenuContainers'][$sideMenuGroupName]['childs'][$sideMenuName]['containerStyle']=$sideMenus[$sideMenuName]['containerStyle'];
				
			$jsSideMenus[$sideMenuName.'SideMenuContentBox']=array(
				'buttonId'=>$sideMenuName.'SideMenuContentBoxButton',
				//iconId:'SideMenuContentBox1Icon',
				//iconCloseSrc:'interface/images/sidebuttons_home_products_off.png',
				//iconOpenSrc:'interface/images/sidebuttons_home_products_on.png'
			);
			
		}
		$_cp['sideMenuContainers'][$sideMenuGroupName]['hasSubChilds']=$hasSubChilds;
	} 
	else {
		if (!isset($sideMenuGroupInfo['file'])) {
			$_cp['sideMenuContainers'][$sideMenuGroupName]['file']=$sideMenuGroupName.'.box.inc.php';
			$_cp['sideMenuContainers'][$sideMenuGroupName]['hasSubChilds']= true;
		}
	}
?>

	<script>  
		var simpleSliderSideMenu<?php echo $sideMenuGroupName?>=new simpleSliderClass();
		simpleSliderSideMenu<?php echo $sideMenuGroupName?>.slidesInfo=<?php echo cmfcHtml::phpToJavascript($jsSideMenus)?>;
		simpleSliderSideMenu<?php echo $sideMenuGroupName?>.instanceName='simpleSliderSideMenu<?php echo $sideMenuGroupName?>';
		simpleSliderSideMenu<?php echo $sideMenuGroupName?>.mode='auto';
		simpleSliderSideMenu<?php echo $sideMenuGroupName?>.prepareOnLoad();
	</script>
<?php }?>

<?php //Menu First Level
foreach ($_cp['sideMenuContainers'] as $sideMenuContainer) if ($sideMenuContainer['hasSubChilds']==true) { 
?>
<div class="sidemenu-section" >
	<span class="sidemenu-title">
		<span style="color:#ff6200">»</span>&nbsp; <?php echo wsfGetValue($sideMenuContainer['name'])?>
	</span>
	
	<?php 
	if(is_file($sideMenuContainer['file'])){
		include_once($sideMenuContainer['file']);
		
	} else if (is_array($sideMenuContainer['childs'])) {
	
		//Menu Second Level
		foreach ($sideMenuContainer['childs'] as $childInfo) if (is_array($childInfo['childs'])) {
			#--(Begin)-->find the second level item icon
			if (!isset($childInfo['icon'])) {
				$childInfo['icon']=$childInfo['name'].'.sideMenu.png';
				$f='interface/images/icons/'.$childInfo['icon'];
				if (!file_exists($f))
					$childInfo['icon']=$childInfo['name'].'.sideMenu.gif';
					
				$f='interface/images/icons/'.$childInfo['icon'];
				if (!file_exists($f))
					$childInfo['icon']='';
			}
			
				
			if (empty($childInfo['icon']))
				$childInfo['icon']='icon.sideMenu.gif';
			#--(End)-->find the second level item icon
			?>
				<span id="<?php echo $childInfo['name']?>SideMenuContentBoxButton" class="sidemenu-items" onmouseover="this.style.color='#ff0000';this.style.backgroundColor='#f7faff'" onmouseout="this.style.color='#000000';this.style.backgroundColor='#ffffff'">
					<img src="interface/images/icons/<?php echo $childInfo['icon']?>" border="0" align="absmiddle" width="16" height="16" />
					<?php echo wsfGetValue($childInfo['name'])?>
				</span>
				
				<div id="<?php echo $childInfo['name']?>SideMenuContentBox" class="sidemenu-subsection" style="<?php echo $childInfo['containerStyle']?>;">
					<?php 					
					//Menu Third Level
					if (is_array($childInfo['childs']))
					foreach ($childInfo['childs'] as $subChildInfo) {
					?>
						<div class="sectionLink">
							<a href="<?php echo $subChildInfo['link']?>" style="font-weight:<?php echo ($subChildInfo['bold'] ? 'bold':'')?>">
								<?php echo wsfGetValue($subChildInfo['name'])?>
							</a>
							&nbsp;
							<?php  if (is_array($subChildInfo['actions']))
								foreach ($subChildInfo['actions'] as $subChildAction) if (!empty($subChildAction)){?>
									<a href="<?php echo $subChildInfo['link']?>&action=<?php echo $subChildAction?>">
										<img src="interface/images/action_<?php echo $subChildAction?>.png" alt="<?php echo $subChildAction?>" border="0" align="absmiddle" width="16" height="16" />
									</a>
							<?php  }?>
						</div>
					<?php }?>
					
					
					
				</div>
		<?php }?>
	<?php }?>
</div>
<div class="sidemenu-spacer"></div>
<?php }?>