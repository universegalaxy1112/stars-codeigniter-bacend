<?php 

class User_push_token_model extends MY_Model {

    protected function callback_after_get($result)
    {
        return $result;
    }

    public function get_users_tokens($user_ids) {
        $str_user_ids = implode(",", $user_ids);
        $query = "SELECT * FROM user_push_tokens WHERE user_id in ($str_user_ids)";
        $result = $this->db->query($query)->result();

        return $result;
    }

}