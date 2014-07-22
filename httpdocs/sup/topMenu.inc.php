<?php
#--(Begin)-->Add sections to their related menu containers
foreach ($_cp['sectionsInfo'] as $mySectionInfo) {
	if (!empty($mySectionInfo['topMenuAddress'])) {
		$containers=$_cp['topMenuContainers'];
		$containerName=$mySectionInfo['topMenuAddress'][0];
				
		if (isset($containers[$containerName]) and (wsfIsAccessible($mySectionInfo['accessPointName']) or !isset($mySectionInfo['accessPointName']))) {

			$_cp['topMenuContainers'][$containerName]['childs'][$mySectionInfo['name']]=array(
				'name'=>$mySectionInfo['name'],
				'title'=>wsfGetValue($mySectionInfo['name']),
				'link'=>(empty($mySectionInfo['link']))?'?sn='.$mySectionInfo['name']:$mySectionInfo['link']
			);
		}
		
		//--(Begin)-->Open Selected Tab Via Section Name
		if (!empty($_cp['sectionInfo']['name'])) {
			if ($_cp['sectionInfo']['name']==$mySectionInfo['name'])
				$status='open'; else $status='close';
			if ($_cp['topMenuContainers'][$containerName]['status']!='open'
				or !isset($_cp['topMenuContainers'][$containerName]['childs'][$_cp['sectionInfo']['name']]) )
			$_cp['topMenuContainers'][$containerName]['status']=$status;
		}
		//--(End)-->Open Selected Tab Via Section Name
	}
}
#--(End)-->Add sections to their related menu containers
                       
$jsTopMenus=array();
$n=0;
foreach ($_cp['topMenuContainers'] as $topMenuName=>$topMenuInfo) { 
    $n++;
	if ($topMenuInfo['status']=='open')
		$topMenus[$topMenuName]['containerStyle']='height:auto;overflow:auto;';
	else
		$topMenus[$topMenuName]['containerStyle']='height:0px;overflow:hidden;';
		
	$_cp['topMenuContainers'][$topMenuName]['containerStyle']=$topMenus[$topMenuName]['containerStyle'];
	$_cp['topMenuContainers'][$topMenuName]['num']=$n;
	
	$jsTopMenus[$topMenuName.'TopMenuContentBox']=array(
		'num'=>$n,
		'buttonId'=>$topMenuName.'TopMenuContentBoxButton',
		//iconId:'TopMenuContentBox1Icon',
		//iconCloseSrc:'interface/images/sidebuttons_home_products_off.png',
		//iconOpenSrc:'interface/images/sidebuttons_home_products_on.png'
	);
}
?>                                    

	<script type="text/javascript">  
	//<![CDATA[
	var simpleSliderTopMenu=new simpleSliderClass();
	simpleSliderTopMenu.slidesInfo=<?php echo cmfcHtml::phpToJavascript($jsTopMenus)?>;
	simpleSliderTopMenu.instanceName='simpleSliderTopMenu';
	simpleSliderTopMenu.mode='simple';
	simpleSliderTopMenu.selfClick=false;
	simpleSliderTopMenu.onClickSlideButton=function (id,slidesInfo) {
		var key;
		var slideInfo;
		var slideElm;
        
		for (key in slidesInfo) {
			slideInfo=slidesInfo[key];
			slideElm=$(key);
			$(slideInfo['buttonId']).className='mainnav-off';
		}
		
		var slideInfo=slidesInfo[id];
		$(slideInfo['buttonId']).className='mainnav-on-'+slideInfo['num'];
		//$(id).className='subnav-3';
		//$(id).style.display='block';
	}
	simpleSliderTopMenu.prepareOnLoad();
	//]]>
</script>

<div id="mainnav"  >
	<?php foreach ($_cp['topMenuContainers'] as $topMenuContainer) if (is_array($topMenuContainer)) if (is_array($topMenuContainer['childs'])) { ?>
	<span id="<?php echo $topMenuContainer['name']?>TopMenuContentBoxButton"  style="width:100px;" class="<?php echo ($topMenuContainer['status']=='open')?'mainnav-on-'.$topMenuContainer['num']:'mainnav-off'?>">
		<?php echo wsfGetValue($topMenuContainer['name'])?>
	</span>
	<img src="interface/images/mainnav-spacer.gif" align="right" alt="" />
	<?php }?>
</div>

<?php foreach ($_cp['topMenuContainers'] as $topMenuContainer) if (is_array($topMenuContainer['childs'])) { ?>
	<div id="<?php echo $topMenuContainer['name']?>TopMenuContentBox" class="subnav-<?php echo $topMenuContainer['num']?>" style="display:<?php echo ($topMenuContainer['status']=='open')?'block':'none'?>;">
	<?php 	$separator='';
	if (is_array($topMenuContainer['childs']))
	foreach ($topMenuContainer['childs'] as $childInfo) {
		//cmfcHtml::printr($childInfo);?>
		<?php echo $separator?><a href="<?php echo $childInfo['link']?>&lang=<?php echo $langInfo['sName']?>" style="font-weight:<?php echo ($childInfo['name']==$_cp['sectionInfo']['name'])?'bold':''?>"><?php echo $childInfo['title']?></a>
	<?php $separator='&nbsp;|&nbsp;&nbsp;'; }?>
	</div>
<?php }?>
<!--
<div id="subnav-2" style="display:none;">
	<a href="#">22222</a> &nbsp; | &nbsp; <a href="#">22222</a>
</div>

<div id="subnav-3" style="display:none;">
	<a href="#">333333</a> &nbsp; | &nbsp; <a href="#">333333</a>
</div>

<div id="subnav-4" style="display:none;">
	<a href="#">44444444</a> &nbsp; | &nbsp; <a href="#">44444444</a>
</div>
-->
