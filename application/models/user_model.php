<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_model extends CI_Model {

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

		require_once 'google-api-php-client/src/Google_Client.php';
		require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
		require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
//		require_once 'google-api-php-client/src/contrib/Google_PlusService.php';
    }
	/**
	 *
	 * @param string $url URL for checkin
	 * @return boolean
	 */
	function check_url($url) {
		$check = FALSE;
		$fp = @fopen($url, "r");

		 if ($fp) {
			 $check = TRUE;
		 }

		@fclose($fp);
		return $check;
	}
	/**
	 * OAuth
	 *
	 * This funcion work in OAuth protocol.
	 * The function return a array with information of user.
	 *
	 * ["title"] : Title of channel
	 * ["channel_id"] : channel id, get of api V3
	 * ["username"] : user name of Youtube.
	 * ["subs"] : Subcriptions by user at channel
	 *
	 * @param int $user_id The user ID of database. table users
	 * @return array Array of
	 */
	function getUserProfile($user_id) {
		$token = $this->user_model->get_user_meta($user_id, 'token', true);

		$client = $this->video_model->get_google_client();

		$youtube = new Google_YoutubeService($client);
		$profile = array();
		if (isset($token)) {
			$client->setAccessToken($token);
		}

		if ($client->getAccessToken()) {
			$_SESSION['token'] = $client->getAccessToken();

			try {
				$channelsResponse = $youtube->channels->listChannels('id,snippet,contentDetails,statistics,topicDetails,invideoPromotion', array(
						'mine' => 'true',
				));

				foreach ($channelsResponse["items"] as $channel) {
					$profile["title"] = $channel["snippet"]["title"];
					$profile["channel_id"] = $channel["id"];
					$profile["username"] = $this->get_channel($user_id);
					$profile["subs"] = $channel["statistics"]["subscriberCount"];
				}
			} catch (Google_ServiceException $e) {
				error_log(sprintf('<p>A service error occurred: <code>%s</code></p>',
						htmlspecialchars($e->getMessage())));
			} catch (Google_Exception $e) {
				error_log(sprintf('<p>An client error occurred: <code>%s</code></p>',
						htmlspecialchars($e->getMessage())));
			}

		}
        return $profile;
    }

	/**
	 * @deprecated since version 1.0
	 *
	 * @param int $user_id The user in database that contain the token id of youtube
	 * @return Zend_Gdata_YouTube
	 */
	function getHttpClient($user_id) {
        // $authenticationURL = 'https://www.google.com/accounts/ClientLogin';
        $user_token = $this->get_user_token($user_id);
		if (!isset($user_token[0])) {
			error_log($e->getMessage());
			redirect("video/msg/error/user-auth");
			return;
		}
		try {
			$httpClient = Zend_Gdata_AuthSub::getHttpClient($user_token[0]['user_yt_token']);
		} catch (Zend_Gdata_App_HttpException $e) {
			error_log($e->getMessage());
			redirect("video/msg/error/user-auth");
			return;
		}
        // $developerKey = 'AI39si6kLzUxB8Fdu2pIq3lGEeWL2X9z8XrjW6j_7adfkAMivtyF0PhIJ9BBGmR5_QKdd6hQunNmzDGf85rXSwFxQLlvZIjUZA';
		$developerKey = "AI39si6G_AZmPTDZamsJd5c5t3PhmqYIgf9i9WSSaRTwxvyULmb0VirwdC_EonzNScua-M1uxH0EcSOpgYlrdy-ovQ8YWFaAqQ";

		// "AI39si6G_AZmPTDZamsJd5c5t3PhmqYIgf9i9WSSaRTwxvyULmb0VirwdC_EonzNScua-M1uxH0EcSOpgYlrdy-ovQ8YWFaAqQ";
        // $applicationId = 23;
        $applicationId = 'Buzzmyvideos';
        // $clientId = 234;
        $clientId = NULL;

		try {
			$yt = new Zend_Gdata_YouTube($httpClient, $applicationId, $clientId, $developerKey);
		} catch (Zend_Gdata_App_HttpException $e) { // Zend_Gdata_App_HttpException
			error_log($e->getMessage());
			redirect("video/msg/error/user-channel");
			return;
		}
        return $yt;
    }

    function register_admin($dbdata) {
        return $this->db_my_db->insert('yt_admin_user', $dbdata);
    }

    function user_exist($username) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->where('username', $username);
        $query = $this->db_my_db->get('yt_admin_user');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
	/**
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function delete_user($user_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$sql = "DELETE FROM `54_yt_admin_user` WHERE `id` =$user_id";

        if ($this->db_my_db->query($sql))
            return true;
        else
            return false;
	}
	/**
	 *
	 * @param string $username
	 * @param string $dbdata
	 * @return bool
	 */
    function update_admin($username, $dbdata) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where('username', $username);
        return $this->db_my_db->update('yt_admin_user', $dbdata);
    }
	/**
	 * Check if user is a admin
	 *
	 * @param string $username The user name of admin
	 * @return boolean True if is admin user
	 */
    function is_user_admin($username) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->where("username", $username);
        $query = $this->db_my_db->get('yt_admin_user');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function login_admin($username, $password) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->where("username", $username);
        $this->db_my_db->where("password", $password);

        $query = $this->db_my_db->get("yt_admin_user");
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $rows) {
                //add all data to session
                $newdata = array(
                    'username' => $username,
                    'password' => $password
                );
            }
            $this->session->set_userdata('logged_in', $newdata);
            return true;
        }
        return false;
    }
	/**
	 * This function retrieve the token for database for this system,
	 *
	 * if The token is stored in database, return token, otherwise return string empty
	 *
	 * @param type $user_id
	 * @return string|array Return array of object
	 */
    function get_user_token($user_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('user_yt_token');
        $this->db_my_db->where('user_id', $user_id);
        $query = $this->db_my_db->get('yt_auth');
        if ($query->num_rows() > 0)
            return $query->result_array();
        else
            return "";
    }
	/**
	 * @deprecated since version 1.0
	 * @param string $token Token auth for youtube manage
	 * @param int $user_id User id of Wordpress Account
	 * @return boolean
	 */
    function update_token($token, $user_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		if ( $this->get_user_token($user_id) != '') {
			$sql = "UPDATE `54_yt_auth` SET `user_yt_token` = '$token' WHERE user_id=$user_id";
		} else {
			$sql = "INSERT INTO `54_yt_auth` (`user_yt_token`, `user_id`) VALUES ('$token', $user_id)";
		}
        if ($this->db_my_db->query($sql))
            return true;
        else
            return false;
    }
	/**
	 *
	 * @param int $user_id User ID of wordpress installation
	 * @return boolean
	 */
	public function delete_token($user_id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$sql = "DELETE FROM `54_yt_auth` WHERE `user_id` =$user_id";

        if ($this->db_my_db->query($sql))
            return true;
        else
            return false;
	}
	/**
	 *
	 * @param array $object
	 * @param string $key
	 * @return array
	 */
	public function get_array($object, $key) {
		if (!is_array($object)) return array();
		$array = array();
		foreach ($object as $item) {
			$array[] = $item["{$key}"];
		}
		return $array;
	}
	/**
	 * Get an array of user auth with token enable.
	 *
	 * @return array
	 */
	public function get_array_ids_users() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select("user_id");
		$query = $this->db_my_db->get('yt_auth');
		return $this->get_array($query->result_array(), "user_id");
	}
	/**
	 *
	 * @param type $page
	 * @return type
	 */
	public function get_users_auth($start = 0) {
		$rp = $this->config->item("rp");
		$this->db_my_db = $this->load->database('my_db', TRUE);
		$this->db_my_db->select("*");
		$this->db_my_db->limit($rp, $start);
		$query = $this->db_my_db->get('yt_auth');

		return $query->result_array();
	}
	/**
	 * Get all user with token if is not empty, this a meta key user
	 * Defined into system wordpress. The user enable auth for your videos
	 *
	 *
	 * @param int $start
	 * @param string $username
	 * @param string $youtube
	 * @param string $country
	 * @param string $category
	 * @param string $sex
	 * @return array|string array of object or empty string for null query
	 */
    function get_all_users($start = 0, $username = "", $youtube = "", $country = "", $category = "", $sex = "") {
		$rp = $this->config->item('rp');
		$auth = $this->config->item('show_auth');

		$this->db = $this->load->database('default', TRUE);
        $sql = "SELECT
                u1.id,
                u1.user_login,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
				CONCAT(m1.meta_value, ' ', m2.meta_value) AS name,
				m3.meta_value AS sex,
				m4.meta_value AS youtube_channels,
				m5.meta_value AS youtube_content_category,
				m7.meta_value AS country,
                m9.meta_value AS user_yt_token_auth

                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "")
				. " WHERE 1"
				. ($username != "" ? " AND u1.user_login LIKE '%{$username}%'" : "")
				. ($youtube != "" ? " AND m4.meta_value LIKE '%{$youtube}%'" : "")
				. ($country != "" ? " AND m7.meta_value LIKE '%{$country}%'" : "")
				. ($category != "" ? " AND m5.meta_value LIKE '%{$category}%'" : "")
				. ($sex != "" ? " AND m3.meta_value = '{$sex}'" : "")
				. "
                ORDER BY u1.id
                LIMIT {$start}, {$rp} ";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }
	/**
	 *
	 * @param type $username
	 * @param type $youtube
	 * @param type $country
	 * @param type $category
	 * @param type $sex
	 * @return int Get total count rows of users
	 */
	public function count_rows_users($username = "", $youtube = "", $country = "", $category = "", $sex = "") {
		// return count($this->get_array_ids_users());
		$auth = $this->config->item('show_auth');
		$this->db = $this->load->database('default', TRUE);
        $sql = "SELECT
                COUNT(u1.id) AS count
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "")
				. " WHERE 1"
				. ($username != "" ? " AND u1.user_login LIKE '%{$username}%'" : "")
				. ($youtube != "" ? " AND m4.meta_value LIKE '%{$youtube}%'" : "")
				. ($country != "" ? " AND m7.meta_value LIKE '%{$country}%'" : "")
				. ($category != "" ? " AND m5.meta_value LIKE '%{$category}%'" : "")
				. ($sex != "" ? " AND m3.meta_value = '{$sex}'" : "")
				;

        $query = $this->db->query($sql);
		$rows = $query->result();

		return $rows[0]->count;
	}
	/**
	 *
	 * @return array
	 */
	public function get_array_users() {
		$auth = $this->config->item('show_auth');

		$this->db = $this->load->database('default', TRUE);
        $sql = "SELECT
				CONCAT(m1.meta_value, ' ', m2.meta_value, ' - ', u1.user_login) AS search

                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "");

        $query = $this->db->query($sql);

		return $query->result("array");
	}

	function search_users($q, $start = 0) {
		$auth = $this->config->item('show_auth');
		$rp = $this->config->item('rp');

		$this->db = $this->load->database('default', TRUE);

        $sql = "SELECT
                u1.id,
				u1.user_login,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
				CONCAT(m1.meta_value, ' ', m2.meta_value) AS name,
				m3.meta_value AS sex,
                m4.meta_value AS youtube_channels,
                m5.meta_value AS youtube_content_category,
				m7.meta_value AS country

                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "")
				. " WHERE CONCAT(m1.meta_value, ' ', m2.meta_value) LIKE '%{$q}%'"
				. "
                ORDER BY u1.id
                LIMIT {$start}, {$rp} ";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }
	/**
	 *
	 * @param string $searchWord
	 * @return array
	 */
	public function search($searchWord)
	{
		$tmpArray=array();
		/**
		 * Obtengo los datos almacenados en el array
		 */
		$data = $this->get_array_users();

		/*
		 * Recorro el array para ver si hay palabras que empiecen con lo que viene
		 * por parametros
		 */
		foreach($data as $word)
		{
			// obtengo el tamaño de la palabra que se busca.
			$searchWordSize=strlen($searchWord);
			// corto la palabra que viene del array y la dejo del mismo tamaño que
			// la que se busca de manera de poder comparar.
			$tmpWord=substr($word->search, 0,$searchWordSize);
			// si son iguales la guardo para devolverla
			if (strtolower($tmpWord) == strtolower($searchWord))
			{
				// guardo la palabra original sin cortar.
				$tmpArray[]=$word->search;
			}
		}

		return $tmpArray;
	}

	public function get_and_where($values) {
		if (!is_array($values)) return;

	}
	/**
	 *
	 * @param int $page
	 * @param array|string $where
	 * @return string|array Return array of
	 */
	function get_users_channel($where = '') {
		$auth = $this->config->item("show_auth");
		$in_values = is_array($where) ? implode(",", $where) : $where;

		$this->db = $this->load->database('default', TRUE);

        $sql = "SELECT
                u1.id,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
                m4.meta_value AS youtube_channels,
				m5.meta_value AS youtube_content_category
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "")
				. ($in_values != "" ? " WHERE u1.id in ({$in_values})" : "")
				;
        $query_videos = $this->db->query($sql);
        if ($query_videos->num_rows() > 0) {
            return $query_videos->result();
        } else {
            return "";
        }
    }
	/**
	 * This function get the username of youtube account for metadata user.
	 *
	 * The meta data user is a html tag a, due to is necessary get content of tag
	 * and analyce the string for get channel.
	 *
	 * @param int $user_id ID for wordpress system
	 * @return boolean|string The username of user, FALSE otherwise.
	 */
	public function get_channel($user_id) {
		$channel = $this->get_users_channel($user_id);

		if ($channel != "") {
			$this->load->helper('htmlsql_helper');

			$wsql = new htmlsql();

			if ($wsql->connect('string', $channel[0]->youtube_channels)) {
				if ($wsql->query( 'SELECT * FROM a')) {
					foreach ($wsql->fetch_array() as $row) {
						$channel = $row["href"];
					}
				}
			}

			if ( ! is_string($channel)) {
				$channel = $channel[0]->youtube_channels;
			}
			$channel = substr($channel, strripos($channel, "/")+1);
		}

		return is_string($channel) ? $channel : "";
	}
	/**
	 *
	 * @return string
	 */
	public function get_all_users_channel() {
		$auth = $this->config->item("show_auth");
		$this->db = $this->load->database('default', TRUE);

        $sql = "SELECT
                u1.id,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
                m4.meta_value AS youtube_channels
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
                LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
                LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "");
        $query_videos = $this->db->query($sql);
        if ($query_videos->num_rows() > 0) {
            return $query_videos->result();
        } else {
            return "";
        }
	}
	/**
	 *
	 * @param string $cat
	 * @return string|array
	 */
    function get_cat_users($cat, $start = 0) {
		$rp = $this->config->item('rp');
		$users = $this->get_array_ids_users();

		$in_values = implode(",", $users);
		$this->db = $this->load->database('default', TRUE);
        $cat = substr($cat, 0, strlen($cat) - 1);
        $sql = "
        SELECT
        u1.id,
        u1.user_login,
        u1.user_pass,
        u1.user_email,
        m1.meta_value AS firstname,
        m2.meta_value AS lastname,
        m7.meta_value AS country,
        m4.meta_value AS youtube_channels,
        m5.meta_value AS youtube_content_category
        FROM 54_users u1
        LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
        LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
        LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
        LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
		LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')
        WHERE m5.meta_value like '%{$cat}%' "
        . ($in_values != 0 ? " AND u1.id in ({$in_values})" : "")
		. " LIMIT {$start}, {$rp}";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }

	public function get_count_cat_users() {
		$users = $this->get_array_ids_users();

		$in_values = implode(",", $users);
		$this->db = $this->load->database('default', TRUE);
        $cat = substr($cat, 0, strlen($cat) - 1);
        $sql = "
        SELECT
        u1.id
        FROM 54_users u1
        LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
        LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
        LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'country')
        LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
        LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
        WHERE m5.meta_value like '%Auto%' "
        . ($in_values != 0 ? " AND u1.id in ({$in_values})" : "");

        $query = $this->db->query($sql);
		return $query->num_rows();
	}

	function get_users_by_sex($sex, $start = 0) {
		$rp = $this->config->item('rp');
		$users = $this->get_array_ids_users();

		$in_values = implode(",", $users);
		$this->db = $this->load->database('default', TRUE);

        $sql = "SELECT
                u1.id,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
                m3.meta_value AS sex,
                m4.meta_value AS youtube_channels,
                m5.meta_value AS youtube_content_category,
                m7.meta_value AS country
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
                LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
                LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
                LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
                LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')
                WHERE m3.meta_value like '%$sex%'"
				. ($in_values != 0 ? " AND u1.id in ({$in_values})" : "")
                . " LIMIT {$start}, {$rp} ";

//        echo $sql;
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }
	/**
	 *
	 * @param string $country
	 * @param type $start
	 * @return string
	 */
    function get_users_by_country($country, $start = 0) {
		$rp = $this->config->item('rp');
		$users = $this->get_array_ids_users();

		$in_values = implode(",", $users);
		$this->db = $this->load->database('default', TRUE);

        $sql = "SELECT
                u1.id,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
                m3.meta_value AS sex,
                m4.meta_value AS youtube_channels,
                m5.meta_value AS youtube_content_category,
                m7.meta_value AS country
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
                LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
                LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
                LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
                LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')
                WHERE m7.meta_value like '%$country%'"
				. ($in_values != 0 ? " AND u1.id in ({$in_values})" : "")
                . " LIMIT {$start}, {$rp} ";

//        echo $sql;
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }

    function get_users_by_name($name, $start = 0) {
		$rp = $this->config->item('rp');
		$users = $this->get_array_ids_users();

		$in_values = implode(",", $users);
		$this->db = $this->load->database('default', TRUE);

        $sql = "SELECT
                u1.id,
                u1.user_email,
                m1.meta_value AS firstname,
                m2.meta_value AS lastname,
                m3.meta_value AS sex,
                m4.meta_value AS youtube_channels,
                m5.meta_value AS youtube_content_category,
                m7.meta_value AS country
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
                LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
                LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
                LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
                LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')
                WHERE (m1.meta_value LIKE '%$name%' OR m2.meta_value LIKE '%$name%')"
				. ($in_values != 0 ? " AND u1.id in ({$in_values})" : "")
                . " LIMIT {$start}, {$rp} ";

//        echo $sql;
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }

    function get_users_light() {
		$this->db = $this->load->database('default', TRUE);
        $sql = "SELECT
                u.id,
                m.meta_value
                FROM 54_users u, 54_usermeta m
                WHERE u.id=m.user_id AND m.meta_key='first_name'";

        $sql2 = "SELECT
                u.id,
                m.meta_value AS firstname
                FROM 54_users u, 54_usermeta m
                WHERE u.id=m.user_id AND m.meta_key='first_name'";

//        echo $sql;
        $query = $this->db->query($sql2);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return "";
        }
    }
	/**
	 * Login for admin
	 *
	 * @param string $username User name
	 * @param string $password User Password
	 * @return boolean
	 */
    function admin_login($username, $password) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where("username", $username);
        $this->db_my_db->where("password", $password);

        $query = $this->db_my_db->get("yt_admin_user");
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                //add all data to session
                $newdata = array(
                    'user_id' => $row->id,
                    'username' => $row->username,
                    'name' => $row->name,
                    'logged_in' => TRUE,
                    'type' => $row->type
                );
            }
            $this->session->set_userdata($newdata);
            return true;
        }
        return false;
    }
	/**
	 *
	 * @return array Array of row of table admin
	 */
    function retrieve_admin() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $username = $this->session->userdata('username');
        $password = $this->session->userdata('password');

        $this->db_my_db->where("username", $username);
        $this->db_my_db->where("password", $password);

        $query = $this->db_my_db->get('yt_admin_user');

        return $query->result();
    }
	/**
	 *
	 * @return array Rows of yt_settings
	 */
    function get_setting() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $query = $this->db_my_db->get('yt_settings');
        return $query->result();
    }


    function update_setting($data) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->update('yt_settings', $data);
    }

    function retrieve_users() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->where("usertype", "Registered");
        $query = $this->db_my_db->get('user');
        return $query->result();
    }

    public function get_all_admins() {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->order_by('name');
        $query = $this->db_my_db->get('yt_admin_user');
        if ($query->num_rows() > 0)
            return $query->result();
        else
            return "";
    }

    function get_user_details($id) {
		$this->db_my_db = $this->load->database('my_db', TRUE);
        $this->db_my_db->select('*');
        $this->db_my_db->where('id', $id);
        $query = $this->db_my_db->get('yt_admin_user');
        return $query->row_array();
    }
	/**
	 * Get a row of user from wordpress installation
	 *
	 * @param int $user_id
	 * @return array Row of user with user_id
	 */
	public function get_auth_user($user_id) {
		$this->db = $this->load->database('default', TRUE);
		$sql = "SELECT *
		FROM  `54_users`
		WHERE  `ID` ={$user_id}
		LIMIT 1";

		$query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
	}
	/**
	 *
	 *
	 * @return array Array of countries for select dropbox.
	 */
	public function get_countries_for_select() {
		return array_merge(array(
			"" => "-- Select --",
			"United Kingdom" => "United Kingdom",
			"United States" => "United States"),
			$this->video_model->get_pair_values(
				$this->video_model->get_all_countries(),
				'country'
		));
	}
	/**
	 *
	 * @return array Array of categories for select dropbox
	 */
	public function get_categories_for_select() {
		return array_merge(
			array('' => '-- Select --'),
			$this->video_model->get_pair_values(
				$this->video_model->get_all_categories(),
				'category',
				'display_category'
			)
		);
	}
	/**
	 *
	 * @return array Get categories with youtube id associate.
	 */
	public function get_youtube_categories() {
		return array("" => "-- Select --") + $options = $this->video_model->get_pair_values(
			$this->video_model->get_all_categories(),
			'categoryId',
			'display_category'
		);
	}
	/**
	 * Get token for database in wordpress system.
	 *
	 * @param type $user_id The user id for user that auth enabled
	 * @return string Json string representation for token auth or false when token not enabled.
	 */
	public function getToken($user_id) {
		$auth = $this->config->item('show_auth');
		$this->db = $this->load->database('default', TRUE);
        $sql = "SELECT
                m9.meta_value AS user_yt_token_auth
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = 'token')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "")
				. sprintf(" WHERE m1.user_id = %d", $user_id)
				;

        $query = $this->db->query($sql);
		$rows = $query->result();

		return isset($rows[0]) ? $rows[0]->user_yt_token_auth : FALSE;
	}
	/**
	 *
	 * @param int $user_id
	 * @param string $metaname
	 * @param boolean $single
	 * @return type
	 */
	public function get_user_meta($user_id, $metaname, $single) {
		$auth = $this->config->item('show_auth');
		$this->db = $this->load->database('default', TRUE);
        $sql = "SELECT
                m9.meta_value AS {$metaname}
                FROM 54_users u1
                LEFT JOIN 54_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
                LEFT JOIN 54_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
				LEFT JOIN 54_usermeta m3 ON (m3.user_id = u1.id AND m3.meta_key = 'sex')
				LEFT JOIN 54_usermeta m4 ON (m4.user_id = u1.id AND m4.meta_key = 'youtube_channels')
				LEFT JOIN 54_usermeta m5 ON (m5.user_id = u1.id AND m5.meta_key = 'youtube_content_category')
				LEFT JOIN 54_usermeta m7 ON (m7.user_id = u1.id AND m7.meta_key = 'country')"
				. ($auth ? " JOIN 54_usermeta m9 ON (m9.user_id = u1.id AND m9.meta_key = '{$metaname}')" : "")
				. ($auth ? " JOIN 54_usermeta m10 ON (m10.user_id = u1.id AND m10.meta_key = 'type_login')" : "")
				. sprintf(" WHERE m1.user_id = %d", $user_id)
				;

        $query = $this->db->query($sql);
		$rows = $query->result();

		return isset($rows[0]) ? $rows[0]->$metaname : FALSE;
	}
	/**
	 *
	 * @param type $user_id
	 */
	public function wp_get_current_user($user_id) {

	}

}

/* -- END model user_model -- */