<style type="text/css">
.td{
	border-bottom:#000000 1px solid;
	padding:5px;
}
</style>
<?php $emailTemplate -> loadByInternalName('base_template');

$internalName = $_REQUEST['internalName'];
//echo $internalName;

if ( !empty($internalName) ){

	//print_r($emailTemplate);
	//if ($emailTemplate -> _baseTemplateInternalName != $internalName)
	
	$templateExists = $emailTemplate->loadByInternalName($internalName);
}
//print_r($_columns);
//print_r($emailTemplate);
$replacements = array(
		'%inline_subject%' => $emailTemplate->cvInlineSubject,
		'%site_url%' => Site_URL,
		'%header%' => '',
	);

if (!empty($_columns)){
	$replacements = array_merge(
		$replacements,
		array(
			'%subject%'=>$_columns['subject'],
			'%inline_subject%' => $_columns['inlineSubject'],
			'%body%' => $_columns['body'],
		)
	);
}

//print_r($replacements);
$emailTemplate->process($replacements);	

//print_r($emailTemplate);
?>
<table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
<tr>
<td class="td" width="10%" valign="top">
موضوع: 
</td>
<td class="td" align="justify">
<?php echo $emailTemplate -> getSubject();
?>
</td>
</tr>
<tr>
<td class="td" valign="top">
متن:
</td>
<td class="td" align="justify">
<?php echo $emailTemplate -> getBody();
?>
</td>
</tr>
</table>