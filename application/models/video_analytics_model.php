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
	
	public function query($user_id){

		$token = $this->user_model->get_user_meta($user_id, 'token', true);
		
		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);
		$youtube_analytics = new Google_YouTubeAnalyticsService($client);
		$rp = $this->config->item("rp");
		$current_tags = array();
		$categories = array();
		$current_category = "";
		$return_res;
		
		if (isset($token)) {
			$client->setAccessToken($token);
		}
		
		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();
		
			$data = array();
			$i = 0;
		
			try {
				$channelsResponse = $youtube->channels->listChannels(
						'id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
								'mine' => 'true',
						));
		
				foreach ($channelsResponse['items'] as $channel) {
					$channel_id = $channel['id'];
					
					$return_res = $youtube_analytics->reports->query("channel==".$channel_id,"2012-01-01","2013-07-11","views");
					
					/*
					$current_channel = $channel["snippet"]["title"];
					$playlistItemsResponse = $youtube->playlistItems->listPlaylistItems(
							'id, snippet,contentDetails',
							array(
									'playlistId' => $channel['contentDetails']['relatedPlaylists']['uploads'],
									'maxResults' => $rp
							)
					);
					foreach ($playlistItemsResponse['items'] as $key => $playlistItem) {
						$videos = $youtube->videos->listVideos(
								$playlistItem['contentDetails']['videoId'],
								'snippet,contentDetails,status,statistics'
						);
		
						foreach ($videos['items'] as $video) {
							if (isset($video['status']['uploadStatus']) &&
							$video['status']['uploadStatus'] == 'rejected' &&
							$video['status']['rejectionReason'] == 'copyright')
							{
								continue;
							}
							$this->put_data($data, $video, $user_id, $i, $current_tags, $current_category, $channel, $playlistItem);
							$categories[] = $current_category;
						}
					}
					*/
				}
		
			} catch (Google_ServiceException $e) {
				/*error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));*/
				return sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
			} catch (Google_Exception $e) {
				/*error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));*/
				return sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
			}
		}

		return $return_res;
	}
	public function get_google_client() {
		$client = new Google_Client();
		$client->setClientId($this->config->item("OAUTH2_CLIENT_ID"));
		$client->setClientSecret($this->config->item("OAUTH2_CLIENT_SECRET"));
		$redirect = filter_var('https://www.buzzmyvideos.com/beta2/signup-oauth',
				FILTER_SANITIZE_URL);
		$client->setRedirectUri($redirect);
		$client->addService('plus.login');
		$client->addService('plus.me');
	
		return $client;
	}
}