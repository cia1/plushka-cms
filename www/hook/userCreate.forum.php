<?php
/* Событие: создание нового пользователя (до подтверждения e-mail)
Модуль: forum (форум)
Параметры: int $data[0] - ИД пользователя, string $data[1] - логин, string $data[2] - e-mail.
*/
$db=core::db();
$db->insert('forumUser',array(
	'id'=>$data[0],
	'login'=>$data[1],
	'date'=>time()
));
return true;