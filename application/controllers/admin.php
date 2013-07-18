<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/* 	@author : Joyonta Roy
 * 	date	: 29 september,2011
 * 	University Of Dhaka
 */

class Admin extends CI_Controller {
	/**
	 * Construct
	 */
    function __construct() {
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
		//$this->dashboard();
//		redirect("admin/users");
		redirect("video/bulk");
    }
	/**
	 *
	 * @param string $category_id
	 */
    function dashboard($category_id = 'all') {
        $page['page_name'] = 'dashboard';
        $page['title'] = "Video Dashboard";
        $this->load->view('admin/index', $page);
    }

    /**
	 * Admin login function.
	 */
    function login() {
        $username = $this->input->post('username');
        $password = md5($this->input->post('password'));
        $validate = $this->user_model->admin_login($username, $password);
        if ($validate) {
            redirect('/video/bulk', 'refresh');
        } else {
            redirect('video/bulk', 'refresh');
		}
    }
    /**
	 * Admin logout. Resetting admin session data
	 */
    function logout() {
        $newdata = array(
            'user_id' => "",
            'username' => "",
            'password' => "",
            'email' => "",
            'logged_in' => 0,
            'login' => 0,
        );
        $this->session->unset_userdata($newdata);
        $this->session->sess_destroy();
        redirect('admin/index', 'refresh');
    }

    /**
	 * Manage admin account
	 *
	 * update admin username, password, name, email address
	 */
    function register() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $data['username'] = '';
        $data['password'] = '';
        $data['name'] = '';
        $data['email'] = '';
//load rules
        $rules = $this->config->item('register_rules');
//default msg
        $data['msg'] = $this->lang->line('form_msg');

