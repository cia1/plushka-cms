<?php
/* Событие: удаление виджета
Модуль: комментарии
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, mixed $data[2] - параметры виджета */

if($data[0]!='comment') return true;
//Составить список всех старниц, с которых на которых был этот виджет
$db=core::db();
$db->query('SELECT url FROM section WHERE widgetId='.$data[1]);
$links='';
while($item=$db->fetch()) {
	if($links) $links.=',';
	$links.=$db->escape(substr($item[0],0,strlen($item[0])-1));
}
//Выбрать ИД групп комментариев, соответствующих страницам, на которых был виджет
$db->query('SELECT id FROM commentGroup WHERE link IN('.$links.')');
unset($links);
$ids='';
while($item=$db->fetch()) {
	if($ids) $ids.=','.$item[0]; else $ids=$item[0];
}
if(!$ids) return true;
$db->query('DELETE FROM comment WHERE groupId IN('.$ids.')');
$db->query('DELETE FROM commentGroup WHERE id IN('.$ids.')');
return true;
?>