<?php
/**
 * Событие: удаление пункта меню
 * @var array $data :
 *  string [0] ссылка на удаляемый пункт меню
 */
use plushka\admin\core\plushka;

$link=$data[0];

$db=plushka::db();
$db->query('DELETE FROM section WHERE url IN('.$db->escape($link.'.').','.$db->escape($link.'*').','.$db->escape($link.'/').')');
return true;