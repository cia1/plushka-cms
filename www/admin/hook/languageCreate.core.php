<?php
/* Событие: добавление языка
Параметры: string string $data[0] - псевдоним языка */
$db=plushka::db();
$cfg=plushka::config();
$db->query('UPDATE menu_item SET title_'.$data[0].'=title_'.$cfg['languageDefault']);
$db->query('UPDATE widget SET title_'.$data[0].'=title_'.$cfg['languageDefault']);
return true;