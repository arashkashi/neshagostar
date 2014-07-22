<div class="menuBox">
    <div class="left">
        <div class="right">
            <div class="menu">
                <a href="<?php echo wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('home');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=products&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('products');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=aboutUs&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('aboutUs');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=licences&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('licences');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=ourCustomers&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('ourCustomers');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=qualityControl&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('qualityControl');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=faq&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('faq');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=eOrder&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('eOrder');?></a>
                <a href="<?php echo wsfPrepareUrl('?sn=contactUs&lang='.$_GET['lang'])?>"><?php echo wsfGetValue('contactUs');?></a>
            </div>
            
            
            <div class="searchBox">
                <div class="searchBody">
                	<form style="margin:0px; padding:0px;display:inline" action="/" method="get">
                    	<input name="sn" value="search" type="hidden" />
                        <input name="search[keyword]" type="text" class="input" value="<?php echo wsfGetValue('search')?>" onfocus="if(this.value=='<?php echo wsfGetValue('search')?>')this.value='';" onblur="if(this.value=='')this.value='<?php echo wsfGetValue('search')?>';" />
                        <input name="" type="submit" class="btn" />
                        
                        <input class="input" name="lang" value="<?php echo $_GET['lang']?>" type="hidden"/>
                    </form>
                </div>
                <?php /*?><div class="search">
                    <form style="margin:0px; padding:0px;display:inline" action="/" method="get">
                        <input name="sn" value="search" type="hidden" />
                        <input class="input" name="lang" value="<?php echo $translation->languageInfo['sName']?>" type="hidden"/>
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <?php
                                    if(!$userSystem->isLoggedIn(false) and $translation->languageInfo['sName']=='fa')
                                    {
                                        ?><a href='#TB_inline?height=155&amp;width=300&amp;inlineId=loginBox' class="thickbox"><img src="interface/images/login<?php echo $translation->languageInfo['dbBigLang'];?>.gif" border="0" alt="login" /></a><?php
                                    }?>
                              </td>
                                                       
                              <td ><?php echo $translation->getValue('search')?> :</td>
                                <td><input name="search[keyword]" type="text" class="input" size="25" /></td>
                                <td><input name="" style="border:0px;" type="image" src="interface/images/go<?php echo $translation->languageInfo['dbBigLang'];?>.gif" /></td>
                            </tr>
                        </table>
                    </form>
                </div><?php */?>
            </div>
            
        </div>
    </div>
</div>