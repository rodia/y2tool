<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
    <form enctype="multipart/form-data" action="<?php echo base_url(); ?>video/newplay" method="post">
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">New Playlist</a></th>
                </tr> 
                <tr class="alternate-row">
                    <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title:</h2>
                    </td>
                    <td>                        
                        <input class="inp-form" size="60px" type="text" name="play_title" id="play_title" value="<?php echo $play_title; ?>"/>
                        <span><?php echo form_error('play_title'); ?></span>
                    </td>    
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description:</h2>
                    </td>
                    <td>
                        <textarea class="form-textarea" name="play_description"><?php echo $play_description; ?></textarea>
                        <span><?php echo form_error('play_description'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">                   
                        <input type="hidden" name="channel" value="<?php echo $channel; ?>"/>
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
                        <input type="submit" name="submit" class="form-submit" value="Create" id="button">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</center>