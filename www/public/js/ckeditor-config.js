CKEDITOR.editorConfig = function( config ) {
	config.toolbar_Full=[
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','Undo','Redo' ] },
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'JustifyLeft','JustifyCenter','JustifyRight' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor','Image' ] },
		{ name: 'tools', items : [ 'Maximize' ] }
	];
//	config.extraPlugins="simpleimage";
};