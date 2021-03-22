<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| CI Bootstrap 3 Configuration
| -------------------------------------------------------------------------
| This file lets you define default values to be passed into views 
| when calling MY_Controller's render() function. 
| 
| See example and detailed explanation from:
| 	/application/config/ci_bootstrap_example.php
*/

$config['ci_bootstrap'] = array(

	// Site name
	'site_name' => 'Admin Panel',

	// Default page title prefix
	'page_title_prefix' => '',

	// Default page title
	'page_title' => '',

	// Default meta data
	'meta_data'	=> array(
		'author'		=> '',
		'description'	=> '',
		'keywords'		=> ''
	),
	
	// Default scripts to embed at page head or end
	'scripts' => array(
		'head'	=> array(
			'assets/dist/admin/adminlte.min.js',
			'assets/dist/admin/lib.min.js',
			'assets/dist/admin/app.min.js'
		),
		'foot'	=> array(
		),
	),

	// Default stylesheets to embed at page head
	'stylesheets' => array(
		'screen' => array(
			'assets/dist/admin/adminlte.min.css',
			'assets/dist/admin/lib.min.css',
			'assets/dist/admin/app.min.css'
		)
	),

	// Default CSS class for <body> tag
	'body_class' => '',
	
	// Multilingual settings
	'languages' => array(
	),

	// Menu items
	'menu' => array(
		'home' => array(
			'name'		=> 'Home',
			'url'		=> '',
			'icon'		=> 'fa fa-home',
		),
		'user' => array(
			'name'		=> 'Users',
			'url'		=> 'user',
			'icon'		=> 'fa fa-users',
			'children'  => array(
				'List'			=> 'user',
                'Leaderboard'	=> 'user/leaderboard',
				'Create'		=> 'user/create',
			)
		),
		'quiz' => array(
            'name'		=> 'Quiz',
            'url'		=> 'quiz',
            'icon'		=> 'ion ion-edit',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                //'Dummy' 		=> 'quiz/dummy',
                'Parts'		    => 'quiz/parts',
                'Categories'	=> 'quiz/category',
                'Quizzes'		=> 'quiz/quizzes',
                'Questions'		=> 'quiz/post',
            )
        ),
        'vignette' => array(
            'name'		=> 'Vignette',
            'url'		=> 'vignette',
            'icon'		=> 'ion ion-edit',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                //'Dummy' 		=> 'quiz/dummy',
                'Stories'		=> 'vignette/story',
                'Questions'		=> 'vignette/questions'
            )
        ),
        'exam' => array(
            'name'		=> 'Exam',
            'url'		=> 'exam',
            'icon'		=> 'ion ion-edit',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                'Exams'		=> 'exam/exams',
                'Questions'		=> 'exam/post',
            )
        ),
        'palooza' => array(
            'name'		=> 'Palooza',
            'url'		=> 'palooza',
            'icon'		=> 'ion ion-edit',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                'Question Palooza'		=> 'palooza/question_questions',
                'Triad Palooza'		=> 'palooza/triad_questions',
            )
        ),
        'other' => array(
            'name'		=> 'Other',
            'url'		=> 'other',
            'icon'		=> 'ion ion-edit',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                'Study Guides'	=> 'other/study',
            )
        ),
        'notification' => array(
            'name'		=> 'Send Notification',
            'url'		=> 'notification',
            'icon'		=> 'fa fa-send',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                'Sent Notifications'		=> 'notification',
                'Create Notification'		=> 'notification/create_notification'
            )
        ),
		'util' => array(
			'name'		=> 'Utilities',
			'url'		=> 'util',
			'icon'		=> 'fa fa-cogs',
			'children'  => array(
			    'Quotes'        => 'settings/quotes',
                'Settings'	    => 'settings/',
			)
		),
		'logout' => array(
			'name'		=> 'Sign Out',
			'url'		=> 'panel/logout',
			'icon'		=> 'fa fa-sign-out',
		)
	),

	// Login page
	'login_url' => 'admin/login',

	// Restricted pages
	'page_auth' => array(
		'user/create'				=> array('webmaster', 'admin', 'manager'),
		'user/group'				=> array('webmaster', 'admin', 'manager'),
		'panel'						=> array('webmaster'),
		'panel/admin_user'			=> array('webmaster'),
		'panel/admin_user_create'	=> array('webmaster'),
		'panel/admin_user_group'	=> array('webmaster'),
		'util'						=> array('webmaster'),
		'util/list_db'				=> array('webmaster'),
		'util/backup_db'			=> array('webmaster'),
		'util/restore_db'			=> array('webmaster'),
		'util/remove_db'			=> array('webmaster'),
	),

	// AdminLTE settings
	'adminlte' => array(
		'body_class' => array(
			'webmaster'	=> 'skin-red',
			'admin'		=> 'skin-purple',
			'manager'	=> 'skin-black',
			'staff'		=> 'skin-blue',
		)
	),

	// Useful links to display at bottom of sidemenu
	'useful_links' => array(
	),

	// Debug tools
	'debug' => array(
		'view_data'	=> FALSE,
		'profiler'	=> FALSE
	),
);

/*
| -------------------------------------------------------------------------
| Override values from /application/config/config.php
| -------------------------------------------------------------------------
*/
$config['sess_cookie_name'] = 'ci_session_admin';