<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Video_model extends CI_Model {

	private $count_videos;
	private $categories;

    public function __construct() {
        parent::__construct();

		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		Zend_Loader::loadClass('Zend_Gdata_YouTube_CommentEntry');
        Zend_Loader::loadClass('Zend_Gdata_YouTube_VideoQuery');
        Zend_Loader::loadClass('Zend_Gdata_AuthSub');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_HttpClient');
        Zend_Loader::loadClass('Zend_Uri_Http');
        Zend_Loader::loadClass('Zend_Gdata_YouTube_PlaylistVideoEntry');

		$this->load->model('user_model');
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
	 * Enable a object of upload video
	 *
	 * @param string $userName User name of youtube account
	 * @return Zend_Gdata_YouTube_VideoFeed
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
	 *
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
	 *
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
	 *
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

	public function get_subscriptors($username) {
		$yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);

		$userProfile = $yt->getUserProfile($username);
        // to retrieve the currently authenticated user's profile
        $profile = array();
        $profile["title"] = $userProfile->title->text;
        $profile["username"] = $userProfile->username->text;
        $profile["subs"] = $userProfile->getFeedLink('http://gdata.youtube.com/schemas/2007#user.subscriptions')->countHint;
        return $profile;
	}

	/**
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
		$this->db_my_db->order_by("id", "ASC");
		$query = $this->db_my_db->get('yt_country');
		return $query->result();
	}
	/**
	 *
	 * @return mixed Get all categories of table category
	 */
	public function get_all_categories() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select(array('category', 'display_category'));
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
	 * @param type $viedo_id youtuve id
	 */
	public function set_video($values, $viedo_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->update('yt_video', $values, "youtube_id = '{$viedo_id}'");
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
	 *
	 * @param type $channel
	 * @return type
	 */
	public function get_report_log($channel, $admin = '', $video_id = '', $action_taken = '') {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select('*');
        $this->db_my_db->select('v.youtube_id as video_id, v.channel, h.registered_date, h.who, h.video_views as views, h.video_likes as likes, h.channel_subs as subs, a.name as admin, t.title as task');
        $this->db_my_db->from('yt_video v');
        $this->db_my_db->join('yt_history h', 'h.video_id = v.id');
        $this->db_my_db->join('yt_admin_user a', 'a.id = h.admin_id');
        $this->db_my_db->join('yt_task t', 't.id = h.task_id', 't.description');
        $this->db_my_db->where('v.channel', $channel);
		if ($admin != '') $this->db_my_db->where("a.name", $admin);
		if ($video_id != '') $this->db_my_db->where("v.youtube_id", $video_id);
		if ($action_taken != '') $this->db_my_db->where("t.description", $action_taken);
        $query = $this->db_my_db->get();
        return $query->result();
	}

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
	 *
	 * @param int $opt
	 * @param string $admin
	 * @param string $task
	 * @param string $video_id
	 * @param string $channel
	 * @param string $who
	 * @return string
	 */
	public function print_desc($opt, $admin, $task, $video_id, $channel, $who) {
		switch ($opt) {
			case 1: return "$admin did $task on video $video_id in the channel $channel";//edit_video
				break;
			case 2: return "";//remove_video
				break;
			case 3: return "$admin did $task on video $video_id in the channel $channel using the channel $who ";//like_video
				break;
			case 4: return " ";//share_video
				break;
			case 5: return "$admin did $task on video $video_id in the channel $channel using the channel $who ";//comment_video
				break;
			case 6: return "$admin did $task ($video_id) in the channel $channel ";//upload video
				break;
			case 7: return "$admin did $task ($video_id) in the channel $channel ";//new_playlis
				break;
			case 8: return "$admin did $task ($video_id) in the channel $channel ";//edit_play
				break;
			case 9: return "$admin did $task ($video_id) in the channel $channel ";//remove_pla
				break;
			default : return "";
				break;
		}
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
	 * Set like a videos
	 *
	 * @param string $video_id Youtube video id. Can be an url for video.
	 * @param int $user_id User ID with enable auth
	 * @return boolean
	 */
	public function like($video_id, $user_id) {
		if (!empty($video_id))
			$video_id = trim($video_id);
		if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
			$aux = explode("=", $video_id);
			$video_id = $aux[1];
		}

		$yt = $this->user_model->getHttpClient($user_id);
		$yt->setMajorProtocolVersion(2);
		$videoEntryToRate = $yt->getVideoEntry($video_id);
		$videoEntryToRate->setVideoRating(5);
		$ratingUrl = $videoEntryToRate->getVideoRatingsLink()->getHref();
		$profile = $this->user_model->getUserProfile($user_id);
		$channel = $profile["username"];
		$title = $profile["title"];
		try {
			$yt->insertEntry($videoEntryToRate, $ratingUrl, 'Zend_Gdata_YouTube_VideoEntry');
			$videoEntry = $yt->getVideoEntry($video_id);
			$views = $videoEntry->getVideoViewCount();
			$video_title = $videoEntry->getVideoTitle();
			$rating = $videoEntry->getVideoRatingInfo();
			$likes = $rating['numRaters'];
			$video = $this->video_model->exists_video($video_id);
			$v_id = $video["id"];
			if (!$video) {
				$data = array(
					"youtube_id" => $video_id,
					"channel" => '',
					"title" => $video_title
				);
				$v_id = $this->video_model->insert_video($data);
			}

			$dbdata = array(
				"registered_date" => date("Y-m-d H:i:s"),
				"admin_id" => $this->session->userdata('user_id'),
				"video_id" => $v_id,
				"video_likes" => $likes,
				"video_views" => $views,
				"task_id" => 3,
				"who" => $channel . " ($title)"
			);
			$this->video_model->insert_history($dbdata);
			return TRUE;
		} catch (Zend_Gdata_App_HttpException $httpException) {
			error_log($httpException->getRawResponseBody());
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

		$yt = $this->user_model->getHttpClient($user_id);
		$profile = $this->user_model->getUserProfile($user_id);
		$channel = $profile["username"];
		$title = $profile["title"];
		$yt->setMajorProtocolVersion(2);

		try {
			$newComment = $yt->newCommentEntry();
			$newComment->content = $yt->newContent()->setText($comment);
			$videoEntry = $yt->getVideoEntry($video_id);
			$commentFeedPostUrl = $videoEntry->getVideoCommentFeedUrl();

			$yt->insertEntry($newComment, $commentFeedPostUrl, 'Zend_Gdata_YouTube_CommentEntry');
			$views = $videoEntry->getVideoViewCount();
			$video_title = $videoEntry->getVideoTitle();
			$rating = $videoEntry->getVideoRatingInfo();
			$likes = $rating['numRaters'];
			$video = $this->video_model->exists_video($video_id);
			$v_id = $video["id"];
			if (!$video) {
				$data = array(
					"youtube_id" => $video_id,
					"channel" => '',
					"title" => $video_title
				);
				$v_id = $this->video_model->insert_video($data);
			}
			if ($video) {
				$dbdata = array(
					"registered_date" => date("Y-m-d H:i:s"),
					"admin_id" => $this->session->userdata('user_id'),
					"video_id" => $v_id,
					"task_id" => 5,
					"video_likes" => $likes,
					"video_views" => $views,
					"who" => $channel . " ($title)"
				);
				$this->video_model->insert_history($dbdata);
			}
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
	 *
	 * @param string $video_id Youtube video ID
	 * @param string $message Message for sharing
	 * @return boolean|mixed The decoded response or false if an error occur
	 */
	public function share($video_id, $message) {
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

		$yt = new Zend_Gdata_YouTube();
        $videoEntry = $yt->getVideoEntry($video_id);
		$videoThumbnails = $videoEntry->getVideoThumbnails();
		$videoThumbnail = $videoThumbnails[0];
		$video_id = $videoEntry->getVideoId();

		try {
			$params = array(
				'message' => $message,
				'link' => 'http://www.youtube.com/watch?v=' . $videoEntry->getVideoId(),
				'picture' => $videoThumbnail["url"]
			);
			$post = $facebook->api("/$fbpageid/feed", 'post', $params);
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

	function showPlaylist($userName = "castrorojasjaime") {
        $yt = new Zend_Gdata_YouTube();
//        $yt = $this->user_model->getHttpClient();
        // optionally set version to 2 to retrieve a version 2 feed
        $yt->setMajorProtocolVersion(2);
        $playlistListFeed = $yt->getPlaylistListFeed($userName);

        $this->printPlaylistListFeed($playlistListFeed, $showPlaylistContents = true);
    }

    function printPlaylistListFeed($playlistListFeed, $showPlaylistContents) {
        $count = 1;
        foreach ($playlistListFeed as $playlistListEntry) {
            echo 'Entry # ' . $count . "\n";
            // This function is defined in the next section
            $this->printPlaylistListEntry($playlistListEntry, $showPlaylistContents);
            echo "<br>";
            $count++;
        }
    }

	function printPlaylistListEntry($playlistListEntry, $showPlaylistContents = false)
	{
	  echo 'Playlist: ' . $playlistListEntry->title->text . "\n";
	  echo "\tDescription: " . $playlistListEntry->description->text .
	"\n";
	  if ($showPlaylistContents === true) {
		$this->getAndPrintPlaylistVideoFeed($playlistListEntry, "\t\t");
	  }
	}

	function getAndPrintPlaylistVideoFeed($playlistListEntry)
	{
		$yt = new Zend_Gdata_YouTube();
		$playlistVideoFeed = $yt->getPlaylistVideoFeed($playlistListEntry->getPlaylistVideoFeedUrl());
		foreach ($playlistVideoFeed as $playlistVideoEntry) {
			$this->printVideoEntry($playlistVideoEntry);
		}
	}

	function printVideoEntry($videoEntry)
	{
		// the videoEntry object contains many helper functions that access the underlying mediaGroup object
		echo 'Video: ' . $videoEntry->getVideoTitle() . "\n";
		echo 'Video ID: ' . $videoEntry->getVideoId() . "\n";
		echo 'Updated: ' . $videoEntry->getUpdated() . "\n";
		echo 'Description: ' . $videoEntry->getVideoDescription() . "\n";
		echo 'Category: ' . $videoEntry->getVideoCategory() . "\n";
		echo 'Tags: ' . implode(", ", $videoEntry->getVideoTags()) . "\n";
		echo 'Watch page: ' . $videoEntry->getVideoWatchPageUrl() . "\n";
		echo 'Flash Player Url: ' . $videoEntry->getFlashPlayerUrl() . "\n";
		echo 'Duration: ' . $videoEntry->getVideoDuration() . "\n";
		echo 'View count: ' . $videoEntry->getVideoViewCount() . "\n";
		echo 'Rating: ' . $videoEntry->getVideoRatingInfo() . "\n";
		echo 'Geo Location: ' . $videoEntry->getVideoGeoLocation() . "\n";

		// see the paragraph above this function for more information on the 'mediaGroup' object
		// here we are using the mediaGroup object directly to its 'Mobile RSTP link' child

		echo "Thumbnails:\n";
		$videoThumbnails = $videoEntry->getVideoThumbnails();

		foreach($videoThumbnails as $videoThumbnail) {
			echo $videoThumbnail['time'] . ' - ' . $videoThumbnail['url'];
			echo ' height=' . $videoThumbnail['height'];
			echo ' width=' . $videoThumbnail['width'] . "\n";
		}
	}

    function getPlaylistListFeed($playlistListFeed, $showPlaylistContents) {
        $count = 1;
        foreach ($playlistListFeed as $playlistListEntry) {
            echo 'Entry # ' . $count . "\n";
            // This function is defined in the next section
            $this->printPlaylistListEntry($playlistListEntry, $showPlaylistContents);
            echo "<br>";
            $count++;
        }
    }
	/**
	 * Enable the field change of video entry defined
	 *
	 * @param Zend_Gdata_YouTube_VideoEntry $videoEntry
	 * @param string $value
	 * @param string $field
	 */
	public function set_value_edit($videoEntry, $value, $field) {
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
}

/**/