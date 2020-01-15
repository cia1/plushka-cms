<?php
/**
 * Событие: страница изменилась
 * Обновляет Last modified
 * @var array $data :
 *  string [0] Относительный URL страницы (без языка)
 *  bool   [1] Если TRUE, то изменение касается всех языков
 */
use plushka\admin\core\plushka;

$db=plushka::db();
$cfg=plushka::config();
if(isset($data[1])===true && $data[1]) {
	$link=[];
	foreach($cfg['languageList'] as $item) {
		if($item===_LANG) $link[]=$data[0]; else $link[]=$item.'/'.$data[0];
	}
} else {
	if($cfg['languageDefault']===_LANG) $link=[$data[0]]; else $link=[_LANG.'/'.$data[0]];
}
foreach($link as $item) {
	$db->query('REPLACE INTO modified (link,time) VALUES ('.$db->escape($item).','.time().')');
}
return true;