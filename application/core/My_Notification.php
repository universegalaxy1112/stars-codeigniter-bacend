<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 9/29/2017
 * Time: 10:20 AM
 */
class My_Notification //extends Thread
{
    private $method;
    private $device_ids; // can be filter when method = 2, can be one device_id when method = 4
    private $notification_data;
    private $badge_count;

    public function __construct($method, $device_ids, $notification_data, $badge_count=0) {
        $this->method = $method;
        $this->device_ids = $device_ids;
        $this->notification_data = $notification_data;
        $this->badge_count = $badge_count;
    }

    public function run() {
        if ($this->method && $this->device_ids && $this->notification_data) {
            switch ($this->method) {
                case 1:
                    $this->send_push_notification_all($this->notification_data);
                    break;

                case 2:
                    $this->send_push_notification_by_filters($this->device_ids, $this->notification_data);
                    break;

                case 3:
                    $this->send_push_notification_by_devices($this->device_ids, $this->notification_data, $this->badge_count);
                    break;

                case 4:
                    $this->send_push_notification_by_device($this->device_ids, $this->notification_data, $this->badge_count);
                    break;
            }
        }
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

    public function send_push_notification_all($notification_data) { //method = 1
        $fields = $this->get_field($notification_data);
        $fields['included_segments'] = array('All');

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_filters($filters, $notification_data) { //method = 2
        $fields = $this->get_field($notification_data);
        $fields['filters'] = $filters;

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_devices($device_ids, $notification_data, $badge_count = 0) { //method = 3
        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $device_ids;
        if($badge_count==0) {
            $fields['ios_badgeType'] = 'Increase';
            $fields['ios_badgeCount'] = 1;

        } else {
            $fields['ios_badgeType'] = 'SetTo';
            $fields['ios_badgeCount'] = $badge_count;
        }

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_device($user_one_signal_id, $notification_data, $badge_count) { //method = 4
        $device_ids[] = $user_one_signal_id;
        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $device_ids;
        $fields['ios_badgeType'] = 'SetTo';
        $fields['ios_badgeCount'] = $badge_count;

        return $this->send_push_notification($fields);
    }

}