<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');

        $this->load->model('Constant_model', 'constants');
    }

	public function index()
	{
        redirect('admin/settings/settings');
	}

    public function quotes()
    {
        $crud = $this->generate_crud('quotes');
        $crud->columns('quote', 'author');
        $this->mPageTitle = 'Quotes';
        $this->render_crud();
    }

    public function settings() {
        $form = $this->form_builder->create_form();

        $constant_about = $this->constants->get_first_one_where('key', 'about');
        $constant_mnemonic = $this->constants->get_first_one_where('key', 'mnemonic');
        $constant_review_code = $this->constants->get_first_one_where('key', 'review_code');

        $constant_q_palooza_day_of_month = $this->constants->get_first_one_where('key', 'q_palooza_day_of_month');
        $constant_q_palooza_before_days = $this->constants->get_first_one_where('key', 'q_palooza_before_days');
        $constant_q_palooza_active_days = $this->constants->get_first_one_where('key', 'q_palooza_active_days');

        $constant_t_palooza_day_of_month = $this->constants->get_first_one_where('key', 't_palooza_day_of_month');
        $constant_t_palooza_before_days = $this->constants->get_first_one_where('key', 't_palooza_before_days');
        $constant_t_palooza_active_days = $this->constants->get_first_one_where('key', 't_palooza_active_days');

        $about = $constant_about->value;
        $mnemonic = $constant_mnemonic->value;
        $review_code = $constant_review_code->value;

        $q_palooza_day_of_month = $constant_q_palooza_day_of_month->value;
        $q_palooza_before_days = $constant_q_palooza_before_days->value;
        $q_palooza_active_days = $constant_q_palooza_active_days->value;

        $t_palooza_day_of_month = $constant_t_palooza_day_of_month->value;
        $t_palooza_before_days = $constant_t_palooza_before_days->value;
        $t_palooza_active_days = $constant_t_palooza_active_days->value;

        if ($form->validate())
        {
            // passed validation
            $post_about = $this->input->post('about');
            $post_mnemonic = $this->input->post('mnemonic');
            $post_review_mode = $this->input->post('review_mode');

            $post_q_palooza_day_of_month = $this->input->post('q_palooza_day_of_month');
            if($post_q_palooza_day_of_month < 1)
                $post_q_palooza_day_of_month = '1';
            else if($post_q_palooza_day_of_month > 28)
                $post_q_palooza_day_of_month = '28';
            $post_q_palooza_before_days = $this->input->post('q_palooza_before_days');
            if($post_q_palooza_before_days < 0)
                $post_q_palooza_before_days = '0';
            else if($post_q_palooza_before_days > 10)
                $post_q_palooza_before_days = '10';
            $post_q_palooza_active_days = $this->input->post('q_palooza_active_days');
            if($post_q_palooza_active_days < 0)
                $post_q_palooza_active_days = '0';
            else if($post_q_palooza_active_days > 10)
                $post_q_palooza_active_days = '10';

            $post_t_palooza_day_of_month = $this->input->post('t_palooza_day_of_month');
            if($post_t_palooza_day_of_month < 1)
                $post_t_palooza_day_of_month = '1';
            else if($post_t_palooza_day_of_month > 28)
                $post_t_palooza_day_of_month = '28';
            $post_t_palooza_before_days = $this->input->post('t_palooza_before_days');
            if($post_t_palooza_before_days < 0)
                $post_t_palooza_before_days = '0';
            else if($post_t_palooza_before_days > 10)
                $post_t_palooza_before_days = '10';
            $post_t_palooza_active_days = $this->input->post('t_palooza_active_days');
            if($post_t_palooza_active_days < 0)
                $post_t_palooza_active_days = '0';
            else if($post_t_palooza_active_days > 10)
                $post_t_palooza_active_days = '10';

            // proceed to create user
            $result1 = $this->constants->update_field($constant_about->id, 'value', $post_about);
            $result2 = $this->constants->update_field($constant_mnemonic->id, 'value', $post_mnemonic);
            $result3 = $this->constants->update_field($constant_review_code->id, 'value', $post_review_mode == '1' ? '4.3.01' : 'x.x.xx');

            $result4 = $this->constants->update_field($constant_q_palooza_day_of_month->id, 'value', $post_q_palooza_day_of_month);
            $result5 = $this->constants->update_field($constant_q_palooza_before_days->id, 'value', $post_q_palooza_before_days);
            $result6 = $this->constants->update_field($constant_q_palooza_active_days->id, 'value', $post_q_palooza_active_days);

            $result7 = $this->constants->update_field($constant_t_palooza_day_of_month->id, 'value', $post_t_palooza_day_of_month);
            $result8 = $this->constants->update_field($constant_t_palooza_before_days->id, 'value', $post_t_palooza_before_days);
            $result9 = $this->constants->update_field($constant_t_palooza_active_days->id, 'value', $post_t_palooza_active_days);

            if ($result1 && $result2 && $result3 && $result4 && $result5 && $result6 && $result7 && $result8 && $result9)
            {
                // success
                $this->system_message->set_success("successfully set");
            }
            else
            {
                $this->system_message->set_error("failed");
            }
            refresh();
        }

        $this->mPageTitle = 'Settings';
        $this->mViewData['form'] = $form;
        $this->mViewData['constant'] = array(
            'about' => $about,
            'mnemonic' => $mnemonic,
            'review_code' => $review_code,
            'q_palooza_day_of_month' => $q_palooza_day_of_month,
            'q_palooza_before_days' => $q_palooza_before_days,
            'q_palooza_active_days' => $q_palooza_active_days,
            't_palooza_day_of_month' => $t_palooza_day_of_month,
            't_palooza_before_days' => $t_palooza_before_days,
            't_palooza_active_days' => $t_palooza_active_days,
        );
        $this->render('util/settings');
    }

}
