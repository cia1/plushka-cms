<?php
/* Событие: удаление пользователя
Модуль: forum (форум)
Параметры: int $data[0] - ИД пользователя
*/
$db=core::db();
$db->query('DELETE FROM forum_user WHERE id='.$data[0]);
return true;
?>