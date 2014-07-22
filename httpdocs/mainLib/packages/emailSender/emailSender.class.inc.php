<?php
/**
* @version $Id: emailSender.class.inc.php 351 2009-09-07 07:29:58Z salek $
* 
* @todo should become part of the email package  as of general lib 3
*/
class cmfcEmailSender {
	function factory($name,$options) {
		if ($name=='old') {
			require_once(dirname(__FILE__).'/old/emailSenderOld.class.inc.php');
			return new cmfcEmailSenderOld($options);
		}
	}
}