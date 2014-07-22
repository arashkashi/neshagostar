<?php
/**
* @version $Id: optimizer.class.inc.php 322 2009-08-25 13:50:59Z salek $
*/
class cmfcOptimizer {
	function factory($name,$options) {
		if ($name=='multiFileV1') {
			require_once(dirname(__FILE__).'/multiFileV1/optimizerMultiFileV1.class.inc.php');
			return new cmfcOptimizerMultiFileV1($options);
		}
	}
}