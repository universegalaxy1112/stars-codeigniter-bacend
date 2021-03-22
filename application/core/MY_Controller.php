<?php

/**
 * Base controllers for different purposes
 * 	- MY_Controller: for Frontend Website
 * 	- Admin_Controller: for Admin Panel (require login), extends from MY_Controller
 * 	- API_Controller: for API Site, extends from REST_Controller
 */
class MY_Controller extends MX_Controller {
	
	// Values to be obtained automatically from router
	protected $mModule = '';			// module name (empty = Frontend Website)
	protected $mCtrler = 'home';		// current controller
	protected $mAction = 'index';		// controller function being called
	protected $mMethod = 'GET';			// HTTP request method

	// Config values from config/ci_bootstrap.php
	protected $mConfig = array();
	protected $mBaseUrl = array();
	protected $mSiteName = '';
	protected $mMetaData = array();
	protected $mScripts = array();
	protected $mStylesheets = array();

	// Values and objects to be overrided or accessible from child controllers
	protected $mPageTitlePrefix = '';
	protected $mPageTitle = '';
	protected $mBodyClass = '';
	protected $mMenu = array();
	protected $mBreadcrumb = array();

	// Multilingual
	protected $mMultilingual = FALSE;
	protected $mLanguage = 'en';
	protected $mAvailableLanguages = array();

	// Data to pass into views
	protected $mViewData = array();

	// Login user
	protected $mPageAuth = array();
	protected $mUser = NULL;
	protected $mUserGroups = array();
	protected $mUserMainGroup;

	// Constructor
	public function __construct()
	{
		parent::__construct();

		// router info
		$this->mModule = $this->router->fetch_module();
		$this->mCtrler = $this->router->fetch_class();
		$this->mAction = $this->router->fetch_method();
		$this->mMethod = $this->input->server('REQUEST_METHOD');
		
		// initial setup
		$this->_setup();
	}

	// Setup values from file: config/ci_bootstrap.php
	private function _setup()
	{
		$config = $this->config->item('ci_bootstrap');
		
		// load default values
		$this->mBaseUrl = empty($this->mModule) ? base_url() : base_url($this->mModule).'/';
		$this->mSiteName = empty($config['site_name']) ? '' : $config['site_name'];
		$this->mPageTitlePrefix = empty($config['page_title_prefix']) ? '' : $config['page_title_prefix'];
		$this->mPageTitle = empty($config['page_title']) ? '' : $config['page_title'];
		$this->mBodyClass = empty($config['body_class']) ? '' : $config['body_class'];
		$this->mMenu = empty($config['menu']) ? array() : $config['menu'];
		$this->mMetaData = empty($config['meta_data']) ? array() : $config['meta_data'];
		$this->mScripts = empty($config['scripts']) ? array() : $config['scripts'];
		$this->mStylesheets = empty($config['stylesheets']) ? array() : $config['stylesheets'];
		$this->mPageAuth = empty($config['page_auth']) ? array() : $config['page_auth'];

		// multilingual setup
		$lang_config = empty($config['languages']) ? array() : $config['languages'];
		if ( !empty($lang_config) )
		{
			$this->mMultilingual = TRUE;
			$this->load->helper('language');

			// redirect to Home page in default language
			if ( empty($this->uri->segment(1)) )
			{
				$home_url = base_url($lang_config['default']).'/';
				redirect($home_url);
			}

			// get language from URL, or from config's default value (in ci_bootstrap.php)
			$this->mAvailableLanguages = $lang_config['available'];
			$language = array_key_exists($this->uri->segment(1), $this->mAvailableLanguages) ? $this->uri->segment(1) : $lang_config['default'];

			// append to base URL
			$this->mBaseUrl.= $language.'/';

			// autoload language files
			foreach ($lang_config['autoload'] as $file)
				$this->lang->load($file, $this->mAvailableLanguages[$language]['value']);

			$this->mLanguage = $language;
		}
		
		// restrict pages
		$uri = ($this->mAction=='index') ? $this->mCtrler : $this->mCtrler.'/'.$this->mAction;
		if ( !empty($this->mPageAuth[$uri]) && !$this->ion_auth->in_group($this->mPageAuth[$uri]) )
		{
			$page_404 = $this->router->routes['404_override'];
			$redirect_url = empty($this->mModule) ? $page_404 : $this->mModule.'/'.$page_404;
			redirect($redirect_url);
		}

		// push first entry to breadcrumb
		if ($this->mCtrler!='home')
		{
			$page = $this->mMultilingual ? lang('home') : 'Home';
			$this->push_breadcrumb($page, '');
		}

		// get user data if logged in
		if ( $this->ion_auth->logged_in() )
		{
			$this->mUser = $this->ion_auth->user()->row();
			if ( !empty($this->mUser) )
			{
				$this->mUserGroups = $this->ion_auth->get_users_groups($this->mUser->id)->result();

				// TODO: get group with most permissions (instead of getting first group)
				$this->mUserMainGroup = $this->mUserGroups[0]->name;	
			}
		}

		$this->mConfig = $config;
	}

