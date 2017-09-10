<?php
/* Событие: удаление пункта меню
Модуль: core
Параметры: string $data[0] - удаляемая ссылка, int $data[1] - идентификатор удаляемого пункта меню */
$link=$data[0];

$db=core::db();
$db->query('DELETE FROM section WHERE url IN('.$db->escape($link.'.').','.$db->escape($link.'*').','.$db->escape($link.'/').')');
return true;
?>