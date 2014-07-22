<?php
/**
* the empty values will be override by default or defaultoffline configurations
*/


$siteInfo = array(
	/**
	* Site base folder for storing files that uploaded by users.
	* useually each section has a folder here. for example if site has gallery
	* there should be gallery folder inside this folder.
	* as you can see there is different kind of addressed here : 
	* 	- Relative
	* 	- full path
	* 	- Folder Name
	* 	- URL
	* for example if you need url of gallery folder you can type : Site_Files_Folder_URL.Galleries_Folder_RPath .
	* or if you need full path it : Site_Files_Folder_Pat.Galleries_Folder_RPath
	* 
	*/
		
	'filesFolderName'=> 'data',
	'filesFolderPath'=> $_ws['siteInfo']['path'].'data/',
	'filesFolderURL'=> $_ws['siteInfo']['url'].'data/',
	'filesFolderRelativePath'=> 'data/',
	
	/**
	* temporary files like exported excel files,  cached image files etc
	*/
	'cacheFolderRPath'  => 'data'.'/'."cache/",
	
	
	/**
	* When an image is not available in image gallery or etc, you
	* can show this image instead
	*/
	'defaultImageURL'    => $_ws['siteInfo']['url'].'/'.'interface/images/no-photo.jpg',
	
	
	
);

$_ws=$_ws['configurator']->updateWs('siteInfo',$siteInfo,true);	


$_ws['translation'] =array(
		'translationMode'   => true, 
		'mode'   => true, 
		'prefixes' => array(
			'general' => 'VN_',
		),
		'debugMode' => true,
	);
	

$_ws['generalLibInfo'] = array(
	'path'         => $_ws['siteInfo']['path'].'mainLib/' ,
	'url'          => $_ws['siteInfo']['url'].'mainLib/' ,
	'inRootPath'   => $_ws['siteInfo']['url'].'mainLib/' ,
	
	'packagesInfo'=>array(
		/**
		* HTML editor xinha needs to be accessible via browser, so you
		* have a copy of it inside root/mainLib and set its path here
		*/
		'xinha'=>array(
			'path'  => "/mainLib/packages/xinha/",
		),
		/**
		* HTML editors needs to be accessible via browser, so you
		* have a copy of it inside root/mainLib and set its path here
		*/
		'wysiwyg'=>array(
			'url'  => "/mainLib/dependencies/xinhaV0/",
			'path'  => $_ws['siteInfo']['path']."/mainLib/dependencies/xinhaV0/",
		)
	)
);


//$_ws['databaseInfo'] = $_ws['configurations']['databaseInfo'];


/**
* Website title in control panel
*/

$_ws['controlPanelInfo']    = array(
	/**
	* default width and height of image thumbnail in control panel
	*/

	'defaultImageWidth'  => 150,
	'defaultImageHeight' =>	150,
	'title'              => '',
	'titleEn'            => '',
	'homePage'=>array(
		'lastItemColumnNo' => 3,
		'staticticsColumnNo' => 3,
	),
);


/**
* relative path of folders inside "files"
*/
$dirs = dir($_ws['siteInfo']['filesFolderPath']);
while (false !== ($entry = $dirs->read())) 
	if($entry != "." && $entry != ".." && $entry != "cache")
		$_ws['directoriesInfo'][$entry."FolderRPath"] = $_ws['siteInfo']['filesFolderRelativePath'].$entry.'/';
$dirs->close();



