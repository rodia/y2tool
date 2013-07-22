<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	$title
)); ?>
<center>
	<?php if (isset($success)) show_messages($success, $message, $type); ?>
	<?php echo form_open_multipart("video/upload/{$user_id}", array("method" => "post")); ?>
    <!--<form enctype="multipart/form-data" action="<?php echo base_url(); ?>video/upload" method="post">-->
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Upload new video</a></th>
                </tr>
                <tr class="alternate-row">
                    <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title:</h2>
                    </td>
                    <td>
						<?php echo form_input(array("name" => "video_title", "id" => "video_title", "value" => $video_title, "class" => "inp-form", "size" => "60px")); ?>
                        <span><?php echo form_error('video_title'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description:</h2>
                    </td>
                    <td>
						<?php echo form_textarea(array("name" => "video_description", "class" => "form-textarea", "value" => $video_description)); ?>
                        <span><?php echo form_error('video_description'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Tags:</h2>
                    </td>
                    <td>
						<?php echo form_input(array("name" => "video_tags", "id" => "video_tags", "value" => $video_tags, "class" => "inp-form", "size" => "60px")); ?>
                        <span><?php echo form_error('video_tags'); ?></span>
                        <br />
                        <span class="url-demo">e.g. </span><b><i>video, music</i></b>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Category:</h2>
                    </td>
                    <td>
						<?php echo form_dropdown('video_category', get_categories($this), array(), 'class="select_style" id="video_category"'); ?>
                    </td>
                </tr>
                <tr >
                    <td align="right">
                        <h2>Choose a file to upload:</h2>
                    </td>
                    <td>
						<?php echo form_upload(array("name" => "video_file", "class" => "inp-form", "size" => "40", "value" => $video_file)); ?>
                    </td>
                </tr>

                <tr>
                    <td align="center" colspan="2">
						<?php echo form_submit(array("name" => "submit", "id" => "button", "class" => "form-submit"), "Upload Video"); ?>
                    </td>
                </tr>
            </tbody></table>
    <?php echo form_close(); ?>
</center>