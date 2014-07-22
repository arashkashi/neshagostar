<?php
ini_set('display_errors', 0);
$htmlTags = NULL;
$javascriptsInsideSection = FALSE;
include 'requirements/preparing.inc.php';
//error_reporting(E_USER_WARNING );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" href="interface/images/favicon.ico"/>

        <?php
        if (isset($_ws['siteInfo']['keywords']))
        {
            ?><meta name="keywords" content="<?php echo $_ws['siteInfo']['keywords'] ?>" http-equiv="keywords" /><?php
        }
        if (isset($_ws['siteInfo']['description']))
        {
            ?><meta name="description" content="<?php echo $_ws['siteInfo']['description'] ?>" http-equiv="description" /><?php
        }

        include("requirements/htmlHead.inc.php");
        if ($_POST['submit_login'] and $translation->languageInfo['sName'] == 'fa')
        {
            if ($_POST['remember_me'] == 'true')
                $userSystem->setRememberUserEnabled(true);

            $userSystem->setOption('autoRedirectEnabled', true);

            $result = $userSystem->login($_POST['username'], $_POST['password']);

            if (PEAR::isError($result))
            {
                $loginMessage = $result->getMessage();
            }
            else
            {
                $pagesInfo = $userSystem->getOption('pagesInfo');
                $userSystem->redirect($pagesInfo['afterLogin']);
                $userInfo = $userSystem->getOption("userInfo");
                $sqlQuery = "Update $userSystem->_tableName SET last_login_datetime=login_datetime, login_datetime=NOW() WHERE id=$userSystem->cvId";
                cmfcMySql::exec($sqlQuery);
            }
        }
        $userInfo = $userSystem->getOption("userInfo");

        $pageViews = wsfSiteCounterPlusPlus();
        ?>
        <title>
            <?php
            echo $translation->getValue('siteTitle');
            if ($pageTitle)
            {
                echo " - " . $pageTitle;
            }
            ?>
        </title>

    </head>

    <body>
        <div id="wrap">
            <div class="header">
                <div class="topHeader">
                    <h1><a href="<?php echo wsfPrepareUrl('?sn=home&lang=' . $_GET['lang']) ?>"><?php echo wsfGetValue('siteTitle') ?></a></h1>
                    <?php
                    $languages = cmfcMySql::getRows($_ws['physicalTables']['languages']['tableName']);
                    if ($languages)
                    {
                        ?>
                        <div class="lang">
                            <?php echo wsfGetValue('chooseLanguage') ?>
                            <form action="<?php echo $urlObject->prepareUrl(wsfExcludeQueryStringVars(array('lang', 'x', 'y'), 'get')); ?>" name="langForm" id="langForm" style="display:inline;">
                                <select id="languagesList" onchange="document.forms.langForm.submit();" name="lang">
                                    <?php
                                    foreach ($languages as $key => $row)
                                    {
                                        ?>
                                        <option value="<?php echo $row['short_name'] == 'fa' ? '' : $row['short_name']; ?>" <?php if ($row['id'] == $translation->languageInfo['id']) echo "selected='selected'"; ?>><?php echo $row['name'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php include('topMenu.inc.php'); ?>
                <div id="slideshow" class="slideShow">
                    <?php
                    $homePagePhotos = cmfcMySql::getRowsCustom("SELECT * FROM " . $_ws['physicalTables']['homePagePhotos']['tableName'] . " WHERE " . $_ws['physicalTables']['homePagePhotos']['columns']['visible'] . " = 1");
                    if ($homePagePhotos)
                    {
                        foreach ($homePagePhotos as $key => $row)
                        {
                            $row = cmfcMySql::convertColumnNames($row, $_ws['physicalTables']['homePagePhotos']['columns']);
                            if ($row['photoFilename'])
                            {
                                $result = $imageManipulator->getAsImageTag(array(
                                    'fileName' => $row['photoFilename'],
                                    'fileRelativePath' => $_ws['directoriesInfo']['homePageFolderRPath'],
                                    'version' => 2,
                                    'actions' => array(
                                        array(
                                            'subActions' => array(
                                                array(
                                                    'name' => 'resizeSmart',
                                                    'parameters' => array(
                                                        'width' => array(
                                                            'min' => 892,
                                                            'max' => 892,
                                                            'zoomInIfRequire' => true,
                                                            'ignoreAspectRatio' => false,
                                                        ),
                                                        'height' => array(
                                                            'min' => 250,
                                                            'max' => 250,
                                                            'zoomInIfRequire' => true,
                                                            'ignoreAspectRatio' => false,
                                                        ),
                                                        'priority' => array(
                                                            'biggerDimension',
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'name' => 'crop',
                                                    'parameters' => array(
                                                        'position' => 'center',
                                                        'width' => 892,
                                                        'height' => 250,
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                    'attributes' => array(
                                        'alt' => $row['photoFilename'],
                                    ),
                                ));
                                echo $result;
                            }
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="container">
                <div class="content">
                    <?php
                    if ($_GET['sn'] != 'home' and $_GET['sn'] != '')
                    {
                        /*
                          ?>
                          <div id="breadcrumb" style="text-align:<?php echo $translation->languageInfo['align']?>;">
                          <?php echo $pageBreadcrumb?>
                          </div>
                          <?php
                         */
                        if (empty($_GET['sn']))
                        {
                            $_GET['sn'] = 'home';
                            $fileToInclude = 'home.section.inc.php';
                        }
                        $_ws['currentSectionInternalPartName'] = 'inSectionContainer';
                        include ($fileToInclude);
                    }
                    else
                    {
                        $_ws['currentSectionInternalPartName'] = 'inSectionContainer';
                        include('home.section.inc.php');
                    }
                    ?> 
                </div>

                <?php include('sideBar.inc.php'); ?>
                <br class="clearfloat" />
            </div>
            <div class="footer">
                <div class="left">&copy; Copyright 2010. NeshaGostar.com. All Rights Reserved</div>
                <div class="right"><?php /* ?><a href="#">Privacy Policy</a>  |  <a href="#">Site Map</a><?php */ ?></div>
                <br class="clearfloat" />
            </div>



        </div>

        </div>
        <?php
        if ($javascriptsInsideSection)
        {
            echo $javascriptsInsideSection;
        }
        ?>
        <script type="text/javascript" src="interface/javascripts/script.js"></script>
        <script type="text/javascript">
            (function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-51478248-1', 'neshagostar.com');
            ga('send', 'pageview');

        </script>
    </body>
</html>
<?php
if ($_ws['settings']['translationMode'])
{
    $translation->printPageWords($langInfo, '/admin/');
}

wsfPageRenderingTime();
wsfGetRegisteredQueries();
wsfGetIncludedFileName();
wsfGetSectionInfo();
/* */
//$translation->printLog();
//cmfcHtml::printr($translation);
/*
  echo "------------- POST ---------------";
  cmfcHtml::printr($_POST);
  echo "------------- GET ----------------";
  cmfcHtml::printr($_GET);
  echo "------------- REQUEST ------------";
  cmfcHtml::printr($_REQUEST);
  /* */
?>


