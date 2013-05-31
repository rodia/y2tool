<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<center>
    <table border="0" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left col-date"><a href="">Date</a></th>
            <th class="table-header-repeat line-left col-admin"><a href="">Admin</a></th>
            <th class="table-header-repeat line-left col-subscribers-in"><a href="">Subs</a></th>
            <th class="table-header-repeat line-left col-like-in"><a href="">Likes</a></th>
            <th class="table-header-repeat line-left col-views-in" width="50"><a href="">Views</a></th>
            <th class="table-header-repeat line-left col-action-taken"><a href="">Task</a></th>
            <th class="table-header-repeat line-left col-description"><a href="">Description</a></th>

        </tr>
        <?php
        $c = 0;
        if (!empty($logs)) {
            foreach ($logs as $row) {
                $c++;
                ?>
                <tr <?php if ($c % 2) echo "class=\"alternate-row\""; ?>>
                    <td><abbr title="<?php echo $row->registered_date; ?>"><?php echo date("d/m/y", strtotime($row->registered_date));?></abbr></td>
                    <td><?php echo $row->admin; ?></td>
                    <td class="number"><?php echo $row->subs; ?></td>
                    <td class="number"><?php echo $row->likes; ?></td>
                    <td class="number"><?php echo $row->views; ?></td>
                    <td><?php echo $row->task; ?></td>
                    <td><?php echo $video_model->print_desc($row->task_id, $row->admin, $row->task, $row->video_id, $row->channel, $row->who); ?></td>
                    <!--                    //print_desc($logs[$j]['task_id'], $logs[$j]['admin'], $logs[$j]['task'], $logs[$j]['video_id'], $logs[$j]['channel'],  $logs[$j]['reviewed_by'])-->
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <br>
    <br>


</center>
