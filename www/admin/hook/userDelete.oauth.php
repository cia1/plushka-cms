<?php
/**
 * @package oauth
 * Событие: удаление пользователя
 * @var array $data :
 *  int    [0] ИД пользователя
 *  string [1] Логин
 *  string [2] Адрес электронной почты
 */
use plushka\admin\core\plushka;

$db=plushka::db();
$db->query('DELETE FROM oauth WHERE userId='.$data[0]);
return true;