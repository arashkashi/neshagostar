<?php
if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$eOrderPage = cmfcMySql::loadWithMultiKeys(
		$sectionInfo['pagesTeble']['tableName'],
		array(
			$sectionInfo['pagesTable']['columns']['languageId']=>$translation->languageInfo['id'],
			$sectionInfo['pagesTable']['columns']['internalName']=>$_GET['sn']
		)
	);
	$excludes = array();
	$products = cmfcMySql::getRowsWithMultiKeys(
		$sectionInfo['productsTable']['tableName'],
		array(
			$sectionInfo['productsTable']['columns']['languageId']=>$translation->languageInfo['id']
		)
	);
	
	$fieldsValidationInfo=array(
		"resumeInfo[personalInfo][receipantFullName]"=>array(
			'name'=>"resumeInfo[personalInfo][receipantFullName]",
			'headName'=>'receipantFullName',
			'title'=>$translation->getValue('receipantFullName'),
			'type'=>'string',
			'param'=>array(
				'notEmpty'=>true
			)
		),
		"resumeInfo[personalInfo][receipantTel]"=>array(
			'name'=>"resumeInfo[personalInfo][receipantTel]",
			'headName'=>'receipantTel',
			'title'=>$translation->getValue('receipantTel'),
			'type'=>'string',
			'param'=>array(
				'notEmpty'=>true
			)
		),
		"resumeInfo[orderDetails][orderDescription]"=>array(
			'name'=>"resumeInfo[orderDetails][orderDescription]",
			'headName'=>'orderDescription',
			'title'=>$translation->getValue('orderDescription'),
			'type'=>'string',
			'param'=>array(
				'notEmpty'=>true
			)
		),
		
		"resumeInfo[security][code]"=>array(
			'name'=>"resumeInfo[security][code]",
			'headName'=>'code',
			'title'=>$translation->getValue('Security_Code'),
			'type'=>'string',
			'param'=>array(
				'notEmpty'=>true
			)
		),
	);
	$validation->setOption('formName', 'myForm');
	$validation->setOption('defaultStylesEnabled', false);
	//$validation->setOption('displayMethod', 'nearFields');
	/*$validation->setOption(
		'displayMethodOptions',
		array(
			'id'=>'messageBoard',
			'backgroundColor'=>'#FFC0C0'
		)
	);*/
			
	$validation->setOption('fieldsInfo',$fieldsValidationInfo);
		
	$pageBreadcrumb	=	"<a href='".wsfPrepareUrl('?sn=home&lang='.$_GET['lang'])."'>".$translation->getValue('home')."</a>"
						." » ".
						$translation->getValue($_ws['sectionInfo']['name']);
	
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
	//$pageDescription = "";
	//$pageKeyWords = "";
	
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	$successfullSubmit = FALSE;
	$messages = array();
	$error = FALSE;
	$emailHtml = NULL;
	
	if (isset($_POST['submit_send']))
	{
		$_resumeInfo = &$_POST['resumeInfo'];
		//cmfcHtml::printr($_resumeInfo);
		/* 
			Validation 
		*/
		if (is_array($fieldsValidationInfo))
			$validateResult=$validation->validate($_POST);
	
		if (strtolower($_SESSION['niceCaptchaCode']) != strtolower($_POST['resumeInfo']['security']['code']) ){
			$validateResult[] = PEAR::raiseError($translation->getValue('Security_Code_Is_Wrong'));
		}
		unset($_SESSION['niceCaptchaCode']);
		
		
		/* End of Validation
		*/
		$result = array();
		if (empty($validateResult)) 
		{
			// in this Part we check if there is any order with same data 
			// begin of duplicate find
			$superString = NULL;
			foreach ($_resumeInfo as $part=>$rows)
			{
				if ($part == 'personalInfo')
				{
					$superString .= $rows['receipantFullName'].$rows['receipantTel'].$rows['receipantAddress'].$rows['receipantEmail'];
				}
				if ($part == 'orderDetails')
				{
					foreach ($rows as $key=>$row)
					{
						$superString .= $row['productId'].$row['orderDescription'];
					}
				}
			}
			if ($superString!=NULL)
			{
				$orderHash = md5($superString);
				$eQuery = "SELECT * FROM ".$_ws['sectionInfo']['tableInfo']['tableName'].
							" WHERE ".$_ws['sectionInfo']['tableInfo']['columns']['orderHash']." = '".$orderHash."'".
							" AND ".$_ws['sectionInfo']['tableInfo']['columns']['insertDatetime']." > '".date("Y-m-d H:i:s",(time()-86400))."'";
				$existant = cmfcMySql::loadCustom($eQuery);

			}
			else
			{
				$orderHash = NULL;
				$existant = FALSE;
			}
			// end of duplicate find
			
			$emailHtml .= '<table style="direction: rtl; font-family: tahoma; font-size: 9pt;"><tbody>';
			foreach ($_resumeInfo as $part=>$rows)
			{
				if (substr(phpversion(),0,1)>4) // if PHP version is 5+
					date_default_timezone_set('Asia/Tehran'); // Sets the default time zone to Iran 
				$submitTime = time(); // form submit time is recorder here in order to make all insertDatetime fields identical in all related tables
				
				if ($part=='personalInfo')
				{
					$row = $rows;
					if ( ! $existant )
					{
						// First inserts info into order table
						$row['languageId'] = $translation->languageInfo['id'];
						$row['insertDatetime'] = date('Y-m-d H:i:s',$submitTime);
						$row['orderHash'] = $orderHash;

						$row = cmfcMySql::convertColumnNames($row,$_ws['sectionInfo']['tableInfo']['columns']);
						//
						$recruitInserted = cmfcMySql::insert($_ws['sectionInfo']['tableInfo']['tableName'],	$row);
						
						if (PEAR::isError($recruitInserted))
						{
							$messages['errors'][] = $translation->getValue('errorOccured');
							$error = mysql_error();
						}
						else
						{
							$recruitId = cmfcMySql::insertId();
							$recruitInserted = TRUE;
							// then uses inserted record's id to insert other form item into related tables
							//$row = cmfcMySql::convertColumnNames($row,$_ws['sectionInfo']['tableInfo']['columns']);
							
							$emailHtml .= '<tr><td colspan="5" style="background:#EEE;padding:3px;"><b>'.$translation->getValue('cutomerInformation').'</b></td></tr>';
							$emailHtml .= '<tr><td colspan="1">'.$translation->getValue("agencyName").'</td><td colspan="4">'.$userInfo['full_name'].'</td></tr>';
							$emailHtml .= '<tr><td colspan="1">'.$translation->getValue("insertDatetime").'</td><td colspan="4">'.wsfGetDateTime('d M Y  - H:i:s',$row['insertDatetime']).'</td></tr>';
							//echo $emailHtml;
						}
						
					}
					else
					{
						$error = 'record exists';
						$messages['errors'][] = $translation->getValue('anOrderWithIdenticalRequestsExistsForToday');
					}
				}
				if ($part == 'orderDetails')
				{
					if ($recruitInserted and !$error)
					{
						$rows['relatedOrder'] = $recruitId; // should be obtained in previous DB insert action
						$rows['insertDatetime'] = date('Y-m-d H:i:s',$submitTime);
						$rows = cmfcMySql::convertColumnNames($rows,$_ws['sectionInfo'][$part.'Table']['columns']);
						$result = cmfcMySql::insert(
							$_ws['sectionInfo'][$part.'Table']['tableName'],
							$rows
						);
						
						$error = mysql_error();
						if (PEAR::isError($result))
						{
							$messages['errors'][] = $translation->getValue('errorOccured');
						}
						else
						{
							$rows = cmfcMySql::convertColumnNames($rows,$_ws['sectionInfo'][$part.'Table']['columns']);
							switch ($part)
							{
								case 'orderDetails':
									$prdct = cmfcMySql::loadWithMultiKeys(
										$_ws['sectionInfo']['productsTable']['tableName'],
										array(
											$_ws['sectionInfo']['productsTable']['columns']['relatedItem'] => $rows['productId'],
											$_ws['sectionInfo']['productsTable']['columns']['languageId'] => $translation->languageInfo['id'],
										)
									);
									
									$emailHtml .= '<tr><td colspan="5" style="background:#EEE;padding:3px;"><b>'.$translation->getValue('requests').'</b></td></tr>';
									$emailHtml .= '
									<tr>
										<td colspan="1">'.$key.'</td>
										<td colspan="4">
										<table style="font-family:tahoma; font-size:9pt; width:100%;">
											<thead>
												<tr>
												<td style="font-weight:bold;">'.$translation->getValue('product').'</td>
												<td style="font-weight:bold;">'.$translation->getValue('capacity').'</td>
												<td style="font-weight:bold;">'.$translation->getValue('currentMonthSalePre').'</td>
												<td style="font-weight:bold;">'.$translation->getValue('lastMonthsSaleBalance').'</td>
												<td style="font-weight:bold;">'.$translation->getValue('paymentMethod').'</td>
												<td style="font-weight:bold;">'.$translation->getValue('itemCount').'</td>
												<td style="font-weight:bold;">'.$translation->getValue('requestedOptions').'</td>
												</tr>
											</thead>
											<tbody>
												<tr>
												<td style="text-align:center;">'.$prdct['title'].'</td>
												<td style="text-align:center;">'.$rows['capacity'].'</td>
												<td style="text-align:center;">'.$rows['currentMonthSalePre'].'</td>
												<td style="text-align:center;">'.$rows['lastMonthsSaleBalance'].'</td>
												<td style="text-align:center;">'.
												cmfcMySql::getVirtualColumnValue( 
													$rows['paymentMethod'], 
													$_ws['virtualTables']['paymentMethods']['rows'], 
													$_ws['virtualTables']['paymentMethods']['columns']['id'], 
													$_ws['virtualTables']['paymentMethods']['columns']['name']
												).
												'</td>
												<td style="text-align:center;">'.$rows['itemCount'].'</td>
												<td style="text-align:center;">'.$rows['requestedOptions'].'</td>
												</tr>
											</tbody>
										</table>
										</td>
									</tr>';
								break;
							}
						}
					}
				}
				
			}
			$emailHtml .= '</tbody></table>';
			if (!isset($messages['errors']))
			{
				$successfullSubmit = TRUE;
				$messages['messages'][] = $translation->getValue('yourOrderHasBeenSubmittedSuccessfully');
				// Here an email should be sent to admin and user
				$receiverEmail = cmfcMySql::load(
					$_ws['physicalTables']['settings']['tableName'],
					$_ws['physicalTables']['settings']['columns']['key'],
					'ordersEmail'
				);
				$receiverEmail = $receiverEmail['value'];
				if ($emailTemplate->loadByInternalName('order')!==false)
				{
					$replacements=array(
						'%name%'=>$_columns['name'],
						'%body%'=>$emailHtml,
						'%title%'=>$_columns['title'],
						'%email%'=>$_columns['email'],
						'%subject%'=>$_columns['subject'],
						'%inline_subject%' => 'Order',
						'%site_url%' => $_ws['siteInfo']['url'],
						'%header%' => '',
					);
					$emailTemplate->process($replacements);
					
					$emailSender->addAddress($receiverEmail);
					
					$emailSender->addAddress($userInfo['email']);
					
					$emailSender->Subject=$emailTemplate->getSubject();
					$emailSender->Body=$emailTemplate->getBody();
					$emailSender->FromName=$_ws['siteInfo']['titleEn']." : Orders";
					$emailSender->From = $_ws['emailsInfo']['info'];
					$result = $emailSender->send();
				}
			}
			
		}
		else
		{
			//cmfcHtml::printr($validateResult);
			foreach ($validateResult as $v)
			{
				$messages['errors'][] = $v->message;
			}
			unset ($v);
		}
	}
	?>
    <h1 class="title"><?php echo $translation->getValue('eOrder')?></h1>
	<div class="page">
        <?php
		wsfPrintMessages($messages);

		if ( $successfullSubmit === FALSE)
		{
			if (!empty($fieldsValidationInfo)) 
			{
				$validation->setOption('optimizerObj',&$optimizer);
				$validation->printJsClass();
				$validation->printJsInstance();
			}
			?>
			<div class="eOrder">
        	<form action="" method="post" name="myForm" id="myForm" enctype="multipart/form-data">
                <fieldset><legend><?php echo $translation->getValue('yourContactInformtion')?></legend>
                <table id="personalInfo" class="eOrderTbl">
                    <tr>
                        <td class="lblForm"><label for="personalInfo_receipantFullName"><?php echo $translation->getValue('receipantFullName');?></label></td>
                        <td class="fldForm">
                        	<input  class="input" type="text" name="resumeInfo[personalInfo][receipantFullName]" id="personalInfo_receipantFullName" value="<?php echo $_POST['resumeInfo']['personalInfo']['receipantFullName']?>" size="20" lang="<?php echo $translation->languageInfo['sName']?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="lblForm"><label for="personalInfo_receipantTel"><?php echo $translation->getValue('receipantTel');?></label></td>
                        <td class="fldForm">
							<input  class="input" type="text" name="resumeInfo[personalInfo][receipantTel]" id="personalInfo_receipantTel" value="<?php echo $_POST['resumeInfo']['personalInfo']['receipantTel']?>" size="20" dir="ltr" />
                        </td>
                    </tr>
                    <tr>
                        <td class="lblForm"><label for="personalInfo_receipantAddress"><?php echo $translation->getValue('receipantAddress');?></label></td>
                        <td class="fldForm">
                        	<textarea name="resumeInfo[personalInfo][receipantAddress]" id="personalInfo_receipantAddress" cols="40" lang="<?php echo $translation->languageInfo['sName']?>"><?php echo $_POST['resumeInfo']['personalInfo']['receipantAddress']?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="lblForm"><label for="personalInfo_receipantEmail"><?php echo $translation->getValue('receipantEmail');?></label></td>
                        <td class="fldForm">
                        	<input  class="input" type="text" name="resumeInfo[personalInfo][receipantEmail]" id="personalInfo_receipantEmail" value="<?php echo $_POST['resumeInfo']['personalInfo']['receipantEmail']?>" size="20" dir="ltr" />
                        </td>
                    </tr>
                </table>
                </fieldset> 
                
                <br />
                
                <fieldset><legend><?php echo $translation->getValue('orderInformation')?></legend>
                <table id="personalInfo" class="eOrderTbl">
                    <tr>
                        <td class="lblForm"><label for="orderDetails_productId"><?php echo $translation->getValue('product');?></label></td>
                        <td class="fldForm">
                        	<?php
								echo cmfcHtml::drawDropDown(
									"resumeInfo[orderDetails][productId]",
									$_POST['resumeInfo']['orderDetails']['productId'],
									$products,
									$sectionInfo['productsTable']['columns']['relatedItem'],
									$sectionInfo['productsTable']['columns']['title'],
									NULL,
									NULL,
									NULL,
									NULL
								)
							?>
                        </td>
                    </tr>
                    <tr>
                        <td class="lblForm"><label for="orderDetails_orderDescription"><?php echo $translation->getValue('orderDescription');?></label></td>
                        <td class="fldForm">
                            <textarea class="inout" name="resumeInfo[orderDetails][orderDescription]" id="orderDetails_orderDescription" dir="<?php echo $translation->languageInfo['dir'];?>" lang="<?php echo $translation->languageInfo['sName'];?>" cols="40"><?php echo $_POST['resumeInfo']['orderDetails']['orderDescription']?></textarea>
                        </td>
                    </tr>
                </table>
                </fieldset> 
                
                <br />
            
                <fieldset><legend><?php echo $translation->getValue('security')?></legend>
                <table class="eOrderTbl">
                    <tbody>
                        <tr>
                        <td class="lblForm" ><?php echo $translation->getValue('Security_Code') ?> <font color="red">*</font></td>
                        <td>
                            <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="fldForm"><input style="background-color:#f2f2f2; " class="input" name="resumeInfo[security][code]" value="" type="text" onfocus="this.style.backgroundColor='#ffffff';" onblur="this.style.backgroundColor='#f2f2f2';" dir="ltr" /> &nbsp;</td>
                                <td>
                                    <a onclick="var icp=document.getElementById('captchaImage');var tmp = new Date();icp.src='/captcha.php?rid='+tmp.getTime();return false" href="javascript:void(0)">
                                        <img id="captchaImage" src="/captcha.php?rid=<?php echo time()?>" style="border:1px solid #444444; " alt="c" />
                                    </a>
                                </td>
                            </tr>
                            </table>
                        </td>
                        </tr>
                        <tr>
                        <td>&nbsp;</td>
                        <td>
                            <p>
                            <?php echo $translation->getValue('Form_Security_Code_Note') ?>
                            </p>
                        </td>
                        </tr>
                    </tbody>
                </table>
                </fieldset>
                <div class="submitBtnBox">
                    <input type="submit" id="step" class="submit_recruit btn" value="<?php echo $translation->getValue('submit')?>" name="submit_send" />
                </div>
            </form>
            </div>
			<?php
		}
		?>
        <br class="clearfloat" />
    </div>
<?php
}
?>
