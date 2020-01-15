<?php
/**
 * @package comment
 * Событие: удаление страницы
 * @var array $data :
 *  string [0] Относительный URL страницы
 *  bool   [1] Если TRUE, то изменение касается всех языков
 */
use plushka\admin\core\plushka;

$db=plushka::db();
$id=$db->fetchValue('SELECT id FROM comment_group WHERE link='.$db->escape($data[0]));
if($id===null) return true;
$db->query('DELETE FROM comment WHERE groupId='.$id);
$db->query('DELETE FROM comment_group WHERE id='.$id);
return true;