<?php
/* Событие: поиск по сайту по ключевой фразе
Модуль: forum (форум)
Параметры: string $data[0] - ключевая фраза
*/
$db=plushka::db();
$keyword=$db->escape('%'.$data[0].'%');
//Поиск по темам
$db->query('SELECT id,categoryId,title,message FROM forum_topic WHERE title LIKE '.$keyword);
while($item=$db->fetch()) {
	echo '<li><p><a href="',plushka::link('forum'),'">'.LNGForum.'</a> / <a href="',plushka::link('forum/'.$item[1].'/'.$item[0]),'">',$item[2].'</a></p>',$item[3],'</li>';
}
return true;
?>