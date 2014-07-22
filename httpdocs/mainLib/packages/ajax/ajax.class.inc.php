<?php
/**
* @version $Id: ajax.class.inc.php 504 2010-01-23 12:06:10Z salek $
*/
class cmfcAjax {
	function factory($name,$options) {
		if ($name=='v1' or $name=='everyWhereV1') {
			require_once(dirname(__FILE__).'/v1/ajaxEveryWhereV1.class.inc.php');
			return new cmfcAjaxEveryWhereV1($options);
		}
	}
}