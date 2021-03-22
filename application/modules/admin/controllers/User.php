<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_builder');
	}

	// Frontend User CRUD
	public function index()
	{
		$crud = $this->generate_crud('users');
		$crud->columns('id', 'username', 'email', 'photo', 'score', 'active', 'free');
		$this->unset_crud_fields('ip_address', 'last_login', 'first_name', 'last_name');

        $state = $crud->getState();
        if ($state == "" || $state == 'list' || $state == 'success' || $state == 'ajax_list_info' || $state == 'ajax_list') { // || $state == 'read'
            $crud->callback_column('photo', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('photo', UPLOAD_PROFILE_PHOTO);
        }

		// disable direct create / delete Frontend User
		$crud->unset_add();
		$crud->unset_delete();

		$this->mPageTitle = 'Users';
		$this->render_crud();
	}

    public function callback_profile_photo($value, $row) {
        if(strlen($value)==0) {
            return "";
        }
        if (strpos($value, 'http') !== false) {
            return "<img style='width:50px; height:50px object-fit:cover' class='img-circle' src='".$value."'></>";

        } else {
            $photo = base_url() . UPLOAD_PROFILE_PHOTO . $value;
            return "<a href='". $photo ."' class='image-thumbnail'><img style='width:50px; height:50px; object-fit:cover' class='img-circle' src='".$photo."'/></a>";
        }
    }

	// Create Frontend User
	public function create()
	{
		$form = $this->form_builder->create_form(NULL, true);

		if ($form->validate())
		{
			// passed validation
            $filename = '';
            if(is_uploaded_file($_FILES['user_image']['tmp_name'])) {
                $path = UPLOAD_PROFILE_PHOTO;

                $milliseconds = round(microtime(true) * 1000);
                $filename = "profile_" . $milliseconds . '.png';
                $file_path = $path . $filename;

                $tmpFile = $_FILES['user_image']['tmp_name'];
                if(move_uploaded_file($tmpFile, $file_path)) {
                    //$this->system_message->set_success("Successfully uploaded");
                } else {
                    $filename = '';
                    //$this->system_message->set_error("Failed move");
                }
            }

			$username = $this->input->post('username');
			$email = $this->input->post('email');
			$password = 'password';
			$identity = empty($username) ? $email : $username;
			$additional_data = array(
				'first_name'	=> $this->input->post('first_name'),
				'last_name'		=> $this->input->post('last_name'),
                'photo'         => $filename
			);
            $groups =  array(1);

			// [IMPORTANT] override database tables to update Frontend Users instead of Admin Users
			$this->ion_auth_model->tables = array(
				'users'				=> 'users',
				'groups'			=> 'groups',
				'users_groups'		=> 'users_groups',
				'login_attempts'	=> 'login_attempts',
			);

			// proceed to create user
			$user_id = $this->ion_auth->register($identity, $password, $email, $additional_data, $groups);			
			if ($user_id)
			{
				// success
				$messages = $this->ion_auth->messages();
				$this->system_message->set_success($messages);

				// directly activate user
				$this->ion_auth->activate($user_id);
			}
			else
			{
				// failed
				$errors = $this->ion_auth->errors();
				$this->system_message->set_error($errors);
			}
			refresh();
		}

		// get list of Frontend user groups
		$this->load->model('group_model', 'groups');
		$this->mViewData['groups'] = $this->groups->get_all();
		$this->mPageTitle = 'Create User';

		$this->mViewData['form'] = $form;
		$this->render('user/create');
	}

	// User Groups CRUD
	public function group()
	{
		$crud = $this->generate_crud('groups');
		$this->mPageTitle = 'User Groups';
		$this->render_crud();
	}

	// Frontend User Reset Password
	public function reset_password($user_id)
	{
		// only top-level users can reset user passwords
		$this->verify_auth(array('webmaster', 'admin'));

		$form = $this->form_builder->create_form();
		if ($form->validate())
		{
			// pass validation
			$data = array('password' => $this->input->post('new_password'));
			
			// [IMPORTANT] override database tables to update Frontend Users instead of Admin Users
			$this->ion_auth_model->tables = array(
				'users'				=> 'users',
				'groups'			=> 'groups',
				'users_groups'		=> 'users_groups',
				'login_attempts'	=> 'login_attempts',
			);

			// proceed to change user password
			if ($this->ion_auth->update($user_id, $data))
			{
				$messages = $this->ion_auth->messages();
				$this->system_message->set_success($messages);
			}
			else
			{
				$errors = $this->ion_auth->errors();
				$this->system_message->set_error($errors);
			}
			refresh();
		}

		$this->load->model('user_model', 'users');
		$target = $this->users->get($user_id);
		$this->mViewData['target'] = $target;

		$this->mViewData['form'] = $form;
		$this->mPageTitle = 'Reset User Password';
		$this->render('user/reset_password');
	}

	public function leaderboard() {
        $crud = $this->generate_crud('leaderboard');
        $crud->columns('user_id', 'photo', 'quiz_id', 'score', 'date');
        $this->unset_crud_fields('formatdate');

        $crud->set_relation('user_id', 'users', 'username');
        $crud->set_relation('quiz_id', 'quizzes', 'title');
        $crud->callback_column('photo', array($this, 'callback_leaderboard_photo'));

        $crud->display_as('user_id', 'User');
        $crud->display_as('quiz_id', 'Quiz');

        $crud->unset_add();
        $crud->unset_read();
        $crud->unset_delete();
        $crud->unset_edit();

        $this->mPageTitle = 'Leaderboard';
        $this->render_crud();
    }

    public function callback_leaderboard_photo($value, $row) {
        $user = $this->users->get($row->user_id);
        if (strpos($user->photo, 'http') !== false) {
            return "<img style='width:50px; height:50px object-fit:cover' class='img-circle' src='".$user->photo."'></>";

        } else {
            $photo = base_url() . UPLOAD_PROFILE_PHOTO . $user->photo;
            return "<a href='". $photo ."' class='image-thumbnail'><img style='width:50px; height:50px; object-fit:cover' class='img-circle' src='".$photo."'/></a>";
        }
    }

}
