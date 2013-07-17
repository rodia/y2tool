/*
 * @version 1.1
 */

/**
 * Check if any check box is selected
 * @param id Id of check box for reseach.
 * @return True if any check is select, False otherwise.
 */
function isCheckedById(id){
	var checked = false;
	$("input[type=checkbox]:checked").each(
	function() {
		checked = true;
		return;
	});
	return checked;
}
