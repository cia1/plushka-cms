<?php
/**
 * Событие: добавление языка
 * @var array $data :
 *  string [0] Псевдоним языка
 */
use plushka\admin\core\plushka;

$languageDefault=plushka::config('_core','languageDefault');
$db=plushka::db();
$db->query('INSERT INTO article_category_'.$data[0].' SELECT * FROM article_category_'.$languageDefault);
$db->query('INSERT INTO article_'.$data[0].' SELECT * FROM article_'.$languageDefault);
return true;