<?php
/**
 *
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Video_model extends CI_Model {

	private $count_videos;
	private $categories;
	private $current_channel;

    public function __construct() {
        parent::__construct();

		require_once 'google-api-php-client/src/Google_Client.php';
		require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
		require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
		$config['upload_path'] = 'uploads';
		$config['allowed_types'] = 'flv|mp4|m3u8|ts|3gp|mov|avi|wmv';
		$config['max_size'] = '67108864';

		$this->load->model('user_model');
		$this->load->library('upload');
    }
	/**
	 *
	 * @return int Count all videos
	 */
	public function get_count_videos() {
		return $this->count_videos;
	}
	/**
	 * @return array current categories of videos;
	 */
	public function get_current_categories() {
		return $this->categories;
	}
	/**
	 *
	 * @return string Return current channel of retrieve for get_videos_by_user()
	 */
	public function get_current_channel() {
		return $this->current_channel;
	}
	/**
	 *
	 * @param type $user_id
	 */
	function sync_videos($user_id) {
        $profile = $this->user_model->getUserProfile($user_id);
        $videos = $this->getUserUploads($profile["username"]);
//        echo "user: " . $profile["title"] . "- " . $profile["username"] . "<br>";
        foreach ($videos as $videoEntry) {
            $video = $this->exists_video($videoEntry->getVideoId());

            if (!$video) {
                $data = array(
                    "youtube_id" => $videoEntry->getVideoId(),
                    "channel" => $profile["username"],
                    "title" => $videoEntry->getVideoTitle()
                );
                $v_id = $this->insert_video($data);
            }
        }
    }

    /**
     *
     * @param string $user_id
     */
    function upload_video($user_id) {
    	$token = $this->user_model->get_user_meta($user_id, 'token', true);

    	$client = $this->get_google_client();
    	$youtube = new Google_YoutubeService($client);
    	$rp = $this->config->item("rp");
    	$current_tags = array();
    	$categories = array();
    	$current_category = "";

    	if (isset($token)) {
    		$client->setAccessToken($token);
    	}

    	if ($client->getAccessToken()) {
    			$_SESSION['token'] = $client->getAccessToken();

			$data = array();
			$i = 0;

			try {


				$video_objt = new Google_Video();
				$video_snippet = new Google_VideoSnippet();
				$video_status = new Google_VideoStatus();
				$video_status->setPrivacyStatus("public");
				$video_objt->setStatus($video_status);

				$video_snippet->setTitle($this->input->post("video_title"));
				$video_snippet->setDescription($this->input->post("video_description"));
				$video_snippet->setCategoryId($this->input->post("video_category"));


				$video_snippet->setTags(split(",",$this->input->post("video_tags")));

				$video_path = "http://y2tool.buzzmyvideos.com/uploads/ES_262_05_00_00.mp4";

/*				$chunkSizeBytes = 1 * 1024 * 1024;
				$media = new Google_MediaFileUpload('video/mp4', null);
				 //http://y2tool.buzzmyvideos.com/uploads/ES_262_05_00_00.mp4";
				$media->setFileSize(filesize($video_path));    */

				$video_objt->setSnippet($video_snippet);
				$result = $youtube->videos->insert("snippet,status",$video_objt,array('data' => file_get_contents($video_path),"mimeType"=>"video/mp4"));

				/*$status = false;
				$handle = fopen($video_path, "rb");
				while (!$status && !feof($handle)) {
					$chunk = fread($handle, $chunkSizeBytes);
					$uploadStatus = $media->nextChunk($result, $chunk);
				}

				fclose($handle);*/


			} catch (Google_ServiceException $e) {
				$log = sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
				error_log($log);
				return $log;
			} catch (Google_Exception $e) {
				$log = sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
				error_log($log);
				return $log;
			}
			return $result;
    	}
    }
	/**
	 * Enable a object of upload video
	 *
	 * @param string $userName User name of youtube account.
	 * @return Google_ChannelListResponse
	 */
	function getUserUploads($userName) {
        $youtube = new Zend_Gdata_YouTube();
        $youtube->setMajorProtocolVersion(2);
        return $youtube->getUserUploads($userName);
    }
	/**
	 * Get all video of youtube channel for each user auth.
	 *
	 * Catch Exception Zend_Gdata_App_HttpException if user closed your account.
	 *
	 * @param array|string $users
	 * @param string $category
	 * @param int $start
	 * @return array Get all videos
	 */
	function all_videos($users = NULL, $category = NULL, $start = 0) {
		$rp = $this->config->item("rp");

		if ($users != NUll) {
			$users_channels = $this->user_model->get_users_channel($users);
		} else {
			$users_channels = $this->user_model->get_all_users_channel();
		}
		$videos = array();
		$categories = array();
        $i = 0;
        foreach ($users_channels as $channel) {
			try {
				$all_videos = $this->getUserUploads($this->getChannel($channel->youtube_channels));
			} catch (Zend_Gdata_App_Exception $e) {
				error_log($e->getMessage());
				continue;
			} catch (Zend_Gdata_App_HttpException $e) { // Zend_Gdata_App_HttpException
				error_log($e->getMessage());
				continue;
			}
			foreach ($all_videos as $videoEntry) {
				$current_category = $videoEntry->getVideoCategory();
				if ($category != NULL && $category != $current_category) continue;
                $videos[$i]["video_id"] = $videoEntry->getVideoId();
                $videos[$i]["title"] = $videoEntry->getVideoTitle();
                $videos[$i]["category"] = $current_category;
                $videos[$i]["description"] = $videoEntry->getVideoDescription();
                $videos[$i]["view_count"] = $videoEntry->getVideoViewCount();
                $videos[$i]["channel"] = $channel->youtube_channels;
                $videos[$i]["user_id"] = $channel->id;
				$categories[] = $current_category;
                $videoThumbnails = $videoEntry->getVideoThumbnails();
                $videoThumbnail = $videoThumbnails[$this->get_video_thumbnail_key($videos[$i]["video_id"])];
				$videos[$i]["thumbnail"] = $videoThumbnail["url"];
                $i++;
			}
        }
		$this->categories = array_unique($categories);
		$this->count_videos = count($videos);
        return array_slice($videos, $start, $rp);
    }
	/**
	 *
	 * @param Google_Client $client
	 * @return Google_YoutubeService
	 */
	public function get_google_youtubeService($client = NULL) {
		if ($client == NULL) $client = new Google_Client();
		$client->setDeveloperKey($this->config->item("API_KEY"));

		return new Google_YoutubeService($client);
	}
	/**
	 * Get youtube client for app.
	 * @return Google_Client
	 */
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
	/**
	 *
	 * @param string $q
	 * @param int $maxResults
	 * @return array
	 */
	public function search_videos($q, $maxResults = 20) {
		$youtube = $this->get_google_youtubeService();

		$videos = array();
		$channels = array();
		$playlists = array();
		$i = 0;
		$j = 0;
		$k = 0;

		try {
			$searchResponse = $youtube->search->listSearch('id,snippet', array(
				'q' => $q,
				'maxResults' => $maxResults
			));

			foreach ($searchResponse['items'] as $searchResult) {
				switch ($searchResult['id']['kind']) {
				case 'youtube#video':
					$videos[$i]["title"] = $searchResult['snippet']['title'];
					$videos[$i++]["video_id"] = $searchResult['id']['videoId'];
					break;
				case 'youtube#channel':
					$channels[$j]["title"] = $searchResult['snippet']['title'];
					$channels[$j++]["channel_id"] = $searchResult['id']['channelId'];
					break;
				case 'youtube#playlist':
					$playlists[$k]["title"] = $searchResult['snippet']['title'];
					$playlists[$k++]["playlist_id"] = $searchResult['id']['playlistId'];
					break;
				}
			}

		} catch (Google_ServiceException $e) {
			error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
			htmlspecialchars($e->getMessage())));
		} catch (Google_Exception $e) {
			error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
			htmlspecialchars($e->getMessage())));
		}

		return array (
			"videos" => $videos,
			"channels" => $channels,
			"playlists" => $playlists
		);
	}
	/**
	 * @todo Esta función tiene un problema, que cuando se hace el llamado este solo retorna una parte de los videos obtenidos.
	 *
	 * @param type $channel_id ID CHANNEL for youtube
	 * @return type
	 */
	public function get_videos_by_channel($channel_id) {
		$youtube = $this->get_google_youtubeService();
		$maxResults = $this->config->item("rp");
		$data = array();

		try {
			$channelResponse = $youtube->channels->listChannels('id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
				'id' => $channel_id,
				'maxResults' => $maxResults
			));

			foreach ($channelResponse['items'] as $channel) {
				$playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('id, snippet,  contentDetails', array(
					'playlistId' => $channel['contentDetails']['relatedPlaylists']['uploads'],
					'maxResults' => $maxResults
				));

				foreach ($playlistItemsResponse['items'] as $playlistItem) {
					$videos = $youtube->videos->listVideos(
						$playlistItem['contentDetails']['videoId'],
						'snippet,contentDetails,status'
					);

					foreach ($videos['items'] as $video) {
						if (isset($video['status']['uploadStatus']) &&
							$video['status']['uploadStatus'] == 'rejected' &&
							$video['status']['rejectionReason'] == 'copyright')
						{
							continue;
						}
						$data[] = $video;
					}
				}
			}

		} catch (Google_ServiceException $e) {
			error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
			htmlspecialchars($e->getMessage())));
		} catch (Google_Exception $e) {
			error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
			htmlspecialchars($e->getMessage())));
		}
		return array (
			"videos" => $videos
		);
	}
	/**
	 * OAuth
	 * Get user videos of account in youtube by user regitered your oauth token.
	 *
	 * @param int|array $user_id This parameter can be a list of id user or a single id user
	 * @param int $categoryId Category of Youtube
	 * @param int $start get offset for get videos.
	 * @return array
	 */
	public function get_videos_by_user($user_id, $categoryId = NULL, $start = 0) {
		if ( ! is_array($user_id)) {
			$users = array($user_id);
		} else {
			$users = $user_id;
		}
		$data = array();
		$i = 0;
		$rp = $this->config->item("rp");
		$current_tags = array();
		$categories = array();
		$current_category = "";
		foreach ($users as $user_id) {
			$token = $this->user_model->get_user_meta($user_id, 'token', true);

			$client = $this->get_google_client();
			$youtube = new Google_YoutubeService($client);

			if (isset($token)) {
				$client->setAccessToken($token);
			}

			if ($client->getAccessToken()) {
				$_SESSION['token'] = $client->getAccessToken();

				try {
					$channelsResponse = $youtube->channels->listChannels(
						'id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
						'mine' => 'true',
					));

					foreach ($channelsResponse['items'] as $channel) {
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
								'snippet,contentDetails,status,statistics,player'
							);

							foreach ($videos['items'] as $video) {
								if (isset($video['status']['uploadStatus']) &&
									$video['status']['uploadStatus'] == 'rejected' &&
									$video['status']['rejectionReason'] == 'copyright')
								{
									continue;
								}
								if (NULL != $categoryId && $categoryId != $video["snippet"]["categoryId"]) continue; /* Continue only if categoryId correspont */
								$this->put_data($data, $video, $user_id, $i, $current_tags, $current_category, $channel, $playlistItem);
								$categories[] = $current_category;
							}
						}
					}

				} catch (Google_ServiceException $e) {
					error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
					htmlspecialchars($e->getMessage())));
				} catch (Google_Exception $e) {
					error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
					htmlspecialchars($e->getMessage())));
				}
			}
		}

		$this->categories = array_unique($categories);
		$this->count_videos = count($data);
		$this->current_channel = $current_channel;
		return array_slice($data, $start, $rp);
	}
	/**
	 * Oauth
	 *
	 * @param string $video_id Youtube Id
	 * @param type $user_id User Id for wordpress installation
	 */
	public function get_video($video_id, $user_id) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);
		$i = 0;
		$data = array();
		$current_tags = array();
		$current_category = "";

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			$videos = $youtube->videos->listVideos(
				$video_id,
				'snippet,contentDetails,status,statistics,player'
			);

			foreach ($videos['items'] as $video) {
				if (isset($video['status']['uploadStatus']) &&
					$video['status']['uploadStatus'] == 'rejected' &&
					$video['status']['rejectionReason'] == 'copyright')
				{
					continue;
				}

				$this->put_data($data, $video, $user_id, $i, $current_tags, $current_category);
			}
		}

		return $data[0];
	}
	/**
	 *
	 * @param string $video_id Youtube ID
	 * @param int $user_id ID for wordpress system
	 * @param boolean True if edit video is success, False otherwise.
	 */
	public function edit_video($video_id, $user_id, $data) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$channelsResponse = $youtube->channels->listChannels(
					'id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
					'mine' => 'true',
				));

				foreach ($channelsResponse['items'] as $channel) {
					$content = new Google_Video();
					$content->setId($video_id);
					$snippet = new Google_VideoSnippet();
					$snippet->setChannelId($channel["id"]);
					$snippet->setTitle($data["video_title"]);
					$snippet->setDescription($data["video_description"]);
					$snippet->setCategoryId($data["category_id"]);
					$snippet->setTags($data["video_tags"]);
					$thumbnails = new Google_ThumbnailDetails();
					$default = new Google_Thumbnail();
					$default->setUrl($data["url"]);
					$thumbnails->setDefault($default);
					$snippet->setThumbnails($thumbnails);
					$content->setSnippet($snippet);

					$youtube->videos->update(
						'snippet,status',
						$content
					);
				}
				return TRUE;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
	}
	/**
	 *
	 * @param string $video_id video ID Youtube
	 * @param int $user_id User ID for wordpress system
	 * @param string $file_image (optional) The field name of file upload in the form
	 * @return string The url of file img saved.
	 */
	public function set_thumbnails($video_id, $user_id, $file_image = "new-thumbnails") {
		$video = $this->get_video($video_id, $user_id);
		$file = $this->upload->do_upload($file_image);
		$errors = $this->upload->display_errors();
		if ("" != $errors) {
			$this->load->helper('cookie_helper');
			set_cookie("show_error", $errors, 300);
		}
		if (FALSE == $file) {
			return $video["thumbnail"]["url"];
		} else {
			$this->load->helper('url');
			$image = $this->upload->data();
			$config['image_library'] = 'gd2';
			$config['source_image']	= $image["full_path"];
			$config['create_thumb'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['width']  = 75;
			$config['height'] = 50;
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			return base_url() . substr($this->config->item("upload_path"), 2) . $image["file_name"];
		}
	}
	/**
	 *
	 * @param string $video_id Id youtube
	 * @param int $user_id Id user for wordpress system.
	 * @param array $data The data of insert
	 */
	public function set_history($video_id, $user_id, $data) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			$channelsResponse = $youtube->channels->listChannels(
				'id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
				'mine' => 'true',
			));

			foreach ($channelsResponse["items"] as $channel) {
				$videos = $youtube->videos->listVideos(
					$video_id,
					'snippet,contentDetails,status,statistics'
				);

				foreach ($videos['items'] as $video) {
					if (isset($video['status']['uploadStatus']) &&
						$video['status']['uploadStatus'] == 'rejected' &&
						$video['status']['rejectionReason'] == 'copyright')
					{
						continue;
					}

					if ( ! $this->video_model->exists_video($video_id)) {
						$v_id = $this->video_model->insert_video(array(
							"youtube_id" => $video_id,
							"channel" => $data["channel"],
							"title" => $video["snippet"]["title"]
						));
					} else {
						$row = $this->get_db_id_video($video_id);
						$v_id = $row[0]->id;
					}

					$dbdata = array(
						"registered_date" => date("Y-m-d H:i:s"),
						"admin_id" => $this->session->userdata('user_id'),
						"user_id" => $user_id,
						"video_id" => $v_id,
						"video_likes" => $video["statistics"]["likeCount"],
						"video_views" => $video["statistics"]["viewCount"],
						"channel_subs" => $channel["statistics"]["subscriberCount"],
						"task_id" => $data["task_id"],
						"who" => $this->session->userdata('name')
					);
					$this->video_model->insert_history($dbdata);

					$this->video_model->db_update_video(array(
						"title" => $video["snippet"]["title"],
						"channel" => $data["channel"],
						"video_thumbnail_key" => 0
					), $video_id);
				}
			}
		}
	}
	/**
	 *
	 * @param int $user_id User ID by system wordpress
	 * @param array $playlist Data of registered into logs by playlist.
	 * @param int $task
	 */
	public function set_history_playlist($user_id, $playlist, $task = 8) {

		if ( ! ($playlist_db = $this->exists_playlist($playlist["id"]))) {
			$play_id = $this->video_model->insert_playlist(array(
				"channel" => $playlist["channel"],
				"title" => $playlist["play_title"],
				"playlist" => $playlist["snippet"]["channelId"]
			));
		} else {
			$play_id = $playlist_db->id;
		}

		$this->video_model->insert_history(array(
			"registered_date" => date("Y-m-d H:i:s"),
			"admin_id" => $this->session->userdata('user_id'),
			"user_id" => $user_id,
			"video_id" => "",
			"task_id" => $task,
			"playlist_id" => $play_id, // Id of database row.
			"who" => $this->session->userdata('name')
		));
	}
	/**
	 * OAuth
	 *
	 * Get videos by playlist ID
	 *
	 * @param int $user_id
	 * @param string $playlistId
	 * @param int $start
	 * @return array
	 */
	public function get_videos_by_playlist($user_id, $playlistId, $start = 0) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);
		$rp = $this->config->item("rp");
		$current_tags = array();
		$categories = array();
		$current_category = "";

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			$data = array();
			$i = 0;

			try {
				/**
				 * @todo The function not sure that return all videos of play list.
				 * @todo In addition the retrieve data must be for pagination method.
				 */
				$playlistItemsResponse = $youtube->playlistItems->listPlaylistItems(
					'id,snippet,contentDetails,player',
					array(
						'playlistId' => $playlistId,
						'maxResults' => $rp
					)
				);
				$this->count_videos = $playlistItemsResponse["pageInfo"]["totalResults"];
				foreach ($playlistItemsResponse['items'] as $key => $playlistItem) {
					$videos = $youtube->videos->listVideos(
						$playlistItem['contentDetails']['videoId'],
						'snippet,contentDetails,status,statistics,player'
					);

					foreach ($videos['items'] as $video) {
						if (isset($video['status']['uploadStatus']) &&
							$video['status']['uploadStatus'] == 'rejected' &&
							$video['status']['rejectionReason'] == 'copyright')
						{
							continue;
						}
						$this->put_data($data, $video, $user_id, $i, $current_tags, $current_category);
						$categories[] = $current_category;
					}
				}

			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			}
		}

		$this->categories = array_unique($categories);

		return array_slice($data, $start, $rp);
	}
	/**
	 * OAuth
	 *
	 * @param int $user_id
	 * @param string $playlistId
	 * @return array
	 */
	public function get_playlistDetail($user_id, $playlistId) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);
		$rp = $this->config->item("rp");
		$data = array();

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$playlistsResponse = $youtube->playlists->listPlaylists(
					'id,snippet,contentDetails',
					array(
						'id' => $playlistId,
						'maxResults' => $rp
					)
				);

				foreach ($playlistsResponse['items'] as $key => $playlistItem) {
					$data["playlistId"] = $playlistId;
					$data["title"] = $playlistItem["snippet"]["title"];
					$data["description"] = $playlistItem["snippet"]["description"];
					$data["channelId"] = $playlistItem["snippet"]["channelId"];
					$data["thumbnails"]["url"] = $playlistItem["snippet"]["thumbnails"]["default"]["url"];
				}
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			}
		}
		return $data;
	}
	/**
	 *
	 * @param type $video_id
	 * @return type
	 */
	public function get_db_id_video($video_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select('yt_video.id');
		$this->db_my_db->join('yt_history', 'yt_video.id = yt_history.video_id', 'LEFT');
		$this->db_my_db->where('youtube_id', $video_id);
		$query = $this->db_my_db->get('yt_video');
		return $query->result();
	}

	/**
	 * Put data into variable $data.
	 *
	 * @param array $data
	 * @param array $video
	 * @param int $user_id
	 * @param int $i
	 * @param array $current_tags
	 * @param array $current_category
	 * @param array $channel
	 * @param array $playlist
	 */
	public function put_data(& $data, $video, $user_id, & $i, & $current_tags, & $current_category, $channel = NULL, $playlist = NULL) {
		/**
		 * @todo This not is the category is an tags. search for solutions.
		 */
		$current_tags = isset($video["snippet"]["tags"]) ? $video["snippet"]["tags"] : array();
		$current_category = $video["snippet"]["categoryId"];
		$data[$i]["video_id"] = $video["id"];
		$data[$i]["title"] = $video["snippet"]["title"];
		$data[$i]["category"] = $current_category = $this->get_youtube_category($current_category);
		$data[$i]["categoryId"] = $video["snippet"]["categoryId"];
		$data[$i]["description"] = $video["snippet"]["description"];
		$data[$i]["tags"] = $current_tags;
		$data[$i]["view_count"] = $video["statistics"]["viewCount"];
		$data[$i]["like_count"] = $video["statistics"]["likeCount"];
		$data[$i]["dislike_count"] = $video["statistics"]["dislikeCount"];
		$data[$i]["favorite_count"] = $video["statistics"]["favoriteCount"];
		$data[$i]["comment_count"] = $video["statistics"]["commentCount"];
		$data[$i]["channel"] = "";
		$data[$i]["channel_id"] = $channel != NULL ? $channel["id"] : "";
		$data[$i]["user_id"] = $user_id;
		$data[$i]["embedHtml"] = $video["player"]["embedHtml"];

		$data[$i++]["thumbnail"]["url"] = $video["snippet"]["thumbnails"]["default"]["url"];
	}

	/**
	 *
	 * @param int $user_id
	 * @param int $video_id
	 * @return int
	 */
	public function get_current_like($user_id, $video_id) {

		$yt = $this->user_model->getHttpClient($user_id);

		try {
            $videoEntry = $yt->getVideoEntry($video_id);
			$rating = $videoEntry->getVideoRatingInfo();
            $likes = $rating['numRaters'];
		} catch (Zend_Gdata_App_HttpException $httpException) {
            error_log($httpException->getRawResponseBody());
        }

		return $likes;
	}
	/**
	 * @deprecated since version 1.0
	 * @param string $videoId Id of youtube video
	 * @return Zend_Gdata_YouTube_VideoEntry Entry of video youtube
	 */
	public function get_video_entry($videoId) {
		$yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);
        try {
            $entry = $yt->getVideoEntry($videoId);
        } catch (Zend_Gdata_App_HttpException $httpException) {
            error_log('ERROR ' . $httpException->getMessage()
            . ' HTTP details<br /><textarea cols="100" rows="20">'
            . $httpException->getRawResponseBody()
            . '</textarea><br />');
        }

		return $entry;
	}
	/**
	 * @deprecated since version 1.0
	 *
	 * @param string $channel
	 * @return Zend_Gdata_YouTube_PlaylistListFeed
	 */
	public function get_PlayList_entry($channel) {
		$yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);
		try {
			$playlistListFeed = $yt->getPlaylistListFeed($channel);
		} catch (Zend_Gdata_App_HttpException $httpException) {
			error_log('ERROR ' . $httpException->getMessage()
            . ' HTTP details<br /><textarea cols="100" rows="20">'
            . $httpException->getRawResponseBody()
            . '</textarea><br />');
		}
		return $playlistListFeed;
	}
	/**
	 *
	 * @param array $logs
	 * @return array
	 */
	public function get_array_for_select($logs, $field) {
		$for_select = array();
		foreach ($logs as $row) {
			$for_select[] = $row->$field;
		}

		return array_unique($for_select);
	}
	/**
	 * @deprecated since version 1.0
	 * @param string $user
	 * @return Zend_Gdata_YouTube_UserProfileEntry
	 */
	public function get_user_entry($user) {
		$yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);
		try {
			$userProfile = $yt->getUserProfile($user);
		} catch (Zend_Gdata_App_HttpException $httpException) {
			error_log('ERROR ' . $httpException->getMessage()
            . ' HTTP details<br /><textarea cols="100" rows="20">'
            . $httpException->getRawResponseBody()
            . '</textarea><br />');
		}
		return $userProfile;
	}
	/**
	 * @deprecated since version 1.0
	 * @param array|string $category
	 * @return Zend_Gdata_YouTube_VideoFeed
	 */
	public function get_category($category) {
		$yt = new Zend_Gdata_YouTube();
		$query = $yt->newVideoQuery();

		if (is_array($category)) {
			$category = implode("/", $category);
		}

		$query->category = $category;

//		echo $query->queryUrl . "\n";
		return $yt->getVideoFeed($query);
	}
	/**
	 *
	 * @param int $categoryId Categories ID
	 * @return string Categories names of list of $categoryId
	 */
	public function get_youtube_category($categoryId) {
//		$youtube = $this->get_google_youtubeService();
//		$categoryResponse = $youtube->videoCategories->listVideoCategories('id,snippet',array('id'=>$categoryId));
//		foreach($categoryResponse['items'] as $cat)
//		{
//			$category = $cat['snippet']['title'];
//		}
//		return $category;

		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select(array('category', 'display_category', 'categoryId'));
		$this->db_my_db->where('categoryId', $categoryId);
		$query = $this->db_my_db->get('yt_category');

		$row = $query->first_row();
		return $row->display_category;
	}

	/**
	 *
	 * @param string $userName user name of youtube account
	 * @return Zend_Gdata_YouTube_SubscriptionFeed
	 */
	function getSubscriptionFeed($userName)
	{
		$yt = new Zend_Gdata_YouTube();
		// obtain a user's subscription feed. Alternatively you can use the string 'default'
		// to retrieve subscriptions for the currently authenticated user
		return $subscriptionFeed = $yt->getSubscriptionFeed($userName);
	}
	/**
	 *
	 * @param mixer $subscriptionFeed
	 */
	function printSubscriptionFeed($subscriptionFeed)
	{
		$count = 1;
		if (!isset($displayTitle) || $displayTitle === null) {
			$displayTitle = $subscriptionFeed->title->text;
		}
		echo '<h2>' . $displayTitle . "</h2>\n";
		echo "<pre>\n";
		foreach ($subscriptionFeed as $subscriptionEntry) {
			echo 'Entry # ' . $count . "\n";
			$this->printSubscriptionEntry($subscriptionEntry);
			echo "\n";
			$count++;
		}
		echo "</pre>\n";
	}
	/**
	 *
	 * @param type $subscriptionEntry
	 */
	function printSubscriptionEntry($subscriptionEntry)
	{
		$type = 'unknown';
		$subscriptionFeedLinkMap = array(
			'query' => 'http://gdata.youtube.com/schemas/2007#video.query',
			'favorites' => 'http://gdata.youtube.com/schemas/2007#user.favorites',
			'channel' => 'http://gdata.youtube.com/schemas/2007#user.uploads'
		);
		echo 'Subscription: ' . $subscriptionEntry->title->text . "\n";

		// look at the category property to to determine the type of subscription,
		// which is currently one of: query, favorites, channel
		foreach ($subscriptionEntry->category as $category) {
		  if ($category->scheme == 'http://gdata.youtube.com/schemas/2007/subscriptiontypes.cat') {
		  $type = $category->term;
		}
			  }
		echo "\tType: " . $type . "\n";
		echo "\tURL: ";

		// use the map defined above to fine the appropriate feedLink, depending on
		// what type of subscription entry was passed
		$feedLink = $subscriptionEntry->getFeedLink($subscriptionFeedLinkMap[$type]);
		if ($feedLink !== null) {
		  echo $feedLink->href;
		}
		echo "\n";
	}
	/**
	 * OAuth
	 *
	 * @param int $user_id
	 * @return array
	 */
	public function get_subscriptors($user_id) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$channelsResponse = $youtube->channels->listChannels(
					'id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
					'mine' => 'true',
				));

				$profile = array();
				foreach ($channelsResponse["items"] as $channel) {
					$profile["title"] = $channel["snippet"]["title"];
					$profile["username"] = $this->user_model->get_channel($user_id);
					$profile["subs"] = $channel["statistics"]["subscriberCount"];
				}

				return $profile;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			}
		}
		return array("title" => "", "username" => "", "subs" => 0);
	}

	/**
	 * This function retrieve the user name or youtube name of url youtube.
	 *
	 * @param string $url
	 * @return string
	 */
	function getChannel($url) {
        $url_arr = explode("/", $url);
        if (sizeof($url_arr) > 0) {
			$channel = $url_arr[sizeof($url_arr) - 1];
			if (strpos($channel, '?')) {
				$temp = explode('?', $channel);
				$channel = $temp[0];
			}
            return $channel;
		} else {
            return $url;
		}
    }
	/**
	 *
	 * @return mixed either a result object or array
	 */
	public function get_all_countries() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select('country');
		$this->db_my_db->order_by("country", "ASC");
		$query = $this->db_my_db->get('yt_country');
		return $query->result();
	}
	/**
	 *
	 * @return mixed Get all categories of table category
	 */
	public function get_all_categories() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select(array('category', 'display_category', 'categoryId'));
		$this->db_my_db->order_by("id", "ASC");
		$query = $this->db_my_db->get('yt_category');

		return $query->result();
	}
	/**
	 *
	 * @param mixed $objects
	 * @param string $key key of objects for retrieve the value for array
	 * @param string $value Opcional if you define this field the pait is key => value
	 * @return array Array of key => key or key => value
	 */
	public function get_pair_values($objects, $key, $value = '') {
		$values = array();
		$value = ($value != '') ? $value : $key;

		foreach ($objects as $item) {
			$values["{$item->$key}"] = $item->$value;
		}
		return $values;
	}
	/**
	 *
	 * @param array $data Values cols vs value into key and value arrays
	 */
    function insert_history($data) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->insert('yt_history', $data);
    }
	/**
	 *
	 * @param string|int $id Is video id or video youtube id
	 * @return int
	 */
	public function get_video_thumbnail_key($id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select('video_thumbnail_key');
		if (is_numeric($id)) {
			$this->db_my_db->where('id', $id);
		} else {
			$this->db_my_db->where('youtube_id', $id);
		}

        $query = $this->db_my_db->get('yt_video');
        $rows = $query->result();
		return count($rows) > 0 ? $rows[0]->video_thumbnail_key : 0;
	}
	/**
	 *
	 * @param string $youtube_id
	 * @param int $key
	 */
	public function set_video_thumbnail_key($youtube_id, $key) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->update('yt_video', array("video_thumbnail_key" => $key), "youtube_id = '{$youtube_id}'");
	}
	/**
	 *
	 * @param array $values Values into cols and value pairs
	 * @param type $video_id youtuve id
	 */
	public function db_update_video($values, $video_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->update('yt_video', $values, "youtube_id = '{$video_id}'");
	}
    /**
     * INSERT VIDEOS
	 *
	 * @param array $data Values type cols and value
	 * @return int Id of query
     */
    function insert_video($data) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->insert('yt_video', $data);
        return $this->db_my_db->insert_id();
    }
	/**
	 *
	 * @param type $data
	 * @return type
	 */
    function insert_playlist($data) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->insert('yt_playlist', $data);
        return $this->db_my_db->insert_id();
    }
	/**
	 * OAuth
	 *
	 * This function create a new playlist into channel of user.
	 *
	 * @param int $user_id
	 * @param string $channel
	 * @param array $data
	 * @return boolean True if create is success, false otherwise.
	 */
	public function oauth_insert_playlist($user_id, $channel, $data) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$postBody = new Google_Playlist();
				$snippet = new Google_PlaylistSnippet();
				$snippet->setTitle($data["play_title"]);
				$snippet->setDescription($data["play_description"]);
				$postBody->setSnippet($snippet);

				$playlist = $youtube->playlists->insert(
					"id,snippet,status",
					$postBody
				);

				$this->set_history_playlist($user_id, array(
					"playlistId" => $playlist["id"],
					"channel" => $channel,
					"play_title" => $playlist["snippet"]["title"],
					"snippet" => array(
						"channelId" => $playlist["snippet"]["channelId"]
					)
				));
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
		return TRUE;
	}
	/**
	 *
	 * @param int $user_id User ID by wordpress system
	 * @param string $playlistId Playlist ID for Youtube
	 * @return boolean
	 */
	public function oauth_delete_playlist($user_id, $playlistId) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$playlist = $youtube->playlists->delete(
					$playlistId
				);

				return TRUE;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
		return FALSE;
	}
	/**
	 *
	 * @param int $user_id
	 * @param string $playlistId
	 * @param array $data
	 */
	public function oauth_insert_video_playlist($user_id, $playlistId, $data) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$video_id = $this->get_id_by_url($data["videoId"]);
				$postBody = new Google_PlaylistItem();

				$resource = new Google_ResourceId();
				$resource->setVideoId($video_id);
				$resource->setKind("youtube#video");

				$snippet = new Google_PlaylistItemSnippet();
				$snippet->setResourceId($resource);
				$snippet->setPlaylistId($playlistId);
				$postBody->setSnippet($snippet);

				$video = $youtube->playlistItems->insert(
					'snippet',
					$postBody
				);
				return TRUE;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
		return FALSE;
	}
	/**
	 *
	 * @param int $user_id
	 * @param string $videoFeedID
	 * @param string $videoId
	 */
	public function oauth_delete_video_playlist($user_id, $videoFeedID, $data) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$playlist = $youtube->playlistItems->delete(
					$data["video_id"]
				);

				return TRUE;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
		return FALSE;
	}
	/**
	 *
	 * @param string $url it's a url from youtube
	 * @return string Video Id parsed into url.
	 */
	public function get_id_by_url($url) {
		$my_array_of_vars = array();
		parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
		return isset($my_array_of_vars['v']) ? $my_array_of_vars['v'] : $url;
	}
	/**
     * CHECK VIDEOS
	 *
	 * @param string $video_id youtube user name
	 * @return boolean
	 */
    function exists_video($video_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where("youtube_id", $video_id);
        $query = $this->db_my_db->get("yt_video");
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }
	/**
	 *
	 * @param string $video_id Youtube id
	 * @return int Id video of database.
	 */
	public function get_video_id($video_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where("youtube_id", $video_id);
        $query = $this->db_my_db->get("yt_video");
		$rows = $query->row_array();
		return count($rows) > 0 ? $rows["id"] : 0;
	}
	/**
	 *
	 * @param string $playlistId Play list of youtube account
	 * @return boolean
	 */
    function exists_playlist($playlistId) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where("playlist", $playlistId);
        $query = $this->db_my_db->get("yt_playlist");
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    /**
     * GETS VIDEOS
     *
	 *
	 * @return array|bool return an array of object videos the database
	 */
    function get_ytool_videos() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->order_by('channel');
        $query = $this->db_my_db->get('yt_video');
        return $query->result();
    }
	/**
	 *
	 * @param int $v_id Id of video registered into logs
	 * @return array
	 */
    function get_video_log($v_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->select('v.youtube_id as video_id, v.channel, h.registered_date, h.who, h.video_views as views, h.channel_subs as subs, h.video_likes as likes, a.name as admin, t.title as task');
        $this->db_my_db->from('yt_video v');
        $this->db_my_db->join('yt_history h', 'h.video_id = v.id');
        $this->db_my_db->join('yt_admin_user a', 'a.id = h.admin_id');
        $this->db_my_db->join('yt_task t', 't.id = h.task_id');
        $this->db_my_db->where('h.video_id', $v_id);
        $query = $this->db_my_db->get();
        return $query->result();
    }
	/**
	 * Get rows of video log
	 * @param string $v_id
	 * @return
	 */
    function get_video_rep($v_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->select('v.youtube_id as video_id, v.channel, h.registered_date, h.who, h.video_views as views, h.video_likes as likes, h.channel_subs as subs, a.name as admin, t.title as task');
        $this->db_my_db->from('yt_video v');
        $this->db_my_db->join('yt_history h', 'h.video_id = v.id');
        $this->db_my_db->join('yt_admin_user a', 'a.id = h.admin_id');
        $this->db_my_db->join('yt_task t', 't.id = h.task_id');
        $this->db_my_db->where('v.youtube_id', $v_id);
        $query = $this->db_my_db->get();
        return $query->result();
    }
	/**
	 * Get report log by user_id into $start and $end date
	 *
	 * @param string $channel
	 * @param string $startDate
	 * @param string $endDate
	 * @param string $admin
	 * @param string $video_id
	 * @param string $action_taken
	 * @return array
	 */
	public function get_report_log($user_id, $startDate, $endDate, $admin = '', $video_id = '', $action_taken = '') {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select('*');
        $this->db_my_db->select('v.youtube_id as video_id, v.channel, h.registered_date, h.who, h.video_views as views, h.video_likes as likes, h.channel_subs as subs, a.name as admin, t.title as task');
        $this->db_my_db->from('yt_video v');
        $this->db_my_db->join('yt_history h', 'h.video_id = v.id');
        $this->db_my_db->join('yt_admin_user a', 'a.id = h.admin_id');
        $this->db_my_db->join('yt_task t', 't.id = h.task_id', 't.description');
        $this->db_my_db->where('h.user_id', $user_id);
		$this->db_my_db->where('h.registered_date >=', $startDate);
		$this->db_my_db->where('h.registered_date <=', $endDate);
		if ($admin != '') $this->db_my_db->where("a.name", $admin);
		if ($video_id != '') $this->db_my_db->where("v.youtube_id", $video_id);
		if ($action_taken != '') $this->db_my_db->where("t.description", $action_taken);
		$this->db_my_db->order_by("h.registered_date", "DESC");
        $query = $this->db_my_db->get();
        return $query->result();
	}
	/**
	 * Get all log by video
	 *
	 * @return array
	 */
    function get_video_logs() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('v.youtube_id as video_id, v.channel, h.registered_date, h.who, h.video_views as views, h.video_likes as likes, a.name as admin, t.title as task, t.id as task_id ');
        $this->db_my_db->from('yt_video v');
        $this->db_my_db->join('yt_history h', 'h.video_id = v.id');
        $this->db_my_db->join('yt_admin_user a', 'a.id = h.admin_id');
        $this->db_my_db->join('yt_task t', 't.id = h.task_id');
        $query = $this->db_my_db->get();
        return $query->result();
    }
	/**
	 * Return all registered logs
	 * @return array
	 */
    function get_play_logs() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('p.playlist as playlistID, p.channel, h.registered_date, h.who, a.name as admin, t.title as task, t.id as task_id ');
        $this->db_my_db->from('yt_playlist p');
        $this->db_my_db->join('yt_history h', 'h.playlist_id = p.id');
        $this->db_my_db->join('yt_admin_user a', 'a.id = h.admin_id');
        $this->db_my_db->join('yt_task t', 't.id = h.task_id');

        $query = $this->db_my_db->get();

        return $query->result();
    }
	/**
	 *
	 * @param string $email User email
	 * @param string $password User password
	 * @return boolean
	 */
    function login($email, $password) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where("email", $email);
        $this->db_my_db->where("password", $password);

        $query = $this->db_my_db->get("user");
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $rows) {
                //add all data to session
                $newdata = array(
                    'user_id' => $rows->id,
                    'user_name' => $rows->username,
                    'user_email' => $rows->email,
                    'logged_in' => TRUE,
                );
            }
            $this->session->set_userdata($newdata);
            return true;
        }
        return false;
    }

    function get_yt_settings() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $query = $this->db_my_db->get("yt_settings");
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }

	/**
	 *
	 * @return array
	 */
    public function get_play_video() {

        $data = array(
            'user_id' => $this->input->post('user_id'),
            'video_id' => $this->input->post('video_id'),
            'videoFeedID' => $this->input->post('videoFeedID'),
            'channel' => $this->input->post('channel')
        );

        return $data;
    }
	/**
	 * Get old subscribers for a video or channel selected into history table.
	 *
	 * @param string $criteria Name of cols into table yt_history
	 * @param int|string $value Whitch value of cols into select
	 * @return int
	 */
	public function get_subscribers_by($criteria, $value) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select('COUNT(h.channel_subs) as total_sub');
        $this->db_my_db->from('yt_history h');
        $this->db_my_db->join('yt_video v', 'h.video_id = v.id');
		$this->db_my_db->where('v.' . $criteria, $value);

        $query = $this->db_my_db->get();

        $subscribers = $query->result();

		return count($subscribers) > 0 ? $subscribers[0]->total_sub : 0;
	}
	/**
	 * OAuth
	 *
	 * Set like a videos
	 *
	 * @param string $video_id Youtube video id. Can be an url for video.
	 * @param int $user_id User ID with enable auth
	 * @return boolean
	 */
	public function like($video_id, $user_id) {
		$video_id = $this->get_id_by_url($video_id);
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$youtube->videos->rate($video_id, "like");

				$this->set_history($video_id, $user_id, array(
					"channel" => $this->user_model->get_channel($user_id),
					"task_id" => 3
				));
				return TRUE;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
		return FALSE;
    }
	/**
	 * Comment a videos
	 *
	 * @param string $video_id Toutube id, can be an url for video
	 * @param int $user_id User ID with enable auth
	 * @param string $comment Content of message for comment
	 * @return boolean
	 */
	public function comment($video_id, $user_id, $comment) {
		if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
			$aux = explode("=", $video_id);
			$video_id = $aux[1];
		}

		$yt = new Zend_Gdata_YouTube();
		$profile = $this->user_model->getUserProfile($user_id);
		$channel = $profile["username"];
		$yt->setMajorProtocolVersion(2);

		try {
			$newComment = $yt->newCommentEntry();
			$newComment->content = $yt->newContent()->setText($comment);
			$videoEntry = $yt->getVideoEntry($video_id);
			$commentFeedPostUrl = $videoEntry->getVideoCommentFeedUrl();

			$yt->insertEntry($newComment, $commentFeedPostUrl, 'Zend_Gdata_YouTube_CommentEntry');
//			$views = $videoEntry->getVideoViewCount();
//			$video_title = $videoEntry->getVideoTitle();
//			$rating = $videoEntry->getVideoRatingInfo();
//			$likes = $rating['numRaters'];

			$this->video_model->set_history($video_id, $user_id, array(
				"channel" => $channel,
				"task_id" => 5
			));

//			$video = $this->video_model->exists_video($video_id);
//			$v_id = $video["id"];
//			if (!$video) {
//				$data = array(
//					"youtube_id" => $video_id,
//					"channel" => '',
//					"title" => $video_title
//				);
//				$v_id = $this->video_model->insert_video($data);
//			}
//			if ($video) {
//				$dbdata = array(
//					"registered_date" => date("Y-m-d H:i:s"),
//					"admin_id" => $this->session->userdata('user_id'),
//					"user_id" => $user_id,
//					"video_id" => $v_id,
//					"task_id" => 5,
//					"video_likes" => $likes,
//					"video_views" => $views,
//					"who" => $channel . " ($title)"
//				);
//				$this->video_model->insert_history($dbdata);
//			}
			return TRUE;
		} catch (Zend_Gdata_App_HttpException $httpException) {
			error_log($httpException->getRawResponseBody());
		}

		return FALSE;
	}
	/**
	 * Set favorite videos
	 *
	 * @param string $video_id Youtube video ID
	 * @param int $user_id User ID with enable auth
	 * @return boolean
	 */
	public function favorite($video_id, $user_id) {
		if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
			$aux = explode("=", $video_id);
			$video_id = $aux[1];
		}

		$yt = $this->user_model->getHttpClient($user_id);
		$yt->setMajorProtocolVersion(2);

		$favoritesFeed = $yt->getUserFavorites($video_id);

		$newFavoriteVideoEntry = $yt->getVideoEntry($video_id);
		try {
			$yt->insertEntry($newFavoriteVideoEntry, $favoritesFeed->getSelfLink()->href);

			return TRUE;
		} catch (Zend_App_Exception $e) {
			error_log($e->getMessage());
		} catch (Zend_Gdata_App_HttpException $e) {
			error_log($e->getMessage());
		}

		return FALSE;
	}
	/**
	 * Set favorite videos
	 *
	 * @param string $user_channel User Youtube ID of channel
	 * @param int $user_id User ID with enable auth
	 * @return boolean
	 */
	public function featured_channel($user_channel,$user_id){

		$token_base = $this->user_model->get_user_meta($user_id, 'token', true);
		$token_featured = $this->user_model->get_user_meta($user_channel, 'token', true);
		$client_base = $this->get_google_client();
		$client_featured = clone $client_base;

		$youtube_base = new Google_YoutubeService($client_base);
		$youtube_featured = new Google_YoutubeService($client_featured);
		if (isset($token_base) && isset($token_featured)) {
			$client_base->setAccessToken($token_base);
			$client_featured->setAccessToken($token_featured);
		}
		if ($client_base->getAccessToken() && $client_featured->getAccessToken()) {
			$_SESSION['token_base'] = $client_base->getAccessToken();
			$_SESSION['token_featured'] = $client_featured->getAccessToken();

			try {
				$video_id = $this->get_id_by_url($data["videoId"]);

				$postBody = new Google_PlaylistItem();

				$resource = new Google_ResourceId();
				$resource->setVideoId($video_id);
				$resource->setKind("youtube#video");

				$snippet = new Google_PlaylistItemSnippet();
				$snippet->setResourceId($resource);
				$snippet->setPlaylistId($playlistId);
				$postBody->setSnippet($snippet);

				$video = $youtube->playlistItems->insert(
						'snippet',
						$postBody
				);
				return TRUE;
			} catch (Google_ServiceException $e) {
				echo(sprintf('<p>A service error occurred: <code>%s</code></p>',
						htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				echo(sprintf('<p>An client error occurred: <code>%s</code></p>',
						htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
		return FALSE;
	}
	/**
	 *
	 * @param string $video_id
	 * @param string $message
	 * @return boolean|mixed
	 *
	 *
	 * @param string $video_id Youtube video ID
	 * @param string $message Message for sharing
	 * @param int $user_id
	 * @return boolean|mixed The decoded response or false if an error occur
	 */
	public function share($video_id, $message, $user_id) {
		$settings = $this->get_yt_settings();
		$fdappId = $settings[0]->facebook_apikey;
		$fbsecret = $settings[0]->facebook_secret;
		$fbaccessToken = $settings[0]->facebook_accesstoken;
		$fbpageid = "me";

		$facebook = new Facebook(array(
            'appId' => $fdappId,
            'secret' => $fbsecret,
            'cookie' => true,
            'fileUpload' => true,
        ));
		$facebook->setAccessToken($fbaccessToken);

		if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
			$aux = explode("=", $video_id);
			$video_id = $aux[1];
		}

        $videoEntry = $this->get_video($video_id, $user_id);
		$videoThumbnail = $videoEntry["thumbnail"];
		$video_id = $videoEntry["video_id"];

		try {
			$params = array(
				'message' => $message,
				'link' => 'http://www.youtube.com/watch?v=' . $videoEntry["video_id"],
				'picture' => $videoThumbnail["url"]
			);
			$post = $facebook->api("/$fbpageid/feed", 'post', $params);
			$this->set_history($video_id, $user_id, array(
				"channel" => $this->user_model->get_channel($user_id),
				"task_id" => 3
			));
			return $post;
		} catch (FacebookApiException $e1) {
			error_log($e1);
		}
		return FALSE;
	}
	/**
	 *
	 * @param array $hold_users Creating an array of user id from source
	 * @param array $pair_user_login Creating an array of pair user id => login from source
	 * @param array $source Array with content "user_id|login" string
	 * @return void
	 */
	public function get_temp_users_id(& $hold_users, & $pair_user_login, $source) {
		if (is_array($source) && count($source) == 1 && $source[0] == "") {
			$hold_users = array();
			$pair_user_login = array();
			return;
		}
		foreach ($source as $temp) {
			$hold = explode("|", $temp);
			$hold_users[] = $hold[0];
			$pair_user_login[$hold[0]] = $hold[1];
		}
		return;
	}
	/**
	 * OAuth
	 *
	 * This function get list of play list by user selected
	 *
	 * @param int $user_id
	 * @return array
	 */
    public function oauth_get_playlists($user_id) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);
		$rp = $this->config->item("rp");
		$playlists = array();

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$channelsResponse = $youtube->channels->listChannels(
					'id, snippet, contentDetails, statistics, topicDetails, invideoPromotion', array(
					'mine' => 'true',
				));

				foreach ($channelsResponse['items'] as $channel) {
					$playlistsResponse = $youtube->playlists->listPlaylists(
						'id, snippet,contentDetails',
						array(
							'channelId' => $channel["id"],
							'maxResults' => $rp
						)
					);
					foreach ($playlistsResponse['items'] as $key => $playlist) {
						$playlists[$key] = $playlist;
					}
				}
				return $playlists;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			}
		}
		return $playlists;
    }
	/**
	 * OAth
	 *
	 * @param type $user_id
	 * @param string $playlistId
	 * @return array Playlist for $playlistId selected
	 */
	public function oauth_get_playlist($user_id, $playlistId) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);
		$rp = $this->config->item("rp");
		$playlists = array();

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {

				$playlistsResponse = $youtube->playlists->listPlaylists(
					'id, snippet,contentDetails',
					array(
						'id' => $playlistId,
						'maxResults' => $rp
					)
				);
				foreach ($playlistsResponse['items'] as $key => $playlist) {
					$playlists = $playlist;
				}
				return $playlists;
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
			}
		}
		return $playlists;
	}
	/**
	 * Enable the field change of video entry defined
	 *
	 * @param Zend_Gdata_YouTube_VideoEntry $videoEntry
	 * @param string $value
	 * @param string $field
	 */
	public function set_value_edit(& $videoEntry, $value, $field) {
		if ($field == "title") {
			$videoEntry->setVideoTitle($value);
		} else if ($field == "description") {
			$videoEntry->setVideoDescription($value);
		} else if ($field == "tags") {
			$videoEntry->setVideoTags($value);
		} else if ($field == "category") {
			$videoEntry->setVideoCategory($value);
		}
	}
	/**
	 *
	 * @param int $user_id
	 * @param string $playlistId
	 * @param array $data
	 *
	 * @return boolean True if success operation
	 */
	public function saveplaylist($user_id, $playlistId, $data) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->get_google_client();
		$youtube = new Google_YoutubeService($client);

		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$content = new Google_Playlist();
				$content->setId($playlistId);

				$snippet = new Google_PlaylistSnippet();
				$snippet->setTitle($data["title"]);
				$snippet->setDescription($data["description"]);
				$content->setSnippet($snippet);

				$youtube->playlists->update(
					'snippet,status',
					$content
				);
				return TRUE;
			} catch (Google_ServiceException $e) {
				echo(sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			} catch (Google_Exception $e) {
				echo(sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage())));
				return FALSE;
			}
		}
	}
}

/**/
