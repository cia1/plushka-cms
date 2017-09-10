<?php
/* Событие: удаление страницы (URL)
Параметры: string $data[0] - псевдоним языка */
$db=core::db();
$id=$db->fetchValue('SELECT id FROM commentGroup WHERE link='.$db->escape($data[0]));
if(!$id) return true;
$db->query('DELETE FROM comment WHERE groupId='.$id);
$db->query('DELETE FROM commentGroup WHERE id='.$id);
return true;
?>
