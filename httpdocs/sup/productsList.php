<?php
require('requirements/preparing.inc.php');

$q = strtolower($_GET["q"]);
//if (!$q) return;

$sqlQuery = "SELECT `title`,`related_item` FROM ".$_ws['physicalTables']['products']['tableName'].
			" WHERE ".$_ws['physicalTables']['products']['columns']['languageId']." = ".$translation->languageInfo['id'];
$items = cmfcMySql::getRowsCustom($sqlQuery);
$products = array();

if ($items)
{
	foreach ($items as $key=>$row)
		$products[$row['related_item']] = $row['title'];
}
else
{
	$products = array(''=>'');
}

foreach ($products as $key=>$value) 
{
	if (strpos(strtolower($value), $q) !== false) 
	{
		echo "$value|$key\n";
	}
}

?>