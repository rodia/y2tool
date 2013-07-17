<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$category_id = 2;

function getChannel($url) {
    $url_arr = explode("/", $url);
    if (sizeof($url_arr) > 0)
        return $url_arr[sizeof($url_arr) - 1];
    else
        return $url;
}
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
    });
</script>
<center>
    <?php
    $attributes = array('id' => 'myForm', 'name' => 'myForm');
    echo form_open('video/s2_favorites',$attributes);
    $ids = "";
    for ($i = 0; $i < sizeof($video_ids); $i++)
        if ($i != sizeof($video_ids) - 1)
            $ids .= $video_ids[$i] . "###";
        else
            $ids .= $video_ids[$i];
    ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th  width="450">
                <?php
                echo form_submit('mysubmit', 'Save', 'class="form-submit"');
                ?>
            </th>
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
                    <td><input type="checkbox" name="users_ids[]" value="<?php echo $user->id; ?>###<?php echo getChannel($user->youtube_channels) ?>"></td>
                    <td><?php echo $user->youtube_channels; ?></td>
                    <td><?php echo $user->lastname . " " . $user->firstname; ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <input type="hidden" name="video_ids" value="<?php echo $ids; ?>"/>
    <?php echo form_close(); ?>


</center>
