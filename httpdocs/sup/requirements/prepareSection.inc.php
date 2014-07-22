<?php
/**
 * for sectional website including this file in {@link prearing.section.inc.php} is require.
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

#--(Begin)-->validate section name
$_GET['sn']=str_replace(array('/',':','\\','.'),'',$_GET['sn']);
#--(End)-->validate section name

#--(Begin)-->validate page type
$_GET['pt']=str_replace(array('/',':','\\','.'),'',$_GET['pt']);
#--(End)-->validate page type

if (isset($_GET['sn'])) $_GET['sectionName']  = $_GET['sn'];
if (isset($_GET['pt'])) $_GET['pageType']     = $_GET['pt'];
if (isset($_GET['in'])) $_GET['internalName'] = $_GET['in'];



//making the name index of sectionsInfo
foreach($_cp['sectionsInfo'] as $k=>$row)
{
	$_cp['sectionsInfo'][$k]['name'] = $k;
}

	

if (empty($_GET['sectionName'])) $_GET['sectionName']='home';

if ($_GET['sectionName']) {
	$_cp['sectionInfo']=array(
		'name'=>$_GET['sectionName'],
		'className'=>$_GET['sectionName'],
	);
}


$info=$_cp['sectionsInfo'][$_cp['sectionInfo']['name']];
if (is_array($info)) {
	$_cp['sectionInfo']=array_merge($_cp['sectionInfo'],$info);
}


#--(Begin)-->find section appropriate file name
$fileNameParts=array();
if (!empty($_cp['sectionInfo']['name'])) $fileNameParts[]=$_cp['sectionInfo']['name'];
if (in_array($_GET['pageType'],array('list','full','treeList','thumbList'))) $fileNameParts[]=$_GET['pageType'];

if (empty($_cp['sectionInfo']['fileName']))
	$fileToInclude=implode('_',$fileNameParts).'.section.inc.php';
else
	$fileToInclude=$_cp['sectionInfo']['fileName'];

#--(End)-->find section appropriate file name

#--(Begin)-->override section file name if expression existed
if ($_cp['sectionInfo']['name']=='home') $fileToInclude='home.section.inc.php';
if ($_GET['pageType']=='list' and $_cp['sectionInfo']['name']!='search') $fileToInclude='list.section.inc.php';
if ($_GET['pageType']=='full') $fileToInclude='full.section.inc.php';
if ($_GET['pageType']=='contact') $fileToInclude='contact.section.inc.php';
if ($_GET['pageType']=='popup') $fileToInclude ='popupTree.section.inc.php';
if ($_GET['pageType']=='staticPage') $fileToInclude='staticPage.section.inc.php';
if ($_GET['pageType']=='multiList') $fileToInclude='multiList.section.inc.php';
if ($_GET['pageType']=='userChangePassword') $fileToInclude='userChangePassword.section.inc.php';

#--(End)-->override section file name if expression existed
$_cp['sectionInfo']=$sectionInfo=$_cp['sectionsInfo'][$_cp['sectionInfo']['name']];

#--(Begin)-->check section accessibility
if (!empty($_cp['sectionInfo']['accessPointName']))
	if (!wsfIsAccessible($_cp['sectionInfo']['accessPointName'])) 
		{
			$fileToInclude='';
		}
//cmfcHtml::printr($_cp['sectionInfo']['accessPointName']);
#--(End)-->check section accessibility





