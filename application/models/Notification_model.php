<?php 

class Notification_model extends MY_Model {

    //protected $where = array('notification_status' => 'active');
	protected $order_by = array('created_at', 'DESC');

	// Append tags
	protected function callback_after_get($result)
	{
        if (!empty($result)) {
            $sender_id = $result->sender_id;
            $sender = $this->users->get($sender_id);
            if ($sender) {
                $result->sender_avatar_url = $sender->photo;
                $result->sender_full_name = $sender->full_name;
            }
        }
		return $result;
	}

}