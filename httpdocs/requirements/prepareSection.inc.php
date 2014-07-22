<?php
/**
 * for sectional website including this file in {@link prearing.inc.php} is require.
 * it finds apporpiriate file for section and if site has access point control system
 * it check sections accessibility before showing them.
 * 
 * @package [D]/requirements
 * @author Sina Salek
 */

/**
* @desc contains require information about website sections
*/

require(dirname(__FILE__).'/sectionsInfo.inc.php');

#--(Begin)--validate section name
$_GET['sn']=str_replace(array('/',':','\\','.'),'',$_GET['sn']);
#--(End)-->validate section name

#--(Begin)-->validate page type
$_GET['pt']=str_replace(array('/',':','\\','.'),'',$_GET['pt']);
#--(End)-->validate page type
if (isset($_GET['sn'])) $_GET['sectionName']=$_GET['sn'];
if (isset($_GET['pt'])) $_GET['pageType']=$_GET['pt'];
if (isset($_GET['in'])) $_GET['internalName']=$_GET['in'];

if (empty($_GET['sectionName'])) $_GET['sectionName']=$_GET['sn']='home';
if ($_GET['sectionName']) {
	$sectionInfo=array(
		'name'=>$_GET['sectionName'],
		'className'=>$_GET['sectionName'],
	);
}
$info=$_ws['sectionsInfo'][$sectionInfo['name']];
if (is_array($info)) {
	$sectionInfo=array_merge($sectionInfo,$info);
}
$_ws['sectionInfo']=$sectionInfo;
if (empty($_GET['pt']) && isset($_ws['sectionInfo']['pageTypes']['defaultPageType']) ){
	$_GET['pageType'] = $_GET['pt'] = $_ws['sectionInfo']['pageTypes']['defaultPageType'];
}
#--(Begin)-->find section appropriate file name
$fileNameParts=array();


if (!empty($_ws['sectionInfo']['fileName']))
	$fileNameParts[]=$_ws['sectionInfo']['fileName'];
elseif (!empty($_ws['sectionInfo']['name']))
	$fileNameParts[]=$_ws['sectionInfo']['name'];
if (in_array($_GET['pageType'],array('list', 'full', 'categories', 'galleries', 'images', 'archive','send','sent')))
	$fileNameParts[]=$_GET['pageType'];

$fileToInclude=implode('_',$fileNameParts).'.section.inc.php';



//echo $fileToInclude;
#--(End)-->find section appropriate file name

#--(Begin)-->override section file name if expression existed
if ($_ws['sectionInfo']['name']=='home')
	$fileToInclude='home.section.inc.php';

if (!empty($_ws['sectionInfo']['pageTypes'][$_GET['pageType']]['file'])) {
	$fileToInclude=$_ws['sectionInfo']['pageTypes'][$_GET['pageType']]['file'];
}

if (!$_ws['sectionInfo']['internalParts']) {
	$_ws['sectionInfo']['internalParts']=array();
}
#--(End)-->override section file name if expression existed


#--(Begin)--> Display page not found if the section does not exists
if (!file_exists($fileToInclude)) {
	header("HTTP/1.0 404 Not Found");
	include("errorDocuments/pageNotFound.html");
	exit();
}
#--(End)--> Display page not found if the section does not exists


#--(Begin)-->check section accessibility
if (!empty($_ws['sectionInfo']['accessPointName'])) {
	if (!wsfIsAccessible($_ws['sectionInfo']['accessPointName'])) {
		$fileToInclude='';
		header("HTTP/1.0 403 Access Forbidden");
		include("errorDocuments/accessForbidden.html");
		exit();
	}
}
#--(End)-->check section accessibility