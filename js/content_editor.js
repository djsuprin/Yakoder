function ContentEditor(editableElement, onSaveHandler) {
	this.editableElement = editableElement;
	this.mode = 'view';
	this.contentBeforeEdit = '';
	this.onSaveHandler = onSaveHandler;

	this.createPanel = function() {
		var contentEditor = this;
		this.panel = $('<div/>').css({
			'margin-top': '10px',
			'padding': '5px'
		});
		this.modeButton = $('<button/>').html('Edit').click(function() {
			if (contentEditor.mode == 'view') {
				contentEditor.mode = 'edit';
				$(this).html('Save');
				contentEditor.contentBeforeEdit = contentEditor.editableElement.html();
				contentEditor.controls.css({'display': 'block'});
				contentEditor.editableElement.attr('contentEditable', 'true');
			} else {
				contentEditor.mode = 'view';
				$(this).html('Edit');
				contentEditor.controls.css({'display': 'none'});
				if (contentEditor.contentBeforeEdit != contentEditor.editableElement.html()) {
					contentEditor.onSaveHandler();
				}
				contentEditor.editableElement.attr('contentEditable', 'false');
			}
		});
		this.controls = $('<div/>').css({
			'display': 'none'
		});
		
		var boldButton = $('<button/>').html('Bold').click(function() {
			document.execCommand('bold',false, null);
		});
		
		var italicButton = $('<button/>').html('Italic').click(function() {
			document.execCommand('italic',false, null);
		});
		
		var underlineButton = $('<button/>').html('Underline').click(function() {
			document.execCommand('underline',false, null);
		});
		
		var strikeButton = $('<button/>').html('Strike').click(function() {
			document.execCommand('strikeThrough',false, null);
		});
		
		var formatButton = $('<button/>').html('Format').click(function() {
			var tag = prompt('Please enter HTML formatting tag.');
			document.execCommand('formatBlock',false, tag);
		});
		
		var backColorButton = $('<button/>').html('Back Color').click(function() {
			var tag = prompt('Please enter the color code (i.e. #FFFFFF) or color name.');
			document.execCommand('backColor',false, tag);
		});
		
		var foreColorButton = $('<button/>').html('Fore Color').click(function() {
			var tag = prompt('Please enter the color code (i.e. #FFFFFF) or color name.');
			document.execCommand('foreColor',false, tag);
		});
		
		var justifyCenterButton = $('<button/>').html('Justify Center').click(function() {
			document.execCommand('justifyCenter',false, null);
		});
		
		var justifyFullButton = $('<button/>').html('Justify Full').click(function() {
			document.execCommand('justifyFull',false, null);
		});
		
		var justifyLeftButton = $('<button/>').html('Justify Left').click(function() {
			document.execCommand('justifyLeft',false, null);
		});
		
		var justifyRightButton = $('<button/>').html('Justify Right').click(function() {
			document.execCommand('justifyRight',false, null);
		});
		
		var linkButton = $('<button/>').html('Link').click(function() {
			var link = prompt('Please specify the link.');
			if (link) {
				document.execCommand('createLink', false, link);
			}
		});
		
		var imageButton = $('<button/>').html('Image').click(function() {
			var source = prompt('Please specify the source of the image.');
			if (source) {
				document.execCommand('insertImage', false, source);
			}
		});
		
		var subscriptButton = $('<button/>').html('Subscript').click(function() {
			document.execCommand('subscript', false, null);
		});
		
		var superscriptButton = $('<button/>').html('Superscript').click(function() {
			document.execCommand('superscript', false, null);
		});
		
		var orderedListButton = $('<button/>').html('Ordered List').click(function() {
			document.execCommand('insertOrderedList', false, null);
		});
		
		var unorderedListButton = $('<button/>').html('Unordered List').click(function() {
			document.execCommand('insertUnorderedList', false, null);
		});
		
		var clearButton = $('<button/>').html('Clear format').click(function() {
			document.execCommand('removeFormat', false, null);
		});
		
		this.controls.append(boldButton).append(italicButton).append(underlineButton).append(strikeButton)
				.append(backColorButton).append(foreColorButton)
				.append(justifyLeftButton).append(justifyCenterButton)
				.append(justifyRightButton).append(justifyFullButton)
				.append(linkButton).append(imageButton)
				.append(subscriptButton).append(superscriptButton)
				.append(orderedListButton).append(unorderedListButton)
				.append(formatButton).append(clearButton);
		this.panel.append(this.modeButton);
		this.panel.append(this.controls);
		this.panel.insertBefore(this.editableElement);
	};
	
	this.createPanel();
}