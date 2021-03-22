<?php

/**
 * Base Controller for Admin module
 */
class Admin_Controller extends MY_Controller {

	protected $mUsefulLinks = array();

	// Grocery CRUD or Image CRUD
	protected $mCrud;
	protected $mCrudUnsetFields;

	// Constructor
	public function __construct()
	{
		parent::__construct();

		// only login users can access Admin Panel
		$this->verify_login();

		// store site config values
		$this->mUsefulLinks = $this->mConfig['useful_links'];
	}

	// Render template (override parent)
	protected function render($view_file, $layout = 'default')
	{
		// load skin according to user role
		$config = $this->mConfig['adminlte'];
		$this->mBodyClass = $config['body_class'][$this->mUserMainGroup];

		// additional view data
		$this->mViewData['useful_links'] = $this->mUsefulLinks;

		parent::render($view_file);
	}

	// Initialize CRUD table via Grocery CRUD library
	// Reference: http://www.grocerycrud.com/
	protected function generate_crud($table, $subject = '')
	{
		// create CRUD object
		$this->load->library('Grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->set_table($table);

		// auto-generate subject
		if ( empty($subject) )
		{
			$crud->set_subject(humanize(singular($table)));
		}

		// load settings from: application/config/grocery_crud.php
		$this->load->config('grocery_crud');
		$this->mCrudUnsetFields = $this->config->item('grocery_crud_unset_fields');

		if ($this->config->item('grocery_crud_unset_jquery'))
			$crud->unset_jquery();

		if ($this->config->item('grocery_crud_unset_jquery_ui'))
			$crud->unset_jquery_ui();

		if ($this->config->item('grocery_crud_unset_print'))
			$crud->unset_print();

		if ($this->config->item('grocery_crud_unset_export'))
			$crud->unset_export();

		if ($this->config->item('grocery_crud_unset_read'))
			$crud->unset_read();

		foreach ($this->config->item('grocery_crud_display_as') as $key => $value)
			$crud->display_as($key, $value);

		// other custom logic to be done outside
		$this->mCrud = $crud;
		return $crud;
	}
	
	// Set field(s) to color picker
	protected function set_crud_color_picker()
	{
		$args = func_get_args();
		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		foreach ($args as $field)
		{
			$this->mCrud->callback_field($field, array($this, 'callback_color_picker'));
		}
	}

	public function callback_color_picker($value = '', $primary_key = NULL, $field = NULL)
	{
		$name = $field->name;
		return "<input type='color' name='$name' value='$value' style='width:80px' />";
	}

	// Append additional fields to unset from CRUD
	protected function unset_crud_fields()
	{
		$args = func_get_args();
		if(isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		$this->mCrudUnsetFields = array_merge($this->mCrudUnsetFields, $args);
	}

	// Initialize CRUD album via Image CRUD library
	// Reference: http://www.grocerycrud.com/image-crud
	protected function generate_image_crud($table, $url_field, $upload_path, $order_field = 'pos', $title_field = '')
	{
		// create CRUD object
		$this->load->library('Image_crud');
		$crud = new image_CRUD();
		$crud->set_table($table);
		$crud->set_url_field($url_field);
		$crud->set_image_path($upload_path);

		// [Optional] field name of image order (e.g. "pos")
		if ( !empty($order_field) )
		{
			$crud->set_ordering_field($order_field);
		}

		// [Optional] field name of image caption (e.g. "caption")
		if ( !empty($title_field) )
		{
			$crud->set_title_field($title_field);
		}

		// other custom logic to be done outside
		$this->mCrud = $crud;
		return $crud;
	}

	// Render CRUD
	protected function render_crud()
	{
		// logic specific for Grocery CRUD only
		$crud_obj_name = strtolower(get_class($this->mCrud));
		if ($crud_obj_name==='grocery_crud')
		{
			$this->mCrud->unset_fields($this->mCrudUnsetFields);	
		}

		// render CRUD
		$crud_data = $this->mCrud->render();

		// append scripts
		$this->add_stylesheet($crud_data->css_files, FALSE);
		$this->add_script($crud_data->js_files, TRUE, 'head');

		// display view
		$this->mViewData['crud_output'] = $crud_data->output;
		$this->render('crud');
	}


    ///////////////////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////////////
    ///
    public function callback_long_wrap_text ($value, $row) {
        return wordwrap($value, 50, "<br>", true);
        //return '<p class="text-wrap1">'.$value.'</p>';
    }

    public function callback_wrap_text ($value, $row) {
        return wordwrap($value, 30, "<br>", true);
        //return character_limiter(strip_tags($value), 50, "...");
        //return '<p class="text-wrap1">'.$value.'</p>';
    }

    public function callback_short_wrap_text ($value, $row) {
        return wordwrap($value, 10, "<br>", true);
    }

    protected function set_bulk_upload($upload_url, $message = '') {
        $this->load->library('form_builder');
        if ($message == 'succeed_data_import') {
            $this->system_message->set_success("Successfully imported");
        } else if ($message == 'failed_data_import') {
            $this->system_message->set_error("Failed import");
        } else if($message == 'no_data_import') {
            $this->system_message->set_error("There is not correct data to import");
        }

        $this->mViewData['allow_bulk_upload'] = true;
        $this->mViewData['upload_url'] = $upload_url;

        $this->mViewData['form'] = $this->form_builder->create_form();
    }

    public function upload_bulk_data($callback_data_process, $redirect_url) {
        $this->load->library('PHPExcel');
        $fileName = time() . $_FILES['fileImport']['name'];

        $config['upload_path'] = './'.UPLOAD_FILE_EXCEL;
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 10000;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('fileImport'))
            $this->upload->display_errors();

        $media = $this->upload->data('fileImport');
        $inputFileName = './'.UPLOAD_FILE_EXCEL.$fileName;

        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);

            /*$cell = $objPHPExcel->getCellByColumnAndRow(3, 5);
            $cell_value = PHPExcel_Style_NumberFormat::toFormattedString($cell->getCalculatedValue(), 'hh:mm:ss');*/

        } catch (Exception $e) {
            if($redirect_url) {
                redirect($redirect_url . '/failed_data_import');
            }
            //die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $result = false;
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if($callback_data_process) {
                $callback_result = call_user_func($callback_data_process, $rowData, $row);
                $result = $result || $callback_result;
            }
        }

        $this->load->helper("file");
        delete_files($media['file_path']);
        if($redirect_url) {
            if($result) {
                redirect($redirect_url . '/succeed_data_import');
            } else {
                redirect($redirect_url . '/no_data_import');
            }
        }
    }

    public function getTimeValue($time_value) {
        //if($time_value=='Closed') return 'Closed';
        if(is_numeric($time_value)) {
            return PHPExcel_Style_NumberFormat::toFormattedString($time_value, 'hh:mm');
        } else {
            return 'Closed';
        }

        /*$time =  $time_value * 86400;
        $hours = round($time / 3600);
        $minutes = round($time / 60) - ($hours * 60);
        $seconds = round($time) - ($hours * 3600) - ($minutes * 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);*/
    }

}