        if (isset($_POST['submit'])) {
//the user has submitted the form
//get the user input
            $data['username'] = $this->input->post('username');
            $data['password'] = md5($this->input->post('password'));
            $data['name'] = $this->input->post('name');
            $data['email'] = $this->input->post('email');

            $this->form_validation->set_rules($rules); //check with the rules


            if ($this->form_validation->run() == FALSE) {
//validation failed
                $data['msg'] = $this->lang->line('form_error');
                $data['page_name'] = 'register_form';
                $data['title'] = "Manage admin account";
                $data['account_info'] = $this->user_model->retrieve_admin();

                $this->load->view('admin/index', $data);
            } else {
//validation passed
                $dbdata = array(
                    'username' => $this->input->post('username'),
                    'password' => md5($this->input->post('password')),
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'registered_date' => time(),
                    'type' => $this->input->post('type')
                );
                $data['msg'] = $this->lang->line('form_success');
                $this->user_model->register_admin($dbdata);
                $data['page_name'] = 'register_form';
                $data['title'] = "Manage admin account";
                $data['account_info'] = $this->user_model->retrieve_admin();

                $this->load->view('admin/index', $data);
            }
        } else {
            $data['page_name'] = 'register_form';
            $data['title'] = "Manage admin account";
            $data['account_info'] = $this->user_model->retrieve_admin();
            $this->load->view('admin/index', $data);
        }
    }
	/**
	 *
	 * @param int $id ID of user
	 */
    function edit($id) {
        $admin = $this->user_model->get_user_details($id);
        $data['id'] = $admin['id'];
        $data['username'] = $admin['username'];
        $data['name'] = $admin['name'];
        $data['email'] = $admin['email'];
        $data['type'] = $admin['type'];
        $data['page_name'] = 'edit_form';
        $data['title'] = "Edit admin";
        $data['msg'] = "";
        $this->load->view('admin/index', $data);
    }
	/**
	 * Delete on user by database,
	 * @param int $id
	 */
	public function delete($id) {
		redirect("admin/admins?success=true&msg=" . ($this->user_model->delete_user($id) ? "User removed success" : "The user selected not possible remove."));
//		$page['users'] = $this->user_model->get_all_admins();
//        $page['page_name'] = 'admins';
//        $page['title'] = "Admins List";
//        $this->load->view('admin/index', $page);
	}
	/**
	 * CONTROLLERS
	 *
	 * Save of admin users.
	 */
    function save() {
        if (isset($_POST['submit'])) {

            $rules = $this->config->item('edit_rules');
            $this->form_validation->set_rules($rules);
            $username = $this->input->post('username');
            $id = $this->input->post('id');
            if ($this->form_validation->run() === FALSE) {
                $data['msg'] = $this->lang->line('form_error');
                $admin = $this->user_model->get_user_details($this->input->post('id'));
                $data['id'] = $admin['id'];
                $data['username'] = $admin['username'];
                $data['name'] = $admin['name'];
                $data['email'] = $admin['email'];
                $data['type'] = $admin['type'];
                $data['page_name'] = 'edit_form';
                $data['title'] = "Edit admin";
                $this->load->view('admin/index', $data);
            } else {
                //data prep
                $dbdata = array(
                    'email' => $this->input->post('email'),
                    'name' => $this->input->post('name'),
                );
                if ($this->input->post('password') != null) {
                    $dbdata['password'] = md5($this->input->post('password'));
                }
                if ($this->user_model->user_exist($username)) {
                    $res = $this->user_model->update_admin($username, $dbdata);
                    if ($res) {
                        $data['msg'] = $this->lang->line('update_success');
                    } else {
                        $data['msg'] = $this->lang->line('update_error');
                    }
                }
                $admin = $this->user_model->get_user_details($id);
                $data['id'] = $admin['id'];
                $data['username'] = $admin['username'];
                $data['name'] = $admin['name'];
                $data['email'] = $admin['email'];
                $data['type'] = $admin['type'];
                $data['page_name'] = 'edit_form';
                $data['title'] = "Edit admin";
                $this->load->view('admin/index', $data);
            }
        }
    }
	/**
	 * CONTROLLER
	 *
	 * Upload video
	 *
	 * @param type $user_id
	 */
    function upload($user_id) {

        $page['page_name'] = 'upload_video';
        $page['title'] = "Upload a new video";
        $page['user_id'] = $user_id;
        $page['msg'] = $this->lang->line('form_msg');
        $page['video_title'] = "";
        $page['video_description'] = "";
        $page['video_tags'] = "";
        $page['video_file'] = "";
        $page['page_name'] = 'upload_video';
        $page['title'] = "Upload a new video";
		$page["user_auth"] = $this->user_model->get_user_token($user_id) != "";
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * Show los of video.
	 */
    function logs() {
        $page['page_name'] = 'logs';
        $page['title'] = "Videos logs";
        $page['videos'] = $this->video_model->get_ytool_videos();
        $page['logs'] = $this->video_model->get_video_logs();
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * Show play logs
	 */
    function play_logs() {
        $page['page_name'] = 'play_logs';
        $page['title'] = "Playlist logs";
        $page['logs'] = $this->video_model->get_play_logs();
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * Show of report of video.
	 */
    function video_log() {
        $v_id = $this->input->post('v_id');

        $page['page_name'] = 'logs';
        $page['title'] = "Video log";

        $page['videos'] = $this->video_model->get_ytool_videos();
        $page['logs'] = $this->video_model->get_video_log($v_id);
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * @param string $video_id id of video youtube
	 */
    function video_rep($video_id) {
        $page['page_name'] = 'logs';
        $page['title'] = "Report For: Video ID {$video_id}";
        $page['videos'] = $this->video_model->get_ytool_videos();
        $page['logs'] = $this->video_model->get_video_rep($video_id);
		$page['video_model'] = $this->video_model;
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * This function get a report of channel selected.
	 *
	 * @param int $user_id
	 * @param string $channel
	 */
	public function channel_report($user_id = "") {
		if ($user_id != "") {
			$subscriptors = $this->video_model->get_subscriptors($user_id);
			$channel = $this->user_model->get_channel($user_id);

			$this->load->library('pagination');
			$opcions = array();
			$start = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;

			$opcions['per_page'] = $this->config->item("rp");
			$opcions['base_url'] = base_url() . "admin/channel_report/{$user_id}";

			$page["current_admin_name"] = $admin_name = $this->input->get('admin_name') ? $this->input->get('admin_name') : '';
			$page["current_video_id"] = $video_id = $this->input->get('video_id') ? $this->input->get('video_id') : '';
			$page["current_action_taken"] = $action_taken = $this->input->get('action_taken') ? $this->input->get('action_taken') : '';
			$page['page_name'] = 'channel_logs';
			$page['title'] = "Reports for: {$channel} | Current Number of Subscribers: {$subscriptors["subs"]}";

			$page["endDate"] = $this->input->post("end-date") ? $this->input->post("end-date") : date("Y-m-d");
			if ($this->input->post("start-date")) {
				$page["startDate"] = $this->input->post("start-date");
			} else {
				$page["startDate"] = strtotime ('-30 day', strtotime($page["endDate"]));
				$page["startDate"] = date ('Y-m-d', $page["startDate"]);
			}
			$page['logs'] = $this->video_model->get_report_log($user_id, $page["startDate"], $page["endDate"], $admin_name, $video_id, $action_taken);
			$page["admin_name"] = $this->video_model->get_array_for_select($page["logs"], "admin");
			$page["video_id"] = $this->video_model->get_array_for_select($page["logs"], "video_id");
			$page["action_taken"] = $this->video_model->get_array_for_select($page["logs"], "description");
			$page["channel"] = $channel;
			$page["user_id"] = $user_id;

			$this->load->view('admin/index', $page);
		} else {
			redirect("admin/user?success=true&msg=Select an user for view reports&type=info");
		}
	}
	/**
	 * CONTROLLER
	 *
	 * Setting of facebook key
	 *
	 * @param string $mode
	 */
    function setting($mode = NULL) {
        if ($mode == 'update') {

            $data['facebook_apikey'] = $this->input->post('facebook_apikey');
            $data['facebook_secret'] = $this->input->post('facebook_secret');
            $data['facebook_id'] = $this->input->post('facebook_id');
            $data['facebook_accesstoken'] = $this->input->post('facebook_accesstoken');

            $this->user_model->update_setting($data);
            redirect('admin/setting', 'refresh');
        }

        $page['page_name'] = 'setting';
        $page['title'] = "Settings";
        $page['account_info'] = $this->user_model->get_setting();
        $this->load->view('admin/index', $page);
    }
	/**
	 * Controller for get user with channel and videos upload
	 *
	 * @param int $start
	 */
//    function users() {
//		$this->load->library('pagination');
//
//		$opcions = array();
//		$start = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
//
//		$opcions['per_page'] = $this->config->item("rp");
//		$opcions['base_url'] = base_url() . "admin/users";
//
//        $page['users'] = $this->user_model->get_all_users($start);
//		$opcions['total_rows'] = $this->user_model->count_rows_users();
//		$opcions['uri_segment'] = 3;
//
//		$this->pagination->initialize($opcions);
//		$page['pagination'] = $this->pagination->create_links();
//        $page['page_name'] = 'users';
//        $page['name'] = "";
//		$page['youtube'] = "";
//		$page['country'] = "";
//		$page['category'] = "";
//		$page['gender'] = "";
//        $page['title'] = "Manage channel users";
//		$page['country_list'] = $this->user_model->get_countries_for_select();
//		$page['category_options'] = $this->user_model->get_categories_for_select();
//        $this->load->view('admin/index', $page);
//    }
	/**
	 *
	 * @param int $name
	 * @param string $youtube
	 * @param string $country
	 * @param string $category
	 * @param string $sex
	 */
	public function users($name = "all", $youtube = "all", $country = "all", $category = "all", $sex = "all") {
//		redirect($this->config->item("home"));
//		return;
		if ($this->input->get("success")) {
			$page["success"] = TRUE;
			$page["msg"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}
		$search_name = ($name != "" && $name != "all" ) ? urldecode($name) : "";
		$search_youtube = ($youtube != "" && $youtube != "all") ? urldecode($youtube) : "";
		$search_country = ($country != "" && $country != "all") ? urldecode($country) : "";
		$search_category = ($category != "" && $category != "all") ? urldecode($category) : "";
		$search_sex = ($sex != "" && $sex != "all") ? urldecode($sex) : "";

		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(8)) ? $this->uri->segment(8) : 0;
		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "admin/users/{$name}/{$youtube}/{$country}/{$category}/{$sex}";

        $page['users'] = $this->user_model->get_all_users($start, $search_name, $search_youtube, $search_country, $search_category, $search_sex);
		$opcions['total_rows'] = $this->user_model->count_rows_users($search_name, $search_youtube, $search_country, $search_category, $search_sex);
		$opcions['uri_segment'] = 8;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'users';
		$page['name'] = $search_name;
		$page['youtube'] = $search_youtube;
		$page['country'] = $search_country;
		$page['category'] = $search_category;
		$page['gender'] = $search_sex;
		$page['country_list'] = $this->user_model->get_countries_for_select();
		$page['category_options'] = $this->user_model->get_categories_for_select();
        $page['title'] = "User Management";
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * Show categories of user.
	 */
    function cat_users($category) {
		if ($category == "" || $category == "all") {
			redirect("admin/users");
			return;
		}
		$search = urldecode($category);
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "admin/cat_users/{$category}";

        $page['users'] = $this->user_model->get_all_users($start, "", "", "", $search);
		$opcions['total_rows'] = $this->user_model->count_rows_users("", "", "", $search);
		$opcions['uri_segment'] = 4;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'users';
		$page['name'] = '';
		$page['youtube'] = '';
		$page['country'] = '';
		$page['category'] = $search;
		$page['gender'] = '';
		$page['country_list'] = $this->user_model->get_countries_for_select();
		$page['category_options'] = $this->user_model->get_categories_for_select();
        $page['title'] = "User Management";
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * Get user by sex
	 */
    function users_by_sex($gender) {
        if ($gender == "") {
			redirect("admin/users");
			return;
		}
		$search = urldecode($gender);
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "admin/users_by_sex/{$gender}";

        $page['users'] = $this->user_model->get_all_users($start, "", "", "", "", $search);
		$opcions['total_rows'] = $this->user_model->count_rows_users("", "", "", "", $search);
		$opcions['uri_segment'] = 4;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'users';
		$page['name'] = '';
		$page['youtube'] = '';
		$page['country'] = '';
		$page['category'] = '';
		$page['gender'] = $search;
		$page['country_list'] = $this->user_model->get_countries_for_select();
		$page['category_options'] = $this->user_model->get_categories_for_select();
        $page['title'] = "User Management";
        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLER
	 *
	 * Get panel of adminitration
	 */
    function country($country) {
		if ($country == "") {
			redirect("admin/users");
			return;
		}
		$search = urldecode($country);
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "admin/country/{$country}";

        // $page['users'] = $this->user_model->get_users_by_country($country);
		$page['users'] = $this->user_model->get_all_users($start, "", "", $search);
		$opcions['total_rows'] = $this->user_model->count_rows_users("", "", $search);
		$opcions['uri_segment'] = 4;

		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'users';
		$page['name'] = '';
		$page['youtube'] = '';
        $page['country'] = $search;
        $page['category'] = '';
		$page['gender'] = '';
		$page['country_list'] = $this->user_model->get_countries_for_select();
		$page['category_options'] = $this->user_model->get_categories_for_select();
        $page['title'] = "User Management";
        $this->load->view('admin/index', $page);
    }
	/**
	 * By dashboard
	 *
	 * Select user by name,
	 */
    function by_name() {
        $name = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		if ($name == '') {
			redirect("admin/users");
			return;
		}

        $page['users'] = $this->user_model->get_users_by_name($name);
        $page['page_name'] = 'users';
		$page['name'] = '';
		$page['youtube'] = '';
		$page['country'] = '';
        $page['category'] = '';
		$page['gender'] = '';
		$page['country_list'] = $this->user_model->get_countries_for_select();
		$page['category_options'] = $this->user_model->get_categories_for_select();
        $page['title'] = "User Management";
        $this->load->view('admin/index', $page);
    }
	/**
	 * Controllers
	 *
	 * Show all users
	 */
    function admins() {
		if ($this->input->get("success")) {
			$page["success"] = $this->input->get("success");
			$page["msg"] = $this->input->get("msg");
			$page["success"] = ($page["success"] == "true") ? TRUE : $page["success"];
		}
        $page['users'] = $this->user_model->get_all_admins();
        $page['page_name'] = 'admins';
        $page['title'] = "Admins List";
        $this->load->view('admin/index', $page);
    }

	public function search($search = "") {
		if (!isset($_GET["search"])) return;
		$search = trim($_GET['search']);

		// la busco
		$result = $this->user_model->search($search);

		// seteo la cabecera como json
		header('Content-type: application/json; charset=utf-8');

		//imprimo el resultado como un json
		echo json_encode($result);
		die();
	}

	public function demographics() {
		$page["title"] = "Demographics";
		$page["page_name"] = "demographic";

		$this->load->view('admin/index', $page);
	}
}

/**/