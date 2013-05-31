<?php
/**
 * Demographics
 *
 * @author César Jaldin <rodia.piedra@gmail.com>
 * @version 0.1 Test build
 *
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<a href="http://insight.youtube.com/video-analytics/csvreports?<?php
	echo http_build_query(array(
		"query" => "t2mQ3C0gpX0",
		"type" => "v",
		"starttime" => 1296086400000,
		"endtime" => 1298505600000,
		"user_starttime" => 1297900800000,
		"user_endtime" => 1298505600000,
		"region" => "world",
		"token" => "1/Z2x3Af8BNB2cPvM3Kyu6kQ2TNr0EdgfBXox86i2rtQ8",
		"hl" => "en_US"
	)); ?>" >Get data</a>