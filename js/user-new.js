/*jQuery(document).ready(function(e){e("select#adduser-role").closest("tr").remove()});
jQuery(document).ready(function(e){e("select#role").closest("tr").remove()});*/

/* Remove fields and add a hidden field with a temp value, which will be deleted in processing */
jQuery(document).ready(function($) {

	$("select#adduser-role").closest("tr").remove();
	$("select#role").closest("tr").remove();
	
	$('<input>').attr('type','hidden').attr('name','role').attr('value','subscriber').appendTo('#adduser');
	$('<input>').attr('type','hidden').attr('name','role').attr('value','subscriber').appendTo('#createuser');
});