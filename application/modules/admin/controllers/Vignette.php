<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vignette extends Admin_Controller
{

    // Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');

        $this->load->model('Vignette_model', 'vignettes');
        $this->load->model('Vignette_question_model', 'questions');

    }

    public function index()
    {
        redirect('vignette/story');
    }

    public function story() {
        $crud = $this->generate_crud('vignettes');
        if($crud->getState() == 'add') {
            redirect('admin/vignette/add_part3_questions');
            return;
        }

        $crud->columns('id', 'quiz_id', 'title', 'story', 'study', 'explanation', 'image');
        $crud->set_relation('quiz_id', 'quizzes', '({id}) {title}');
        $crud->set_field_upload('image', UPLOAD_QUESTION_IMAGE);
        $crud->set_subject('Questions');
        $this->mPageTitle = 'Vignettes';
        $this->render_crud();
    }

    public function questions()
    {
        $vignettes = $this->vignettes->get_all();
        if(isset($_SESSION["vignette_init"])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $vignette_id = $this->input->post('vignette_id');
                if($vignette_id && $vignette_id != $_SESSION["questions_selected_vignette_id"]) {
                    $_SESSION["questions_selected_vignette_id"] = $vignette_id;
                }
            }
        } else {
            $_SESSION["questions_selected_vignette_id"] = $vignettes[0]->id;
            $_SESSION["vignette_init"] = true;
        }

        $selected_vignette_id = $_SESSION["questions_selected_vignette_id"];

        $crud = $this->generate_crud('vignette_questions');
        $crud->columns('id', 'content', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e',
            'option_f', 'option_g', 'option_h', 'answer', 'status');
        $this->unset_crud_fields('vignette_id');

        $crud->display_as('content', 'Question');
        $crud->callback_column('content', array($this, 'callback_long_wrap_text'));
        $this->mPageTitle = 'Questions';

        $crud->where('vignette_id', $selected_vignette_id);

        $form = $this->form_builder->create_form();
        $this->mViewData['form'] = $form;
        $this->mViewData['vignettes'] = $vignettes;
        $this->mViewData['selected_vignette_id'] = $selected_vignette_id;

        $crud->callback_after_insert(array($this, 'question_after_insert'));
        $crud->unset_add();

        $this->render_crud();
    }

    function question_after_insert($post_array, $primary_key)
    {
        $selected_vignette_id = $_SESSION["questions_selected_vignette_id"];
        $result = $this->questions->update_field($primary_key, "vignette_id", $selected_vignette_id);

        return true;
    }

    public function add_part3_questions() {
        $form = $this->form_builder->create_form(NULL, true);

        if ($form->validate())
        {
            // passed validation
            $filename = '';
            if(is_uploaded_file($_FILES['image']['tmp_name'])) {
                $path = UPLOAD_QUESTION_IMAGE;

                $milliseconds = round(microtime(true) * 1000);
                $filename = "vignette_" . $milliseconds . '.png';
                $file_path = $path . $filename;

                $tmpFile = $_FILES['image']['tmp_name'];
                if(move_uploaded_file($tmpFile, $file_path)) {
                    //$this->system_message->set_success("Successfully uploaded");
                } else {
                    $filename = '';
                    //$this->system_message->set_error("Failed move");
                }
            }

            $story = $this->input->post('story');
            $study = $this->input->post('study');
            $question1 = $this->input->post('question1');
            $question2 = $this->input->post('question2');
            $question3 = $this->input->post('question3');
            $option_a1 = $this->input->post('option_a1');
            $option_a2 = $this->input->post('option_a2');
            $option_a3 = $this->input->post('option_a3');
            $option_b1 = $this->input->post('option_b1');
            $option_b2 = $this->input->post('option_b2');
            $option_b3 = $this->input->post('option_b3');
            $option_c1 = $this->input->post('option_c1');
            $option_c2 = $this->input->post('option_c2');
            $option_c3 = $this->input->post('option_c3');
            $option_d1 = $this->input->post('option_d1');
            $option_d2 = $this->input->post('option_d2');
            $option_d3 = $this->input->post('option_d3');
            $option_e1 = $this->input->post('option_e1');
            $option_e2 = $this->input->post('option_e2');
            $option_e3 = $this->input->post('option_e3');
            $option_f1 = $this->input->post('option_f1');
            $option_f2 = $this->input->post('option_f2');
            $option_f3 = $this->input->post('option_f3');
            $option_g1 = $this->input->post('option_g1');
            $option_g2 = $this->input->post('option_g2');
            $option_g3 = $this->input->post('option_g3');
            $option_h1 = $this->input->post('option_h1');
            $option_h2 = $this->input->post('option_h2');
            $option_h3 = $this->input->post('option_h3');
            $answer1 = $this->input->post('answer1');
            $answer2 = $this->input->post('answer2');
            $answer3 = $this->input->post('answer3');

            $new_vignette = array(
                'story' => $story,
                'study' => $study,
                'image' => $filename
            );
            $vignette_id = $this->vignettes->insert($new_vignette);
            $q1 = array(
                'vignette_id' => $vignette_id,
                'content' => $question1,
                'option_a' => $option_a1,
                'option_b' => $option_b1,
                'option_c' => $option_c1,
                'option_d' => $option_d1,
                'option_e' => $option_e1,
                'option_f' => $option_f1,
                'option_g' => $option_g1,
                'option_h' => $option_h1,
                'answer'  => $answer1,
                'status' => 1
            );
            $q1_id = $this->questions->insert($q1);

            $q2 = array(
                'vignette_id' => $vignette_id,
                'content' => $question2,
                'option_a' => $option_a2,
                'option_b' => $option_b2,
                'option_c' => $option_c2,
                'option_d' => $option_d2,
                'option_e' => $option_e2,
                'option_f' => $option_f2,
                'option_g' => $option_g2,
                'option_h' => $option_h2,
                'answer'  => $answer2,
                'status' => 1
            );
            $q2_id = $this->questions->insert($q2);

            $q3 = array(
                'vignette_id' => $vignette_id,
                'content' => $question3,
                'option_a' => $option_a3,
                'option_b' => $option_b3,
                'option_c' => $option_c3,
                'option_d' => $option_d3,
                'option_e' => $option_e3,
                'option_f' => $option_f3,
                'option_g' => $option_g3,
                'option_h' => $option_h3,
                'answer'  => $answer3,
                'status' => 1
            );
            $q3_id = $this->questions->insert($q3);

            $error_message = null;
            if(!$vignette_id) {
                $error_message = "Error occurred while adding vignette";
            }
            if(!$q1_id) {
                if (!$error_message) {
                    $error_message = "Error occurred while adding question1";
                } else {
                    $error_message .= "question1";
                }
            }
            if(!$q2_id) {
                if (!$error_message) {
                    $error_message = "Error occurred while adding question2";
                } else {
                    $error_message .= "question2";
                }
            }
            if(!$q3_id) {
                if (!$error_message) {
                    $error_message = "Error occurred while adding question3";
                } else {
                    $error_message .= "question3";
                }
            }

            if($error_message) {
                $this->system_message->set_error($error_message);
            } else {
                $this->system_message->set_success("Successfully added");
            }

            refresh();
        }

        $this->mPageTitle = 'Add Part3 Questions';

        $this->mViewData['form'] = $form;
        $this->render('quiz/add_part3_questions');
    }


}