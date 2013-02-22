$(document).ready(function() {
	$('#previewEditButton').click(function() {
		ContentEditor.edit($('#post-preview-editable'));
	});
});