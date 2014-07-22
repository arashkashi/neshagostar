<?php
/**
* @version $Id: validation.class.inc.php 322 2009-08-25 13:50:59Z salek $
*/
class cmfcValidation {
	function factory($name,$options) {
		if ($name=='beta' or $name=='v1' or $name=='version1') {
			require_once(dirname(__FILE__).'/v1/validationV1.class.inc.php');
			return new cmfcValidationV1($options);
		}
	}
}