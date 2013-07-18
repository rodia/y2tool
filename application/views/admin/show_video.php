<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<h3><?php echo $entry["title"]; ?></h3>
<?php echo $entry["embedHtml"]; ?>
<form name="comments" method="post" action="<?php echo base_url() . 'video/comment'; ?>">
<?php echo form_open("video/comment", array("method" => "post", "name" => "comments")); ?>
    <br/>
    <?php echo form_textarea(array("name" => "comment", "cols" => 15)); ?>
	<?php echo form_hidden("video_id", $entry["video_id"]); ?>
	<?php echo form_hidden("channel", $channel); ?>
    <br/>
	<?php echo form_submit(array("name" => "submit", "value" => "Comment")); ?>
</form>
