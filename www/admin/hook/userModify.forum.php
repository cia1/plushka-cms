<?php
/* Событие: изменение информации о пользователе
Модуль: forum (форум)
Параметры: int $data[0] - ИД пользователя, string $data[1] - логин, string $data[2] - e-mail.
*/
$db=plushka::db();
$db->query('UPDATE forum_user SET login='.$db->escape($data[1]).' WHERE id='.$data[0]);
return true;
?>