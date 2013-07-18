<?php
/**
 * @version 1.1
 *
 * Add video playlist
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<script type="text/javascript">
$(function() {

	$('.addInput').live('click', function() {
		var select_action = $(this).attr("rel");

		var inputsDiv = $('#' + select_action);
		var i = $('#' + select_action + ' p').size() + 1;
		var name_field = select_action.replace("_inputs", "");
		$('<p><label for="video_id"><span>Video ID ' + i + ' * </span><input type="text" class="inp-form" size="60" name="' + name_field + '_ids[]" id="video_ids_' + i + '" value="" placeholder="Enter youtube video id" /></label> <span><a href="#" class="remInput" rel="' + select_action + '" style="color:#0093F0">Remove</a></span></p>').appendTo(inputsDiv);
		$(inputsDiv).attr("title", i);
		return false;
	});

	$('.remInput').live('click', function() {
		var select_action = $(this).attr("rel");
		var i = $('#' + select_action + ' p').size() + 1;
		if( i > 2 ) {
			$(this).parents('p').remove();
			i--;
		}
		return false;
	});

	$("#get-videos").click(function() {
		var url = "/video/get_ajax_videos";
		var user_id = [];
		user_id[0] = <?php echo $user_id; ?>

		$("#content-dinamic-show-videos").html("<img src=\"<?php echo base_url(); ?>css/admin/images/loading_bar.gif\" />");

		$.ajax({
			url : url,
			data: {users: user_id, category: null},
			type: "post",
			success: function(data) {
				$("#content-dinamic-show-videos").html("");
				$("#content-dinamic-show-videos").append("<table width=\"800\" id=\"product-table\">" +
					"<thead>" +
						"<tr>" +
							"<td></td>" +
							"<td>Title</td>" +
							"<td>Views</td>" +
							"<td>Category</td>" +
						"</tr>" +
					"</thead>" +
					"<tbody></tbody>" +
					"</table>"
				);
				for(var item in data) {
					if (data[item].video_id == undefined) continue;
					$("#content-dinamic-show-videos table tbody").append(
						"<tr" + (item % 2 ? " class=\"alternate-row\"" : "") + ">" +
							"<td><input type=\"checkbox\" name=\"videos_user[]\" value=\"" + data[item].video_id + "\"></td>" +
							"<td>" + data[item].title + "</td>" +
							"<td>" + data[item].view_count + "</td>" +
							"<td>" + data[item].category + "</td>" +
						"<tr>"
					);
				}
			}
		});

		return false;
	});
});

	jQuery.extend(jQuery.validator.prototype, {
		/*
		 * Modifica necessaria per controllare tutti i campi nel caso in cui ci sia un array di <input>
		 * I campi interessati devono avere l'attributo id valorizzato
		 */
		checkForm: function() {
			this.prepareForm();
			for ( var i = 0, elements = (this.currentElements = this.elements()); elements[i]; i++ ) {
				if (this.findByName( elements[i].name ).length != undefined && this.findByName( elements[i].name ).length > 1) {
					for (var cnt = 0; cnt < this.findByName( elements[i].name ).length; cnt++) {
						this.check( this.findByName( elements[i].name )[cnt] );
					}
				} else {
					this.check( elements[i] );
				}
			}
			return this.valid();
		},

		showErrors: function(errors) {
			if(errors) {
				// add items to error list and map
				$.extend( this.errorMap, errors );
				this.errorList = [];
				for ( var name in errors ) {
					this.errorList.push({
						message: errors[name],
						/* NOTE THAT I'M COMMENTING THIS OUT
										element: this.findByName(name)[0]
						 */
						element: this.findById(name)[0]
					});
				}
				// remove items from success list
				this.successList = $.grep( this.successList, function(element) {
					return !(element.name in errors);
				});
			}
			this.settings.showErrors
				? this.settings.showErrors.call( this, this.errorMap, this.errorList )
			: this.defaultShowErrors();
		},

		findById: function( id ) {
			// select by name and filter by form for performance over form.find("[id=...]")
			var form = this.currentForm;
			return $(document.getElementById(id)).map(function(index, element) {
				return element.form == form && element.id == id && element || null;
			});
		}

	});
</script>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	"video/playlist/{$user_id}" => "Playlist",
	"video/videolist/{$user_id}/{$videoFeedID}" => "Video list",
	$title
)); ?>
<center>
	<?php if (isset($success)) show_messages($success, $msg, $type); ?>
	<?php echo form_open("video/add_video_playlist/{$user_id}/{$videoFeedID}", array("method" => "post", 'novalidate' => 'novalidate')); ?>
	<!--video/addvideo-->
        <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
            <tr>
                <th class="table-header-repeat line-left" colspan="2"><a href="#">Add video to Playlist</a></th>
            </tr>
            <tr class="alternate-row">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
            <tr>
                <td>
                    <h2>Youtube video ids:<br/> <span class="sized">(It's can an url the video)</span></h2>
                </td>
                <td>
					<div id="video" title="1">
						<p>Video ID 1 * <?php echo form_input(array("name" => "video_ids[]", "placeholder" => "Enter youtube video id", "class" => "inp-form", "size" => 60)); ?>
						<span><?php echo form_error('video_id'); ?></span><br/>
						<span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
						</p>
					</div>
					<h2><a href="#" class="addInput" rel="video" style="color:#0093F0">Add another input box</a></h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
					<a href="/video/videolist/<?php echo $user_id; ?>/<?php echo $videoFeedID; ?>" class="go-back-user">Cancel</a>
					<?php echo form_hidden("type", "youtube"); ?>
					<?php echo form_hidden("videoFeedID", $videoFeedID); ?>
					<?php echo form_hidden("user_id", $user_id); ?>
					<?php echo form_hidden("channel", $channel); ?>
					<?php echo form_submit(array("name" => "submit", "id" => "button", "class" => "form-submit"), "Add video"); ?>
                </td>
            </tr>
        </table>

		<div id="show-common-videos" class="step-video">
			<a href="#" id="get-videos">Show my videos</a>
			<div id="content-dinamic-show-videos"></div>
		</div>
    <?php echo form_close(); ?>
</center>