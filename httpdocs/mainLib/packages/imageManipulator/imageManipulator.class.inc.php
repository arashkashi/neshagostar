<?php
/**
* @version $Id: imageManipulator.class.inc.php 322 2009-08-25 13:50:59Z salek $
* 
* @todo should rename to image package as of general lib 3
*/
class cmfcImageManipulator {
	function factory($name,$options) {
		if ($name=='v1' or $name=='beta') {
			require_once(dirname(__FILE__).'/v1/imageManipulatorV1.class.inc.php');
			return new cmfcImageManipulatorV1($options);
		}
	}
}