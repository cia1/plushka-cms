<?php
/**
 * Событие: добавление языка
 * @var array $data :
 *  string [0] Псевдоним языка
 */
use plushka\admin\core\plushka;

$languageDefault=plushka::config('_core','languageDefault');
$db=plushka::db();
$db->query('UPDATE menu_item SET title_'.$data[0].'=title_'.$languageDefault);
$db->query('UPDATE widget SET title_'.$data[0].'=title_'.$languageDefault);
return true;