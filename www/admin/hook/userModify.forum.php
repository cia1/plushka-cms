<?php
/**
 * @package forum
 * Событие: изменение пользователя
 * @var array $data :
 *  int    [0] ИД пользователя
 *  string [1] Логин
 *  string [2] Адрес электронной почты
 */
use plushka\admin\core\plushka;

$db=plushka::db();
$db->query('UPDATE forum_user SET login='.$db->escape($data[1]).' WHERE id='.$data[0]);
return true;