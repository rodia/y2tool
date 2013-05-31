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
<center>
    <?php
    $attributes = array('class' => 'forms', 'id' => 'myForm', 'name' => 'myForm');
    echo form_open('video/videoActions', $attributes);
    ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">

        <tr>

            <th class="table-header-repeat line-left minwidth-1"><a href="<?php echo base_url(); ?>admin/channel_report/<?php echo $channel; ?>"><b>Report Current Channel</b></a></th>
            <th class="table-header-repeat line-left" width="300"><?php
        echo "<a>Bulk Actions:</a>";
        $users_options = array();
        foreach ($users as $row) {
            $users_options[$row->id] = $row->lastname . " " . $row->firstname;
        }
        $selected2 = ($this->input->post('user_id')) ? $this->input->post('user_id') : '';
        echo form_dropdown('user_id', $users_options, $selected2, 'class="select_style"');
        ?></th>
            <th class="table-header-repeat line-left" width="220">
                <?php
                $tasks_options = array(
                    "liking_videos" => "Liking videos",
                );
                $selected2 = ($this->input->post('video_opt')) ? $this->input->post('video_opt') : '';
                echo form_dropdown('video_opt', $tasks_options, $selected2, 'class="select_style"');
                echo form_submit('mysubmit', 'Process');
                ?>
            </th>
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
        <?php
        $c = 0;
        foreach ($videos as $row) {
			$video_id = $row["video_id"];
            $c++;
            ?>
            <tr<?php echo ($c % 2) ? " class=\"alternate-row\"" : ""; ?>>
                <td><input type="checkbox" name="videos_ids[]" id="videos_ids" value="<?php echo $video_id; ?>" />  </td>
                <td><?php echo $row["title"]; ?></td>
                <td><?php echo str_replace("<", "", substr($row["description"], 0, 100)); ?></td>
                <td align="center"><?php echo $row["category"]; ?></td>
                <td align="center">
                    <img src="<?php echo $row["thumbnail"]; ?>" class="borderPhoto" style="height:100px;width:150px;"  />

                    <h3></h3>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style ">
                        <a class="addthis_button_preferred_1" addthis:url="https://www.youtube.com/watch?v=<?php echo $video_id; ?>" addthis:title="<?php echo $row["title"]; ?>"></a>
                        <a class="addthis_button_preferred_2" addthis:url="https://www.youtube.com/watch?v=<?php echo $video_id; ?>" addthis:title="<?php echo $row["title"]; ?>"></a>
                        <a class="addthis_button_preferred_3" addthis:url="https://www.youtube.com/watch?v=<?php echo $video_id; ?>" addthis:title="<?php echo $row["title"]; ?>"></a>
                        <a class="addthis_button_preferred_4" addthis:url="https://www.youtube.com/watch?v=<?php echo $video_id; ?>" addthis:title="<?php echo $row["title"]; ?>"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?domready=1#pubid=/*Your pubid*/"></script>
                    <!-- AddThis Button END -->
                </td>
                <td><?php echo $row["view_count"]; ?></td>
                <td class="">
                    <a href="<?php echo base_url(); ?>admin/channel_report/<?php echo $channel; ?>?video_id=<?php echo $video_id; ?>" ><b>Report</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/edit_video/<?php echo $video_id; ?>/<?php echo $owner; ?>" ><b>Edit video</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/share/<?php echo $video_id; ?>" ><b>Share</b></a><br/>
                </td>

            </tr>
            <?php
        }
        ?>
    </table>
	<div class="pagination">
	<?php echo $pagination; ?>
	</div>
    <input type="hidden" id="channel" name="channel" value="<?php echo $channel; ?>" />
    <input type="hidden" id="owner" name="owner" value="<?php echo $owner; ?>" />
    <?php echo form_close(); ?>
</center>