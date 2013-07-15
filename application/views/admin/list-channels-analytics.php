<center>
	<?php if ($this->input->get("msg")): ?>
	<div class="forgot-pwd success">
		<p><?php echo $this->input->get("msg"); ?></p>
	</div>
	<?php endif; ?>
    <?php echo form_open('video/bulkActions', array('id' => 'bulk-form', 'name' => 'bulk-form', 'method' => 'post')); ?>
	<div class="step-user">
		<table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
			<tr>
				<th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll"></th>
				<th class="table-header-repeat line-left"><a href="">Task</a></th>
				<th class="table-header-repeat line-left"><a href="">Username</a></th>
				<th class="table-header-repeat line-left"><a href="">Youtube Channel</a></th>
				<!--<th class="table-header-repeat line-left"><a href="">Views</a></th>-->
				<th class="table-header-repeat line-left"><a href="">Category</a></th>
				<th class="table-header-repeat line-left"><a href="">Country</a></th>
				<th class="table-header-repeat line-left"><a href="">Sex</a></th>
				<th class="table-header-repeat line-left"><a href="">Bulk Actions</a></th>

			</tr>
			<?php if (!empty($users)): ?>
				<?php foreach ($users as $key => $row) : ?>
				<?php $checked = in_array($row->id, $hold_users); ?>
				<?php $hold_username[$row->id] = $row->user_login; ?>
					<tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
						<td><input type="checkbox" name="ids[]" value="<?php echo $row->id ?>" title="<?php echo $row->id; ?>"<?php echo $checked ? " checked=\"checked\"" : ""; ?>></td>
						<td>
						<a href="<?php echo base_url(); ?>video/videos/<?php echo $row->id; ?>" ><b>Show videos</b></a><br/>
						<a href="<?php echo base_url(); ?>video/playlist/<?php echo $row->id; ?>" ><b>Show playlist</b></a><br/>
						<a href="<?php echo base_url(); ?>admin/upload/<?php echo $row->id; ?>" ><b>Upload video</b></a><br/></td>
						<td title="<?php echo $row->id; ?>"><?php echo $row->user_login; ?></td>
						<td><?php echo $row->youtube_channels; ?></td>
						<!--<td><?php  ?></td>-->
						<td><?php echo $row->youtube_content_category; ?></td>
						<td><?php echo $row->country; ?></td>
						<td><?php echo $row->sex; ?></td>
						<td><button name="selecting" class="add-remove <?php echo $checked ? "remove" : "padding-button"; ?>" title="<?php echo $row->id; ?>"><?php echo $checked ? "Remove" : "Add"; ?></button></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</table>
		<div id="pagination">
			<?php //echo $pagination; ?>
		</div>
	</div>
		<div id="show-common-videos" class="step-video" style="display: none;">
		<a href="#" id="get-videos">Show videos of user selected</a>
		<div id="content-dinamic-show-videos"></div>
	</div>
	<?php echo form_hidden("selected-action"); ?>
	<?php echo form_hidden("users[]"); ?>
    <?php echo form_close(); ?>
</center>