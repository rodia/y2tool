<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#myForm").submit(function(){
            if (!isCheckedById("ids")){
                alert ("Please select at least one checkbox");//sincronización de canales de usuarios
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
    <table>
        <tr>
            <td valign="top">
                <form action="<?php echo base_url(); ?>video/add_video" method="post" name="form1" id="form1">                                  
                    <input type="hidden" name="channel" value="<?php echo $channel ?>"/>
                    <input type="hidden" name="user_id" value="<?php echo $user_id ?>"/>
                    <input type="hidden" name="videoFeedID" value="<?php echo $videoFeedID ?>"/>
                    <input  class="form-submit" type="submit" value="Add video"/>
                </form>
            </td>
            <td width="50"></td>
        </tr>
    </table>
    <br/>
   
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <?php if (!empty($msg)) { ?>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
        <?php } ?>
        <tr>
            <th class="table-header-repeat line-left" width="30"><input type="checkbox" id="checkAll"></th>            
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Description</a></th>  
            <th class="table-header-repeat line-left" width="100"><a href="">Preview</a></th>
            <th class="table-header-repeat line-left"><a href="">Viewed</a></th>
            <th class="table-header-repeat line-left" width="100"><a href="">Options</a></th>
        </tr>
        <?php
        $c = 0;
        foreach ($playlistVideoFeed as $videoEntry) {
            $c++;
            ?>
            <tr <?php if ($c % 2)
            echo "class=\"alternate-row\""; ?>>
                <td><input type="checkbox" name="ids[]" value="<?php echo $videoEntry->getVideoId(); ?>" /></td>
                <td><?php echo $videoEntry->getVideoTitle(); ?></td>
                <td><?php echo str_replace("<", "", substr($videoEntry->getVideoDescription(), 0, 100)); ?></td>                

                <td align="center">
                    <?php
                    $videoThumbnails = $videoEntry->getVideoThumbnails();
                    $videoThumbnail = $videoThumbnails[0];
                    ?>
                    <a href="<?php echo base_url(); ?>video/view/<?php echo $videoEntry->getVideoId(); ?>" >
                        <img src="<?php echo $videoThumbnail["url"]; ?>" class="borderPhoto" style="height:100px;width:150px;"  />
                    </a>
                    <h3></h3>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style ">
                        <a class="addthis_button_preferred_1" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry->getVideoId(); ?>" addthis:title="<?php echo $videoEntry->getVideoTitle(); ?>"></a>
                        <a class="addthis_button_preferred_2" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry->getVideoId(); ?>" addthis:title="<?php echo $videoEntry->getVideoTitle(); ?>"></a>                        
                        <a class="addthis_button_preferred_3" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry->getVideoId(); ?>" addthis:title="<?php echo $videoEntry->getVideoTitle(); ?>"></a>
                        <a class="addthis_button_preferred_4" addthis:url="https://www.youtube.com/watch?v=<?php echo $videoEntry->getVideoId(); ?>" addthis:title="<?php echo $videoEntry->getVideoTitle(); ?>"></a>
                    </div>
                    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?domready=1#pubid=/*Your pubid*/"></script>
                    <!-- AddThis Button END -->
                </td>
                <td><?php echo $videoEntry->getVideoViewCount(); ?></td>

                <td class="">
                    <a href="<?php echo base_url(); ?>video/share/<?php echo $videoEntry->getVideoId(); ?>" ><b>Share</b></a><br/>                    
                    <a href="<?php echo base_url(); ?>video/delvideo/<?php echo $user_id; ?>/<?php echo $videoFeedID; ?>/<?php echo $videoEntry->getVideoId(); ?>" ><b>Remove Video</b></a>                    
                </td>
            </tr>
            <?php
        }
        ?>
    </table>    
    <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>" /> 
    <input type="hidden" id="user_id" name="playlist_id" value="<?php echo $videoFeedID; ?>" /> 
    <input type="hidden" id="user_id" name="channel" value="<?php echo $channel; ?>" /> 
    <?php echo form_close(); ?>

</center>