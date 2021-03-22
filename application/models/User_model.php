<?php 

class User_model extends MY_Model {

    protected $order_by = array('score', 'DESC');

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->name = $result->username;
            if($result->photo && $result->photo != "" && strpos($result->photo, 'http') === false) {
                $result->photo = base_url() . UPLOAD_PROFILE_PHOTO . $result->photo;
            }
        }

        unset($result->ip_address);
        unset($result->password);
        unset($result->salt);
        unset($result->activation_code);
        unset($result->forgotten_password_code);
        unset($result->forgotten_password_time);
        unset($result->remember_code);
        unset($result->last_login);

        return $result;
    }

    public function get_top_users_all_time() {
        return array(
            "part1" => $this->get_top_users_by_part_and_time(1),
            "part2" => $this->get_top_users_by_part_and_time(2),
            "part3" => $this->get_top_users_by_part_and_time(3),
            "pt" => $this->get_top_users_by_part_and_time(4),
        );
    }

    public function get_top_users_last_month() {
        $a_month_ago_time = time() - 86400 * 30;
        return array(
            "part1" => $this->get_top_users_by_part_and_time(1, $a_month_ago_time),
            "part2" => $this->get_top_users_by_part_and_time(2, $a_month_ago_time),
            "part3" => $this->get_top_users_by_part_and_time(3, $a_month_ago_time),
            "pt" => $this->get_top_users_by_part_and_time(4, $a_month_ago_time),
        );
    }

    public function get_top_users_last_week() {
        $a_week_ago_time = time() - 86400 * 7;
        return array(
            "part1" => $this->get_top_users_by_part_and_time(1, $a_week_ago_time),
            "part2" => $this->get_top_users_by_part_and_time(2, $a_week_ago_time),
            "part3" => $this->get_top_users_by_part_and_time(3, $a_week_ago_time),
            "pt" => $this->get_top_users_by_part_and_time(4, $a_week_ago_time),
        );
    }

    public function get_top_users_by_part_and_time($partId, $time=0) {
        $this->db->limit(100, 0);
        $this->load->model('Category_model', 'categories');

        $categories = $this->categories->get_where('part_id', $partId);
        if(count($categories)==0) return [];

        $category_ids = $categories[0]->id;
        for ($i=1; $i<count($categories); $i++) {
            $category_ids .= ",".$categories[$i]->id;
        }
        $query_for_quiz_list = "SELECT * FROM quizzes WHERE category_id IN ($category_ids)";
        $quizzes = $this->db->query($query_for_quiz_list)->result();
        if(count($quizzes) == 0) return [];

        $quiz_ids = $quizzes[0]->id;
        for ($i=1; $i<count($quizzes); $i++) {
            $quiz_ids .= ",".$quizzes[$i]->id;
        }

        $query = "SELECT users.id, users.username, users.email, users.photo, users.score, 
                    leaderboard.formatdate, leaderboard.quiz_id,
                    (SELECT sum(score) FROM leaderboard WHERE user_id = users.id) as sum_score 
                    FROM users JOIN leaderboard ON users.id = leaderboard.user_id 
                    WHERE leaderboard.formatdate > $time AND leaderboard.quiz_id IN ($quiz_ids) 
                    GROUP BY users.id ORDER BY sum_score DESC";
        $query = "SELECT users.id, users.username, users.email, users.photo, users.score, 
                    leaderboard.formatdate, leaderboard.quiz_id, leaderboard.score as sum_score 
                    FROM users JOIN leaderboard ON users.id = leaderboard.user_id 
                    WHERE leaderboard.formatdate > $time AND leaderboard.quiz_id IN ($quiz_ids) 
                    GROUP BY leaderboard.id ORDER BY sum_score DESC";

        $query_results = $this->db->query($query)->result();
        $query_users = [];

        foreach ($query_results as $user) {
            if($user->sum_score) {
                $user->score = $user->sum_score;
            } else {
                $user->score = 0;
            }

            $is_exist = false;
            foreach( $query_users as $query_user ){
                if( $query_user->email == $user->email ){
                    $is_exist = true;
                    if( $query_user->score < $user->score ){
                        $query_user->score = $user->score;
                        $query_user->sum_score = $user->sum_score;
                    }
                    break;
                }
            }
            if( !$is_exist ){
                $query_users[] = $user;
            }
        }

        $users = [];
        foreach( $query_users as $user ){
            $user = $this->callback_after_get($user);
            $users[] = $user;
        }

        return $users;
    }

    public function profile_filter() {
        $query = "SELECT photo from users WHERE id > 0";
        $photos = $this->db->query($query)->result();

        foreach ($photos as $photo) {
            if( $photo->photo == '' )
                continue;
            $file_src = UPLOAD_PROFILE_PHOTO.$photo->photo;
            $file_dest = UPLOAD_PROFILE_FILTERED.$photo->photo;

            copy($file_src, $file_dest);
        }
    }

}