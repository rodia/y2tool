<?php

session_start();
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        Zend_Loader::loadClass('Zend_Gdata_YouTube');
        Zend_Loader::loadClass('Zend_Gdata_YouTube_CommentEntry');
        Zend_Loader::loadClass('Zend_Gdata_AuthSub');

        $this->load->model('user_model');
    }
	/**
	 * CONTROLLER
	 *
	 * Index
	 */
    public function index() {
//		redirect("admin/users");
		redirect("video/bulk");
    }

	/**
	 * CONTROLLER
	 * Set a token for auth session
	 *
	 * @param int $user_id user id of wordpress site.
	 */
    function authsub($user_id) {
        $token =  Zend_Gdata_AuthSub::getAuthSubSessionToken($_REQUEST["token"]);
        $tokens = array(
			"token" => $_REQUEST["token"],
			"sess_token" => $token
		);

		$user = $this->user_model->get_auth_user($user_id);

		if (count($user) <= 0) {
			redirect("https://www.buzzmyvideos.com/youtube-authorization/?error=1&msg=User+register+dont+exist");
			die();
		}

		if (!$this->user_model->is_user_admin($user[0]->user_login)) {
			$dbdata = array(
				'username' => $user[0]->user_login,
				'password' => md5($token),
				'name' => $user[0]->display_name,
				'email' => $user[0]->user_email,
				'registered_date' => time(),
				'type' => "Administrator"
			);

			$this->user_model->register_admin($dbdata);
		} else {
			$dbdata = array(
				'password' => md5($token),
				'name' => $user[0]->display_name,
				'email' => $user[0]->user_email
			);
			$this->user_model->update_admin($user[0]->user_login, $dbdata);
		}

		if ($this->user_model->update_token($token, $user_id) && $this->user_model->admin_login($user[0]->user_login, md5($token))) {
			redirect("https://www.buzzmyvideos.com/youtube-authorization/?token={$token}");
		} else {
			redirect("https://www.buzzmyvideos.com/youtube-authorization/?error=1");
		}
    }

	public function youtube() {
//		$login = isset($_GET["a"]) ? $_GET["a"] : NULL;
//		$token = isset($_GET["t"]) ? $_GET["t"] : NULL;
//		$this->user_model->admin_login($login, md5($token));
//		redirect("admin/users");
		redirect("video/bulk");
	}

}
