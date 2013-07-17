<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
    $(document).ready(function(){
		$("#select-user select#select-item-user").change(function() {
			if ($(this).val() != "") {
				var action = $("#select-user").attr("action");
				var categories = $("#select-category select#select-item-category").val();
				$("#select-user").attr("action", action + "/" + $(this).val() + ((categories != '' && categories != undefined) ? "/" + categories : ""));
				$("#select-user").submit();
			}
		});

		$("#select-category select#select-item-category").change(function() {
			if ($(this).val() != "") {
				var action = $("#select-category").attr("action");
				var user = $("#select-user select#select-item-user").val();
				$("#select-category").attr("action", action + (user != '' ? "/" + user : "/all") + "/" + $(this).val());
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
	<table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
		<tr>
			<th class="table-header-repeat align-right line-left">
				<?php
				$selecting = array('id' => 'select-user', 'name' => 'select-user', 'method' => 'get');
				echo form_open('video/favorites', $selecting);
				?>
				<a><b>Select User: </b></a> <select id="select-item-user" name="select-item-user">
					<option value="">- Select -</option>
					<?php foreach($users as $item):?>
					<option value="<?php echo $item->id;?>"<?php echo $item->id == $user ? " selected=\"selected\"" : "";?>><?php echo $item->name;?></option>
					<?php endforeach;?>
				</select>
				<?php echo form_close();?>
			</th>
			<th class="table-header-repeat align-left line-left">
				<?php
				$selecting_categories = array('id' => 'select-category', 'name' => 'select-category', 'method' => 'get');
				echo form_open('video/favorites', $selecting_categories);
				?>
				<a><b>Category: </b></a> <select id="select-item-category" name="select-item-category">
					<option value="">- Select -</option>
					<?php foreach($categories as $item):?>
					<option value="<?php echo $item;?>"<?php echo $item == $category ? " selected=\"selected\"" : "";?>><?php echo $item;?></option>
					<?php endforeach;?>
				</select> <a class="restore" alt="admin_name" title="Click for restore selects"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a>
				<?php echo form_close();?>
			</th>
			<th class="table-header-repeat align-left line-left">

			</th>
<!--            <th class="table-header-repeat align-right line-left">
				<?php
				$searching = array('id' => 'search', 'name' => 'search', 'method' => 'get');
				echo form_open('video/favorites', $searching);
				?>
				<a><b>Search User</b></a> <input type="text" name="q" id="q" class="inp-form inp-admin" size="6" /> <input name="s" type="submit" class="form-submit" value="Search" />
				<?php echo form_close();?>
			</th>-->
        </tr>
    </table>

    <?php
    $attributes = array('id' => 'myForm', 'name' => 'myForm');
    echo form_open('video/s1_favorites', $attributes);
    ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>
            </th>
            <th width="450">
                <?php
                echo form_submit('mysubmit', 'Next >', 'class="form-submit"');
                ?>
            </th>
        </tr>
    </table>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
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
                    <td><input id="fav<?php echo $videos[$i]['video_id']; ?>" type="checkbox" name="ids[]" value="<?php echo $videos[$i]['video_id']; ?>"></td>
                    <td><label for="fav<?php echo $videos[$i]['video_id']; ?>"><?php echo substr($videos[$i]['title'], 0, 80); ?></label></td>
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
