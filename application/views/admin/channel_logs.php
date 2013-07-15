<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<script type="text/javascript">
    $(document).ready(function(){
        $("#myForm select").change(function() {
			//if ($(this).val() !== "")
				$("#myForm").submit();
		});

		$(".restore").click(function() {
			$("#" + $(this).attr("alt")).val("");
			$("#myForm").submit();
		});
	});
</script>

<script>
$(function() {
	var startDate = $("#start-date").datepicker({
		dateFormat: 'yy-mm-dd'
	});
	var endDate = $("#end-date").datepicker({
		dateFormat: 'yy-mm-dd'
	});

});
</script>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	$title
)); ?>
<center>
	<?php echo form_open('admin/channel_report/' . $user_id, array('class' => 'forms', 'id' => 'myForm', 'name' => 'myForm', 'method' => 'get')); ?>
	<table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left minwidth-1"><a><b>Filters</b></a></th>
            <th class="table-header-repeat line-left"><a><b>Admin Name</b></a> <select name="admin_name" id="admin_name">
				<option value="">-- Select --</option>
				<?php
				foreach ($admin_name as $item):
					$selected = $current_admin_name == $item ? " selected=\"selected\"" : "";
					echo "<option value=\"{$item}\"{$selected}>{$item}</option>";
				endforeach;
				?></select> <a class="restore" alt="admin_name" title="Click for restore select"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a></th>
            <th class="table-header-repeat line-left"><a><b>Video ID</b></a> <select name="video_id" id="video_id">
					<option value="">-- Select --</option>
				<?php
				foreach ($video_id as $item):
					$selected = $current_video_id == $item ? " selected=\"selected\"" : "";

					echo "<option value=\"{$item}\"{$selected}>{$item}</option>";
				endforeach;
				?></select> <a class="restore" alt="video_id" title="Click for restore select"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a></th>
            <th class="table-header-repeat line-left"><a><b>Action Taken</b></a> <select name="action_taken" id="action_taken">
					<option value="">-- Select --</option>
				<?php
				foreach ($action_taken as $item):
					$selected = $current_action_taken == $item ? " selected=\"selected\"" : "";
					echo "<option value=\"{$item}\"{$selected}>{$item}</option>";
				endforeach;
				?>
				</select> <a class="restore" alt="action_taken" title="Click for restore select"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a></th>
        </tr>
    </table>
	<?php echo form_close(); ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
		<tr>
			<td colspan="10">
				<h4>Report for last 30 day:</h4>
				<?php echo form_label("Start date:", "start-date"); ?> <?php echo form_input(array("name" => "start-date", "id" => "start-date"), $startDate); ?>
				<?php echo form_label("End date", "end-date"); ?> <?php echo form_input(array("name" => "end-date", "id" => "end-date"), $endDate); ?>
			</td>
		</tr>
        <tr>
            <th class="table-header-repeat line-left col-date"><a href="">Date</a></th>
            <th class="table-header-repeat line-left col-admin"><a href="">Admin Name</a></th>
            <th class="table-header-repeat line-left col-video-id"><a href="">Video ID</a></th>
            <th class="table-header-repeat line-left col-action-taken"><a href="">Action Taken</a></th>
            <th class="table-header-repeat line-left col-description"><a href="">Action Description</a></th>
            <th class="table-header-repeat line-left col-like-at"><a href="">No. of Likes at Action</a></th>
            <th class="table-header-repeat line-left col-like-in"><a href="">Current No. of Likes</a></th>
            <th class="table-header-repeat line-left col-subscribers-at"><a href="">No. of subscribers at Action</a></th>
            <th class="table-header-repeat line-left col-subscribers-in"><a href="">No. of Views at Action</a></th>
            <th class="table-header-repeat line-left col-current-view"><a href="">No. Current Views</a></th>
        </tr>
        <?php if ( ! empty($logs)) : ?>
            <?php foreach ($logs as $key => $row) : ?>
                <tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
                    <td><abbr title="<?php echo $row->registered_date; ?>"><?php echo date("d/m/y", strtotime($row->registered_date));?></abbr></td><!-- Date -->
                    <td><?php echo $row->admin; ?></td><!-- Admin Name -->
                    <td><?php echo $row->video_id; ?></td><!-- Video ID -->
                    <td><?php echo $row->description; ?></td><!-- Action Taken -->
					<td><?php echo print_desc($row->task_id, $row->admin, $row->task, $row->video_id, $row->channel, $row->who); ?></td><!-- Action Description -->
					<td class="number"><?php echo $row->likes; ?></td><!-- No. of Likes at Action -->
                    <td class="number"><?php ?></td><!-- Current No. of Likes -->
                    <td class="number"><?php echo $row->subs; ?></td><!-- No. of subscribers at Action -->
                    <td class="number"><?php echo $row->views; ?></td><!-- No. of Views at Action -->
                    <td class="number"><?php ?></td><!-- No. Current Views -->

                </tr>
			<?php endforeach; ?>
		<?php endif; ?>
    </table>
    <br>
    <br>
</center>
