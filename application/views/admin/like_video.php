<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>
    <form action="<?php echo base_url(); ?>video/like" method="post">
        <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header-repeat line-left"><a href="#">Like video</a></th>
                </tr> 
                <tr>
                    <td align="right">
                        <h2>Video</h2>
                    </td>
                    <td>
                        <?php
                        $videoThumbnails = $videoEntry->getVideoThumbnails();
                        $videoThumbnail = $videoThumbnails[0];
                        ?>
                        <a href="<?php echo base_url(); ?>video/view/<?php echo $videoEntry->getVideoId(); ?>" >
                            <img src="<?php echo $videoThumbnail["url"]; ?>" class="borderPhoto" style="height:100px;width:150px;"  />
                        </a>
                    </td>  
                </tr>
                <tr>
                    <td align="right">
                        <h2>User:</h2>
                    </td>
                    <td>                        
                        <select class="select_style" name="user_id" id="user_id">          
                            <?php foreach ($users as $row) { ?>
                                <option value="<?php echo $row->id; ?>"><?php echo $row->lastname . " " . $row->firstname; ?></option>                                                                                                            
                            <?php } ?>
                        </select>
                    </td>    
                </tr>

                <tr>
                    <td align="center" colspan="2">                   
                        <input type="hidden" name="video_id" value="<?php echo $videoEntry->getVideoId(); ?>"/>
                        <input type="hidden" name="channel" value="<?php echo $channel; ?>"/>
                        <input type="submit" class="form-submit" value="Like" id="button">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</center>