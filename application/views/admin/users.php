<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
    $(document).ready(function(){
		$('.autocomplete').autocomplete();

		$(".restore").click(function() {
			$("#by_name").submit();
		});

		$("#by_name input[type=submit]").click(function() {
			var item_selected = $("#by_name input#name-filter").val();
			var by_name = $("#by_name input#name-filter").val() != "" ? $("#by_name input#name-filter").val() : "all";
			var country = $("#country select#country-filter").val() != "" ? $("#country select#country-filter").val() : "all";
			var category = $("#category select#category-filter").val() != "" ? $("#category select#category-filter").val() : "all";
			var gender = $("#users_by_sex select#gender-filter").val() != "" ? $("#users_by_sex select#gender-filter").val() : "all";
			if (item_selected != "") {
				var action = $("#by_name").attr("action");
				$("#by_name").attr("action", action + "/"
					+ encodeURIComponent(by_name) + "/"
					+ "all/"
					+ encodeURIComponent(country) + "/"
					+ encodeURIComponent(category) + "/"
					+ encodeURIComponent(gender)
				);
				$("#by_name").submit();
				return true;
			}
			return false;
		});

		$("#country input[type=submit]").click(function() {
			var item_selected = $("#country select#country-filter").val();
			var by_name = $("#by_name input#name-filter").val() != "" ? $("#by_name input#name-filter").val() : "all";
			var country = $("#country select#country-filter").val() != "" ? $("#country select#country-filter").val() : "all";
			var category = $("#category select#category-filter").val() != "" ? $("#category select#category-filter").val() : "all";
			var gender = $("#users_by_sex select#gender-filter").val() != "" ? $("#users_by_sex select#gender-filter").val() : "all";
			if (item_selected != "") {
				var action = $("#country").attr("action");
				$("#country").attr("action", action + "/"
					+ encodeURIComponent(by_name) + "/"
					+ "all/"
					+ encodeURIComponent(country) + "/"
					+ encodeURIComponent(category) + "/"
					+ encodeURIComponent(gender)
				);
				$("#country").submit();
				return true;
			}
			return false;
		});

		$("#category input[type=submit]").click(function() {
			var item_selected = $("#category select#category-filter").val();
			var by_name = $("#by_name input#name-filter").val() != "" ? $("#by_name input#name-filter").val() : "all";
			var country = $("#country select#country-filter").val() != "" ? $("#country select#country-filter").val() : "all";
			var category = $("#category select#category-filter").val() != "" ? $("#category select#category-filter").val() : "all";
			var gender = $("#users_by_sex select#gender-filter").val() != "" ? $("#users_by_sex select#gender-filter").val() : "all";
			if (item_selected != "") {
				var action = $("#category").attr("action");
				$("#category").attr("action", action + "/"
					+ encodeURIComponent(by_name) + "/"
					+ "all/"
					+ encodeURIComponent(country) + "/"
					+ encodeURIComponent(category) + "/"
					+ encodeURIComponent(gender)
				);
				$("#category").submit();
				return true;
			}
			return false;
		});

		$("#users_by_sex input[type=submit]").click(function() {
			var item_selected = $("#users_by_sex select#gender-filter").val();
			var by_name = $("#by_name input#name-filter").val() != "" ? $("#by_name input#name-filter").val() : "all";
			var country = $("#country select#country-filter").val() != "" ? $("#country select#country-filter").val() : "all";
			var category = $("#category select#category-filter").val() != "" ? $("#category select#category-filter").val() : "all";
			var gender = $("#users_by_sex select#gender-filter").val() != "" ? $("#users_by_sex select#gender-filter").val() : "all";
			if (item_selected != "") {
				var action = $("#users_by_sex").attr("action");
				$("#users_by_sex").attr("action", action + "/"
					+ encodeURIComponent(by_name) + "/"
					+ "all/"
					+ encodeURIComponent(country) + "/"
					+ encodeURIComponent(category) + "/"
					+ encodeURIComponent(gender)
				);
				$("#users_by_sex").submit();
				return true;
			}
			return false;
		});
	});
