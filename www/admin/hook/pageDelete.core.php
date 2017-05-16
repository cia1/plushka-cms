<?php
/* Событие: удаление страницы (URL)
Параметры: string $data[0] - псевдоним языка */
$db=core::db();
$link=$data[0];
$cfg=core::config();
if(isset($data[1]) && $data[1]) {
	$link=array();
	foreach($cfg['languageList'] as $item) {
		$link[]=$db->escape(($item==_LANG ? '' : $item.'/').$data[0]);
	}
} elseif($cfg['languageDefault']!=_LANG) $link=_LANG.'/'.$link;
if(is_string($link)) $link=array($db->escape($link));
$db->query('DELETE FROM modified WHERE link IN ('.implode(',',$link).')');
return true;