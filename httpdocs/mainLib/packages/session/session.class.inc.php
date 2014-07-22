<?php
/**
* 
*/
class cmfcSession {
	function factory($name,$configs=null) {
		if ($name=='dbOld') {
			include(dirname(__FILE__).'/dbOld/sessionDbOld.class.inc.php');
			return new dbSession($configs);
		}
	}
}