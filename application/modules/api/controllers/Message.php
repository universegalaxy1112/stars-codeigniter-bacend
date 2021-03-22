<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Message extends Base_Api_Controller
{
    public function get_room_list_post()
    {
        $user_id = $this->post('user_id');
        $page_number = $this->post('page_number');
        $count_per_page = $this->post('count_per_page');

        $limit = $count_per_page;
        $offset = ($page_number - 1) * $count_per_page;

        $query = "
            SELECT *
            FROM chat_messages
            WHERE ((sender_id = '$user_id' OR receiver_id = '$user_id') AND (id IN (
                SELECT MAX(id)
                FROM chat_messages
                GROUP BY room_id)))
            ORDER BY updated_at DESC
            LIMIT
            $offset, $limit
        ";
        $query_result = $this->db->query($query)->result();

        $data = [];
        foreach ($query_result as $item) {
            $opponent_id = 0;
            if ($user_id == $item->sender_id) {
                $opponent_id = $item->receiver_id;
            } else {
                $opponent_id = $item->sender_id;
            }

            $query = "
                SELECT
                    username, photo
                FROM
                    users
                WHERE
                    id = $opponent_id
            ";
            $query_result = $this->db->query($query)->result();
            $opponent_name = '';
            $opponent_photo = base_url() . UPLOAD_PROFILE_PHOTO . 'profile_default.png';
            if (count($query_result) > 0) {
                $query_row = $query_result[0];
                $opponent_name = $query_row->username;
                if ($query_row->photo) {
                    $opponent_photo = base_url() . UPLOAD_PROFILE_PHOTO . $query_row->photo;
                }
            }

            $query = "
                SELECT
                    COUNT(*) AS unread_count
                FROM
                    chat_messages
                WHERE
                    sender_id = $opponent_id AND receiver_id = $user_id AND status < 1 
            ";
            $query_result = $this->db->query($query)->result();
            $unread_count = $query_result[0]->unread_count;

            $last_message = $item->message;
            $updated_at = $item->updated_at;

            $item_data = [
                'opponent_id' => $opponent_id,
                'opponent_name' => $opponent_name,
                'opponent_photo' => $opponent_photo,
                'opponent_status' => 'offline',
                'last_message' => $last_message,
                'unread_count' => $unread_count,
                'last_time' => $updated_at,
            ];
            array_push($data, $item_data);
        }

        $query = "
                SELECT
                    COUNT(*) AS total_count
                FROM
                    chat_rooms
            ";
        $query_result = $this->db->query($query)->result();
        $total_count = $query_result[0]->total_count;

        $response = [
            "status" => 1,
            "data" => [
                'total_count' => intval($total_count),
                'room_list' => $data,
            ]
        ];

        $this->response($response);
    }

    public function get_chat_title_post()
    {
        $response = [
            "status" => 0,
            "data" => []
        ];

        $query = "
            SELECT 
                value 
            FROM
                constants
            WHERE 
                `key` = 'chat_title' 
        ";
        $query_result = $this->db->query($query)->result();
        if (count($query_result) > 0) {
            $query_row = $query_result[0];
            $chat_title = $query_row->value;

            $response['status'] = 1;
            $response['data'] = ['chat_title' => $chat_title];
        }

        $this->response($response);
    }

    public function get_unread_count_post()
    {
        $response = [
            "status" => 0,
            "data" => []
        ];

        $user_id = $this->post('user_id');

        if (empty($user_id)) {
            $this->response($response);
        }

        $query = "
            SELECT 
                COUNT(*) AS unread_count
            FROM
                chat_messages
            WHERE 
                receiver_id = $user_id AND status < 1 
        ";
        $query_result = $this->db->query($query)->result();
        $unread_count = $query_result[0]->unread_count;

        $response['status'] = 1;
        $response['data'] = ['unread_count' => $unread_count];

        $this->response($response);
    }

    public function set_read_status_post()
    {
        $response = [
            "status" => 0,
            "data" => []
        ];

        $user_id = $this->post('user_id');
        $other_id = $this->post('other_id');

        if (empty($user_id) || empty($other_id)) {
            $this->response($response);
        }

        $query = "
            UPDATE chat_messages
            SET status = 1            
            WHERE 
                receiver_id = $user_id AND sender_id = $other_id
        ";
        $this->db->query($query);

        $response['status'] = 1;

        $this->response($response);
    }
}
