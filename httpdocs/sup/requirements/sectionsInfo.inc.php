<?php
/**
* containers of the side menu
*
* sample of a container which include a custom file as its
* content ,if no childs defined and if "file" parameter didn't set it will
* include "containerName.box.inc.php"
* <code>
*	'currentDate'=>array(
*		'name'    => 'currentDate',
*		'title'   => 'تاریخ روز',
*		'titleEn' => 'Current Date',
*		//'file'    => 'currentDate.box.inc.php',
*	)
* </code>
*/ 



$_cp['sideMenuContainers']=array( 
	
	'currentDate'=>array(
		'title'   => 'تاریخ روز',
		'titleEn' => 'Current Date'
	),
	/*
	'maintenance'=>array(
	),
	 */
	'common'=>array(
		'title'=>'تنظیمات عمومی',
		'titleEn'=>'General Configurations',
		'childs'=>array(
			'users'=>array(
				'icon'=>'group.png',
				'title'=>'کاربران',
				'mainSections'=> true
				
			),
			'settings'=>array(
				'icon'=>'setting.png'
			),

			'languages'=>array(
				'title'=>'زبانها',
				'titleEn'=>'languages'
			),
				
		),
	),
	
	'site'=>array(
		'title'=>' اطلاعات وب سایت',
		'titleEn'=>'Web Site Data',
		'childs'=>array(
			
			'products'=>array(
				'icon'=>'products.png',
				'title'=>'محصولات',
				//'mainSections'=> true
			),
			/*
			'testsNStandards'=>array(
				'icon'=>'chart.png',
				'title'=>'تست ها و استانداردها',
				//'mainSections'=> true
			),
			*/
			'aboutUs'=>array(
				'icon'=>'about.png',
				'title'=>'درباره ما',
				//'mainSections'=> true
			),
			'qualityControl'=>array(
				'icon'=>'chart.png',
				'title'=>'کنترل کیفیت',
				//'mainSections'=> true
			),
			'news'=>array(
				'icon'=>'news.png',
				'title'=>'اخبار',
				'mainSections'=> true
			),
			'faq'=>array(
				'icon'=>'faq.png',
				'title'=>'سوالات متداول',
				//'mainSections'=> true
			),
			'staticPages'=>array(
				'icon'=>'staticPages.png',
				'title'=>'صفحات ثابت',
				'mainSections'=> true
			),
			'ourCustomers'=>array(
				'icon'=>'user.png',
				'title'=>'مشتریان ما',
				//'mainSections'=> true
			),
			
			
			'homePage'=>array(
				'icon'=>'log.png',
				'title'=>'های صفحه اول',
			),
			/*
			'relatedSites'=>array(
				'icon'=>'articles.png',
				'title'=>'سایت های مرتبط',
				'mainSections'=> true
			),
			'resellers'=> array(
				'name' =>'resellers',
				'icon'=>'siteMap.png'
			),
			'documents'=> array(
				'title' =>'مستندات',
				'icon'=>'documents.png',
				'mainSections'=> true
			),
			'softwares'=> array(
				'title' =>'نرم افزار',
				'icon'=>'softwares.png',
				'mainSections'=> true
			),
			'circulars'=> array(
				'title' =>'بخشنامه ها',
				'icon'=>'circulars.png',
				'mainSections'=> true
			),
			'galleries'=>array(
				'icon'=>'galleries.png',
				'title'=>'Galleries',
				'mainSections'=> true
			),
			
			'videos'=>array(
				'icon'=>'movie.jpg',
			),
			*/
		),
	),
	
	'orders'=>array(
		'title'=>'سفارشات',
		'titleEn'=>'Orders',
		'childs'=>array(
			'orders'=>array(
				'icon'=>'orders.png',
				'title'=>'سفارشات',
				'mainSections'=> true
			),
		),
	),
	/**/
	'reflects'=>array(
		'title'=>'بازتاب ها',
		'titleEn'=>'Reflects',
		'childs'=>array(
			'contactUs'=>array(
				'title'=>'تماس با ما',
				'titleEn'=>'Contact Us',
				'icon'=>'contact.png',
				'mainSections'=> true
			),
			'surveys'=>array(
				'icon'=>'poll.png',
				'title'=>'نظر سنجی از مشتریان',
				'mainSections'=> true
				
			),
			/*
			'recruit'=>array(
				'icon'=>'script.png',
				'title'=>'استخدام',
				'mainSections'=> true
			),
			'agencyApplications'=>array(
				'icon'=>'agencyApplications.png',
				'title'=>'درخواست های عاملیت',
				'mainSections'=> true
			),
			*/
		),
	),
/*	
	'subSets'=>array( 
		'childs'=>array(
					
			'branchesNAgencies'=>array(
				'icon'=>'group.png',
				'mainSections'=> true
			),
			
			'sellingAgents'=>array(
				'icon'=>'group.png',
				'mainSections'=> true
			),
			
			'messages'=>array(
				'icon'=>'messages.png',
				'mainSections'=> true
			),
		),
	),
	/* */
);


