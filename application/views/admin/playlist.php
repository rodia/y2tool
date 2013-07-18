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
	<?php if (isset($success)) show_messages($success, $msg, $type); ?>
    <table>
        <tr>
            <td valign="top">
				<?php echo form_open("video/new_playlist/{$user_id}/{$channel}", array("method" => "post", "name" => "form1", "id" => "form1")); ?>
					<?php echo form_submit(array("class" => "form-submit"), "Create Playlist"); ?>
                <?php echo form_close(); ?>
            </td>
            <td width="50"></td>
        </tr>
    </table>
    <br/>
    <br/>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <?php if(!empty($msg)){?>
        <tr class="alternate-row" colspan="2">
            <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
        </tr>
        <?php }?>
        <tr>
            <th class="table-header-check table-header-repeat align-left"><a href="">#</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Title</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Description</a></th>
            <th class="table-header-repeat line-left minwidth-1" style="width: 130px;"><a href="">Options</a></th>
        </tr>
        <?php foreach ($playlistListFeed as $key => $playlistListEntry) : ?>
            <tr<?php echo $key % 2 == 0 ? " class=\"alternate-row\"" : ""; ?>>
                <td><?php echo $key +1; ?></td>
                <td>
                    <p><?php echo $playlistListEntry["snippet"]["title"]; ?></p>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style ">
                        <a class="addthis_button_preferred_1" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry["id"]; ?>" addthis:title="<?php echo $playlistListEntry["snippet"]["title"]; ?>"></a>
                        <a class="addthis_button_preferred_2" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry["id"]; ?>" addthis:title="<?php echo $playlistListEntry["snippet"]["title"]; ?>"></a>
                        <a class="addthis_button_preferred_3" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry["id"]; ?>" addthis:title="<?php echo $playlistListEntry["snippet"]["title"]; ?>"></a>
                        <a class="addthis_button_preferred_4" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry["id"]; ?>" addthis:title="<?php echo $playlistListEntry["snippet"]["title"]; ?>"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?domready=1#pubid=/*Your pubid*/"></script>
                    <!-- AddThis Button END -->
                </td>
                <td><?php echo $playlistListEntry["snippet"]["description"]; ?></td>
                <td class="">
                    <a href="<?php echo base_url(); ?>video/videolist/<?php echo $user_id; ?>/<?php echo $playlistListEntry["id"]; ?>" ><b>Show videos</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/add_video_playlist/<?php echo $user_id; ?>/<?php echo $playlistListEntry["id"]; ?>" ><b>Add video</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/edit_playlist/<?php echo $user_id; ?>/<?php echo $playlistListEntry["id"]; ?>" ><b>Edit playlist</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/delplaylist/<?php echo $user_id; ?>/<?php echo $playlistListEntry["id"]; ?>" ><b>Remove playlist</b></a><br/>
                </td>
            </tr>
            <?php endforeach; ?>
    </table>

</center>