$_ws['virtualTables']=array(
	'genderTypes' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => 'male',
				'name' => "مرد"
			),
			array(
				'id' => 'female',
				'name' => 'زن',
			),
		),
	),
	'maritalStatus' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => 'married',
				'name' => "متاهل"
			),
			array(
				'id' => 'single',
				'name' => 'مجرد',
			),
		),
	),
	'ownershipTypes' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => '1',
				'name' => "مالکیت"
			),
			array(
				'id' => '2',
				'name' => 'سرقفلی',
			),
			array(
				'id' => '3',
				'name' => 'اجاره ای',
			),
		),
	),
	'securityTypes' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => '1',
				'name' => "چک"
			),
			array(
				'id' => '2',
				'name' => 'سفته',
			),
			array(
				'id' => '3',
				'name' => 'سند ملکی',
			),
		),
	),
	'diplomaTypes' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => '1',
				'name' => "سیکل و پایین تر"
			),
			array(
				'id' => '2',
				'name' => 'دیپلم',
			),
			array(
				'id' => '3',
				'name' => 'فوق دیپلم',
			),
			array(
				'id' => '4',
				'name' => 'لیسانس',
			),
			array(
				'id' => '5',
				'name' => 'فوق لیسانس',
			),
			array(
				'id' => '6',
				'name' => 'دکترا',
			),
		),
	),
	
	'skillLevels' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
			'nameEn'=>'nameEn'
		),
		'rows' => array(
			array(
				'id' => '1',
				'name' => "ضعیف",
				'nameEn'=> 'weak'
			),
			array(
				'id' => '2',
				'name' => 'متوسط',
				'nameEn'=> 'moderate'
			),
			array(
				'id' => '3',
				'name' => 'خوب',
				'nameEn'=> 'good'
			),
			array(
				'id' => '4',
				'name' => 'عالی',
				'nameEn'=> 'excellent'
			),
		),
	),
	
	'userActiveStatus' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => 1,
				'name' => "فعال"
			),
			array(
				'id' => 0,
				'name' => 'غیر فعال',
			),
		),
	),
	
	'bannersTypes'=>array(
		'name'=>'',
		'title'=>'نوع آگهی',
		'title_en' => '',
		'columns'=>array(
			'id'=>'id',
			'name'=>'name',
			'nameEn' => 'name_en',
		),
		'rows'=>array(
			array(
				'id'=>'1',
				'name'=>'فایل flash',
				'name_en' => '',
			),
			array(
				'id'=>'2',
				'name'=>'تصویر',
				'name_en' => '',
			),
			/*array(
				'id'=>'3',
				'name'=>'متنی',
				'name_en' => '',
			),*/
		),
	),
	
	'bannersPlaces'=>array(
		'name'=>'',
		'title'=>'محل آگهی',
		'title_en' => '',
		'columns'=>array(
			'id'=>'id',
			'name'=>'name',
			'internalName' => 'internal_name',
		),
		'rows'=>array(
			array(
				'id'=>'1',
				'name'=>'بالا',
				'internal_name' => 'top',
			),
			array(
				'id'=>'2',
				'name'=>'وسط',
				'internal_name' => 'middle',
			),
			array(
				'id'=>'3',
				'name'=>'پایین',
				'internal_name' => 'bottom',
			),		  
			/*		  
			
			array(
				'id'=>'4',
				'name'=>'صفحه‌ی آرشيو - پائين',
				'internal_name' => 'archiveBottom',
			),
			array(
				'id'=>'5',
				'name'=>'صفحه‌ی متن مقاله - بالا',
				'internal_name' => 'fullTop',
			),
			array(
				'id'=>'6',
				'name'=>'صفحه‌ی متن مقاله - پائين',
				'internal_name' => 'fullBottom',
			),
			array(
				'id'=>'7',
				'name'=>'منوی سمت چپ - بالا',
				'internal_name' => 'leftTop',
			),
			array(
				'id'=>'8',
				'name'=>'منوی سمت چپ - پائين',
				'internal_name' => 'leftBottom',
			),*/
			
		),
	),
	
	'paymentMethods' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => '1',
				'name' => "نقدی همزمان"
			),
			array(
				'id' => '2',
				'name' => 'نقدی یک ماه بعد',
			),
			array(
				'id' => '3',
				'name' => 'چهار ماهه بودن پیش',
			),
		),
	),
	
	'branchCashTypes' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
		),
		'rows' => array(
			array(
				'id' => '5',
				'name' => "نزد شعبه"
			),
			array(
				'id' => '6',
				'name' => 'نزد عوامل',
			),
			array(
				'id' => '7',
				'name' => 'نزد ویزیتورها',
			),
			array(
				'id' => '8',
				'name' => 'تسویه نزد شعبه',
			),
		),
	),
	'videoTypes'=>array(
		'name'=>'product',
		'title' => 'انواع فيلدهای محصول',
		'columns'=>array(
			'id'=>'id',
			'tag'=>'tag',
			'name'=>'name',
		),
		'rows'=>array(
			array(
				'id'=>'1',
				'tag'=>'file',
				'name'=>'فایل',
			),
			array(
				'id'=>'2',
				'tag'=>'text',
				'name'=>'فایل بر روی یک سرور',
			),
			array(
				'id'=>'3',
				'tag'=>'textarea',
				'name'=>'جاگذاری تگ نمایش فیلم',
			)
		),
	),
	'surveyOptions' => array(
		'columns' => array(
			'id' => 'id',
			'name' => 'name',
			'nameEn'=>'nameEn'
		),
		'rows' => array(
			array(
				'id' => '1',
				'name' => "عالی",
				'nameEn'=> 'excellent'
			),
			array(
				'id' => '2',
				'name' => 'خوب',
				'nameEn'=> 'good'
			),
			array(
				'id' => '3',
				'name' => 'متوسط',
				'nameEn'=> 'moderate'
			),
			array(
				'id' => '4',
				'name' => 'معمولی',
				'nameEn'=> 'normal'
			),
			array(
				'id' => '5',
				'name' => 'ضعیف',
				'nameEn'=> 'weak'
			),
			
		),
	),
);

