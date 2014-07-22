<?php
ini_set('short_open_tag',true);
ini_set('display_errors',0);
//error_reporting(E_ALL ^ E_NOTICE);
$htmlHeadTags = NULL;
require('requirements/preparing.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $translation->getValue('siteTitle')?> » <?php echo $translation->getValue('controlPanel')?></title>
	<?php include('requirements/htmlHead.inc.php') ?>
	
	<!--[if lt IE 7]>
	<style type="text/css">
		img {
		   behavior: url("interface/css/pngbehavior.htc");
		}
	</style>
	
	<![endif]-->
	
</head>
<body>
	<div id="header">
	<div id="logo" style="float:<?php echo $translation->languageInfo['align']?>">
			<a href="?lang=<?php echo $_GET['lang']?>">
				<img border="0" src="../interface/images/logo<?php echo $translation->languageInfo['dbBigLang']?>.gif" alt="logo" />
			</a>
		</div>
		<div id="headernav" dir="<?php echo $translation->languageInfo['direction']?>"  style="float:<?php echo $translation->languageInfo['!align']?>" >
			
		<?php 		/*
		if ($langInfo['shortName']=='en') {
			?>
			<a href="?<?php echo cmfcUrl::excludeQueryStringVars(array('lang', 'sectionName', 'pageType',),'get')?>&amp;lang=fa">
				فارسی
			</a>
			<?php 
		} 
		else {
			?>
			<a href="?<?php echo cmfcUrl::excludeQueryStringVars(array('lang', 'sectionName', 'pageType',),'get')?>&amp;lang=en">
				English
			</a>
			<?php 
		}
		*/
		?>	
		<?php /*?><a href="http://persiantools.com/contact" target="_blank"><?php echo $translation->getValue('ContactPersianTools')?></a>
		|<?php */?>
		<a href="?sn=userProfile"><?php echo $translation->getValue('Edit_Profile')?></a>
		|
		<?php 				
			if ($userSystem->isLoggedIn()) {
				echo $translation->getValue('Welcome');
		?>
			<?php echo $userSystem->cvFullName?> 
			[<span class="logout" ><a href="?action=logout"><?php echo $translation->getValue('Logout')?></a></span>] 
			<?php 			}
		?>
			
			</div>
	</div>
	
	<div id="navbar" dir="<?php echo $translation->languageInfo['direction']?>" >
		<?php include('topMenu.inc.php') ?>
	</div>
		
	<table width="100%" border="0" cellspacing="0" cellpadding="0" id="wrapper"  dir="<?php echo $translation->languageInfo['direction']?>" >
	<tr>
		<td id="sidemenu" valign="top" nowrap="nowrap">
			<?php include('sideMenu.inc.php') ?>
		</td>
		<td id="content" height="100%" valign="top" style="padding:7px;">
			<!--(Begin) : Content -->
			<?php 			if (file_exists($fileToInclude)) {
				wsfPrepareSectionToIncludeInternalPart('inSectionContainer');
				include($fileToInclude);
			}
			else {
				?>
				<div style="text-align:center;color:red;font-weight:bold;vertical-align:middle;padding-top:60px">
					<?php echo $translation->getValue('accessDenied')?>
				</div>
				<?php 
			}
			?>
            <!--(End) : Content -->
		</td>
	</tr>
	</table>

	<div id="footer">
		<div id="copyright" style="float:<?php echo $translation->languageInfo['align']?>; margin-<?php echo $translation->languageInfo['align']?>:10px; direction:<?php echo $translation->languageInfo['direction']?>">
			<?php /*?>&copy; <?php echo date("Y", strtotime('2001-01-01'));?> - <?php echo date("Y");?> <a href="http://design.persiantoools.com/"><?php echo $translation->getValue('persianTools')?></a>.<?php */?>
		</div>
		<div id="footernav" style="float:<?php echo $translation->languageInfo['!align']?>; margin-<?php echo $translation->languageInfo['!align']?>:10px;">
			<a href="index.php"><?php echo $translation->getValue('home')?></a>
		</div>
	</div>
	
	<?php
	if ($_ws['settings']['translationMode']) 
	{
		$translation->printPageWords($langInfo);
	}

	
	wsfPageRenderingTime();
	wsfGetRegisteredQueries();
	wsfGetIncludedFileName();
	wsfGetSectionInfo();
	
	//$translation->printLog();
	//cmfcHtml::printr($translation);
	?>
</body>
</html>