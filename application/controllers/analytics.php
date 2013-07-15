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
    function index($name = "all", $youtube = "all", $country = "all", $category = "all", $sex = "all") {
    	$this->load->library('pagination');
    	//$this->pagination->initialize($opcions);
    	
    	$search_name = ($name != "" && $name != "all" ) ? urldecode($name) : "";
    	$search_youtube = ($youtube != "" && $youtube != "all") ? urldecode($youtube) : "";
    	$search_country = ($country != "" && $country != "all") ? urldecode($country) : "";
    	$search_category = ($category != "" && $category != "all") ? urldecode($category) : "";
    	$search_sex = ($sex != "" && $sex != "all") ? urldecode($sex) : "";
    	
    	$page["users"] = $this->user_model->get_all_users($start, $search_name, $search_youtube, $search_country, $search_category, $search_sex);
    	
    	$temp_users = explode(",", $this->input->cookie("hold-users"));
    	$this->video_model->get_temp_users_id($page["hold_users"], $page["pair_user_login"], $temp_users);
    	 
    	
    	$page['pagination'] = $this->pagination->create_links();
		$page['page_name'] = 'list-channels-analytics';
        $page['title'] = "Select Channel Analytics";
        $this->load->view('admin/index', $page);
    }
}