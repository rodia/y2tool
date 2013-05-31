<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$videoTitle = htmlspecialchars($entry->getVideoTitle());
$videoUrl = htmlspecialchars($entry->getFlashPlayerUrl());
$result = "";

$result .= "<h3>$videoTitle</h3>"
        . '<object width="425" height="350">
            <param name="movie" value="' . $videoUrl . '"></param>
            <param name="allowFullScreen" value="true"></param>
            <param name="allowscriptaccess" value="always">
            <param name="wmode" value="transparent" /></param>
            <embed src="' . $videoUrl . '"
            width="425" height="350" type="application/x-shockwave-flash" allowscriptaccess="always" wmode="transparent" allowfullscreen="true"
            movie="' . $videoUrl . '" wmode="transparent"></embed></object>';
echo $result;
?>
<form name="comments" method="post" action="<?php echo base_url() . 'video/comment'; ?>">
    <select class="select_style" name="user_id" id="user_id">
        <?php foreach ($users as $row) {?>
        <option value="<?php echo $row->id; ?>"><?php echo $row->lastname." ".$row->firstname; ?></option>
        <?php } ?>
    </select>
    <br/>
    <textarea name="comment" cols=50></textarea>
    <input type="hidden" value="<?php echo $entry->getVideoId(); ?>" name="video_id">
    <input type="hidden" value="<?php echo $channel; ?>" name="channel">
    <br/>
    <input type="submit" value="Comment"></form>
