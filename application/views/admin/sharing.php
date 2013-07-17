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
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	"/video/videos/{$user}" => "Videos",
	$title
)); ?>
<center>
	<?php if ($this->input->get("msg")): ?>
	<div class="forgot-pwd success">
		<p><?php echo $this->input->get("msg"); ?></p>
	</div>
	<?php endif; ?>
	<table>
		<tr>
			<td class="align-right line-left">
				<?php echo form_open('video/sharing', array('id' => 'select-user', 'name' => 'select-user', 'method' => 'get')); ?>
				<a><b>User: </b></a> <select id="select-item-user" class="select_style" name="select-item-user">
					<option value="">- Select -</option>
					<?php foreach($users as $item):?>
					<option value="<?php echo $item->id;?>"<?php echo $item->id == $user ? " selected=\"selected\"" : "";?>><?php echo $item->name;?></option>
					<?php endforeach;?>
				</select>
				<?php echo form_submit("user_id", $user); ?>
				<?php echo form_submit("filter-user", "Filter"); ?>
				<?php echo form_close();?>
			</td>
			<td class="align-left line-left">
				<?php echo form_open('video/sharing', array('id' => 'select-category', 'name' => 'select-category', 'method' => 'get')); ?>
				<a><b>Category: </b></a>
				<?php echo form_dropdown('category_name', $categories, $category, 'id="select-item-category" class="select_style"'); ?>
				<?php echo form_submit("user_id", $user); ?>
				<?php echo form_submit("filter-user", "Filter"); ?>
				<a class="restore" alt="admin_name" title="Click for restore selects"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a>
				<?php echo form_close();?>
			</td>
<!--            <td class="align-right line-left">
				<?php echo form_open('video/sharing', array('id' => 'search', 'name' => 'search', 'method' => 'get')); ?>
				<a><b>Search User</b></a> <input type="text" name="q" id="q" class="inp-form inp-admin" size="6" /> <input name="s" type="submit" class="form-submit" value="Search" />
				<?php echo form_close();?>
			</td>-->
        </tr>
    </table>

    <?php echo form_open('video/s1_sharing', array('id' => 'myForm', 'name' => 'myForm')); ?>
    <table class="filter-table">
        <tr>
            <td>Choose a Videos and click in Share
                <?php echo form_submit('mysubmit', 'Share', 'class="form-submit"'); ?>
            </td>
        </tr>
    </table>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll"></th>
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left"><a href="">Preview</a></th>
        </tr>
        <?php if (!empty($videos)) : ?>
			<?php foreach ($videos as $key => $video) : ?>
                <tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
                    <td><input id="ids<?php echo $video['video_id']; ?>" type="checkbox" name="ids[]" value="<?php echo $video['video_id']; ?>"></td>
                    <td><label for="ids<?php echo $video['video_id']; ?>"><?php echo substr($video['title'], 0, 80); ?></label></td>
                    <td>
                        <img src="<?php echo $video['thumbnail']; ?>" class="borderPhoto" style="height:33px;width:50px;"  />
                    </td>
                </tr>
			<?php endforeach; ?>
        <?php endif; ?>
    </table>
	<div class="pagination">
	<?php echo $pagination; ?>
	</div>
	<?php echo form_hidden("user_id", $user); ?>
    <?php echo form_close(); ?>
</center>
