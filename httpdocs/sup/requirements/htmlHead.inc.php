<?php
/**
 * Any common html and javascript codes between <head> and </head>
 * should put here<br>
 * 
 * rules :
 * 	- name of javascript functions should start with "cpf" as described here {@link functions.inc.php}
 * 
 * @package [D]/requirements
 * @author Sina Salek
 */

$jquery = $_ws['packageManager']->getPackageVersionInstance('packageExternal','jquery','v1.3');
$slider = $_ws['packageManager']->getPackageVersionInstance('package','slider','multiFrameworkSimple');
$subWindow = $_ws['packageManager']->getPackageVersionInstance('package','subWindow','jqueryThickbox');

$htmlHeadTags = '
<script src="'.$jquery->packageGetFilePathInBrowser('dist/jquery.js').'" type="text/javascript"></script>
<link href="interface/css/common'.$langInfo['dbBigLang'].'.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">tb_pathToImage="'.$subWindow->packageGetFilePathInBrowser('loading2.gif').'";</script>
<script src="'.$subWindow->packageGetFilePathInBrowser('thickbox.js').'" type="text/javascript"></script>
<link href="'.$subWindow->packageGetFilePathInBrowser('thickbox.css').'" rel="stylesheet" type="text/css"  media="screen" />
<script src="'.$subWindow->packageGetFilePathInBrowser('compatibility.js').'" type="text/javascript"></script>
<script src="'.$slider->packageGetFilePathInBrowser('sliderMultiFrameworkSimple.class.inc.js').'" type="text/javascript"></script>
<script type="text/javascript" src="interface/javascripts/jquery/ui.core.js"></script>
<script type="text/javascript" src="interface/javascripts/jquery/ui.sortable.js"></script>';


$js->printJsFunctions(array(
	'confimationMessage',
	'toggleTabsDisplayStyle',
	'toggleDisplayStyle',
	'popitup',
	'getElements',
	'createHtmlNode',
	'htmlEntityDecode',
	'clearSelectObject',
	'clone'
));

//require(dirname(__FILE__)."/ajax.define.inc.php");

//if (is_object($xajax))
	//$xajax->printJavascript($_ws['generalLibInfo']['url'].'/packages/xajax/');


#--(Begin)-->include section related javascripts
$htmlHeadTags .= wsfIncludeSectionRelatedJavascripts();
#--(End)-->include section related javascripts

#--(Begin)-->include section related javascripts
$htmlHeadTags .= wsfIncludeSectionRelatedCss();
#--(End)-->include section related javascripts

$opt = FALSE;
if ($opt) 
{
	echo $optimizer->getTagsOptimizedVersion($htmlHeadTags,array(
		'files'	=>	array(
			
			'/admin/interface/javascripts//jqupload/jquery.jqUploader.js'=>array(
				'methods'	=>	array('compress'),
				'javascriptRemoveLineBreaks' => false
			),
			/*
			'interface/javascripts/test.js'=>array(			//This one is not semicolon safe, we need to mention it
				'javascriptRemoveLineBreaks'	=>	false	//This option is only avaialbe for php5
			)*/
		),
		'groups'=>array(				//We want the whole folder to be copied to cache folder not only the defined files
			/*
			'interface/css/smoothness'=>array(''),
			'/interface/css/calendar/aqua'=>array('')	
			'interface/javascripts/calendar'=>array('javascriptRemoveLineBreaks','compress')
			*/
		)
	));
} 
else 
{
	echo $htmlHeadTags;
}

#--(Begin)-->load section internal part if it has
if (wsfPrepareSectionToIncludeInternalPart('inHeader')) {
	include(dirname(__FILE__).'/../'.$fileToInclude);
}
#--(End)-->load section internal part if it has

if (!$ajax->isCalledByAjax()) 
{
	$ajax->printRequirements(array('alreadyIncluded'=>array('jquery'/*,'jquery.browser'*/)));
}
?>

