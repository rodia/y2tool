<?php

session_start();
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        Zend_Loader::loadClass('Zend_Gdata_YouTube');
        Zend_Loader::loadClass('Zend_Gdata_YouTube_CommentEntry');
        Zend_Loader::loadClass('Zend_Gdata_YouTube_VideoQuery');
        Zend_Loader::loadClass('Zend_Gdata_AuthSub');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_HttpClient');
        Zend_Loader::loadClass('Zend_Uri_Http');
        //Zend_Loader::loadClass('Zend_Oauth_Consumer');

        $this->load->model('user_model');
    }

    public function index() {
        //$this->authSub();
		//print_r($_REQUEST);
		$this->authSub2($_REQUEST["user_id"]);
    }

    function authSub() {
        $next = 'http://yttool.buzzmyvideos.com/user';
        $scope = 'http://gdata.youtube.com';
        $secure = false;
        $session = true;

        $tmpusers = $this->user_model->get_all_users();
        $tmpusers2 = array();
        foreach ($tmpusers as $row) {
            $next = 'http://yttool.buzzmyvideos.com/user/authsub/' . $row->id;
            $link = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
            $tmpusers2[] = array("link" => $link, "lastname" => $row->lastname . " " . $row->firstname . " id: " . $row->id );
        }
        $page['users'] = $tmpusers2;

        $this->load->view('welcome_message', $page);
    }
	/**
	 *
	 * @param string $user_id
	 */
	function authSub2($user_id) {
        // $next = 'http://yttool.buzzmyvideos.com/user';
        $scope = 'http://gdata.youtube.com';
        $secure = false;
        $session = true;
        $next = 'http://yttool.buzzmyvideos.com/user/authsub/' . $user_id;
		//echo $next;
        $link = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
        //echo '<h3 style="font-size:18px"><a href="'.$link.'">Confirm Authorize</a></h3>';
		redirect($link);
    }
	/**
	 *
	 * @param int $user_id
	 */
	function delete($user_id) {
		$this->user_model->delete_token($user_id);
		redirect("https://www.buzzmyvideos.com/youtube-authorization/?delete=1");
	}
}
