<?php
class Analytics extends CI_Controller {

	public function __construct() {
		parent::__construct();
	
        $this->load->model('user_model');
        $this->load->model('video_model');
        $this->load->model('video_analytics_model');
     
    }

    /**
	 * CONTROLLER
	 *
	 * By default, video dashboard is showed to logged in admin
	 */
    function index($name = "all", $youtube = "all", $country = "all", $category = "all", $sex = "all") {
		$search_name = ($name != "" && $name != "all" ) ? urldecode($name) : "";
		$search_youtube = ($youtube != "" && $youtube != "all") ? urldecode($youtube) : "";
		$search_country = ($country != "" && $country != "all") ? urldecode($country) : "";
		$search_category = ($category != "" && $category != "all") ? urldecode($category) : "";
		$search_sex = ($sex != "" && $sex != "all") ? urldecode($sex) : "";
    	
    	$this->load->library('pagination');
    	//$this->pagination->initialize($opcions);
    	

    	$start = ($this->uri->segment(8)) ? $this->uri->segment(8) : 0;
    	$page["users"] = $this->user_model->get_all_users($start, $search_name, $search_youtube, $search_country, $search_category, $search_sex);
    	
    	$temp_users = explode(",", $this->input->cookie("hold-users"));
    	$this->video_model->get_temp_users_id($page["hold_users"], $page["pair_user_login"], $temp_users);
    	 
    	
    	$page['pagination'] = $this->pagination->create_links();
		$page['page_name'] = 'list-channels-analytics';
        $page['title'] = "Select Channel Analytics";
        $page['type'] = "channels";
        $this->load->view('admin/index', $page);
    	
    }
    
    function channel($user_id,$category = "all"){
    		//$this->load->library('pagination');
    	
    		//$opcions = array();
    		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;
    	
    		//$opcions['per_page'] = $this->config->item("rp");
    		//$opcions['base_url'] = base_url() . "video/videos/{$user_id}/{$category}";
    	
    		$page['videos'] = $this->video_model->get_videos_by_user($user_id, NULL, $start);
    		//$opcions['total_rows'] = $this->video_model->get_count_videos();
    		$channel = $this->user_model->get_channel($user_id);
    		//$opcions['uri_segment'] = 5;
    		//$this->pagination->initialize($opcions);
    		$page['report'] = $this->video_analytics_model->query($user_id);
    		$page['pagination'] = $this->pagination->create_links();
    		$page['users'] = $this->user_model->get_all_users();
    		$page['msg'] = "";
    		$page['page_name'] = 'list-channels-analytics';
    		$page['title'] = "Analytics Videos (Channel: $channel)";
    		$page['channel'] = $channel;
    		$page['owner'] = $user_id;
    		$page['selected'] = ($this->input->post('user_id')) ? $this->input->post('user_id') : '';
    		$page['tasks_options'] = array(
    				"liking_videos" => "Liking videos"
    		);
    		$page['selected2'] = ($this->input->post('video_opt')) ? $this->input->post('video_opt') : '';
    		$page["video_model"] = $this->video_model;
    		$page['type'] = "videos";
    		$this->load->view('admin/index', $page);
    	
    }
}