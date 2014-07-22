<?php
ini_set('display_errors', 0);
require('requirements/preparing.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<style type="text/css" media="print"> 
		.no-print-on-print
		{
			display:none;
			visibility:hidden;
		}
	</style>
	<style type="text/css"> 
		*{
			font-family:tahoma;
			font-size:10pt;
		}
		.table
		{
			border-collapse:collapse;
		}
		.border
		{
			border:1px #666666 solid;
		}
		.tr-user{
			background-color:#999999;
			height:50px;
		}
		.tr-header{
			background-color:#CCCCCC;
		}
		.no-print{
			display:none;
			visibility:hidden;
		}
	</style>
    <script language="jscript" type="text/jscript">
		function testPrint()
		{
			window.print();
		}
	</script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $_ws['siteInfo']['title']?></title>
	<link href="interface/css/print.css" rel="stylesheet" type="text/css" />
	<!--[if lt IE 7]>
	<style type="text/css">
		img {
		   behavior: url("interface/css/pngbehavior.htc");
		}
	</style>
	
	<![endif]-->
	
</head>
<body>

	<table  border="0" align="center" cellpadding="0" cellspacing="0"  dir="<?php echo $langInfo['htmlDir']?>" id="wrapper" >
	<tr>
		<td id="content" height="100%" align="right" valign="top" style="padding:7px;">
			<!--(Begin) : Content -->
			<input type="button" class="button no-print-on-print" value="<?php echo wsfGetValue('VN_print')?>" onclick="window.print()" />
			
			<input type="button" class="button no-print-on-print" value="<?php echo wsfGetValue('VN_close')?>" onclick="window.close();" />
			<?php 			
            if (file_exists($fileToInclude)) {
				wsfPrepareSectionToIncludeInternalPart('inSectionContainer');
				include($fileToInclude);
			} else {
			?>
				<div style="text-align:center;color:red;font-weight:bold;vertical-align:middle;padding-top:60px">You have not permision to access this page </div>
			<?php }?>
			
			<br style="clear:both" />
			<input type="button" class="button no-print-on-print" value="<?php echo wsfGetValue('VN_print')?>" onclick="window.print()" />
			
			<input type="button" class="button no-print-on-print" value="<?php echo wsfGetValue('VN_close')?>" onclick="window.close();" />
            <!--(End) : Content -->
		</td>
	</tr>
	</table>

</body>
</html>