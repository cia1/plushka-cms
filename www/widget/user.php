<?php
/* Форма авторизации или приветствие пользователя
array $options: bool form - выводить форму авторизации или нет; bool link - выводить ссылки "регистрация", "восстановление пароля" и т.д.;
bool message - Ссылка на личные сообщения */
class widgetUser extends widget {

	public function action() { return true; }

	public function render() {
		//Значения по умолчанию
		if(is_array($this->options)) $this->options=array_merge(array('form'=>true,'link'=>true,'message'=>true),$this->options);
		else $this->options=array('form'=>true,'link'=>true,'message'=>true);
		$u=core::user();
		if($u->id) { //Пользователь авторизован
			echo 'Здравствуйте, <a href="'.core::link('user').'">'.$u->login.'</a> (<a href="'.core::link('user/logout').'">выйти</a>)<br />';
			if($this->options['message']) echo '<span class="link"><a href="'.core::link('user/message').'">Сообщения</a></span>';
			$this->view=null;
		} else { //Пользователь не авторизован
			if($this->options['form']) {
				$f=core::form('user');
				$f->text('login','Логин');
				$f->password('password','Пароль');
				$f->submit('Войти');
				$f->render('user/login');
			}
			if($this->options['link']) echo '<span class="link"><a href="'.core::link('user/register').'">Регистрация</a> / <a href="'.core::link('user/restore1').'">Забыли пароль?</a></span>';
		}
	}

}
?>