<?php 

class User_unread_pn_count_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model', 'users');
    }

    public $belongs_to = array(
        'user' => array(
            'model'			=> 'user_model',
            'primary_key'	=> 'user_id'
        )
    );

    protected $order_by = array('created_at', 'DESC');

    // Append tags
    protected function callback_after_get($result)
    {
        $result = parent::callback_after_get($result);

        if ( !empty($result) ) {
            $result->user = $this->users->get($result->user_id);
        }

        return $result;
    }

}