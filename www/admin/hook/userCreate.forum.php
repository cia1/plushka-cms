<?php
/**
 * @package forum
 * Событие: удаление пользователя
 * @var array $data :
 *  int    [0] ИД пользователя
 *  string [1] Логин
 *  string [2] Адрес электронной почты
 */
use plushka\admin\core\plushka;

$db=plushka::db();
$db->insert('forum_user',[
	'id'=>$data[0],
	'login'=>$data[1],
	'date'=>time()
]);
return true;