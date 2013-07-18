<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<h3><?php echo $video["title"]; ?></h3>
<?php echo $video["embedHtml"]; ?>
<form name="comments" method="post" action="<?php echo base_url() . 'video/comment'; ?>">
    <br/>
    <textarea name="comment" cols=50></textarea>
    <input type="hidden" value="<?php echo $entry["video_id"]; ?>" name="video_id">
    <input type="hidden" value="<?php echo $channel; ?>" name="channel">
    <br/>
    <input type="submit" value="Comment">
</form>
