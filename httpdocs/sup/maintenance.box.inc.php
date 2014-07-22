<?php
$maintenanceFilePath = realpath(dirname(__FILE__)."/../files/cache/").'/maintenance.inf';
if($_GET['maintenance'] == "on")
{
	$fp = @fopen($maintenanceFilePath, "w");
} elseif($_GET['maintenance'] == "off") {
	@unlink($maintenanceFilePath);
}
if (file_exists($maintenanceFilePath)) {
	$isMaintenanceOn=true;
} else {
	$isMaintenanceOn=false;
}
?>
<div class="sidemenu-items">
	<img width="16" height="16" border="0" align="absmiddle" src="interface/images/icons/icon_tick.gif"/>
	<a href="?maintenance=on" style="<?php if ($isMaintenanceOn) {?>font-weight:bold<?php }?>">
		<?php echo wsfGetValue('active')?>
	</a>
</div>
<div class="sidemenu-items">
	<img width="16" height="16" border="0" align="absmiddle" src="interface/images/icons/stop.png"/>
	<a href="?maintenance=off" style="<?php if (!$isMaintenanceOn) {?>font-weight:bold<?php }?>">
		<?php echo wsfGetValue('inactive')?>
	</a>
</div>