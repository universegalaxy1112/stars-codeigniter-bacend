<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_builder');

        $this->load->model('Notification_model', 'notifications');
        $this->load->model('User_notification_model', 'userNotifications');
        $this->load->model('User_unread_pn_count_model', 'user_unread_pn_counts');
        $this->load->model('User_push_token_model', 'userPushTokens');
    }

	public function index()
	{
        $crud = $this->generate_crud('notifications');
        $crud->columns('content', 'created_at');
        $this->unset_crud_fields('sender_id', 'receiver_id', 'type', 'title');
        $crud->display_as('content', 'Message');
        $crud->where('notification_status', 1);

        $state = $crud->getState();
        $this->unset_crud_fields('notification_status');
        $crud->unset_add();
        $crud->unset_edit();
        $crud->callback_delete(array($this, 'delete_notification'));

        $this->mPageTitle = 'Sent Notifications';
        $this->render_crud();
	}

    public function delete_notification($primary_key) {
        return $this->notifications->update_field($primary_key, 'notification_status', 0);
    }

    // Create Admin User
    public function create_notification()
    {
        $form = $this->form_builder->create_form();
        if ($form->validate()) {
            // passed validation
            $message = $this->input->post('message');
            $now = date("Y-m-d H:i:s");

            $data = array(
                'title' => APP_NAME,
                'content' => $message,
                'created_at' => $now
            );

            $response = $this->sendPushMessage(APP_NAME, $message);
            if (!$response) {
                $this->system_message->set_error("No users to receive push");

            } else {
                $resultObject = json_decode($response);
                //print json_encode($resultObject);
                if(isset($resultObject->recipients) && isset($resultObject->id)) {
                    $messages = "Sent Successfully";
                    $this->system_message->set_success($messages);
                    $new_id = $this->notifications->insert($data);

                    $users = $this->users->get_all();
                    foreach ($users as $user) {
                        $this->get_user_unread_notification_count($user->id);
                    }

                } else {
                    $errors = "Failed";
                    $this->system_message->set_error($errors);
                }
            }

            refresh();
        }

        $this->mPageTitle = 'Create Notification';
        $this->mViewData['form'] = $form;
        $this->render('notification/notification_create');
    }

    public function get_user_unread_notification_count ($user_id) {
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

    function sendPushMessage($messageTitle, $message) {
        $notification_data = $this->notification_data(array("foo" => "bar"), $message, $messageTitle);
        $userPushTokens = $this->userPushTokens->get_all();
        $one_signal_ids = [];
        foreach ($userPushTokens as $userPushToken) {
            if($userPushToken->status == 1) $one_signal_ids[] = $userPushToken->one_signal_id;
        }
        if (count($one_signal_ids)>0) {
            return $this->send_push_notification_by_devices($one_signal_ids, $notification_data);
        } else {
            return false;
        }
    }

}
