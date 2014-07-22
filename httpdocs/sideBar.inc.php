<div class="sideBar">
	<?php /* ?>
    <h3 class="title"><?php echo wsfGetValue('customerLogin')?></h3>
    <div class="sideBox">
    	<?php
		if(!$userSystem->isLoggedIn(false) and $translation->languageInfo['sName']=='fa')
		{
			?><div align="center"><?php
			if($loginMessage)
				echo $loginMessage;
			?>
            </div>
			<form action="" method="post">
				<table class="customersLogin">
					<tr>
						<td><label><?php echo $translation->getValue('userName')?> :</label></td>
						<td><input dir="ltr" name="username" id="username" type="text" class="input" /></td>
					</tr>
					<tr>
						<td><label><?php echo $translation->getValue('password')?> :</label></td>
						<td><input dir="ltr" name="password" id="password" type="password" class="input" /></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input name="submit_login" type="submit" value="<?php echo $translation->getValue('submit')?>" onclick="checkLogin($('#username').attr('value'), $('#password').attr('value')); return false;" />
							<input type="hidden" name="submit_login" value="1" />
							<input type="hidden" name="returnTo" value="<?php echo $_ws['siteInfo']['url'];?>" />
							<a href="<?php echo wsfPrepareUrl('?sn=new&pt=full&id='.$row['relatedItem']."&lang=".$_GET['lang'])?>">بازیابی کلمه عبور</a>
							<div id="loginMessages"></div>
						</td>
					</tr>
				</table>
			</form>
			<?php
		}
		?>
    </div>
    <?php
	/* */
	$recentNewsQuery = "SELECT * FROM ".$_ws['physicalTables']['news']['tableName'].
						" WHERE ".$_ws['physicalTables']['news']['columns']['languageId']." = ".$translation->languageInfo['id'].
						" AND ".$_ws['physicalTables']['news']['columns']['archive']." = 0".
						" ORDER BY ".$_ws['physicalTables']['news']['columns']['publishDatetime'].
						" LIMIT 3";
	$recentNews = cmfcMySql::getRowsCustom($recentNewsQuery);
	if ($recentNews)
	{
		?>
        <h3 class="title"><?php echo wsfGetValue('recentNews');?></h3>
        <div class="sideBox">
        	<?php
			foreach ($recentNews as $key=>$row)
			{
				$row = cmfcMySql::convertColumnNames($row,$_ws['physicalTables']['news']['columns']);
				?>
				<div class="sideNews">
					<span><?php echo wsfGetDateTime('d M Y', $row['publishDatetime'], $translation->languageInfo['shortName']);?></span>
					<a href="<?php echo wsfPrepareUrl('?sn=news&pt=full&id='.$row['relatedItem'].'&lang='.$_GET['lang']);?>"><?php echo cmfcString::briefText($row['title'],80);?></a>
				</div>
				<?php
			}
			?>
        </div>
        <?php
	}
	?>
</div>