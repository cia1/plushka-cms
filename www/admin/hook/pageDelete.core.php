<?php
/**
 * Событие: удаление страницы
 * @var array $data :
 *  string [0] Относительный URL страницы
 *  bool   [1] Если TRUE, то изменение касается всех языков
 */
use plushka\admin\core\plushka;

$db=plushka::db();
$link=$data[0];
$cfg=plushka::config();
if(isset($data[1])===true && $data[1]) {
	$link=[];
	foreach($cfg['languageList'] as $item) {
		$link[]=$db->escape(($item===_LANG ? '' : $item.'/').$data[0]);
	}
} elseif($cfg['languageDefault']!==_LANG) $link=_LANG.'/'.$link;
if(is_string($link)) $link=[$db->escape($link)];
$db->query('DELETE FROM modified WHERE link IN ('.implode(',',$link).')');
return true;