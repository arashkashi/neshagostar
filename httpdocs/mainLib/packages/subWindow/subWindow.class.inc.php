<?php
/**
* @version $Id: configurator.class.inc.php 229 2009-06-21 08:54:09Z sinasalek $
* 
*/

class cmfcSubWindow {
	function factory($name,$options) {
		if ($name=='!!!' or 1==1) {
			//require_once('standAloneMultiAutoV1/configuratorStandAloneMultiAutoV1.class.inc.php');
			return new cmfcPackageManagerV1Wrapper($options);
		}
	}
}