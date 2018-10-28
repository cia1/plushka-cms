<?php return array(
'right'=>'forum.moderate,forum.category',
'menu'=>'12,13',
'table'=>'forum_category,forum_post,forum_topic,forum_user',
'file'=>array(
	'admin/controller/forum.php',
	'admin/hook/userCreate.forum.php',
	'admin/hook/userDelete.forum.php',
	'admin/hook/userModify.forum.php',
	'config/forum.php',
	'controller/forum.php',
	'hook/search.forum.php',
	'hook/userCreate.forum.php',
	'public/avatar/',
	'public/css/forum.css',
	'view/forumProfile.php',
	'view/forumUser.php',
	'view/forumIndex.php',
	'view/forumCategory.php',
	'view/forumTopic.php'
)
); ?>