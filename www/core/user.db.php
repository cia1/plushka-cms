<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует "пользователя", именно этот класс находится в сессии */
class user {
	public $id;
	public $login;
	public $email;
	public $group=0;

	/* Пытается загрузить данные пользователя */
	public function __construct($id=null) {
		if(!$id) { //идентификатора пользователя нет - искать логин и пароль в кукисах
			if(isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
				$this->login($_COOKIE['login'],$_COOKIE['password']);
			}
		} else { //идентификатор пользователя задан непосредственно
			$db=core::db();
			$item=$db->fetchArrayOnce('SELECT login,groupId,email FROM user WHERE id='.(int)$id.' AND status=1');
			if(!$item) {
				controller::$error='Пользователь не найден';
				return;
			}
			$this->id=$id;
			$this->login=$item[0];
			$this->email=$item[2];
			$this->group=$item[1];
		}
	}

	/* Возвращает массив, содержащий все права пользователя (только для администраторов) */
	public function rightData($module) {
		$db=core::db();
		return $db->fetchArrayOnceAssoc('SELECT description,picture FROM userRight WHERE module='.$db->escape($module));
	}

	/* Авторизация по логину и паролю */
	public function login($login,$password) {
		$db=core::db();
		$login=$db->getEscape($login);
		$password=$db->getEscape($password);
		$item=$db->fetchArrayOnce('SELECT id,email,groupId FROM user WHERE login="'.$login.'" AND password="'.$password.'" AND status=1');
		if(!$item) {
			controller::$error='Логин или пароль указаны неверно';
			return false;
		}
		$this->login=$login;
		$this->id=$item[0];
		$this->email=$item[1];
		$this->group=$item[2];
		//Если пользователь является администратором, то загрузить его права
		if($this->group>=200) {
			$this->right=array();
			$db->query('SELECT module,groupId,picture FROM userRight');
			while($item=$db->fetch()) {
				$group=explode(',',$item[1]);
				if(in_array($this->group,$group) || $this->group==255) {
					if($item[2]) $this->right[$item[0]]=true; else $this->right[$item[0]]=false;
				}
			}
		}
		return true;
	}

	/* "Выход" пользователя */
	public function logout() {
		$this->id=null;
		$this->login=null;
		$this->email=null;
		$this->group=0;
		return true;
	}

}
?>