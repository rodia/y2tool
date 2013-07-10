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
	 *
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

    function sharing($user = "all", $category = "all") {
		$this->load->library('pagination');

		$opcions = array();
		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;

		$opcions['per_page'] = $this->config->item("rp");
		$opcions['base_url'] = base_url() . "video/sharing/{$user}/{$category}";

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
		$page["categories"] = array_merge(
			array("" => "-- Select --"),
			$this->video_model->get_pair_values(
				$this->video_model->get_all_categories(),
				'category',
				'display_category'
			)
		);
		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$opcions['uri_segment'] = 5;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
        $page['page_name'] = 'sharing';
        $page['title'] = "Sharing Videos";
        $this->load->view('admin/index', $page);
    }

    function s1_sharing() {
//        $settings = $this->video_model->get_yt_settings();
        $page["video_ids"] = $this->input->post('ids');
//        $page['page_name'] = 'bulksharing';
//        $page['title'] = "Sharing videos";
		$date = date("Y-m-d H:i:s");
		$admin_id = $this->session->userdata('user_id');
		foreach ($page["video_ids"] as $item) {
			$this->video_model->share($item, "");
			$video_id = $this->video_model->get_video_id($item);
			if ($video_id == 0) {
				$data = array(
					"youtube_id" => $item,
					"channel" => "",
					"title" => $video_id
				);
				$this->video_model->insert_video($data);
			}
			$dbdata = array(
				"registered_date" => $date,
				"admin_id" => $admin_id,
				"video_id" => $video_id,
				"task_id" => 4,
				"who" => "Sharing Video " + $item + " (" + $this->session->userdata('name') + ")"
			);
			$this->video_model->insert_history($dbdata);
		}
		$msg = "Bulk sharing is done!";
//        $page['fdappId'] = $settings[0]->facebook_apikey;
//        $page['fbsecret'] = $settings[0]->facebook_secret;
//        $page['fbaccessToken'] = $settings[0]->facebook_accesstoken;
//        $page['fbpageid'] = "me";

//        $this->load->view('admin/index', $page);
		redirect("video/sharing". (isset($msg) ? "?msg=" . $msg : ""));
    }

    function s1_favorites() {
        $page["video_ids"] = $this->input->post('ids');
        $page["users"] = $this->user_model->get_users_channel();
        $page['page_name'] = 'favorites_users';
        $page['title'] = "Favoriting Videos (select the channels)";
        $this->load->view('admin/index', $page);
    }

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

    function removeVideos($playlistId, $video_ids, $user_id, $channel) {
        for ($i = 0; $i < sizeof($video_ids); $i++) {
            $this->delvideo2($playlistId, $video_ids[$i], $user_id, $channel);
        }
//        delvideo($videoFeedID, $videoId, $user_id, $channel);
        $this->videolist($playlistId, $user_id, $channel);
    }

	function userActions() {
        $video_id = $this->input->post('video_id');
        $users_ids = $this->input->post('ids');

        for ($i = 0; $i < sizeof($users_ids); $i++) {
            $this->like2($video_id, $users_ids[$i]);
        }
        redirect("video/liking");
    }

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

    function delvideo($user_id, $videoFeedID, $videoId) {
        $feedUrl = "http://gdata.youtube.com/feeds/api/playlists/$videoFeedID";
        $playlistId = $videoFeedID;
        $yt = $this->user_model->getHttpClient($user_id);
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
//        $yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);

        $playlistVideoFeed = $yt->getPlaylistVideoFeed($feedUrl);
        foreach ($playlistVideoFeed as $videoEntry) {
            if ($videoEntry->getVideoId() == $videoId)
                $videoEntry->delete();
        }

        $pl_title = $this->getPlaylistTitle($playlistId, $channel, $user_id);
        $pl_title = str_replace("%20", " ", $pl_title);
        $page['playlistVideoFeed'] = $yt->getPlaylistVideoFeed($feedUrl);
        $page['page_name'] = 'videolist';
        $page['title'] = "Video list ($pl_title)";
        $page['videoFeedID'] = $playlistId;
        $page['msg'] = $this->lang->line('form_rm_success');
        $page['user_id'] = $user_id;
        $page['channel'] = $channel;
        $this->load->view('admin/index', $page);
    }

    function edit_playlist($user_id, $playlistId) {
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
        $yt = $this->user_model->getHttpClient($user_id);
        $yt->setMajorProtocolVersion(2);
        //
        $playlistListFeed = $yt->getPlaylistListFeed($channel);

        foreach ($playlistListFeed as $playlistListEntry) {
            if ($playlistListEntry->playlistId == $playlistId) {
                $page["playlistListEntry"] = $playlistListEntry;
                break;
            }
        }
        $page['page_name'] = 'edit_playlist';
        $page['title'] = "Edit playlist";
        $page['channel'] = $channel;
        $page['user_id'] = $user_id;
        $page['msg'] = $this->lang->line('form_msg');
        $this->load->view('admin/index', $page);
    }

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

    function delplaylist($user_id, $playlistId) {
        $yt = $this->user_model->getHttpClient($user_id);
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
        $yt->setMajorProtocolVersion(2);
        $playlistListFeed = $yt->getPlaylistListFeed($channel);
        foreach ($playlistListFeed as $playlistListEntry) {
            if ($playlistListEntry->playlistId == $playlistId) {
                $playlistListEntry->delete();

                $playlist = $this->video_model->exists_playlist($playlistId);
                $play_id = $playlist['id'];
//                if (!$playlist) {
//                    $data = array(
//                        "channel" => $channel,
//                        "title" => $playlist_title,
//                        "playlistId" => $playlistId
//                    );
//                    $play_id = $this->video_model->insert_playlist($data);
//                }
                $dbdata = array(
                    "registered_date" => date("Y-m-d H:i:s"),
                    "admin_id" => $this->session->userdata('user_id'),
                    "video_id" => 0,
                    "task_id" => 9,
                    "playlist_id" => $play_id,
                    "who" => $this->session->userdata('name')
                );
                $this->video_model->insert_history($dbdata);
            }
        }
        $page['playlistListFeed'] = $yt->getPlaylistListFeed($channel);
        $page['msg'] = $this->lang->line('form_rm_play_success');
        $page['page_name'] = 'playlist';
        $page['title'] = "Playlist (channel: " . $channel . ")";
        $page['user_id'] = $user_id;
        $page['channel'] = $channel;
        $this->load->view('admin/index', $page);
    }

    function like() {

        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = $this->lang->line('form_msg');
        $page['video_id'] = "";
        $page['page_name'] = 'like';
        $page['title'] = "Single like";
        $this->load->view('admin/index', $page);
    }

    function likingnvideos() {

        $page['users'] = $this->user_model->get_all_users();
        $page['msg'] = $this->lang->line('form_msg');
        $page['page_name'] = 'likingnvideos';
        $page['title'] = "Liking N videos";
        $this->load->view('admin/index', $page);
    }

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
	 *
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
	 *
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
	 * @deprecated since version 1
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
					$page['msg'] = "Error when try insert playlist.";
				} else {
					redirect("video/playlist/" . $user_id);
					return;
				}
			}
		}

        $page['page_name'] = 'new_playlist';
        $page['title'] = "Add Video to Playlist";
        $page['user_id'] = $user_id;
        $page['channel'] = $channel;
        $page['play_title'] = "";
        $page['play_description'] = "";
        $this->load->view('admin/index', $page);
    }

    function add_video() {
        $yt = $this->user_model->getHttpClient($this->input->post('user_id'));
        $page['page_name'] = 'add_video';
        $page['msg'] = $this->lang->line('form_msg');
        $page['videoFeedID'] = $this->input->post('videoFeedID');
        $page['user_id'] = $this->input->post('user_id');
        $page['channel'] = $this->input->post('channel');
        $page['title'] = "Add Video to Playlist";
        $this->load->view('admin/index', $page);
    }

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
        $profile = $this->user_model->getUserProfile($user_id);
        $channel = $profile['username'];
        $page['playlistListFeed'] = $this->video_model->oauth_get_playlist($user_id);
        $page['msg'] = "";
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

			$this->video_model->edit_video($video_id, $user_id, array(
				"video_id" => $this->input->post('video_id'),
				"video_title" => $this->input->post('video_title'),
				"video_description" => $this->input->post('video_description'),
				"category_id" => $this->input->post('category_id'),
				"video_tags" => explode(",", $this->input->post('video_tags')),
				"url" => $file
			));
			$this->video_model->set_history($video_id, $user_id, array(
				"channel" => $this->user_model->get_channel($user_id),
				"task_id" => 1
			));
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

		if ($video_id) {
			$yt = $this->user_model->getHttpClient($user_id);
			$videoEntry = $this->video_model->get_video_entry($video_id);
			$profile = $this->user_model->getUserProfile($user_id);

			if ($videoEntry->getEditLink() !== null) {
				$putUrl = $videoEntry->getEditLink()->getHref();
				$this->video_model->set_value_edit($videoEntry, $value, $field);
				$yt->updateEntry($videoEntry, $putUrl);

				$views = $videoEntry->getVideoViewCount();
				$rating = $videoEntry->getVideoRatingInfo();
				$likes = $rating['numRaters'];

				if ($field == "title") {
					$video_title = $value;
				} else {
					$video_title = $videoEntry->getVideoTitle();
				}

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
					"channel_subs" => $profile["subs"],
					"task_id" => 1,
					"who" => $this->session->userdata('name')
				);
				$this->video_model->insert_history($dbdata);

				$this->video_model->db_update_video(array(
					"title" => $video_title,
					"video_thumbnail_key" => $this->video_model->get_video_thumbnail_key($video_id)
				), $video_id);
			}
		}

		die(json_encode(array("video_id" => $video_id, "user_id" => $user_id, "value" => $value, "field" => $field, "channel" => $channel)));
	}
	/**
	 * CONTROLLERS
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
	 * If the user enable your token auth, this task is possible.
	 */
    function upload() {
        $user_id = $this->input->post('user_id');
        $profile = $this->user_model->getUserProfile($user_id);
        $subs = $profile['subs'];
        $yt = $this->user_model->getHttpClient($user_id);
        $yt->setMajorProtocolVersion(2);
        $file_name = $_FILES['video_file']['name'];
        $tmp_name = $_FILES['video_file']['tmp_name'];

        $video_title = $this->input->post('video_title');
        $video_description = $this->input->post('video_description');
        $video_category = $this->input->post('video_category');
        $video_tags = $this->input->post('video_tags');


        $rules = $this->config->item('new_video_rules');
        $page['msg'] = $this->lang->line('form_msg');

        if (isset($_POST['submit'])) {

            $this->form_validation->set_rules($rules); //check with the rules
            if ($this->form_validation->run() == FALSE) {
                //validation failed
                $page['msg'] = $this->lang->line('form_error');
                $page['video_title'] = $video_title;
                $page['video_description'] = $video_description;
                $page['video_tags'] = $video_tags;
                $page['video_file'] = $tmp_name;
                $page['page_name'] = 'upload_video';
                $page['title'] = "Upload a new video";
                $page['user_id'] = $user_id;

                $this->load->view('admin/index', $page);
            } else {

                $myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

                $filesource = $yt->newMediaFileSource($tmp_name);
                $filesource->setContentType('video/quicktime');
                // set slug header
                $filesource->setSlug($tmp_name);

                $myVideoEntry->setMediaSource($filesource);
                $myVideoEntry->setVideoTitle($video_title);
                $myVideoEntry->setVideoDescription($video_description);
                $myVideoEntry->setVideoCategory(trim($video_category));
                $myVideoEntry->SetVideoTags($video_tags);
                // set some developer tags -- this is optional
                $myVideoEntry->setVideoDeveloperTags(array('mydevtag', 'anotherdevtag'));

                // set the video's location -- this is also optional
                $yt->registerPackage('Zend_Gdata_Geo');
                $yt->registerPackage('Zend_Gdata_Geo_Extension');
                $where = $yt->newGeoRssWhere();
                $position = $yt->newGmlPos('37.0 -122.0');
                $where->point = $yt->newGmlPoint($position);
                $myVideoEntry->setWhere($where);

                // upload URI for the currently authenticated user
                $uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
                // if available, or just a regular Zend_Gdata_App_Exception otherwise
                try {
                    $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
                    $newEntry->setMajorProtocolVersion(2);

                    $data = array(
                        "youtube_id" => $newEntry->getVideoId(),
                        "channel" => $profile["username"],
                        "title" => $video_title
                    );
                    $v_id = $this->video_model->insert_video($data);

                    $dbdata = array(
                        "registered_date" => date("Y-m-d H:i:s"),
                        "admin_id" => $this->session->userdata('user_id'),
                        "video_id" => $v_id,
                        "video_likes" => 0,
                        "video_views" => 0,
                        "channel_subs" => $subs,
                        "task_id" => 6,
                        "who" => $this->session->userdata('name')
                    );
                    $this->video_model->insert_history($dbdata);
                } catch (Zend_Gdata_App_HttpException $httpException) {
                    echo $httpException->getRawResponseBody();
                } catch (Zend_Gdata_App_Exception $e) {
                    echo $e->getMessage();
                }
                $page['msg'] = $this->lang->line('form_new_video_success');
                $page['video_title'] = $video_title;
                $page['video_description'] = $video_description;
                $page['video_tags'] = $video_tags;
                $page['video_file'] = "";
                $page['page_name'] = 'upload_video';
                $page['title'] = "Upload a new video";
                $page['user_id'] = $user_id;
                $this->load->view('admin/index', $page);
            }
        }
    }

    function index() {
//        redirect("admin/users");
        redirect("video/bulk");
    }

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
		$page['title'] = "Upload a new video";
		$this->load->view('admin/index', $page);
	}

	public function videos($user_id, $category = "all") {

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
        $page['title'] = "Report Videos (Channel: $channel)";
        $page['channel'] = $channel;
        $page['owner'] = $user_id;
		$page["video_model"] = $this->video_model;
        $this->load->view('admin/index', $page);
	}

	/**
	 *
	 * @param int $user
	 * @param string $category
	 */
