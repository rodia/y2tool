<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#myForm").submit(function(){
            if (!isCheckedById("ids")){
                alert ("Please select at least one checkbox");//sincronización de canales de usuarios
                return false;
            }else{
                return true; //submit the form
            }
        });

        function isCheckedById(id){
            var checked = $("input[@id="+id+"]:checked").length;
            if (checked == 0){
                return false;
            }
            else{
                return true;
            }
        }
    });
</script>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	"video/playlist/{$user_id}" => "Playlist",
	$title
)); ?>
<center>
	<?php if (isset($success)) show_messages($success, $msg, $type); ?>
    <table>
        <tr>
            <td valign="top">
				<?php echo form_open("video/add_video/{$user_id}/{$videoFeedID}", array("method" => "post", "name" => "form1", "id" => "form1")); ?>
				<?php echo form_hidden("channel", $channel); ?>
				<?php echo form_hidden("user_id", $user_id); ?>
				<?php echo form_hidden("videoFeedID", $videoFeedID); ?>
				<?php echo form_submit(array("class" => "form-submit"), "Add Video"); ?>
                <?php echo form_close(); ?>
            </td>
            <td width="50">&nbsp;</td>
        </tr>
    </table>
    <br/>

    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <?php if (!empty($msg)) : ?>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th class="table-header-repeat line-left" width="30"><input type="checkbox" id="checkAll"></th>
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Description</a></th>
            <th class="table-header-repeat line-left" width="100"><a href="">Preview</a></th>
            <th class="table-header-repeat line-left"><a href="">Viewed</a></th>
            <th class="table-header-repeat line-left" width="100"><a href="">Options</a></th>
        </tr>
        <?php foreach ($playlistVideoFeed as $key => $videoEntry) : ?>
            <tr<?php echo $key % 2 == 0 ? " class=\"alternate-row\"" : ""; ?>>
                <td><input type="checkbox" name="ids[]" value="<?php echo $videoEntry["video_id"]; ?>" /></td>
                <td><?php echo $videoEntry["title"]; ?></td>
                <td><?php echo str_replace("<", "", substr($videoEntry["description"], 0, 100)); ?></td>

                <td align="center">
                    <a href="<?php echo base_url(); ?>video/view/<?php echo $videoEntry["video_id"]; ?>" >
                        <img src="<?php echo $videoEntry["thumbnail"]["url"]; ?>" class="borderPhoto" style="height:100px;width:150px;" />
                    </a>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style">
                        <a class="addthis_button_preferred_1" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry["video_id"]; ?>" addthis:title="<?php echo $videoEntry["title"]; ?>"></a>
                        <a class="addthis_button_preferred_2" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry["video_id"]; ?>" addthis:title="<?php echo $videoEntry["title"]; ?>"></a>
                        <a class="addthis_button_preferred_3" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry["video_id"]; ?>" addthis:title="<?php echo $videoEntry["title"]; ?>"></a>
                        <a class="addthis_button_preferred_4" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry["video_id"]; ?>" addthis:title="<?php echo $videoEntry["title"]; ?>"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?domready=1#pubid=/*Your pubid*/"></script>
                    <!-- AddThis Button END -->
                </td>
                <td class="align-left"><?php echo $videoEntry["view_count"]; ?></td>
                <td>
                    <a href="<?php echo base_url(); ?>video/share/<?php echo $videoEntry["video_id"]; ?>"><b>Share</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/delvideo/<?php echo $user_id; ?>/<?php echo $videoFeedID; ?>/<?php echo $videoEntry["video_id"]; ?>" onclick="return confirm('Are you sure to remove this video?');"><b>Remove Video</b></a>
                </td>
            </tr>
		<?php endforeach; ?>
    </table>
</center>