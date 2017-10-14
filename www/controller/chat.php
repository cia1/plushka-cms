<?php class sController extends controller {

	public function actionIndex() {
		core::import('model/chat');
		$cfg=core::config('chat');
		$this->pageTitle=$cfg['pageTitle_'._LANG];
		$this->metaTitle=$cfg['metaTitle_'._LANG];
		$this->metaDescription=$cfg['metaDescription_'._LANG];
		$this->metaKeyword=$cfg['metaKeyword_'._LANG];
		return 'Index';
	}

	//Отправка сообщения в чат
	public function actionIndexSubmit($data) {
		core::import('model/chat');
		$line=chat::submit(MAIN_CHAT_ID,$data);
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
		$data=chat::content(MAIN_CHAT_ID,$_GET['time']);
		foreach($data as $item) {
			if($this->content) $this->content.="\n";
			$this->content.=$item['time']."\t".$item['fromLogin'].'|'.$item['fromId']."\t".$item['toLogin'].'|'.$item['toId']."\t".$item['message']."\t".$item['attribute'];
		}
		return '_empty';
	}

}