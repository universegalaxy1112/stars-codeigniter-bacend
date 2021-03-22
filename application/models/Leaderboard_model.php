<?php

class Leaderboard_model extends MY_Model
{

    protected $_table = "leaderboard";

    public function number_of_quizzes_for_day($user_id, $date)
    {
        //$query = "select * from leaderboard where user_id = $user_id AND date(date) = '$date'";
        $query = "
                SELECT
                  id 
                FROM
                  leaderboard 
                WHERE
                  user_id = '$user_id' 
                  AND date(date) = '$date'";
        return $this->db->query($query)->num_rows();
    }

    public function getCategorySums($user_id, $category_id)
    {
        $query = "
                SELECT
                  SUM( l.number_corrects ) as correct_sum,
                  SUM( l.number_wrongs ) as wrong_sum 
                FROM
                  (
                    SELECT
                      q.id as quiz_id 
                    FROM
                      categories AS c 
                      LEFT JOIN
                        quizzes AS q 
                        ON c.id = q.category_id 
                    WHERE
                      c.id = '$category_id' 
                  )
                  AS r 
                  LEFT JOIN
                    leaderboard AS l 
                    ON r.quiz_id = l.quiz_id 
                WHERE
                  l.user_id = '$user_id'";
        return $this->db->query($query)->result();
    }

    public function getPartQuestionAllCount($part_id)
    {
        $query = "
                SELECT
                  COUNT(*) as count
                FROM
                  (
                    SELECT
                      qz.id AS id 
                    FROM
                      (
                        SELECT
                          c.id AS id 
                        FROM
                          parts AS p 
                          LEFT JOIN
                            categories AS c 
                            ON p.id = c.part_id 
                        WHERE
                          p.id = '$part_id'
                      )
                      AS r_c 
                      LEFT JOIN
                        quizzes AS qz 
                        ON r_c.id = qz.category_id
                  )
                  AS r_q 
                  INNER JOIN
                    questions AS qs 
                    ON r_q.id = qs.quiz_id";

        if ($part_id == '3'){ //vignettes
            $query = "
                SELECT
                  COUNT(*) as count
                FROM
                  vignette_questions";
        }

        return $this->db->query($query)->result();
    }

    public function getPartQuestionCorrectCount($user_id, $part_id)
    {
        $query = "
                SELECT
                  SUM(l.number_corrects) as correct_count, SUM(l.number_wrongs) as wrong_count
                FROM
                  (
                    SELECT
                      qz.id AS id 
                    FROM
                      (
                        SELECT
                          c.id AS id 
                        FROM
                          parts AS p 
                          LEFT JOIN
                            categories AS c 
                            ON p.id = c.part_id 
                        WHERE
                          p.id = '$part_id' 
                      )
                      AS r_c 
                      LEFT JOIN
                        quizzes AS qz 
                        ON r_c.id = qz.category_id 
                  )
                  AS r_q 
                  INNER JOIN
                    leaderboard AS l 
                    ON r_q.id = l.quiz_id 
                WHERE
                  l.user_id = '$user_id'";
        return $this->db->query($query)->result();
    }

    public function clearStatistics($user_id)
    {
        $query = "
                DELETE
                FROM
                  leaderboard 
                WHERE
                  user_id = '$user_id'
        ";
        $this->db->query($query);
    }
}

