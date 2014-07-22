<?php
//ob_start();
/**
 * initializing common packages of website accroding to config.inc.php
 * settings. package will only initialize if their require files included
 * in {@link config.inc.php}
<style type="text/css">
<!--
.style1 {color: #245EDC}
-->
</style>

<span class="style1"></span><br>
 * Including thie file in every website file is necessary.<br>7
 * have a look at source code for more information
 * this file will be used in both site and control panel. so it should only contains
 * common configs
 * 
 * @author Sina Salek
 * @package [D]/admin/requirements
 */

//--(Begin)-->Loading configuration
require(dirname(__FILE__).'/../../mainLib/init.php');
$_ws['configurator']=cmfGetInitObject();
$_ws['configurator']->setOptions(array(
	'configurationsFolderPath'=>realpath(dirname(__FIlE__).'/../../configurations'),
	'server'=>&$_SERVER,
	'legacyFormatEnabled'=>true
));

$_ws['packageManager']=&$_ws['configurator']->packageManager;
$_ws=$_ws['configurator']->load($_ws);
//--(End)-->Loading configuration


require(dirname(__FILE__).'/constants.inc.php');//this will override the old constants

/**
* including site common functions
*/
require_once(dirname(__FILE__).'/functions.inc.php');

//--(Begin)-->Loading generalLib
$_ws['configurator']->setOption('siteCacheFolderPath',$_ws['siteInfo']['path'].'/data/cache');
$r=$_ws['configurator']->packageManager->addPackages(array(
	array('type'=>'package','name'=>'cmf','version'=>'v2','file'=>array('PEAR.php','classesCore.class.inc.php','utf8.php','common.inc.php','datetime.class.inc.php','compatibility.inc.php','base.class.inc.php','mysql.class.inc.php','tableClassesBase2.class.inc.php'),'exact'=>false,'optional'=>false,'onlyConsider'=>false),
	//array('type'=>'packageExternalOld','name'=>'pear','version'=>'*','file'=>array('PHP/Compat/Function/scandir.php'),'exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'packageExternal','name'=>'excelWriter','version'=>'v2004-12-30','file'=>array('excelwriter.inc.php'),'exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'packageExternal','name'=>'jquery','version'=>'v1.2','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'packageExternal','name'=>'jquery','version'=>'v1.3','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'userSystem','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	
	array('type'=>'package','name'=>'emailTemplate','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'emailSender','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	
	array('type'=>'package','name'=>'javascript','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'paging','version'=>'dbV2','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'validation','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'search','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'wysiwyg','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'imageManipulator','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'session','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'hierarchicalSystem','version'=>'*','file'=>array('dbOld/requirements/dbMySql/dbMySql.class.inc.php'),'exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'paging','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'optimizer','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'captcha','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'ajax','version'=>'v1','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'language','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'translation','version'=>'*','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'webPath','version'=>'friendlyV1','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'slider','version'=>'multiFrameworkSimple','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false),
	array('type'=>'package','name'=>'subWindow','version'=>'jqueryThickbox','file'=>'','exact'=>false,'optional'=>false,'onlyConsider'=>false)
));
if ($_ws['configurator']->isError($r)){
	echo $r->getMessage();
	exit;
};
//--(End)-->Loading generalLib

#--(Begin)--> connecting to database
$dbConnLink = cmfcMySql::connect($_ws['databaseInfo']['host'], $_ws['databaseInfo']['username'], $_ws['databaseInfo']['password']);
if ($dbConnLink) {
	$isDbSelected=cmfcMySql::selectDb($_ws['databaseInfo']['name'], $dbConnLink);
	cmfcMySql::setConnectionLink($dbConnLink);
	if ($isDbSelected) {
		if ($_ws['databaseInfo']['collation']=='utf8') {
			cmfcMySql::exec('SET CHARACTER SET UTF8', $dbConnLink);
			cmfcMySql::exec("SET NAMES 'utf8'", $dbConnLink);
		}
	}
}
if (!$dbConnLink or !$isDbSelected) {
	header("HTTP/1.0 500 Internal Server Error");
	include("errorDocuments/unknown.html");
	exit();
}
#--(End)--> connecting to database


wsfSetSettingVars();

#--(Begin)--> URL system
$_ws['url']= $urlObject = $_ws['packageManager']->getPackageVersionInstance('package','webPath','friendlyV1',array(
	'tableName'=>$_ws['physicalTables']['urlAlias']['tableName'],
	//'propertiesColumn'=>$_ws['physicalTables']['languages']['columns'],
));
if(!$_insideAdmin) {
	$_GET=wsfPrepareGet($_GET);
	$_REQUEST=cmfcPhp4::array_merge($_REQUEST, $_GET);
}
#--(End)--> URL system




if (!$_insideAdmin)
{
	if ($_GET['sn'])
	{
		if ($_GET['sn'] == 'recruit' or 
				  $_GET['sn'] == 'agencyApplication' or 
				  $_GET['sn'] == 'messages' or 
				  $_GET['sn'] == 'orders' or
				  $_GET['sn'] == 'resellers'
				  )
		{
			if ($_GET['lang'])
				$_GET['lang'] = 'fa';
			if ($_REQUEST['lang'])
				$_REQUEST['lang'] = 'fa';
			if ($_POST['lang'])
				$_POST['lang'] = 'fa';	
		}
	}
}
else
{
	
}






#--(Begin)--> Languages
$language = $_ws['packageManager']->getPackageVersionInstance('package','language','v1',array(
	'tableName'=>$_ws['physicalTables']['languages']['tableName'],
	'dbModeEnabled'=>true,
	'loadDefaultWhenUnavailable'=>false,
	'defaultLanguageShortName'=>$_ws['siteInfo']['defaultLanguage'],
	'currentLanguageShortName'=>$_GET['lang']
));
$langInfo = $language->getLanguageInfo();
#--(End)--> Languages

#--(Begin)--> Translation
$translation = $_ws['packageManager']->getPackageVersionInstance('package','translation','interfaceV1',array(
	'tableName'=>$_ws['physicalTables']['words']['tableName'],
	'languageId'=>$language->cvId,
	'registerTotalRequestsEnabled'=>false,
	'loadPageWordsAtOnceEnabled'=>false
));
//For backward compatibility
$translation->setOption('languageObject',&$language);
$translation->languageInfo=&$langInfo;
#--(End)--> Translation

#--(Begin)--> translation system
/*
require_once(dirname(__FILE__).'/translation.class.inc.php');
$_customWs = array(
	'siteInfo' => $_ws['siteInfo'],
	'debugModeEnabled' => $_ws['debugModeEnabled'],
	'physicalTables' => array (
		'languages' => $_ws['physicalTables']['languages'],
		'words' => $_ws['physicalTables']['words'],
		'settings' => $_ws['physicalTables']['settings']
	),
	'translation' => $_ws['translation'],
	'insideAdmin' => $_insideAdmin,
);
$translation = new wscTranslation($_customWs);
$langInfo = $translation->createTraditionalLangInfo();
//cmfcHtml::printr($langInfo);
*/
#--(End)--> translation system

//cmfcHtml::printr($langInfo);

#--(Begin)--> Make an instance of DBSession
$session=cmfcSession::factory('dbOld',array(
	'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'sessions'
));

//Create the table if it's not already exists
//$session->createTable();
#--(end)--> Make an instance of DBSession


#--(Begin)--> Preparing and configuring email sender
/**
* This package takes care of sending emails. it has some useful
* methods
*/
if (class_exists('cmfcEmailSender')) {
	$emailSender = cmfcEmailSender::factory('old',array());
	$emailSender->From = $_ws['siteInfo']['emailsInfo']['info'];
	$emailSender->FromName = $_ws['siteInfo']['titleEn'];
	$emailSender->IsHTML(true); // send as HTML
	$emailSender->CharSet = "UTF-8";
	
	if (isset($_ws['smtp'])) {
		if (isset($_ws['smtp']['host'])) {
			$emailSender->Host = $_ws['smtp']['host']; // SMTP host
		}
		if (isset($_ws['smtp']['username'])) {
			$emailSender->IsSMTP();
			$emailSender->SMTPAuth = true; // turn on SMTP authentication
			$emailSender->Username = $_ws['smtp']['usernmae']; // SMTP username
		}
		if (isset($_ws['smtp']['password'])) {
			$emailSender->Password = $_ws['smtp']['password']; // SMTP password
		}
		if (isset($_ws['smtp']['port'])) {
			$emailSender->Port = $_ws['smtp']['port']; // SMTP port
		}
	}
	$_ws['emailSender']=&$emailSender;
}
#--(End)--> Preparing and configuring email sender


#--(Begin)-->email tempalte system
/**
* Email template parser for sending editable formatted emails
*/
if (class_exists('cmfcEmailTemplate')) {
	$emailTemplate=cmfcEmailTemplate::factory('ptSimpleBeta',array(
		'subjectMainFormatVariableName'=>'%subject%',
		'bodyMainFormatVariableName'=>'%body%',
		'baseTemplateInternalName'=>'base_template'
	));
	$emailTemplate->_tableName = $_ws['physicalTables']['emailTemplates']['tableName'];
}
#--(End)-->email tempalte system


#--(Begin)-->make an instance of cJavscriptFunctions for using in head section
/**
* Php interface for popular javascript functions, for using it
* you should print your desire functions in htmlHead.inc.php via
* mentioning their names to a method. (there is sample in htmlHead.inc.php)
*/
if (class_exists('cmfcJavscript')) 
	{
		$js=cmfcJavscript::factory('light',array());
	}
#--(End)-->make an instance of cJavscriptFunctions for using in head section

#--(Begin)-->Validation
/**
* Combo validation class that means it validate html forms or any other user input
* via php and also javascript.
* it's possible via defining an array contains details information about form fields
* and passing it to a method of this package. (there is a sample in root/contact.inc.php)
* 
* Error messages are also customizable,
*/
if (class_exists('cmfcValidation')) {
	$validation=cmfcValidation::factory('v1',array(
		'jsInstanceName'=>'myValidation',
		'formName'=>'myForm'
	));

	/**
	* @desc for multilingual website you can change this messages easily
	* @package test
	*/
	if($langInfo['sName'] == 'fa')
	{
		$validation->setOption('messagesValue',array(
			CMF_ValidationV1_Error=>'Unknown error',
			CMF_ValidationV1_Is_Not_Valid_Email=>'"__value__" در فیلد "__title__" آدرس ایمیل معتبر نیست',
			CMF_ValidationV1_Is_Not_Valid_Url=>'"__value__" در فیلد "__title__" url معتبر نیست',
			CMF_ValidationV1_Is_Not_Number=>'"__value__" در فیلد "__title__" عدد نیست , مقدار خالی صفر است',
			CMF_ValidationV1_Is_Not_Within_Range=>'"__value__" در فیلد "__title__" در محدوده مقابل نیست (__min__,__max__)',
			CMF_ValidationV1_Is_Not_Within_Count_Range=>'"__value__" در فیلد "__title__" در محدوده مقابل نیست (حداقل __min__ رقم, حد اکثر __max__ رقم)',
			CMF_ValidationV1_Is_Empty=>'فیلد "__title__" خالی است',
			CMF_ValidationV1_Is_Not_Selected=>'فیلد "__title__" انتخاب نشده است',
			CMF_ValidationV1_Is_Not_Within_Length_Range=>'طول مقدار فیلد "__title__" در محدوده مقابل نیست (حداقل __min__ کاراکتر, حد اکثر __max__ کاراکتر)',
			CMF_ValidationV1_Is_Not_String=>'فیلد "__title__" از نوع متنی نیست',
			CMF_ValidationV1_Is_Not_Match_With_Pattern=>'مقدار فیلد "__title__" مطابق این شرایط نیست : __desc__ ',
			CMF_ValidationV1_Field_Does_No_Exists=>'__title__ field "__fieldName__" وجود ندارد'
		));
	}
}
#--(End)-->Validation


#--(Begin)-->making & preparing an instance of cmfcUserSystem
/**
* Website authentication system 
*/

/**
* Defining permission system that can be attached to userSystem for checking
* and setting users permissions.
* The only availabe mode right now is "groupBaseSimpleBeta" 
* it is based on access points. there is table in database called "site_sections"
* that contains different sections of website, in contorl panel it's possible to
* select each one of this section of a specific user group.
* users of each user group can see selected access points of their user group
*/
$userPermissionsSystem=cmfcUserPermissionSystem::factory('groupBaseSimpleBeta',array(
	'tableName'=>$_ws['physicalTables']['userGroups']['tableName']
));

/**
* User authentication package that has numerous features
* 	- error handling system
* 	- customizable table,columns
* 	- customizable validation
* 	- customizable permission system
* 	- auto redirection system
* 	- remember me
* 	- customized email sender
* 	- auto activation system
* 	- etc
*/
$userSystem=cmfcUserSystem::factory('advancedBeta',array(
	'tableName'=>$_ws['physicalTables']['users']['tableName'],
	'language'=>'fa',
	//'defaultAccessLevel'=>UserSystem_Default_Access_Level,
	'permissionSystem'=>&$userPermissionsSystem,#instance of a compatible permissions system
	'passwordRules'=>'همه کاراکترها با حداقل طول ۵ و حداکثر ۱۰ کاراکتر', # sample : All characters , minimum length is 6
	'passwordRegexValidator'=>'/.{5,10}/', # sample : /.{6,}/
	'usernameRules'=>'همه کاراکترها با حداقل طول ۵ و حداکثر ۱۰ کاراکتر', # sample : All characters , minimum length is 6
	'usernameRegexValidator'=>'/.{5,10}/', # sample : /.{6,}/
	'autoActivation'=>true, # if true, activation doesn't need admin approval
	'autoRedirectEnabled'=>true, #redirects to defined pages after specific events
	'keepPasswordEnabled'=>false,
//	'rememberUserEnabled'=>true,
	'pagesInfo'=>array( #pages for redirection
		'default'=>'index.php',
		'login'=>'login.php',
		'afterLogin'=>'index.php?',
		'afterLogout'=>'index.php',
		'afterActivation'=>'',
		'activation'=>'?sn=userAccountActivation',
		'denyAccess'=>'?sn=denyAccess',
		'requestNewPassword'=>'?sn=userForgetPassword&action=applyNewPassword'
	)
));
if($langInfo['sName'] == 'fa')
{
	$userSystem->setOption("messagesValue", array(
		CMF_UserSystem_Username_Is_Not_Valid=>'نام کاربری "%username%" معتبر نیست, از این قواعد نام گذاری پیروی کنید : %usernameRules%',
		CMF_UserSystem_Email_Is_Empty=>'ایمیل وارد نشده است',
		CMF_UserSystem_Email_Is_No_Valid=>'ایمل "%email%" معتبر نیست',
		CMF_UserSystem_Password_Is_Not_Valid=>'رمز عبور "%password%" معتبر نیست, از این قواعد نام گذاری پیروی کنید : %passwordRules%',
		CMF_UserSystem_User_Account_Does_No_Exists=>'حساب کاربری با نام "%username%" وجود ندارد',
		CMF_UserSystem_Username_Or_Password_Is_Empty=>'نام کاربری یا رمز عبور خالی است.',
		CMF_UserSystem_Username_And_Password_Do_Not_Match=>'نام کاربر و رمز عبور مطابقت ندارند',
		CMF_UserSystem_User_Account_Is_Not_Active=>'حساب کاربر فعال نمی‌باشد : %username%',
		CMF_UserSystem_Auto_Activation_Is_Not_Enabled=>'فعال سازی خودکار فعال نیست',
		CMF_UserSystem_Confirmation_Code_Is_Not_Valid=>'کد فعال سازی معتبر نیست. کد (%activationCode%) نام کاربری (%username%)',
		CMF_UserSystem_Activation_Failed=>'فعال سازی حساب کاربری موفقیت آمیز نبود, علت نامعلوم است',
		CMF_UserSystem_Incompatible_Permission_System=>'Permission system "%permissionSystemName%" is not compatible with %userSystemClass%  ,select on of this : %supportedPermissionSystems%',
		CMF_UserSystem_No_Permission_System_Available=>'There is not permission system specified',
		CMF_UserSystem_Password_Is_Empty=>'رمز عبور وارد نشده است',
		CMF_UserSystem_Username_Is_Duplicate=>'نام کاربر تکراری است',
		CMF_UserSystem_Email_Is_Duplicate=>'ایمیل تکراری است',
		CMF_UserSystem_Username_Or_Email_Does_Not_Exists=>'نام کاربری یا ایمیل وجود ندارد!'
	));
}

function onUserSystemSendEmail(&$commander,$command,$params=null) {
	$commander->setOption("pagesInfo", $commander->_options['pagesInfo']);
	global $emailTemplate;
	global $emailSender;
	global $_ws;
	switch ($command) {
		case 'sendEmailAfterRegistration' : $templateInternalName='user_account_activation';break;
		case 'sendEmailAfterActivation' : $templateInternalName='user_welcome';break;
		case 'sendEmailRequestNewPassword' : $templateInternalName='user_request_new_password';break;
		case 'sendEmailNewPasswordApplied' : $templateInternalName='user_new_password_applied';break;
		default : break;
	}
	//echo $templateInternalName.' ';
	
	if ($emailTemplate->loadByInternalName($templateInternalName)!==false) 
	{
		$replacements=array(
			'%header%'=>'',
			'%username%'=>$params[$commander->_colnUsername],
			'%password%'=>$params[$commander->_colnPassword],
			'%site_title%'=>$_ws['siteInfo']['title'],
			'%site_url%'=>$_ws['siteInfo']['url'],
			'%inline_subject%'=>$emailTemplate->cvInlineSubject,
			'%user_full_name%'=>$params['full_name'],
			'%new_password%'=>$params[$commander->_colnPassword],
			'%request_new_password_url%'=>$_ws['siteInfo']['url'].$commander->_pagesInfo['requestNewPassword'].'&email='.$params[$commander->_colnEmail].'&activation_code='.$params[$commander->_colnActivationCode],
			'%activation_url%'=>$_ws['siteInfo']['url'].$commander->_pagesInfo['activation'].'&username='.$params[$commander->_colnUsername].'&activation_code='.$params[$commander->_colnActivationCode],
		);
		
		
		$emailTemplate->process($replacements);
		$emailSender->addAddress($params[$commander->_colnEmail]);
		$emailSender->Subject=$emailTemplate->getSubject();
		$emailSender->Body=$emailTemplate->getBody();
		
		$x = $emailSender->send();
		//echo ' succeeded: ';
		//var_dump($emailSender);

		return $x;
	}
	//echo ' failed';
	return false;
}

/**
* userSystem doesn't have built in email sender, but it can connect to
* external email sender and use it for sending emails.
* for using this ability you should define a emailsender and also a function
* called "onUserSystemSendEmail" for sending emails
*/
if (is_callable('onUserSystemSendEmail')) {
	$userSystem->addCommandHandler('sendEmailAfterRegistration','onUserSystemSendEmail');
	$userSystem->addCommandHandler('sendEmailAfterActivation','onUserSystemSendEmail');
	$userSystem->addCommandHandler('sendEmailRequestNewPassword','onUserSystemSendEmail');
	$userSystem->addCommandHandler('sendEmailNewPasswordApplied','onUserSystemSendEmail');
}
#--(End)-->making & preparing an instance of cmfUserSystem






#--(Begin)-->fetch protected nodes for protecting and also assigning to sections
$_ws["Main_Tree_Nodes"]=array();
$_tableInfo=$_ws['physicalTables']['categories'];
$rows=cmfcMySql::getRowsCustom("SELECT * FROM ".$_tableInfo['tableName']." WHERE ".$_tableInfo['columns']['internalName']."<>'' AND ".$_tableInfo['columns']['internalName']." IS NOT NULL");
if (is_array($rows))
foreach ($rows as $row) {
	$_ws["Main_Tree_Nodes"][$row[$_tableInfo['columns']['internalName']]]=$row[$_tableInfo['columns']['id']];
	$_ws["Main_Tree_Nodes_Details"][$row[$_tableInfo['columns']['internalName']]]=$row;
}
$_ws["Main_Tree_Protected_Nodes"]=array_values($_ws["Main_Tree_Nodes"]);
#--(End)-->fetch protected nodes for protecting and also assigning to sections




/*$imageManipulator = cmfcImageManipulator::factory(
	'v1',
	array(
		'cacheFolderRPath' => $_ws['siteInfo']['cacheFolderRPath'],
		'sitePath' => $_ws['siteInfo']['path'],
		'siteUrl' => $_ws['siteInfo']['url'],
		//'showDebug' => true,
		'watermarkTextFont' 	=>  $_ws['siteInfo']['path'].'/mainLib/dependencies/fonts/arial.ttf',
	)
);*/

$imageManipulator = cmfcImageManipulator::factory(
	'v1',
	array(
		'cacheFolderRPath' => $_ws['siteInfo']['cacheFolderRPath'],
		'sitePath' => $_ws['siteInfo']['path'],
		'siteUrl' => $_ws['siteInfo']['url'],
		//'watermarkText'=>'© ',
		//'watermarkPattern'=>'zigZag',
		//'watermarkTextSize'=>null,
		//'watermarkRepeatsNumber'=>1,
		//'watermarkHorizontalMargin'=>'auto',
		//'watermarkVerticalMargin'=>'auto',
		//'watermarkHorizontalAlign'=>'center',
		//'watermarkVerticalAlign'=>'center',
		'quality'=>90,
		//'watermarkOpacity'=>70,
		//'watermarkTextPosition'=>array(left,bottom),
		//'watermarkTextFont' =>  $_ws['siteInfo']['path'].'/mainLib/dependencies/fonts/arial.ttf',
		'disableCache'=>false,
		//'showDebug' => true,
		
	)
);




#--(Begin)-->make an instance of wysiwyg
/**
* Php interface for wysiwyg editors
*/
$xinha=$_ws['packageManager']->getPackageVersionInstance('packageExternal','xinha','v0');
$wysiwyg=cmfcWysiwyg::factory('xinhaV1',array(
	'formName'=>'myForm',
	//'xinhaUrl'=>Xinha_Url,
	//'xinhaPath'=>Xinha_Path,

	'xinhaUrl'=>$xinha->packageGetFolderPathInBrowser(),
	'xinhaPath'=>$xinha->packageGetFolderPathFull(),
	'skinName'=>'blueLook',
	'language'=> $langInfo['sName'],
	'cssFileAddress'=>'interface/css/editor.css',
	'templateName'=>'full',
));
#--(End)-->make an instance of wysiwyg

$_GET = wsfConvertPostedDataToStandardPersianCharacters($_GET);
$_POST = wsfConvertPostedDataToStandardPersianCharacters($_POST);
$_REQUEST = wsfConvertPostedDataToStandardPersianCharacters($_REQUEST);


#--(Begin)-->Website main tree object initializing
@$db = new cmfcHierarchicalSystemDbTreeDb(null, null, null, null);
$db->conn=&$dbConnLink;
//$myTree = new cDbTreeView('tree', '', $db);
$mainTree = cmfcHierarchicalSystem::factory('dbOld',
	array(
		'tableName'=>$_ws['physicalTables']['categories']['tableName'],
		'dbInstance'=>$db,
		'prefix'=> '' 
	)
);
$mainTree->setColnLink('link');
$mainTree->setColnLeftNumber('lft');
$mainTree->setColnRightNumber('rgt');
$mainTree->setColnLevelNumber('level');
$mainTree->setColnVisible('visible');
$mainTree->setPrefix('myTree');
$mainTree->setColumnsNames(array('name','name_en'));
$mainTree->setTitleColumnName('name');
$mainTree->setNodeVisibilityEnabled(false);
$mainTree->setDisplayMode('view');
$mainTree->setTemplates($templates);
$myTree=clone($mainTree);
#--(End)-->Website main tree object initializing

$optimizer = cmfcOptimizer::factory('multiFileV1',array(
	'cacheFolderPath'=>$_ws['siteInfo']['path'].'data/cache',//Should be accessible by browser
	//'cacheFolderPathBrowser'=>'',						//Should be accessible by browser
	//'pageFolderPath'=>dirname(__FILE__),
	//'pageFolderPathBrowser'=>'',
	//'siteFolderPath'=>'',
	//'siteFolderPathBrowser'=>'',
	//'filesDefaultMethods'=>array('minify','compress'),
	'cacheEnabled'=>true,
	//'debugEnabled'=>true,
	//'javascriptRemoveLineBreaks'=>false,
	
));

#--(Begin)-->Ajax package intilization
$ajax = cmfcAjax::factory('everyWhereV1',array(
	'get'=>&$_GET,
	'server'=>&$_SERVER,
	'requestUri'=>&$_SERVER['REQUEST_URI'],
	'delayResponse'=>2,//in seconds
	'debugModeEnabled'=>false,
	'jsTimeout'=>5000 //in millisecons
));
//cmfcHtml::printr($ajax);

//If you want to use it with optimizer package :
if ($optimizer) {
	$ajax->setOption('optimizerObj',&$optimizer);
}



$ajax->setOption('jsLoadingIndicator',array(
	//'trigger'=>array('enabled'=>true),
	'content'=>array(
		'enabled'=>true,
		'imageUrl'=>'interface/images/ajax-loader.gif',
		)
),true);
$ajax->setOption('jsLoadingIndicatorGlobal',array(
		'enabled'=>false
	),true);
/* */
//In order to completely ignore default parameters set the last parameter to false
if ($_GET['debug']=='true') 
{
	$ajax->setOption('debugModeEnabled',true);
}
/*
$ajax->setOption('jsLoadingIndicatorGlobal',array(
	'id'=>'ajaxFreeLoading'
),true);
*/
#--(End)-->Ajax package implementation