	// Verify user login (regardless of user group)
	protected function verify_login($redirect_url = NULL)
	{
		if ( !$this->ion_auth->logged_in() )
		{
			if ( $redirect_url==NULL )
				$redirect_url = $this->mConfig['login_url'];

			redirect($redirect_url);
		}
	}

	// Verify user authentication
	// $group parameter can be name, ID, name array, ID array, or mixed array
	// Reference: http://benedmunds.com/ion_auth/#in_group
	protected function verify_auth($group = 'members', $redirect_url = NULL)
	{
		if ( !$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group) )
		{
			if ( $redirect_url==NULL )
				$redirect_url = $this->mConfig['login_url'];
			
			redirect($redirect_url);
		}
	}

	// Add script files, either append or prepend to $this->mScripts array
	// ($files can be string or string array)
	protected function add_script($files, $append = TRUE, $position = 'foot')
	{
		$files = is_string($files) ? array($files) : $files;
		$position = ($position==='head' || $position==='foot') ? $position : 'foot';

		if ($append)
			$this->mScripts[$position] = array_merge($this->mScripts[$position], $files);
		else
			$this->mScripts[$position] = array_merge($files, $this->mScripts[$position]);
	}

	// Add stylesheet files, either append or prepend to $this->mStylesheets array
	// ($files can be string or string array)
	protected function add_stylesheet($files, $append = TRUE, $media = 'screen')
	{
		$files = is_string($files) ? array($files) : $files;

		if ($append)
			$this->mStylesheets[$media] = array_merge($this->mStylesheets[$media], $files);
		else
			$this->mStylesheets[$media] = array_merge($files, $this->mStylesheets[$media]);
	}

	// Render template
	protected function render($view_file, $layout = 'default')
	{
		// automatically generate page title
		if ( empty($this->mPageTitle) )
		{
			if ($this->mAction=='index')
				$this->mPageTitle = humanize($this->mCtrler);
			else
				$this->mPageTitle = humanize($this->mAction);
		}

		$this->mViewData['module'] = $this->mModule;
		$this->mViewData['ctrler'] = $this->mCtrler;
		$this->mViewData['action'] = $this->mAction;

		$this->mViewData['site_name'] = $this->mSiteName;
		$this->mViewData['page_title'] = $this->mPageTitlePrefix.$this->mPageTitle;
		$this->mViewData['current_uri'] = empty($this->mModule) ? uri_string(): str_replace($this->mModule.'/', '', uri_string());
		$this->mViewData['meta_data'] = $this->mMetaData;
		$this->mViewData['scripts'] = $this->mScripts;
		$this->mViewData['stylesheets'] = $this->mStylesheets;
		$this->mViewData['page_auth'] = $this->mPageAuth;

		$this->mViewData['base_url'] = $this->mBaseUrl;
		$this->mViewData['menu'] = $this->mMenu;
		$this->mViewData['user'] = $this->mUser;
		$this->mViewData['ga_id'] = empty($this->mConfig['ga_id']) ? '' : $this->mConfig['ga_id'];
		$this->mViewData['body_class'] = $this->mBodyClass;

		// automatically push current page to last record of breadcrumb
		$this->push_breadcrumb($this->mPageTitle);
		$this->mViewData['breadcrumb'] = $this->mBreadcrumb;

		// multilingual
		$this->mViewData['multilingual'] = $this->mMultilingual;
		if ($this->mMultilingual)
		{
			$this->mViewData['available_languages'] = $this->mAvailableLanguages;
			$this->mViewData['language'] = $this->mLanguage;
		}

		// debug tools - CodeIgniter profiler
		$debug_config = $this->mConfig['debug'];
		if (ENVIRONMENT==='development' && !empty($debug_config))
		{
			$this->output->enable_profiler($debug_config['profiler']);
		}

		$this->mViewData['inner_view'] = $view_file;
		$this->load->view('_base/head', $this->mViewData);
		$this->load->view('_layouts/'.$layout, $this->mViewData);

		// debug tools - display view data
		if (ENVIRONMENT==='development' && !empty($debug_config) && !empty($debug_config['view_data']))
		{
			$this->output->append_output('<hr/>'.print_r($this->mViewData, TRUE));
		}

		$this->load->view('_base/foot', $this->mViewData);
	}

	// Output JSON string
	protected function render_json($data, $code = 200)
	{
		$this->output
			->set_status_header($code)
			->set_content_type('application/json')
			->set_output(json_encode($data));
			
		// force output immediately and interrupt other scripts
		global $OUT;
		$OUT->_display();
		exit;
	}

	// Add breadcrumb entry
	// (Link will be disabled when it is the last entry, or URL set as '#')
	protected function push_breadcrumb($name, $url = '#', $append = TRUE)
	{
		$entry = array('name' => $name, 'url' => $url);

		if ($append)
			$this->mBreadcrumb[] = $entry;
		else
			array_unshift($this->mBreadcrumb, $entry);
	}

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////My code//////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function increase_and_get_user_unread_notification_count ($user_id) {
        $unread_count = 1;
        $user_unread_notification_count = $this->user_unread_pn_counts->get_first_one_where('user_id', $user_id);
        if($user_unread_notification_count) {
            $this->user_unread_pn_counts->increment_field($user_unread_notification_count->id, 'unread_count');
            $unread_count = $user_unread_notification_count->unread_count + 1;

        } else {
            $new_unread_count = array(
                'user_id' => $user_id,
                'unread_count' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_unread_pn_counts->insert($new_unread_count);
        }

        return $unread_count;
    }

    public function reset_user_unread_notification_count ($user_id) {
        $user_unread_notification_count = $this->user_unread_pn_counts->get_first_one_where('user_id', $user_id);
        if($user_unread_notification_count) {
            $this->user_unread_pn_counts->update_field($user_unread_notification_count->id, 'unread_count', 0);

        } else {
            $new_unread_count = array(
                'user_id' => $user_id,
                'unread_count' => 0,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_unread_pn_counts->insert($new_unread_count);
        }
    }

    public function send_push_notification($fields) {
        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ONE_SIGNAL_API);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(API_CONTENT_TYPE, ONE_SIGNAL_AUTHORIZATION));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function get_field($notification_data) {
        $fields = array(
            'app_id' => ONE_SIGNAL_APP_ID,
            'headings' => $notification_data['title'],
            'contents' => $notification_data['content'],
            'data' => $notification_data['data']
        );

        return $fields;
    }

    public function notification_data($data, $content = "Test", $title = APP_NAME) {
        $result = array(
            "title" => array( "en" => $title ),
            "content" => array( "en" => $content ),
            "data" => $data
        );

        return $result;
    }

    public function send_push_notification_all($notification_data) {
        $fields = $this->get_field($notification_data);
        $fields['included_segments'] = array('All');

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_filters($filters, $notification_data) {
        $fields = $this->get_field($notification_data);
        $fields['filters'] = $filters;

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_devices($user_one_signal_ids, $notification_data, $badge_count = 0) {
        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $user_one_signal_ids;
        /*if($badge_count==0) {
            $fields['ios_badgeType'] = 'Increase';
            $fields['ios_badgeCount'] = 1;

        } else {
            $fields['ios_badgeType'] = 'SetTo';
            $fields['ios_badgeCount'] = $badge_count;
        }*/

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_device($user_one_signal_id, $notification_data, $badge_count) {
        $device_ids[] = $user_one_signal_id;
        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $device_ids;
        $fields['ios_badgeType'] = 'SetTo';
        $fields['ios_badgeCount'] = $badge_count;

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_user($user_id, $notification_data) {
		$user_tokens = $this->user_push_tokens->get_where('user_id', $user_id);
        if(count($user_tokens)>0) {
            $device_ids = array();
            foreach ($user_tokens as $user_token) {
                $device_ids[] = $user_token->one_signal_id;
            }
            if(count($device_ids)>0) {
                $badge_count = $this->increase_and_get_user_unread_notification_count($user_id);
                //$thread_notification = new My_Notification(3, $device_ids, $notification_data, $badge_count);
                //$thread_notification->start();
                return $this->send_push_notification_by_devices($device_ids, $notification_data, $badge_count);

            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    public function send_email($recipients, $subject, $msg) {
        $mail = new PHPMailer;

        $mail->isSMTP();                    // Set mailer to use SMTP
        $mail->Host = SMTP_HOST;            // Specify main and backup SMTP servers
        $mail->Port = 587;                  // TCP port to connect to
        $mail->SMTPSecure = 'tls';          // Enable TLS encryption, `ssl` also accepted
        $mail->SMTPAuth = true;             // Enable SMTP authentication
        $mail->Username = SMTP_USERNAME;    // SMTP username
        $mail->Password = SMTP_PASSWORD;    // SMTP password

        foreach ($recipients as $recipient) {
            $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
            $mail->addAddress($recipient["email"], $recipient["name"]);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $msg;

            $mail->send();
        }
    }

    private function _random_filename()
    {
        $seedstr = explode(" ", microtime(), 5);
        $seed    = $seedstr[0] * 10000;
        srand($seed);
        $random  = rand(1000,10000);

        return date("YmdHis", time()) . $random;
    }

    function _getRandomHexString($length) {
        $characters = '0123456789abcdef';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function sendMessageToUser($message, $user_token) {
        if(strlen($user_token) > 0) {
            $fields = array(
                'to' => $user_token,
                'data' => array(
                    'message' => $message
                )
            );
            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FCM_MESSAGING_API);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(FCM_CONTENT_TYPE,
                FCM_SERVER_KEY));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);

        } else {
            $response = json_encode(array(
                "success"  => 0,
                "failure"  => 1
            ));
        }

        return $response;
    }

    function sendMessageForTopic($message, $topic = ""){
        if($topic != "") {
            $fields = array(
                'to' => "/topics/".$topic,
                'data' => array(
                    'message' => $message
                )
            );
            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FCM_MESSAGING_API);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(FCM_CONTENT_TYPE,
                FCM_SERVER_KEY));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);

        } else {
            $response = json_encode(array(
                "error"  => "Topic is not set",
            ));
        }

        return $response;
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

// include base controllers
require APPPATH."core/controllers/Admin_Controller.php";
require APPPATH."core/controllers/Api_Controller.php";