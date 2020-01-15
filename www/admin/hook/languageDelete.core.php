<?php
/**
 * Событие: удаление языка
 * @var array $data :
 *  string [0] Псевдоним языка
 */
use plushka\admin\core\plushka;

$path=plushka::path().'data/widgetHtml/';
$d=opendir($path);
while($f=readdir($d)) {
	if($f==='.' || $f==='..') continue;
	if(substr($f,strlen($f)-8)!=='_'.$data[0].'.html') continue;
	$f=$path.$f;
	if(file_exists($f)===true) unlink($f);
}
closedir($d);
return true;