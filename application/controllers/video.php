<?php

session_start();

class Video extends CI_Controller {

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

        $this->load->model('video_model');
        $this->load->model('user_model');
    }
	/**
	 * @deprecated since version 1.0
	 */
    function liking() {
//        $page['users'] = $this->user_model->get_all_users();
        $page["users"] = $this->user_model->get_all_users();
        $page['videos'] = $this->video_model->all_videos();
		$page['name'] = '';
		$page['country'] = '';
		$page['category'] = '';
		$page['gender'] = '';
		$page['country_list'] = array_merge(array(
			"United Kingdom" => "United Kingdom",
			"United States" => "United States"),
			$this->video_model->get_pair_values(
				$this->video_model->get_all_countries(),
				'country'
		));
		$page['category_options'] = $this->video_model->get_pair_values(
			$this->video_model->get_all_categories(),
			'category',
			'display_category'
		);
		if ($this->input->post("selected-action")) {

			redirect("videos/liking");
		}
        $page['page_name'] = 'liking';
        $page['title'] = "Bulk action > Liking Videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 * @param string|int $user User Admin for select your channel.
	 * @param string $category Categories of video
	 */
    function grabbing($user = "all", $category = "all") {
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/grabbing/{$user}/{$category}";

		if ($user != "all" && $category == "all") {
			$page['videos'] = $this->video_model->all_videos(array($user), NULL, $start);
		} else if ($user != "all" && $category != "all") {
			$page['videos'] = $this->video_model->all_videos(array($user), $category, $start);
		} else if ($user == "all" && $category != "all") {
			$page['videos'] = $this->video_model->all_videos(NULL, $category, $start);
		} else {
			$page['videos'] = $this->video_model->all_videos(NULL, NULL, $start);
		}

		$page["users"] = $this->user_model->get_all_users();
		$page["user"] = $user;
		$page["category"] = $category;
		$page["categories"] = array_merge(
			array("" => "-- Select --"),
			$this->video_model->get_pair_values(
				$this->video_model->get_all_categories(),
				'category',
				'display_category'
			)
		);
        $page['page_name'] = 'grabbing';
        $page['msg'] = "";
        $page['title'] = "Grabbing videos from various channels (select the videos)";

		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$opcions['uri_segment'] = 5;

		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();

        $this->load->view('admin/index', $page);
    }

    function s1_grabbing() {
        $page["video_ids"] = $this->input->post('ids');
        $page["users"] = $this->user_model->get_users_channel();
        $page['page_name'] = 'gr_users';
        $page['msg'] = $this->lang->line('form_msg');
        $page['play_title'] = "";
        $page['play_description'] = "";
        $page['title'] = "Distributing across multiple channels (select the channels)";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function s2_grabbing() {

        $video_ids = explode("###", $this->input->post('video_ids'));
        $users_ids = $this->input->post('users_ids');
        $play_title = $this->input->post('play_title');
        $play_description = $this->input->post('play_description');

        $page['msg'] = $this->lang->line('form_msg');
        $rules = $this->config->item('rule_for_title');
        if (isset($_POST['submit'])) {

            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == FALSE) {
                $page['msg'] = $this->lang->line('form_error');
                $page["users"] = $this->user_model->get_users_channel();
                $page['page_name'] = 'gr_users';
                $page['title'] = "Distributing across multiple channels (select the channels)";
                $page['play_title'] = $play_title;
                $page['play_description'] = $play_description;
                $page['video_ids'] = $video_ids;
                $this->load->view('admin/index', $page);
            } else {
                for ($i = 0; $i < sizeof($users_ids); $i++) {

                    $yt = $this->user_model->getHttpClient($users_ids[$i]);
                    $yt->setMajorProtocolVersion(2);
                    $newPlaylist = $yt->newPlaylistListEntry();

                    $newPlaylist->title = $yt->newTitle()->setText($play_title);
                    $newPlaylist->summary = $yt->newDescription()->setText($play_description);
                    // post the new playlist
                    $postLocation = 'http://gdata.youtube.com/feeds/api/users/default/playlists';
                    try {

                        $playlist = $yt->insertEntry($newPlaylist, $postLocation, 'Zend_Gdata_YouTube_PlaylistListEntry');
                        $playlistId = $playlist->getPlaylistID();
                        $postUrl = "http://gdata.youtube.com/feeds/api/playlists/$playlistId";

                        for ($j = 0; $j < sizeof($video_ids); $j++) {
                            $videoEntryToAdd = $yt->getVideoEntry($video_ids[$j]);
                            $newPlaylistListEntry = $yt->newPlaylistListEntry($videoEntryToAdd->getDOM());
                            try {
                                $yt->insertEntry($newPlaylistListEntry, $postUrl);
                            } catch (Zend_App_Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                    } catch (Zend_Gdata_App_Exception $e) {
                        echo $e->getMessage();
                    }
                }
                $page['videos'] = $this->video_model->all_videos();
                $page['msg'] = 'Grabbing success';
                $page['page_name'] = 'grabbing';
                $page['title'] = "Grabbing videos from various channels (select the videos)";
                $this->load->view('admin/index', $page);
            }
        }
    }

    function favorites($user = "all", $category = "all") {
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/favorites/{$user}/{$category}";

        // optionally specify version 2 to retrieve a v2 feed
        if ($user != "all" && $category == "all") {
			$page['videos'] = $this->video_model->all_videos(array($user), NULL, $start);
		} else if ($user != "all" && $category != "all") {
			$page['videos'] = $this->video_model->all_videos(array($user), $category, $start);
		} else if ($user == "all" && $category != "all") {
			$page['videos'] = $this->video_model->all_videos(NULL, $category, $start);
		} else {
			$page['videos'] = $this->video_model->all_videos(NULL, NULL, $start);
		}
		$page["users"] = $this->user_model->get_all_users();
		$page["user"] = $user;
		$page["category"] = $category;
		$page["categories"] = $this->video_model->get_current_categories();
		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$opcions['uri_segment'] = 5;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'favorites';
        $page['title'] = "Favoriting Videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 *
	 * @param int $user
	 * @param int|string $categoryId
	 */
    function sharing($user_id, $categoryId = "all") {
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/sharing/{$user_id}/{$categoryId}";

        // optionally specify version 2 to retrieve a v2 feed
//        if ($user != "all" && $category == "all") {
//			$page['videos'] = $this->video_model->all_videos(array($user), NULL, $start);
//		} else if ($user != "all" && $category != "all") {
//			$page['videos'] = $this->video_model->all_videos(array($user), $category, $start);
//		} else if ($user == "all" && $category != "all") {
//			$page['videos'] = $this->video_model->all_videos(NULL, $category, $start);
//		} else {
//			$page['videos'] = $this->video_model->all_videos(NULL, NULL, $start);
//		}
		$page['videos'] = $this->video_model->get_videos_by_user($user_id, $categoryId == "all" ? NULL : $categoryId, $start);
		$page["users"] = $this->user_model->get_all_users();
		$page["user"] = $user_id;
		$page["category"] = $categoryId;
		$page["categories"] = $this->user_model->get_youtube_categories();
		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$opcions['uri_segment'] = 5;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'sharing';
        $page['title'] = "Sharing Videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 * Share video
	 */
    function s1_sharing() {
        $page["video_ids"] = $this->input->post('ids');
		$user_id = $this->input->post("user_id");
		foreach ($page["video_ids"] as $item) {
			$this->video_model->share($item, "", $user_id);
		}
		$msg = "Bulk sharing is done!";
		redirect("video/sharing". (isset($msg) ? "?success=true&msg=" . $msg . "&type=success": ""));
    }

    function s1_favorites() {
        $page["video_ids"] = $this->input->post('ids');
        $page["users"] = $this->user_model->get_users_channel();
        $page['page_name'] = 'favorites_users';
        $page['title'] = "Favoriting Videos (select the channels)";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function s2_favorites() {

        $video_ids = explode("###", $this->input->post('video_ids'));
        $users_ids = $this->input->post('users_ids');

        for ($i = 0; $i < sizeof($users_ids); $i++) {

            list($user_id, $channel) = explode("###", $users_ids[$i]);
            $yt = $this->user_model->getHttpClient($user_id);
            $yt->setMajorProtocolVersion(2);

            $favoritesFeed = $yt->getUserFavorites($channel);

            for ($j = 0; $j < sizeof($video_ids); $j++) {
                $newFavoriteVideoEntry = $yt->getVideoEntry($video_ids[$j]);
                try {
                    $yt->insertEntry($newFavoriteVideoEntry, $favoritesFeed->getSelfLink()->href);
                } catch (Zend_App_Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        redirect("video/favorites");
    }

    function commenting() {
        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = $this->lang->line('form_msg');
        $page['page_name'] = 'commenting';
        $page['video_id'] = '';
        $page['comment'] = '';
        $page['title'] = "Single comment";
        $this->load->view('admin/index', $page);
    }

    function sync() {
        $page['users'] = $this->user_model->get_users_channel();
        $page['page_name'] = 'sync';
        $page['title'] = "User synchronization channel";
        $this->load->view('admin/index', $page);
    }

    function synchronize() {
        $users_ids = $this->input->post('ids');
        for ($i = 0; $i < sizeof($users_ids); $i++) {
            $this->video_model->sync_videos($users_ids[$i]);
        }
        redirect("video/sync");
    }
	/**
	 * @deprecated since version 1.0
	 */
    function actions() {
        $user_id = $this->input->post('user_id');
        $playlistId = $this->input->post('playlist_id');
        $channel = $this->input->post('channel;');
        $video_ids = $this->input->post('ids');
        $playlist_opt = $this->input->post('playlist_opt');

        switch ($playlist_opt) {
            case "remove_videos":
                $this->removeVideos($playlistId, $video_ids, $user_id, $channel);
                break;
        }
    }
	/**
	 * @deprecated since version 1.0
	 * @param type $playlistId
	 * @param type $video_ids
	 * @param type $user_id
	 * @param type $channel
	 */
    function removeVideos($playlistId, $video_ids, $user_id, $channel) {
        for ($i = 0; $i < sizeof($video_ids); $i++) {
            $this->delvideo2($playlistId, $video_ids[$i], $user_id, $channel);
        }
//        delvideo($videoFeedID, $videoId, $user_id, $channel);
        $this->videolist($playlistId, $user_id, $channel);
    }
	/**
	 * @deprecated since version 1.0
	 */
	function userActions() {
        $video_id = $this->input->post('video_id');
        $users_ids = $this->input->post('ids');

        for ($i = 0; $i < sizeof($users_ids); $i++) {
            $this->like2($video_id, $users_ids[$i]);
        }
        redirect("video/liking");
    }
	/**
	 * @deprecated since version 1.0
	 */
    function videoActions() {
        $user_id = $this->input->post('user_id');
        $video_opt = $this->input->post('video_opt');
        $video_ids = $this->input->post('videos_ids');
        $owner = $this->input->post('owner');
        switch ($video_opt) {
            case "liking_videos";
                $this->likingVideos($user_id, $video_ids, $owner);
                break;
            default:
                break;
        }
    }
	/**
	 * @deprecated since version 1.0
	 * @param type $user_id
	 * @param type $video_ids
	 * @param type $owner
	 */
    function likingVideos($user_id, $video_ids, $owner) {
        for ($i = 0; $i < sizeof($video_ids); $i++) {
//            echo "$video_ids[$i]<br>";
            $this->like2($video_ids[$i], $user_id, $owner);
        }
//        $profile = $this->user_model->getUserProfile($owner);
//        $channel = $profile["username"];
//        $page['videos'] = $this->video_model->getUserUploads($channel);
//        $page['users'] = $this->user_model->get_all_users();
//        $page['msg'] = "Bulk liking success";
//        $page['page_name'] = 'videos';
//        $page['title'] = "Videos (Channel: $channel)";
//        $page['channel'] = $channel;
//        $page['owner'] = $owner;
//        $this->load->view('admin/index', $page);
		redirect("video/videos/{$owner}");
    }
	/**
	 * @deprecated since version 1.0
	 *
	 * @param string $video_id
	 * @param int $user_id
	 * @param int $owner
	 */
    function like2($video_id, $user_id, $owner) {
        $profile = $this->user_model->getUserProfile($owner);
        $channel = $profile['username'];
        $title = $profile['title'];

        $user_profile = $this->user_model->getUserProfile($user_id);
        $u_channel = $user_profile['username'];
        $u_title = $user_profile['title'];

        $yt = $this->user_model->getHttpClient($user_id);
        $videoEntryToRate = $yt->getVideoEntry($video_id);
        $videoEntryToRate->setVideoRating(5);
        $ratingUrl = $videoEntryToRate->getVideoRatingsLink()->getHref();
        try {
            $ratedVideoEntry = $yt->insertEntry($videoEntryToRate, $ratingUrl, 'Zend_Gdata_YouTube_VideoEntry');
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
                    "channel" => $channel,
                    "title" => $video_title
                );
                $v_id = $this->video_model->insert_video($data);
            }

            if ($video) {
                $dbdata = array(
                    "registered_date" => date("Y-m-d H:i:s"),
                    "admin_id" => $this->session->userdata('user_id'),
                    "video_id" => $v_id,
                    "video_likes" => $likes,
                    "video_views" => $views,
                    "task_id" => 3,
                    "who" => $u_channel . " ($u_title)"
                );
            }
            $this->video_model->insert_history($dbdata);
        } catch (Zend_Gdata_App_HttpException $httpException) {
            echo $httpException->getRawResponseBody();
        }
    }
	/**
	 * OAuth
	 *
	 * Delete video the playlist selected by $videoFeedID
	 *
	 * @param int $user_id user of system wordpress
	 * @param string $videoFeedID Id of playlist
	 * @param string $videoId Id of youtube
	 */
    public function delvideo($user_id, $videoFeedID, $videoId) {
		$success = $this->video_model->oauth_delete_video_playlist($user_id, $videoFeedID, array(
				"video_id" => $videoId
		));
		$msg = $success ? "The video(s) was deleted success!" : "The video not was possible deleted";
		$type = $success ? "success" : "error";
		redirect("video/videolist/{$user_id}/{$videoFeedID}?success=" .
			(($success) ? "true" : "false") . "&msg=" . $msg . "&type=" . $type);
    }
	/**
	 * OAuth
	 *
	 * @param int $user_id
	 * @param string $playlistId
	 */
    function edit_playlist($user_id, $playlistId) {
		if ($this->input->post("submit")) {
			$page['msg'] = $this->lang->line('form_msg');
			$rules = $this->config->item('rule_for_title');
            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() != FALSE) {
				if ($this->video_model->saveplaylist($user_id, $playlistId, array(
					"title" => $this->input->post("play_title"),
					"description" => $this->input->post("play_description")
				))) {
					$msg = "The playlist selected is update";
					redirect("video/videolist/{$user_id}/{$playlistId}?success=true&msg=" . $msg . "&type=success");
				}
			}
		}

		if ($this->input->get("success")) {
			$page["success"] = $this->input->get("success");
			$page["message"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}

		$page["playlistEntry"] = $this->video_model->oauth_get_playlist($user_id, $playlistId);
        $page['page_name'] = 'edit_playlist';
        $page['title'] = "Edit playlist";
        $page['channel'] = $this->user_model->get_channel($user_id);
        $page['user_id'] = $user_id;
        $page['videoFeedID'] = $playlistId;
        $page['msg'] = $this->lang->line('form_msg');
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function saveplaylist() {
        $user_id = $this->input->post("user_id");
        $channel = $this->input->post('channel');
        $playlistId = $this->input->post('playlist_id');
        $playlist_title = $this->input->post('play_title');
        $playlist_description = $this->input->post('play_description');
        $yt = $this->user_model->getHttpClient($user_id);
        $yt->setMajorProtocolVersion(2);

        $page['msg'] = $this->lang->line('form_msg');
        $rules = $this->config->item('rule_for_title');

        if (isset($_POST['submit'])) {

            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() == FALSE) {

                $page['msg'] = $this->lang->line('form_error');

                $playlistListFeed = $yt->getPlaylistListFeed($channel);

                foreach ($playlistListFeed as $playlistListEntry) {
                    if ($playlistListEntry->playlistId == $playlistId) {
                        $page["playlistListEntry"] = $playlistListEntry;
                        break;
                    }
                }

                $page["playlistListEntry"] = $playlistListEntry;
                $page['page_name'] = 'edit_playlist';
                $page['title'] = "Edit playlist";
                $page['channel'] = $channel;
                $page['user_id'] = $user_id;
                $this->load->view('admin/index', $page);
            } else {
                $playlistListFeed = $yt->getPlaylistListFeed($channel);

                foreach ($playlistListFeed as $playlistToBeUpdated) {
                    if ($playlistToBeUpdated->playlistId == $playlistId) {
                        $playlistToBeUpdated->description->setText($playlist_description);
                        $playlistToBeUpdated->title->setText($playlist_title);
                        $playlistToBeUpdated->save();

                        $playlist = $this->video_model->exists_playlist($playlistId);
                        $play_id = $playlist['id'];
                        if (!$playlist) {
                            $data = array(
                                "channel" => $channel,
                                "title" => $playlist_title,
                                "playlist" => $playlistId
                            );
                            $play_id = $this->video_model->insert_playlist($data);
                        }
                        $dbdata = array(
                            "registered_date" => date("Y-m-d H:i:s"),
                            "admin_id" => $this->session->userdata('user_id'),
                            "video_id" => "",
                            "task_id" => 8,
                            "playlist_id" => $play_id,
                            "who" => $this->session->userdata('name')
                        );
                        $this->video_model->insert_history($dbdata);

                        break;
                    }
                }
                $page['playlistListFeed'] = $yt->getPlaylistListFeed($channel);
                $page['msg'] = $this->lang->line('form_edit_play_success');
                $page['page_name'] = 'playlist';
                $page['title'] = "Playlist (channel: " . $channel . ")";
                $page['user_id'] = $user_id;
                $page['channel'] = $channel;
                $this->load->view('admin/index', $page);
            }
        }
    }
	/**
	 *
	 * @param int $user_id
	 * @param string $playlistId
	 */
    function delplaylist($user_id, $playlistId) {
        if ($this->video_model->oauth_delete_playlist($user_id, $playlistId)) {
			redirect("video/playlist/{$user_id}?success=true&msg=Playlist is deleted success&type=success");
		} else {
			redirect("video/playlist/{$user_id}?success=true&msg=Error playlist not was deleted&type=error");
		}
    }
	/**
	 *	oauth
	 *
	 * @param string $video_id Youtube ID
	 * @param int $user_id User Id for wordpress system
	 */
    function like($user_id, $video_id) {
		if ($this->video_model->like($video_id, $user_id)) {
			redirect("video/videos/{$user_id}?success=true&msg=Action apply&type=success");
		} else {
			redirect("video/videos/{$user_id}?success=true&msg=Not Possible this action&type=error");
		}
//        $page['users'] = $this->user_model->get_all_users();
//        $page['msg'] = $this->lang->line('form_msg');
//        $page['video_id'] = "";
//        $page['page_name'] = 'like';
//        $page['title'] = "Single like";
//        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function likingnvideos() {

        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = $this->lang->line('form_msg');
        $page['page_name'] = 'likingnvideos';
        $page['title'] = "Liking N videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function playlistnvideos() {

        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = $this->lang->line('form_msg');
        $page['page_name'] = 'playlistnvideos';
        $page['play_title'] = '';
        $page['play_description'] = '';
        $page['title'] = "Playlist N videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function processliking() {
        $video_ids = $this->input->post('video_ids');
        $user_id = $this->input->post("user_id");

        foreach ($video_ids as $video_id) {
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
                $ratedVideoEntry = $yt->insertEntry($videoEntryToRate, $ratingUrl, 'Zend_Gdata_YouTube_VideoEntry');
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
            } catch (Zend_Gdata_App_HttpException $httpException) {
                echo $httpException->getRawResponseBody();
            }
        }
        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = "Liking N videos success...";
        $page['page_name'] = 'likingnvideos';
        $page['title'] = "Liking N videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function processplaylist() {
        $video_ids = $this->input->post('video_ids');
        $user_id = $this->input->post("user_id");
        $play_title = $this->input->post("play_title");
        $play_description = $this->input->post("play_description");

        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];

        $yt = $this->user_model->getHttpClient($user_id);
        $newPlaylist = $yt->newPlaylistListEntry();
        $newPlaylist->summary = $yt->newDescription()->setText($play_description);
        $newPlaylist->title = $yt->newTitle()->setText($play_title);
        $postLocation = 'http://gdata.youtube.com/feeds/api/users/default/playlists';
        try {

            $playlist = $yt->insertEntry($newPlaylist, $postLocation, 'Zend_Gdata_YouTube_PlaylistListEntry');
            $yt->setMajorProtocolVersion(2);
            $play = $playlist->getPlaylistID();
            $videoFeedID = $play;

            $data = array(
                "channel" => $channel,
                "title" => $play_title,
                "playlist" => "$play"
            );
            $play_id = $this->video_model->insert_playlist($data);

            $dbdata = array(
                "registered_date" => date("Y-m-d H:i:s"),
                "admin_id" => $this->session->userdata('user_id'),
                "video_id" => "",
                "task_id" => 7,
                "playlist_id" => $play_id,
                "who" => $this->session->userdata('name')
            );
            $this->video_model->insert_history($dbdata);
        } catch (Zend_Gdata_App_Exception $e) {
            echo $e->getMessage();
        }

        $postUrl = "http://gdata.youtube.com/feeds/api/playlists/$videoFeedID";

        foreach ($video_ids as $video_id) {
            if (!empty($video_id))
                $video_id = trim($video_id);
            if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
                $aux = explode("=", $video_id);
                $video_id = $aux[1];
            }
            // video entry to be added
            $videoEntryToAdd = $yt->getVideoEntry($video_id);
            $newPlaylistListEntry = $yt->newPlaylistListEntry($videoEntryToAdd->getDOM());
            try {
                $yt->insertEntry($newPlaylistListEntry, $postUrl);
            } catch (Zend_App_Exception $e) {
                echo $e->getMessage();
            }
        }


        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = 'Playlist n videos success';
        $page['page_name'] = 'playlistnvideos';
        $page['play_title'] = '';
        $page['play_description'] = '';
        $page['title'] = "Playlist N videos";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function apply_like() {

        $rule = $this->config->item('video_id_rule');
        $page['msg'] = $this->lang->line('form_msg');
        $page['users'] = $this->user_model->get_all_users();
        $video_id = $this->input->post('video_id');
        $page['video_id'] = trim($video_id);

        if (isset($_POST['submit'])) {

            if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
                $aux = explode("=", $video_id);
                $video_id = $aux[1];
            }

            $user_id = $this->input->post('user_id');
            $this->form_validation->set_rules($rule); //check with the rules


            if ($this->form_validation->run() == FALSE) {
                $page['video_id'] = $this->input->post('video_id');
                $page['msg'] = $this->lang->line('form_error');
                $page['page_name'] = 'like';
                $page['title'] = "Single like";
                $this->load->view('admin/index', $page);
            } else {
                $yt = $this->user_model->getHttpClient($user_id);
                $yt->setMajorProtocolVersion(2);
                $videoEntryToRate = $yt->getVideoEntry($video_id);
                $videoEntryToRate->setVideoRating(5);
                $ratingUrl = $videoEntryToRate->getVideoRatingsLink()->getHref();
                $profile = $this->user_model->getUserProfile($user_id);
                $channel = $profile["username"];
                $title = $profile["title"];
                try {
                    $ratedVideoEntry = $yt->insertEntry($videoEntryToRate, $ratingUrl, 'Zend_Gdata_YouTube_VideoEntry');
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
                } catch (Zend_Gdata_App_HttpException $httpException) {
                    echo $httpException->getRawResponseBody();
                }
                $page['msg'] = $this->lang->line('form_like_success');
                $page['page_name'] = 'like';
                $page['title'] = "Single like";
                $this->load->view('admin/index', $page);
            }
        } else {
            $page['page_name'] = 'like';
            $page['title'] = "Single like";
            $this->load->view('admin/index', $page);
        }
    }
	/**
	 * @deprecated since version 1.0
	 *
	 * @param string $video_id
	 * @param string $channel
	 */
    function like_video($video_id, $channel) {
        $yt = new Zend_Gdata_YouTube();
//        $videoEntry = $yt->getVideoEntry($video_id);
        $page['videoEntry'] = $yt->getVideoEntry($video_id);
        $page['users'] = $this->user_model->get_all_users();
        $page['page_name'] = 'like_video';
        $page['title'] = "Like Video";
        $page['channel'] = $channel;
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 *
	 */
    function newplay() {
        $user_id = $this->input->post('user_id');
        $channel = $this->input->post('channel');
        $play_title = "";
        $play_description = "";

        $page['msg'] = $this->lang->line('form_msg');
        $rules = $this->config->item('rule_for_title');

        $yt = $this->user_model->getHttpClient($user_id);

        if (isset($_POST['submit'])) {

            $play_title = $this->input->post('play_title');
            $play_description = $this->input->post('play_description');
            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() == FALSE) {
                $page['page_name'] = 'new_playlist';
                $page['msg'] = $this->lang->line('form_error');
                $page['title'] = "Add Video to Playlist";
                $page['user_id'] = $user_id;
                $page['channel'] = $channel;
                $page['play_title'] = $play_title;
                $page['play_description'] = $play_description;
                $this->load->view('admin/index', $page);
            } else {

                $newPlaylist = $yt->newPlaylistListEntry();
                $newPlaylist->summary = $yt->newDescription()->setText($play_description);
                $newPlaylist->title = $yt->newTitle()->setText($play_title);
                // post the new playlist
                $postLocation = 'http://gdata.youtube.com/feeds/api/users/default/playlists';
                try {
//                    $playlist = $yt->insertEntry($newPlaylist, $postLocation);
                    $playlist = $yt->insertEntry($newPlaylist, $postLocation, 'Zend_Gdata_YouTube_PlaylistListEntry');
                    $yt->setMajorProtocolVersion(2);
                    $play = $playlist->getPlaylistID();
                    $data = array(
                        "channel" => $channel,
                        "title" => $play_title,
                        "playlist" => "$play"
                    );
                    $play_id = $this->video_model->insert_playlist($data);

                    $dbdata = array(
                        "registered_date" => date("Y-m-d H:i:s"),
                        "admin_id" => $this->session->userdata('user_id'),
                        "video_id" => "",
                        "task_id" => 7,
                        "playlist_id" => $play_id,
                        "who" => $this->session->userdata('name')
                    );
                    $this->video_model->insert_history($dbdata);
                } catch (Zend_Gdata_App_Exception $e) {
                    echo $e->getMessage();
                }
                $playlistListFeed = $yt->getPlaylistListFeed($channel);
                $page['playlistListFeed'] = $yt->getPlaylistListFeed($channel);
                $page['msg'] = $this->lang->line('form_new_play_success');
                $page['page_name'] = 'playlist';
                $page['title'] = "Playlist (channel: " . $channel . ")";
                $page['user_id'] = $user_id;
                $page['channel'] = $channel;
                $this->load->view('admin/index', $page);
            }
        }
    }
	/**
	 * OAuth
	 *
	 * Controller Add new playlist
	 *
	 * @param int $user_id ID of user for wordpress system.
	 * @param string $channel User name of youtube channel.
	 */
    public function new_playlist($user_id, $channel = "") {
		$page['msg'] = $this->lang->line('form_msg');
		if ($this->input->post("submit")) {
			$page['msg'] = $this->lang->line('form_msg');
			$rules = $this->config->item('rule_for_title');
            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() != FALSE) {

				if ( ! $this->video_model->oauth_insert_playlist($user_id, $channel, array(
					"play_title" => $this->input->post('play_title'),
					"play_description" => $this->input->post('play_description')
				))) {
					$page['msg'] = $this->lang->line("error_playlist");
				} else {
					redirect("video/playlist/" . $user_id . "?success=true&msg=Playlist added success&type=success");
					return;
				}
			}
		}

        $page['page_name'] = 'new_playlist';
        $page['title'] = $this->lang->line("title_new_playlist");
        $page['user_id'] = $user_id;
        $page['channel'] = $channel;
        $page['play_title'] = "";
        $page['play_description'] = "";
        $this->load->view('admin/index', $page);
    }
	/**
	 * OAuth
	 * This function redirect to add_video_playlist controller
	 *
	 * @param int $user_id Id user for wordpress system
	 */
	public function add_video($user_id, $playlistId) {
		redirect("video/add_video_playlist/{$user_id}/{$playlistId}");
	}
	/**
	 * Oauth
	 *
	 * This function capture the post request for form Add Video.
	 *
	 * @param int $user_id ID of user for wordpress system
	 * @param string $playlistId ID of playlist from Youtube API
	 */
	public function add_video_playlist($user_id, $playlistId) {
		if ($this->input->post("submit")) {
			$page['msg'] = $this->lang->line('form_msg');
			$video_user = $this->input->post("videos_user") ? $this->input->post("videos_user") : array();
			$video_ids = array_unique(array_merge($this->input->post("video_ids"), $video_user));
			foreach ($video_ids as $video_id) {
				if ($video_id == "") continue;
				$this->video_model->oauth_insert_video_playlist($user_id, $playlistId, array(
					"videoId" => $video_id
				));
			}
			redirect("video/videolist/{$user_id}/{$playlistId}?success=true&msg=Videos added successful&type=success");
			return;
		}
        $page['page_name'] = 'add_video';
        $page['msg'] = $this->lang->line('form_msg');
        $page['videoFeedID'] = $playlistId;
        $page['user_id'] = $user_id;
        $page['channel'] = $this->user_model->get_channel($user_id);
        $page['title'] = $this->lang->line("title_add_video_playlist");
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 *
	 * @param type $user_id
	 * @param type $playlistId
	 */
    function add_video2($user_id, $playlistId) {
        $yt = $this->user_model->getHttpClient($user_id);
        $profile = $this->user_model->getUserProfile($user_id);
        $page['page_name'] = 'add_video';
        $page['msg'] = $this->lang->line('form_msg');
        $page['videoFeedID'] = $playlistId;
        $page['user_id'] = $user_id;
        $page['channel'] = $profile['username'];
        $page['title'] = "Add Video to Playlist";
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 */
    function addvideo() {
        $videoFeedID = $this->input->post("videoFeedID");
        $user_id = $this->input->post("user_id");
        $videoId = $this->input->post("video_id");
        $channel = $this->input->post("channel");

        $page['msg'] = $this->lang->line('form_msg');
        $rules = $this->config->item('video_id_rule');

        if (isset($_POST['submit'])) {
            $this->form_validation->set_rules($rules);
            $yt = $this->user_model->getHttpClient($user_id);

            if ($this->form_validation->run() == FALSE) {
                $page['page_name'] = 'add_video';
                $page['msg'] = $this->lang->line('form_error');
                $page['videoFeedID'] = $videoFeedID;
                $page['user_id'] = $user_id;
                $page['channel'] = $channel;
                $page['title'] = "Add Video to Playlist";
                $this->load->view('admin/index', $page);
            } else {

                $postUrl = "http://gdata.youtube.com/feeds/api/playlists/$videoFeedID";
                // video entry to be added
                $videoEntryToAdd = $yt->getVideoEntry($videoId);
                $newPlaylistListEntry = $yt->newPlaylistListEntry($videoEntryToAdd->getDOM());
                try {
                    $yt->insertEntry($newPlaylistListEntry, $postUrl);
                } catch (Zend_App_Exception $e) {
                    echo $e->getMessage();
                }
                $pl_title = $this->getPlaylistTitle($videoFeedID, $channel, $user_id);
//                $feedUrl = "http://gdata.youtube.com/feeds/api/playlists/$playlistId";
                $pl_title = str_replace("%20", " ", $pl_title);
                $page['playlistVideoFeed'] = $yt->getPlaylistVideoFeed($postUrl);
                $page['page_name'] = 'videolist';
                $page['title'] = "Video list ($pl_title)";
                $page['videoFeedID'] = $videoFeedID;
                $page['user_id'] = $user_id;
                $page['channel'] = $channel;
                $page['msg'] = $this->lang->line('form_add_video_play_success');
                $this->load->view('admin/index', $page);
            }
        }
    }
	/**
	 * OAuth
	 *
	 * This function get and show the playlist for user id selected
	 *
	 * @param type $user_id
	 */
    function playlist($user_id) {
		if ($this->input->get("success")) {
			$page["success"] = TRUE;
			$page["msg"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
        $page['playlistListFeed'] = $this->video_model->oauth_get_playlists($user_id);
        $page['page_name'] = 'playlist';
        $page['title'] = "Playlist (channel: {$channel})";
        $page['user_id'] = $user_id;
        $page['channel'] = $channel;
        $this->load->view('admin/index', $page);
    }
	/**
	 * Oauth
	 *
	 * @param type $user_id
	 * @param type $playlistId
	 */
	public function videolist($user_id, $playlistId) {
		if ($this->input->get("success")) {
			$page["success"] = TRUE;
			$page["message"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
        $page['playlistVideoFeed'] = $this->video_model->get_videos_by_playlist($user_id, $playlistId);
        $page['page_name'] = 'videolist';
		$plalistDetail = $this->video_model->get_playlistDetail($user_id, $playlistId);
        $page['title'] = "Video list ({$plalistDetail["title"]})";
        $page['videoFeedID'] = $playlistId;
        $page['user_id'] = $user_id;
        $page['channel'] = $channel;
        $this->load->view('admin/index', $page);
    }
	/**
	 * @deprecated since version 1.0
	 *
	 * @param type $playlistId
	 * @param type $channel
	 * @param type $user_id
	 * @return string
	 */
    function getPlaylistTitle($playlistId, $channel, $user_id) {
        $yt = $this->user_model->getHttpClient($user_id);
        $yt->setMajorProtocolVersion(2);
        $playlistListFeed = $yt->getPlaylistListFeed($channel);
        foreach ($playlistListFeed as $playlistListEntry) {
            if ($playlistListEntry->playlistId == $playlistId) {
                return $playlistListEntry->title->text;
            }
        }
        return "";
    }
	/**
	 * OAuth
	 *
	 * @param type $video_id Youtube ID Video
	 * @param type $user_id User Id for wordpress installation.
	 */
	public function edit_video($video_id, $user_id) {
		$config['upload_path'] = $this->config->item("upload_path");;
		$config['allowed_types'] = $this->config->item("allowed_types");
		$config['max_size']	= $this->config->item("max_size");
		$config['max_width'] = $this->config->item("max_width");
		$config['max_height'] = $this->config->item("max_height");

		$this->load->library('upload', $config);

		$this->upload->initialize($config);
		if ($this->input->post("submit")) {

			$file = $this->video_model->set_thumbnails($video_id, $user_id);

			if ($this->video_model->edit_video($video_id, $user_id, array(
				"video_id" => $this->input->post('video_id'),
				"video_title" => $this->input->post('video_title'),
				"video_description" => $this->input->post('video_description'),
				"category_id" => $this->input->post('category_id'),
				"video_tags" => explode(",", $this->input->post('video_tags')),
				"url" => $file
			))) {
				$this->video_model->set_history($video_id, $user_id, array(
					"channel" => $this->user_model->get_channel($user_id),
					"task_id" => 1
				));
				$page["success"] = TRUE;
				$page["message"] = "The video was modified successfully!";
				$page["type"] = "success";
			} else {
				$page["success"] = TRUE;
				$page["message"] = "The video could not be edited! try Again.";
				$page["type"] = "error";
			}
		}
        $page['videoEntry'] = $this->video_model->get_video($video_id, $user_id);
        $page['page_name'] = 'edit_video';
		$page["category_options"] = $this->user_model->get_youtube_categories();
		$page["selected"] = $page['videoEntry']['categoryId'];
        $page['msg'] = $this->lang->line('form_msg');
        $page['title'] = "Video edit";
        $page['user_id'] = $user_id;
        $page['video_id'] = $video_id;
        $page['post_form'] = "edit_video";
        $this->load->view('admin/index', $page);
	}
	/**
	 * Oauht
	 *
	 * @param string $video_id Youtube id
	 * @param int $user_id user Id of wordpress installation
	 */
	public function delete_video($video_id, $user_id) {
		if ($this->video_model->delete_video($video_id, $user_id)) {
			redirect("video/videos/{$user_id}?success=true&msg=Video delete success!&type=success");
		} else {
			redirect("video/videos/{$user_id}?success=false&msg=The Video not was delete&type=error");
		}
	}
	/**
	 * Oauth
	 *
	 * Ajax request
	 *
	 * This controller save the data of video bulk
	 */
	public function ajax_edit() {
		header('Content-type: application/json; charset=utf-8');
		$video_id = $this->input->post("video_id");
		$value = $this->input->post("value");
		$field = $this->input->post("field");
		$user_id = $this->input->post("user_id");
		$channel = $this->input->post("channel");

		$video = $this->video_model->get_video($video_id, $user_id);
		if ($this->video_model->edit_video($video_id, $user_id, array(
			"video_id" => $video_id,
			"video_title" => $field == "title" ? $value : $video["title"],
			"video_description" => $field == "description" ? $value : $video["description"],
			"category_id" => $field == "category" ? $value : $video["categoryId"],
			"video_tags" => $video["tags"],
			"url" => $video["thumbnail"]["url"]
		))) {

			$this->video_model->set_history($video_id, $user_id, array(
				"channel" => $this->user_model->get_channel($user_id),
				"task_id" => 1
			));
		}
		$this->load->helper("views_helper");
		die(json_encode(array("video_id" => $video_id, "user_id" => $user_id, "value" => $value, "field" => $field, "channel" => $channel, "excerpt" => get_excerpt($field == "description" ? $value : $video["description"]))));
	}
	/**
	 * CONTROLLERS
	 *
	 * @deprecated since version 1.0
	 *
	 * This function manage the action for show list of videos availables
	 */
    function edit() {
        $data["onwer"] = $user_id = $this->input->post('user_id');
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
        $subs = $profile['subs'];
        $video_id = $this->input->post('video_id');
        $video_title = $this->input->post('video_title');
        $video_description = $this->input->post('video_description');
        $category_name = $this->input->post('category_name');
        $video_tags = $this->input->post('video_tags');
        $videoThumbnailKey = $this->input->post('videoThumbnailKey');

        $yt = $this->user_model->getHttpClient($user_id);
        $rules = $this->config->item('video_edit_rules');
        $page['msg'] = $this->lang->line('form_msg');

        if (isset($_POST['submit'])) {
            $videoFeed = $yt->getVideoFeed("http://gdata.youtube.com/feeds/api/users/$channel/uploads");
            foreach ($videoFeed as $videoEntry) {
                if ($videoEntry->getEditLink() !== null) {

                    if ($videoEntry->getVideoId() == $video_id) {

                        $this->form_validation->set_rules($rules); //check with the rules
                        if ($this->form_validation->run() == FALSE) {
                            //validation failed
                            $page['videoEntry'] = $videoEntry;
                            $page['page_name'] = 'edit_video';
                            $page['title'] = "Video edit";
                            $page['user_id'] = $user_id;
							$page["category_options"] = $this->user_model->get_categories_for_select();
							$page["videoThumbnailKey"] = $this->video_model->get_video_thumbnail_key($video_id);
                            return $this->load->view('admin/index', $page);
                        } else {
                            $putUrl = $videoEntry->getEditLink()->getHref();
                            $videoEntry->setVideoTitle($video_title);
                            $videoEntry->setVideoDescription($video_description);
                            $videoEntry->setVideoTags($video_tags);
                            $videoEntry->setVideoCategory($category_name);
                            $yt->updateEntry($videoEntry, $putUrl);

                            $views = $videoEntry->getVideoViewCount();
                            $rating = $videoEntry->getVideoRatingInfo();
                            $likes = $rating['numRaters'];

                            $video = $this->video_model->exists_video($video_id);
                            $v_id = $video["id"];
                            if (!$video) {
                                $data = array(
                                    "youtube_id" => $video_id,
                                    "channel" => $channel,
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
                                "channel_subs" => $subs,
                                "task_id" => 1,
                                "who" => $this->session->userdata('name')
                            );
                            $this->video_model->insert_history($dbdata);

							$this->video_model->db_update_video(array(
								"title" => $video_title,
								"video_thumbnail_key" => $videoThumbnailKey
							), $video_id);
                        }
						break;
                    }
                } else {

                    $yt = $this->user_model->getHttpClient($user_id);
                    $page['videoEntry'] = $yt->getVideoEntry($video_id);
                    $page['page_name'] = 'edit_video';
					$page["category_options"] = $this->user_model->get_categories_for_select();
                    $page['msg'] = 'Video is not editable by current user';
                    $page['title'] = "Video edit";
                    $page['user_id'] = $user_id;
                    return $this->load->view('admin/index', $page);
                }
            }
//			$this->db = $this->load->database('default', TRUE);
//            $profile = $this->user_model->getUserProfile($user_id);
//            $channel = $profile['username'];
//            $page['videos'] = $this->video_model->getUserUploads($channel);
//            $page['users'] = $this->user_model->get_all_users();
//            $page['msg'] = $this->lang->line('form_video_edit_success');
//            $page['page_name'] = 'videos';
//            $page['title'] = "Videos (Channel: $channel)";
//            $page['channel'] = $channel;
//            $page["owner"] = $page['user_id'] = $user_id;
//			$page["video_model"] = $this->video_model;
//            return $this->load->view('admin/index', $page);
			redirect("video/videos/{$user_id}");
        }
    }
	/**
	 * OAuth
	 *
	 * If the user enable your token auth, this task is possible.
	 *
	 * @param int $user_id
	 */
    function upload($user_id) {
		$res = "";
		$path_video = FALSE;
		$load_video = TRUE;
		if ($this->input->post("submit")) {
			if ( ! ($path_video = $this->video_model->load_video("video_file"))) {
				$page["success"] = TRUE;
				$page["message"] = "Video Not was upload to server! Types allowed: mp4";
				$page["type"] = "error";
			}
			$user_id = $this->input->post('user_id') ? $this->input->post("user_id") : $user_id;
			if ($path_video  && ($load_video = $this->video_model->upload_video($user_id, array(
				"video_title" => $this->input->post("video_title"),
				"video_description" => $this->input->post("video_description"),
				"video_category" => $this->input->post("video_category"),
				"video_tags" => $this->input->post("video_tags"),
				"video_path" => $path_video
			)))) {
				$page["success"] = TRUE;
				$page["message"] = "Video upload success!";
				$page["type"] = "success";
			} else if ( ! $load_video) {
				$page["success"] = TRUE;
				$page["message"] = "Video Not was upload to Youtube!";
				$page["type"] = "error";
			}
		}

		if ($this->input->get("success")) {
			$page["success"] = TRUE;
			$page["message"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}

    	$page['msg'] = $res;
    	$page['video_title'] = "";
    	$page['video_description'] = "";
    	$page['video_tags'] = "";
    	$page['video_file'] = "";
    	$page['video_category'] = "";
    	$page['page_name'] = 'upload_video';
    	$page['title'] = "Upload a new video";
    	$page['user_id'] = $user_id;
    	$this->load->view('admin/index', $page);
    }
	/**
	 * Controller
	 *
	 * Home redirect.
	 */
    function index() {
        redirect($this->config->item("home"));
    }
	/**
	 * Controller
	 *
	 * Search videos by keyword defined.
	 */
	public function result() {
		$videos = array();
		$q = $this->input->get('q') ? $this->input->get('q') : "";
		$max = $this->input->get('maxResults') ? $this->input->get('maxResults') : "";
		if ($q != "" && $max != "") {
			$request = $this->video_model->get_videos($q, $max);

			$videos = $request["videos"];
		}

		$page["videos"] = $videos;
		$page['page_name'] = 'videos';
		$page['title'] = "Search Videos";
		$this->load->view('admin/index', $page);
	}
	/**
	 * Controller
	 *
	 * Show videos by user_id selected.
	 *
	 * @param int $user_id
	 * @param int $category
	 */
	public function videos($user_id, $category = "all") {
		if ($this->input->get("success")) {
			$page["success"] = TRUE;
			$page["message"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/videos/{$user_id}/{$category}";

		$page['videos'] = $this->video_model->get_videos_by_user($user_id, NULL, $start);
		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$channel = $this->user_model->get_channel($user_id);
		$opcions['uri_segment'] = 5;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = "";
        $page['page_name'] = 'videos';
        $page['title'] = "Videos (Channel: $channel)";
        $page['channel'] = $channel;
        $page['owner'] = $user_id;
		$page['selected'] = ($this->input->post('user_id')) ? $this->input->post('user_id') : '';
		$page['tasks_options'] = array(
			"liking_videos" => "Liking videos"
		);
		$page['selected2'] = ($this->input->post('video_opt')) ? $this->input->post('video_opt') : '';
		$page["video_model"] = $this->video_model;
        $this->load->view('admin/index', $page);
	}
	/**
	 *
	 */
    function comment() {
        $rules = $this->config->item('video_id_and_comment');
        $page['msg'] = $this->lang->line('form_msg');
        $page['users'] = $this->user_model->get_all_users();
        $user_id = $this->input->post('user_id');
        $video_id = $this->input->post('video_id');
        $comment = $this->input->post('comment');
        $page['comment'] = $comment;
        $page['video_id'] = trim($video_id);

        if (isset($_POST['submit'])) {
            if (strlen($video_id) > 11 && strpos($video_id, "=") !== false) {
                $aux = explode("=", $video_id);
                $video_id = $aux[1];
            }
            $this->form_validation->set_rules($rules); //check with the rules

            if ($this->form_validation->run() == FALSE) {
                $page['video_id'] = $this->input->post('video_id');
                $page['msg'] = $this->lang->line('form_error');
                $page['page_name'] = 'commenting';
                $page['title'] = "Single comment";
                $this->load->view('admin/index', $page);
            } else {
                $yt = $this->user_model->getHttpClient($user_id);
                $profile = $this->user_model->getUserProfile($user_id);
                $channel = $profile["username"];
                $title = $profile["title"];
                $yt->setMajorProtocolVersion(2);
                $newComment = $yt->newCommentEntry();

                $newComment->content = $yt->newContent()->setText($comment);
                $videoEntry = $yt->getVideoEntry($video_id);
                $commentFeedPostUrl = $videoEntry->getVideoCommentFeedUrl();

                $updatedVideoEntry = $yt->insertEntry($newComment, $commentFeedPostUrl, 'Zend_Gdata_YouTube_CommentEntry');
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
                $page['msg'] = $this->lang->line('form_comm_success');
                $page['page_name'] = 'commenting';
                $page['title'] = "Single comment";
                $this->load->view('admin/index', $page);
            }
        } else {
            $page['msg'] = $this->lang->line('form_msg');
            $page['page_name'] = 'commenting';
            $page['title'] = "Single comment";
            $this->load->view('admin/index', $page);
        }
    }
	/**
	 *
	 * @param string $videoId Youtube Id
	 * @param int $user_id
	 */
    function view($videoId, $user_id) {
        $page['users'] = $this->user_model->get_all_users();
        $page['entry'] = $this->video_model->get_video($videoId, $user_id);
        $page['channel'] = $this->user_model->get_channel($user_id);
        $page['page_name'] = 'show_video';
        $page['title'] = "Video";
        $page['user_id'] = $user_id;
        $this->load->view('admin/index', $page);
    }
	/**
	 *
	 * @param string $video_id
	 */
    function share($user_id, $video_id) {
		if ($this->input->post("submit")) {
			$this->video_model->share($video_id, $this->input->post("message"), $user_id);
			redirect("video/videos/{$user_id}?success=true&msg=Video was sharing success&type=success");
			return;
		}
        $page['page_name'] = 'message';
        $page['msg'] = '';
        $page['message'] = '';
        $page['title'] = "Sharing video";
        $page['video_id'] = $video_id;
        $page['user_id'] = $user_id;

        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLERS
	 *
	 * @deprecated since version 1.0
	 *
	 * Set a share video of request post
	 */
    function share_video() {
        $settings = $this->video_model->get_yt_settings();
        $video_id = $this->input->post('video_id');
        $message = $this->input->post('message');
        $yt = new Zend_Gdata_YouTube();
        $videoEntry = $yt->getVideoEntry($video_id);
        $rule = $this->config->item('message_rule');
        $page['msg'] = $this->lang->line('form_msg');

        if (isset($_POST['submit'])) {

            $this->form_validation->set_rules($rule); //check with the rules

            if ($this->form_validation->run() == FALSE) {

                $page['msg'] = $this->lang->line('form_error');
                $page['message'] = $message;
                $page['page_name'] = 'message';
                $page['title'] = "Sharing video";
                $page['video_id'] = $video_id;
                $this->load->view('admin/index', $page);
            } else {
                $page['msg'] = $this->lang->line('form_msg');
                $page['message'] = $message;
                $page['videoEntry'] = $videoEntry;

                $page['page_name'] = 'share';
                $page['title'] = "Sharing video";

                $page['fdappId'] = $settings[0]->facebook_apikey;
                $page['fbsecret'] = $settings[0]->facebook_secret;
                $page['fbaccessToken'] = $settings[0]->facebook_accesstoken;
                $page['fbpageid'] = "me";

                $this->load->view('admin/index', $page);
            }
        }
    }
	/**
	 * Controller action
	 *
	 * This controller manage the action /video/bulk
	 *
	 * @param string $name
	 * @param string $youtube
	 * @param string $country
	 * @param string $category
	 * @param string $sex
	 */
	public function bulk($name = "all", $youtube = "all", $country = "all", $category = "all", $sex = "all") {
		if ($this->input->get("success")) {
			$page["success"] = TRUE;
			$page["message"] = $this->input->get("msg");
			$page["type"] = $this->input->get("type");
		}
		$search_name = ($name != "" && $name != "all" ) ? urldecode($name) : "";
		$search_youtube = ($youtube != "" && $youtube != "all") ? urldecode($youtube) : "";
		$search_country = ($country != "" && $country != "all") ? urldecode($country) : "";
		$search_category = ($category != "" && $category != "all") ? urldecode($category) : "";
		$search_sex = ($sex != "" && $sex != "all") ? urldecode($sex) : "";

		$this->load->helper('cookie');
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(8)) ? $this->uri->segment(8) : 0;
		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/bulk/{$name}/{$youtube}/{$country}/{$category}/{$sex}";

		$page["users"] = $this->user_model->get_all_users($start, $search_name, $search_youtube, $search_country, $search_category, $search_sex);
		$opcions['total_rows'] = $this->user_model->count_rows_users($search_name, $search_youtube, $search_country, $search_category, $search_sex);
		$opcions['uri_segment'] = 8;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
		// delete_cookie("hold-users");

		$temp_users = explode(",", $this->input->cookie("hold-users"));
		$this->video_model->get_temp_users_id($page["hold_users"], $page["pair_user_login"], $temp_users);

        // $page['videos'] = $this->video_model->all_videos();
		$page['name'] = $search_name;
		$page['youtube'] = $search_youtube;
		$page['country'] = $search_country;
		$page['category'] = $search_category;
		$page['gender'] = $search_sex;
		$page['country_list'] = $this->user_model->get_countries_for_select();
		$page['category_options'] = $this->user_model->get_categories_for_select();
        $page['page_name'] = 'bulk';
        $page['title'] = "Bulk Action";
        $this->load->view('admin/index', $page);
	}
	/**
	 * Controller for form action by bulk action
	 *
	 * This controller manage the task for bulk action
	 */
	public function bulkActions() {
		$this->load->helper('cookie');
		$action = $this->input->post("selected-action");
		if (!$action) {
			redirect ("video/bulk");
		}
		$msg = "";
		$type = "success"; /*can do error, info, success type*/
		$hold_users = array();
		$pair_user_login = array();
		$users_checkbox = $this->input->post("ids");
		$temp_users = explode(",", $this->input->cookie("hold-users"));
		$this->video_model->get_temp_users_id($hold_users, $pair_user_login, $temp_users);
		$video_user = $this->input->post("videos_user") ? $this->input->post("videos_user") : array();

		$users = array_unique(array_merge($users_checkbox, $hold_users));

		if ($action == "like-video") {
			$videos = array_unique(array_merge($this->input->post("like_ids"), $video_user));

			foreach ($users as $user) {
				foreach ($videos as $video) {
					$this->video_model->like($video, $user);
				}
			}

			$msg = "The Bulk likes was success for all videos";
		} else if ($action == "comment-video") {
			$videos = array_unique(array_merge($this->input->post("comment_ids"), $video_user));
			$comment = $this->input->post("comment");

			if ($comment != "") {
				foreach ($users as $user) {
					foreach ($videos as $video) {
						if ($video != "") $this->video_model->comment($video, $user, $comment);
					}
				}

				$msg = "The bulk comments was success for all videos";
			} else {
				$type = "error";
				$msg = "Error: Not was possible comment this video";
			}
		} else if ($action == "favorite-video") {
			$videos = array_unique(array_merge($this->input->post("favorite_ids"), $video_user));

			foreach ($users as $user) {
				foreach ($videos as $video) {
					$this->video_model->favorite($video, $user);
				}
			}

			$msg = "The bulk favorite was success for all videos";
		} else if ($action == "share-video") {
			$videos = array_unique(array_merge($this->input->post("share_ids"), $video_user));

			foreach ($videos as $video) {
				$this->video_model->share($video, "");
			}

			$msg = "The bulk share was success for all videos";
		} else if ($action == "description-video") {
			delete_cookie("hold-users");
			redirect("video/select/" . implode("-", $users));
			return;
		} else if ($action == 'featured-channel'){
			$channels_usrs = $this->input->post("featured_ids");
			foreach ($users as $user) {
				foreach ($channels_usrs as $channel_usr) {
					$res = $this->video_model->featured_channel($channel_usr, $user);
					$msg = var_dump($res,true);
				}
			}

		}
		delete_cookie("hold-users");
		redirect("video/bulk" . (!empty($msg) ? "?success=true&msg={$msg}&type={$type}" : ""));
	}

	public function select($users) {
		$url = $users;
		$users = explode("-", $users);
		if (count($users) == 0 && $users[0] == "") {
			redirect("video/bulk");
			return;
		}
		$this->load->helper("views_helper");
		$this->load->library('pagination');
		$opcions = array();
		$start = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/select/{$url}";

		$page['videos'] = $this->video_model->get_videos_by_user($users, NULL, $start);
		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$opcions['uri_segment'] = 4;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
		$page["page_name"] = "edit_description";
		$page["title"] = "Edit Description";

		$this->load->view('admin/index', $page);
	}
	/**
	 *
	 * @param string $type Type of message
	 * @param string $mode Mode of message
	 */
	public function msg($type, $mode) {
		$page["page_name"] = 'msg';
		$page["title"] = 'Message';
		$page["type"] = $type;
		$page["mode"] = $mode;
		$this->load->view('admin/index', $page);
	}
	/**
	 * Controller ajax response
	 *
	 * This controller get all videos
	 */
	public function get_ajax_videos() {
		header('Content-type: application/json; charset=utf-8');
		$users_id = $this->input->post("users");
		$category = $this->input->post("category");
		$videos = $this->video_model->get_videos_by_user($users_id, $category);
		die(json_encode($videos));
	}
	/**
	 *
	 * @param string $videoId
	 * @param int $user_id
	 */
	public function fancy_box_view($videoId, $user_id) {
		$page['users'] = $this->user_model->get_all_users();
        $page['entry'] = $this->video_model->get_video($videoId, $user_id);
        $page['channel'] = $this->user_model->get_channel($user_id);
        $page['page_name'] = 'show_video';
        $page['title'] = "Video";
        $page['user_id'] = $user_id;
        $this->load->view('admin/fancybox_view', $page);
	}
//	public function path() {
//		echo __FILE__;
//	}

}

/**/
