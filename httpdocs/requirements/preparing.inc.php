<?php
#--(Begin)--> Maintenance mode
$maintenanceFilePath = dirname(__FILE__)."/../files/cache/maintenance.inf";
if(file_exists($maintenanceFilePath))
{
	$url = "/errorDocuments/maintenance.html";
	header("HTTP/1.0 509 Maintenance");
	header("Location: ".$url);
	exit;
}
#--(End)--> Maintenance mode

//session_start();
/**
 * initializing common packages of website accroding to config.inc.php
 * settings. package will only initialize if their require files included
 * in {@link config.inc.php}.<br>
 * Including thie file in every website file is necessary.<br>
 * have a look at source code for more information.<br>
 * 
 * @package [D]/requirements
 * @author Sina Salek
 */
require(dirname(__FILE__).'/config.inc.php');
require(dirname(__FILE__).'/functions.inc.php');
$_insideAdmin = false;

require(dirname(__FILE__).'/prepareSection.inc.php');

$translation->setOption('loadPageWordsAtOnceEnabled', true);

$userSystem->setOption('pagesInfo',array(
	'default'=>'/index.php',
	'login'=>wsfGetLoginUrl(),
	'afterLogin'=>'/sn/messages',//wsfGetAfterLoginUrl(),
	'afterLogout'=>'/',
	'afterActivation'=>'',
	'activation'=>'?sn=userAccountActivation',
	'denyAccess'=>'denyAccess.php',
	'requestNewPassword'=>'?sn=forgetPassword&action=applyNewPassword'
));

$result = $userSystem->readLogin();

if ($_GET['action'] == "logout") $userSystem->logout();

//$feed = cmfcFeed::factory('writerV1',array());

#--(Begin)-->load section internal part if it has
if (is_array($_ws['sectionInfo']['internalParts'])){
	if (in_array('beforeHtml',$_ws['sectionInfo']['internalParts'])) {
		$_ws['currentSectionInternalPartName'] = 'beforeHtml';
		include(dirname(__FILE__).'/../'.$fileToInclude);
	}
}
#--(End)-->load section internal part if it has

