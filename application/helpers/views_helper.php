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