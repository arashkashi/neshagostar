<?php
require('requirements/preparing.inc.php');

$q = strtolower($_GET["q"]);
//if (!$q) return;

$sqlQuery = "SELECT `full_name`,`id` FROM ".$_ws['physicalTables']['webUsers']['tableName'];
//." WHERE ".$_ws['physicalTables']['webUsers']['columns']['languageId']." = ".$translation->languageInfo['id'];
$items = cmfcMySql::getRowsCustom($sqlQuery);
$users = array();

if ($items)
{
	foreach ($items as $key=>$row)
		$users[$row['id']] = $row['full_name'];
}
else
{
	$users = array(''=>'');
}

foreach ($users as $key=>$value) 
{
	if (strpos(strtolower($value), $q) !== false) 
	{
		echo "$value|$key\n";
	}
}

?>