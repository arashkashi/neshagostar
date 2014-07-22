<?php
/**
 * Any common html and javascript codes between <head> and </head>
 * should put here<br>
 * 
 * rules :
 * 	- name of javascript functions should start with "wsf" as described here {@link functions.inc.php}
 * 
 * @package [D]/requirements
 * @author Sina Salek
 */

$htmlTags .= 
'<link href="/interface/css/style.css" rel="stylesheet" type="text/css" />
<link href="/interface/css/style-'.$translation->languageInfo['direction'].'.css" rel="stylesheet" type="text/css" />
';

if (trim($_SERVER['PHP_SELF'],'/')!="print.php")
{
	$jquery=$_ws['packageManager']->getPackageVersionInstance('packageExternal','jquery','v1.3');
	$slider = $_ws['packageManager']->getPackageVersionInstance('package','slider','multiFrameworkSimple');
	$subWindow = $_ws['packageManager']->getPackageVersionInstance('package','subWindow','jqueryThickbox');

	$htmlTags .= '
        <script type="text/javascript" src="/interface/javascripts/jquery-1.3.2.min.js"></script>
		<script type="text/javascript">tb_pathToImage="'.$subWindow->packageGetFilePathInBrowser('loading2.gif').'";</script>
		<script src="'.$subWindow->packageGetFilePathInBrowser('thickbox.js').'" type="text/javascript"></script>
		<link href="'.$subWindow->packageGetFilePathInBrowser('thickbox.css').'" rel="stylesheet" type="text/css"  media="screen" />
		<script src="'.$subWindow->packageGetFilePathInBrowser('compatibility.js').'" type="text/javascript"></script>

		<script type="text/javascript" src="interface/javascripts/jquery.cycle.lite.min.js"></script>
	';
	
	$js->printJsFunctions(array(
		//'confimationMessage',
		//'toggleTabsDisplayStyle',
		//'//toggleDisplayStyle',
		'popitup',
		//'getElements',
		//'createHtmlNode',
		//'htmlEntityDecode',
		//'clearSelectObject',
		//'clone'
	));
	
	
	#--(Begin)-->include section related javascripts
	//echo wsfIncludeSectionRelatedJavascripts();
	$htmlTags .= wsfIncludeSectionRelatedJavascripts();
	#--(End)-->include section related javascripts
	
	
	#--(Begin)-->include section related javascripts
	//echo wsfIncludeSectionRelatedCss();
	$htmlTags .= wsfIncludeSectionRelatedCss();
	#--(End)-->include section related javascripts
	$opt = FALSE;
	if ($opt) 
	{
		echo $optimizer->getTagsOptimizedVersion($htmlTags,array(
			'files'	=>	array(
				'/interface/javascripts/farsitype.min.js'	=>	array(	//It's already minified
					'methods'	=>	array('compress'),
					'javascriptRemoveLineBreaks' => false
				),
				
				'/interface/javascripts/ajaxupload.3.6.js'=>array(
					'methods'	=>	array('compress'),
					'javascriptRemoveLineBreaks' => true
				),
			),
			'groups'=>array(				//We want the whole folder to be copied to cache folder not only the defined files
				'/interface/css/ui-darkness'=>array(''),
				/*			
				'interface/javascripts/calendar'=>array('javascriptRemoveLineBreaks','compress')
				*/
			)
		));
	} 
	else 
	{
		echo $htmlTags;
	}
}
#--(Begin)-->load section internal part if it has
if (is_array($_ws['sectionInfo']['internalParts']))
	if (in_array('inHeader',$_ws['sectionInfo']['internalParts'])) {
		$_ws['currentSectionInternalPartName']='inHeader';
		include(dirname(__FILE__).'/../'.$fileToInclude);
	}
#--(End)-->load section internal part if it has
if (!$ajax->isCalledByAjax()) 
{
	$ajax->printRequirements(array('alreadyIncluded'=>array('jquery'/*,'jquery.browser'*/)));
}
?>
<script language="javascript" type="text/javascript">
	//FarsiType.ShowChangeLangButton = 0;
	//FarsiType.KeyBoardError = 1;
	//FarsiType.ChangeDir = 0;
</script>
