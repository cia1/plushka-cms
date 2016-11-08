<?php
/* Событие: удаление страницы (URL)
Параметры: string $data[0] - псевдоним языка */
$db=core::db();
$db->query('DELETE FROM modified WHERE link='.$db->escape($data[0]));
return true;
?>
