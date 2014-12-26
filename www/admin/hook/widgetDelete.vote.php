<?php
/* Событие: удаление виджета
Модуль: vote (опрос)
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, mixed $data[2] - идентификатор опроса */
if($data[0]!='vote') return true;

$db=core::db();
$db->query('DELETE FROM vote WHERE id='.$data[2]);
return true;
?>