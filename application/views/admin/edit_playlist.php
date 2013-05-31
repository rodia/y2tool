<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
    <form action="<?php echo base_url(); ?>video/saveplaylist" method="post">
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Edit Playlist</a></th>
                </tr> 
                <tr class="alternate-row">
                    <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
                </tr>
                <tr>
                    <td align="right">
                        <h2>Title *</h2>
                    </td>
                    <td>                        
                        <input class="inp-form" size="60px" type="text" name="play_title" id="video_title" value="<?php echo $playlistListEntry->title->text; ?>"/>
                        <span><?php echo form_error('play_title'); ?></span>
                    </td>    
                </tr>
                <tr class="alternate-row">
                    <td align="right">
                        <h2>Description:</h2>
                    </td>
                    <td>
                        <textarea class="form-textarea" name="play_description"><?php echo $playlistListEntry->description->text ?></textarea>
                    </td>
                </tr>               
                <tr>
                    <td align="center" colspan="2">                   
                        <input type="hidden" name="channel" value="<?php echo $channel; ?>"/>
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
                        <input type="hidden" name="playlist_id" value="<?php echo $playlistListEntry->playlistId; ?>"/>
                        <input type="submit" class="form-submit" value="Update" id="button" name="submit">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</center>