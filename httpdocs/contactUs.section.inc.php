<?php
if ($_ws['currentSectionInternalPartName'] == 'inHeader')
{
    $tableInfo = $_ws['sectionInfo']['tableInfo'];

    $contactUsPage = cmfcMySql::loadWithMultiKeys(
                    $tableInfo['tableName'], array(
                $tableInfo['columns']['internalName'] => 'contactUsPage',
                $tableInfo['columns']['languageId'] => $translation->languageInfo['id'],
                    )
    );
    $contactUsPage = cmfcMySql::convertColumnNames($contactUsPage, $tableInfo['columns']);
    $categoryItems = cmfcMysql::getRowsWithMultiKeys(
                    $sectionInfo['categoriesTable']['tableName'], array(
                $sectionInfo['categoriesTable']['columns']['languageId'] => $translation->languageInfo['id'],
                    )
    );
    $pageBreadcrumb = "<a href='" . wsfPrepareUrl('?sn=home&lang=' . $_GET['lang']) . "'>" . $translation->getValue('home') . "</a>"
            . " » " .
            $translation->getValue($_ws['sectionInfo']['name']);

    $pageTitle = $translation->getValue($_ws['sectionInfo']['name']);
    //$pageDescription = "";
    //$pageKeyWords = "";
}

if ($_ws['currentSectionInternalPartName'] == 'inSectionContainer')
{
    ?>
    <h1 class="title"><?php echo $translation->getValue($_GET['sn']); ?></h1>
    <div class="page">
        <?php
        $fieldsValidationInfo = array(
            "contactInfo[firstName]" => array(
                'name' => "contactInfo[firstName]",
                'headName' => 'firstName',
                'title' => $translation->getValue('firstName'),
                'type' => 'string',
                'param' => array(
                    'notEmpty' => true
                )
            ),
            "contactInfo[lastName]" => array(
                'name' => "contactInfo[lastName]",
                'headName' => 'lastName',
                'title' => $translation->getValue('lastName'),
                'type' => 'string',
                'param' => array(
                    'notEmpty' => true
                )
            ),
            "contactInfo[email]" => array(
                'name' => "contactInfo[email]",
                'headName' => 'email',
                'title' => $translation->getValue('Email'),
                'type' => 'email',
                'param' => array(
                    'notEmpty' => true
                )
            ),
            "contactInfo[body]" => array(
                'name' => "contactInfo[body]",
                'headName' => 'body',
                'title' => $translation->getValue('request'),
                'type' => 'string',
                'param' => array(
                    'notEmpty' => true
                )
            ),
            "contactInfo[code]" => array(
                'name' => "contactInfo[code]",
                'headName' => 'code',
                'title' => $translation->getValue('Security_Code'),
                'type' => 'string',
                'param' => array(
                    'notEmpty' => true
                )
            ),
        );

        $validation->setOption('formName', 'myForm');
        $validation->setOption('defaultStylesEnabled', false);
        $validation->setOption('displayMethod', 'nearFields');
        $validation->setOption('optimizerObj', &$optimizer);

        /* $validation->setOption(
          'displayMethodOptions',
          array(
          'id'=>'messageBoard',
          'backgroundColor'=>'#FFC0C0'
          )
          ); */

        $validation->setOption('fieldsInfo', $fieldsValidationInfo);

        $successfulSubmit = 0;
        $messages = array();
        if (isset($_POST['submit_send']))
        {
            $_columns = &$_POST['contactInfo'];
            if (is_array($fieldsValidationInfo))
            {
                $validateResult = $validation->validate($_POST);
            }

            if (strtolower($_SESSION['niceCaptchaCode']) != strtolower($_POST['contactInfo']['code']))
            {
                $validateResult[] = PEAR::raiseError($translation->getValue('Security_Code_Is_Wrong'));
            }
            unset($_SESSION['niceCaptchaCode']);

            if (empty($validateResult))
            {
                $_columns['insertDatetime'] = date('Y-m-d h:m');
                /* 	$_columns['purchaseDate'] = wsfConvertDateTimeDropDownArrayToDateTimeString($_columns['purchaseDate']); */
                $columnValue = cmfcMySql::convertColumnNames($_columns, $_ws['sectionInfo']['formTable']['columns']);

                $result = cmfcMySql::insert($_ws['sectionInfo']['formTable']['tableName'], $columnValue);


                if ($result)
                {
                    unset($_POST['contactInfo']);
                    $messages['messages'][] = $translation->getValue('successfullySubmitted');
                    $successfulSubmit = 1;
                }
                else
                {
                    $messages['errors'][] = $translation->getValue('submissionOfYourRequestFailedPleaseTryAgain');
                    $messages['errors'][] = ' <a href="' . $url . '">[' . $translation->getValue('Return') . '...]</a>';
                    $successfulSubmit = 0;
                }
                $url = wsfPrepareUrl('?' . cmfcUrl::excludeQueryStringVars(array('sort', 'searchFor', 'sortBy', 'sortType', 'defaultTab', 'sectionName', 'pt', 'pageType'), 'get'));
                //$messages['messages'][]=' <a href="'.$url.'">['.$translation->getValue('Return').'...]</a>';
            }
            else
            {
                foreach ($validateResult as $r)
                {
                    $messages['errors'][] = $r->getMessage();
                    $successfulSubmit = 0;
                }
            }
        }

        wsfPrintMessages($messages);

        if (!$successfulSubmit)
        {
            if (!empty($fieldsValidationInfo))
            {
                $validation->printJsClass();
                $validation->printJsInstance();
            }
            ?>
            <div>
                <?php
                if ($contactUsPage['photoFilename'])
                {
                    echo $imageManipulator->getAsImageTag(
                            array(
                                'fileName' => $contactUsPage['photoFilename'],
                                'fileRelativePath' => $_ws['sectionInfo']['folderRelative'],
                                'height' => 150,
                                //'showDebug' => true,
                                'mode' => 'resizeByMaxSize',
                                'attributes' => array(
                                    'style' => "border:1px solid #e1dfd0; float:" . $translation->languageInfo['!align'] . "; margin:0 10px; padding:2px; display:inline;",
                                    'alt' => 'Contact Us',
                                ),
                            )
                    );
                }
                echo $contactUsPage['body'];
                ?>
                <br style="clear:both" />
            </div>
            <br />
            <div id="contactForm" >
                <div class="contact">
                    <form action="" method="post" enctype="application/x-www-form-urlencoded" name="myForm">
                        <table cellspacing="0" cellpadding="0" class="contactform" dir="<?php echo $langInfo['htmlDir']; ?>" >

                            <tr>
                                <td class="lblForm"><?php echo $translation->getValue('firstName') ?> <font class="star">*</font> : </td>
                                <td class="fldForm"><input lang="<?php echo $translation->languageInfo['shortName'] ?>" name="contactInfo[firstName]" type="text" class="input"  onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" value="<?php echo $_POST['contactInfo']['firstName'] ?>" size="35" /></td>
                            </tr>
                            <tr>
                                <td class="lblForm" ><?php echo $translation->getValue('lastName') ?> <font class="star">*</font> : </td>
                                <td class="fldForm"><input lang="<?php echo $translation->languageInfo['shortName'] ?>" name="contactInfo[lastName]" type="text" class="input"  onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" value="<?php echo $_POST['contactInfo']['lastName'] ?>" size="35" /></td>
                            </tr>

                            <tr>
                                <td class="lblForm" ><?php echo $translation->getValue('companyName') ?> : </td>
                                <td class="fldForm"><input lang="<?php echo $translation->languageInfo['shortName'] ?>" name="contactInfo[companyName]" type="text" class="input"  onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" value="<?php echo $_POST['contactInfo']['companyName'] ?>" size="35"  /></td>
                            </tr>
                            <tr>
                                <td class="lblForm" ><?php echo $translation->getValue('email') ?>  <font class="star">*</font> : </td>
                                <td class="fldForm"><input name="contactInfo[email]" type="text" class="input"  onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" value="<?php echo $_POST['contactInfo']['email'] ?>" size="35" dir="ltr" /></td>
                            </tr>
                            <tr>
                                <td class="lblForm" ><?php echo $translation->getValue('tel') ?> : </td>
                                <td class="fldForm"><input name="contactInfo[tel]" type="text" class="input"  onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" value="<?php echo $_POST['contactInfo']['tel'] ?>" size="35" dir="ltr" /></td>
                            </tr>
                            <tr>
                                <td class="lblForm" ><?php echo $translation->getValue('address'); ?> : </td>
                                <td class="fldForm"><textarea lang="<?php echo $translation->languageInfo['shortName']; ?>"  class="input" name="contactInfo[address]" cols="50" rows="7" onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" ><?php echo $_POST['contactInfo']['address']; ?></textarea></td>
                            </tr>
                            <tr>
                                <td class="lblForm"><?php echo $translation->getValue('requestType') ?>  : </td>
                                <td class="fldForm">
                                    <select lang="<?php echo $translation->languageInfo['shortName'] ?>" name="contactInfo[categoryId]" onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" >
                                        <option value=""></option>
                                        <?php
                                        foreach ($categoryItems as $item)
                                        {
                                            $item = cmfcMySql::convertColumnNames($item, $sectionInfo['categoriesTable']['columns']);
                                            ?>
                                            <option value="<?php echo $item['relatedItem']; ?>" <?php echo ($item['relatedItem'] == $_POST['contactInfo']['categoryId']) ? 'selected="selected"' : ''; ?> >
                                                <?php echo $item['title']; ?>
                                            </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="lblForm" ><?php echo $translation->getValue('request'); ?> <font class="star">*</font> : </td>
                                    <td class="fldForm"><textarea lang="<?php echo $translation->languageInfo['shortName']; ?>"  class="input" name="contactInfo[body]" cols="50" rows="7" onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" ><?php echo $_POST['contactInfo']['body']; ?></textarea></td>
                                </tr>
                                <tr>
                                    <td class="lblForm" ><?php echo $translation->getValue('Security_Code'); ?> <font class="star">*</font> : </td>
                                    <td>
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td class="fldForm"><input  class="input" name="contactInfo[code]" value="" type="text" onfocus="this.style.backgroundColor = '#ffffff';" onblur="this.style.backgroundColor = '#fcfcfc';" dir="ltr" /> &nbsp;</td>
                                                <td>
                                                    <a onclick="var icp = document.getElementById('captchaImage');
                                                            var tmp = new Date();
                                                            icp.src = '/captcha.php?rid=' + tmp.getTime();
                                                            return false" href="javascript:void(0)">
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
                                            <?php echo $translation->getValue('Form_Security_Code_Note'); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input class="submit_recruit" name="submit_send" type="submit" value=" <?php echo $translation->getValue('submit'); ?> " />
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
                <?php
            }
            ?>
            <br class="clearfloat" />
        </div>
        <?php
    }
    