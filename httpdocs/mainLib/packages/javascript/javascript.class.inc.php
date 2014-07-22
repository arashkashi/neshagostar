<?php
/**
 * 
 * @var unknown_type
 */
define ('CMF_Javascript_Ok',true);
define ('CMF_Javascript_Error',2);

class cmfcJavascript {
	function factory($name,$options) {
		if ($name=='light') {
			require_once(dirname(__FILE__).'/light/javascriptLight.class.inc.php');
			return new cmfcJavascriptLight($options);
		}
		if ($name=='old') {
			require_once(dirname(__FILE__).'/old/javascriptOld.class.inc.php');
			return new cmfcJavascriptOld($options);
		}
	}
}
class cmfcJavscript extends cmfcJavascript {
	
}