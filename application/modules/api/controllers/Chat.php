<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * product Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Chat extends Base_Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function rooms_last_message_post()
    {
        $user_id = $this->post('user_id');
        $str_ids = $this->post('room_ids');
        $room_ids = explode(",", $str_ids);
        $data = [];
        foreach ($room_ids as $room_id) {
            $last_message = $this->messages->get_first_one_where('room_id', $room_id);

            $search_key = array(
                'seen_status' => 0,
                'user_id' => $user_id
            );
            $unread_messages = [];
            $message_seens = $this->message_seens->get_where($search_key);
            foreach ($message_seens as $message_seen) {
                $message = $this->messages->get($message_seen->message_id);
                if ($message && $message->room_id == $room_id) {
                    $unread_messages[] = $message;
                }
            }
            $data[] = array(
                'last_message' => $last_message,
                'unread_count' => count($unread_messages),
                'unread_messages' => $unread_messages
            );
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );
        $this->response($result);
    }

    public function set_as_read_room_messages_post()
    {
        $user_id = $this->post('user_id');
        $room_id = $this->post('room_id');

        $search_key = array(
            'seen_status' => 0,
            'user_id' => $user_id
        ); // search unread
        $message_seens = $this->message_seens->get_where($search_key);
        foreach ($message_seens as $message_seen) {
            $message = $this->messages->get($message_seen->message_id);
            if ($message && $message->room_id == $room_id) {
                $this->message_seens->update_field($message_seen->id, 'seen_status', 1);
            }
        }

        $result = array(
            'status' => 1,
            'data' => $room_id
        );
        $this->response($result);
    }

    public function rooms_list_get($user_id)
    {
        $search_key = array('user_id' => $user_id);
        $room_users = $this->room_users->get_where($search_key);
        $result = array(
            "status" => TRUE,
            "data" => $room_users
        );
        $this->response($result);
    }

    public function rooms_list_post()
    {
        $offset = $this->post("offset");
        $limit = $this->post("limit");
        $user_id = $this->post('user_id');

        $room_users = $this->room_users->paginate_with_offset($offset, array('user_id' => $user_id), $limit);
        $result = array(
            "status" => TRUE,

            "data" => $room_users
        );
        $this->response($result);
    }

    public function room_get($room_id)
    {
        $room = $this->rooms->get($room_id);
        if ($room) {
            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $room
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "no room"
            );
        }
        $this->response($result);
    }

    public function rooms_by_ids_post()
    {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $id) {
            $data[] = $this->rooms->get($id);
        }

        if (count($data) > 0) {
            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $data
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "no data"
            );
        }
        $this->response($result);
    }

    public function create_dialog_post()
    {
        $user_id = $this->post('user_id');
        $str_opponent_ids = $this->post("opponent_ids");
        $opponent_ids = explode(",", $str_opponent_ids);

        $user = $this->users->get($user_id);
        if (!$user) {
            $this->my_response(0, '', 'Your account is inactive, please contact us');
        }

        if (count($opponent_ids) == 1) {
            $opponent_id = $opponent_ids[0];
            $opponent = $this->users->get($opponent_id);
            if (!$opponent) {
                $this->my_response(0, '', 'The opponent account is inactive');
            }

            $room = $this->rooms->get_first_one_where(array('creator_id' => $user_id, 'contact_id' => $opponent_id));
            if (!$room) {
                $room = $this->rooms->get_first_one_where(array('creator_id' => $opponent_id, 'contact_id' => $user_id));
            }
            if ($room) {
                // add opponent's room user
                $search_key = array(
                    'room_id' => $room->id,
                    'user_id' => $opponent_id
                );
                $room_users = $this->room_users->set_where($search_key)->get_all();
                if (count($room_users) < 1) {
                    $new_room_user = array(
                        'room_id' => $room->id,
                        'user_id' => $opponent_id,
                        'created_at' => round(microtime(true) * 1000),
                    );
                    $this->room_users->insert($new_room_user);
                }

                $search_key = array(
                    'room_id' => $room->id,
                    'user_id' => $user_id
                );
                $room_users = $this->room_users->set_where($search_key)->get_all();
                if (count($room_users) > 0) {
                    $new_room_user_id = $room_users[0]->id;
                } else {
                    // add user's room user
                    $new_room_user = array(
                        'room_id' => $room->id,
                        'user_id' => $user_id,
                        'created_at' => round(microtime(true) * 1000),
                    );
                    $new_room_user_id = $this->room_users->insert($new_room_user);
                }

                $new_room_user = $this->room_users->get($new_room_user_id);
            } else {
                $new_room = array(
                    'name' => $opponent->username,
                    'avatar_url' => $opponent->photo,
                    'contact_id' => $opponent_id,
                    'creator_id' => $user_id,
                    'type' => 0, // private dialog
                    'created_at' => round(microtime(true) * 1000),
                    'updated_at' => round(microtime(true) * 1000)
                );
                $new_room_id = $this->rooms->insert($new_room);
                //$room = $this->rooms->get($new_room_id);

                // add opponent's room user
                $new_room_user = array(
                    'room_id' => $new_room_id,
                    'user_id' => $opponent_id,
                    'created_at' => round(microtime(true) * 1000),
                );
                $this->room_users->insert($new_room_user);

                // add user's room user
                $new_room_user = array(
                    'room_id' => $new_room_id,
                    'user_id' => $user_id,
                    'created_at' => round(microtime(true) * 1000),
                );
                $new_room_user_id = $this->room_users->insert($new_room_user);
                $new_room_user = $this->room_users->get($new_room_user_id);
            }
        } else if (count($opponent_ids) > 1) {
            $room_name = $user->username;
            foreach ($opponent_ids as $opponent_id) {
                $opponent = $this->users->get($opponent_id);
                if (!$opponent) {
                    $opponent = $this->users->get_first_one_where('id', $opponent_id);
                    $this->my_response(0, '', 'User ' . $opponent->username . ' is inactive');
                }

                $room_name .= ", " . $this->users->get($opponent_id)->username;
            }

            $new_room = array(
                'name' => $room_name,
                'avatar_url' => 'icon_group.png',
                'creator_id' => $user_id,
                'type' => 1, // group dialog
                'created_at' => round(microtime(true) * 1000),
                'updated_at' => round(microtime(true) * 1000)
            );
            $new_room_id = $this->rooms->insert($new_room);
            //$room = $this->rooms->get($new_room_id);

            foreach ($opponent_ids as $opponent_id) {
                $opponent = $this->users->get($opponent_id);
                $new_room_user = array(
                    'room_id' => $new_room_id,
                    'user_id' => $opponent->id,
                    'created_at' => round(microtime(true) * 1000)
                );
                $this->room_users->insert($new_room_user);
            }
            $new_room_user = array(
                'room_id' => $new_room_id,
                'user_id' => $user_id,
                'created_at' => round(microtime(true) * 1000),
            );
            $new_room_user_id = $this->room_users->insert($new_room_user);
            $new_room_user = $this->room_users->get($new_room_user_id);
        } else {
            $result = array(
                'status' => 0,
                'error' => "no opponents"
            );
            $this->response($result);
            return;
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $new_room_user
        );
        $this->response($result);
    }

    public function delete_dialog_post()
    {
        $user_id = $this->post('user_id');
        $dialog_id = $this->post('dialog_id');
        $this->room_users->delete($dialog_id);

        $result = array(
            'status' => 1,
            'data' => $dialog_id,
            'error' => 'Success'
        );
        $this->response($result);
    }

    public function create_room_post()
    {
        $new_room = array(
            'name' => $this->post('name'),
            'avatar_url' => $this->post('avatarURL'),
            'creator_id' => $this->post('userID'),
            'created_at' => round(microtime(true) * 1000) //$this->post('created')
        );

        $new_id = $this->rooms->insert($new_room);
        $data = $this->rooms->get($new_id);
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );
        $this->response($result);
    }

    public function update_room_post()
    {
        $room_id = $this->post('roomID');
        $avatar_url = $this->post('avatarURL');
        $room_name = $this->post('name');
        if (isset($avatar_url) && $avatar_url) {
            $room_data = array(
                'name' => $room_name,
                'avatar_url' => $avatar_url
            );
        } else {
            $room_data = array(
                'name' => $room_name
            );
        }

        $id = $this->rooms->update($room_id, $room_data);
        $data = $this->rooms->get($id);
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );
        $this->response($result);
    }

    public function create_room_user_post()
    {
        $new_room_user = array(
            'room_id' => $this->post('roomID'),
            'user_id' => $this->post('userID'),
            'created_at' => round(microtime(true) * 1000) //$this->post('created'),
        );
        $search_key = array(
            'room_id' => $this->post('roomID'),
            'user_id' => $this->post('userID')
        );
        $room_users = $this->room_users->set_where($search_key)->get_all();
        if (count($room_users) > 0) {
            $data = $room_users[0];
        } else {
            $new_id = $this->room_users->insert($new_room_user);
            $data = $this->room_users->get($new_id);
        }
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );
        $this->response($result);
    }

    public function update_room_avatar_post()
    {
        $room_id = $this->post('room_id');
        $room_name = $this->post('room_name');
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $path = UPLOAD_CHAT_ROOM_PHOTO;
            $milliseconds = round(microtime(true) * 1000);
            $fileName = "profile_" . $milliseconds . '.png';
            $file_path = $path . $fileName;

            $tmpFile = $_FILES['image']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                $update_data = array(
                    "name" => $room_name,
                    "avatar_url" => $fileName
                );

                $this->rooms->update($room_id, $update_data);
                $room = $this->rooms->get($room_id);
                $response = array(
                    'status' => 1,
                    'data' => $room
                );
                $this->response($response);
            } else {
                $this->response(array('status' => 0, "error" => "Image Upload failed"));
            }
        } else {
            $this->response(array('status' => 0, "error" => "Upload failed."));
        }
    }

    public function room_user_get($id)
    {
        $room_user = $this->room_users->get($id);
        if ($room_user) {
            $search_key = array(
                'seen_status' => 0,
                'user_id' => $room_user->user_id
            );
            $message_seens = $this->message_seens->set_where($search_key)->get_all();
            foreach ($message_seens as $message_seen) {
                $message = $this->messages->get($message_seen->message_id);
                if ($message && $message->room_id == $room_user->room_id) {
                    $this->message_seens->update_field($message_seen->id, 'seen_status', 1);
                }
            }

            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $room_user
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "No data"
            );
        }
        $this->response($result);
    }

    public function room_user_by_room_user_id_get($room_user_id)
    {
        $room_user = $this->room_users->get($room_user_id);
        if ($room_user) {
            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $room_user
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "No data"
            );
        }
        $this->response($result);
    }

    public function room_user_by_room_id_and_user_id_post()
    {
        $search_key = array(
            'room_id' => $this->post('roomID'),
            'user_id' => $this->post('userID')
        );
        $room_users = $this->room_users->set_where($search_key)->get_all();
        if (count($room_users) > 0) {
            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $room_users[0]
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "No data"
            );
        }
        $this->response($result);
    }

    public function room_users_by_room_id_get($room_id)
    {
        $room_users = $this->room_users->get_where('room_id', $room_id);
        if (count($room_users) > 0) {
            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $room_users
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "No data"
            );
        }
        $this->response($result);
    }

    public function room_users_by_user_id_get($user_id)
    {
        $room_users = $this->room_users->get_where('user_id', $user_id);
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $room_users
        );
        $this->response($result);
    }

    public function room_users_by_room_ids_post()
    {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $room_id) {
            $data = array_merge($data, $this->room_users->get_where('room_id', $room_id));
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );
        $this->response($result);
    }

    public function room_users_by_user_ids_post()
    {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $user_id) {
            $data = array_merge($data, $this->room_users->get_where('user_id', $user_id));
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );
        $this->response($result);
    }

    public function message_get($id)
    {
        $message = $this->messages->get($id);
        if ($message) {
            $result = array(
                'status' => 1,
                'error' => 'Success',
                'data' => $message
            );
        } else {
            $result = array(
                'status' => 0,
                'error' => "no exist"
            );
        }
        $this->response($result);
    }

    public function save_new_message_post()
    {
        $type = $this->post('type');
        $user_id = $this->post('user_id');
        $room_id = $this->post('room_id');
        $local_id = $this->post('local_id');
        $message = $this->post('message');
        $should_update = $this->post('should_update');
        $playlist_id = $this->post('playlist_id');

        $search_message = array(
            'user_id' => $user_id,
            'room_id' => $room_id
        );

        $song_info = json_decode($message);
        if (isset($song_info->songId)) {
            $this->load->model('Song_model', 'songs');

            $song = $this->songs->get($song_info->songId);
            if ($song) {
                $album = $this->albums->get($song->albumId);
                $new_song_stream = array(
                    'user_id' => $user_id,
                    'song_id' => $song_info->songId,
                    'album_id' => $song->albumId,
                    'artist_id' => $album ? $album->artistId : '',
                    'playlist_id' => $playlist_id ? $playlist_id : '',
                    'streamed_at' => round(microtime(true) * 1000)
                );
                $this->streams->insert($new_song_stream);
            } else {
                $result = array(
                    'status' => 0,
                    'error' => 'Invalid Song',
                    'data' => $song_info
                );
                $this->response($result);
            }
        }

        $user_message = $this->messages->get_first_one_where($search_message);
        if ($should_update == 1 && $user_message) {
            $message_id = $user_message->id;
            $new_message = array(
                'local_id' => $local_id ? $local_id : "",
                'message' => $message,
                'type' => $type,
                'updated_at' => round(microtime(true) * 1000)
            );

            if ($type == 2) {
                $file_id = $this->post('fileID');
                if ($file_id) {
                    $new_message['message'] = $file_id;
                }
            }
            $this->messages->update($message_id, $new_message);
        } else {
            $new_message = array(
                'user_id' => $user_id,
                'room_id' => $room_id,
                'local_id' => $local_id ? $local_id : "",
                'message' => $message,
                'type' => $type,
                'created_at' => round(microtime(true) * 1000), //$this->post('created')
                'updated_at' => round(microtime(true) * 1000)
            );

            if ($type == 2) {
                $file_id = $this->post('fileID');
                if ($file_id) {
                    $new_message['message'] = $file_id;
                }
            }
            $message_id = $this->messages->insert($new_message);
        }

        $message = $this->messages->get($message_id);
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $message
        );
        $this->response($result);
    }

    public function delete_message_post()
    {
        $message_id = $this->post('id');
        $update_data = array(
            'message_status' => 0,
            'deleted_at' => round(microtime(true) * 1000) //$this->post('created')
        );
        $updated = $this->messages->update($message_id, $update_data);
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $updated
        );
        $this->response($result);
    }

    public function send_push_to_offline_users_post()
    {
        $sender_id = $this->post('sender_id');
        $receiver_id = $this->post('receiver_id');
        $message = $this->post('message');

        if (!$message) {
            $result = array(
                'status' => 0,
                'error' => 'Failed',
                'data' => "empty message."
            );
            $this->response($result);
        }

        $sender = $this->users->get($sender_id);

        $push_message = $message;
        $new_notification = [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'type' => '2',
            'title' => 'New message from ' . $sender->username,
            'content' => $message,
            'created_at' => date("Y-m-d H:i:s")
        ];
        $data = array(
            'notification' => $new_notification,
            'message' => $message
        );

        $notification_data = $this->notification_data($data, $push_message, "New message from " . $sender->username);
        $this->send_push_notification_by_user($receiver_id, $notification_data);

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => "message is pushed successfully."
        );
        $this->response($result);
    }

    public function set_all_messages_as_seen_post()
    {
        $room_id = $this->post('room_id');
        $user_id = $this->post('user_id');
        $search_key = array(
            'seen_status' => 0,
            'user_id' => $user_id
        );
        $message_seens = $this->message_seens->set_where($search_key)->get_all();
        foreach ($message_seens as $message_seen) {
            $message = $this->messages->get($message_seen->message_id);
            if ($message && $message->room_id == $room_id) {
                $this->message_seens->update_field($message_seen->id, 'seen_status', 1);
            }
        }
        $result = array(
            'status' => 1,
            'data' => $room_id,
            'error' => 'Success'
        );
        $this->response($result);
    }

    public function add_seen_message_by_post()
    {
        $message_id = $this->post('messageID');
        $new_seen = array(
            'message_id' => $message_id,
            'user_id' => $this->post('userID'),
        );
        $message_seens = $this->message_seens->set_where($new_seen)->get_all();
        if (count($message_seens) > 0) {
            $this->message_seens->update_field($message_seens[0]->id, 'seen_status', 1);
            $result = array(
                'status' => 1,
                'error' => "Already added",
                'data' => $this->messages->get($message_id)
            );
        } else {
            $this->message_seens->insert(array_merge($new_seen, array('created_at' => round(microtime(true) * 1000))));
            $result = array(
                'status' => 1,
                'error' => "Succeed",
                'data' => $this->messages->get($message_id)
            );
        }
        $this->response($result);
    }

    public function messages_post()
    {
        $room_id = $this->post('room_id');
        $offset = $this->post('offset');
        $limit = $this->post('limit');

        $where = array(
            'room_id' => $room_id,
            'message_status' => 1
        );
        $data = $this->messages
            ->paginate_with_offset($offset, $where, $limit);

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $data
        );

        $this->response($result);
    }

    public function message_latest_get($room_id, $last_message_id)
    {
        if ($last_message_id > 0) {
            if ($room_id > 0) {
                $last_message = $this->messages->get($last_message_id);
                if ($last_message) {
                    $last_message_created_at = $last_message->created_at;
                    $messages = $this->messages->get_messages_after($room_id, $last_message_created_at);
                } else {
                    $messages = $this->messages->get_where('room_id', $room_id);
                }
            } else {
                $messages = [];
            }
        } else {
            $messages = $this->messages->get_where('room_id', $room_id);
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => $messages
        );

        $this->response($result);
    }

    public function message_list_options()
    {
    }

    public function message_list_get($room_id, $last_message_id)
    {
        if ($room_id > 0) {
            if ($last_message_id > 0) {
                $last_message = $this->messages->get($last_message_id);
                if ($last_message) {
                    $last_message_created_at = $last_message->created_at;
                    $messages = $this->messages->get_messages_after($room_id, $last_message_created_at);
                } else {
                    $messages = $this->messages->get_where('room_id', $room_id);
                }
            } else {
                $messages = $this->messages->get_where('room_id', $room_id);
            }
        } else {
            $messages = [];
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => array('messages' => $messages)
        );

        $this->response($result);
    }

    public function message_list_post()
    {
        $room_id = $this->post('roomID');
        $last_message_id = $this->post('lastMessageID');

        if ($room_id > 0) {
            if ($last_message_id > 0) {
                $last_message = $this->messages->get($last_message_id);
                if ($last_message) {
                    $last_message_created_at = $last_message->created_at;
                    $messages = $this->messages->get_messages_after($room_id, $last_message_created_at);
                } else {
                    $messages = $this->messages->get_where('room_id', $room_id);
                }
            } else {
                $messages = $this->messages->get_where('room_id', $room_id);
            }
        } else {
            $messages = [];
        }

        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => array('messages' => $messages)
        );

        $this->response($result);
    }

    public function upload_file_options()
    {
    }

    public function upload_file_post()
    {
        $mime_type = $this->post('mime_type');
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $path = UPLOAD_CHAT_FILES;
            $milliseconds = round(microtime(true) * 1000);

            $name = $_FILES["file"]["name"];
            $ext = pathinfo($name, PATHINFO_EXTENSION);

            if (!$ext) {
                $ext = ".jpg";
            }
            $fileName = "file_" . $milliseconds . "." . $ext;
            $file_path = $path . $fileName;

            $tmpFile = $_FILES['file']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                //$finfo = finfo_open(FILEINFO_MIME_TYPE);
                //$mime = finfo_file($finfo, $tmpFile);
                $file_type = $mime_type ? $mime_type : $this->mime_content_type($file_path);
                $new_file = array(
                    'name' => $fileName,
                    'mime_type' => $file_type,
                    'size' => $_FILES['file']['size'],
                    'created_at' => round(microtime(true) * 1000)
                );
                $new_id = $this->files->insert($new_file);
                $file = $this->files->get($new_id);

                $thumb = null;
                if (strpos($file_type, 'jpeg') !== false || strpos($file_type, 'gif') !== false || strpos($file_type, 'png') !== false) {
                    $thumbName = "thumb_" . $milliseconds . "." . $ext;
                    $thumb_path = UPLOAD_CHAT_THUMBS . $thumbName;
                    $this->resize_crop_image(180, 180, $file_path, $thumb_path);

                    $new_file['name'] = $thumbName;
                    $new_file['is_thumb'] = 1;
                    $new_thumb_id = $this->files->insert($new_file);
                    $thumb = $this->files->get($new_thumb_id);

                    $this->files->update_field($new_id, 'thumb_id', $new_thumb_id);
                    $file->thumb_id = $new_thumb_id;
                } else if (strpos($file_type, 'video') !== false) {
                    /*$ffmpeg = FFMpeg\FFMpeg::create([
                        'ffmpeg.binaries'  => 'C:/ffmpeg-3.4.1/bin/ffmpeg.exe', // the path to the FFMpeg binary
                        'ffprobe.binaries' => 'C:/ffmpeg-3.4.1/bin/ffprobe.exe', // the path to the FFProbe binary
                        'timeout'          => 3600, // the timeout for the underlying process
                        'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
                    ]);*/
                    $ffmpeg = FFMpeg\FFMpeg::create([
                        'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                        'ffprobe.binaries' => '/usr/bin/ffprobe',
                        'timeout' => 3600, // the timeout for the underlying process
                        'ffmpeg.threads' => 12,   // the number of threads that FFMpeg should use
                    ]);
                    //$ffmpeg = FFMpeg\FFMpeg::create();
                    $video = $ffmpeg->open($file_path);
                    $thumbName = "thumb_" . $milliseconds . ".jpg";
                    $thumb_path = UPLOAD_CHAT_THUMBS . $thumbName;
                    $video
                        ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))
                        ->save($thumb_path);

                    $new_file['name'] = $thumbName;
                    $new_file['is_thumb'] = 1;
                    $new_file['mime_type'] = "image/jpeg";
                    $new_thumb_id = $this->files->insert($new_file);
                    $thumb = $this->files->get($new_thumb_id);

                    $this->files->update_field($new_id, 'thumb_id', $new_thumb_id);

                    $file->thumb_id = $new_thumb_id;
                }

                if ($thumb) {
                    $data = array('file' => $file, 'thumb' => $thumb);
                } else {
                    $data = array('file' => $file);
                }
                $result = array(
                    'status' => 1,
                    'error' => "Uploaded successfully",
                    'data' => $data
                );
            } else {
                $result = array('status' => 0, 'error' => "Copy failed");
            }
        } else {
            $result = array('status' => 0, 'error' => "Upload failed");
        }

        $this->response($result);
    }

    protected function mime_content_type($file)
    {
        $type = null;

        // First try with fileinfo functions
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $type = mime_content_type($file);
        }

        // Fallback to the default application/octet-stream
        if (!$type) {
            $type = 'application/octet-stream';
        }

        return $type;
    }

    function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80)
    {
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];
        $mime = $imgsize['mime'];

        switch ($mime) {
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 7;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 80;
                break;

            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) imagedestroy($dst_img);
        if ($src_img) imagedestroy($src_img);
    }

    public function download_file_get($id)
    {
        $file = $this->files->get($id);
        redirect($file->downloadUrl);
    }

    public function stickers_get()
    {
        $sticker_categories = $this->sticker_categories->get_all();
        $data = [];
        foreach ($sticker_categories as $sticker_category) {
            $stickers = $this->stickers->get_where('sticker_category_id', $sticker_category->id);
            $data[] = array(
                'id' => $sticker_category->id,
                'mainPic' => $sticker_category->mainPic,
                'list' => $stickers
            );
        }
        $result = array(
            'status' => 1,
            'error' => 'Success',
            'data' => array('stickers' => $data)
        );
        $this->response($result);
    }

    public function send_ss_notification_post()
    {
        $user_id = $this->post('user_id');
        $room_id = $this->post('room_id');

        $message = 'Sent a notification';

        $this->load->model('User_follow_model', 'user_follows');
        $followers = $this->user_follows->get_followers($user_id);
        foreach ($followers as $follower) {
            $sender = $this->users->get($user_id);
            $data = array(
                'sender' => $sender,
                'room_id' => $room_id
            );
            $notification_data = $this->notification_data($data, $sender->username . ' has started broadcasting.', 'Listen Now');
            $this->send_push_notification_by_user($follower->user_id, $notification_data);
            $message .= ',' . $follower->user_id;
        }

        $result = array(
            'status' => 1,
            'data' => $message
        );
        $this->response($result);
    }
}
