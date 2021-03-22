<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| Custom Constants (added by CI Bootstrap)
|--------------------------------------------------------------------------
| Constants to be used in both Frontend and other modules
|
*/
if (!(PHP_SAPI === 'cli' OR defined('STDIN')))
{
	// Base URL with directory support
	$protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!== 'off') ? 'https' : 'http';
	$base_url = $protocol.'://'.$_SERVER['HTTP_HOST'];
	$base_url.= dirname($_SERVER['SCRIPT_NAME']);
	define('BASE_URL', $base_url);
	
	// For API prefix in Swagger annotation (/application/modules/api/swagger/info.php)
	define('API_HOST', $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
}

define('APP_NAME',	                        'ChiroBoardReview');
define('CI_BOOTSTRAP_REPO',			'https://github.com/waifung0207/ci_bootstrap_3');
define('CI_BOOTSTRAP_VERSION',		'Build 20161209');	// will follow semantic version (e.g. v1.x.x) after first stable launch

// Upload paths
define('UPLOAD_COVER_PHOTO',	            'assets/uploads/cover_photos');
define('UPLOAD_BLOG_POST',		            'assets/uploads/blog_posts');
define('UPLOAD_PROFILE_PHOTO',	            'assets/uploads/profile_photos/');
define('UPLOAD_VIDEO_THUMB',	            'assets/uploads/video_thumbs/');
define('UPLOAD_PROFILE_FILTERED',	        'assets/uploads/profile_filtered/');
define('UPLOAD_CATEGORY_IMAGE',	            'assets/uploads/category_images/');
define('UPLOAD_QUESTION_IMAGE',	            'assets/uploads/question_images/');
define('UPLOAD_STUDY_IMAGE',	            'assets/uploads/study_images/');
define('UPLOAD_FILE_EXCEL',	                'assets/uploads/file_excel/');

// One signal
define('ONE_SIGNAL_API',                    'https://onesignal.com/api/v1/notifications');
define('API_CONTENT_TYPE',                  'Content-Type: application/json; charset=utf-8');
define('ONE_SIGNAL_AUTHORIZATION',          'Authorization: Basic MThmMzE0MjAtYjlkYS00ZWJjLWFjYjQtNjU1ZjEwMTgyZjA4');
define('ONE_SIGNAL_APP_ID',                 '26974209-5e4f-40e7-a8ec-732b81998f01');
define('ONE_SIGNAL_SMALL_ICON',             'ic_notification');
define('ONE_SIGNAL_LARGE_ICON',             'ic_large_notification');

// ActiveCompaign
define('ACTIVE_COMPAIGN_URL',               'https://anchoredlifestyles.activehosted.com/admin/api.php?api_action=contact_add&api_key=53bd8e63af12a34750daf4aa9788ba22b4c8c9f05e0b89dfcad44e5003d193c2ddb4e13d&api_output=json');
define('AC_CONTENT_TYPE',                   'Content-Type: application/x-www-form-urlencoded');
