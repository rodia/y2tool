<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @todo Hay un problema cuando se selecciona elementos del checkbox y elementos definidos por el button add/Remove, en algunos casos no reposnde al cambio requerido cuando se intercalan las selecciones para insertar un nuevo user.
 */
?>
<script type="text/javascript">
var stack = [];

Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};

$(document).ready(function(){

	$(window).bind('beforeunload', function() {
		var pre = readCookie("hold-users");
		// console.log(pre);
		var temp = (pre != null && pre != "") ? pre.split(",") : [];
		var all_users = temp.concat(stack).unique();
		createCookie("hold-users", all_users);
	});

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

	$("#bulk-form").submit(function(){
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

	$(".add-remove").click(function() {
		var obj = this;
		var title = $(obj).attr("title");
		var checked = $('input[title=' + title + ']').attr('checked');
		var action = (!checked && checked == undefined);

		set_checked(obj);
		set_username(obj, action);
		return false;
	});

	$("input[type=checkbox]").click(function() {
		var title = $(this).attr("title");
		var checked = $(this).is(':checked');
		var obj = $("button[title=" + title + "]");

		set_button(obj, checked);
//		$(".pre-action").removeAttr("disabled");
		if($(this).attr("name")!="featured_ids[]")
		set_username(obj, checked);
	});

	$(".go-back-user").click(function() {
		$(".step-user").show();
		$(".step-video").hide();
		$("#show-common-videos").hide();
//		$(".pre-action").removeAttr("disabled");

		return false;
	});

	$(".action").click(function() {
		var action = $(this).attr("title");

		$("input[name=selected-action]").val(action);
		return true;
	});

	$(".pre-action").click(function() {
		var action = $(this).attr("title");
		$(".step-user").hide();
		$(".step-video").hide();
		$("#show-common-videos").show();
		/** hide field for form post */
		$("input[name=selected-action]").val(action);

//		$(".pre-action").attr("disabled", "disabled");

		$("#" + action).show();
		if (action == "like-video") {
			$('#bulk-form').validate({
				rules: {
					"like_ids[]": {required: true}
				},
				submitHandler: function(form) {form.submit();}
			});
			$(this).removeAttr("disabled");
		} else if (action == "comment-video") {
			$('#bulk-form').validate({
				rules: {
					"comment_ids[]": {required: true}
				},
				submitHandler: function(form) {form.submit();}
			});
			$(this).removeAttr("disabled");
		} else if (action == "favorite-video") {
			$('#bulk-form').validate({
				rules: {
					"favorite_ids[]": {required: true}
				},
				submitHandler: function(form) {form.submit();}
			});
			$(this).removeAttr("disabled");
		} else if (action == "share-video") {
			$('#bulk-form').validate({
				rules: {
					"share_ids[]": {required: true}
				},
				submitHandler: function(form) {form.submit();}
			});
			$(this).removeAttr("disabled");
		}
		else if (action == "featured-channel") {
			$('#bulk-form').validate({
				rules: {
					"featured_ids[]": {required: true}
				},
				submitHandler: function(form) {form.submit();}
			});
			$(this).removeAttr("disabled");
		}

		return false;
	});
	<?php $is_enable_action = FALSE; ?>
	<?php foreach ($pair_user_login as $title => $username) :?>
	$(".close[title=<?php echo $title; ?>]").click(function() {
		var obj = $("button[title=<?php echo $title; ?>]");
		$(this).remove();
		if (obj) {
			set_checked(obj);
		}
		remove_username("<?php echo $title; ?>", "<?php echo $username; ?>");
	});
	<?php $is_enable_action = TRUE; ?>
	<?php endforeach; ?>

	<?php if ($is_enable_action) : ?>
//		$(".pre-action").removeAttr("disabled");
	<?php endif; ?>

	function set_button(obj, checked) {
		$(obj).html(checked ? "Remove" : "Add");
		$(obj).toggleClass("remove");
		$(obj).toggleClass("padding-button");
	}

	function set_username(obj, checked) {
		var title = $(obj).attr("title");
		var username = $('td[title=' + title + ']').html();
		if (checked) {
			$("#hold-selected").append("<span title=\"" + title + "\" id=\"un" + title + "\" class=\"close\">" + username + "</span>");
			$(".close[title=" + title + "]").click(function() {
				$(this).remove();
				set_checked(obj);
			});

			stack.push(title + "|" + username);
		} else {
			$("span#un" + title).remove();

			remove_username(title, username);
		}
	}

	function remove_username(title, username) {
		var pos = stack.indexOf(title + "|" + username);
		pos > -1 && stack.splice(pos, 1);

		var pre = readCookie("hold-users");
		var temp = (pre != null && pre != "") ? pre.split(",") : [];

		pos = temp.indexOf(title + "|" + username);
		pos > -1 && temp.splice(pos, 1);

		var all_users = temp.concat(stack).unique();
		createCookie("hold-users", all_users);
	}

	function set_checked(obj) {
		var title = $(obj).attr("title");
		var checked = $('input[title=' + title + ']').attr('checked');
		var action = (!checked && checked == undefined);

		$('input[title=' + title + ']').attr('checked', !checked);
		set_button(obj, action);
	}

	function createCookie(name, value, hour) {
		if (hour) {
			var date = new Date();
			date.setTime(date.getTime() + (hour*60*60*1000));
			var expires = "; expires=" + date.toGMTString();
		}
		else var expires = "";
		document.cookie = name + "=" + value + expires + "; path=/";
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}

	function eraseCookie(name) {
		createCookie(name + "", "", -1);
	}

	$(function() {

		$('.addInput').live('click', function() {
			var select_action = $(this).attr("rel");

			var inputsDiv = $('#' + select_action);
			var i = $('#' + select_action + ' p').size() + 1;
			var name_field = select_action.replace("_inputs", "");
			$('<p><label for="video_id"><span>Video ID ' + i + ' * </span><input type="text" class="inp-form" size="50" name="' + name_field + '_ids[]" id="video_ids_' + i + '" value="" placeholder="Enter youtube video id" /></label> <span><a href="#" class="remInput" rel="' + select_action + '" style="color:#0093F0">Remove</a></span></p>').appendTo(inputsDiv);
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
	});

	$("#get-videos").click(function() {
		var url = "/video/get_ajax_videos";
		var user_id = [];

		$.each($("#hold-selected span"), function(key, value) {
			user_id[key] = $(value).attr("title");
		});

		$("#content-dinamic-show-videos").html("<img src=\"<?php echo base_url(); ?>css/admin/images/loading_bar.gif\" />");

		$.ajax({
			url : url,
			data: {users: user_id, category: null},
			type: "post",
			success: function(data) {
				alert(data);
				$("#content-dinamic-show-videos").html("");
				$("#content-dinamic-show-videos").append("<table width=\"800\">" +
					"<thead>" +
						"<tr>" +
							"<th>Youtube ID</th>" +
							"<th>Title</th>" +
							"<th>Views</th>" +
							"<th>Category</th>" +
						"</tr>" +
					"</thead>" +
					"<tbody></tbody>" +
					"</table>"
				);
				for(var item in data) {
					$("#content-dinamic-show-videos table tbody").append(
						"<tr>" +
							"<td>" + data[item].video_id + "</td>" +
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
<center>
	<?php if ($this->input->get("msg")): ?>
	<div class="forgot-pwd success">
		<p><?php echo $this->input->get("msg"); ?></p>
	</div>
	<?php endif; ?>
	<div class="step-user">
		<table>
			<tr>
				<td valign="top">
					<?php echo form_open('video/bulk', array('name' => 'by_name', 'id' => 'by_name', 'method' => 'get')); ?>
						Name:
						<?php echo form_input(array("id" => "name-filter", "name" => "search-name", "value" => $name, "class" => "imp-form", "data-source" => "admin/search?search=")); ?>
						<?php echo form_submit('s', 'Filter'); ?>

					<?php echo form_close(); ?>
				</td>
				<td width="20"></td>
				<td valign="top">
					<?php echo form_open('video/bulk', array('name' => 'country', 'id' => 'country', 'method' => 'get')); ?>

					Country:
					<?php echo form_dropdown('co', $country_list, $country, 'class="select_style" id="country-filter"'); ?>
					<?php echo form_submit('s', 'Filter'); ?>

					<?php echo form_close(); ?>
				</td>
				<td width="20"></td>

				<td valign="top">
					<?php echo form_open('video/bulk', array("name" => "category", "id" => "category", "method" => "get")); ?>
						Category:
						<?php echo form_dropdown('c', $category_options, $category, 'class="select_style" id="category-filter"'); ?>
						<?php echo form_submit("s", "Filter"); ?>
					<?php echo form_close(); ?>
				</td>

				<td width="20"></td>
				<td valign="top">
					<?php echo form_open('video/bulk', array('name' => 'users_by_sex', 'id' => 'users_by_sex', 'method' => 'get')); ?>
						Sex:
						<?php echo form_dropdown('g', array("" => "-- Select --", "Male" => "Male", "Female" => "Female"), $gender, 'class="select_style" id="gender-filter"'); ?>
						<?php echo form_submit('s', 'Filter'); ?>
					<?php echo form_close(); ?>
				</td>
				<td width="20"></td>
				<td width="20"><a class="restore" alt="restore" title="Click for restore selects"><img src="<?php echo base_url(); ?>css/admin/images/icons/icon_restore.png" width="15" height="15" /></a></td>
			</tr>
		</table>
	</div>

    <?php echo form_open('video/bulkActions', array('id' => 'bulk-form', 'name' => 'bulk-form', 'method' => 'post')); ?>
	<div class="step-user">
		<table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
			<tr>
				<th class="table-header-repeat line-left" width="20"><!-- <input type="checkbox" id="checkAll"> --></th>
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
			<?php echo $pagination; ?>
		</div>
	</div>
	<div id="user-selected">
		<h3><label for="selected">User Selected:</label></h3>
		<div id="hold-selected"><?php
		foreach ($hold_users as $user):
			echo "<span title=\"" . $user . "\" id=\"un" . $user . "\" class=\"close\">" . $pair_user_login[$user] . "</span>";
		endforeach;
		?></div>
	</div>
	<div id="select-action-to-take">
		<h3><label for="action">Select Action to Take</label></h3>
		<div id="select-action">
			<button title="like-video" class="pre-action">Like Videos</button>
			<button title="comment-video" class="pre-action">Comment on Videos</button>
			<button title="favorite-video" class="pre-action">Favorite Videos</button>
			<button title="share-video" class="pre-action">Share Videos</button>
			<button title="featured-channel" class="pre-action">Featured Channel</button>
<!--			<button title="like-video" class="pre-action" disabled="disabled">Like Videos</button>
			<button title="comment-video" class="pre-action" disabled="disabled">Comment on Videos</button>
			<button title="favorite-video" class="pre-action" disabled="disabled">Favorite Videos</button>
			<button title="share-video" class="pre-action" disabled="disabled">Share Videos</button>-->
			<button title="description-video" class="action">Edit Description</button>
		</div>
	</div>

	<div id="like-video" class="step-video" style="display: none;">
		<table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
			<tr>
				<th colspan="2" class="table-header-repeat line-left"><a href="#">Liking videos</a></th>
			</tr>

			<tr class="alternate-row">

				<td  colspan="2">

					<div id="like_inputs" title="1">
						<p>
							<label><span>Video ID 1 * </span><input type="text" class="inp-form" size="50" name="like_ids[]" value="" placeholder="Enter youtube video id" /></label>
							<br/><span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
						</p>
					</div>
					<h2><a href="#" class="addInput" rel="like_inputs" style="color:#0093F0">Add another input box</a></h2>
				</td>
			</tr>

			<tr>
				<td align="center" colspan="2">
					<a href="#" class="go-back-user">Cancel</a>
					<input class="form-submit" type="submit" value="Process" name="submit"/>
				</td>
			</tr>

		</table>

	</div>

	<div id="comment-video" class="step-video" style="display: none">
		<table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
			<tbody>
				<tr>
					<th colspan="2" class="table-header-repeat line-left"><a href="#">New comment</a></th>
				</tr>
				<tr class="alternate-row">

					<td  colspan="2">

						<div id="comment_inputs" title="1">
							<p>
								<label><span>Video ID 1 * </span><input type="text" class="inp-form" size="50" name="comment_ids[]" value="" placeholder="Enter youtube video id" /></label>
								<br/><span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
							</p>
						</div>
						<h2><a href="#" class="addInput" rel="comment_inputs" style="color:#0093F0">Add another input box</a></h2>
					</td>
				</tr>
				<tr >
					<td align="right">
						<h2>Comment *</h2>
					</td>
					<td>
						<textarea name="comment" cols="50" rows="5"><?php echo isset($comment) ? $comment : ""; ?></textarea>
						<span><?php echo form_error('comment'); ?></span>
					</td>
				</tr>
				<tr>
					<td align="center" colspan="2">
						<a href="#" class="go-back-user">Cancel</a>
						<input class="form-submit" type="submit" name="submit" value="Comment"/>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div id="favorite-video" class="step-video" style="display: none">
		<table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
			<tr>
				<th colspan="2" class="table-header-repeat line-left"><a href="#">Select Favorite Videos</a></th>
			</tr>

			<tr class="alternate-row">

				<td  colspan="2">

					<div id="favorite_inputs">
						<p>
							<label for="video_id"><span>Video ID 1 * </span><input type="text" class="inp-form" size="50" name="favorite_ids[]" value="" placeholder="Enter youtube video id" /></label>
							<br/><span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
						</p>
					</div>
					<h2><a href="#" class="addInput" rel="favorite_inputs" style="color:#0093F0">Add another input box</a></h2>
				</td>
			</tr>

			<tr>
				<td align="center" colspan="2">
					<a href="#" class="go-back-user">Cancel</a>
					<input class="form-submit" type="submit" value="Process" name="submit"/>
				</td>
			</tr>

		</table>

	</div>

	<div id="share-video" class="step-video" style="display: none">
		<table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
			<tr>
				<th colspan="2" class="table-header-repeat line-left"><a href="#">Input Videos for Share</a></th>
			</tr>

			<tr class="alternate-row">

				<td  colspan="2">

					<div id="share_inputs">
						<p>
							<label for="video_id"><span>Video ID 1 * </span><input type="text" class="inp-form" size="50" name="share_ids[]" value="" placeholder="Enter youtube video id" /></label>
							<br/><span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
						</p>
					</div>
					<h2><a href="#" class="addInput" rel="share_inputs" style="color:#0093F0">Add another input box</a></h2>
				</td>
			</tr>

			<tr>
				<td align="center" colspan="2">
					<a href="#" class="go-back-user">Cancel</a>
					<input class="form-submit" type="submit" value="Process" name="submit"/>
				</td>
			</tr>

		</table>
	</div>

	<div id="featured-channel" class="step-video" style="display: none">

	<table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
							<tr>
								<th class="table-header-repeat line-left" width="20"><input type="checkbox" id="checkAll"></th>
								<th class="table-header-repeat line-left"><a href="">Username</a></th>
								<th class="table-header-repeat line-left"><a href="">Youtube Channel</a></th>
							</tr>
							<?php if (!empty($users)): ?>
								<?php foreach ($users as $key => $row) : ?>
								<?php //$checked = in_array($row->id, $hold_users);
										if(in_array($row->id, $hold_users)) continue;
								?>
								<?php $hold_username[$row->id] = $row->user_login; ?>
									<tr<?php echo ($key % 2) ? " class=\"alternate-row\"" : ""; ?>>
										<td><input type="checkbox" name="featured_ids[]" value="<?php echo $row->id ?>" title="<?php echo $row->id; ?>"></td>
										<td title="<?php echo $row->id; ?>"><?php echo $row->user_login; ?></td>
										<td><?php echo $row->youtube_channels; ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							</table>
							<a href="#" class="go-back-user">Cancel</a>
					<input class="form-submit" type="submit" value="Process" name="submit"/>
		<!-- <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
			<tr>
				<th colspan="2" class="table-header-repeat line-left"><a href="#">Input Videos for Share</a></th>
			</tr>

			<tr class="alternate-row">

				<td  colspan="2">

					<div id="featured_inputs">
						<p>

						</p>
					 </div>
					<h2><a href="#" class="addInput" rel="featured_inputs" style="color:#0093F0">Add another input box</a></h2>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<a href="#" class="go-back-user">Cancel</a>
					<input class="form-submit" type="submit" value="Process" name="submit"/>
				</td>
			</tr>
		</table>-->
	</div>

	<div id="show-common-videos" class="step-video" style="display: none;">
		<a href="#" id="get-videos">Show videos of user selected</a>
		<div id="content-dinamic-show-videos"></div>
	</div>
	<?php echo form_hidden("selected-action"); ?>
	<?php echo form_hidden("users[]"); ?>
    <?php echo form_close(); ?>
</center>
