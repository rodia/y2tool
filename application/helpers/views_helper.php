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
	<li><a href="<?php echo base_url() . $url; ?>"><?php echo $label; ?></a></li>
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
		$users_options[$row->id] = $row->lastname . " " . $row->firstname;
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