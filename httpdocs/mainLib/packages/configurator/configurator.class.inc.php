<?php
/**
* @version $Id: configurator.class.inc.php 229 2009-06-21 08:54:09Z sinasalek $
* 
*/

class cmfcConfigurator {
	function factory($name,$options) {
		if ($name=='standAloneMultiAutoV1') {
			require_once('standAloneMultiAutoV1/configuratorStandAloneMultiAutoV1.class.inc.php');
			return new cmfcConfiguratorStandAloneMultiAutoV1($options);
		}
	}
}