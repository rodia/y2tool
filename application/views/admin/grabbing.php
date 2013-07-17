<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
    $(document).ready(function(){
		$("#select-user input[type=submit]").click(function() {
			var item_selected = $("#select-user select#select-item-user").val();
			if (item_selected != "") {
				var action = $("#select-user").attr("action");
				var categories = $("#select-category select#select-item-category").val();
				$("#select-user").attr("action", action + "/" + item_selected + ((categories != '' && categories != undefined) ? "/" + categories : ""));
				$("#select-user").submit();
			}
		});

		$("#select-category input[type=submit]").click(function() {
			var item_selected = $("#select-category select#select-item-category").val();
			if (item_selected != "") {
				var action = $("#select-category").attr("action");
				var user = $("#select-user select#select-item-user").val();
				$("#select-category").attr("action", action + (user != '' ? "/" + user : "/all") + "/" + item_selected);
				$("#select-category").submit();
			}
		});

		$(".restore").click(function() {
			$("#select-category select#select-item-category").val("");
			$("#select-category").submit();
		});

        $("#myForm").submit(function(){
            if (!isCheckedById("ids")){
                alert ("Please select at least one checkbox");//sincronización de canales de usuarios
                return false;
            }else{
                return true; //submit the form
            }
        });
    });
</script>
<center>

	<table>
		<tr>
			<td class="align-right line-left">
				<?php echo form_open('video/grabbing', array('id' => 'select-user', 'name' => 'select-user', 'method' => 'get')); ?>
				<a><b>User: </b></a> <select id="select-item-user" class="select_style" name="select-item-user">
					<option value="">- Select -</option>
					<?php foreach($users as $item):?>
					<option value="<?php echo $item->id;?>"<?php echo $item->id == $user ? " selected=\"selected\"" : "";?>><?php echo $item->name;?></option>
					<?php endforeach;?>
				</select>
				<?php echo form_submit("filter-user", "Filter"); ?>
				<?php echo form_close();?>
			</td>
			<td class="align-left line-left">
				<?php echo form_open('video/grabbing', array('id' => 'select-category', 'name' => 'select-category', 'method' => 'get')); ?>
				<a><b>Category: </b></a>
				<?php echo form_dropdown('category_name', $categories, $category, 'id="select-item-category" class="select_style"'); ?>
				<?php echo form_submit("filter-user", "Filter"); ?>
				<a class="restore" alt="admin_name" title="Click for restore selects"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a>
				<?php echo form_close();?>
			</td>
<!--            <td class="align-right line-left">
				<?php echo form_open('video/grabbing', array('id' => 'search', 'name' => 'search', 'method' => 'get')); ?>
				<a><b>Search User</b></a> <input type="text" name="q" id="q" class="inp-form inp-admin" size="6" /> <input name="s" type="submit" class="form-submit" value="Search" />
				<?php echo form_close();?>
			</td>-->
        </tr>
    </table>

    <?php echo form_open('video/s1_grabbing', array('id' => 'myForm', 'name' => 'myForm')); ?>
    <table border="0" class="filter-table">
        <tr>
            <td>Choose a video for grabbing and press Next
                <?php echo form_submit('mysubmit', 'Next >', 'class="form-submit"'); ?>
            </td>
        </tr>
    </table>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <?php if (!empty($msg)) { ?>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
        <?php } ?>
        <tr>
            <th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll"></th>
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left"><a href="">Preview</a></th>
        </tr>
        <?php
        $c = 0;
        if (!empty($videos)) {
            for ($i = 0; $i < sizeof($videos); $i++) {
                $c++;
                ?>
                <tr <?php if ($c % 2)
            echo "class=\"alternate-row\""; ?>>
                    <td><input type="checkbox" name="ids[]" value="<?php echo $videos[$i]['video_id']; ?>"></td>
                    <td><?php echo substr($videos[$i]['title'], 0, 80); ?></td>
                    <td>
                        <img src="<?php echo $videos[$i]['thumbnail']; ?>" class="borderPhoto" style="height:33px;width:50px;"  />
					</td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
	<div class="pagination">
	<?php echo $pagination; ?>
	</div>
    <?php echo form_close(); ?>


</center>
