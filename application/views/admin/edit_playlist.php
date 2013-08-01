<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	"video/playlist/{$user_id}" => "Playlist",
	"video/videolist/{$user_id}/{$videoFeedID}" => "Video list",
	$title
)); ?>
<center>
	<?php if(isset($success)) show_messages($success, $message, $type); ?>
	<?php echo form_open("video/edit_playlist/{$user_id}/{$videoFeedID}", array("method" => "post")); ?>
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Edit Playlist</a></th>
                </tr>
                <tr class="alternate-row">
                    <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title *</h2>
                    </td>
                    <td>
						<?php echo form_input(array("name" => "play_title", "id" => "video_title", "value" => $playlistEntry["snippet"]["title"], "size" => "60px", "class" => "unp-form")); ?>
                        <span><?php echo form_error('play_title'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description:</h2>
                    </td>
                    <td>
						<?php echo form_textarea(array("name" => "play_description", "value" => $playlistEntry["snippet"]["description"], "class" => "form-textarea")); ?>
                    </td>
                </tr>
				<tr>
                    <td align="right">
                        <h2>Status</h2>
                    </td>
                    <td>
						<?php echo form_dropdown('play_status', get_status_options($this), $playlistEntry["status"]["privacyStatus"], 'class="select_style"'); ?>
                        <span><?php echo form_error('play_status'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
						<?php echo form_submit(array("id" => "button", "name" => "submit", "class" => "form-submit"), "Update"); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</center>