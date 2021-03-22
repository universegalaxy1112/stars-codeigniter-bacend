<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once('My_Notification.php');

class Base_Api_Controller extends API_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Notification_model', 'notifications');
        $this->load->model('User_unread_pn_count_model', 'user_unread_pn_counts');
        $this->load->model('User_push_token_model', 'user_push_tokens');
        $this->load->model('Video_model', 'videos');
    }

    public function increase_and_get_user_unread_notification_count($user_id)
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

    public function reset_user_unread_notification_count($user_id)
    {
        $user_unread_notification_count = $this->user_unread_pn_counts->get_first_one_where('user_id', $user_id);
        if ($user_unread_notification_count) {
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

    public function send_push_notification($fields)
    {
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

    public function get_field($notification_data)
    {
        $fields = array(
            'app_id' => ONE_SIGNAL_APP_ID,
            'small_icon' => ONE_SIGNAL_SMALL_ICON,
            'large_icon' => ONE_SIGNAL_LARGE_ICON,
            'headings' => $notification_data['title'],
            'contents' => $notification_data['content'],
            'data' => $notification_data['data']
        );

        return $fields;
    }

    public function notification_data($data, $content = "Test", $title = APP_NAME)
    {
        $result = array(
            "title" => array("en" => $title),
            "content" => array("en" => $content),
            "data" => $data
        );

        return $result;
    }

    public function send_push_notification_all($notification_data)
    {
        $fields = $this->get_field($notification_data);
        $fields['included_segments'] = array('All');

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_filters($filters, $notification_data)
    {
        $fields = $this->get_field($notification_data);
        $fields['filters'] = $filters;

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_devices($device_ids, $notification_data, $badge_count = 0)
    {
        if ($badge_count == 0) {
            $notification_data['data']['unread_count'] = 1;
        } else {
            $notification_data['data']['unread_count'] = $badge_count;
        }

        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $device_ids;
        if ($badge_count == 0) {
            $fields['ios_badgeType'] = 'Increase';
            $fields['ios_badgeCount'] = 1;
        } else {
            $fields['ios_badgeType'] = 'SetTo';
            $fields['ios_badgeCount'] = $badge_count;
        }

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_device($user_one_signal_id, $notification_data, $badge_count)
    {
        if ($badge_count == 0) {
            $notification_data['data']['unread_count'] = 1;
        } else {
            $notification_data['data']['unread_count'] = $badge_count;
        }

        $device_ids[] = $user_one_signal_id;
        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $device_ids;
        $fields['ios_badgeType'] = 'SetTo';
        $fields['ios_badgeCount'] = $badge_count;

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_user($user_id, $notification_data)
    {
        $user_tokens = $this->user_push_tokens->get_where('user_id', $user_id);
        if (count($user_tokens) > 0) {
            $device_ids = array();
            foreach ($user_tokens as $user_token) {
                $device_ids[] = $user_token->one_signal_id;
            }
            if (count($device_ids) > 0) {
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

    public function send_email($to_email, $subject, $message)
    {
        $from_email = EMAIL_FROM_ADDRESS;
        $from_name = EMAIL_FROM_NAME;
        $to = $to_email;

        $headers = 'From: ' . $from_name . '<' . $from_email . '>' . "\r\n" .
            'Content-type: text/html; charset=utf8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $data = array();
        if (mail($to, $subject, $message, $headers)) {
            $data['status'] = 'success';
        } else {
            $data['status'] = 'fail';
        }
        return $data;
    }

    public function send_email_by_phpmailer($email, $username, $subject, $msg)
    {
        $mail = new PHPMailer;

        $mail->isSMTP();                    // Set mailer to use SMTP
        $mail->Host = SMTP_HOST;            // Specify main and backup SMTP servers
        $mail->Port = 587;                  // TCP port to connect to
        $mail->SMTPSecure = 'tls';          // Enable TLS encryption, `ssl` also accepted
        $mail->SMTPAuth = true;             // Enable SMTP authentication
        $mail->Username = SMTP_USERNAME;    // SMTP username
        $mail->Password = SMTP_PASSWORD;    // SMTP password

        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $msg;

        return $mail->send();
    }

    public function upload_image($image)
    {
        if (!isset($_FILES[$image])) {
            return "";
        }

        if (is_uploaded_file($_FILES[$image]["tmp_name"])) {
            $fileName = $this->_random_filename() . "." . pathinfo($_FILES[$image]["name"], PATHINFO_EXTENSION);
            $filePath = UPLOAD_IMAGE_PATH . $fileName;

            if (move_uploaded_file($_FILES[$image]["tmp_name"], $filePath)) {
                $filePathTV = UPLOAD_IMAGE_PATH . "tv/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(1400, 1400)->save($filePathTV);

                $filePathPC = UPLOAD_IMAGE_PATH . "pc/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(1400, 1400)->save($filePathPC);

                $filePathTablet = UPLOAD_IMAGE_PATH . "tablet/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(1200, 1200)->save($filePathTablet);

                $filePathPhone = UPLOAD_IMAGE_PATH . "phone/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(800, 800)->save($filePathPhone);

                $filePathThumbnail = UPLOAD_IMAGE_PATH . "watch/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(400, 400)->save($filePathThumbnail);

                $filePathThumbnail = UPLOAD_IMAGE_PATH . "icon/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(100, 100)->save($filePathThumbnail);

                return $filePath;
            } else {
                return "";
            }
        }

        return "";
    }

    public function upload_video($video)
    {
        if (!isset($_FILES[$video])) {
            return "";
        }

        if (is_uploaded_file($_FILES[$video]["tmp_name"])) {
            $fileName = $this->_random_filename() . "." . pathinfo($_FILES[$video]["name"], PATHINFO_EXTENSION);
            $filePath = UPLOAD_VIDEO_PATH . $fileName;

            if (move_uploaded_file($_FILES[$video]["tmp_name"], $filePath)) {
                return $filePath;
            } else {
                return "";
            }
        }

        return "";
    }

    private function _random_filename()
    {
        $seedstr = explode(" ", microtime(), 5);
        $seed    = $seedstr[0] * 10000;
        srand($seed);
        $random  = rand(1000, 10000);

        return date("YmdHis", time()) . $random;
    }

    function _getRandomHexString($length)
    {
        $characters = '0123456789abcdef';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function sendMessageToUser($message, $user_token)
    {
        if (strlen($user_token) > 0) {
            $fields = array(
                'to' => $user_token,
                'data' => array(
                    'message' => $message
                )
            );
            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FCM_MESSAGING_API);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                FCM_CONTENT_TYPE,
                FCM_SERVER_KEY
            ));
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

    function sendMessageForTopic($message, $topic = "")
    {
        if ($topic != "") {
            $fields = array(
                'to' => "/topics/" . $topic,
                'data' => array(
                    'message' => $message
                )
            );
            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FCM_MESSAGING_API);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                FCM_CONTENT_TYPE,
                FCM_SERVER_KEY
            ));
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

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function upload_file($file_key, $upload_file_path, $file_prefix = "")
    {
        $result = array(
            "status" => false,
            "error"  => ""
        );
        if (!isset($_FILES[$file_key])) {
            $result["error"] = "Not Uploaded File";
        }

        if (is_uploaded_file($_FILES[$file_key]["tmp_name"])) {
            $filename = $this->get_filename_with_milliseconds($_FILES[$file_key]["name"], $file_prefix);
            $filePath = $upload_file_path . $filename;

            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $filePath)) {
                $result["status"] = true;
                $result["filename"] = $filename;
            } else {
                $result["error"] = "File Move Failed.";
            }
        } else {
            $result["error"] = "Upload failed.";
        }

        return $result;
    }

    public function get_filename_with_milliseconds($filename, $prefix = "")
    {
        $tmp = explode(".", $filename);
        $ext = end($tmp);

        $milliseconds = round(microtime(true) * 1000);
        return $prefix . sprintf("%.0f", $milliseconds) . "." . $ext;
    }

    ////// token test //////
    ///
    public function test_jwt_get()
    {
        $id = $this->get('id');

        $this->response($this->get_token($id));
    }

    public function test_decode_jwt_get()
    {
        $token = $this->get('token');

        $this->response($this->get_identity_with_token($token));
    }
}
