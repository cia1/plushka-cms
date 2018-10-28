<?php
/* Событие: добавление языка
Параметры: string string $data[0] - псевдоним языка */
$db=core::db();
$cfg=core::config();
$db->query('INSERT INTO article_category_'.$data[0].' SELECT * FROM article_category_'.$cfg['languageDefault']);
$db->query('INSERT INTO article_'.$data[0].' SELECT * FROM article_'.$cfg['languageDefault']);
return true;