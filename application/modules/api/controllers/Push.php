<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Push extends Base_Api_Controller
{
    public function update_push_token_post()
    {
        $user_id = $this->post('user_id');
        $push_token = $this->post('push_token');

        $query = "
            UPDATE
                dick_push_tokens
            SET
                token = '$push_token'
            WHERE
                user_id = $user_id
            ";
        $this->db->query($query);

        $result = [
            "status" => 1,
            "data" => "success"
        ];

        $this->response($result);

    }

    public function send_push_get()
    {
        $result = [
            "status" => 0,
            "data" => "failed"
        ];

        $ret = $this->send2ios();

        if($ret){
            $result = [
                "status" => 1,
                "data" => "success"
            ];
        }

        $this->response($result);
    }

    public function send2ios()
    {
        //        $host = 'gateway.sandbox.push.apple.com';
        $host = 'gateway.push.apple.com';
        $port = 2195;

        $query = "
            SELECT
                *
            FROM
                dick_push_tokens
            WHERE
                user_id = '777'
            ";
        $query_results = $this->db->query($query)->result();
        if(count($query_results) > 0 ){
            $query_row = $query_results[0];
            $push_token = $query_row->token;
        } else {
            return false;
        }

        $passphrase = '';
        $certFile = __DIR__ . '/PEM/FreelancerHelper.pem';

        // override with debug mode
        $badge = 0;
        $type = 1; //NEW_MESSAGE

        // create the message content that is to be sent to the device.
        // and encode the body to JSON.
        $body = json_encode(array(
            'aps' => array(
                // the message that is to appear on the dialog.
                'alert' => 'Please check Freelancer',
                // the Badge Number for the Application Icon (integer >=0)
                'badge' => $badge,
                // audible Notification Option
                'sound' => 'default',
                // type of Notification (see above switch for values)
                'payload' => sprintf('%d', $type),
            ),
        ));

        try {
            $error = false;

            // create the Socket Stream.
            $context = stream_context_create();
            stream_context_set_option($context, 'ssl', 'local_cert', $certFile);

            // remove this line if you would like to enter the Private Key Passphrase manually.
            stream_context_set_option($context, 'ssl', 'passphrase', $passphrase);

            // open the Connection to the APNS Server.
            $socket = stream_socket_client('ssl://' . $host . ':' . $port, $error, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $context);

            // check if we were able to open a socket.
            if (!$socket) {
                throw new \Exception(sprintf('APNS Connection Failed: %s %s.', $error, $errstr));
            }

            // build the Binary Notification.
            $message = chr(0) . pack('n', 32) . pack('H*', $push_token) . pack('n', strlen($body)) . $body;

            // send the Notification to the Server.
            $result = fwrite($socket, $message, strlen($message));

            if (!$result) {
                throw new \Exception('An error occurred during the notification.');
            }

            // close the Connection to the Server.
            fclose($socket);

            return true;

        } catch (\Exception $e) {
        }

        return false;
    }


    public function delete_notification($primary_key)
    {
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
                if (isset($resultObject->recipients) && isset($resultObject->id)) {
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

    public function get_user_unread_notification_count($user_id)
    {
        $unread_count = 1;
        $user_unread_notification_count = $this->user_unread_pn_counts->get_first_one_where('user_id', $user_id);
        if ($user_unread_notification_count) {
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

    function sendPushMessage($messageTitle, $message)
    {
        $notification_data = $this->notification_data(array("foo" => "bar"), $message, $messageTitle);
        $userPushTokens = $this->userPushTokens->get_all();
        $one_signal_ids = [];
        foreach ($userPushTokens as $userPushToken) {
            if ($userPushToken->status == 1) $one_signal_ids[] = $userPushToken->one_signal_id;
        }
        if (count($one_signal_ids) > 0) {
            return $this->send_push_notification_by_devices($one_signal_ids, $notification_data);
        } else {
            return false;
        }
    }

}
