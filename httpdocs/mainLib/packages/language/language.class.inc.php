<?php
/**
* @version $Id: language.class.inc.php 379 2009-09-14 05:40:44Z salek $
*/
class cmfcLanguage {
	function factory($name,$configs) {
		if ($name=='beta') {
			require_once(dirname(__FILE__).'/beta/languageBeta.class.inc.php');
			return new cmfcLanguageBeta($configs);
		}
		if ($name=='v1') {
			require_once(dirname(__FILE__).'/v1/languageV1.class.inc.php');
			return new cmfcLanguageV1($configs);
		}
	}
}