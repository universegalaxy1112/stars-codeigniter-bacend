<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Palooza extends Admin_Controller
{

    // Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');

        $this->load->model('Question_palooza_question_model', 'question_palooza_questions');
        $this->load->model('Triad_palooza_question_model', 'triad_palooza_questions');
    }

    public function index()
    {
        redirect('palooza/question_questions');
    }

    // Grocery CRUD - Exam Posts
    public function question_questions()
    {
        $crud = $this->generate_crud('question_palooza_questions');
        $crud->columns('id', 'content', 'option_a', 'option_b', 'answer', 'status');
        $this->unset_crud_fields('image', 'option_c', 'option_d', 'option_e', 'option_f', 'option_g', 'option_h', 'click_c', 'click_d', 'click_e', 'click_f', 'click_g', 'click_h');

        $crud->callback_column('id', array($this, 'callback_short_wrap_text'));

        $crud->display_as('content', 'Question');

        $this->mPageTitle = 'Question Palooza';
        $this->render_crud();
    }

    public function triad_questions()
    {
        $crud = $this->generate_crud('triad_palooza_questions');
        $crud->columns('id', 'content', 'option_a', 'option_b', 'answer', 'status');
        $this->unset_crud_fields('image', 'option_c', 'option_d', 'option_e', 'option_f', 'option_g', 'option_h', 'click_c', 'click_d', 'click_e', 'click_f', 'click_g', 'click_h');

        $crud->callback_column('id', array($this, 'callback_short_wrap_text'));

        $crud->display_as('content', 'Question');

        $this->mPageTitle = 'Triad Palooza';
        $this->render_crud();
    }


}
