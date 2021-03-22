<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz extends API_Controller
{

    // Constructor
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Part_model', 'parts');
        $this->load->model('Category_model', 'categories');
        $this->load->model('Quiz_model', 'quizzes');
        $this->load->model('Question_model', 'questions');
        $this->load->model('Exam_model', 'exams');
        $this->load->model('Exam_question_model', 'exam_questions');
        $this->load->model('Question_palooza_question_model', 'question_palooza_questions');
        $this->load->model('Triad_palooza_question_model', 'triad_palooza_questions');
        $this->load->model('Vignette_model', 'vignettes');
        $this->load->model('Vignette_question_model', 'vignette_questions');
        $this->load->model('Leaderboard_model', 'leaderboard');
        $this->load->model('study_guide_model', 'study_guides');
    }

    public function part_list_get()
    {
        $data = $this->parts->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function category_list_get()
    {
        $data = $this->categories->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function quote_list_get()
    {
        $this->load->model('Quote_model', 'quotes');
        $data = $this->quotes->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function quiz_list_get()
    {
        $data = $this->quizzes->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function question_list_get()
    {
        $data = $this->questions->get_all();
        $result = [
            'status' => 1,
            'data' => $data
        ];
        $this->response($result);
    }

    public function exam_list_get()
    {
        $data = $this->exams->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function exam_question_list_get()
    {
        $data = $this->exam_questions->get_all();
        $result = [
            'status' => 1,
            'data' => $data
        ];
        $this->response($result);
    }

    public function palooza_status_get()
    {
        $this->load->model('Constant_model', 'constants');

        $q_palooza_day_of_month = $this->constants->get_first_one_where('key', 'q_palooza_day_of_month')->value;
        $q_palooza_before_days = $this->constants->get_first_one_where('key', 'q_palooza_before_days')->value;
        $q_palooza_active_days = $this->constants->get_first_one_where('key', 'q_palooza_active_days')->value;

        $t_palooza_day_of_month = $this->constants->get_first_one_where('key', 't_palooza_day_of_month')->value;
        $t_palooza_before_days = $this->constants->get_first_one_where('key', 't_palooza_before_days')->value;
        $t_palooza_active_days = $this->constants->get_first_one_where('key', 't_palooza_active_days')->value;

        $now_date = new DateTime('now');
        $q_start_date = new DateTime(date("Y") . '-' . date("m") . '-' . $q_palooza_day_of_month . ' 00:00:00');
        $q_start_date_month_ago = $q_start_date->modify("-1 months");
        $q_start_date = new DateTime(date("Y") . '-' . date("m") . '-' . $q_palooza_day_of_month . ' 00:00:00');
        $q_start_date_month_after = $q_start_date->modify("1 months");
        $q_start_date = new DateTime(date("Y") . '-' . date("m") . '-' . $q_palooza_day_of_month . ' 00:00:00');

        $t_start_date = new DateTime(date("Y") . '-' . date("m") . '-' . $t_palooza_day_of_month . ' 00:00:00');
        $t_start_date_month_ago = $t_start_date->modify("-1 months");
        $t_start_date = new DateTime(date("Y") . '-' . date("m") . '-' . $t_palooza_day_of_month . ' 00:00:00');
        $t_start_date_month_after = $t_start_date->modify("1 months");
        $t_start_date = new DateTime(date("Y") . '-' . date("m") . '-' . $t_palooza_day_of_month . ' 00:00:00');

        $q_status = 'none';
        $q_minutes = '0';
        $date_diff = $now_date->diff($q_start_date);
        if($now_date < $q_start_date){
            if ($date_diff->days < $q_palooza_before_days) {
                $q_status = 'inactive';
                $q_minutes = $date_diff->days * 24 * 60 + $date_diff->h * 60 + $date_diff->i;
            }
        } else {
            if ($date_diff->days < $q_palooza_active_days) {
                $q_status = 'active';
                $q_minutes = '0';
            }
        }

        $t_status = 'none';
        $t_minutes = '0';
        $date_diff = $now_date->diff($t_start_date);
//        if($date_diff->days > 11){
//            $date_diff = $now_date->diff($t_start_date_month_ago);
//        }
//        if($date_diff->days > 11){
//            $date_diff = $now_date->diff($t_start_date_month_after);
//        }
//        console_log('$date_diff', $date_diff);
        if($now_date < $t_start_date){
            if ($date_diff->days < $t_palooza_before_days) {
                $t_status = 'inactive';
                $t_minutes = $date_diff->days * 24 * 60 + $date_diff->h * 60 + $date_diff->i;
            }
        } else {
            if ($date_diff->days < $t_palooza_active_days) {
                $t_status = 'active';
                $t_minutes = '0';
            }
        }

        $result = [
            'status' => 1,
            'data' => [
                'question_palooza' => [
                    'status' => $q_status, 'left_time' => strval($q_minutes)
                ],
                'triad_palooza' => [
                    'status' => $t_status, 'left_time' => strval($t_minutes)
                ],
            ]
        ];
                
        $this->response($result);
    }

    public function question_palooza_question_list_get()
    {
        $data = $this->question_palooza_questions->get_all();
        $result = [
            'status' => 1,
            'data' => $data
        ];
        $this->response($result);
    }

    public function triad_palooza_question_list_get()
    {
        $data = $this->triad_palooza_questions->get_all();
        $result = [
            'status' => 1,
            'data' => $data
        ];
        $this->response($result);
    }

    public function top_user_list_get()
    {
        $top_users_all_time = $this->users->get_top_users_all_time();
        $top_users_last_month = $this->users->get_top_users_last_month();
        $top_users_last_week = $this->users->get_top_users_last_week();
        $result = array(
            'status' => 1,
            'data' => array(
                'all_time' => $top_users_all_time,
                'last_month' => $top_users_last_month,
                'last_week' => $top_users_last_week
            )
        );
        $this->response($result);
    }

    public function score_post()
    {
        $user_id = $this->post('user_id');
        $quiz_id = $this->post('quiz_id');
        $score = $this->post('score');
        $number_corrects = $this->post('number_of_corrects');
        $number_wrongs = $this->post('number_of_wrongs');
        $time = $this->post('time');

        $data = array(
            'user_id' => $user_id,
            'quiz_id' => $quiz_id,
            'score' => $score,
            'number_corrects' => $number_corrects,
            'number_wrongs' => $number_wrongs,
            'time' => $time,
            'date' => date("Y-m-d H:i:s"),
            'formatdate' => round(microtime(true))
        );
        $this->leaderboard->insert($data);

        $user = $this->users->get($user_id);
        $score += $user->score;
        $updated = $this->users->update_field($user_id, "score", $score);
        $result = array(
            'status' => 1,
            'data' => $score
        );
        $this->response($result);
    }

    public function answer_click_post()
    {
        $question_id = $this->post('question_id');
        $click_index = $this->post('click_index');

        $this->questions->update_answer_click($question_id, $click_index);

        $result = array(
            'status' => 1,
            'data' => ''
        );
        $this->response($result);
    }

    public function mnemonics_get()
    {
        $this->load->model('Constant_model', 'constants');
        $constant_mnemonic = $this->constants->get_first_one_where('key', 'mnemonic');
        $result = array(
            'status' => 1,
            'data' => $constant_mnemonic->value
        );
        $this->response($result);
    }

    public function about_get()
    {
        $this->load->model('Constant_model', 'constants');
        $constant_about = $this->constants->get_first_one_where('key', 'about');
        $result = array(
            'status' => 1,
            'data' => $constant_about->value
        );
        $this->response($result);
    }

    public function mode_get()
    {
        $this->load->model('Constant_model', 'constants');
        $constant_about = $this->constants->get_first_one_where('key', 'review_code');
        $result = array(
            'status' => 1,
            'data' => $constant_about->value
        );
        $this->response($result);
    }

    public function statistics_get()
    {
        $user_id = $this->get('user_id');

        /*
        1: general_anatomy
        2: spinal_anatomy
        3: biochemistry
        4: physiology
        5: pathology
        6: microbiology
        7: general_diagnosis
        8: neuromuscular
        10: principles
        11: practices
        12: advanced_imaging
        13: associated_sciences
         * */
        $general_anatomy = $this->_getCategoryPercent($user_id, '1');
        $spinal_anatomy = $this->_getCategoryPercent($user_id, '2');
        $biochemistry = $this->_getCategoryPercent($user_id, '3');
        $physiology = $this->_getCategoryPercent($user_id, '4');
        $pathology = $this->_getCategoryPercent($user_id, '5');
        $microbiology = $this->_getCategoryPercent($user_id, '6');
        $general_diagnosis = $this->_getCategoryPercent($user_id, '7');
        $neuromuscular = $this->_getCategoryPercent($user_id, '8');
        $principles = $this->_getCategoryPercent($user_id, '10');
        $practices = $this->_getCategoryPercent($user_id, '11');
        $advanced_imaging = $this->_getCategoryPercent($user_id, '12');
        $associated_sciences = $this->_getCategoryPercent($user_id, '13');

        $part1_done_percent = $this->_getPartPercent($user_id, '1');
        $part2_done_percent = $this->_getPartPercent($user_id, '2');
        $part3_done_percent = $this->_getPartPercent($user_id, '3');
        $pt_done_percent = $this->_getPartPercent($user_id, '4');
        $all_done_percent = $this->_getAllPercent($user_id, $part1_done_percent, $part2_done_percent, $part3_done_percent, $pt_done_percent);

        $week_statistic[] = $this->leaderboard->number_of_quizzes_for_day($user_id, date('Y-m-d'));
        for ($i = 1; $i < 7; $i++) {
            $date_ago = date('Y-m-d', strtotime("-$i days", time()));
            $week_statistic[] = $this->leaderboard->number_of_quizzes_for_day($user_id, $date_ago);
        }

        $user_quizzes = $this->leaderboard->get_where('user_id', $user_id);
        $total_number_of_questions = 0;
        $total_number_of_corrects = 0;
        $total_number_of_wrongs = 0;
        $total_time = 0;
        foreach ($user_quizzes as $quiz) {
            $questions = $this->questions->get_where('quiz_id', $quiz->quiz_id);
            $total_number_of_questions += count($questions);
            $total_number_of_corrects += $quiz->number_corrects;
            $total_number_of_wrongs += $quiz->number_wrongs;
            $total_time += $quiz->time;
        }

        $result = [
            'status' => 1,
            'data' => [
                'week_statistic' => array_reverse($week_statistic),
                'total_number_of_quiz' => count($user_quizzes),
                'total_number_of_questions' => $total_number_of_questions,
                'total_number_of_corrects' => $total_number_of_corrects,
                'total_number_of_wrongs' => $total_number_of_wrongs,
                'total_time' => $total_time,

                'general_anatomy' => $general_anatomy,
                'spinal_anatomy' => $spinal_anatomy,
                'biochemistry' => $biochemistry,
                'physiology' => $physiology,
                'pathology' => $pathology,
                'microbiology' => $microbiology,
                'general_diagnosis' => $general_diagnosis,
                'neuromuscular' => $neuromuscular,
                'principles' => $principles,
                'practices' => $practices,
                'advanced_imaging' => $advanced_imaging,
                'associated_sciences' => $associated_sciences,

                'part1_done_percent' => $part1_done_percent,
                'part2_done_percent' => $part2_done_percent,
                'part3_done_percent' => $part3_done_percent,
                'pt_done_percent' => $pt_done_percent,
                'all_done_percent' => $all_done_percent,
            ]
        ];
        $this->response($result);
    }

    private function _getCategoryPercent($user_id, $category_id)
    {
        $answer_sums = ($this->leaderboard->getCategorySums($user_id, $category_id))[0];
        $correct_sum = $answer_sums->correct_sum;
        $wrong_sum = $answer_sums->wrong_sum;
        $total_sum = $correct_sum + $wrong_sum;
        if ($total_sum == 0) {
            $percent = 0;
        } else {
            $percent = round($correct_sum * 100 / $total_sum);
        }

        return $percent;
    }

    private function _getPartPercent($user_id, $part_id)
    {
        $part_question_all_count = ($this->leaderboard->getPartQuestionAllCount($part_id))[0]->count;
        $part_question_counts = ($this->leaderboard->getPartQuestionCorrectCount($user_id, $part_id))[0];
        $part_question_correct_count = $part_question_counts->correct_count;
        $part_question_wrong_count = $part_question_counts->wrong_count;
        $part_question_count = $part_question_correct_count + $part_question_wrong_count;
        if ($part_question_all_count == 0) {
            $percent = 0;
        } else {
            $percent = round($part_question_count * 100 / $part_question_all_count);
        }

        if ($percent > 100) {
            $percent = 100;
        }

        return $percent;
    }

    private function _getAllPercent($user_id, $part1_done_percent, $part2_done_percent, $part3_done_percent, $pt_done_percent)
    {
        $part1_question_all_count = ($this->leaderboard->getPartQuestionAllCount('1'))[0]->count;
        $part2_question_all_count = ($this->leaderboard->getPartQuestionAllCount('2'))[0]->count;
        $part3_question_all_count = ($this->leaderboard->getPartQuestionAllCount('3'))[0]->count;
        $pt_question_all_count = ($this->leaderboard->getPartQuestionAllCount('4'))[0]->count;
        $all_question_count = $part1_question_all_count + $part2_question_all_count + $part3_question_all_count + $pt_question_all_count;
        $percent = round($part1_done_percent * ($part1_question_all_count / $all_question_count)
            + $part2_done_percent * ($part2_question_all_count / $all_question_count)
            + $part3_done_percent * ($part3_question_all_count / $all_question_count)
            + $pt_done_percent * ($pt_question_all_count / $all_question_count));

        if ($percent > 100) {
            $percent = 100;
        }

        return $percent;
    }

    public function clear_statistics_post()
    {
        $user_id = $this->post('user_id');

        if (!$user_id) {
            $result = [
                'status' => 0,
                'data' => [
                    'result' => 'fail',
                ]
            ];
        } else {
            $this->leaderboard->clearStatistics($user_id);
            $result = [
                'status' => 1,
                'data' => [
                    'result' => 'success',
                ]
            ];
        }


        $this->response($result);
    }

    public function vignette_list_get()
    {
        $data = $this->vignettes->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function vignette_question_list_get()
    {
        $data = $this->vignette_questions->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

    public function study_guides_post()
    {
        $this->study_guides->limit(6);
        $this->study_guides->order_by('place');
        $data = $this->study_guides->get_all();
        $result = array(
            'status' => 1,
            'data' => $data
        );
        $this->response($result);
    }

}