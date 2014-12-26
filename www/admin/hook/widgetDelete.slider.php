<?php
/* Событие: удаление виджета
Модуль: slider (слайдер)
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, mixed $data[2] - параметры виджета
int $data[2]['id'] - идентификатор слайдера */
if($data[0]!='slider') return true;

$cfg=core::config('slider-'.$data[2]['id']);
$path=core::path().'public/slider/';
foreach($cfg['data'] as $item) unlink($path.$data[2]['id'].'.'.$item['image']);
unlink(core::path().'config/slider-'.$data[2]['id'].'.php');
return true;
?>