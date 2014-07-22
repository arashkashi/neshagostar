<?php
/**
* @todo should rename to hierarchical as of general lib 3
*/
class cmfcHierarchicalSystem {
	function factory($name,$options) {
		if ($name=='dbBeta') {
			require_once(dirname(__FILE__).'/core.class.inc.php');
			return new cmfcHierarchicalSystemDbBeta($options);
		}
		
		if ($name=='dbOld') {
			require_once(dirname(__FILE__).'/dbOld/dbtree.view.class.php');
			return new cmfcDbTreeView($options);
		}
		
		if ($name=='dbOldCanola') {
			require_once(dirname(__FILE__).'/dbOld/dbtreeCanola.view.class.php');
			return new cmfcDbTreeView($options);
		}
	}
}