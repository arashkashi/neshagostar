<?php
/**
 * Each website may need specific functions in some circumstances
 * for encapsulating some complex or heavy works. and this file
 * is for this kind of purposes.
 * 
 * there is only one rule here, name of the functions should start
 * with "wsf" prefix (means website specific function). for example if the name of the function is
 * getUsers, it shoud rename to wsfGetUsers
 * 
 * @package [D]/requirements
 * @author Sina Salek
 */
 


function wsfGetLoginUrl()
{
	$queryString = "?".wsfExcludeQueryStringVars(array('lang'));
	$queryString = wsfRepairUrl($queryString);
	if($queryString == "?")
	{
		$queryString = "?sn=profile";
	}
	$returnTo = base64_encode(urlencode($queryString));
	
	$loginUrl = "?sn=login&returnTo=".$returnTo;
	
	return wsfPrepareUrl($loginUrl);
}

function wsfGetAfterLoginUrl()
{
	//cmfcHtml::printr($_POST);
	//$returnTo = urldecode(base64_decode($_REQUEST['returnTo']));
	$returnTo = $_POST['returnTo'];
	if(!$returnTo)
		$returnTo = "?sn=home";
		
	$returnTo = wsfPrepareUrl($returnTo);

	return $returnTo;
}

function wsfHighlightKeyword($keyword, $string){
	$string = strtolower($string);
	return str_replace($keyword, '<span class="keyword">'.$keyword.'</span>', $string);
}

function wsfCreateLinkForSearch($sectionName, $itemId, $title)
{
	global $_ws,$translation;
	
	$sectionTable = $_ws['sectionsInfo'][$sectionName]['tableInfo'];
	$catSql = "SELECT * FROM ".$sectionTable['tableName'].
				" WHERE ".$sectionTable['columns']['relatedItem']." = ".$itemId.
				" AND ".$sectionTable['columns']['languageId']." = ".$translation->languageInfo['id'];
	$item = cmfcMySql::loadCustom($catSql);
	$catId = $item['category_id'];
	
	switch($sectionName)
	{
		case 'contactUs':
		case 'eOrder':
		case "aboutUs":
		case "ourCustomers":
			$link = wsfPrepareUrl('?sn='.$sectionName.'&lang='.$_GET['lang']);
		break;
		case "news":
		case "products":
		case "standards":
		case "tests":
			$link = wsfPrepareUrl('?sn='.$sectionName.'&pt=full&id='.$itemId.'&lang='.$_GET['lang']);
		break;
		
		default:
			$link = wsfPrepareUrl('?sn='.$sectionName.'&pt=full&id='.$itemId.'&lang='.$_GET['lang']);
		
	}

	return $link;
}
function wsfSiteCounterPlusPlus()
{
	global $_ws;
	$sd = cmfcMySql::loadWithMultiKeys(
		$_ws['physicalTables']['settings']['tableName'],
		array(
			$_ws['physicalTables']['settings']['columns']['key']=>'statsDate'
		)
	);
	$pv = cmfcMySql::loadWithMultiKeys(
		$_ws['physicalTables']['settings']['tableName'],
		array(
			$_ws['physicalTables']['settings']['columns']['key']=>'pageViews'
		)
	);
	if (date('Y-m-d',$sd['value'])==date('Y-m-d'))
	{
		cmfcMySql::update(
			$_ws['physicalTables']['settings']['tableName'],
			$_ws['physicalTables']['settings']['columns']['key'],
			array('value'=>cmfcMySql::asStatement('value+1')),
			'pageViews'
		);
		return ($pv['value']+1);
	}
	else
	{
		cmfcMySql::update(
			$_ws['physicalTables']['settings']['tableName'],
			$_ws['physicalTables']['settings']['columns']['key'],
			array(
				  'value' => 1,
			),
			'pageViews'
		);
		cmfcMySql::update(
			$_ws['physicalTables']['settings']['tableName'],
			$_ws['physicalTables']['settings']['columns']['key'],
			array(
				  'value' => time(),
			),
			'statsDate'
		);
		return(1);
	}
	

}


