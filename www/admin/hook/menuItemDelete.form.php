<?php
/* Событие: удаление пункта меню
Модуль: контактные формы
Параметры: string $data[0] - удаляемая ссылка, int $data[1] - идентификатор пункта меню */
$link=$data[0];

if(substr($link,0,5)!='form/') return true;
$id=substr($link,5);
$db=core::db();
$db->query('DELETE FROM frmField WHERE formId='.$id);
$db->query('DELETE FROM frmForm WHERE id='.$id);
return true;
?>