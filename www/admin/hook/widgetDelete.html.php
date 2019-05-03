<?php
/* Событие: удаление виджета
Модуль: произвольный текст
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, mixed $data[2] - параметры виджета */

if($data[0]!='html') return true;
$cfg=plushka::config();
foreach($cfg['languageList'] as $item) {
	$f=plushka::path().'data/widgetHtml/'.$data[2].'_'.$item.'.html';
	if(file_exists($f)) unlink($f);
}
return true;
?>