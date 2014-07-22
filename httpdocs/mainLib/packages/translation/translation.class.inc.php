<?php
/**
* @version $Id: translation.class.inc.php 364 2009-09-07 10:47:03Z salek $
*/
class cmfcTranslation extends cmfcTableClassesBase2 {
	function factory($name,$options) {
		if ($name=='interfaceV1') {
			require_once(dirname(__FILE__).'/interfaceV1/translationInterfaceV1.class.inc.php');
			return new cmfcTranslationInterfaceV1($options);
		}
		
		if ($name=='alpha') {
			require_once(dirname(__FILE__).'/alpha/translationAlpha.class.inc.php');
			return new cmfcTranslationAlpha($options);
		}
	}
}