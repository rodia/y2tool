<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<center>
  
    <table border="0" width="1024" cellpadding="0" cellspacing="0" id="product-table">
        <tr>            
            <th class="table-header-repeat line-left" width="200"><a href="">Date</a></th>
            <th class="table-header-repeat line-left" width="200"><a href="">Admin</a></th>               
            <th class="table-header-repeat line-left" width="150"><a href="">Channel</a></th>                           
            <th class="table-header-repeat line-left" width="150"><a href="">Task</a></th>               
            <th class="table-header-repeat line-left" width="700"><a href="">Description</a></th>

        </tr>
        <?php
        $c = 0;
        if (!empty($logs)) {
            foreach ($logs as $row){
                $c++;
                ?>
                <tr <?php if ($c % 2)
            echo "class=\"alternate-row\""; ?>>
                    <td><?php echo $row->registered_date; ?></td>                
                    <td><?php echo $row->admin; ?></td>                
                    <td><?php echo $row->channel; ?></td>                                           
                    <td><?php echo $row->task; ?></td>                                                
                    <td><?php echo print_desc($row->task_id, $row->admin, $row->task,  $row->channel, $row->who, $row->playlistID); ?></td>                                    
                </tr>
                <?php
            }
        }

        function print_desc($opt, $admin, $task, $channel, $who, $playID) {            
            switch ($opt) {
                case 1: return "$admin did $task on video $video_id in the channel $channel using the channel $who ";
                    //Administrador did like_video on video LLHQbjjYYQo ​​in the channel castrorojasjaime using channel ()
                    break;
                case 2: return "";
                    break;
                case 3: return "$admin did $task on video $video_id in the channel $channel using the channel $who ";
                    break;
                case 4: return " ";
                    break;
                case 5: return "  ";
                    break;
                case 6: return "$admin did $task ($video_id) in the channel $channel ";
                    break;
                case 7: return "$admin did $task ($playID) in the channel $channel ";
                    break;
                case 8: return "$admin did $task ($playID) in the channel $channel ";
                    break;
                case 9: return "$admin did $task ($playID) in the channel $channel ";
                    break;
                default : return "";
                    break;
            }
        }
        ?>
    </table>
    <br>
    <br>


</center>
