<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>

    <table>
        <tr>
            <td valign="top">
                <form action="<?php echo base_url(); ?>video/new_playlist" method="post" name="form1" id="form1">                                  
                    <input  type="hidden" name="channel" value="<?php echo $channel; ?>"/>
                    <input  type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
                    <input  class="form-submit" type="submit" value="Create Playlist"/>
                </form>
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
            <th class="table-header-check table-header-repeat line-left minwidth-1"><a href="">#</a>	</th>
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Description</a></th>   
            <th class="table-header-repeat line-left" width="200"><a href="">Options</a></th>
        </tr>
        <?php
        $c = 0;
        foreach ($playlistListFeed as $playlistListEntry) {
            $c++;
            ?>
            <tr <?php if ($c % 2)
            echo "class=\"alternate-row\""; ?>>
                <td><?php echo $c; ?></td>
                <td>
                    <p><?php echo $playlistListEntry->title->text; ?></p>
                    <h3></h3>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style ">
                        <a class="addthis_button_preferred_1" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry->playlistId; ?>" addthis:title="<?php echo $playlistListEntry->title->text; ?>"></a>
                        <a class="addthis_button_preferred_2" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry->playlistId; ?>" addthis:title="<?php echo $playlistListEntry->title->text;?>"></a>                        
                        <a class="addthis_button_preferred_3" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry->playlistId; ?>" addthis:title="<?php echo $playlistListEntry->title->text; ?>"></a>
                        <a class="addthis_button_preferred_4" addthis:url="https://www.youtube.com/playlist?list=<?php echo $playlistListEntry->playlistId; ?>" addthis:title="<?php echo $playlistListEntry->title->text; ?>"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?domready=1#pubid=/*Your pubid*/"></script>
                    <!-- AddThis Button END -->
                </td>
                <td><?php echo $playlistListEntry->description->text; ?></td>
                <td class="">
                    <a href="<?php echo base_url(); ?>video/videolist/<?php echo $user_id; ?>/<?php echo $playlistListEntry->playlistId; ?>" ><b>Show videos</b></a><br/>
                    <a href="<?php echo base_url(); ?>video/add_video2/<?php echo $user_id; ?>/<?php echo $playlistListEntry->playlistId; ?>" ><b>Add video</b></a>                    <br/>
                    <a href="<?php echo base_url(); ?>video/edit_playlist/<?php echo $user_id; ?>/<?php echo $playlistListEntry->playlistId; ?>" ><b>Edit playlist</b></a>                    <br/>
                    <a href="<?php echo base_url(); ?>video/delplaylist/<?php echo $user_id; ?>/<?php echo $playlistListEntry->playlistId; ?>" ><b>Remove playlist</b></a>                   <br/> 
                </td>
            </tr>
            <?php
        }
        ?>
    </table>


</center>