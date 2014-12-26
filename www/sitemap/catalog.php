<?php
/* Выводит XML-карту сайта для модуля catalog (универсальный каталог) */
$path=core::path().'config/catalogLayout/';
$d=opendir($path);
//Перечислить все каталоги сайта
while($f=readdir($d)) {
	$f=(int)$f;
	if(!$f) continue;
	_catalogSitemap($f);
}
closedir($d);

/* Выводит карту сайта для каталога с идентификатором $id */
function _catalogSitemap($id) {
	echo '<url><loc>http://'.$_SERVER['HTTP_HOST'].core::link('catalog/'.$id)."</loc></url>\n";
	$db1=core::db();
	$db1->query('SELECT alias FROM catalog_'.$id);
	$db2=core::db(true);
	while($item=$db1->fetch()) {
		$lm=$db2->fetchValue('SELECT time FROM modified WHERE link='.$db2->escape('catalog/'.$id.'/'.$item[0]));
		echo '<url><loc>http://'.$_SERVER['HTTP_HOST'].core::link('catalog/'.$id.'/'.$item[0]).'</loc>';
		if($lm) echo '<lastmod>'.date('d.m.Y',$lm).'</lastmod>';
		echo "</url>\n";
	}
}
?>