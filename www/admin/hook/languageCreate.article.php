<?php
/* Событие: добавление языка
Параметры: string string $data[0] - псевдоним языка */
$db=core::db();
$cfg=core::config();
$db->query('INSERT INTO articleCategory_'.$data[0].' SELECT * FROM articleCategory_'.$cfg['languageDefault']);
$db->query('INSERT INTO article_'.$data[0].' SELECT * FROM article_'.$cfg['languageDefault']);
return true;