<?php class sController extends controller {

	public function actionIndex() {
		core::import('model/chat');
		$this->content=chat::content(MAIN_CHAT_ID);
		$user=core::user();
		if($user->id) $this->fromLogin=$user->login;
		elseif(isset($_SESSION['chatLogin'])) $this->fromLogin=$_SESSION['chatLogin'];
		else $this->fromLogin=null;
		$this->smile=chat::smile();

		$cfg=core::config('chat');
		$this->pageTitle=$cfg['pageTitle_'._LANG];
		$this->metaTitle=$cfg['metaTitle_'._LANG];
		$this->metaDescription=$cfg['metaDescription_'._LANG];
		$this->metaKeyword=$cfg['metaKeyword_'._LANG];
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
		if(!$login) die(strip_tags(core::error(false)));

		$message=chat::filterMessage($data['message']);
		if(!$message) die(strip_tags(core::error(false)));
		$line=chat::post(MAIN_CHAT_ID,$login,$message);
		if(!$line) die(strip_tags(core::error(false)));
		die($line);
	}

	public function adminIndexLink() {
		return array(
			array('chat.setting','?controller=chat&action=setting&chatId='.MAIN_CHAT_ID,'setting','Настройки чата'),
			array('chat.moderate','?controller=chat&action=message&chatId='.MAIN_CHAT_ID,'edit','Модерирование сообщений')
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