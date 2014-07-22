<?php
require('requirements/preparing.inc.php');

$q = strtolower($_GET["q"]);
//if (!$q) return;

$sqlQuery = "SELECT `title`,`related_item` FROM ".$_ws['physicalTables']['tests']['tableName'].
			" WHERE ".$_ws['physicalTables']['tests']['columns']['languageId']." = ".$translation->languageInfo['id'];
$items = cmfcMySql::getRowsCustom($sqlQuery);
$tests = array();

if ($items)
{
	foreach ($items as $key=>$row)
		$tests[$row['related_item']] = $row['title'];
}
else
{
	$tests = array(''=>'');
}

foreach ($tests as $key=>$value) 
{
	if (strpos(strtolower($value), $q) !== false) 
	{
		echo "$value|$key\n";
	}
}

?>