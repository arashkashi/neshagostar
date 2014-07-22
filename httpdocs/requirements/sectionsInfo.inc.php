<?php
/**
 * This files is heart of sectional websites, information about each section is defined
 * with detials here.<br>
 * have a look at source code for details
 * 
 * @todo finding a way to support multilingual section info
 * @package [D]/requirements
 * @author Sina Salek
 */

$_ws['sectionsInfo']=array(
	'aboutUs'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['aboutUs'],
		'tableInfo'=>$_ws['physicalTables']['staticPages'],
		
		'folderRelative'=>$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
		
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['staticPages']['columns']['languageId']." = ".$translation->languageInfo['id'].
											" AND ".$_ws['physicalTables']['staticPages']['columns']['internalName']." = 'aboutUs'",
		),
	),
	'qualityControl'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['qualityControl'],
		'tableInfo'=>$_ws['physicalTables']['staticPages'],
		
		'folderRelative'=>$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
		'fileName'=>'aboutUs',
		
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['staticPages']['columns']['languageId']." = ".$translation->languageInfo['id'].
											" AND ".$_ws['physicalTables']['staticPages']['columns']['internalName']." = 'qualityControl'",
		),
	),
	'ourCustomers'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['ourCustomers'],
		'tableInfo'=>$_ws['physicalTables']['ourCustomers'],
		
		'folderRelative'=>$_ws['directoriesInfo']['ourCustomersFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['ourCustomersFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['ourCustomersFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
		'pageTypes'=>array(
			'defaultPageType'=>'list'
		),
		
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['ourCustomers']['columns']['languageId']." = ".$translation->languageInfo['id'],
		),
	),
	/*
	'standards'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['standards'],
		'tableInfo'=>$_ws['physicalTables']['standards'],
		'testsTable'=>$_ws['physicalTables']['tests'],
		
		'folderRelative'=>$_ws['directoriesInfo']['standardsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['standardsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['standardsFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
		'pageTypes'=>array(
			'defaultPageType'=>'list'
		),
		
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['standards']['columns']['languageId']." = ".$translation->languageInfo['id'],
		),
	),
	
	'tests'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['tests'],
		'tableInfo'=>$_ws['physicalTables']['tests'],
		
		'folderRelative'=>$_ws['directoriesInfo']['testsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['testsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['testsFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
		'pageTypes'=>array(
			'defaultPageType'=>'list'
		),
		
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['tests']['columns']['languageId']." = ".$translation->languageInfo['id'],
		),
	),
	*/
	'home'=>array(
		'tableInfo'=>$_ws['physicalTables']['products'],
		'categoriesTableInfo'=>$_ws['physicalTables']['categories'],
		'productsTableInfo'=>$_ws['physicalTables']['products'],
		
		'productsNodeId'=>$_ws['Main_Tree_Nodes']['products'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'folderRelative'=>$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'internalParts' => array(
			'inHeader',
			'inSectionContainer',
		),
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
	),
	
	'search'=>array(
		'internalParts' => array(
			'inHeader'
		)
	),
	
	'products'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['products'],
		
		'tableInfo'=>$_ws['physicalTables']['products'],
		'categoriesTableInfo'=>$_ws['physicalTables']['categories'],
		'catLangTableInfo'=>$_ws['physicalTables']['categoryLanguages'],
		'standardsTable'=>$_ws['physicalTables']['standards'],
		'documentsTable'=>$_ws['physicalTables']['documents'],
		'documentsCategoriesTable'=>$_ws['physicalTables']['documentsCategories'],
		
		'imagesTableInfo'=>$_ws['physicalTables']['productsImages'],
		
		'folderRelative'=>$_ws['directoriesInfo']['productsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['productsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['productsFolderRPath'],
		'documentsFolderRelative'=>$_ws['directoriesInfo']['documentsFolderRPath'],
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		'javascriptFilesToInclude' => array(
			//'jquery-ui-1.7.1.custom.min.js',
			'jquery.lightbox-0.5.pack.js'
		),
		'cssFilesToInclude' => array(
			//'ui-darkness/jquery-ui-1.7.2.custom.css',
			'jquery.lightbox-0.5.css'
		),
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'generalCharacteristics',
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'generalCharacteristics'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['products']['columns']['languageId']." = ".$langInfo['id'],
		),
	),
	
	'news'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['news'],
		
		'tableInfo'=>$_ws['physicalTables']['news'],
		'categoriesTableInfo'=>$_ws['physicalTables']['newsCategories'],
		
		'folderRelative'=>$_ws['directoriesInfo']['newsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['newsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['newsFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		'javascriptFilesToInclude' => array(
			//'jquery-ui-1.7.1.custom.min.js',
		),
		'cssFilesToInclude' => array(
			//'smoothness/ui.all.css',
		),
		'pageTypes'=>array(
			'defaultPageType'=>'list'
		),
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['news']['columns']['languageId']." = ".$translation->languageInfo['id'],
		),
	),
	
	'contactUs'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['contactUs'],
			   
		'tableInfo'=>$_ws['physicalTables']['staticPages'],
		'formTable'=>$_ws['physicalTables']['contactUs'],
		'categoriesTable'=>$_ws['physicalTables']['contactUsCategories'],
		
		'folderRelative'=>$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
			'farsitype.min.js',
		),
		'cssFilesToInclude' => array(
		),
		
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['staticPages']['columns']['languageId']." = ".$translation->languageInfo['id'].
											" AND ".$_ws['physicalTables']['staticPages']['columns']['internalName']." = 'contactUsPage'",
		),
	),
	
	'eOrder'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['eOrder'],
		
		'listLimit'=>10,
		'tableInfo'=>$_ws['physicalTables']['orders'],
		'productsTable'=>$_ws['physicalTables']['products'],
		'orderDetailsTable'=>$_ws['physicalTables']['orderDetails'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		
		'javascriptFilesToInclude' => array(
			'farsitype.min.js',
			//'/jqThickbox/thickbox.js',
			//'/jqThickbox/compatibility.js',
			//'ajaxupload.3.6.js'
		),
		'cssFilesToInclude' => array(
			//'/thickbox.css',
		)

	),
	
	'faq'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['faq'],
		
		'tableInfo'=>$_ws['physicalTables']['faq'],
		//'categoriesTableInfo'=>$_ws['physicalTables']['faqCategories'],
		
		'folderRelative'=>$_ws['directoriesInfo']['faqFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['faqFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['faqFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		'javascriptFilesToInclude' => array(
			//'jquery-ui-1.7.1.custom.min.js',
		),
		'cssFilesToInclude' => array(
			//'smoothness/ui.all.css',
		),
		'pageTypes'=>array(
			'defaultPageType'=>'list'
		),
		'search'=>array (
			'columnsForSearch'=>array(
				'question',
				'answer'
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['faq']['columns']['languageId']." = ".$translation->languageInfo['id'],
		),
	),
	
	
	'survey'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['survey'],
		
		'tableInfo'=>$_ws['physicalTables']['surveys'],
		'surveyDetailsTable'=>$_ws['physicalTables']['surveyDetails'],
		'surveyQuestionsTable'=>$_ws['physicalTables']['surveyQuestions'],
		
		'folderRelative'=>$_ws['directoriesInfo']['surveysFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['surveysFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['surveysFolderRPath'],
		
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),
		
	),
	
	'licences'=>array(
		'nodeId'=>$_ws['Main_Tree_Nodes']['licences'],
		
		'tableInfo'=>$_ws['physicalTables']['licences'],
		
		'folderRelative'=>$_ws['directoriesInfo']['licencesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['licencesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['licencesFolderRPath'],
		'internalParts' => array(
			'inHeader',
			'inSectionContainer'
		),
		'search'=>array (
			'columnsForSearch'=>array(
				'title',
				'body',
			),
			'resultColumns'=>array(
				'id'=>'relatedItem',
				'title'=>'title',
				'description'=>'body'
			),
			'customSqlWhereConditions' =>	" AND ".$_ws['physicalTables']['licences']['columns']['languageId']." = ".$langInfo['id'],
		),
	),
);
?>