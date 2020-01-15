<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/* Форма авторизации или приветствие пользователя
array $options: bool form - выводить форму авторизации или нет; bool link - выводить ссылки "регистрация", "восстановление пароля" и т.д.;
bool message - Ссылка на личные сообщения */

/**
 * Блок пользователь: форма авторизации, ссылки на регистрацию и восстановление пароля,, блок приветствия
 * @property-read array $options:
 *  bool $form Выводить форму авторизации или нет
 *  bool $link Выводить ли ссылки "регистрация", "восстановить пароль"
 *  bool $message Ссылка на личные сообщения и количество непрочитанных сообщений
 */
class UserWidget extends Widget {

	public function __invoke(): bool {
		plushka::language('user');
		return true;
	}

	public function render($view): void {
		//Значения по умолчанию
		if(is_array($this->options)===true) $this->options=array_merge(['form'=>true,'link'=>true,'message'=>true],
			$this->options);
		else $this->options=['form'=>true,'link'=>true,'message'=>true];
		$u=plushka::user();
		if($u->id) { //Пользователь авторизован
			echo LNGHello.', <a href="',plushka::link('user'),'">',$u->login,'</a> (<a href="',plushka::link('user/logout')
			,'">',LNGexit,'</a>)<br />';
			if($this->options['message']) {
				if(isset($_SESSION['newMessageCount'])===false || $_SESSION['newMessageTimeout']<time()) {
					$db=plushka::db();
					$_SESSION['newMessageCount']=(int)$db->fetchValue('SELECT COUNT(user2Id) FROM user_message WHERE user2Id='.$u->id.' AND isNew=1');
					$_SESSION['newMessageTimeout']=time()+120;
				}
				echo '<span class="link"><a href="',plushka::link('user/message'),'">',LNGMessages,'(',$_SESSION['newMessageCount'],')</a></span>';
			}
			$this->view=null;
		} else { //Пользователь не авторизован
			if($this->options['form']) {
				$f=plushka::form('user');
				$f->text('login',LNGLogin);
				$f->password('password',LNGPassword);
				$f->submit(LNGEnter);
				$f->render('user/login');
			}
			if($this->options['link']) echo '<span class="link"><a href="',plushka::link('user/register'),'">'
			,LNGRegistration,'</a> / <a href="',plushka::link('user/restore'),'">',LNGForgotPassword,'</a></span>';
		}
	}

}
