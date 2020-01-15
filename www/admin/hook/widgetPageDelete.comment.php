<?php
/**
 * @package comment
 * Событие: удаление виджета со определённых страниц
 * @var array $data :
 *  string   [0] Имя виджета
 *  int      [1] Идентификатор виджета
 *  string[] [2] Список страниц, с которых был удалён виджет
 */
use plushka\admin\core\plushka;

if($data[0]!=='comment') return true;

$db=plushka::db();
$ids='';
foreach($data[2] as $item) {
	$i=strlen($item)-1;
	$s=substr($item,0,$i);
	if($item[$i]==='/') $s=' LIKE '.$db->escape($s.'%');
	elseif($item[$i]==='.') $s='='.$db->escape($s);
	else $s=' LIKE '.$db->escape($s.'/%');
	$id=$db->fetchArray('SELECT id FROM comment_group WHERE link'.$s);
	if(!$id) continue;
	foreach($id as $_id) {
		if($ids) $ids.=','.$_id[0]; else $ids=$_id[0];
	}
}
if(!$ids) return true;
$db->query('DELETE FROM comment WHERE groupId IN('.$ids.')');
$db->query('DELETE FROM comment_group WHERE id IN('.$ids.')');
return true;