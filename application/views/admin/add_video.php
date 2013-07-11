<?php
/**
 * @version 1.1
 *
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
	$(function() {

		$('.addInput').live('click', function() {
			var select_action = $(this).attr("rel");

			var inputsDiv = $('#' + select_action);
			var i = $('#' + select_action + ' p').size() + 1;
			var name_field = select_action.replace("_inputs", "");
			$('<p><label for="video_id"><span>Video ID ' + i + ' * </span><input type="text" class="inp-form" size="50" name="' + name_field + '_ids[]" id="video_ids_' + i + '" value="" placeholder="Enter youtube video id" /></label> <span><a href="#" class="remInput" rel="' + select_action + '" style="color:#0093F0">Remove</a></span></p>').appendTo(inputsDiv);
			$(inputsDiv).attr("title", i);
			return false;
		});

		$('.remInput').live('click', function() {
			var select_action = $(this).attr("rel");
			var i = $('#' + select_action + ' p').size() + 1;
			if( i > 2 ) {
				$(this).parents('p').remove();
				i--;
			}
			return false;
		});
	});
</script>
<center>
	<?php echo form_open("video/add_video_playlist/{$user_id}/{$videoFeedID}", array("method" => "post")); ?>
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
					<div id="like_inputs" title="1">
						<p><?php echo form_input(array("name" => "video_id", "placeholder" => "Enter youtube video id", "class" => "inp-form", "size" => 60)); ?>
						<span><?php echo form_error('video_id'); ?></span><br/>
						<span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
						</p>
					</div>
					<h2><a href="#" class="addInput" rel="like_inputs" style="color:#0093F0">Add another input box</a></h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
					<?php echo form_hidden("type", "youtube"); ?>
					<?php echo form_hidden("videoFeedID", $videoFeedID); ?>
					<?php echo form_hidden("user_id", $user_id); ?>
					<?php echo form_hidden("channel", $channel); ?>
					<?php echo form_submit(array("name" => "submit", "id" => "button", "class" => "form-submit"), "Add video"); ?>
                </td>
            </tr>
        </table>
    <?php echo form_close(); ?>
</center>