</script>
<center>
    <table>
        <tr>

            <td valign="top">
				<?php echo form_open('admin/users', array('name' => 'by_name', 'id' => 'by_name', 'method' => 'get')); ?>
					Name:
					<?php echo form_input(array("id" => "name-filter", "name" => "search-name", "value" => $name, "class" => "imp-form", "data-source" => "admin/search?search=")); ?>
					<?php echo form_submit('s', 'Filter'); ?>

                <?php echo form_close(); ?>
            </td>
            <td width="20"></td>
            <td valign="top">
                <?php
                echo form_open('admin/users', array('name' => 'country', 'id' => 'country', 'method' => 'get'));

                echo "Country:";
                echo form_dropdown('co', $country_list, $country, 'class="select_style" id="country-filter"');
                echo form_submit('s', 'Filter');

                echo form_close();
                ?>
            </td>
            <td width="20"></td>

            <td valign="top">
				<?php echo form_open('admin/users', array("name" => "category", "id" => "category", "method" => "get")); ?>
                    Category:
                    <?php echo form_dropdown('c', $category_options, $category, 'class="select_style" id="category-filter"'); ?>
					<?php echo form_submit("s", "Filter"); ?>
                <?php echo form_close(); ?>
            </td>

			<td width="20"></td>
			<td valign="top">
				<?php echo form_open('admin/users', array('name' => 'users_by_sex', 'id' => 'users_by_sex', 'method' => 'get')); ?>
                    Sec:
					<?php echo form_dropdown('g', array("" => "-- Select --", "Male" => "Male", "Female" => "Female"), $gender, 'class="select_style" id="gender-filter"'); ?>
					<?php echo form_submit('s', 'Filter'); ?>
                <?php echo form_close(); ?>
			</td>
			<td width="20"></td>
			<td width="20"><a class="restore" alt="restore" title="Click for restore selects"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a></td>
        </tr>
    </table>
    <br/>
    <br/>
    <?php echo form_open("#", array('class' => 'email', 'id' => 'myForm', 'name' => 'myForm')); ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left"><a href="">ID</a></th>
            <th class="table-header-repeat line-left"><a href="">Username</a></th>
            <th class="table-header-repeat line-left"><a href="">Youtube Channel</a></th>
            <th class="table-header-repeat line-left"><a href="">Category</a></th>
            <th class="table-header-repeat line-left"><a href="">Country</a></th>
            <th class="table-header-repeat line-left"><a href="">Sex</a></th>

            <th class="table-header-repeat line-left" width="200"><a href="">Tasks</a></th>

        </tr>
		<?php if (!empty($users)) : ?>
			<?php foreach ($users as $key => $row) : ?>
				<tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
					<td><?php echo $row->id; ?></td>
					<td><?php echo $row->user_login; ?></td>
					<td><a href="<?php echo base_url(); ?>video/videos/<?php echo $row->id; ?>"> <b><?php echo $row->youtube_channels; ?></b></a></td>
					<td><?php echo isset($row->youtube_content_category) ? $row->youtube_content_category : ""; ?></td>
					<td><?php echo isset($row->country) ? $row->country : ""; ?></td>
					<td><?php echo isset($row->sex) ? $row->sex : ""; ?></td>

					<td >
						<a href="<?php echo base_url(); ?>video/videos/<?php echo $row->id; ?>" ><b>Show videos</b></a><br/>
						<a href="<?php echo base_url(); ?>video/playlist/<?php echo $row->id; ?>" ><b>Show playlist</b></a><br/>
						<a href="<?php echo base_url(); ?>admin/upload/<?php echo $row->id; ?>" ><b>Upload video</b></a><br/>
					</td>
				</tr>
			 <?php endforeach; ?>
		<?php endif; ?>
    </table>
	<div id="pagination">
		<?php echo $pagination; ?>
	</div>
    <?php echo form_close(); ?>
    <br>
    <br>

</center>
