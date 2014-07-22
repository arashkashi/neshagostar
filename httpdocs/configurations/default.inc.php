<?php
/**
 * Main configuration of website, it should be only contain parameters.<br>
 * defining or initializing objects should be in preparing.inc.php<br>
 * have a look at source code for more information
 * 
 * @author Sina Salek
 * @package [D]/admin/requirements/configurations
 */
 

/**
* Most of the time website needs different configuration on online and offline server.
* by configuring below parameters it will be possible to switch between online an offline
* configuration via changing $isOnTheOnlineServer value to false or true
* 
* @package admin/requirements
*/
$__config=array(
        'siteInfo'=>array(
                //'url'             => 'http://www.neshagostar.com/' , // with trailing Slash !
                //'path'            => '/var/www/vhosts/neshagostar.com/httpdocs/' ,
                //'domain'          => 'www.neshagostar.com',
                'title'           => 'نشاگستر پردیس',    // Website title
                'titleEn'         => 'Neshagostar Pardis',
                'defaultLanguage' => 'fa',
                'defaultViewLanguage' => 'fa',
        ),
        'controlPanelInfo'=>array(
                'title'              => 'نشاگستر پردیس مدیریت پند',
                'titleEn'            => 'Neshagostar Pardis'
        ),
        'databaseInfo'=>array(
                'host'=>'localhost', // The host where your database is
                'name'=>'neshagostar', // Your database name
                'username'=>'neshagostar', // The user name
                'password'=>'st2AurTOxW',  // The user's password
                'collation'=>'utf8', // if databse doesn't support collations just empty this options
                'tablesPrefix'=>'neshagostar_'
        ),
        'emailsInfo'=>array(
                'info' => 'info@neshagostar.com',
                'newsletter' => 'newsletter@neshagostar.com',
        ),
		'smtp'=>array(
			'host'=>'mail.neshagostar.com',
			//'username'=>'info@neshagostar.com',
			'password'=>'7xdc4PIW2kR',
			'port'=>'25'
		),

        'debugModeEnabled'=>false,
);
/**
$__config=array(
	'siteInfo'=>array(
		//'url'             => 'http://neshagostarnline.pt2.com/' , // with trailing Slash !
		//'path'            => '/home/neshagostarnline/public_html/' ,
		//'domain'          => 'neshagostarnline.pt2.com',
		'title'           => 'پند (آفلاين)',    // Website title
		'titleEn'         => 'neshagostar (Offline)',	
		'defaultLanguage' => 'fa',
		'defaultViewLanguage' => 'fa',
	),
	'controlPanelInfo'=>array(
		'title'              => 'پنل مدیریت پند',
		'titleEn'            => 'neshagostar (Offline)'
	),
	'databaseInfo'=>array(
		'host'=>'localhost', // The host where your database is
		'name'=>'neshagostar', // Your database name
		'username'=>'neshagostar', // The user name
		'password'=>'neshagostar',  // The user's password
		'collation'=>'utf8', // if databse doesn't support collations just empty this options
		'tablesPrefix'=>'neshagostar_'
	),
	'emailsInfo'=>array(
		'info' => 'neshagostar@pt2.com',
		'newsletter' => 'neshagostar@pt2.com',
	),
	'smtp'=>array(
		//'host'=>'localhost',
		//'username'=>'myname',
		//'password'=>'mypass',
		//'port'=>'25'
	),
	'debugModeEnabled'=>false,
);
*/
?>
