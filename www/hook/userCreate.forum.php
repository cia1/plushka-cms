<?php
/* Событие: создание нового пользователя (до подтверждения e-mail)
Модуль: forum (форум)
Параметры: int $data[0] - ИД пользователя, string $data[1] - логин, string $data[2] - e-mail.
*/
$db=core::db();
$db->query('INSERT INTO forumUser (id,login,date) VALUES ('.$data[0].','.$db->escape($data[1]).','.time().')');
return true;
?>