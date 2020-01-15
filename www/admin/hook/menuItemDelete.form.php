<?php
/**
 * Событие: удаление пункта меню
 * @var array $data :
 *  string [0] ссылка на удаляемый пункт меню
 */
use plushka\admin\core\plushka;

$link=$data[0];

if(substr($link,0,5)!=='form/') return true;
$id=substr($link,5);
$db=plushka::db();
$db->query('DELETE FROM frm_field WHERE formId='.$id);
$db->query('DELETE FROM frm_form WHERE id='.$id);
return true;