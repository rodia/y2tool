<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	"video/videos/{$user_id}" => "Videos",
	$title
)); ?>
<center>
	<?php if (isset($success)) show_messages($success, $msg, $type); ?>
    <?php echo form_open("video/share/{$user_id}/{$video_id}")?>
    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Message</a></th>
                </tr>
                <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
                <tr >
                    <td align="right">
                        <h2>Message *</h2>
                    </td>
                    <td>
						<?php echo form_textarea(array("name" => "message", "cols" => "50", "value" => $message)); ?>
                        <span><?php echo form_error('message'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
						<?php echo form_submit(array("name" => "submit", "value" => "Submit", "class" => "form-submit")); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php echo form_close(); ?>
</center>
