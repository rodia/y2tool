<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
    <form method="post" action="<?php echo base_url(); ?>video/addvideo" >
        <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
            <tr>
                <th class="table-header-repeat line-left" colspan="2"><a href="#">Add video to Playlist</a></th>
            </tr>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
            <tr>
                <td>
                    <h2>Youtube video id:</h2>
                </td>
                <td>
                    <input class="inp-form" size="60" type="text" name="video_id" value="" placeholder="Enter youtube video id" />
                    <span><?php echo form_error('video_id'); ?></span><br/>
                    <span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
                    
                    <input type="hidden" name="type" value="youtube"  />
                    <input type="hidden" name="videoFeedID" value="<?php echo $videoFeedID; ?>"  />
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"  />
                    <input type="hidden" name="channel" value="<?php echo $channel; ?>"  />
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input class="form-submit" type="submit" value="Add video" id="button" name="submit" />
                </td>
            </tr>
        </table>
    </form>
</center>