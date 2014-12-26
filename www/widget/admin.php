<?php
//Внимание! Этот файл является частью фреймворка, вносить изменения не рекомендуется.
/* Виджет выводит общие кнопки административного интерфейса */
class widgetAdmin extends widget {

	public function action() { return true; }

	public function adminLink() {
		$u=core::userCore();
		$link=array();
		//перебрать права пользователя - среди них есть те, которые являют собой кнопки админки
		foreach($u->right as $module=>$isMain) {
			if(!$isMain) continue;
			$data=$u->rightData($module);
			$item=explode('.',$module);
			$link[]=array($module,'?controller='.$item[0].($item[1] && $item[1]!='*' ? '&action='.$item[1] : ''),$data['picture'],$data['description']);
		}
		if(isset($_SESSION['userCore'])) $link[]=array('user.user','?controller=user&action=return','logout','Выйти из режима подмены пользователя');
		return $link;
	}
}
?>