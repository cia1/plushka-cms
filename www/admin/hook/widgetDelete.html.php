<?php
/* Событие: удаление виджета
Модуль: произвольный текст
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, mixed $data[2] - параметры виджета */

if($data[0]!='html') return true;
$f=core::path().'data/widgetHtml/'.$data[2].'.html';
if(!file_exists($f)) return true;
unlink($f);
return true;
?>