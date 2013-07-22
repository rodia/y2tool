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
	<?php if(isset($success)) show_messages($success, $message, $type); ?>
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
                        <input class="inp-form" size="60px" type="text" name="video_title" id="video_title" value="<?php echo $video_title ?>"/>
                        <span><?php echo form_error('video_title'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description:</h2>
                    </td>
                    <td>
                        <textarea class="form-textarea" name="video_description"><?php echo $video_description; ?></textarea>
                        <span><?php echo form_error('video_description'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Tags:</h2>
                    </td>
                    <td>
                        <input class="inp-form" size="60px" type="text" name="video_tags" id="video_tags" value="<?php echo $video_tags; ?>"/>
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
<!--                        <select  class="select_style" name="video_category" id="video_category">
                            <option value="Autos">Autos &amp; Vehicles</option>
                            <option value="Comedy">Comedy</option>
                            <option value="Education">Education</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Film">Film &amp; Animation</option>
                            <option value="Games">Gaming</option>
                            <option value="Howto">Howto &amp; Style</option>
                            <option value="Music">Music</option>
                            <option value="News">News &amp; Politics</option>
                            <option value="Nonprofit">Nonprofits &amp; Activism</option>
                            <option value="People">People &amp; Blogs</option>
                            <option value="Animals">Pets &amp; Animals</option>
                            <option value="Tech">Science &amp; Technology</option>
                            <option value="Sports">Sports</option>
                            <option value="Travel">Travel &amp; Events</option>
                        </select>-->


                    </td>
                </tr>
                <tr >
                    <td align="right">
                        <h2>Choose a file to upload:</h2>
                    </td>
                    <td>
                        <input type="file" name="video_file" size="40" class="inp-form" value="<?php echo $video_file; ?>">
                    </td>
                </tr>

                <tr>
                    <td align="center" colspan="2">

                        <input type="hidden"  name="user_id" value="<?php echo $user_id; ?>" >
                        <input type="submit" class="form-submit" value="Upload video" id="button" name="submit">
                    </td>
                </tr>
            </tbody></table>
    </form>
</center>