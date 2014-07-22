<?php
class cmfcWysiwyg {
	function factory($name,$options) {
		if ($name=='xinhaV1') {
			require_once(dirname(__FILE__).'/xinhaV1/wysiwygXinhaV1.class.inc.php');
			return new cmfcWysiwygXinhaV1($options);
		}
	}
}