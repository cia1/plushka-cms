<?php
/* Событие: создание нового пользователя (до подтверждения e-mail)
Модуль: forum (форум)
Параметры: int $data[0] - ИД пользователя, string $data[1] - логин, string $data[2] - e-mail.
*/
$db=plushka::db();
$db->insert('forum_user',array(
	'id'=>$data[0],
	'login'=>$data[1],
	'date'=>time()
));
return true;