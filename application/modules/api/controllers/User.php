<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Demo Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class User extends Base_Api_Controller
{
  public function index_get()
  {
    $data = $this->users
      ->select('id, username, email, active, first_name, last_name')
      ->get_all();
    $this->response($data);
  }

  public function id_get($id)
  {
    $data = $this->users
      ->select('id, username, email, active, first_name, last_name')
      ->get($id);
    $this->response($data);
  }

  public function signup_post()
  {
    $username = $this->post('username');
    $phone = $this->post('phone');
    $password = $this->post('password');

    if (empty($phone)) {
      $phone = $username;
    }
    $result = $this->signup($username, $phone, $password);
    $this->response($result);
  }

  public function signup($username, $phone, $password)
  {
    $result = [
      "status" => 0,
      "data" => []
    ];

    $user = $this->users->get_first_one_where("phone", $phone);

    if ($user) {
      $result["error"] = "Phone number already exist.";
      return $result;
    }

    $additional_data = [
      "username" => $username,
    ];
    $group = array('1');
    $user_id = $this->ion_auth->register($phone, $password, $additional_data, $group);
    if ($user_id) {
      $createdUser = $this->users->get($user_id);
      $this->register_active_compaign($createdUser);

      $result["status"] = 1;
      $result["data"] = $createdUser;
      return $result;
    } else {
      $result["data"] =  $this->ion_auth->errors();
      return $result;
    }
  }

  public function login_post()
  {
    $email = $this->post('username');
    $password = $this->post('password');

    // proceed to login user
    $result = $this->login($email, $password);
    $this->response($result);
  }

  public function login($email, $password)
  {
    $logged_in = $this->ion_auth->login($email, $password, FALSE);

    if ($logged_in) {
      $user = $this->ion_auth->user()->row();
      $user = $this->users->get($user->id);
      $token = $this->get_token($user->id);

      $user->token = $token;

      $result = [
        'status' => 1,
        'data' => $user,
        'token' => $token
      ];
      return $result;
    }

    return [
      'status' => 0,
      'error' => $this->ion_auth->errors()
    ];
  }

  public function login_facebook_post()
  {
    $fb_id = $this->post('fb_id');
    $username = $this->post('username');
    $email = $this->post('email');
    $picture = $this->post('picture');

    $fileName = '';
    if ($picture && $picture != "" && strpos($picture, 'http') !== false) {
      $path = UPLOAD_PROFILE_PHOTO;
      $milliseconds = round(microtime(true) * 1000);
      $fileName = "profile_" . sprintf("%.0f", $milliseconds) . '.png';
      $file_path = $path . $fileName;
      file_put_contents($file_path, file_get_contents($picture));
    }
    $password = $fb_id;

    $search_key = array(
      'social_id' => $fb_id,
      'social_type' => 'facebook'
    );
    $exist_users = $this->users->set_where($search_key)->get_all();
    if (count($exist_users) > 0) {
      $exist_user = $exist_users[0];
      $update_data = array(
        "username" => $username,
        "photo" => $fileName,
      );
      $this->users->update($exist_user->id, $update_data);

      $logged_in = $this->ion_auth->login($email, $password, FALSE);
      if ($logged_in) {
        // get User object and remove unnecessary fields
        $user = $this->ion_auth->user()->row();
        $user = $this->users->get($user->id);
        $this->register_active_compaign($user);

        // return result
        $result = array(
          "status" => 1,
          "is_login" => "1",
          "data" => $user
        );
        $this->response($result);
      } else {
        $this->error($this->ion_auth->errors());
      }
    } else {
      // additional fields
      //$photo = "http://graph.facebook.com/" update_profile_with_image. $fb_id . "/picture?type=square";
      $additional_data = array(
        "social_id" => $fb_id,
        "social_type" => 'facebook',
        "username" => $username,
        "photo" => $fileName,
      );

      // set user to "members" group
      $group = array('1');

      // proceed to create user
      $user_id = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
      if ($user_id) {
        $createdUser = $this->users->get($user_id);
        $this->register_active_compaign($createdUser);

        $result = array(
          "status" => 1,
          "is_login" => "0",
          "data" => $createdUser
        );
        $this->response($result);
      } else {
        $this->error($this->ion_auth->errors());
      }
    }
  }

  public function signin_or_signup_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $username = $this->post('username');
    $password = $this->post('password');

    if (empty($username)) {
      $this->response($response);
    }

    if (strpos($username, 'guest_') !== false) {
      $response = $this->login($username, $username);
    } else {
      $response = $this->login($username, $password);
    }

    if ($response["status"] == 0 && strpos($username, 'guest_') !== false) {
      $response = $this->signup($username, $username, $username);
    }

    $this->response($response);
  }

  public function get_user_profile_post()
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
        COUNT(*) AS view_count 
      FROM
        video_views
      WHERE 
        owner_id = '$user_id' 
    ";
    $query_result = $this->db->query($query)->result();
    $all_view_count = $query_result[0]->view_count;

    $query = "
      SELECT
        COUNT(*) AS view_count
      FROM
        video_views
      WHERE
        owner_id = '$user_id'
        AND (
          updated_at BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01')
          AND NOW()
        )
        ";
    $query_result = $this->db->query($query)->result();
    $month_view_count = $query_result[0]->view_count;
    $month_view_count_list = [$month_view_count];


    for ($i = 0; $i < 11; $i++) {
      $min = $i + 1;
      $query = "
        SELECT
          COUNT(*) AS view_count
        FROM
          video_views
        WHERE
          owner_id = '3'
          AND (
            updated_at > DATE_FORMAT(DATE_SUB(NOW(), INTERVAL $min month), '%Y-%m-01')
            AND updated_at < DATE_FORMAT(DATE_SUB(NOW(), INTERVAL $i month), '%Y-%m-01')
          )
      ";
      $query_result = $this->db->query($query)->result();
      $month_view_count = $query_result[0]->view_count;
      array_push($month_view_count_list, $month_view_count);
    }

    $query = "
      SELECT * 
      FROM
        users
      WHERE 
        id = '$user_id' 
    ";
    $query_result = $this->db->query($query)->result();
    if (count($query_result) < 1) {
      $this->response($response);
    }
    $user_id = $query_result[0]->id;
    $username = $query_result[0]->username;
    $photo = $query_result[0]->photo;
    $phone = $query_result[0]->phone;

    $response = [
      "status" => 1,
      "data" => [
        'user_id' => $user_id,
        'username' => $username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($photo ? $photo : 'profile_default.png'),
        'phone' => $phone,
        'all_view_count' => $all_view_count,
        'month_view_count_list' => $month_view_count_list,
      ]
    ];
    $this->response($response);
  }

  public function update_profile_post()
  {
    $user_id = $this->post('user_id');
    $username = $this->post('username');
    $phone = $this->post('phone');
    $password = $this->post('password');

    if ($password) {
      $update_data = array(
        "username" => $username,
        "phone" => $phone,
        "password" => $this->ion_auth->hash_password($password),
      );
    } else {
      $update_data = array(
        "username" => $username,
        "phone" => $phone,
      );
    }

    $this->users->update($user_id, $update_data);
    $mUser = $this->users->get($user_id);
    $this->register_active_compaign($mUser);

    $response = array(
      "status" => 1,
      "data" => $mUser
    );
    $this->response($response);
  }

  public function update_profile_with_image_post()
  {
    $user_id = $this->post('user_id');
    $username = $this->post('username');
    $phone = $this->post('phone');
    $password = $this->post('password');

    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
      $path = UPLOAD_PROFILE_PHOTO;

      $milliseconds = round(microtime(true) * 1000);
      $fileName = "profile_" . sprintf("%015d", $milliseconds) . '.png';
      $file_path = $path . $fileName;

      $tmpFile = $_FILES['image']['tmp_name'];
      if (move_uploaded_file($tmpFile, $file_path)) {
        if ($password) {
          $update_data = array(
            "username" => $username,
            "phone" => $phone,
            "password" => $this->ion_auth->hash_password($password),
            "photo" => $fileName
          );
        } else {
          $update_data = array(
            "username" => $username,
            "phone" => $phone,
            "photo" => $fileName
          );
        }

        $this->users->update($user_id, $update_data);
        $mUser = $this->users->get($user_id);
        $this->register_active_compaign($mUser);

        $response = array(
          "status" => 1,
          "data" => $mUser
        );
        $this->response($response);
      } else {
        $this->response(array("status" => 0, "error" => "Image Upload failed"));
      }
    } else {
      $this->response(array("status" => 0, "error" => "Upload failed."));
    }
  }

  public function add_video_post()
  {
    $user_id = $this->post('user_id');
    $uploaded_url = $this->post('uploaded_url');
    $tags = $this->post('tags');
    $price = $this->post('price');
    $description = $this->post('description');
    $number = $this->post('number');

    $tag_list = explode(',', $tags);
    foreach ($tag_list as $item) {
      $new_tag = ['name' => $item];
      $query = "
                    INSERT IGNORE INTO tags (name)
                    VALUES
                        ('$item');
            ";
      $this->db->query($query);
    }

    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
      $path = UPLOAD_VIDEO_THUMB;

      $milliseconds = round(microtime(true) * 1000);
      $fileName = "thumb_" . sprintf("%015d", $milliseconds) . '.jpeg';
      $file_path = $path . $fileName;

      $tmpFile = $_FILES['image']['tmp_name'];
      if (move_uploaded_file($tmpFile, $file_path)) {
        $new_video = [
          'user_id' => $user_id,
          'url' => $uploaded_url,
          "thumb" => $fileName,
          'tag_list' => $tags,
          'price' => $price,
          'description' => $description,
          'number' => $number,
        ];
        $res = $this->videos->insert($new_video);

        $query = "
                    UPDATE users 
                    SET upload_count = upload_count + 1
                    WHERE id = '$user_id'
                    ";
        $this->db->query($query);

        $response = [
          "status" => 1,
          "data" => ""
        ];
        $this->response($response);
      } else {
        $this->response(array("status" => 0, "error" => "Image Upload failed"));
      }
    } else {
      $this->response(array("status" => 0, "error" => "Upload failed."));
    }
  }

  public function get_user_video_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $me_id = $this->post('me_id');
    $user_id = $this->post('user_id');
    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');

    if (empty($me_id) || empty($user_id) || empty($page_number) || empty($count_per_page)) {
      $this->response($response);
    }

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $query = "
            SELECT
                v.*,
                u.id AS user_id,
                u.username,
                u.photo,
                vl.user_id AS vl_user_id, 
                COUNT(vv.video_id) AS view_count, 
                30 - DATEDIFF(NOW(), v.created_at) AS left_days 
            FROM
                videos AS v
                LEFT JOIN users AS u ON v.user_id = u.id
                LEFT JOIN video_likes AS vl ON (
                    v.id = vl.video_id
                    AND vl.user_id = '$me_id'
                )
                LEFT JOIN video_views as vv ON v.id = vv.video_id
                WHERE
                    v.user_id = '$user_id'
                GROUP BY 
                    v.id
                ORDER BY
                    v.created_at DESC
                LIMIT
                    $offset, $limit
        ";
    $query_result = $this->db->query($query)->result();

    $data = [];
    foreach ($query_result as $item) {
      $query1 = "
                SELECT
                    COUNT(*) AS like_count 
                FROM
                    video_likes 
                WHERE
                    video_id = $item->id
            ";
      $query_result1 = $this->db->query($query1)->result();
      $like_count = $query_result1[0]->like_count;

      $item_data = [
        'id' => $item->id,
        'url' => $item->url,
        'thumb' => base_url() . UPLOAD_VIDEO_THUMB . $item->thumb,
        'tag_list' => $item->tag_list,
        'price' => $item->price,
        'description' => $item->description,
        'number' => $item->number,
        'sticker' => $item->sticker,
        'user_id' => $item->user_id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
        'is_like' => $item->vl_user_id ? true : false,
        'view_count' => $item->view_count,
        'like_count' => $like_count,
        'left_days' => $item->left_days,
      ];
      array_push($data, $item_data);
    }

    $query = "
            SELECT
                COUNT(*) AS total_count
            FROM
                videos
            WHERE
                user_id = $user_id
        ";
    $query_result = $this->db->query($query)->result();
    $total_count = $query_result[0]->total_count;

    $query = "
            SELECT
                COUNT(*) AS view_count
            FROM
                video_views
            WHERE
                owner_id = $user_id
        ";
    $query_result = $this->db->query($query)->result();
    $view_count = $query_result[0]->view_count;

    $query = "
            SELECT 
                COUNT(*) AS save_count
            FROM
                videos AS v 
                INNER JOIN video_likes as vl ON v.id = vl.video_id 
            WHERE v.user_id = $user_id                
        ";
    $query_result = $this->db->query($query)->result();
    $save_count = $query_result[0]->save_count;

    $query = "
            SELECT
                COUNT(type) AS like_count
            FROM
                user_likes
            WHERE
                other_id = $user_id AND type = 2
        ";
    $query_result = $this->db->query($query)->result();
    $like_count = $query_result[0]->like_count;

    $query = "
            SELECT
                COUNT(type) AS dislike_count
            FROM
                user_likes
            WHERE
                other_id = $user_id AND type = 0
        ";
    $query_result = $this->db->query($query)->result();
    $dislike_count = $query_result[0]->dislike_count;

    $query = "
            SELECT type 
            FROM user_likes 
            WHERE user_id = '$me_id' AND other_id = '$user_id';
        ";
    $query_result = $this->db->query($query)->result();
    if (count($query_result) > 0) {
      $type = $query_result[0]->type;
    } else {
      $type = 1;
    }

    $response = [
      "status" => 1,
      "data" => [
        'view_count' => intval($view_count),
        'save_count' => intval($save_count),
        'like_count' => intval($like_count),
        'dislike_count' => intval($dislike_count),
        'type' => intval($type),
        'total_count' => intval($total_count),
        'video_list' => $data,
      ]
    ];
    $this->response($response);
  }

  public function get_all_video_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');
    $username = $this->post('username');
    $password = $this->post('password');

    if (empty($page_number) || empty($count_per_page)) {
      $this->response($response);
    }

    $login_result = [
      "status" => 0,
      "data" => []
    ];
    if (empty($user_id)) {
      if (!empty($username)) {
        if (strpos($username, 'guest_') !== false) {
          $login_result = $this->login($username, $username);
        } else {
          $login_result = $this->login($username, $password);
        }

        if ($login_result["status"] == 0 && strpos($username, 'guest_') !== false) {
          $login_result = $this->signup($username, $username, $username);
        }
      }
    }

    $query = "
            DELETE FROM videos 
            WHERE  
                created_at < NOW() - INTERVAL 30 DAY;
        ";
    $this->db->query($query);

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $query = "
            SELECT
                v.*,
                u.id AS user_id,
                u.username,
                u.photo,
                vl.user_id AS vl_user_id,
                COUNT(vv.video_id) AS view_count,                
                30 - DATEDIFF(NOW(), v.created_at) AS left_days 
            FROM
                videos AS v
                LEFT JOIN users AS u ON v.user_id = u.id
                LEFT JOIN video_likes AS vl ON (
                    v.id = vl.video_id
                    AND vl.user_id = '$user_id'
                )                 
                LEFT JOIN video_views as vv ON v.id = vv.video_id                 
                GROUP BY
                    v.id
                ORDER BY
                    RAND()
                LIMIT
                    $offset, $limit
        ";
    // COUNT(vl_a.video_id) AS like_count,
    // LEFT JOIN video_likes as vl_a ON v.id = vl_a.video_id
    // WHERE v.id NOT IN (SELECT vv_a.video_id FROM video_views AS vv_a WHERE vv_a.viewer_id = '$user_id')         
    // ORDER BY v.sponsor DESC, like_count DESC, v.created_at DESC
    $query_result = $this->db->query($query)->result();

    $data = [];
    foreach ($query_result as $item) {
      $query1 = "
                SELECT
                    COUNT(*) AS like_count 
                FROM
                    video_likes 
                WHERE
                    video_id = $item->id
            ";
      $query_result1 = $this->db->query($query1)->result();
      $like_count = $query_result1[0]->like_count;

      $item_data = [
        'id' => $item->id,
        'url' => $item->url,
        'thumb' => base_url() . UPLOAD_VIDEO_THUMB . $item->thumb,
        'tag_list' => $item->tag_list,
        'price' => $item->price,
        'description' => $item->description,
        'number' => $item->number,
        'sticker' => $item->sticker,
        'user_id' => $item->user_id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
        'is_like' => $item->vl_user_id ? true : false,
        'view_count' => $item->view_count,
        'like_count' => $like_count,
        'left_days' => $item->left_days,
      ];
      array_push($data, $item_data);
    }

    $query = "
            SELECT
                COUNT(*) AS total_count
            FROM
                videos
        ";
    $query_result = $this->db->query($query)->result();
    $total_count = $query_result[0]->total_count;

    $response = [
      "status" => 1,
      "data" => [
        'total_count' => intval($total_count),
        'video_list' => $data,
        'login_data' => $login_result,
      ]
    ];
    $this->response($response);
  }

  public function get_filtered_video_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');
    $keyword = $this->post('keyword');

    if (empty($page_number) || empty($count_per_page)) {
      $this->response($response);
    }

    $query = "
            DELETE FROM videos 
            WHERE  
                created_at < NOW() - INTERVAL 30 DAY;
        ";
    $this->db->query($query);

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $pattern = '/[ ,;]/';
    $keyword_list = preg_split($pattern, $keyword);

    $word = $keyword_list[0];
    $query = "
            SELECT 
                v.*,
                u.id AS user_id,
                u.username,
                u.photo, 
                vl.user_id AS vl_user_id, 
                COUNT(vv.video_id) AS view_count, 
                30 - DATEDIFF(NOW(), v.created_at) AS left_days 
            FROM
                videos AS v
                LEFT JOIN users AS u ON v.user_id = u.id
                LEFT JOIN video_likes AS vl ON (
                    v.id = vl.video_id
                    AND vl.user_id = '$user_id'
                ) 
                LEFT JOIN video_views as vv ON v.id = vv.video_id 
                GROUP BY
                    v.id                 
        ";
    if (!empty($keyword)) {
      $order = 1;

      $query = $query .
        "
                ORDER BY
                    CASE
                WHEN v.tag_list REGEXP '";

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $query = $query . "(?=.*$word)";
      }
      $query = $query . "' THEN $order ";

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $order++;
        $query = $query .
          "
                    WHEN v.tag_list REGEXP '^$word$' THEN $order
                    ";
      }

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $order++;
        $query = $query .
          "
                    WHEN v.tag_list REGEXP '^$word,' THEN $order 
                    ";
      }

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $order++;
        $query = $query .
          "
                    WHEN v.tag_list REGEXP '";
        for ($j = 0; $j < count($keyword_list); $j++) {
          $word_j = $keyword_list[$j];
          if ($word == $word_j) {
            $query = $query . "(?=.*$word)";
          } else {
            $query = $query . "(.*$word_j)?";
          }
        }
        $query = $query . "' THEN $order ";
      }

      $order++;
      $query = $query . "
                ELSE $order 
                END,
                v.created_at DESC
                LIMIT
                    $offset, $limit
            ";
    } else {
      $query = $query . "
                ORDER BY
                    v.created_at DESC
                LIMIT
                    $offset, $limit
            ";
    }

    $query_result = $this->db->query($query)->result();

    $valid_days = 7;
    $data = [];
    $now_date = new DateTime('now');
    foreach ($query_result as $item) {
      $create_date = new DateTime($item->created_at);
      $date_diff = $now_date->diff($create_date);
      $diff_days = $valid_days - $date_diff->days;

      $query1 = "
                SELECT
                    COUNT(*) AS like_count 
                FROM
                    video_likes 
                WHERE
                    video_id = $item->id
            ";
      $query_result1 = $this->db->query($query1)->result();
      $like_count = $query_result1[0]->like_count;

      $item_data = [
        'id' => $item->id,
        'url' => $item->url,
        'thumb' => base_url() . UPLOAD_VIDEO_THUMB . $item->thumb,
        'tag_list' => $item->tag_list,
        'price' => $item->price,
        'description' => $item->description,
        'number' => $item->number,
        'sticker' => $item->sticker,
        'left_days' => $diff_days,
        'user_id' => $item->user_id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
        'is_like' => $item->vl_user_id ? true : false,
        'view_count' => $item->view_count,
        'like_count' => $like_count,
        'left_days' => $item->left_days,
      ];
      array_push($data, $item_data);
    }

    $query = "
            SELECT 
                COUNT(*) AS total_count                  
            FROM
                videos                 
        ";
    $query_result = $this->db->query($query)->result();
    $total_count = $query_result[0]->total_count;

    $response = [
      "status" => 1,
      "data" => [
        'total_count' => intval($total_count),
        'video_list' => $data,
      ]
    ];
    $this->response($response);
  }

  public function get_quick_search_video_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');
    $keyword = $this->post('keyword');

    if (empty($page_number) || empty($count_per_page)) {
      $this->response($response);
    }

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $pattern = '/[ ,;]/';
    $keyword_list = preg_split($pattern, $keyword);

    $word = $keyword_list[0];
    $query = "
      SELECT 
        v.*,
        u.id AS user_id,
        u.username,
        u.photo, 
        vl.user_id AS vl_user_id, 
        COUNT(vv.video_id) AS view_count, 
        30 - DATEDIFF(NOW(), v.created_at) AS left_days 
      FROM
        videos AS v
        LEFT JOIN users AS u ON v.user_id = u.id
        LEFT JOIN video_likes AS vl ON (
          v.id = vl.video_id
          AND vl.user_id = '$user_id'
        ) 
        LEFT JOIN video_views as vv ON v.id = vv.video_id 
    ";
    if (!empty($keyword)) {
      $query = $query . "
        WHERE v.tag_list = '$word' OR v.tag_list LIKE '$word,%' OR v.tag_list LIKE '%,$word,%' OR v.tag_list LIKE '%,$word' 
      ";

      for ($i = 1; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $query = $query . "
          OR v.tag_list = '$word' OR v.tag_list LIKE '$word,%' OR v.tag_list LIKE '%,$word,%' OR v.tag_list LIKE '%,$word' 
        ";
      }

      $query = $query . "
        GROUP BY
          v.id    
        ORDER BY
          v.created_at DESC
        LIMIT
          $offset, $limit
      ";
    } else {
      $query = $query . "
        ORDER BY
          v.created_at DESC
        LIMIT
          $offset, $limit
      ";
    }
    $query_result = $this->db->query($query)->result();

    $valid_days = 7;
    $data = [];
    $now_date = new DateTime('now');
    foreach ($query_result as $item) {
      $create_date = new DateTime($item->created_at);
      $date_diff = $now_date->diff($create_date);
      $diff_days = $valid_days - $date_diff->days;

      $query1 = "
                SELECT
                    COUNT(*) AS like_count 
                FROM
                    video_likes 
                WHERE
                    video_id = $item->id
            ";
      $query_result1 = $this->db->query($query1)->result();
      $like_count = $query_result1[0]->like_count;

      $item_data = [
        'id' => $item->id,
        'url' => $item->url,
        'thumb' => base_url() . UPLOAD_VIDEO_THUMB . $item->thumb,
        'tag_list' => $item->tag_list,
        'price' => $item->price,
        'description' => $item->description,
        'number' => $item->number,
        'sticker' => $item->sticker,
        'left_days' => $diff_days,
        'user_id' => $item->user_id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
        'is_like' => $item->vl_user_id ? true : false,
        'view_count' => $item->view_count,
        'like_count' => $like_count,
        'left_days' => $item->left_days,
      ];
      array_push($data, $item_data);
    }

    $query = "
      SELECT 
          COUNT(*) AS total_count                  
      FROM
          videos AS v
    ";
    $word = $keyword_list[0];
    if (!empty($keyword)) {
      $query = $query . "
        WHERE v.tag_list = '$word' OR v.tag_list LIKE '$word,%' OR v.tag_list LIKE '%,$word,%' OR v.tag_list LIKE '%,$word' 
      ";

      for ($i = 1; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $query = $query . "
          OR v.tag_list = '$word' OR v.tag_list LIKE '$word,%' OR v.tag_list LIKE '%,$word,%' OR v.tag_list LIKE '%,$word' 
        ";
      }
    }
    $query_result = $this->db->query($query)->result();
    $total_count = $query_result[0]->total_count;

    $response = [
      "status" => 1,
      "data" => [
        'total_count' => intval($total_count),
        'video_list' => $data,
      ]
    ];
    $this->response($response);
  }

  public function get_liked_video_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');

    if (empty($user_id) || empty($page_number) || empty($count_per_page)) {
      $this->response($response);
    }

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $query = "
      SELECT
        v.*,
        u.id AS user_id,
        u.username,
        u.photo,
        vl.user_id AS vl_user_id,
        COUNT(vv.video_id) AS view_count, 
        30 - DATEDIFF(NOW(), v.created_at) AS left_days 
      FROM
        videos AS v
        LEFT JOIN users AS u ON v.user_id = u.id
        LEFT JOIN video_likes AS vl ON (
            v.id = vl.video_id
            AND vl.user_id = '$user_id'
        )
        LEFT JOIN video_views as vv ON v.id = vv.video_id
      WHERE
        vl.user_id = '$user_id'
      GROUP BY 
        v.id
      ORDER BY
        v.created_at DESC
      LIMIT
        $offset, $limit
    ";
    $query_result = $this->db->query($query)->result();

    $data = [];
    foreach ($query_result as $item) {
      $query1 = "
                SELECT
                    COUNT(*) AS like_count 
                FROM
                    video_likes 
                WHERE
                    video_id = $item->id
            ";
      $query_result1 = $this->db->query($query1)->result();
      $like_count = $query_result1[0]->like_count;

      $item_data = [
        'id' => $item->id,
        'url' => $item->url,
        'thumb' => base_url() . UPLOAD_VIDEO_THUMB . $item->thumb,
        'tag_list' => $item->tag_list,
        'price' => $item->price,
        'description' => $item->description,
        'number' => $item->number,
        'sticker' => $item->sticker,
        'user_id' => $item->user_id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
        'is_like' => $item->vl_user_id ? true : false,
        'view_count' => $item->view_count,
        'like_count' => $like_count,
        'left_days' => $item->left_days,
      ];
      array_push($data, $item_data);
    }

    $query = "
            SELECT
                COUNT(*) AS total_count
            FROM
                videos AS v
                LEFT JOIN video_likes AS vl ON v.id = vl.video_id
            WHERE
                vl.user_id = '$user_id'
        ";
    $query_result = $this->db->query($query)->result();
    $total_count = $query_result[0]->total_count;

    $response = [
      "status" => 1,
      "data" => [
        'total_count' => intval($total_count),
        'video_list' => $data,
      ]
    ];
    $this->response($response);
  }

  public function get_filtered_user_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $keyword = $this->post('keyword');
    $user_id = $this->post('user_id');
    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $pattern = '/[ ,;]/';
    $keyword_list = preg_split($pattern, $keyword);

    $word = $keyword_list[0];

    $query = "
            SELECT
                id, username, photo
            FROM
                users
            ";

    if (!empty($keyword)) {
      $order = 1;

      $query = $query .
        "
                ORDER BY
                    CASE
                WHEN username REGEXP '";

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $query = $query . "(?=.*$word)";
      }
      $query = $query . "' THEN $order ";

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $order++;
        $query = $query . "
                    WHEN username REGEXP '^$word$' THEN $order
                ";
      }

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $order++;
        $query = $query .
          "
                    WHEN username REGEXP '^$word,' THEN $order 
                    ";
      }

      for ($i = 0; $i < count($keyword_list); $i++) {
        $word = $keyword_list[$i];
        $order++;
        $query = $query .
          "
                    WHEN username REGEXP '";
        for ($j = 0; $j < count($keyword_list); $j++) {
          $word_j = $keyword_list[$j];
          if ($word == $word_j) {
            $query = $query . "(?=.*$word)";
          } else {
            $query = $query . "(.*$word_j)?";
          }
        }
        $query = $query . "' THEN $order ";
      }

      $order++;
      $query = $query . "
                    ELSE $order 
                    END,
                    created_at DESC
                LIMIT
                    $offset, $limit
            ";
    } else {
      $query = $query .
        "
                ORDER BY
                    created_at DESC
                LIMIT
                    $offset, $limit
                ";
    }

    $query_result = $this->db->query($query)->result();

    $data = [];
    foreach ($query_result as $item) {
      $item_data = [
        'id' => $item->id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
      ];
      array_push($data, $item_data);
    }

    $query = "
                SELECT
                    COUNT(*) AS total_count
                FROM
                    users
            ";
    $query_result = $this->db->query($query)->result();
    $total_count = $query_result[0]->total_count;

    $response = [
      "status" => 1,
      "data" => [
        'total_count' => intval($total_count),
        'user_list' => $data,
      ]
    ];
    $this->response($response);
  }

  public function get_top_user_list_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $page_number = $this->post('page_number');
    $count_per_page = $this->post('count_per_page');

    $limit = $count_per_page;
    $offset = ($page_number - 1) * $count_per_page;

    $query = "
      SELECT
        u.id,
        u.username,
        u.photo,
        COUNT(v.user_id) AS save_count 
      FROM
        users AS u,
        videos AS v 
        INNER JOIN
          video_likes AS vl 
          ON v.id = vl.video_id 
      WHERE
        v.user_id = u.id 
      GROUP BY
        v.user_id 
      ORDER BY
        save_count DESC
      LIMIT
        $offset, $limit
    ";
    $query_result = $this->db->query($query)->result();

    $data = [];
    foreach ($query_result as $item) {
      $item_data = [
        'id' => $item->id,
        'user_name' => $item->username,
        'user_photo' => base_url() . UPLOAD_PROFILE_PHOTO . ($item->photo ? $item->photo : 'profile_default.png'),
        'save_count' => $item->save_count,
      ];
      array_push($data, $item_data);
    }

    $query = "
      SELECT
        u.id,
        u.username,
        u.photo,
        COUNT(v.user_id) AS save_count 
      FROM
        users AS u,
        videos AS v 
        INNER JOIN
          video_likes AS vl 
          ON v.id = vl.video_id 
      WHERE
        v.user_id = u.id 
      GROUP BY
        v.user_id       
    ";
    $query_result = $this->db->query($query)->result();
    $total_count = count($query_result);

    $response = [
      "status" => 1,
      "data" => [
        'total_count' => intval($total_count),
        'user_list' => $data,
      ]
    ];
    $this->response($response);
  }

  public function update_like_video_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $video_id = $this->post('video_id');
    $is_like = $this->post('is_like');
    if (empty($user_id) || empty($video_id) || empty($is_like)) {
      $this->response($response);
    }

    if ($is_like == 'true') {
      $query = "
                SELECT 
                    COUNT(*) AS total_count
                FROM
                    video_likes
                WHERE
                    video_id = '$video_id' AND user_id = '$user_id'
            ";
      $query_result = $this->db->query($query)->result();
      if (count($query_result) > 0) {
        $query_row = $query_result[0];
        $total_count = $query_row->total_count;

        if ($total_count > 0) {
          $response['status'] = 1;
        } else {
          $query = "
                        INSERT INTO video_likes (video_id, user_id)
                        VALUES
                            ('$video_id', '$user_id');
                    ";
          $this->db->query($query);

          $response['status'] = 1;
        }
      }
    } else {
      $query = "
                DELETE FROM video_likes 
                WHERE  
                    video_id = '$video_id' AND user_id = '$user_id';
            ";
      $this->db->query($query);

      $response['status'] = 1;
    }

    $this->response($response);
  }

  public function update_video_view_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $video_id = $this->post('video_id');
    $owner_id = $this->post('owner_id');
    $viewer_id = $this->post('viewer_id');
    $device_type = $this->post('device_type');
    $device_identifier = $this->post('device_identifier');
    if (empty($video_id) || empty($owner_id) || empty($device_identifier)) {
      $this->response($response);
    }

    $query = "
            INSERT IGNORE INTO video_views (video_id, owner_id, viewer_id, device_type, device_identifier)
                VALUES
                    ('$video_id', '$owner_id', '$viewer_id', '$device_type', '$device_identifier');              
        ";
    $this->db->query($query);
    $query = "
            UPDATE video_views 
                SET deleted_at = NOW() 
                WHERE 
                    video_id = '$video_id' AND owner_id = '$owner_id' AND viewer_id = '$viewer_id' AND device_type = '$device_type' AND device_identifier = '$device_identifier';
        ";
    $this->db->query($query);
    $response['status'] = 1;

    $this->response($response);
  }

  public function update_user_like_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $other_id = $this->post('other_id');
    $type = $this->post('type');
    if (empty($other_id)) {
      $this->response($response);
    }

    if (!empty($user_id)) {
      $query = "
                INSERT IGNORE INTO user_likes (user_id, other_id, type)
                    VALUES
                        ('$user_id', '$other_id', '$type');
            ";
      $this->db->query($query);
      $query = "
                UPDATE user_likes 
                    SET type = '$type' 
                    WHERE 
                        user_id = '$user_id' AND other_id = '$other_id';
            ";
      $this->db->query($query);
    }

    $query = "
            SELECT
                COUNT(type) AS like_count
            FROM
                user_likes
            WHERE
                other_id = $other_id AND type = 2
        ";
    $query_result = $this->db->query($query)->result();
    $like_count = $query_result[0]->like_count;

    $query = "
            SELECT
                COUNT(type) AS dislike_count
            FROM
                user_likes
            WHERE
                other_id = $other_id AND type = 0
        ";
    $query_result = $this->db->query($query)->result();
    $dislike_count = $query_result[0]->dislike_count;

    $response = [
      "status" => 1,
      "data" => [
        'like_count' => intval($like_count),
        'dislike_count' => intval($dislike_count),
      ]
    ];

    $this->response($response);
  }

  public function update_video_sticker_post()
  {
    $response = [
      "status" => 0,
      "data" => []
    ];

    $video_id = $this->post('video_id');
    $sticker = $this->post('sticker');
    if (empty($video_id)) {
      $this->response($response);
    }

    $query = "                    
            UPDATE videos 
            SET sticker = $sticker 
            WHERE 
                id = '$video_id';
        ";
    $this->db->query($query);
    $response['status'] = 1;

    $this->response($response);
  }

  public function register_push_token_post()
  {
    $response = [
      "status" => 1,
      "data" => []
    ];

    $user_id = $this->post('user_id');
    $one_signal_id = $this->post('one_signal_id');
    $token = $this->post('token');
    $device_id = $this->post('device_id');
    $device_type = $this->post('device_type');

    if (empty($user_id) || empty($one_signal_id) || empty($token) || empty($device_id)) {
      $this->response($response);
    }

    if ($token == 'undefined' || $token == 'null' || $one_signal_id == 'undefined' || $one_signal_id == 'null') {
      $this->response($response);
    }

    $search_key = [
      'device_id' => $device_id,
    ];

    $user_token = $this->user_push_tokens->get_first_one_where($search_key);
    if ($user_token) {
      $update_data = array(
        'user_id' => $user_id,
        'token' => $token,
        'device_type' => $device_type,
        'one_signal_id' => $one_signal_id,
        'updated_at' => date("Y-m-d H:i:s")
      );
      $res = $this->user_push_tokens->update($user_token->id, $update_data);
    } else {
      $new_token = array(
        'user_id' => $user_id,
        'token' => $token,
        'one_signal_id' => $one_signal_id,
        'topic' => 'news',
        'device_id' => $device_id,
        'device_type' => $device_type,
        'created_at' => date("Y-m-d H:i:s"),
        'updated_at' => date("Y-m-d H:i:s")
      );
      $res = $this->user_push_tokens->insert($new_token);
    }

    $result = array(
      "status" => 1,
      "data" => $res
    );
    $this->response($result);
  }


  public function register_active_compaign($user)
  {
    return true;
  }
}
