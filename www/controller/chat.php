<?php class sController extends controller {

	public function actionIndex() {
		core::import('model/chat');
		$this->content=chat::content();
		$user=core::user();
		if($user->id) $this->fromLogin=$user->login;
		elseif(isset($_SESSION['chatLogin'])) $this->fromLogin=$_SESSION['chatLogin'];
		else $this->fromLogin=null;
		$this->smile=chat::smile();

		core::language('chat');
		$this->js('jquery.min');
		$this->js('jquery.form');
		$this->js('jquery.chat');
		$this->style('chat');
		return 'Index';
	}

	//Отправка сообщения в чат
	public function actionIndexSubmit($data) {
		//Определить логин пользвателя
		$user=core::user();
		core::import('model/chat');
		if($user->id) $login=$user->login;
		elseif(isset($_SESSION['chatLogin'])) $login=$_SESSION['chatLogin'];
		else $login=chat::filterLogin($data['login'],$data['captcha']);
		if(!$login) return;

		$message=chat::filterMessage($data['message']);
		if(!$message) return false;
		$line=chat::post($login,$message);
		if(!$line) die(core::error());
		die($line);
	}

	public function adminIndexLink() {
		return array(
			array('chat.setting','?controller=chat&action=setting','setting','Настройки чата'),
			array('chat.moderate','?controller=chat&action=message','edit','Модерирование сообщений')
		);
	}

	//Для AJAX-запросов обновления чата
	public function actionContent() {
		core::import('model/chat');
		$this->content='';
		$data=chat::content($_GET['time']);
		foreach($data as $item) {
			if($this->content) $this->content.="\n";
			$this->content.=$item['time']."\t".$item['fromLogin'].'|'.$item['fromId']."\t".$item['toLogin'].'|'.$item['toId']."\t".$item['message']."\t".$item['attribute'];
		}
		return '_empty';

	}

}