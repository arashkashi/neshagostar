<?php
/**
* @todo should  move to captcha package as of general lib 3
*/
class cmfcCaptcha {
	function factory($name,$options) {
		if ($name=='smartV1Visual' or $name=='visual') {
			require_once(dirname(__FILE__).'/../captcha/smartV1/captchaSmartV1Visual.class.inc.php');
			return new cmfCaptchaSmartV1Visual($options);
		}
	}
}

class cmfcSmartCaptcha extends cmfcCaptcha {
	//backward compatibiltiy
}