<?php

/**
 * Config file for form validation
 * Reference: http://www.codeigniter.com/user_guide/libraries/form_validation.html
 * (Under section "Creating Sets of Rules")
 */

$config = array(

	// Admin User Login
	'login/index' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
	),

	// Create User
	'user/create' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'is_unique[users.username]',				// use email as username if empty
		),
		array(
			'field'		=> 'email',
			'label'		=> 'Email',
			'rules'		=> 'required|valid_email|is_unique[users.email]',
		)
	),

	// Reset User Password
	'user/reset_password' => array(
		array(
			'field'		=> 'new_password',
			'label'		=> 'New Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[new_password]',
		),
	),

	// Create Admin User
	'panel/admin_user_create' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'required|is_unique[users.username]',
		),
		array(
			'field'		=> 'first_name',
			'label'		=> 'First Name',
			'rules'		=> 'required',
		),
		/* Admin User can have no email
		array(
			'field'		=> 'email',
			'label'		=> 'Email',
			'rules'		=> 'valid_email|is_unique[users.email]',
		),*/
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[password]',
		),
	),

	// Reset Admin User Password
	'panel/admin_user_reset_password' => array(
		array(
			'field'		=> 'new_password',
			'label'		=> 'New Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[new_password]',
		),
	),

	// Admin User Update Info
	'panel/account_update_info' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
	),

	// Admin User Change Password
	'panel/account_change_password' => array(
		array(
			'field'		=> 'new_password',
			'label'		=> 'New Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[new_password]',
		),
	),

    // Setting constants
    'settings/settings' => array(
        array(
            'field'		=> 'about',
            'label'		=> 'About',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'mnemonic',
            'label'		=> 'Mnemonic',
            'rules'		=> 'required',
        ),
    ),

    // Create notification
    'notification/create_notification' => array(
        array(
            'field'		=> 'message',
            'label'		=> 'Message',
            'rules'		=> 'required',
        ),
    ),

    // Setting constants
    'vignette/add_part3_questions' => array(
        array(
            'field'		=> 'title',
            'label'		=> 'Title',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'story',
            'label'		=> 'Story',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'study',
            'label'		=> 'Study',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'question1',
            'label'		=> 'Question1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'question2',
            'label'		=> 'Question2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'question3',
            'label'		=> 'Question3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_a1',
            'label'		=> 'Option_a1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_a2',
            'label'		=> 'Option_a2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_a3',
            'label'		=> 'Option_a3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_b1',
            'label'		=> 'Option_b1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_b2',
            'label'		=> 'Option_b2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_b3',
            'label'		=> 'Option_b3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_c1',
            'label'		=> 'Option_c1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_c2',
            'label'		=> 'Option_c2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_c3',
            'label'		=> 'Option_c3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_d1',
            'label'		=> 'Option_d1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_d2',
            'label'		=> 'Option_d2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_d3',
            'label'		=> 'Option_d3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_e1',
            'label'		=> 'Option_e1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_e2',
            'label'		=> 'Option_e2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_e3',
            'label'		=> 'Option_e3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_f1',
            'label'		=> 'Option_f1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_f2',
            'label'		=> 'Option_f2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_f3',
            'label'		=> 'Option_f3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_g1',
            'label'		=> 'Option_g1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_g2',
            'label'		=> 'Option_g2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_g3',
            'label'		=> 'Option_g3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_h1',
            'label'		=> 'Option_h1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_h2',
            'label'		=> 'Option_h2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'option_h3',
            'label'		=> 'Option_h3',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'answer1',
            'label'		=> 'Answer1',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'answer2',
            'label'		=> 'Answer2',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'answer3',
            'label'		=> 'Answer3',
            'rules'		=> 'required',
        ),
    ),

);