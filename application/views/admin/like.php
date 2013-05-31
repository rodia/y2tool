<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$user_options = array();
foreach ($users as $row) {
    $user_options[$row->id] = $row->lastname . " " . $row->firstname;
}
?>

<center>
    <?php echo form_open("video/apply_like") ?>
    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
        <tbody>
            <tr>
                <th colspan="2" class="table-header-repeat line-left"><a href="#">Like</a></th>
            </tr>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
            <tr>
                <td align="right">
                    <h2>User:</h2>
                </td>
                <td>
                    <?php
                    $selected = ($this->input->post('user_id')) ? $this->input->post('user_id') : '';
                    echo form_dropdown('user_id', $user_options, $selected, 'class="select_style"');
                    ?>
                </td>
            </tr>
            <tr class="alternate-row">
                <td align="right">
                    <h2>Video ID *</h2>
                </td>
                <td>
                    <input class="inp-form" size="50" type="text" name="video_id" value="<?php echo $video_id; ?>" placeholder="Enter youtube video id" /><br>
                    <span><?php echo form_error('video_id'); ?></span>
                    <span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
                </td>
            </tr>

            <tr>
                <td align="center" colspan="2">
                    <input class="form-submit" type="submit" value="Like" name="submit"/>
                </td>
            </tr>
        </tbody>
    </table>
    <?php echo form_close(); ?>
</center>
