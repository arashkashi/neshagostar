<?php $sessionName = ini_get("session.name");
$_COOKIE[$sessionName] = $_GET[$sessionName];

include(dirname(__FILE__).'/requirements/preparing.inc.php');


$validExtensions = array('flv', 'divx' , 'wma' , 'qt' , 'mpeg' , 'avi' , 'dat' , 'wmf' , 'rm' , 'asf' , 'wmv' , 'swf', 'jpg', 'jpeg', 'png' );
if ($_GET['jqUploader'] == 1)
{
	$photoFilename = wsfUploadFileAuto("Filedata", $sectionInfo['folderPath'], $validExtensions );
}

$_SESSION['fileNames'][$_GET['flatFieldName']] = $photoFilename;

//file_put_contents(dirname(__FILE__).'/../files/cache/zzzzzz.txt', print_r(array($_FILES, $_SESSION, $_GET), true));
?>