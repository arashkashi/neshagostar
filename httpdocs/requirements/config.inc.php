<?php
/**
 * Only specific configurations belongs to the view section of website
 * should be here.<br>
 * If main configuration "/admin/requirements/config.inc.php" is same between
 * website and its contorl panel there is no need put anything here. but if
 * it's not , only overriding main config parameters is allowed<br>
 * have a look at source code for more information
 * 
 * @package [D]/requirements
 * @author Sina Salek
 */

 /**
 * @desc including main config
 */
$_insideAdmin = 0;
require_once(dirname(__FILE__).'/../common/requirements/preparing.inc.php');

$userSystem->setOption('sessionBaseName','userSystemViewST');
$userSystem->setOption('cookieBaseName','userSystemViewCT');
$userSystem->setOption('tableName',$_ws['physicalTables']['webUsers']['tableName']);

$userSystem->setOption("columnsProperty", array(
	'resume'=>'resume',
	'activities'=>'activities',
	'website'=>'website',
	'photo_filename'=>'photoFilename',
	'birthday_date'=>'birthdayDate',
	'tel'=>'tel',
	'mobile'=>'mobile',
	'address'=>'address',
	'gender'=>'gender',
), true);

