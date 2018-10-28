<?php
/* Выводит XML-карту сайта для модуля article (статьи) */
$db=core::db();

$items=$db->fetchArray('SELECT link FROM menu_item WHERE link LIKE '.$db->escape('article/%'));
$category=array();
for($i=0,$cnt=count($items);$i<$cnt;$i++) {
	$item=$items[$i];
	$l=substr($item[0],8,5);
	if($l=='blog/') $l='blog'; elseif($l=='list/') $l='list'; else continue;
	$category[substr($item[0],13)]=$l;
	$m=$db->fetchValue('SELECT time FROM modified WHERE link='.$db->escape($item[0]));
	echo '<url><loc>//'.$_SERVER['HTTP_HOST'].core::link($item[0]).'</loc>'.($m ? '<lastmod>'.date('Y-m-d',$m).'</lastmod>' : '').'</url>'."\n";
}
echo "\n";
$items=$db->fetchArray('SELECT c.alias,a.alias FROM article_'._LANG.' a LEFT JOIN article_category_'._LANG.' c ON c.id=a.categoryId ORDER BY c.alias');
$lastAlias='';
for($i=0,$cnt=count($items);$i<$cnt;$i++) {
	echo '<url>';
	$alias1=$items[$i][0];
	if($alias1) {
		if(!isset($category[$alias1])) continue;
		$l='article/'.$category[$alias1].'/'.$alias1.'/'.$items[$i][1];
	}	else $l='article/view/'.$items[$i][1];
	$m=$db->fetchValue('SELECT time FROM modified WHERE link='.$db->escape($l));
	echo '<loc>//'.$_SERVER['HTTP_HOST'].core::link($l).'</loc>'.($m ? '<lastmod>'.date('Y-m-d',$m).'</lastmod>' : '');
	echo "</url>\n";
}