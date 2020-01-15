<?php
/**
 * @package vote
 * Событие: удаление виджета
 * @var array $data :
 *  string [0] Имя виджета
 *  int    [1] Идентификатор виджета
 *  mixed  [2] Данные виджета (десериализованные)
 */
use plushka\admin\core\plushka;

if($data[0]!=='vote') return true;

$db=plushka::db();
$db->query('DELETE FROM vote WHERE id='.$data[2]);
return true;