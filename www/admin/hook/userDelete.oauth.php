<?php
/* Событие: удаление пользователя
Модуль: oauth
Параметры: int $data[0] - ИД пользователя
*/
$db=plushka::db();
$db->query('DELETE FROM oauth WHERE userId='.$data[0]);
return true;
?>