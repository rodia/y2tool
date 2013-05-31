<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$category_id = 2;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#myForm").submit(function(){
            if (!isCheckedById("users_ids")){
                alert ("Please select at least one channel");//sincronización de canales de usuarios
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
    $atribbutes = array('id' => 'myForm', 'name' => 'myForm');
    echo form_open('video/s2_grabbing', $atribbutes);
    $ids = "";
    if (!empty($video_ids)) {
        for ($i = 0; $i < sizeof($video_ids); $i++)
            if ($i != sizeof($video_ids) - 1)
                $ids .= $video_ids[$i] . "###";
            else
                $ids .= $video_ids[$i];
    }
    ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th width="450">
                <?php
                echo form_submit('submit', 'Save', 'class="form-submit"');
                ?>
            </th>
        </tr>
    </table>

    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left" colspan="2"><a href="#">Playlist Data</a></th>
        </tr>
        <tr class="alternate-row">
            <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
        </tr>
        <tr class="alternate-row">
            <td><h2>Title *</h2></td>
            <td><input class="inp-form inp-form2" size="60px" type="text" name="play_title" id="play_title" value="<?php echo $play_title; ?>"/>
                <span><?php echo form_error('play_title'); ?></span></td>
        </tr>
        <tr>
            <td><h2>Description:</h2></td>
            <td><textarea class="form-textarea" name="play_description"><?php echo $play_description; ?></textarea></td>
        </tr>

    </table>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll"></th>
            <th class="table-header-repeat line-left"><a href="">Channel</a></th>
            <th class="table-header-repeat line-left"><a href="">Name</a></th>
        </tr>
        <?php
        $c = 0;
        if (!empty($users)) {
            foreach ($users as $user) {
                $c++;
                ?>
                <tr <?php if ($c % 2)
            echo "class=\"alternate-row\""; ?>>
                    <td><input id="usr<?php echo $user->id; ?>" type="checkbox" name="users_ids[]" value="<?php echo $user->id; ?>"></td>
                    <td><label for="usr<?php echo $user->id; ?>"><?php echo $user->youtube_channels ?></label></td>
                    <td><label for="usr<?php echo $user->id; ?>"><?php echo $user->lastname . " " . $user->firstname; ?></label></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <input type="hidden" name="video_ids" value="<?php echo $ids; ?>"/>
    <?php echo form_close(); ?>

</center>
