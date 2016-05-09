<?php return array(
'hook1'=>'search,userCreate',
'hook2'=>'userCreate,userDelete,userModify',
'right'=>'forum.moderate,forum.category',
'menu'=>'12,13',
'table'=>'forumCategory,forumPost,forumTopic,forumUser',
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