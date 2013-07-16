<script type="text/javascript">
$(document).ready(function(){
	$("#product-table tr").hover(function(){
			$(this).css( "cursor","pointer");
		},function(){
			$(this).css( "cursor","default");
			});

	$('#product-table tr').click(function() {
        var href = $(this).find("a").attr("href");
        if(href) {
            window.location = href;
        }
    });
});
</script>

<center>
	<?php if ($this->input->get("msg")): ?>
	<div class="forgot-pwd success">
		<p><?php echo $this->input->get("msg"); ?></p>
	</div>
	<?php endif; ?>
    <?php if($type=="channels"){?>
	<div class="step-user">
		<table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
			<tr>
				
				<th style="display:none;"></th>
				<th class="table-header-repeat line-left"><a href="">Username</a></th>
				<th class="table-header-repeat line-left"><a href="">Youtube Channel</a></th>
				<!--<th class="table-header-repeat line-left"><a href="">Views</a></th>-->
				<th class="table-header-repeat line-left"><a href="">Category</a></th>
				<th class="table-header-repeat line-left"><a href="">Country</a></th>
				<th class="table-header-repeat line-left"><a href="">Sex</a></th>
				

			</tr>
			<?php if (!empty($users)): ?>
				<?php foreach ($users as $key => $row) : ?>
				<?php $checked = in_array($row->id, $hold_users); ?>
				<?php $hold_username[$row->id] = $row->user_login; ?>
				
					<tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
					<td style="display:none;"><a href="<?php echo base_url(); ?>analytics/channel/<?php echo $row->id; ?>"></a></td>
						<td title="<?php echo $row->id; ?>"><?php echo $row->user_login; ?></td>
						<td><?php echo $row->youtube_channels; ?></td>
						<!--<td><?php  ?></td>-->
						<td><?php echo $row->youtube_content_category; ?></td>
						<td><?php echo $row->country; ?></td>
						<td><?php echo $row->sex; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</table>
		<div id="pagination">
			<?php //echo $pagination; ?>
		</div>
	</div>
	<?php }else{?>
	
	<?php var_dump($report);?>
	
    
	<?php }?>
		<!-- <div id="show-common-videos" class="step-video" style="display: none;">
		<a href="#" id="get-videos">Show videos of user selected</a>
		<div id="content-dinamic-show-videos"></div>
		</div>-->
	
</center>