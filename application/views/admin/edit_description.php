<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
$(document).ready(function(){
	$("#myForm").submit(function(){
		if (!isCheckedById("videos_ids")){
			alert ("Please select at least one checkbox");
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

	$(".edit").click(function() {
		var video_id = $(this).attr("video_id");
		var value = $(this).attr("value");
		var field = $(this).attr("field");
		var temp = $(this).html();

		$('.field[title=' + video_id + ']').focus();

		$("#" + field + "-field" + video_id).toggle();
		$(this).toggle();
	});

	$(".control").click(function() {
		var id = $(this).parent().attr("video_id");
		var field = $(this).parent().attr("field");
		var action = $(this).attr("action");
		$(this).parent().parent().toggle();

		$("#" + field + id).toggle();

		if (action == "save") {
			var url = "<?php echo base_url(); ?>video/ajax_edit";
			var value = $("#" + field + "-value" + id).val();
			var user_id = $("#" + field + "-field" + id).attr("user_id");
			var channel = $("#" + field + "-field" + id).attr("channel");

			$.ajax({
				url: url,
				type: "post",
				data: {video_id: id, value: value, field: field, user_id: user_id, channel: channel},
				error: function() {
					$("#message").show().html("Error an occured");
					$("#message").delay(3000).hide();
				},
				success: function(data) {
					$("#message").show().html("<div id=\"message-green\">The data was saved!</div>");
					$("#message").delay(3000).hide();

					if (field == "description") {
						$("#" + field + id).html(value.split(" ", 3));
					}

				}
			});
		}
	});

});
</script>
<center>
	<div id="message"></div>
    <?php echo form_open('video/videoActions', array('class' => 'forms', 'id' => 'myForm', 'name' => 'myForm')); ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
        <?php if (!empty($msg)) { ?>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
        <?php } ?>
        <tr>
            <th class="table-header-repeat line-left"><a href="">Title</a></th>
            <th class="table-header-repeat line-left minwidth-1"><a href="">Description</a></th>
            <th class="table-header-repeat line-left"><a href="">Category</a></th>
            <th class="table-header-repeat line-left"><a href="">Channel</a></th>
            <th class="table-header-repeat line-left" width="100"><a href="">Preview</a></th>
        </tr>
        <?php foreach ($videos as $key => $video) : ?>
            <tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
                <td><span class="edit" video_id="<?php echo $video["video_id"]; ?>" field="title" id="title<?php echo $video["video_id"]; ?>" title="Click for edit Title"><?php echo $video["title"]; ?></span>
					<span class="content" style="display: none;" id="title-field<?php echo $video["video_id"]; ?>" value="<?php echo $video["title"]; ?>" user_id="<?php echo $video["user_id"]; ?>" channel="<?php echo $video["channel"]; ?>"><textarea class="title" id="title-value<?php echo $video["video_id"]; ?>"><?php echo $video["title"]; ?></textarea>

						<span class="control-panel" video_id="<?php echo $video["video_id"]; ?>" field="title">
							<img src="<?php echo base_url(); ?>css/admin/images/icons/save_disc.png" class="control" action="save" title="Save" />
							<img src="<?php echo base_url(); ?>css/admin/images/icons/trash.png" class="control" action="trash" title="Cancel" />
						</span>
					</span>

				</td>


                <td><span class="edit" type="textarea" video_id="<?php echo $video["video_id"]; ?>" value="<?php echo $video["description"]; ?>" field="description" id="description<?php echo $video["video_id"]; ?>" title="Click for edit Description"><?php echo get_excerpt($video["description"]); ?></span>

					<span class="content" style="display: none;" id="description-field<?php echo $video["video_id"]; ?>" value="<?php echo $video["description"]; ?>" user_id="<?php echo $video["user_id"]; ?>" channel="<?php echo $video["channel"]; ?>"><textarea class="description" id="description-value<?php echo $video["video_id"]; ?>"><?php echo $video["description"]; ?></textarea>

						<span class="control-panel" video_id="<?php echo $video["video_id"]; ?>" field="description">
							<img src="<?php echo base_url(); ?>css/admin/images/icons/save_disc.png" class="control" action="save" title="Save" />
							<img src="<?php echo base_url(); ?>css/admin/images/icons/trash.png" class="control" action="trash" title="Cancel" />
						</span>
					</span>
				</td>

				<!-- class="edit" -->
                <td align="center"><span video_id="<?php echo $video["video_id"]; ?>"><?php echo $video["category"]; ?></span>
					<span class="<?php echo $video["video_id"]; ?>" title="category" style="display: none;"></span></td>
                <td><?php echo $video["channel"]; ?></td>
                <td align="center">
					<img src="<?php echo $video["thumbnail"]; ?>" class="borderPhoto" style="height:100px;width:150px;" />
                </td>
            </tr>
		<?php endforeach; ?>
    </table>
	<div class="pagination">
	<?php echo $pagination; ?>
	</div>
    <?php echo form_close(); ?>
</center>