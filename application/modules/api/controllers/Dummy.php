<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dummy extends API_Controller
{
    // before this, you have to import table from csv
    public function update_question_answers_get()
    {
        $this->load->model('Csv_exam_model', 'csv_exams');
        $questions = $this->csv_exams->get_all();
        foreach ($questions as $question) {
            $temp_answer = '';
            if ($question->option_a == $question->answer) {
                $temp_answer = "a";
            }
            if ($question->option_b == $question->answer) {
                $temp_answer .= "b";
            }
            if ($question->option_c == $question->answer) {
                $temp_answer .= "c";
            }
            if ($question->option_d == $question->answer) {
                $temp_answer .= "d";
            }
            $this->csv_exams->update_field($question->id, 'temp_answer', $temp_answer);
        }
        $this->response("success");
    }

    // before this, confirm the exam_id.
    public function move_to_exam_table_get()
    {
        $this->load->model('Exam_question_model', 'exam_questions');
        $this->load->model('Csv_exam_model', 'csv_exams');

        $csv_exams = $this->csv_exams->get_all();
        foreach ($csv_exams as $csv_exam) {
            $data = array(
                'exam_id' => '125',
                'content' => $csv_exam->question,
                'option_a' => $csv_exam->option_a,
                'option_b' => $csv_exam->option_b,
                'option_c' => $csv_exam->option_c,
                'option_d' => $csv_exam->option_d,
                'option_e' => '',
                'option_f' => '',
                'option_g' => '',
                'option_h' => '',
                'answer' => $csv_exam->temp_answer,
                'explanation' => $csv_exam->explanation,
                'status' => 1
            );
            $this->exam_questions->insert($data);
        }
        $this->response("success");
    }

    // before this, confirm the destination table.
    public function move_to_palooza_table_get()
    {
        $this->load->model('Triad_palooza_question_model', 'palooza_questions');
        $this->load->model('Csv_exam_model', 'csv_exams');

        $csv_exams = $this->csv_exams->get_all();
        foreach ($csv_exams as $csv_exam) {
            $data = array(
                'content' => $csv_exam->question,
                'option_a' => $csv_exam->option_a,
                'option_b' => $csv_exam->option_b,
                'option_c' => '',
                'option_d' => '',
                'option_e' => '',
                'option_f' => '',
                'option_g' => '',
                'option_h' => '',
                'answer' => $csv_exam->temp_answer,
                'status' => 1
            );
            $this->palooza_questions->insert($data);
        }
        $this->response("success");
    }

    public function set_quizzes_data_get()
    {
        $this->load->library('PHPExcel');
        $this->load->model('quiz_model', 'quizzes');
        $inputFileName = './' . UPLOAD_FILE_EXCEL . 'quizzes.csv';

        $file = fopen($inputFileName, 'r');
        $data = [];
        while (($line = fgetcsv($file)) !== FALSE) {
            $exist_category = $this->categories->get_where(array('title' => $line[2], 'part_id' => $line[4]));
            if (!$exist_category) {
                $category = [
                    'pos' => $line[1],
                    'title' => $line[2],
                    'image' => $line[3],
                    'part_id' => $line[4],
                ];
                $result = $this->categories->insert($category);
                $data[] = $result;
            }
        }
        $this->response($data);
    }

    public function set_categories_data_get()
    {
        $this->load->library('PHPExcel');
        $this->load->model('category_model', 'categories');
        $inputFileName = './' . UPLOAD_FILE_EXCEL . 'categories.csv';

        $file = fopen($inputFileName, 'r');
        $data = [];
        while (($line = fgetcsv($file)) !== FALSE) {
            $exist_category = $this->categories->get_where(array('title' => $line[2], 'part_id' => $line[4]));
            if (!$exist_category) {
                $category = array(
                    'pos' => $line[1],
                    'title' => $line[2],
                    'image' => $line[3],
                    'part_id' => $line[4],
                );
                $result = $this->categories->insert($category);
                $data[] = $result;
            }
        }
        $this->response($data);
    }

    public function set_data_get()
    {
        $this->load->model('quest_model', 'quests');
        $this->load->model('question_model', 'questions');
        $this->load->model('quiz_model', 'quizzes');

        $questions = $this->quests->get_all();
        foreach ($questions as $question) {
            $quiz_data = array(
                'part_id' => 1,
                'category_id' => $question->quiz_id,
                'title' => $question->quiz_name
            );
            $quiz = $this->quizzes->get_first_one_where($quiz_data);
            if ($quiz) {
                $quiz_id = $quiz->id;
            } else {
                $quiz_id = $this->quizzes->insert($quiz_data);
            }

            $new_data = array(
                'quiz_id' => $quiz_id,
                'content' => $question->question,
                'option_a' => $question->optiona,
                'option_b' => $question->optionb,
                'option_c' => $question->optionc,
                'option_d' => $question->optiond,
                'answer' => $question->answer,
                'status' => 1
            );
            $this->questions->insert($new_data);
        }

        $this->response("success");
    }

    public function set_data_from_csv_get()
    {
        $this->load->library('PHPExcel');
        $this->load->model('quest_model', 'quests');
        $inputFileName = './' . UPLOAD_FILE_EXCEL . 'PT- 4 Quizzes!! APP READY!! - PT (4Qs).csv';

        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            $data = [];
            $quiz_id = 0;
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

                if (($rowData[0][0] && $rowData[0][0] != '') && (!$rowData[0][1] || $rowData[0][1] == '')) {
                    $quiz_id++;

                } else {
                    $temp_answer = '';
                    if ($rowData[0][1] == $rowData[0][5]) {
                        $temp_answer = 'a';
                    } else if ($rowData[0][2] == $rowData[0][5]) {
                        $temp_answer = 'b';
                    } else if ($rowData[0][3] == $rowData[0][5]) {
                        $temp_answer = 'c';
                    } else if ($rowData[0][4] == $rowData[0][5]) {
                        $temp_answer = 'd';
                    }
                    $question = array(
                        'question' => $rowData[0][0],
                        'option_a' => $rowData[0][1],
                        'option_b' => $rowData[0][2],
                        'option_c' => $rowData[0][3],
                        'option_d' => $rowData[0][4] ? $rowData[0][4] : '',
                        'answer' => $rowData[0][5],
                        'quiz_id' => $quiz_id,
                        'temp_answer' => $temp_answer,
                        'category' => 14
                    );
                    if ($rowData[0][0] && $rowData[0][1] && $rowData[0][2] && $rowData[0][3] && $rowData[0][5]) {
                        $data[] = $question;
                        $this->quests->insert($question);
                    }
                }
            }

            $this->response(array('rows' => $highestRow));

        } catch (Exception $e) {
            $this->response($e->getMessage());
        }
    }

    public function update_quiz_ids_get()
    {
        $this->load->model('quest_model', 'quests');
        $questions = $this->quests->get_all();
        foreach ($questions as $question) {
            /*$quiz_id = ($question->id - 800) / 90 + 1;
            $this->quests->update_field($question->id, 'quiz_id', $quiz_id);*/
            if ($question->id < 891) {
                $this->quests->update_field($question->id, 'quiz_id', 1);
            } else if ($question->id < 981) {
                $this->quests->update_field($question->id, 'quiz_id', 2);
            } else if ($question->id < 1071) {
                $this->quests->update_field($question->id, 'quiz_id', 3);
            } else if ($question->id < 1161) {
                $this->quests->update_field($question->id, 'quiz_id', 4);
            } else if ($question->id < 1251) {
                $this->quests->update_field($question->id, 'quiz_id', 5);
            } else if ($question->id < 1341) {
                $this->quests->update_field($question->id, 'quiz_id', 6);
            }
        }
        $this->response("success");
    }

    public function update_question_quiz_ids_get()
    {
        $this->load->model('quest_model', 'quests');
        $questions = $this->quests->get_all();
        foreach ($questions as $question) {
            $quiz_id = '';
            if ($question->quiz_id == 1) {
                $quiz_id = "39";
            } else if ($question->quiz_id == 2) {
                $quiz_id = "40";
            } else if ($question->quiz_id == 3) {
                $quiz_id = "41";
            } else if ($question->quiz_id == 4) {
                $quiz_id = "42";
            } else if ($question->quiz_id == 5) {
                $quiz_id = "43";
            } else if ($question->quiz_id == 6) {
                $quiz_id = "44";
            }
            $this->quests->update_field($question->id, 'quiz_id', $quiz_id);
        }
        $this->response("success");
    }

    public function move_to_question_table_get()
    {
        $this->load->model('Question_model', 'questions');
        $this->load->model('quest_model', 'quests');

        $quests = $this->quests->get_all();
        foreach ($quests as $quest) {
            $data = array(
                'quiz_id' => $quest->quiz_id,
                'content' => $quest->question,
                'option_a' => $quest->option_a,
                'option_b' => $quest->option_b,
                'option_c' => $quest->option_c,
                'option_d' => $quest->option_d,
                'option_e' => '',
                'option_f' => '',
                'option_g' => '',
                'option_h' => '',
                'answer' => $quest->temp_answer,
                'status' => 1
            );
            $this->questions->insert($data);
        }
        $this->response("success");
    }

    public function move_to_question_table_with_category_get()
    {
        $this->load->model('Question_model', 'questions');
        $this->load->model('quest_model', 'quests');
        $this->load->model('quiz_model', 'quizzes');

        $quests = $this->quests->get_all();
        $result = [];
        foreach ($quests as $quest) {
            $quiz = $this->quizzes->get_first_one_where(array(
                    'category_id' => $quest->category,
                    'title' => 'Quiz ' . $quest->quiz_id)
            );
            if ($quiz && $quiz->id) {
                $data = array(
                    'quiz_id' => $quiz->id,
                    'content' => $quest->question,
                    'option_a' => $quest->option_a,
                    'option_b' => $quest->option_b,
                    'option_c' => $quest->option_c,
                    'option_d' => $quest->option_d,
                    'option_e' => '',
                    'option_f' => '',
                    'option_g' => '',
                    'option_h' => '',
                    'answer' => $quest->temp_answer,
                    'status' => 1
                );
                $result[] = $this->questions->insert($data);
            }
        }
        $this->response($result);
    }

    public function insert_quotes_get()
    {
        $this->load->library('PHPExcel');
        $this->load->model('Quote_model', 'quotes');
        $inputFileName = './' . UPLOAD_FILE_EXCEL . 'Quotes.xlsx';

        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            $data = [];
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                $question = array(
                    'quote' => $rowData[0][0],
                    'author' => $rowData[0][1],
                );
                $data[] = $question;
                if ($rowData[0][0] && $rowData[0][1]) {
                    $this->quotes->insert($question);
                }
            }

            $this->response($data);
            //$this->response("success");

        } catch (Exception $e) {
            $this->response($e->getMessage());
        }

        $this->response("success");
    }

}
