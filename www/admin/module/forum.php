<?php return array(
'hook1'=>'search,userCreate',
'hook2'=>'userCreate,userDelete,userModify',
'right'=>'forum.moderate,forum.category',
'menu'=>'12,13',
'table'=>'forumCategory,forumPost,forumTopic,forumUser',
'file'=>array(
	'view/forumProfile.php',
	'view/forumUser.php',
	'view/forumIndex.php',
	'view/forumCategory.php',
	'view/forumTopic.php',
	'public/css/forum.css',
	'public/avatar/no.png',
	'public/avatar/5.jpeg',
	'hook/search.forum.php',
	'hook/userCreate.forum.php',
	'config/forum.php',
	'controller/forum.php',
	'admin/public/icon/setting32.png',
	'admin/hook/userCreate.forum.php',
	'admin/hook/userDelete.forum.php',
	'admin/hook/userModify.forum.php',
	'admin/controller/forum.php',
	'admin/module/forum.php'
)
); ?>