<?php
/**
* @version $Id: configurator.class.inc.php 229 2009-06-21 08:54:09Z sinasalek $
* 
*/

class cmfcPackageManager {
	function factory($name,$options) {
		if ($name=='v1') {
			require_once(dirname(__FILE__).'/v1/packageManagerV1.class.inc.php');
			return new cmfcPackageManagerV1($options);
		}
	}
}