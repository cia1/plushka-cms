CKEDITOR.editorConfig = function( config ) {
	config.toolbar_Full=[
		{name:'document',items:['Source']},
		{name:'clipboard',items:['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		{name:'editing',items:['Find','Replace','SelectAll']},
		{name:'basicstyles',items:['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat']},
		{name:'paragraph',items:['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
		{name:'links',items:['Link','Unlink','Anchor']},
		{name:'insert',items:['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','Iframe']},
		{name:'styles',items:['Format','Font','FontSize']},
		{name:'colors',items:['TextColor','BGColor']},
		{name:'tools',items:['Maximize','ShowBlocks']}
	];
	config.extraPlugins="cyberim";
};

