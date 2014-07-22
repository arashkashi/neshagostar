<?php
/**
 * initializing common packages of website accroding to config.inc.php
 * settings. package will only initialize if their require files included
 * in {@link config.inc.php}<br>
 * Including thie file in every website file is necessary.<br>
 * have a look at source code for more information
 * 
 * @author Sina Salek
 * @package [D]/admin/requirements
 */

//session_start();

/**
* including common preparings
*/

$_insideAdmin=true;
require(dirname(__FILE__).'/../../common/requirements/preparing.inc.php');

$translation->setOption('loadPageWordsAtOnceEnabled', true);

/**
* read information of logged in user
*/

$result=$userSystem->readLogin();
/**
* logout logged in user
*/


if (isset($_POST['cancel'])){
	//cmfcHtml::printr($_SERVER);
	//$url = str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['HTTP_REFERER']);
	$url ='?'.cmfcUrl::excludeQueryStringVars(
		array(
			'action', 
			'sectionName', 
			'pageType', 
			'id',
			//'lang',
		),
		'get'
	);
	//echo $url;
	?>
	<script language="javascript">
	window.location.href = '<?php echo $url?>';
	</script>
	<meta content="0; url=<?php echo $url?>" http-equiv="refresh" />
	if you are not redirected in a short period of time, it means you have disabled both javascript and meta tags and hence, we are not able to redirect you.<br />
	please click on <a href="<?php echo $url?>">this link </a> to get redirected manually...
	<?php
	die;
}

if ($_GET['action'] == "logout") $userSystem->logout();

/**
* including control panel specific classes
*/
require(dirname(__FILE__).'/../../lib/packages/interface/interface.inc.php');


/**
* including control panel specific portal classe
*/
//require(dirname(__FILE__).'/portal.class.inc.php');


/**
* including control panel specific functions
*/
require_once(dirname(__FILE__).'/functions.inc.php');

/**
* preparing selected section parameters
*/
require(dirname(__FILE__).'/prepareSection.inc.php'); 


#--(Begin)-->redirecto to login page if not logged in

if (strpos($_SERVER['PHP_SELF'],'login')===false)
	$userSystem->isLoggedIn(true);
#--(End)-->redirecto to login page if not logged in


#--(Begin)-->load section internal part if it has
if (wsfPrepareSectionToIncludeInternalPart('beforeHtml')) {
	include(dirname(__FILE__).'/../'.$fileToInclude);
}
#--(End)-->load section internal part if it has

if (isset($_GET['sn']) ){
	$sectionInfo = $_cp['sectionsInfo'][$_GET['sn']];
	$treeTableName = $sectionInfo['tableInfo']['tableName'];
	//print_r($sectionInfo);
}
else{
	echo "no section is defined. can't continue";
	return;
}

/*
@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
$db->conn=&$dbConnLink;
*/
?>