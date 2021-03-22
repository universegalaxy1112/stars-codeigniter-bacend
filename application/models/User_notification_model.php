<?php

class User_notification_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model', 'users');
        $this->load->model('Notification_model', 'notifications');
    }

    public $belongs_to = array(
        'user' => array(
            'model' => 'user_model',
            'primary_key' => 'user_id'
        ),
        'notification' => array(
            'model' => 'notification_model',
            'primary_key' => 'notification_id'
        )
    );

    protected $order_by = array('created_at', 'DESC');

    // Append tags
    protected function callback_after_get($result)
    {
        $result = parent::callback_after_get($result);

        if (!empty($result)) {
            $result->user = $this->users->get($result->user_id);
            $result->notification = $this->notifications->get($result->notification_id);
        }

        return $result;
    }

    public function get_user_notifications($user_id)
    {
        $user = $this->users->get($user_id);
        $user_created_at = $user->created_on;

        $user_notifications = $this->get_where(array('user_id' => $user_id, 'is_deleted' => true));
        if (count($user_notifications) > 0) {
            $ids = $user_notifications[0]->notification_id;
            foreach ($user_notifications as $user_notification) {
                $ids .= ',' . $user_notification->notification_id;
            }
            $query = "SELECT * FROM notifications WHERE created_at > $user_created_at and id NOT IN ($ids) ORDER BY created_at DESC";

        } else {
            $query = "SELECT * FROM notifications WHERE created_at > $user_created_at ORDER BY created_at DESC";
        }
        $notifications = $this->db->query($query)->result();

        $data = [];
        $user_notifications = $this->get_where(array('user_id' => $user_id, 'is_deleted' => false));
        foreach ($notifications as $notification) {
            $is_read = 0;
            foreach ($user_notifications as $user_notification) {
                if ($user_notification->notification_id == $notification->id) {
                    $is_read = $user_notification->is_read;
                }
            }
            $notification->is_read = $is_read;

            $data[] = $notification;
        }

        return $data;
    }

}