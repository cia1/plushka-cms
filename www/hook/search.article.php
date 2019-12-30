<?php
use plushka\core\plushka;

/**
 * Поиск по сайту (статьи)
 * @var array $data: [0] - ключевая фраза
 * @return bool Успешно ли обработано событие
 */
$db=plushka::db();
$keyword=$db->escape('%'.$data[0].'%');
$db->query('SELECT a.alias,a.title,a.text1,c.alias,c.title FROM article_'._LANG.' a LEFT JOIN article_category_'._LANG.' c ON c.id=a.categoryId WHERE a.title LIKE '.$keyword.' OR a.text1 LIKE '.$keyword.' OR a.text2 LIKE '.$keyword);
while($item=$db->fetch()) {
	if(!$item[3]) $link='<a href="'.plushka::link('article/view/'.$item[0]).'">';
	elseif($item[2]) $link='<a href="'.plushka::link('article/blog/'.$item[3]).'">'.$item[4].'</a> / <a href="'.plushka::link('article/blog/'.$item[3].'/'.$item[0]).'">';
	else $link='<a href="'.plushka::link('article/list/'.$item[3]).'">'.$item[4].'</a> / <a href="'.plushka::link('article/list/'.$item[3].'/'.$item[0]).'">';
	echo '<li><p>'.$link.$item[1].'</a></p>'.$item[2].'</li>';
}
return true;