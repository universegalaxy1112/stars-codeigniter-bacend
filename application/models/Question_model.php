<?php 

class Question_model extends MY_Model {

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            if($result->image && $result->image != "") {
                $result->image = base_url() . UPLOAD_QUESTION_IMAGE . $result->image;
            }
        }

        return $result;
    }

    public function update_answer_click($question_id, $click_index){
        $query = "";
        switch ($click_index){
            case "0":
                $query = "UPDATE questions SET click_a = click_a + 1 WHERE id = $question_id";
                break;
            case "1":
                $query = "UPDATE questions SET click_b = click_b + 1 WHERE id = $question_id";
                break;
            case "2":
                $query = "UPDATE questions SET click_c = click_c + 1 WHERE id = $question_id";
                break;
            case "3":
                $query = "UPDATE questions SET click_d = click_d + 1 WHERE id = $question_id";
                break;
            case "4":
                $query = "UPDATE questions SET click_e = click_e + 1 WHERE id = $question_id";
                break;
            case "5":
                $query = "UPDATE questions SET click_f = click_f + 1 WHERE id = $question_id";
                break;
            case "6":
                $query = "UPDATE questions SET click_g = click_g + 1 WHERE id = $question_id";
                break;
            case "7":
                $query = "UPDATE questions SET click_h = click_h + 1 WHERE id = $question_id";
                break;
            default:
                break;
        }

        return $this->db->query($query);
    }

}