function wsfItemHitPlusPlus($sn,$itemId)
{
	global $_ws;
	
	$tableInfo = $_ws['sectionsInfo'][$sn]['tableInfo'];
	if ($tableInfo['columns']['hit'])
		cmfcMySql::update(
			$tableInfo['tableName'],
			$tableInfo['columns']['id'],
			array('hit'=>cmfcMySql::asStatement('hit+1')),
			$itemId
		);
}


// Function uploadImage
// last Update 25/Apr/2009
// @author YAAK
// 
function uploadImage($fieldName, $maxSize, $maxW, $fullPath, $relPath, $maxH = null)
{
	
	global $_ws, $translation,$imageManipulator;
		$folder = $relPath;
		$maxlimit = $maxSize;
		$allowed_ext = "jpg,jpeg,gif,png,bmp";
		$match = "";
		$filesize = $_FILES[$fieldName]['size'];
		if($filesize > 0)
		{	
			$filename = strtolower($_FILES[$fieldName]['name']);
			$filename = preg_replace('/\s/', '_', $filename);
		   	if($filesize < 1)
			{ 
				$errorList[] = $translation->getValue('File_Size_Is_Empty');
			}
			if($filesize > $maxlimit){ 
				$errorList[] = $translation->getValue('File_Size_Is_Too_Big');
			}
			
			if(count($errorList)<1)
			{
				$file_ext = preg_split("/\./",$filename);
				$allowed_ext = preg_split("/\,/",$allowed_ext);
				foreach($allowed_ext as $ext)
				{
					if($ext==end($file_ext))
					{
						$match = "1"; // File is allowed
						$NUM = time();
						$front_name = substr($file_ext[0], 0, 15);
						$newfilename = $front_name."_".$NUM.".".end($file_ext);
						$filetype = end($file_ext);
						
						$save = $folder.$newfilename;
						
						if(!file_exists($save))
						{
							$image = $imageManipulator->manipulateImage(
								array(
									'fileName'=>$_FILES[$fieldName]['name'],
									'fileRelativePath'=>$relPath,			
									'width'=> 150,
									'height' => 150,
									'cropPosition' => 'center',
									'mode'=> array(
										'resizeByMinSize',
										'cropToSize'
									),
								)
							);
						}
						else
						{
							$errorList[]= "CANNOT MAKE IMAGE IT ALREADY EXISTS";
						}
					}
				}		
			}
		}else{
			$errorList[]= "NO FILE SELECTED";
		}
		if(!$match){
		   	$errorList[]= "File type isn't allowed: $filename";
		}
		if(sizeof($errorList) == 0){
			return $fullPath.$image;
		}else{
			$eMessage = array();
			for ($x=0; $x<sizeof($errorList); $x++){
				$eMessage[] = $errorList[$x];
			}
		   	return $eMessage;
		}
	}

// Function isThisCatIdOwnedByThisSection
// last Update 11/May/2009
// @author YAAK
// 
function isThisCatIdOwnedByThisSection ($sn , $catId , $tree)
{
	global $_ws;
	
	$thisCatInfo = cmfcMySql::load(
		$_ws['physicalTables']['categories']['tableName'],
		$_ws['physicalTables']['categories']['columns']['id'],
		$catId
	);
	if ($thisCatInfo['internal_name']==$sn)
	{
		return TRUE;
	}
	else
	{
		if ($thisCatInfo['parent_id'] != 0)
			return 	isThisCatIdOwnedByThisSection($sn, $thisCatInfo['parent_id'],$tree);
		else
			return FALSE;
	}
}

