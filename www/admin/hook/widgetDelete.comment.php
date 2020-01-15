<?php
/**
 * @package comment
 * Событие: удаление виджета
 * @var array $data :
 *  string [0] Имя виджета
 *  int    [1] Идентификатор виджета
 *  mixed  [2] Данные виджета (десериализованные)
 */
use plushka\admin\core\plushka;

if($data[0]!=='comment') return true;
$db=plushka::db();
$db->query('SELECT url FROM section WHERE widgetId='.$data[1]);
$links='';
while($item=$db->fetch()) {
	if($links) $links.=',';
	$links.=$db->escape(substr($item[0],0,strlen($item[0])-1));
}
$db->query('SELECT id FROM comment_group WHERE link IN('.$links.')');
unset($links);
$ids='';
while($item=$db->fetch()) {
	if($ids) $ids.=','.$item[0]; else $ids=$item[0];
}
if(!$ids) return true;
$db->query('DELETE FROM comment WHERE groupId IN('.$ids.')');
$db->query('DELETE FROM comment_group WHERE id IN('.$ids.')');
return true;