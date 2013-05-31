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
    if ($post) {
        ?>

        <center>      
            
            <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
                <tbody>
                    <tr>
                        <th colspan="2" class="table-header-repeat line-left"><a href="#">Message</a></th>
                    </tr> 
                    <tr class="alternate-row">
                        <td colspan="2"><h2><em><?php echo "It sometimes successfully shared the video      " ?></em><a target="_blank" href="http://www.youtube.com/watch?v=<?php echo $video_id; ?>">View video</a></h2></td>
                    </tr>          
             
                </tbody>
            </table>
          
        </center>
        <?php
    }
} catch (FacebookApiException $e1) {
    error_log($e1);
}