// Function wsfDrawCatRec
// last Update 11/May/2009
// @author YAAK
// 
function wsfDrawCatRec($node,$parentInternalName = NULL)
{
		global $topTree,$_ws, $translation;
		
		$link = "#";
		if ($node['link'] and $node['link']!='')
		{
			$link = $_ws['siteInfo']['url'].$node['link'];
		}
		else
		{
			if ($node['internal_name'])
				$link = $_ws['siteInfo']['url'].wsfPrepareUrl('?sn='.$node['internal_name'].'&lang='.$_GET['lang']);
			else
			{
				$link = $_ws['siteInfo']['url'].wsfPrepareUrl('?sn='.$parentInternalName."&catId=".$node['id'].'&lang='.$_GET['lang']);
				$node['internal_name'] = $parentInternalName;
			}
		}
		
		$name = " ";
		$catLang = cmfcMySql::loadWithMultiKeys(
			$_ws['physicalTables']['categoryLanguages']['tableName'],
			array(
				  $_ws['physicalTables']['categoryLanguages']['columns']['languageId']=>$translation->languageInfo['id'],
				  $_ws['physicalTables']['categoryLanguages']['columns']['categoryId']=>$node['id'],
			)
		);
		
		if ($catLang)
			$name = $catLang['name'];
		else
			$name = $node['name'];
		?>
		<li>
			<?php
			if ($node['rgt']-$node['lft']>1)
			{
				?>
				<a class="head" href="<?php echo $link ?>"><?php echo $name?></a>
				<ul>
					<?php
					$subNodes = $topTree->getNodeSubordinates($node['id']);
					foreach ($subNodes as $subNode)
						wsfDrawCatRec($subNode,$node['internal_name']);
					?>
				</ul>
				<?php
			}
			else
			{
				?>
				<a href="<?php echo $link ?>"><?php echo $name?></a>
				<?php
			}
		?>
        </li>
        <?php
	}
/*-------------------------------------------------------------------------------------*/

/**
* @since 2009-05-05
* @author Mojtaba Eskandari
* @desc Make a image Slider with FadeIn and Fade Out.
* @param $items an array of data.( view Example)
* @param $optns options of this function. [fadeIn] and [fadeOut] time in millisecond. default is 1.5 seconds.
* @example 
	$items[] = array( 'img' => '/interface/images/001.jpg', 'url' => '/sn/news/pt/full/id/25');
	$items[] = array( 'img' => '/interface/images/658xs_s.jpg', 'url' => '/sn/news/pt/full/id/36', 'title' => 'This is my title');
	...
	//[url] can be set to NULL value.

	wsfImageSlider( $items, 
		array( 
			'fadeIn'	=> 1000,
			'fadeOut'	=> 2000,
			'wait' 		=> 4000, //wait on each slide.( Ex: 4 Seconds).
			'div' => 'slidImg',
			'loaderImg' => '/interface/images/loader.gif',
			);
*/

