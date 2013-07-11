<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
	<?php echo form_open("video/add_video/{$user_id}/{$videoFeedID}", array("method" => "post")); ?>
	<!--video/addvideo-->
        <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
            <tr>
                <th class="table-header-repeat line-left" colspan="2"><a href="#">Add video to Playlist</a></th>
            </tr>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
            <tr>
                <td>
                    <h2>Youtube video id:</h2>
                </td>
                <td>
					<?php echo form_input(array("name" => "video_id", "placeholder" => "Enter youtube video id", "class" => "inp-form", "size" => 60)); ?>
                    <span><?php echo form_error('video_id'); ?></span><br/>
                    <span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>

					<?php echo form_hidden("type", "youtube"); ?>
					<?php echo form_hidden("videoFeedID", $videoFeedID); ?>
					<?php echo form_hidden("user_id", $user_id); ?>
					<?php echo form_hidden("channel", $channel); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
					<?php echo form_submit(array("name" => "submit", "id" => "button", "class" => "form-submit"), "Add video"); ?>
                </td>
            </tr>
        </table>
    <?php echo form_close(); ?>
</center>