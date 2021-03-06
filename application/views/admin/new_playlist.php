<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	"/video/playlist/{$user_id}" => "Playlist",
	$title
)); ?>
<center>
	<?php if (isset($success)) show_messages($success, $msg, $type); ?>
	<?php echo form_open("video/new_playlist/{$user_id}/{$channel}", array("method" => "post")); ?>
	<!--video/newplay-->
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">New Playlist</a></th>
                </tr>
                <tr class="alternate-row">
                    <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title: <span style="color: red;">*</span></h2>
                    </td>
                    <td>
						<?php echo form_input(array("name" => "play_title", "id" => "play_tilte", "value" => $play_title, "class" => "inp-form", "size" => "60px")); ?>
                        <span><?php echo form_error('play_title'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description:</h2>
                    </td>
                    <td>
						<?php echo form_textarea(array("name" => "play_description", "class" => "form-textarea", "value" => $play_description)); ?>
                        <span><?php echo form_error('play_description'); ?></span>
                    </td>
                </tr>
				<tr>
                    <td align="right">
                        <h2>Status</h2>
                    </td>
                    <td>
						<?php echo form_dropdown('play_status', get_status_options($this), array(), 'class="select_style"'); ?>
                        <span><?php echo form_error('play_status'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
						<?php echo form_hidden("channel", $channel); ?>
						<?php echo form_hidden("user_id", $user_id); ?>
						<?php echo form_submit(array("name" => "submit", "class" => "form-submit", "id" => "button"), "Create"); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php echo form_close(); ?>
</center>