$_cp['topMenuContainers']=array(		
	
	'dashboard'=> array(
		'name' =>'dashboard',
		'titleFa'=>'داشبورد',
		'titleEn' => 'Dashboard',
		'status' => 'close',
	),
);


/**
 * This files is heart of sectional websites, information about each section is defined
 * with detials here.<br>
 * have a look at source code for details
 * 
 * @todo finding a way to support multilingual section info
 * @package [D]/requirements
 * @author Sina Salek
 */


$_cp['sectionsInfo']=array(	
/* Settings -------------------------------------------------------------------------| */ 
	'languages' => array (
		'accessPointName' => 'cp_languages',
		'sideMenuAddress'=>array('common','languages'),
		'title'=>'زبانها',
		'titleEn'=>'languages',
		'actions'=>array('new','list'),
		'title_en'=>'Languages',
		'listLimit'=>10,
		'tableInfo' => $_ws['physicalTables']['languages'],
		
	),
	
	'home' => array (
		//'accessPointName' => 'cp_other',
		//'sideMenuAddress'=>array('common','languages'),
		'topMenuAddress'=>array('dashboard'),
		'title'=>'داشبورد',
		'titleEn'=>'Dashboard',
		//'actions'=>array('new','list'),
	),
	
	'words' => array (
		'accessPointName' => 'cp_languages',
		'sideMenuAddress'=>array('common','languages'),
		'title'=>'لغات',
		'titleEn'=>'words',
		'actions'=>array('new','list'),
		'title_en'=>'words',
		'listLimit'=>20,
		'tableInfo' => &$_ws['physicalTables']['words'],
		
	),
	
	'settings'=>array(
		'actions'=>array('list'),
		'accessPointName' => 'cp_settings',
		'sideMenuAddress'=>array('common','settings'),
		//'topMenuAddress'=>array('settings'),
		'title'=>'تنظیمات اصلی',
		'titleEn'=>'Main Settings',
		'listLimit'=>10,
		'tableInfo'=>$_ws['physicalTables']['settings'],
		'folderRelative'=> $_ws['directoriesInfo']['settingsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['settingsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['settingsFolderRPath'],
	),
	
	'menuCategories' => array(
		'title'=>'ساختار سایت',
		'titleEn'=>'Site Structure',
							  
		'accessPointName' => 'cp_settings',
		'sideMenuAddress' => array( 'common', 'settings'),
		'fileName'=>'categories.section.inc.php',
		'listLimit' => 3,
		'tableInfo'	=>	& $_ws[ 'physicalTables'][ 'categories'],
	),
	/*
	'emailTemplates' => array (
		'accessPointName' => 'cp_settings',
		'sideMenuAddress'=>array('common','settings'),
		'title'=>'قالب نامه های سایت',
		'titleEn'=>'emailTemplates',
		'actions'=>array('list'),
		'listLimit'=>30,
		'tableInfo'=>$_ws['physicalTables']['emailTemplates'],

		
	),
	/*
	'categoryLanguages' => array (
		'title'=>'دسته بندی منو',
		//'sideMenuAddress'=>array('common','languages'),
		'actions'=>array('new','list'),
		'listLimit'=>10,
		'tableInfo'=>$_ws['physicalTables']['categoryLanguages'],
		'folderRelative'=> $_ws['directoriesInfo']['categoriesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['categoriesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['categoriesFolderRPath'],
		
	),
	*/
/* Static Pages -------------------------------------------------------------------------| */ 

	/*
	'aboutUs' => array (
		'accessPointName' => 'cp_aboutUs',
		'sideMenuAddress'=>array('site','aboutUs'),
		'actions'=>array('new','list'),
		'title'=>'درباره ما',
		'tableInfo' => $_ws['physicalTables']['aboutUs'],
		'listLimit'=>10,

		'folderRelative'=> $_ws['directoriesInfo']['aboutUsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['aboutUsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['aboutUsFolderRPath'],
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'aboutUs.png',
			)
		),
	),
	*/
	
	
/* Users -------------------------------------------------------------------------| */ 
	'webUsers' => array (
		'title'=>'کاربران سایت',
		'titleEn'=>'Web Users',
						 
		'accessPointName' => 'cp_userManagement',
		'sideMenuAddress'=>array('subSets', 'branchesNAgencies'),
		'actions'=>array('new','list'),
		
		'tableInfo'=>$_ws['physicalTables']['webUsers'],
		'listLimit'=>15,
		
		'userGroupTypeName' => 'viewUserGroup',
		'folderRelative'=> $_ws['directoriesInfo']['profilesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['profilesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['profilesFolderRPath'],
		'dashboard' => array(
			'mainSections' => array(
			'icon'=>'webUsers.png',
			),
		),
		
	),
	
	/*
	'viewUserGroups'=>array(
		'accessPointName' => 'cp_userManagement',
		'sideMenuAddress'=>array('subSets','branchesNAgencies'),
		'actions'=>array('list'),
		//'folderRelative'=> $_ws['directoriesInfo']['usersFolderRPath'],
		//'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['usersFolderRPath'],
		//'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['usersFolderRPath'],		
		//'listLimit'=>15,
		'fileName' => 'userGroups.section.inc.php',
		'tableInfo'=>$_ws['physicalTables']['userGroups'],
		'siteSectionsTable'=>$_ws['physicalTables']['siteSections'],
		'typeName' => 'viewUserGroup',
		'siteSectionsType' => 'viewSiteSection',
		
		'list' => array(
			'actions'	=> array( 
					'edit' 		=> true, 
					'delete'	=> array( //Add Any Attribute...
							'onclick' => 'return '. $js -> jsfConfimationMessage( wsfGetValue( 'areYouSure'))
							)
					),
			'checkBox'	=> true,
			'columns' 	=> array(
					'name' => array(
						'title'		=> 'groupName',
						'sortBy'	=> true,
					),
			)
		)
		
	),
	
	*/
	'users' => array (
		'title'=>'کاربران مدیریت',
		'titleEn'=>'Admin Users',
					  
		'accessPointName' => 'cp_userManagement',
		'sideMenuAddress'=>array('common', 'users'),
		'actions'=>array('new','list'),
		//'fileName' => 'newUser.section.inc.php',
		'tableInfo'=>$_ws['physicalTables']['users'],
		'userGroupTypeName' => 'cpUserGroup',

		'folderRelative'=> $_ws['directoriesInfo']['profilesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['profilesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['profilesFolderRPath'],


	),
	
	'userProfile' => array (
		'accessPointName' => 'cp_userManagement',
		//'sideMenuAddress'=>array('usersSections', 'users'),
		//'actions'=>array('new','list'),
		//'fileName' => 'newUser.section.inc.php',
		'tableInfo'=>$_ws['physicalTables']['users'],
	),
	
	'userGroups'=>array(
		'title'=>'گروه های کاربری مدیریت',
		'accessPointName' => 'cp_userManagement',
		'sideMenuAddress'=>array('common','users'),
		//'topMenuAddress'=>array('userManagement'),
		'actions'=>array('list','new'),
		//'folderRelative'=> $_ws['directoriesInfo']['usersFolderRPath'],
		//'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['usersFolderRPath'],
		//'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['usersFolderRPath'],		
		//'listLimit'=>15,
		'tableInfo'=>$_ws['physicalTables']['userGroups'],
		'siteSectionsTable'=>$_ws['physicalTables']['siteSections'],
		'typeName' => 'cpUserGroup',
		'siteSectionsType' => 'cpSiteSection',
		
	),
	
	'logs' => array (
		'title'=>'فعالیت های انجام شده',
		'titleEn'=>'Logs',
		'listLimit'=> 10,			  
		'accessPointName' => 'cp_userManagement',
		'sideMenuAddress'=>array('common', 'users'),
		'actions'=>array('list'),
		//'fileName' => 'newUser.section.inc.php',
		'tableInfo'=>$_ws['physicalTables']['logs'],
		'usersTable'=>$_ws['physicalTables']['users'],

	),

/* News     -------------------------------------------------------------------------| */ 
	'news' => array (
		'title'=>'اخبار',
		'titleEn'=>'News',
					 
		'accessPointName' => 'cp_news',
		'nodeId'=>$_ws['Main_Tree_Nodes']['news'],
		'sideMenuAddress'=>array('site', 'news'),
		'actions'=>array('new','list'),
		'listLimit'=>30 ,
		
		'tableInfo'=>$_ws['physicalTables']['news'],
		//'imagesTable'=> & $_ws['physicalTables']['newsImages'],
		'categoriesTable'=>$_ws['physicalTables']['newsCategories'],
		
		'folderRelative'=>$_ws['directoriesInfo']['newsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['newsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['newsFolderRPath'],
		
		'dashboard' => array(
			'mainSections' => array(
			'icon'=>'news.png',
			),
		),
	),
/* Products -------------------------------------------------------------------------| */ 
	
	'products' => array (
		'accessPointName' => 'cp_products',
		'sideMenuAddress'=>array('site', 'products'),
		'nodeId'=>$_ws["Main_Tree_Nodes"]['products'],
		'actions'=>array('new','list','order'),
		'listLimit'=>30,
		'title'=>'محصولات',
		
		'tableInfo'=>$_ws['physicalTables']['products'],
		'productImagesTable'=> $_ws['physicalTables']['productsImages'],
		'standardsTable'=> $_ws['physicalTables']['standards'],
		//'categoriesTable' => $_ws['physicalTables']['categories'],
		
		'folderRelative'=>$_ws['directoriesInfo']['productsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['productsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['productsFolderRPath'],
		
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'products.png',
			)
		),
		
		'javascriptFilesToInclude' => array(
			//'autocomplete/lib/jquery.ajaxQueue.js',
			'autocomplete/lib/jquery.bgiframe.min.js',
			'autocomplete/jquery.autocomplete.js',
			//'/jqupload/jquery.flash.js',
			//'/jqupload/jquery.jqUploader.js'
		),
		'cssFilesToInclude' => array(
			'autocomplete/jquery.autocomplete.css',
		),

	),
	
	'orders' => array (
		'accessPointName' => 'cp_orders',
		'sideMenuAddress'=>array('orders', 'orders'),
		'actions'=>array('list'),
		'title'=>'سفارشات',
		
		'tableInfo' => $_ws['physicalTables']['orders'],
		'personalInfoTable'=>$_ws['physicalTables']['orders'],
		'productsTable'=>$_ws['physicalTables']['products'],
		'orderDetailsTable'=>$_ws['physicalTables']['orderDetails'],
		'customersTable'=>$_ws['physicalTables']['webUsers'],
		'sellingAgentsTable'=>$_ws['physicalTables']['sellingAgents'],
		
		'relatedTables'=>array(
			'products'=>$_ws['physicalTables']['products'],
			'orderDetails'=>$_ws['physicalTables']['orderDetails'],
		),
		
		
		'listLimit'=>10,
		'folderRelative'=> $_ws['directoriesInfo']['ordersFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['ordersFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['ordersFolderRPath'],
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'orders.png',
			)
		),
	),
	/*
	'confirmedOrders' => array (
		'accessPointName' => 'cp_orders',
		'sideMenuAddress'=>array('orders', 'orders'),
		'actions'=>array('list'),
		'title'=>'سفارشات تایید شده',
		
		'tableInfo' => $_ws['physicalTables']['orders'],
		'personalInfoTable'=>$_ws['physicalTables']['orders'],
		'productsTable'=>$_ws['physicalTables']['products'],
		'orderDetailsTable'=>$_ws['physicalTables']['orderDetails'],
		'customersTable'=>$_ws['physicalTables']['webUsers'],
		'sellingAgentsTable'=>$_ws['physicalTables']['sellingAgents'],
		
		'relatedTables'=>array(
			'products'=>$_ws['physicalTables']['products'],
			'orderDetails'=>$_ws['physicalTables']['orderDetails'],
		),
		
		
		'listLimit'=>10,
		'folderRelative'=> $_ws['directoriesInfo']['ordersFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['ordersFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['ordersFolderRPath'],
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'orders.png',
			)
		),
	),
	*/
	
/* RelatedSites -------------------------------------------------------------------------| */ 
/* Reflects -------------------------------------------------------------------------| */ 
	'contactUs' => array (
		'accessPointName' => 'cp_contactUs',
		'sideMenuAddress'=>array('reflects','contactUs'),
		
		'nodeId'=>$_ws["Main_Tree_Nodes"]['contactUs'],
		
		'actions'=>array('list'),
		'listLimit'=>10,
		'title'=>'تماس با ما',
		
		'tableInfo'=>$_ws['physicalTables']['contactUs'],
		'categoriesTable' => $_ws['physicalTables']['contactUsCategories'],
		
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'contactUs.png',
			)
		),
	),
	
	'contactUsCategories' => array (
		'title'=>'انواع درخواست',
					 
		'accessPointName' => 'cp_contactUs',
		'sideMenuAddress'=>array('reflects','contactUs'),
		'actions'=>array('new','list'),
		'listLimit'=>30 ,
		'tableInfo'=>$_ws['physicalTables']['contactUsCategories'],
	),

	'contactUsPage' => array (
		'accessPointName' => 'cp_contactUs',
		'sideMenuAddress'=>array('reflects','contactUs'),
		'actions'=>array('edit'),
		'title'=>'تماس با ما',
		'tableInfo' => $_ws['physicalTables']['staticPages'],
		'fileName' => 'editablePages.section.inc.php',
		'folderRelative'=> $_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
	),
	
	'surveys' => array (
		'accessPointName' => 'cp_surveys',
		'sideMenuAddress'=>array('reflects','surveys'),
		
		'nodeId'=>$_ws["Main_Tree_Nodes"]['surveys'],
		
		'actions'=>array('new','list'),
		'listLimit'=>10,
		'title'=>'نظرسنجی ها',
		
		'tableInfo'=>$_ws['physicalTables']['surveys'],
		'surveyDetails'=>$_ws['physicalTables']['surveyDetails'],
		'relatedTables'=>array(
			'surveyDetails'=>$_ws['physicalTables']['surveyDetails'],
		),
		'surveyQuestionsTable' => $_ws['physicalTables']['surveyQuestions'],
		
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'poll.png',
			)
		),
	),
	
	'surveyQuestions' => array (
		'accessPointName' => 'cp_surveys',
		'sideMenuAddress'=>array('reflects','surveys'),
		
		'nodeId'=>$_ws["Main_Tree_Nodes"]['surveys'],
		
		'actions'=>array('new','list'),
		'listLimit'=>10,
		'title'=>'سوالات نظرسنجی',
		
		'tableInfo'=>$_ws['physicalTables']['surveyQuestions'],
	),
	
	'surveyPage' => array (
		'accessPointName' => 'cp_surveys',
		'sideMenuAddress'=>array('reflects','surveys'),
		'actions'=>array('edit'),
		'title'=>'صفحه نظرسنجی',
		'tableInfo' => $_ws['physicalTables']['staticPages'],
		'fileName' => 'editablePages.section.inc.php',
		'folderRelative'=> $_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
	),
	
	
	
	
	'offersNComplaintsPage' => array (
		'accessPointName' => 'cp_offersNComplaints',
		'sideMenuAddress'=>array('reflects','offersNComplaints'),
		'actions'=>array('edit'),
		'title'=>'صفحه شکایات و پیشنهادات',
		'tableInfo' => $_ws['physicalTables']['staticPages'],
		'fileName' => 'editablePages.section.inc.php',
		'folderRelative'=> $_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
	),
	
	'offersNComplaintsAfterSubmitPage' => array (
		'accessPointName' => 'cp_offersNComplaints',
		'sideMenuAddress'=>array('reflects','offersNComplaints'),
		'actions'=>array('edit'),
		'title'=>'صفحه بعد از ارسال',
		'tableInfo' => $_ws['physicalTables']['staticPages'],
		'fileName' => 'editablePages.section.inc.php',
		'folderRelative'=> $_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
	),
	
