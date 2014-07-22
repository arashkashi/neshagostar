<?php
/**
* @version $Id: emailTemplate.class.inc.php 351 2009-09-07 07:29:58Z salek $
* 
* @todo should be more generic and should be rename to tempalte as of general lib 3
*/
class cmfcEmailTemplate {
	function factory($name,$configs) {
		if ($name=='simpleBeta' or $name=='ptSimpleBeta' or $name=='simple') {
			require_once(dirname(__FILE__).'/simple/emailTemplateSimple.class.inc.php');
			return new cmfcEmailTemplateSimple($configs);
		}
	}
}