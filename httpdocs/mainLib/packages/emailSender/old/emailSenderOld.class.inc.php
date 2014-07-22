<?php
/**
* @version $Id: emailSenderOld.class.inc.php 351 2009-09-07 07:29:58Z salek $
* 
*/
if (!class_exists('PHPMailer'))
	trigger_error('dependency PHPMailer require for class cmfcEmailSenderOld does not exists',E_USER_ERROR);
	
class cmfcEmailSenderOld extends PHPMailer {
	var $email_template_system;//instance pointer of CEmailTemplateSystem
	var $clearEmptyReplacements=false;

	function clear() {
		$this->to=array();
	}

	function fill_via_template($template_email_id,$replacements,$includeSubject=false) {
		$this->email_template_system->load($template_email_id);
		if ($includeSubject)
			$this->Subject = replace_variables($replacements,$this->email_template_system->subject);

		$this->Body = replace_variables($replacements,$this->email_template_system->body);
		$this->AltBody = strip_tags($this->Body);
	}
}  
?>
