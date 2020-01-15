<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/**
 * Выводит общие кнопки административного интерфейса
 */
class AdminWidget extends Widget {

	public function __invoke(): bool { return true; }

	public function adminLink(): array {
		$user=plushka::userReal();
		$link=[];
		//Загрузить общие кнопки админки для текущего пользователя
		$q='';
		$db=plushka::db();
		array_walk($user->right,function($isMain,$module) use (&$q,$db) {
			if($isMain) {
				if($q) $q.=','.$db->escape($module); else $q=$db->escape($module);
			}
		});
		if(!$q) return [];
		$db->query('SELECT module,description,picture FROM user_right WHERE module IN ('.$q.')');
		//Сформировать массив кнопок
		while($item=$db->fetch()) {
			$module=explode('.',$item[0]);
			$link[]=[$item[0],'?controller='.$module[0].($module[1] && $module[1]!='*' ? '&action='.$module[1] : ''),$item[2],$item[1]];
		}
		if(isset($_SESSION['userReal'])===true) $link[]=['user.user','?controller=user&action=return','logout','Выйти из режима подмены пользователя'];
		return $link;
	}

}
