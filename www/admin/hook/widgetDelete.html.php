<?php
/**
 * Событие: удаление виджета
 * @var array $data :
 *  string [0] Имя виджета
 *  int    [1] Идентификатор виджета
 *  mixed  [2] Данные виджета (десериализованные)
 */
use plushka\admin\core\plushka;

if($data[0]!=='html') return true;
foreach(plushka::config('_core','languageList') as $item) {
	$f=plushka::path().'data/widgetHtml/'.$data[2].'_'.$item.'.html';
	if(file_exists($f)===true) unlink($f);
}
return true;