$_ws["physicalTables"]=array(
	'aboutUs'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'about_us',
		'originalTableName'=>'about_us',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'categoryId'=>'category_id',
			'categoryPath'=>'category_path',
			'photoFilename'=>'photo_filename',
			'publishDatetime'=>'publish_datetime',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'hit'=>'hit',
		)
	),
	'categories'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'categories',
		'originalTableName'=>'categories',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'name'=>'name',
			'nameEn'=>'name_en',
			'parentId'=>'parent_id',
			'lft'=>'lft',
			'rgt'=>'rgt',
			'level'=>'level',
			'link'=>'link',
			'linkEn'=>'link_en',
			'visible'=>'visible',
			'internalName'=>'internal_name',
			'leftVisible'=>'left_visible',
			'photo'=>'photo',
		)
	),
	'categoryLanguages'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'category_languages',
		'originalTableName'=>'category_languages',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'categoryId'=>'category_id',
			'languageId'=>'language_id',
			'name'=>'name',
			'referringId'=>'referring_id',
			'description'=>'description',
			'photoFilename'=>'photo_filename',
			'link'=>'link',
			'multimediaFileType'=>'multimedia_file_type',
		)
	),
	'contactUs'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'contact_us',
		'originalTableName'=>'contact_us',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'firstName'=>'first_name',
			'lastName'=>'last_name',
			'email'=>'email',
			'tel'=>'tel',
			'companyName'=>'company_name',
			'body'=>'body',
			'purchaseDate'=>'purchase_date',
			'address'=>'address',
			'categoryId'=>'category_id',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'contactUsCategories'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'contact_us_categories',
		'originalTableName'=>'contact_us_categories',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'surveyQuestions'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'survey_questions',
		'originalTableName'=>'survey_questions',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'active'=>'active',
			'rank'=>'rank',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'documents'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'documents',
		'originalTableName'=>'documents',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'photoFilename'=>'photo_filename',
			'filename'=>'filename',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'categoryId'=>'category_id',
			'productId'=>'product_id',
		)
	),
	'documentsCategories'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'documents_categories',
		'originalTableName'=>'documents_categories',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'photoFilename'=>'photo_filename',
			'publishDatetime'=>'publish_datetime',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'emailTemplates'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'email_templates',
		'originalTableName'=>'email_templates',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'internalName'=>'internal_name',
			'name'=>'name',
			'subject'=>'subject',
			'inlineSubject'=>'inline_subject',
			'body'=>'body',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'faq'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'faq',
		'originalTableName'=>'faq',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'question'=>'question',
			'answer'=>'answer',
			//'photoFilename'=>'photo_filename',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'homePagePhotos'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'home_page_photos',
		'originalTableName'=>'home_page_photos',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'photoFilename'=>'photo_filename',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'visible'=>'visible',
		)
	),
	'languages'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'languages',
		'originalTableName'=>'languages',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'name'=>'name',
			'englishName'=>'english_name',
			'direction'=>'direction',
			'shortName'=>'short_name',
			'align'=>'align',
			'calendarType'=>'calendar_type',
		)
	),
	'logs'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'logs',
		'originalTableName'=>'logs',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'sectionName'=>'section_name',
			'userId'=>'user_id',
			'title'=>'title',
			'occurrenceDateTime'=>'occurrence_date_time',
			'ip'=>'ip',
		)
	),
	'licences'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'licences',
		'originalTableName'=>'licences',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'photoFilename'=>'photo_filename',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'news'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'news',
		'originalTableName'=>'news',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'publishDatetime'=>'publish_datetime',
			'photoFilename'=>'photo_filename',
			'jalaliYear'=>'jalali_year',
			'jalaliMonth'=>'jalali_month',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'categoryId'=>'category_id',
			'briefBody'=>'brief_body',
			'archive'=>'archive',
			'important'=>'important',
			'sendNewsletter'=>'send_newsletter',
			'overTitle'=>'over_title',
			'relatedNewsId'=>'related_news_id',
			'sentByNewsletter'=>'sent_by_newsletter',
			'userTotalScore'=>'user_total_score',
			'orderNumber'=>'order_number',
			'hit'=>'hit',
		)
	),
	'newsCategories'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'news_categories',
		'originalTableName'=>'news_categories',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'photoFilename'=>'photo_filename',
			'relatedItem'=>'related_item',
			'languageId'=>'language_id',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'orderDetails'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'order_details',
		'originalTableName'=>'order_details',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'relatedOrder'=>'related_order',
			'productId'=>'product_id',
			'orderDescription'=>'order_description',
		)
	),
	'orders'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'orders',
		'originalTableName'=>'orders',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'customerId'=>'customer_id',
			'sellingAgent'=>'selling_agent',
			'receipantFullName'=>'receipant_full_name',
			'receipantAddress'=>'receipant_address',
			'receipantTel'=>'receipant_tel',
			'confirmed'=>'confirmed',
			'orderHash'=>'order_hash',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	
	'surveyDetails'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'survey_details',
		'originalTableName'=>'survey_details',
		'surveyType'=>'DESC',
		'surveyByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'surveyId'=>'survey_id',
			'questionId'=>'question_id',
			'answer'=>'answer',
		)
	),
	'surveys'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'surveys',
		'originalTableName'=>'surveys',
		'surveyType'=>'DESC',
		'surveyByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'receipantFullName'=>'receipant_full_name',
			'receipantEmail'=>'receipant_email',
			'confirmed'=>'confirmed',
			'surveyHash'=>'survey_hash',
			'relatedItem'=>'related_item',
			'languageId'=>'language_id',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'products'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'products',
		'originalTableName'=>'products',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'generalCharacteristics'=>'general_characteristics',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'categoryId'=>'category_id',
			'categoryPath'=>'category_path',
			'photoFilename'=>'photo_filename',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'orderNumber'=>'order_number',
			'relatedStandards'=>'related_standards',
		)
	),
	'productsImages'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'products_images',
		'originalTableName'=>'products_images',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'productId'=>'product_id',
			'title'=>'title',
			'titleEn'=>'title_en',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'image'=>'image',
		)
	),
	'provinces'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'provinces',
		'originalTableName'=>'provinces',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'name'=>'name',
			'nameEn'=>'name_en',
			'photoFilename'=>'photo_filename',
			'postPrice'=>'post_price',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'relatedItem'=>'related_item',
			'languageId'=>'language_id',
		)
	),
	'sessions'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'sessions',
		'originalTableName'=>'sessions',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'sessionId'=>'session_id',
			'httpUserAgent'=>'http_user_agent',
			'sessionData'=>'session_data',
			'sessionExpire'=>'session_expire',
		)
	),
	'settings'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'settings',
		'originalTableName'=>'settings',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'section'=>'section',
			'title'=>'title',
			'key'=>'key',
			'value'=>'value',
			'inputType'=>'input_type',
		)
	),
	'siteSections'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'site_sections',
		'originalTableName'=>'site_sections',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'internalName'=>'internal_name',
			'name'=>'name',
			'type'=>'type',
		)
	),
	'staticPages'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'static_pages',
		'originalTableName'=>'static_pages',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'internalName'=>'internal_name',
			'photoFilename'=>'photo_filename',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'hit'=>'hit',
		)
	),
	'standards'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'standards',
		'originalTableName'=>'standards',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'photoFilename'=>'photo_filename',
			'related_tests'=>'relatedTests',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'tests'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'tests',
		'originalTableName'=>'tests',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'photoFilename'=>'photo_filename',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
		)
	),
	'userGroups'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'user_groups',
		'originalTableName'=>'user_groups',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'name'=>'name',
			'permissions'=>'permissions',
			'type'=>'type',
			'internalName'=>'internal_name',
		)
	),
	'users'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'users',
		'originalTableName'=>'users',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'username'=>'username',
			'password'=>'password',
			'encryptedPassword'=>'encrypted_password',
			'firstName'=>'first_name',
			'lastName'=>'last_name',
			'fullName'=>'full_name',
			'email'=>'email',
			'activated'=>'activated',
			'loginDatetime'=>'login_datetime',
			'registerDatetime'=>'register_datetime',
			'lastLoginDatetime'=>'last_login_datetime',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'lastLockDatetime'=>'last_lock_datetime',
			'loginAttempts'=>'login_attempts',
			'lock'=>'lock',
			'ip'=>'ip',
			'permissions'=>'permissions',
			'userGroupId'=>'user_group_id',
			'activationCode'=>'activation_code',
			'editorId'=>'editor_id',
		)
	),
	'webUsers'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'web_users',
		'originalTableName'=>'web_users',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'username'=>'username',
			'password'=>'password',
			'encryptedPassword'=>'encrypted_password',
			'firstName'=>'first_name',
			'lastName'=>'last_name',
			'fullName'=>'full_name',
			'gender'=>'gender',
			'birthdayDate'=>'birthday_date',
			'activated'=>'activated',
			'loginDatetime'=>'login_datetime',
			'registerDatetime'=>'register_datetime',
			'lastLoginDatetime'=>'last_login_datetime',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'lastLockDatetime'=>'last_lock_datetime',
			'loginAttempts'=>'login_attempts',
			'lock'=>'lock',
			'ip'=>'ip',
			'permissions'=>'permissions',
			'userGroupId'=>'user_group_id',
			'activationCode'=>'activation_code',
			'email'=>'email',
			'tel'=>'tel',
			'fax'=>'fax',
			'mobile'=>'mobile',
			'provinceId'=>'province_id',
			'address'=>'address',
			'photoFilename'=>'photo_filename',
		)
	),
	'words'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'words',
		'originalTableName'=>'words',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'key'=>'key',
			'value'=>'value',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'totalRequest'=>'total_request',
			'sectionNames'=>'section_names',
		)
	),
	'ourCustomers'=>array(
		'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'our_customers',
		'originalTableName'=>'our_customers',
		'orderType'=>'DESC',
		'orderByColumnName'=>'id',
		'columns'=>array(
			'id'=>'id',
			'title'=>'title',
			'body'=>'body',
			'languageId'=>'language_id',
			'relatedItem'=>'related_item',
			'categoryId'=>'category_id',
			'categoryPath'=>'category_path',
			'photoFilename'=>'photo_filename',
			'publishDatetime'=>'publish_datetime',
			'insertDatetime'=>'insert_datetime',
			'updateDatetime'=>'update_datetime',
			'hit'=>'hit',
		)
	),
);