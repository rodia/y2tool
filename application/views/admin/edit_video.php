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
<!--<div id="related-link">
	<ul>
		<li><a href="<?php echo base_url(); ?>admin/users">Dashboard</a></li>
		<li><a href="<?php echo base_url(); ?>video/videos/<?php echo $user_id; ?>">Videos</a></li>
		<li>Edit Video</li>
	</ul>
</div>-->
<center>
	<?php echo form_open("video/" . implode( "/", array($post_form, $video_id, $user_id)), array("enctype" => "multipart/form-data", "method" => "post")); ?>
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Edit video</a></th>
                </tr>
                <tr class="alternate-row">
                    <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Video</h2>
                    </td>
                    <td>
                        <p><a href="<?php echo base_url(); ?>video/view/<?php echo $videoEntry["video_id"]; ?>" >
                            <img src="<?php echo $videoEntry["thumbnail"]["url"]; ?>" class="borderPhoto" style="height:100px;width:150px;"  />
							</a></p>
							<p><?php echo form_input(array("name" => "thumbnail", "class" => "inp-form", "size" => "60px", "value" => $videoEntry["thumbnail"]["url"], "disabled" => "disabled")); ?> <?php echo form_upload("new-thumbnails"); ?></p>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title *</h2>
                    </td>
                    <td>
						<?php echo form_input(array("name" => "video_title", "id" => "video_title", "value" => $videoEntry["title"], "class" => "inp-form", "size" => "60px")); ?>
                        <span><?php echo form_error('video_title'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description *</h2>
                    </td>
                    <td>
<!--                        <textarea class="form-textarea" name="video_description"><?php echo $videoEntry["description"]; ?></textarea>-->
						<?php echo form_textarea(array("name" => "video_description", "value" => $videoEntry["description"], "class" => "form-textarea")); ?>
                        <span><?php echo form_error('video_description'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Category *</h2>
                    </td>
                    <td>
                        <?php echo form_dropdown('category_id', $category_options, $selected, 'class="select_style"'); ?>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Tags *</h2>
                    </td>
                    <td>
						<?php echo form_input(array("name" => "video_tags", "id" => "video_tags", "value" => implode(", ", $videoEntry["tags"]), "class" => "inp-form", "size" => "60px")); ?>
                        <span><?php echo form_error('video_tags'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
						<?php echo form_hidden("user_id", $user_id); ?>
						<?php echo form_hidden("video_id", $videoEntry["video_id"]); ?>

						<?php echo form_submit(array("name" => "submit", "id" => "button", "class" => "form-submit", "value" => "Update")); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php echo form_close(); ?>
</center>