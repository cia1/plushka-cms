<?php
/* Событие: обновление Last modified
Параметры: string $data[0] - адрес страницы, которая была изменена */
$db=core::db();
$db->query('REPLACE INTO modified (link,time) VALUES ('.$db->escape($data[0]).','.time().')');
return true;
?>