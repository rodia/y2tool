<?php
class Analytics extends CI_Controller {

	public function __construct() {
		parent::__construct();
	
        $this->load->model('user_model');
        $this->load->model('video_model');
     
    }

    /**
	 * CONTROLLER
	 *
	 * By default, video dashboard is showed to logged in admin
	 */
    function index() {
    	$this->load->library('pagination');
    	//$this->pagination->initialize($opcions);
    	$page['pagination'] = $this->pagination->create_links();
		$page['page_name'] = 'list-channels-analytics';
        $page['title'] = "Select Channel Analytics";
        $this->load->view('admin/index', $page);
    }
}