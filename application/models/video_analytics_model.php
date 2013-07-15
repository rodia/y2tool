<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Video_analytics_model extends CI_Model {

	public function __construct() {
		parent::__construct();

		require_once 'google-api-php-client/src/Google_Client.php';
		require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
		require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
		require_once 'google-api-php-client/src/contrib/Google_YouTubeAnalyticsService.php';
		//		require_once 'google-api-php-client/src/contrib/Google_PlusService.php';
	}
	
}