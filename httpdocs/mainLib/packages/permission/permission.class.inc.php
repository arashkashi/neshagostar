<?php
/**
 * 
 * @author salek
 *
 */
class cmfcPermission {
	function factory($name,$options) {
		if ($name=='groupBaseSimpleBeta') {
			require(dirname(__FILE__).'/groupBaseSimpleBeta/permissionGroupBaseSimpleBeta.class.inc.php');
			return new cmfcPermissionGroupBaseSimpleBeta($options);
		}
		if ($name=='groupBaseSimple') {
			require(dirname(__FILE__).'/groupBaseSimple/permissionGroupBaseSimple.class.inc.php');
			return new cmfcPermissionGroupBaseSimple($options);
		}
	}
}

class cmfcUserPermissionSystem extends cmfcPermission {
	//for backward compatibitliy
}