/* Documents -------------------------------------------------------------------------| */ 
/* Standards  -------------------------------------------------------------------------| */ 
	/*
	'standards' => array (
		'accessPointName' => 'cp_standards',
		'sideMenuAddress'=>array('site', 'testsNStandards'),
		'nodeId'=>$_ws["Main_Tree_Nodes"]['standards'],
		'actions'=>array('new','list'),
		'listLimit'=>30,
		'title'=>'استانداردها',
		
		'tableInfo'=>$_ws['physicalTables']['standards'],
		'testsTable'=>$_ws['physicalTables']['tests'],
		
		'folderRelative'=>$_ws['directoriesInfo']['standardsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['standardsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['standardsFolderRPath'],
		
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'standards.png',
			)
		),
		
		'javascriptFilesToInclude' => array(
			//'autocomplete/lib/jquery.ajaxQueue.js',
			'autocomplete/lib/jquery.bgiframe.min.js',
			'autocomplete/jquery.autocomplete.js',
			//'/jqupload/jquery.flash.js',
			//'/jqupload/jquery.jqUploader.js'
		),
		'cssFilesToInclude' => array(
			'autocomplete/jquery.autocomplete.css',
		),

	),
	
	'tests' => array (
		'accessPointName' => 'cp_tests',
		'sideMenuAddress'=>array('site', 'testsNStandards'),
		'nodeId'=>$_ws["Main_Tree_Nodes"]['tests'],
		'actions'=>array('new','list'),
		'listLimit'=>30,
		'title'=>'تست ها',
		
		'tableInfo'=>$_ws['physicalTables']['tests'],
		
		'folderRelative'=>$_ws['directoriesInfo']['testsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['testsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['testsFolderRPath'],
		
		'dashboard' => array(
			'mainSections' => array(
				'icon'=>'tests.png',
			)
		),
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),

	),
	*/
