<?php
/* Форма авторизации или приветствие пользователя
array $options: bool form - выводить форму авторизации или нет; bool link - выводить ссылки "регистрация", "восстановление пароля" и т.д.;
bool message - Ссылка на личные сообщения */
class widgetUser extends widget {

	public function __invoke() {
		core::language('user');
		return true;
	}

	public function render($view) {
		//Значения по умолчанию
		if(is_array($this->options)) $this->options=array_merge(array('form'=>true,'link'=>true,'message'=>true),$this->options);
		else $this->options=array('form'=>true,'link'=>true,'message'=>true);
		$u=core::user();
		if($u->id) { //Пользователь авторизован
			echo LNGHello.', <a href="'.core::link('user').'">'.$u->login.'</a> (<a href="'.core::link('user/logout').'">'.LNGexit.'</a>)<br />';
			if($this->options['message']) {
				if(!isset($_SESSION['newMessageCount']) || $_SESSION['newMessageTimeout']<time()) {
					$db=core::db();
					$_SESSION['newMessageCount']=(int)$db->fetchValue('SELECT COUNT(user2Id) FROM userMessage WHERE user2Id='.$u->id.' AND isNew=1');
					$_SESSION['newMessageTimeout']=time()+120;
				}
				echo '<span class="link"><a href="'.core::link('user/message').'">'.LNGMessages.'('.$_SESSION['newMessageCount'].')</a></span>';
			}
			$this->view=null;
		} else { //Пользователь не авторизован
			if($this->options['form']) {
				$f=core::form('user');
				$f->text('login',LNGLogin);
				$f->password('password',LNGPassword);
				$f->submit(LNGEnter);
				$f->render('user/login');
			}
			if($this->options['link']) echo '<span class="link"><a href="'.core::link('user/register').'">'.LNGRegistration.'</a> / <a href="'.core::link('user/restore').'">'.LNGForgotPassword.'</a></span>';
		}
	}

}