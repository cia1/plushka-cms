<?php
/**
 * Событие: удаление виджета
 * @var array $data :
 *  string [0] Имя виджета
 *  int    [1] Идентификатор виджета
 *  mixed  [2] Данные виджета (десериализованные)
 */
use plushka\admin\core\plushka;

if($data[0]!=='form') return true;
$db=plushka::db();
$db->query('DELETE FROM frm_field WHERE formId='.$data[2]);
$db->query('DELETE FROM frm_form WHERE id='.$data[2]);
return true;