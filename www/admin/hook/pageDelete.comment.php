<?php
/* Событие: удаление страницы (URL)
Параметры: string $data[0] - псевдоним языка */
$db=plushka::db();
$id=$db->fetchValue('SELECT id FROM comment_group WHERE link='.$db->escape($data[0]));
if(!$id) return true;
$db->query('DELETE FROM comment WHERE groupId='.$id);
$db->query('DELETE FROM comment_group WHERE id='.$id);
return true;