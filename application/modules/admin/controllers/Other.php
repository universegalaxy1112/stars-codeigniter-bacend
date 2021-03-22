<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Other extends Admin_Controller
{

    // Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');

        $this->load->model('Study_guide_model', 'study_guides');
    }

    public function index()
    {
        redirect('other/study');
    }

    public function study() {
        $crud = $this->generate_crud('study_guides');
        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_read();

        $crud->columns('place', 'image', 'link');
        $crud->set_field_upload('image', UPLOAD_STUDY_IMAGE);
        $crud->set_subject('Study Guides');
        $this->mPageTitle = 'Study Guides';
        $this->render_crud();
    }


}