<?php 
ob_start();
require('requirements/preparing.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php require('requirements/htmlHead.inc.php'); 
		if (isset($_REQUEST['print']))
		{
			?>
			<link rel="stylesheet" href="/admin/interface/css/print.css" type="text/css" />
			<?php
		}
	?>
</head>

<body dir="<?php echo $translation->languageInfo['direction']?>">
	<?php
	if ($_GET['sn']!='offersNComplaints')
	{
	?>
	<div id="header" align="<?php echo $translation->languageInfo['align']?>" dir="<?php echo $translation->languageInfo['direction']?>">
		<div id="headernav">
			<?php echo wsfGetDateTime("l"); ?> <?php echo wsfGetDateTime("H:i A"); ?> | 
			<?php echo wsfGetDateTime("d M Y"); ?>
		</div>
	</div>
	
	<br />
	<?php
	}
	?>
	<div>
		<?php if (!include($fileToInclude)) { ?>
			<div style="text-align:center;color:red;font-weight:bold;vertical-align:middle;padding-top:60px">
				<?php echo wsfGetValue('accessDenied')?>
			</div>
		<?php }?>
	</div>
</body>
</html>
