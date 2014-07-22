<?php
 /**
 *
 */

/**
 * 
 * @author salek
 *
 */
class cmfcWebPath {
	function factory($name,$configs) {
		if ($name=='queryStringBeta' or $name=='queryStringV1') {
			require_once(dirname(__FILE__).'/queryStringV1/webPathQueryStringV1.class.inc.php');
			return new cmfcWebPathQueryStringV1($options);
		}
		if ($name=='friendlyV1') {
			require_once(dirname(__FILE__).'/friendlyV1/webPathFriendlyV1.class.inc.php');
			return new cmfcWebPathFriendlyV1($options);
		}
		if ($name=='friendlyAlpha') {
			require_once(dirname(__FILE__).'/friendlyAlpha/webPathFriendlyAlpha.class.inc.php');
			return new cmfcWebPathFriendlyV1Alpha($options);
		}
	}
}
?>
