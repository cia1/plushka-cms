<?php
/* Обновляет Last modified
Параметры: string $data[0] - относительный URL страницы (без указания языка);
bool $data[1] - если true, то обновить для всех языков */
$db=plushka::db();
$cfg=plushka::config();
if(isset($data[1]) && $data[1]) {
	$link=array();
	foreach($cfg['languageList'] as $item) {
		if($item==_LANG) $link[]=$data[0]; else $link[]=$item.'/'.$data[0];
	}
} else {
	if($cfg['languageDefault']==_LANG) $link=array($data[0]); else $link=array(_LANG.'/'.$data[0]);
}
foreach($link as $item) {
	$db->query('REPLACE INTO modified (link,time) VALUES ('.$db->escape($item).','.time().')');
}
return true;