function wsfImageSlider( & $items, $optns = NULL)
{
	static $pstfx = 0;
	$pstfx++; //For Multi Use.

	isset( $optns['fadeIn'])	or $optns['fadeIn']		= 2000;
	isset( $optns['fadeOut'])	or $optns['fadeOut']	= 2000;
	isset( $optns['wait'])		or $optns['wait']		= 1000;
	
	isset( $optns['div'])		or $optns['div']		= 'slidImg_'. $pstfx;
	isset( $optns['loaderImg'])	or $optns['loaderImg']	= '/interface/images/loader.gif';

	?>
		<script type="text/javascript">
		//<![CDATA[
			function fadeIn_<?php print( $pstfx)?>(id,mSec,func)
			{
				var speed=Math.round(mSec/100),timer=0;
				for(var i=0;i<=100;i+=Math.round(100/speed),timer++)
				{
					setTimeout('setOpcty_<?php print( $pstfx)?>('+i+',"'+id+'");',timer*speed);
				}
				if(typeof(func)=='function')setTimeout(func,mSec);
			}

			function fadeOut_<?php print( $pstfx)?>(id,mSec,func)
			{
				var speed=Math.round(mSec/100),timer=0;
				for(var i=100;i>=0;i-=Math.round(100/speed),timer++)
				{
					setTimeout('setOpcty_<?php print( $pstfx)?>('+i+',"'+id+'");',timer*speed);
				}
				if(typeof(func)=='function')setTimeout(func,mSec);
			}

			function setOpcty_<?php print( $pstfx)?>(opc, id) {
				var object = document.getElementById(id).style;
				object.opacity = (opc / 100);
				object.MozOpacity = (opc / 100);
				object.KhtmlOpacity = (opc / 100);
				object.filter = "alpha(opacity=" + opc + ")";
				
			}
	
			var Img_<?php print( $pstfx)?> = new Array();
			function addImg_<?php print( $pstfx)?>( src, url,title)
			{
				var i = Img_<?php print( $pstfx)?>.length;
				Img_<?php print( $pstfx)?>[i] = new Image();
				Img_<?php print( $pstfx)?>[i].src = src;
				Img_<?php print( $pstfx)?>[i].alt = url;
				Img_<?php print( $pstfx)?>[i].title = title;
			}
			var thisImg_<?php print( $pstfx)?> = -1;
			function slidShw_<?php print( $pstfx)?>()
			{
				if( thisImg_<?php print( $pstfx)?> == Img_<?php print( $pstfx)?>.length -1)thisImg_<?php print( $pstfx)?> = -1;
				if( !Img_<?php print( $pstfx)?>[ thisImg_<?php print( $pstfx)?> +1].complete)
				{
					setTimeout( 'slidShw_<?php print( $pstfx)?>()', 400);
					return;
				}
				var D=window.document, nueObj=D.getElementById( '<?php print( $optns['div']);?>_new'), obj=D.getElementById( '<?php print( $optns['div']);?>');
				++thisImg_<?php print( $pstfx)?>;
				nueObj.innerHTML = Img_<?php print( $pstfx)?>[thisImg_<?php print( $pstfx)?>].alt ? '<a href="'+Img_<?php print( $pstfx)?>[thisImg_<?php print( $pstfx)?>].alt+'" title="'+Img_<?php print( $pstfx)?>[thisImg_<?php print( $pstfx)?>].title+'"><img src="'+Img_<?php print( $pstfx)?>[thisImg_<?php print( $pstfx)?>].src+'" /></a>' : '<img src="'+Img_<?php print( $pstfx)?>[thisImg_<?php print( $pstfx)?>].src+'" title="'+Img_<?php print( $pstfx)?>[thisImg_<?php print( $pstfx)?>].title+'" />';
				fadeOut_<?php print( $pstfx)?>('<?php print( $optns['div']);?>',<?php print( $optns['fadeOut']);?>);
				fadeIn_<?php print( $pstfx)?>('<?php print( $optns['div']);?>_new',<?php print( $optns['fadeIn']);?>, function(){
					obj.id = '<?php print( $optns['div']);?>_new';
					nueObj.id = '<?php print( $optns['div']);?>';
					setTimeout( 'slidShw_<?php print( $pstfx)?>()', <?php print( $optns['wait']);?>);
				});

			}/*End of function slidShw_<?php print( $pstfx)?>();*/
			
			<?php
				foreach( $items as $item)
				{
					if( $item['img'])
					{
						print( "addImg_$pstfx('{$item['img']}','{$item['url']}','{$item['title']}');");
					}

				}//End of foreach( $items as $item);
			?>
			//]]>
		</script>
		<div class="imgSlider" style="position:relative;">
			<div id="<?php print( $optns['div']);?>" style="position:absolute;">
				<img src="<?php print( $optns['loaderImg']);?>" alt="Loading ..."  class="loaderImg" />
			</div>
			<div id="<?php print( $optns['div']);?>_new" style="z-index:5;position:absolute;"></div>
		</div>
		<script type="text/javascript">slidShw_<?php print( $pstfx)?>();</script>
	<?php

}//End of function wsfImageSlider( & $items, $optns = NULL);

