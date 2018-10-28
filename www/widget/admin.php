<?php
//Внимание! Этот файл является частью фреймворка, вносить изменения не рекомендуется.
/* Виджет выводит общие кнопки административного интерфейса */
class widgetAdmin extends widget {

	public function __invoke() { return true; }

	public function adminLink() {
		$u=core::userCore();
		$link=array();
		//Загрузить общие кнопки админки для текущего пользователя
		$q='';
		$db=core::db();
		array_walk($u->right,function($isMain,$module) use(&$q,$db) {
			if($isMain) {
				if ($q) $q.=','.$db->escape($module); else $q=$db->escape($module);
			}
		});
		if(!$q) return array();
		$db->query('SELECT module,description,picture FROM user_right WHERE module IN ('.$q.')');
		//Сформировать массив кнопок
		while($item=$db->fetch()) {
			$module=explode('.',$item[0]);
			$link[]=array($item[0],'?controller='.$module[0].($module[1] && $module[1]!='*' ? '&action='.$module[1] : ''),$item[2],$item[1]);
		}
		if(isset($_SESSION['userCore'])) $link[]=array('user.user','?controller=user&action=return','logout','Выйти из режима подмены пользователя');
		return $link;
	}
}