<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
	<?php echo form_open("video/new_playlist", array("method" => "post")); ?>
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
                        <h2>Title:</h2>
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