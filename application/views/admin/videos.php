<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#myForm").submit(function(){
            if (!isCheckedById("videos_ids")){
                alert ("Please select at least one checkbox");
                return false;
            }else{
                return true; //submit the form
            }
        });
    });
</script>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	$title
)); ?>
<center>
	<?php if (isset($success)) show_messages($success, $message, $type); ?>
    <?php echo form_open('video/videoActions', array('class' => 'forms', 'id' => 'myForm', 'name' => 'myForm')); ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left minwidth-1"><a href="<?php echo base_url(); ?>admin/channel_report/<?php echo $owner; ?>"><b>Report Current Channel</b></a></th>
        </tr>
    </table>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <?php if (!empty($msg)) { ?>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
        <?php } ?>
        <tr>
            <th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll" value=""></th>
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Description</a></th>
            <th class="table-header-repeat line-left" ><a href="">Category</a></th>
            <th class="table-header-repeat line-left" width="100"><a href="">Preview</a></th>
            <th class="table-header-repeat line-left"><a href="">Viewed</a></th>
            <th class="table-header-repeat line-left" width="100"><a href="">Options</a></th>

        </tr>
        <?php foreach ($videos as $key => $video) : ?>
            <tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
                <td><input type="checkbox" name="videos_ids[]" id="videos_ids" value="<?php echo $video["video_id"]; ?>" />  </td>
                <td><?php echo $video["title"]; ?></td>
                <td><?php echo str_replace("<", "", substr($video["description"], 0, 100)); ?></td>
                <td align="center"><?php echo $video["category"]; ?></td>
                <td align="center">
					<a href="<?php echo base_url(); ?>video/view/<?php echo $video["video_id"]; ?>/<?php echo $owner; ?>" >
                        <img src="<?php echo $video["thumbnail"]["url"]; ?>" class="borderPhoto" style="height:100px;width:150px;" />
                    </a>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style ">
                        <a class="addthis_button_preferred_1" addthis:url="https://www.youtube.com/watch?v=<?php echo $video["video_id"]; ?>" addthis:title="<?php echo $video["title"]; ?>"></a>
                        <a class="addthis_button_preferred_2" addthis:url="https://www.youtube.com/watch?v=<?php echo $video["video_id"]; ?>" addthis:title="<?php echo $video["title"]; ?>"></a>
                        <a class="addthis_button_preferred_3" addthis:url="https://www.youtube.com/watch?v=<?php echo $video["video_id"]; ?>" addthis:title="<?php echo $video["title"]; ?>"></a>
                        <a class="addthis_button_preferred_4" addthis:url="https://www.youtube.com/watch?v=<?php echo $video["video_id"]; ?>" addthis:title="<?php echo $video["title"]; ?>"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?domready=1#pubid=/*Your pubid*/"></script>
                    <!-- AddThis Button END -->
                </td>
                <td><?php echo $video["view_count"]; ?></td>
                <td class="">
                    <a href="<?php echo base_url(); ?>admin/channel_report/<?php echo $owner; ?>?video_id=<?php echo $video["video_id"]; ?>" ><b>Report</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/edit_video/<?php echo $video["video_id"]; ?>/<?php echo $owner; ?>" ><b>Edit video</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/delete_video/<?php echo $video["video_id"]; ?>/<?php echo $owner; ?>" ><b>Edit video</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/share/<?php echo $owner; ?>/<?php echo $video["video_id"]; ?>" ><b>Share</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/like/<?php echo $owner; ?>/<?php echo $video["video_id"]; ?>" ><b>Like</b></a><br/>

                </td>

            </tr>
            <?php endforeach; ?>
    </table>
	<div class="pagination">
	<?php echo $pagination; ?>
	</div>
    <input type="hidden" id="channel" name="channel" value="<?php echo $channel; ?>" />
    <input type="hidden" id="owner" name="owner" value="<?php echo $owner; ?>" />
    <?php echo form_close(); ?>
</center>