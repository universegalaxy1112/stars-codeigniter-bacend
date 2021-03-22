<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Exam extends Admin_Controller
{

    // Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');

        $this->load->model('Part_model', 'parts');
        $this->load->model('Category_model', 'categories');
        $this->load->model('Exam_model', 'exams');
        $this->load->model('Exam_question_model', 'exam_questions');
    }

    public function index()
    {
        redirect('exam/post');
    }

    // Grocery CRUD - Exam Posts
    public function post()
    {
        $parts = $this->parts->get_all();
        if (isset($_SESSION["exam_questions_session_init"])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $part_id = $this->input->post('part_id');
                $category_id = $this->input->post('category_id');
                $exam_id = $this->input->post('exam_id');
                if ($part_id && $part_id != $_SESSION["exam_questions_selected_part_id"]) {
                    $_SESSION["exam_questions_selected_part_id"] = $part_id;
                    $categories = $this->categories->get_where('part_id', $part_id);
                    if (count($categories) > 0) {
                        $_SESSION["exam_questions_selected_category_id"] = $categories[0]->id;
                        $exams = $this->exams->get_where('category_id', $_SESSION["exam_questions_selected_category_id"]);
                        if (count($exams) > 0) {
                            $_SESSION["exam_questions_selected_exam_id"] = $exams[0]->id;
                        } else {
                            $_SESSION["exam_questions_selected_exam_id"] = '';
                        }
                    } else {
                        $_SESSION["exam_questions_selected_category_id"] = '';
                        $_SESSION["exam_questions_selected_exam_id"] = '';
                    }
                } else if ($category_id && $category_id != $_SESSION["exam_questions_selected_category_id"]) {
                    $_SESSION["exam_questions_selected_category_id"] = $category_id;
                    $exams = $this->exams->get_where('category_id', $category_id);
                    if (count($exams) > 0) {
                        $_SESSION["exam_questions_selected_exam_id"] = $exams[0]->id;
                    } else {
                        $_SESSION["exam_questions_selected_exam_id"] = '';
                    }
                } else if ($exam_id && $exam_id != $_SESSION["exam_questions_selected_exam_id"]) {
                    $_SESSION["exam_questions_selected_exam_id"] = $exam_id;
                }
            }
        } else {
            $_SESSION["exam_questions_selected_part_id"] = $parts[0]->id;
            $categories = $this->categories->get_where('part_id', $_SESSION["exam_questions_selected_part_id"]);
            $_SESSION["exam_questions_selected_category_id"] = $categories[0]->id;
            $exams = $this->exams->get_where('category_id', $_SESSION["exam_questions_selected_category_id"]);
            $_SESSION["exam_questions_selected_exam_id"] = $exams[0]->id;
            $_SESSION["exam_questions_session_init"] = true;
        }

        $selected_part_id = $_SESSION["exam_questions_selected_part_id"];
        $selected_category_id = $_SESSION["exam_questions_selected_category_id"];
        $selected_exam_id = $_SESSION["exam_questions_selected_exam_id"];

        $crud = $this->generate_crud('exam_questions');
        if ($selected_part_id && $selected_part_id == 3) {
            $crud->columns('id', 'content', 'image', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e',
                'option_f', 'option_g', 'option_h', 'click_a', 'click_b', 'click_c', 'click_d', 'click_e', 'click_f', 'click_g', 'click_h', 'answer', 'status');
            $this->unset_crud_fields('exam_id');

        } else {
            $crud->columns('id', 'content', 'image', 'option_a', 'option_b', 'option_c', 'option_d', 'answer', 'status');
            $this->unset_crud_fields('exam_id', 'option_e', 'option_f', 'option_g', 'option_h', 'click_a', 'click_b', 'click_c', 'click_d', 'click_e', 'click_f', 'click_g', 'click_h');
        }

        $crud->set_field_upload('image', UPLOAD_QUESTION_IMAGE);
        $crud->display_as('content', 'Question');
        $crud->callback_column('content', array($this, 'callback_long_wrap_text'));
        $this->mPageTitle = 'Exam_Questions';

        $crud->where('exam_id', $selected_exam_id);
        //$crud->where($selected_date_type.' <=', $current_date_to);

        $form = $this->form_builder->create_form();
        $this->mViewData['form'] = $form;
        $this->mViewData['parts'] = $parts;
        $this->mViewData['selected_part_id'] = $selected_part_id;
        $this->mViewData['categories'] = $this->categories->get_where('part_id', $selected_part_id);
        $this->mViewData['selected_category_id'] = $selected_category_id;
        $this->mViewData['exams'] = $this->exams->get_where('category_id', $selected_category_id);
        $this->mViewData['selected_exam_id'] = $selected_exam_id;

        $crud->callback_after_insert(array($this, 'question_after_insert'));

        $this->render_crud();
    }

    function question_after_insert($post_array, $primary_key)
    {
        $selected_exam_id = $_SESSION["exam_questions_selected_exam_id"];
        $result = $this->exam_questions->update_field($primary_key, "exam_id", $selected_exam_id);

        return true;
    }


    public function exams()
    {
        $crud = $this->generate_crud('exams');
        $crud->columns('id', 'category_id', 'title');
        $crud->callback_column('id', array($this, 'callback_short_wrap_text'));
        $crud->set_relation('category_id', 'categories', '({id}) {title}');

        $this->mPageTitle = 'Exams';
        $this->render_crud();
    }


}
