<?php
/* Событие: поиск по сайту по ключевой фразе. Ищет только по заголовку записи
Модуль: catalog (универсальный каталог)
Параметры: string $data[0] - ключевая фраза */
$db=plushka::db();
$keyword=$db->escape('%'.$data[0].'%');
$path=plushka::path().'config/catalogLayout/'; //тут хранятся конфиграции всех каталогов
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
	$db=plushka::db();
	$db->query('SELECT alias,title FROM catalog_'.$id.' WHERE title LIKE '.$keyword);
	while($item=$db->fetch()) {
		echo '<li><p><a href="'.plushka::link('catalog/'.$id).'">'.LNGCatalog.'</a> / <a href="'.plushka::link('catalog/'.$id.'/'.$item[0]).'">'.$item[1].'</a></p></li>';
	}
}
?>