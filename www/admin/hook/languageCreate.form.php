<?php
/**
 * Событие: добавление языка
 * @var array $data :
 *  string [0] Псевдоним языка
 */
use plushka\admin\core\plushka;

$languageDefault=plushka::config('_core','languageDefault');
$db=plushka::db();
$db->query('UPDATE frm_field SET title_'.$data[0].'=title_'.$languageDefault.',data_'.$data[0].'=data_'.$languageDefault);
$db->query('UPDATE frm_form SET title_'.$data[0].'=title_'.$languageDefault.',subject_'.$data[0].'=subject_'.$languageDefault.',successMessage_'.$data[0].'=successMessage_'.$languageDefault);
return true;