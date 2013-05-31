<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once "facebook-php-sdk/facebook.php";
$facebook = new Facebook(array(
            'appId' => $fdappId,
            'secret' => $fbsecret,
            'cookie' => true,
            'fileUpload' => true,
        ));
$facebook->setAccessToken($fbaccessToken);

try {

    for ($i = 0; $i < sizeof($video_ids); $i++) {

        $params = array(
            'message' => "Hey you! check this!",
            'link' => 'http://www.youtube.com/watch?v=' . $video_ids[$i], //the video to embed
            //'name' => $videoEntry->getTitle(),
            //'caption' => 'Some caption',
//            'picture' => $videoThumbnail["url"],
            'source' => 'http://www.youtube.com/watch?v=' . $video_ids[$i], //the video to embed
//            'description' => $videoEntry->getVideoDescription(),
            'type' => 'video',
//        'actions' => array(
//            'name' => 'My app ',
//            'link' => 'http://apps.facebookcom/mydummyapp'),
            'privacy' => array('value' => 'EVERYONE')
        );
        $ans = $facebook->api("/$fbpageid/feed", 'post', $params);
        if (!empty($ans['id']))
            $facebook_ref = $ans['id'];
    }
    echo "<h2>Total sharing videos: " . sizeof($video_ids) . "</h2>";
} catch (FacebookApiException $e1) {
    error_log($e1);
}
