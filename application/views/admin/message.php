<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
    <?php echo form_open("video/share_video")?>
    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Message</a></th>
                </tr>
                <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
                <tr >
                    <td align="right">
                        <h2>Message *</h2>
                    </td>
                    <td>
                        <textarea name="message" cols=30><?php echo $message; ?></textarea>
                        <span><?php echo form_error('message'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
                        <input class="form-submit" type="submit" name="submit" value="Submit"/>
                        <input type="hidden" name="video_id" value="<?php echo $video_id; ?>"/>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php echo form_close(); ?>
</center>
