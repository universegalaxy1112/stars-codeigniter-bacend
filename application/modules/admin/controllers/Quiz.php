<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz extends Admin_Controller {

    // Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');

        $this->load->model('Part_model', 'parts');
        $this->load->model('Question_model', 'questions');
        $this->load->model('Quiz_model', 'quizzes');
        $this->load->model('Category_model', 'categories');
    }

	public function index()
	{
		redirect('quiz/post');
	}

	public function dummy() {
        $crud = $this->generate_crud('quests');
        $crud->columns('id', 'question',  'image', 'optiona', 'optionb', 'optionc', 'optiond', 'answer', 'quiz_name', 'quiz_id');
        $this->mPageTitle = 'Dummy';
        $this->render_crud();
    }

	// Grocery CRUD - Quiz Posts
	public function post()
	{
        $parts = $this->parts->get_all();
        if(isset($_SESSION["questions_session_init"])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $part_id = $this->input->post('part_id');
                $category_id = $this->input->post('category_id');
                $quiz_id = $this->input->post('quiz_id');
                if($part_id && $part_id != $_SESSION["questions_selected_part_id"]) {
                    $_SESSION["questions_selected_part_id"] = $part_id;
                    $categories = $this->categories->get_where('part_id', $part_id);
                    if(count($categories)>0) {
                        $_SESSION["questions_selected_category_id"] = $categories[0]->id;
                        $quizzes = $this->quizzes->get_where('category_id', $_SESSION["questions_selected_category_id"]);
                        if(count($quizzes)>0) {
                            $_SESSION["questions_selected_quiz_id"] = $quizzes[0]->id;
                        } else {
                            $_SESSION["questions_selected_quiz_id"] = '';
                        }
                    } else {
                        $_SESSION["questions_selected_category_id"] = '';
                        $_SESSION["questions_selected_quiz_id"] = '';
                    }
                } else if($category_id && $category_id != $_SESSION["questions_selected_category_id"]) {
                    $_SESSION["questions_selected_category_id"] = $category_id;
                    $quizzes = $this->quizzes->get_where('category_id', $category_id);
                    if(count($quizzes)>0) {
                        $_SESSION["questions_selected_quiz_id"] = $quizzes[0]->id;
                    } else {
                        $_SESSION["questions_selected_quiz_id"] = '';
                    }
                } else if($quiz_id && $quiz_id != $_SESSION["questions_selected_quiz_id"]) {
                    $_SESSION["questions_selected_quiz_id"] = $quiz_id;
                }
            }
        } else {
            $_SESSION["questions_selected_part_id"] = $parts[0]->id;
            $categories = $this->categories->get_where('part_id', $_SESSION["questions_selected_part_id"]);
            $_SESSION["questions_selected_category_id"] = $categories[0]->id;
            $quizzes = $this->quizzes->get_where('category_id', $_SESSION["questions_selected_category_id"]);
            $_SESSION["questions_selected_quiz_id"] = $quizzes[0]->id;
            $_SESSION["questions_session_init"] = true;
        }

        $selected_part_id = $_SESSION["questions_selected_part_id"];
        $selected_category_id = $_SESSION["questions_selected_category_id"];
        $selected_quiz_id = $_SESSION["questions_selected_quiz_id"];

		$crud = $this->generate_crud('questions');
        if($selected_part_id && $selected_part_id == 3) {
            $crud->columns('id', 'content',  'image', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e',
                'option_f', 'option_g', 'option_h', 'answer', 'status');
            $this->unset_crud_fields('quiz_id', 'click_a', 'click_b', 'click_c', 'click_d', 'click_e', 'click_f', 'click_g', 'click_h');

        } else {
            $crud->columns('id', 'content',  'image', 'option_a', 'option_b', 'option_c', 'option_d', 'answer', 'status');
            $this->unset_crud_fields('quiz_id', 'option_e', 'option_f', 'option_g', 'option_h', 'click_a', 'click_b', 'click_c', 'click_d', 'click_e', 'click_f', 'click_g', 'click_h');
        }

        $crud->set_field_upload('image', UPLOAD_QUESTION_IMAGE);
		$crud->display_as('content', 'Question');
		$crud->callback_column('content', array($this, 'callback_long_wrap_text'));
		$this->mPageTitle = 'Questions';

        $crud->where('quiz_id', $selected_quiz_id);
        //$crud->where($selected_date_type.' <=', $current_date_to);

        $form = $this->form_builder->create_form();
        $this->mViewData['form'] = $form;
        $this->mViewData['parts'] = $parts;
        $this->mViewData['selected_part_id'] = $selected_part_id;
		$this->mViewData['categories'] = $this->categories->get_where('part_id', $selected_part_id);
        $this->mViewData['selected_category_id'] = $selected_category_id;
        $this->mViewData['quizzes'] = $this->quizzes->get_where('category_id', $selected_category_id);
        $this->mViewData['selected_quiz_id'] = $selected_quiz_id;

        $crud->callback_after_insert(array($this, 'question_after_insert'));

		$this->render_crud();
	}

    function question_after_insert($post_array, $primary_key)
    {
        $selected_quiz_id = $_SESSION["questions_selected_quiz_id"];
        $result = $this->questions->update_field($primary_key, "quiz_id", $selected_quiz_id);

        return true;
    }

    // Grocery CRUD - Quiz Categories
    public function parts()
    {
        $crud = $this->generate_crud('parts');
        $crud->columns('id', 'title');

        $crud->callback_column('id', array($this, 'callback_short_wrap_text'));

        $crud->unset_read();
        $crud->unset_edit();
        $crud->unset_add();

        $this->mPageTitle = 'Parts';
        $this->render_crud();
    }

	// Grocery CRUD - Quiz Categories
	public function category()
	{
		$crud = $this->generate_crud('categories');
		$crud->columns('id', 'part_id', 'title', 'image');

        $crud->callback_column('id', array($this, 'callback_short_wrap_text'));
		$crud->set_relation('part_id', 'parts', '({id}) {title}');

        $state = $crud->getState();
        if ($state == 'list' || $state == 'success' || $state == 'read') { // || $state == 'ajax_list_info' || $state == 'ajax_list'
            $crud->callback_column('image', array($this, 'callback_category_image'));
        } else {
            $crud->set_field_upload('image', UPLOAD_CATEGORY_IMAGE);
        }

		$this->mPageTitle = 'Categories';
		//$this->mViewData['crud_note'] = modules::run('adminlte/widget/btn', 'Sort Order', 'blog/category_sortable');
		$this->render_crud();
	}

    public function callback_category_image($value, $row) {
        if(strlen($value)==0) {
            return "";
        }
        if (strpos($value, 'http') !== false) {
            return "<img style='width:50px; height:50px object-fit:cover' class='img-circle' src='".$value."'></>";

        } else {
            $photo = base_url() . UPLOAD_CATEGORY_IMAGE . $value;
            return "<a href='". $photo ."' class='image-thumbnail'><img style='width:160px; height:90px; object-fit:cover' src='".$photo."'/></a>";
        }
    }

    // Sortable - Quiz Categories
    public function category_sortable()
    {
        $this->load->library('sortable');
        $this->sortable->init('blog_category_model');
        $this->mViewData['content'] = $this->sortable->render('{title}', 'blog/category');
        $this->mPageTitle = 'Categories';
        $this->render('general');
    }

    public function quizzes()
    {
        $crud = $this->generate_crud('quizzes');
        $crud->columns('id', 'category_id', 'title');
        $crud->callback_column('id', array($this, 'callback_short_wrap_text'));
        $crud->set_relation('category_id', 'categories', '({id}) {title}');

        $this->mPageTitle = 'Quizzes';
        $this->render_crud();
    }

	// Grocery CRUD - Quiz Tags
	public function tag()
	{
		$crud = $this->generate_crud('blog_tags');
		$this->mPageTitle = 'Quiz Tags';
		$this->render_crud();
	}
}