//    function videos($user, $category = "all") {
//		$this->load->library('pagination');
//
//		$opcions = array();
//		$start = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;
//
//		$opcions['per_page'] = $this->config->item("rp");
//		$opcions['base_url'] = base_url() . "video/videos/{$user}/{$category}";
//
//        // $page['videos'] = $this->video_model->getUserUploads($channel);
//		// optionally specify version 2 to retrieve a v2 feed
//
//        if ($user != "all" && $category == "all") {
//			$page['videos'] = $this->video_model->all_videos(array($user), NULL, $start);
//		} else if ($user != "all" && $category != "all") {
//			$page['videos'] = $this->video_model->all_videos(array($user), $category, $start);
//		} else if ($user == "all" && $category != "all") {
//			$page['videos'] = $this->video_model->all_videos(NULL, $category, $start);
//		} else {
//			$page['videos'] = $this->video_model->all_videos(NULL, NULL, $start);
//		}
//		$opcions['total_rows'] = $this->video_model->get_count_videos();
//		$opcions['uri_segment'] = 5;
//		$this->pagination->initialize($opcions);
//		$page['pagination'] = $this->pagination->create_links();
//        $page['users'] = $this->user_model->get_all_users();
//        $page['msg'] = "";
//        $page['page_name'] = 'videos';
//        $page['title'] = "Videos (Channel: $channel)";
//        $page['channel'] = $channel;
//        $page['owner'] = $user;
//		$page["video_model"] = $this->video_model;
//        $this->load->view('admin/index', $page);
//    }

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

    function view($videoId, $channel = "", $user_id = 0) {
        $yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);
        try {
            $entry = $yt->getVideoEntry($videoId);
        } catch (Zend_Gdata_App_HttpException $httpException) {
            echo 'ERROR ' . $httpException->getMessage();
            echo ' HTTP details<br /><textarea cols="100" rows="20">';
            echo $httpException->getRawResponseBody();
            echo '</textarea><br />';
        }
        $page['users'] = $this->user_model->get_all_users();
        $page['entry'] = $entry;
        $page['channel'] = $channel;
        $page['page_name'] = 'show_video';
        $page['title'] = "Video";
        $page['user_id'] = $user_id;
        $this->load->view('admin/index', $page);
    }
	/**
	 *
	 * @param string $video_id
	 */
    function share($video_id) {
		if ($this->input->post("submit")) {

		}
        $page['page_name'] = 'message';
        $page['msg'] = '';
        $page['message'] = '';
        $page['title'] = "Sharing video";
        $page['video_id'] = $video_id;

        $this->load->view('admin/index', $page);
    }
	/**
	 * CONTROLLERS
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
	 * Controller for form action
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
		$hold_users = array();
		$pair_user_login = array();
		$users_checkbox = $this->input->post("ids");
		$temp_users = explode(",", $this->input->cookie("hold-users"));
		$this->video_model->get_temp_users_id($hold_users, $pair_user_login, $temp_users);

		$users = array_unique(array_merge($users_checkbox, $hold_users));

		if ($action == "like-video") {
			$videos = $this->input->post("like_ids");

			foreach ($users as $user) {
				foreach ($videos as $video) {
					$this->video_model->like($video, $user);
				}
			}

			$msg = "The Bulk likes was success for all videos";
		} else if ($action == "comment-video") {
			$videos = $this->input->post("comment_ids");
			$comment = $this->input->post("comment");

			foreach ($users as $user) {
				foreach ($videos as $video) {
					$this->video_model->comment($video, $user, $comment);
				}
			}

			$msg = "The bulk comments was success for all videos";
		} else if ($action == "favorite-video") {
			$videos = $this->input->post("favorite_ids");

			foreach ($users as $user) {
				foreach ($videos as $video) {
					$this->video_model->favorite($video, $user);
				}
			}

			$msg = "The bulk favorite was success for all videos";
		} else if ($action == "share-video") {
			$videos = $this->input->post("share_ids");

			foreach ($videos as $video) {
				$this->video_model->share($video, "");
			}

			$msg = "The bulk share was success for all videos";
		} else if ($action == "description-video") {
			delete_cookie("hold-users");
			redirect("video/select/" . implode("-", $users));
			return;
		}
		delete_cookie("hold-users");
		redirect("video/bulk" . (!empty($msg) ? "?msg=" . $msg : ""));
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

		$page['videos'] = $this->video_model->all_videos($users, NULL, $start);
		$opcions['total_rows'] = $this->video_model->get_count_videos();
		$opcions['uri_segment'] = 4;
		$this->pagination->initialize($opcions);
		$page['pagination'] = $this->pagination->create_links();
		$page["page_name"] = "edit_description";
		$page["title"] = "Bulk Action > Edit Description";

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
		/*$users_id = $this->input->post("users");
		$category = $this->input->post("category");*/
		$users_id = '40220';
		$category = 'Games';
		$videos = $this->video_model->all_videos($users_id, $category);
		//echo json_encode($videos)
		die(json_encode($videos));
	}

	public function php() {
		phpinfo();
	}

	public function path() {
		echo __FILE__;
	}

}

/**/