// shuffle array
function wsfShuffleArray($array)
{
    $count = count($array);
    $indi = range(0,$count-1);
    shuffle($indi);
    $newarray = array($count);
    $i = 0;
    foreach ($indi as $index)
    {
        $newarray[$i] = $array[$index];
        $i++;
    }
    return $newarray;
}
// Function wsfDrawProductCatsRec
// last Update 16/Nov/2009
// @author YAAK
// 
function wsfDrawProductCatsRec($node,$parentInternalName = NULL)
{
		global $topTree,$_ws, $translation, $imageManipulator;
		
		$link = "#";
		if ($node['link'] and $node['link']!='')
		{
			$link = $_ws['siteInfo']['url'].$node['link'];
		}
		else
		{
			if ($node['internal_name'])
				$link = $_ws['siteInfo']['url'].wsfPrepareUrl('?sn='.$node['internal_name'].'&lang='.$_GET['lang']);
			else
			{
				$link = $_ws['siteInfo']['url'].wsfPrepareUrl('?sn='.$parentInternalName."&catId=".$node['id'].'&lang='.$_GET['lang']);
				$node['internal_name'] = $parentInternalName;
			}
		}
		
		$name = " ";
		$photo = FALSE;
		$catLang = cmfcMySql::loadWithMultiKeys(
			$_ws['physicalTables']['categoryLanguages']['tableName'],
			array(
				  $_ws['physicalTables']['categoryLanguages']['columns']['languageId']=>$translation->languageInfo['id'],
				  $_ws['physicalTables']['categoryLanguages']['columns']['categoryId']=>$node['id'],
			)
		);
		
		if ($catLang)
		{
			$name = $catLang['name'];
			$photo = $catLang['photo_filename'];
		}
		else
			$name = $node['name'];
		?>
		<li>
			<?php
			if ($node['rgt']-$node['lft']>1)
			{
				/*
				if ($photo)
				{
					$result = $imageManipulator->getAsImageTag(array (
						'fileName' => $photo,
						'fileRelativePath' => $_ws['directoriesInfo']['categoriesFolderRPath'],
						'version' => 2,
						'actions' => array (
							 array (
								'subActions' => array (
									 array (
										'name' => 'resizeSmart',
										'parameters' => array (
											'width' => array (
												'min' => 50,
												'max' => 50,
												'zoomInIfRequire' => true,
												'ignoreAspectRatio' => false,
											),
											'height' => array (
												'min' => 50,
												'max' => 50,
												'zoomInIfRequire' => true,
												'ignoreAspectRatio' => false,
											),
											'priority' => array (
												 'width',
												 'height',
											),
										),
									),
									 array (
										'name' => 'crop',
										'parameters' => array (
											'position' => 'center',
											'width' => '50',
											'height' => '50',
										),
									),
								),
							),
						),
					));	
					echo $result;
				}
				*/
				?>
				<a class="head" href="<?php echo $link ?>">
				<?php echo $name?>
                </a>
				<ul>
					<?php
					$subNodes = $topTree->getNodeSubordinates($node['id']);
					foreach ($subNodes as $subNode)
						wsfDrawProductCatsRec($subNode,$node['internal_name']);
					?>
				</ul>
				<?php
			}
			else
			{
				/*
				if ($photo)
				{
					$result = $imageManipulator->getAsImageTag(array (
						'fileName' => $photo,
						'fileRelativePath' => $_ws['directoriesInfo']['categoriesFolderRPath'],
						'version' => 2,
						'actions' => array (
							 array (
								'subActions' => array (
									 array (
										'name' => 'resizeSmart',
										'parameters' => array (
											'width' => array (
												'min' => 50,
												'max' => 50,
												'zoomInIfRequire' => true,
												'ignoreAspectRatio' => false,
											),
											'height' => array (
												'min' => 50,
												'max' => 50,
												'zoomInIfRequire' => true,
												'ignoreAspectRatio' => false,
											),
											'priority' => array (
												 'width',
												 'height',
											),
										),
									),
									 array (
										'name' => 'crop',
										'parameters' => array (
											'position' => 'center',
											'width' => '50',
											'height' => '50',
										),
									),
								),
							),
						),
					));	
					echo $result;
				}
				*/
				?>
				<a href="<?php echo $link ?>"><?php echo $name?></a>
				<?php
			}
		?>
        </li>
        <?php
	}
?>