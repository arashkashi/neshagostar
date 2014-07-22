<?php
if($_ws['currentSectionInternalPartName'] == "beforeHtml")
{
	$pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
}

if($_ws['currentSectionInternalPartName'] == "inHeader")
{
	$messages = array();
	$filled = FALSE;
	if ($_GET['surveyKey'])
	{
		$survey = cmfcMySql::load(
			$sectionInfo['tableInfo']['tableName'],
			$sectionInfo['tableInfo']['columns']['surveyHash'],
			$_GET['surveyKey']
		);
		
		if ($survey)
		{
			if ($survey['confirmed'])
			{
				$messages['errors'][] = 'با تشکر از شما، این پرسشنامه قبلا تکمیل و ارسال شده است.';
				$filled = TRUE;
			}
			else
			{
				$survey = cmfcMySql::convertColumnNames($survey,$sectionInfo['tableInfo']['columns']);
				$sqlQuery = "
					SELECT 
						* 
					FROM
						".$sectionInfo['surveyQuestionsTable']['tableName']."
					WHERE
						".$sectionInfo['surveyQuestionsTable']['columns']['languageId']." = ".$translation->languageInfo['id']."
					AND
						".$sectionInfo['surveyQuestionsTable']['columns']['active']." = 1
					ORDER BY ".$sectionInfo['surveyQuestionsTable']['columns']['id']."
					DESC
				";
				$questions = cmfcMysql::getRowsWithMultiKeys(
					$sectionInfo['surveyQuestionsTable']['tableName'],
					array(
						$sectionInfo['surveyQuestionsTable']['columns']['languageId']=>$translation->languageInfo['id'],
						$sectionInfo['surveyQuestionsTable']['columns']['active']=>1
					)
				);
				if ($questions)
				{
					
				}
				else
				{
					$messages['errors'][] = 'متاسفانه ارسال فرم در حال حاضر مقدور نمی باشد. ';
				}
			}
		}
		else
		{
			$messages['errors'][] = 'دسترسی شما به این صفحه امکان بدون کد دعوتنامه امکامپذیر نمی باشد.';
		}
		
	}
	
	$fieldsValidationInfo=array(
        "surveyInfo[surveyHash]"=>array(
            'name'=>"surveyInfo[surveyHash]",
            'headName'=>'surveyHash',
            'title'=>$translation->getValue('surveyHash'),
            'type'=>'string',
            'param'=>array(
                'notEmpty'=>true
            )
        ),
        "surveyInfo[code]"=>array(
            'name'=>"surveyInfo[code]",
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
	$validation->setOption('displayMethod', 'nearFields');
	$validation->setOption('optimizerObj',&$optimizer);
	
	/*$validation->setOption(
		'displayMethodOptions',
		array(
			'id'=>'messageBoard',
			'backgroundColor'=>'#FFC0C0'
		)
	);*/
	
	$validation->setOption('fieldsInfo',$fieldsValidationInfo);
	$successfulSubmit = 0;
	
	if (isset($_POST['submit_send']) and !$filled)
	{
		$_columns = &$_POST['surveyInfo'];
		if (is_array($fieldsValidationInfo))
			$validateResult=$validation->validate($_POST);
			
		if (strtolower($_SESSION['niceCaptchaCode']) != strtolower($_POST['surveyInfo']['code']) )
		{
			$validateResult[] = PEAR::raiseError($translation->getValue('Security_Code_Is_Wrong'));
		}
		unset($_SESSION['niceCaptchaCode']);
	
		if (empty($validateResult)) 
		{
			$survey = cmfcMySql::load(
				$sectionInfo['tableInfo']['tableName'],
				$sectionInfo['tableInfo']['columns']['surveyHash'],
				$_columns['surveyHash']
			);
			$surveyId = $survey['related_item'];
			
			$_columns['updateDatetime'] = date('Y-m-d h:m');
			$_columns['confirmed'] = 1;
			$surveyColumnValues = cmfcMySql::convertColumnNames($_columns, $sectionInfo['tableInfo']['columns']);
			$result = cmfcMySql::update($sectionInfo['tableInfo']['tableName'], $sectionInfo['tableInfo']['columns']['surveyHash'],$surveyColumnValues,$_columns['surveyHash']);
			
			
			if ($_columns['surveyDetails'] and $surveyId)
			{
				foreach ($_columns['surveyDetails'] as $key=>$row)
				{
					$row['surveyId'] = $surveyId;
					
					$surveyDetailColumnValues = cmfcMySql::convertColumnNames($row, $sectionInfo['surveyDetailsTable']['columns']);
					$result = cmfcMySql::insert($sectionInfo['surveyDetailsTable']['tableName'], $surveyDetailColumnValues);
				}
			}
			
			if ($result)
			{
				unset($_POST['surveyInfo']);
				$messages['messages'][]= $translation->getValue('successfullySubmitted');
				$successfulSubmit = 1;
			} 
			else 
			{
				$messages['errors'][] =  $translation->getValue('submissionOfYourSurveyFailedPleaseTryAgain');
				$messages['errors'][] = ' <a href="'.$url.'">['.$translation->getValue('Return').'...]</a>';
				$successfulSubmit = 0;
			}
		}
		else 
		{
			foreach ($validateResult as $r)
			{
				$messages['errors'][]=$r->getMessage();
				$successfulSubmit = 0;
			}
		}
	}
	
}
if($_ws['currentSectionInternalPartName'] == "inSectionContainer")
{
	
	?>
	<h1 class="title"><?php echo $translation->getValue($_GET['sn'])?></h1>
	<div class="page">
    
    	<?php
        wsfPrintMessages($messages);
		if (!$successfulSubmit and !$filled ) 
        {
            if (!empty($fieldsValidationInfo)) 
            {
                $validation->printJsClass();
                $validation->printJsInstance();
            }
			if ($questions)
			{
				?>
				<div id="contactForm" >
					<div class="contact">
						<form action="" method="post" enctype="application/x-www-form-urlencoded" name="myForm">
                        	<input type="hidden" name="surveyInfo[surveyHash]" value="<?php echo $_GET['surveyKey']?>" />
						<table cellspacing="0" cellpadding="0" class="contactform" dir="<?php echo $langInfo['htmlDir']?>" >
							<?php
							foreach ($questions as $key=>$question)
							{
								$question = cmfcMySql::convertColumnNames($question,$sectionInfo['surveyQuestionsTable']['columns']);
							?>
							<tr>
								<td><?php echo $key+1 ?>. &nbsp;<?php echo $question['title'] ?></td>
							</tr>
                            <tr>
								<td>
                                <input type="hidden" name="surveyInfo[surveyDetails][<?php echo $key?>][questionId]" value="<?php echo $question['relatedItem'];?>" />
                                <?php
								echo cmfcHtml::drawMultiRadioBoxes(
									"surveyInfo[surveyDetails][$key][answer]",
									explode(',',$_POST['surveyInfo']['surveyDetails'][$key]['answer']),
									$_ws['virtualTables']['surveyOptions']['rows'],
									$_ws['virtualTables']['surveyOptions']['columns']['id'],
									$_ws['virtualTables']['surveyOptions']['columns']['name']
								 );
								?>
                                </td>
							</tr>
                            <?php
							}
							?>
							<tr>
								<td class="lblForm" ><?php echo $translation->getValue('Security_Code') ?> <font class="star">*</font> : </td>
								<td>
									<table border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td class="fldForm"><input  class="input" name="surveyInfo[code]" value="" type="text" onfocus="this.style.backgroundColor='#ffffff';" onblur="this.style.backgroundColor='#fcfcfc';" dir="ltr" /> &nbsp;</td>
										<td>
											<a onclick="var icp=document.getElementById('captchaImage');var tmp = new Date();icp.src='/captcha.php?rid='+tmp.getTime();return false" href="javascript:void(0)">
												<img id="captchaImage" src="/captcha.php?rid=5" style="border:1px solid #444444; " alt="c" />
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
							<tr>
								<td></td>
								<td>
								<input class="submit_recruit" name="submit_send" type="submit" value=" <?php echo $translation->getValue('submit') ?> " />
								</td>
							</tr>
						</table>
						</form>
					</div>
				</div>
				<?php
			}
		}
		?>
    <br class="clearfloat" />
	</div>
    <?php

}?>
