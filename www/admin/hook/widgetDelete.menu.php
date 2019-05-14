<?php
use plushka\admin\model\objectLink;

/* Событие: удаление виджета
Модуль: меню
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, mixed $data[2] - параметры виджеты */
if($data[0]!='menu') return true;

//Если есть копии виджета, то ничего не делать
$cnt=ObjectLink::fromSectionWidget('menu',$data[2])+ObjectLink::fromTemplateWidget('menu',$data[2]);
if($cnt>1) return true;
//Иначе удалить пункты меню и само меню
$db=plushka::db();
if($db->fetchValue('SELECT 1 FROM menu_item WHERE menuId='.$data[2])) {
	plushka::error('Необходимо сначала удалить все пункты меню.');
	return false;
}
$db->query('DELETE FROM menu WHERE id='.$data[2]);
return true;