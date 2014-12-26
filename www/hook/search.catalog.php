<?php
/* Событие: поиск по сайту по ключевой фразе. Ищет только по заголовку записи
Модуль: catalog (универсальный каталог)
Параметры: string $data[0] - ключевая фраза */
$db=core::db();
$keyword=$db->escape('%'.$data[0].'%');
$path=core::path().'config/catalogLayout/'; //тут хранятся конфиграции всех каталогов
$d=opendir($path);
while($f=readdir($d)) { //перебрать все каталоги на сайте
	$f=(int)$f;
	if(!$f) continue;
	_catalog($f,$keyword);
}
closedir($d);
return true;

/* Выводит результаты поиска по ключевой фразе $keyword для каталога с идентификатором $id */
function _catalog($id,$keyword) {
	$db=core::db();
	$db->query('SELECT alias,title FROM catalog_'.$id.' WHERE title LIKE '.$keyword);
	while($item=$db->fetch()) {
		echo '<li><p><a href="'.core::link('catalog/'.$id).'">Каталог</a> / <a href="'.core::link('catalog/'.$id.'/'.$item[0]).'">'.$item[1].'</a></p></li>';
	}
}
?>