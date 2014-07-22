<?php
/**
 * @author Sina Salek
 * @package userSystem
 * @todo should rename to user as of general lib 3
 * @todo permission system package should be separate package called permission as of general lib 3
 * @version $Id: userSystem.class.inc.php 288 2009-08-23 11:05:27Z salek $
 */
 

class cmfcUserSystem {
	function factory($name,$options) {
		if ($name=='v1' or $name=='advancedBeta') {
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'v1'.DIRECTORY_SEPARATOR.'userSystemV1.class.inc.php');
			return new cmfcUserSystemV1($options);
		}
		if ($name=='advancedBetaParallel' or $name=='v1Parallel') {//should port to v1
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'v1'.DIRECTORY_SEPARATOR.'userSystemV1.class.inc.php');
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'v1Paralell'.DIRECTORY_SEPARATOR.'userSystemV1Parallel.class.inc.php');
			return new cmfcUserSystemV1Parallel($options);
		}
	}
}