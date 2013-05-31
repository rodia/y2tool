<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript" src="<?php echo base_url(); ?>css/admin/imageselect/imageselect.js"></script>

<link href="<?php echo base_url(); ?>css/admin/imageselect/imageselect.css" media="screen" rel="stylesheet" type="text/css" />
<div id="related-link">
	<ul>
		<li><a href="<?php echo base_url(); ?>admin/users">Dashboard</a></li>
		<li><a href="<?php echo base_url(); ?>video/videos/<?php echo $user_id; ?>">Videos</a></li>
		<li>Video edit</li>
	</ul>
</div>
<center>
    <form enctype="multipart/form-data" action="<?php echo base_url(); ?>video/edit" method="post">
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
                        <?php
                        $videoThumbnails = $videoEntry->getVideoThumbnails();
                        $videoThumbnail = $videoThumbnails[$videoThumbnailKey];
                        ?>
<!--                        <p><a href="<?php echo base_url(); ?>video/view/<?php echo $videoEntry->getVideoId(); ?>" >
                            <img src="<?php echo $videoThumbnail["url"]; ?>" class="borderPhoto" style="height:100px;width:150px;"  />
							</a></p>-->
						<p><select name="videoThumbnailKey" id="videoThumbnail">
						<?php foreach ($videoThumbnails as $key => $item):?>
						<option value="<?php echo $key;?>"<?php echo $key == $videoThumbnailKey ? " selected=\"selected\"" : ""; ?>><?php echo $item["url"];?></option>
						<?php endforeach;?>
							</select></p>

<script type="text/javascript">
	$(document).ready(function(){
		$('select[name=videoThumbnailKey]').ImageSelect({dropdownWidth:225});
	});
</script>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title *</h2>
                    </td>
                    <td>
                        <input class="inp-form" size="60px" type="text" name="video_title" id="video_title" value="<?php echo $videoEntry->getVideoTitle(); ?>"/>
                        <span><?php echo form_error('video_title'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description *</h2>
                    </td>
                    <td>
                        <textarea class="form-textarea" name="video_description"><?php echo $videoEntry->getVideoDescription(); ?></textarea>
                        <span><?php echo form_error('video_description'); ?></span>
                    </td>
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Category *</h2>
                    </td>
                    <td>
                        <?php
//                        $category_options = array(
//                            '' => 'Choose category',
//                            'Autos' => 'Autos &amp; Vehicles',
//                            'Comedy' => 'Comedy',
//                            'Education' => 'Education',
//                            'Entertainment' => 'Entertainment',
//                            'Film' => 'Film &amp; Animation',
//                            'Games' => 'Gaming',
//                            'Howto' => 'Howto &amp; Style',
//                            'Music' => 'Music',
//                            'News' => 'News &amp; Politics',
//                            'Nonprofit' => 'Nonprofits &amp; Activism',
//                            'People' => 'People &amp; Blogs',
//                            'Animals' => 'Pets &amp; Animals',
//                            'Tech' => 'Science &amp; Technology',
//                            'Sports' => 'Sports',
//                            'Travel' => 'Travel &amp; Events'
//                        );
                        $selected = ($this->input->post('category_name')) ? $this->input->post('category_name') : $videoEntry->getVideoCategory();
                        echo form_dropdown('category_name', $category_options, $selected, 'class="select_style"');
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Tags *</h2>
                    </td>
                    <td>
                        <input class="inp-form" size="60px" type="text" name="video_tags" id="video_tags" value="<?php echo implode(", ", $videoEntry->getVideoTags()); ?>"/>
                        <span><?php echo form_error('video_tags'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
                        <input type="hidden" name="video_id" value="<?php echo $videoEntry->getVideoId(); ?>"/>
                        <input type="submit" class="form-submit" value="Update" name="submit" id="button">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</center>