<script type="text/javascript">
//<![CDATA[
	function cpfOption(i) {
		$('option-buttons').style.display='';
		var j=1
		for (j=1;j<=3;j++) {
			if ($('option-'+j)) {
				$('option-'+j).style.display='none';
				$('option-button-'+j).className='option-table-buttons';
			}
		}
		
		if ($('option-'+i)) {
			$('option-'+i).style.display='';
			$('option-button-'+i).className='option-table-buttons-select';
		}
	}
	
	function cpfToggleCheckBoxes(me,container) {
		if (typeof(container)=='string') container=document.getElementById(container);
		var myAll=container.getElementsByTagName('INPUT');
		for (i in myAll) {
			var elm=myAll[i];
			if (elm.type=='checkbox') {
				elm.checked=me.checked;
			}
		}
	}
//]]>
</script>


<script language="javascript" type="text/javascript">
//<![CDATA[
	function cpfAddNewBox(boxContainerId,tempBoxId,baseName) {

		var itemNumber=0;
		var elementName;
		var element;
		
		do {
			//alert(itemNumber);
			itemNumber++;
			elementName=baseName+'['+itemNumber+'][columns][id]';
			element=document.myForm.elements[elementName];
			
		} while (element);
	
		
		
		var itemsBorad=document.getElementById(boxContainerId);
		var templateItemBox=document.getElementById(tempBoxId).innerHTML;
		

		templateItemBox=<?php echo $js->jsfHtmlEntityDecode($js->asJsCode('templateItemBox'))?>;
		

		//alert(templateItemBox);
		var myregexp = new RegExp("%{item_number}%", "gmi");
		templateItemBox=templateItemBox.replace(myregexp,itemNumber);
		myregexp = new RegExp("%[^ %{}]*%", "gmi");
		if (document.all) {
			templateItemBox=templateItemBox.replace(myregexp,'');
		} else {
			templateItemBox=templateItemBox.replace(myregexp,'');
		}

		//itemsBorad.innerHTML=itemsBorad.innerHTML+templateItemBox;
		var htmlNode=<?php echo $js->jsfCreateHtmlNode('span',$js->asJsCode('templateItemBox'))?>;
		itemsBorad.appendChild( htmlNode );
	}	
//]]>
</script>

<script type="text/javascript">
//<![CDATA[
	var backupElements=new Array(2);
	function onSelectParentDropDown(parentElmId, childElmId) {
	
		var parent=document.getElementById(parentElmId);
		var child=document.getElementById(childElmId);
		var display='none';

		if (!backupElements[childElmId]) {
			//alert(clone(document.getElementById(childElmId)));
			backupElements[childElmId]=document.getElementById(childElmId).cloneNode(true);
		}

		var orgChild=backupElements[childElmId];
		<?php echo $js->jsfClearSelectObject($js->asJsCode('child'))?>
		
		orgItems = orgChild.getElementsByTagName('optgroup');
		child.appendChild(new Option());
		for (var j = 0; j < orgItems.length; j++)
		{
			if (parent.value==orgItems[j].title) {
				child.appendChild(orgItems[j].cloneNode(true));
			} else {
			}
		}
				

	}
	
	function onKeyPressParentDropDown(e,parent_id, child_id) {
		if (!e) e=event;
		if (document.all) {
			k = e.keyCode ;
		} else {
			k = e.which ;
			if (k==9 || k==13) 
				onSelectParentDropDown(parent_id,child_id);
		}
	}
//]]>
</script>

<script language="javascript" type="text/javascript">
//<![CDATA[
function jsSyncPathStatus(thisPtr, categoryPathFieldId){
	document.getElementById(categoryPathFieldId).value = thisPtr.checked;
}
function cpfAcceptOnHome(rowId, section, action)
{
	document.getElementById('image-'+section+action+rowId).style.display = 'none';
	xajaxAcceptOnHome(rowId, section, action);
	document.getElementById('image-'+section+action+rowId).style.display = '';
}
//]]>
</script>

<script language="javascript" type="text/javascript">
//<![CDATA[
	function changeLang(lang){
		document.getElementById('itemLanguage').value = lang;
		document.forms['myForm'].changeLangButton.click();
	}
	
	function selectLang(){
		document.mySearchForm.submit();
	}
//]]>
</script>




<!--[if lt IE 7]>
<style type="text/css">
	img {
	   behavior: url("interface/css/pngbehavior.htc");
	}
</style>
<![endif]-->