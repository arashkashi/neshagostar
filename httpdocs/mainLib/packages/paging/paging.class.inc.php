<?php
/**
 * @version v1 $Id: paging.class.inc.php 328 2009-08-29 07:56:23Z salek $
*/
class cmfcPaging {
	function factory($name,$options) {
		if ($name=='ver2' or $name=='2' or $name=='2Dev' or $name=='v2' or $name=='version2' or $name='dbV2') {
			require_once(dirname(__FILE__).'/dbV2/pagingDbV2.class.inc.php');
			return new cmfcPagingDbV2($options);
		}
		if ($name=='beta' or $name=='dbBeta') {
			require_once(dirname(__FILE__).'/richPagingBeta.class.inc.php');
			return new cmfcRichPagingBeta($options);
		}
		if ($name=='old' or $name=='dbOld') {
			trigger_error('Incompatible with generallib Version 2!',E_USER_ERROR);
			require_once(dirname(__FILE__).'/richPagingOld.class.inc.php');
			return new cmfcRichPagingOld($options['total'],$options['limit'],$options['sqlQuery']);
		}
	}
}

class cmfcRichPaging extends cmfcPaging {
	//backward compatibility
}