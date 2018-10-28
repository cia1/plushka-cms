<?php
/* Событие: удаление виджета
Модуль: контактные формы
Параметры: string $data[0] - имя виджета, int $data[1] - идентификатор виджета, $data[2] - параметры виджета */

if($data[0]!='form') return true;
$db=core::db();
$db->query('DELETE FROM frm_field WHERE formId='.$data[2]);
$db->query('DELETE FROM frm_form WHERE id='.$data[2]);
return true;