<?php return array(
'right'=>'comment.moderate,comment.widget',
'widget'=>'comment',
'table'=>'comment,comment_group',
'file'=>array(
	'admin/controller/comment.php',
	'admin/hook/pageDelete.comment.php',
	'admin/hook/widgetDelete.comment.php',
	'admin/hook/widgetPageDelete.comment.php',
	'admin/public/icon/comment16.png',
	'config/comment.php',
	'controller/comment.php',
	'public/js/comment.js',
	'view/widgetComment.php',
	'model/mComment.php',
	'widget/comment.php'
)
);