<?php
/* Событие: удаление языка
Параметры: string string $data[0] - псевдоним языка */
$path=plushka::path().'data/widgetHtml/';
$d=opendir($path);
while($f=readdir($d)) {
	if($f=='.' || $f=='..') continue;
	if(substr($f,strlen($f)-8)!='_'.$data[0].'.html') continue;
	$f=$path.$f;
	if(file_exists($f)) unlink($f);
}
closedir($d);
return true;
?>