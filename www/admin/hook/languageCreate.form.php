<?php
/* Событие: добавление языка
Параметры: string string $data[0] - псевдоним языка */
$db=core::db();
$cfg=core::config();
$db->query('UPDATE frm_field SET title_'.$data[0].'=title_'.$cfg['languageDefault'].',data_'.$data[0].'=data_'.$cfg['languageDefault']);
$db->query('UPDATE frm_form SET title_'.$data[0].'=title_'.$cfg['languageDefault'].',subject_'.$data[0].'=subject_'.$cfg['languageDefault'].',successMessage_'.$data[0].'=successMessage_'.$cfg['languageDefault']);
return true;