/* About Us -------------------------------------------------------------------------| */ 
	/*'aboutUs' => array (
		'accessPointName' => 'cp_aboutUs',
		'sideMenuAddress'=>array('site','aboutUs'),
		'actions'=>array('new','list'),
		'listLimit'=>30,
		'title'=>'درباره ما',
		
		'tableInfo' => $_ws['physicalTables']['aboutUs'],
		
		'folderRelative'=> $_ws['directoriesInfo']['aboutUsFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['aboutUsFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['aboutUsFolderRPath'],
	),
	*/
	'aboutUs' => array (
		'accessPointName' => 'cp_aboutUs',
		'sideMenuAddress'=>array('site','aboutUs'),
		
		'nodeId'=>$_ws["Main_Tree_Nodes"]['aboutUs'],
		
		'actions'=>array('edit'),
		'listLimit'=>10,
		'title'=>'درباره نشاگستر',
		
		'tableInfo'=>$_ws['physicalTables']['staticPages'],
		'fileName'=>'editablePages.section.inc.php',
		
		'folderRelative'=> $_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		
	),
	
	'licences' => array (
		'accessPointName' => 'cp_aboutUs',
		'sideMenuAddress'=>array('site', 'aboutUs'),
		'nodeId'=>$_ws["Main_Tree_Nodes"]['licences'],
		'actions'=>array('new','list'),
		'listLimit'=>30,
		'title'=>'گواهینامه ها',
		
		'tableInfo'=>$_ws['physicalTables']['licences'],
		
		'folderRelative'=>$_ws['directoriesInfo']['licencesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['licencesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['licencesFolderRPath'],
		
		'javascriptFilesToInclude' => array(
		),
		'cssFilesToInclude' => array(
		),

	),
	
	'qualityControl' => array (
		'accessPointName' => 'cp_qualityControl',
		'sideMenuAddress'=>array('site','qualityControl'),
		
		'nodeId'=>$_ws["Main_Tree_Nodes"]['qualityControl'],
		
		'actions'=>array('edit'),
		'listLimit'=>10,
		'title'=>'کنترل کیفیت',
		
		'tableInfo'=>$_ws['physicalTables']['staticPages'],
		'fileName'=>'editablePages.section.inc.php',
		
		'folderRelative'=> $_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['staticPagesFolderRPath'],
		
	),
/* Homepage -------------------------------------------------------------------------| */ 

	'homePagePhotos' => array (
		'accessPointName' => 'cp_homePage',
		'sideMenuAddress'=>array('site', 'homePage'),
		//'nodeId'=>$_ws["Main_Tree_Nodes"]['multimediaGalleries'],
		//'fileName' => 'photos.section.inc.php',
		'actions'=>array('new','list'),
		'listLimit'=>10,
		'title'=>'تصاویر صفحه اول',
		'tableInfo'=>$_ws['physicalTables']['homePagePhotos'],
		//'categoriesTableInfo' => $_ws['physicalTables']['categories'],
		'folderRelative'=>$_ws['directoriesInfo']['homePageFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['homePageFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['homePageFolderRPath'],
		
		'dashboard' => array(
			'mainSections' => array(
			'icon'=>'homePagePhotos.png'),
		),
	),
/* Faq -------------------------------------------------------------------------| */ 
	'faq' => array (
		'accessPointName' => 'cp_faq',
		'sideMenuAddress'=>array('site','faq'),
		'actions'=>array('new','list'),
		'listLimit'=>30,
		'title'=>'سوالات متداول',
		
		'tableInfo' => $_ws['physicalTables']['faq'],
		
		'folderRelative'=> $_ws['directoriesInfo']['faqFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['faqFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['faqFolderRPath'],
	),
/* About Us -------------------------------------------------------------------------| */ 
	'ourCustomers' => array (
		'accessPointName' => 'cp_ourCustomers',
		'sideMenuAddress'=>array('site','ourCustomers'),
		'actions'=>array('new','list'),
		'listLimit'=>30,
		'title'=>'مشتریان ما',
		
		'tableInfo' => $_ws['physicalTables']['ourCustomers'],
		
		//'fileName'=> 'aboutUs.section.inc.php',
		
		'folderRelative'=> $_ws['directoriesInfo']['ourCustomersFolderRPath'],
		'folderPath'=>$_ws['siteInfo']['path'].$_ws['directoriesInfo']['ourCustomersFolderRPath'],
		'folderUrl'=>$_ws['siteInfo']['url'].$_ws['directoriesInfo']['ourCustomersFolderRPath'],
	),
);
?>