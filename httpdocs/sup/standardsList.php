<?php
require('requirements/preparing.inc.php');

$q = strtolower($_GET["q"]);
//if (!$q) return;

$sqlQuery = "SELECT `title`,`related_item` FROM ".$_ws['physicalTables']['standards']['tableName'].
			" WHERE ".$_ws['physicalTables']['standards']['columns']['languageId']." = ".$translation->languageInfo['id'];
$items = cmfcMySql::getRowsCustom($sqlQuery);
$standards = array();

if ($items)
{
	foreach ($items as $key=>$row)
		$standards[$row['related_item']] = $row['title'];
}
else
{
	$standards = array(''=>'');
}

foreach ($standards as $key=>$value) 
{
	if (strpos(strtolower($value), $q) !== false) 
	{
		echo "$value|$key\n";
	}
}

?>