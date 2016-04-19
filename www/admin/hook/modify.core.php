<?php
/* Событие: обновление Last modified
Параметры: string $data[0] - адрес страницы, которая была изменена */
$db=core::db();
$cfg=core::config();
if($cfg['languageDefault']==_LANG) $link=$data[0]; else $link=_LANG.'/'.$data[0];
$db->query('REPLACE INTO modified (link,time) VALUES ('.$db->escape($link).','.time().')');
return true;
?>