<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$category_id = 2;
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
    <?php
    $attributes = array('id' => 'myForm', 'name' => 'myForm');
    echo form_open('video/synchronize', $attributes);
    ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th></th>            
            <th></th>
            <th></th>  
            <th></th>
            <th></th>
            <th  width="500">                
                <?php
                echo form_submit('mysubmit', 'Syncronize', 'class="form-submit"');
                ?>
            </th>
        </tr>
    </table>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <tr>            
            <th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll"></th>
            <th class="table-header-repeat line-left"><a href="">Name</a></th>
            <th class="table-header-repeat line-left"><a href="">Channel</a></th>
            <th class="table-header-repeat line-left"><a href="">Category</a></th>                                 
        </tr>
        <?php
        $c = 0;
        if (!empty($users)) {
            foreach ($users as $row) {
                $c++;
                ?>
                <tr <?php if ($c % 2)
            echo "class=\"alternate-row\""; ?>>
                    <td><input type="checkbox" name="ids[]" value="<?php echo $row->id ?>"></td>                                    
                    <td><?php echo $row->lastname . " " . $row->firstname; ?></td>  
                    <td><?php echo $row->youtube_channels ?></td>
                    <td><?php echo $row->youtube_content_category; ?></td>

                </tr>
                <?php
            }
        }
        ?>
    </table>
    <?php echo form_close(); ?>


</center>
