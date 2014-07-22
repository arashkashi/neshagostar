<?php
ini_set('display_errors',0);
require(dirname(__FILE__).'/common/requirements/preparing.inc.php');
#--(Begin)-->define a captcha object
cmfcMySql::setOption('debugEnabled', true);
$smartCaptcha=cmfcSmartCaptcha::factory('visual',array(
    'sessionVarName'=>'niceCaptchaCode',
    'size'=>25,
	'fontAngle'=>array(-10,10),
    'marginTop'=>30,
	'marginLeft'=>10,
    'spacing'=>20,
	'width'=>100,
	'height'=>40,
	'color' => array(
		'r' => 255,
		'g' => 255,
		'b' => 255,
	),
	'captchaType' => 'charsLowercase',
//	'font'=>'BROKEN_GHOST.ttf'
));
#--(End)-->define a captcha object

$smartCaptcha->display();

//file_put_contents(dirname(__FILE__).'/files/cache/session.txt', print_r(cmfcMySql::getRegisteredQueries(), true));
?>