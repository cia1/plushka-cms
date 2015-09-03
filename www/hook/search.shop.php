<?php
/* Событие: поиск по сайту по ключевой фразе
Модуль: shop (интернет-магазин)
Параметры: string $data[0] - ключевая фраза
*/
$db=core::db();
$keyword=$db->escape('%'.$data[0].'%');
$db->query('SELECT alias,title,categoryId FROM shpProduct WHERE title LIKE '.$keyword);
while($item=$db->fetch()) {
	echo '<li><p><a href="'.core::link('shop/category').'">'.LNGShop.'</a> / <a href="'.core::link('shop/category/'.$item[2].'/'.$item[0]).'">'.$item[1].'</a></p></li>';
}
return true;
?>