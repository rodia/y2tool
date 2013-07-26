<?php
/**
 * Helper for load view
 */

global $scripts;

function add_script($key, $path, $base = '') {
	global $scripts;

	$scripts["$key"] = $base . $path;

	return $scripts;
}

function get_scripts() {
	global $scripts;
	return implode("\n", $scripts);
}
/**
 * Get excerpt for the content param
 *
 * @param string $content
 * @return string
 */
function get_excerpt($content, $limit = 10) {
	return implode(" ", array_slice(explode(" ", $content), 0, $limit));
}
/**
 * Build a list li with url for $links parameter.
 *
 * @param array $links
 */
function get_link_relates(array $links) {
?>
<div id="related-link">
<ul>
	<?php foreach ($links as $url => $label) : ?>
	<?php if ( ! is_numeric($url)) : ?>
	<?php if ($url == "go-back") : ?>
	<li><a href="#" onclick="javascript:history.go(-1);"><?php echo $label; ?></a>
	<?php else : ?>
	<li><a href="<?php echo base_url() . $url; ?>"><?php echo $label; ?></a></li>
	<?php endif; ?>
	<?php else : ?>
	<li class="last"><?php echo $label; ?></li>
	<?php endif; ?>
	<?php endforeach; ?>
</ul>
</div>
<?php
}
/**
 *
 * @param array $users List of user admin from the database.
 * @return array
 */
function get_user_dropbox($users) {
	$users_options = array();
	foreach ($users as $row) {
		if ($row->lastname != "" || $row->firstname != "") {
			$users_options[$row->id] = $row->lastname . " " . $row->firstname;
		} else {
			$users_options[$row->id] = $row->user_login;
		}
	}

	return $users_options;
}
/**
 *
 * @param mixed $success type or state for message
 * @param string $msg The message for show
 *
 *
 * @param mixed $success Trigger for show message
 * @param string $msg The message for show.
 * @param string $type Is an value in (info, success, warning, error, validation) list
 */
function show_messages($success, $msg = "", $type = "success") { ?>
	<?php if ( ! empty($success) && $success === TRUE) : ?>
	<div class="<?php echo $type; ?>">
		<p><?php echo $msg; ?></p>
	</div>
	<?php endif; ?>
<?php
}

/**
 * Print description of output for view report
 *
 * @param int $opt
 * @param string $admin
 * @param string $task
 * @param string $video_id
 * @param string $channel
 * @param string $who
 * @return string
 */
function print_desc($opt, $admin, $task, $video_id, $channel, $who) {
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
 *
 * @param string $video_id ID Youtube
 * @param object $resource
 * @return int Count of like in the video
 */
function print_likes($video_id, $resource) {
	$resource->load->model("video_model");
	$youtube = $resource->video_model->get_google_youtubeService();
	$videoResponse = $youtube->videos->listVideos(
		$video_id,
		'statistics'
	);
	$video_like = "No data";
	foreach($videoResponse['items'] as $video)
	{
		$video_like = $video['statistics']['likeCount'];
	}
	return $video_like;
}
/**
 *
 * @param string $video_id
 * @param object $resource
 * @return string
 */
function print_current_views($video_id,  $resource) {
	$resource->load->model("video_model");
	$youtube = $resource->video_model->get_google_youtubeService();
	$videoResponse = $youtube->videos->listVideos(
		$video_id,
		'statistics'
	);
	$video_views = "No data";
	foreach($videoResponse['items'] as $video)
	{
		$video_views = $video['statistics']['viewCount'];
	}
	return $video_views;
}
/**
 *
 * @param object $resource
 * @return array
 */
function get_categories($resource) {
	$resource->load->model("user_model");
	return $resource->user_model->get_youtube_categories();
}