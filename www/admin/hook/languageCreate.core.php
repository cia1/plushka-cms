<?php
/* Событие: добавление языка
Параметры: string string $data[0] - псевдоним языка */
$db=core::db();
$cfg=core::config();
$db->query('UPDATE menuItem SET title_'.$data[0].'=title_'.$cfg['languageDefault']);
$db->query('UPDATE widget SET title_'.$data[0].'=title_'.$cfg['languageDefault']);
return true;
?>