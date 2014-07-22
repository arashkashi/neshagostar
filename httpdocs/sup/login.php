<?php
ini_set('display_errors',0);
//$_GET['lang'] = $_REQUEST['lang'] = 'fa';
require('requirements/preparing.inc.php');

#--(Begin)-->for login form
if ($_REQUEST['submit_login']) {
	if ($_POST['remember_me']=='true') $userSystem->setRememberUserEnabled(true);
	$userSystem->setOption('autoRedirectEnabled',false);
	$result=$userSystem->login($_POST['username'],$_POST['password']);
	
	if (PEAR::isError($result)) 
	{
		$message=$result->getMessage();
	}
	else
	{
		$pagesInfo = $userSystem->getOption("pagesInfo");
		$userSystem->setOption('autoRedirectEnabled',true);
		$userSystem->redirect($pagesInfo['afterLogin']);
	}
	/*
	else 
	{
		if(!wsfIsAccessible("cp_administration"))
		{
			$userSystem->logout();
		}
		else
		{
			$pagesInfo = $userSystem->getOption("pagesInfo");
			$userSystem->setOption('autoRedirectEnabled',true);
			$userSystem->redirect($pagesInfo['afterLogin']);
		}
		//$userInfo=$userSystem->getUserInfo();
		//$sqlQuery="Update $userSystem->_tableName SET last_login_datetime=login_datetime, login_datetime=NOW() WHERE id=$userSystem->cvId";
		//cmfcMySql::exec($sqlQuery);
	}
	/* */
}
#--(End)-->for login form
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $translation->getValue('siteTitle')?> » <?php echo $translation->getValue('controlPanel')?></title>

<style>
	body , .input , .button{
		font-family:Tahoma, Verdana, Arial, sans-serif;
		font-size:11px;
		color:#333333;
	}
	
	a:link , a:visited {
		color:#990000;
		text-decoration:none;
	}
	
	
	a:hover , a:active{
		color:#FF9966;
	
	}
</style>


</head>

<body>
<table id="wrapper" dir="<?php echo $translation->languageInfo['direction']?>" width="1000" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
  <tr>
    <td>
	
	<br />
	<br />
	
	<table width="300" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>

        <td align="center" valign="top">
          <img src="/interface/images/logo<?php echo $translation->languageInfo['dbBigLang']?>.gif" />
		</td>
      </tr>

      <tr>
        <td align="<?php echo $translation->languageInfo['!align']?>" style="padding-left:50px; padding-right:50px;" valign="top" ><?php echo wsfGetDateTime('d M Y')?></td>
      </tr>
      <tr>
      	<td align="<?php echo $translation->languageInfo['align']?>" valign="top">
      		<?php echo ($message)?'<br/><span style="color:red">'.$message.'</span>':''?>
      	</td>
      </tr>
      <tr>

        <td align="center">
		<form action="?" method="post">
		<table width="270"  border="0" cellspacing="0" cellpadding="0">
            <tr bgcolor="#E2E1E1">
              <td height="2"> </td>
            </tr>
            <tr bgcolor="#A9A9A9">
              <td height="3"> </td>
            </tr>
            <tr>
              <td>
			  	<table width="100%"  border="0" cellpadding="1" cellspacing="0" bgcolor="#939393">
                  <tr>
                    <td><table align="<?php echo $translation->languageInfo['align']?>" width="100%"  border="0" cellspacing="0" cellpadding="3">
                        <tr bgcolor="#F8F8F8">
						<td width="45%" valign="top" ><?php echo $translation->getValue('username')?>: </td>
                          <td width="55%" valign="top">
                            <input type="text" name="username" style="text-align:left" id="username" class="input" value="" size="25" dir="ltr" />                          </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                          <td valign="top"><?php echo $translation->getValue('password')?>: </td>
                          <td valign="top">
                            <input type="password" name="password" style="text-align:left" id="password" class="input" value="" size="25" dir="ltr" />                          </td>
                        </tr>
                        <tr bgcolor="#F8F8F8">
                          <td >IP address: </td>
                          <td valign="top"><?php echo $_SERVER['REMOTE_ADDR']?></td>

                        </tr>
                        <tr align="center" bgcolor="#FFFFFF">
                          <td colspan="2">
                            <input name="submit_login" type="submit" class="button" value=" <?php echo $translation->getValue('login')?> " />
						  </td>
                        </tr>
                    </table></td>
                  </tr>
              </table></td>

            </tr>
            <tr bgcolor="#A9A9A9">
              <td height="3"> </td>
            </tr>
            <tr bgcolor="#E2E1E1">
              <td height="2"> </td>
            </tr>
        </table>
		</form>
		</td>
      </tr>

    </table>
	
	
	<br />
	<br />	
	
	
	</td>
  </tr>

  <tr>
    <td>
		<div style=" margin-bottom:10px; margin-right:10px; margin-left:10px; border-top:1px solid #999999; padding-top:5px; text-align:center;">
		 All rights reserved <strong><?php echo $translation->getValue("siteTitle");?></strong>
		<?php /*?><br />
		Designed By:
			<a href="http://design.persiantools.com/">
				PersianTools
			</a><?php */?>
		</div>
	</td>
  </tr>

</table>
<?php
//$translation->printLog();
?>
</body>
</html>