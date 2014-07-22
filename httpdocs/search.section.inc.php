<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$translation->languageInfo['sName'])."'>".$translation->getValue('home')."</a>"
						." &raquo; ".
						$translation->getValue($_ws['sectionInfo']['name']);
						
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
}
else
{
	?>
	<script language="javascript" type="text/javascript">
		function ShowHideDetails(thisPtr, num){
			detailsElm = document.getElementById('details-' + num );
			//alert(thisPtr.title);
			if (thisPtr.title == 'open'){
				detailsElm.style.display = 'block';
				thisPtr.src = '<?php echo $_ws['siteInfo']['url']?>interface/images/min_on.gif';
				thisPtr.title = 'close';
			}
			else{
				detailsElm.style.display = 'none';
				thisPtr.src = '<?php echo $_ws['siteInfo']['url']?>interface/images/plus_on.gif';
				thisPtr.title = 'open';
			}
		}
	</script>	
	<h1 class="title"><?php echo wsfGetValue($_GET['sn'])?></h1>	
    <div class="body">
		<?php
        $search=cmfcSearch::factory(
            'dbCombinedV1',
            array(
                'titleMaxChars'=>40,
                'images'=>array(
                'openIconSrc'=>'interface/images/plus_on.gif',
                'closeIconSrc'=>'interface/images/min_on.gif'
                )
            )
        );
        //$search->addCommandHandler('getLink','getResultRowLink');

        if ($_REQUEST['search']['sectionNames'] == array('') || empty($_REQUEST['search']['sectionNames']))
            $_REQUEST['search']['sectionNames'] = array();
            
        if($_REQUEST['search']['sectionName'])
            $_REQUEST['search']['sectionNames'] = array($_REQUEST['search']['sectionName']);
            
        $search->setOption('sectionsInfo',$_ws['sectionsInfo']);
        
        if (utf8::strlen($_REQUEST['search']['keyword']) < 3 )
        {
            echo wsfGetValue('VN_minimumSearchLength');
        }
        else
        {
			//if ($translation->languageInfo['sName']=='en')

            $sqlQuery=$search->getSearchQuery($_REQUEST['search']['keyword'], 'and',  $_REQUEST['search']['sectionNames']);
            //echo $sqlQuery;
            
            $listLimit=20;
            $paging=cmfcPaging::factory(
                'dbV2',
                array(
                    'total'=>null,
                    'limit'=>$listLimit,
                    'sqlQuery'=>$sqlQuery,
                    'wordNext'=>wsfGetValue('VN_next'),
                    'wordPrev'=>wsfGetValue('VN_prev'),
                    'link'=>'?'.cmfcUrl::excludeQueryStringVars(array('sectionName','pageType'),'get'),
                    'sortingEnabled'=>false,
                    //'staticLinkEnabled'=>true,
                    //'sortBy'=>'rate',
                    //'sortType'=>'DESC',
                    'colnId'=>'id',
                )
            );
            $sqlQuery=$paging->getPreparedSqlQuery();
            $rows=cmfcMySql::getRowsCustom($sqlQuery);
            //cmfcHtml::printr($sqlQuery);
            //$search->printDefaultStyles();
            //$search->printDefaultScripts();
            //$search->printResults($rows);
            if ($rows)
			{
                ?><p><?php echo count($rows)?> <?php echo wsfGetValue('VN_resultFound')	?>:</p><?php
                foreach ($rows as $num => $row)
				{
                    ?>
                    <div class="searchResults" dir="<?php echo $translation->languageInfo['direction'];  ?>">
                        <img src="/interface/images/plus_on.gif" alt="open"  title="open" onclick="ShowHideDetails(this, '<?php echo $num?>')" />&nbsp;
                        <a href="<?php echo wsfCreateLinkForSearch($row['sectionName'], $row['id'], $row['title'])?>">
                        <?php echo $row['title']?>
                        </a> 
                        <span dir="<?php echo $translation->languageInfo['direction'];  ?>" > ( <?php echo round($row['rate'])?>  <?php echo wsfGetValue('VN_reason')?> / <?php echo wsfGetValue($row['sectionName'])?> ) </span>
                        
                       
                        
                        <div style="display:none" class="details" id="details-<?php echo $num?>">
                        <?php echo  wsfHighlightKeyword($_REQUEST['search']['keyword'], cmfcString::briefText(strip_tags($row['fullContent']), 350 ) )?>
                        </div>
                    </div>
                    <?php
                }
            }
            else
			{
				?>
				<?php echo wsfGetValue('VN_yourSearch')?> -<strong> <?php echo $_REQUEST['search']['keyword']?> </strong>- <?php echo wsfGetValue('VN_noResultFound')?>
				<?php
            }
        }
		if ($paging && $paging->getTotalPages()>1) 
		{
		?>
			<table class="paging-table" border="0" cellspacing="1" cellpadding="0" style="font-family:tahoma; font-size:11px;">
				<tr>
					<td class="paging-body"> <?php echo wsfGetValue('VN_page')?>
						<?php echo $paging->getPageNumber()?>
						<?php echo wsfGetValue('VN_from')?>
						<?php echo $paging->getTotalPages()?>
						|
						<?php echo $paging->show('nInCenterWithJumps',array())?>
					</td>
				</tr>
			</table>
		<?php
		}
		?>
    </div>
<?php
}
?>