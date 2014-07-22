<?php
/**
* @version $Id: search.class.inc.php 322 2009-08-25 13:50:59Z salek $
* 
*/
class cmfcSearch {
	function factory($name,$options) {
		if ($name=='dbCombinedV1') {
			require_once(dirname(__FILE__).'/dbCombinedV1/searchDbCombinedV1.class.inc.php');
			return new cmfcSearchDbCombinedV1($options);
		}
	}
}