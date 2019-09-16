<?php
namespace plushka\controller;
use plushka\core\plushka;
use plushka\model\Chat;

class ChatController extends \plushka\core\Controller {

	public function actionIndex() {
		$cfg=plushka::config('chat');
		$this->pageTitle=$cfg['pageTitle_'._LANG];
		$this->metaTitle=$cfg['metaTitle_'._LANG];
		$this->metaDescription=$cfg['metaDescription_'._LANG];
		$this->metaKeyword=$cfg['metaKeyword_'._LANG];
		return 'Index';
	}

	protected function breadcrumbIndex() {
		return array('{{pageTitle}}');
	}

	//Отправка сообщения в чат
	public function actionIndexSubmit($data) {
		$line=Chat::submit(MAIN_CHAT_ID,$data);
		if(!$line) die(strip_tags(plushka::error(false)));
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
		$this->content='';
		$data=Chat::content(MAIN_CHAT_ID,$_GET['time']);
		foreach($data as $item) {
			if($this->content) $this->content.="\n";
			$this->content.=$item['time']."\t".$item['fromLogin'].'|'.$item['fromId']."\t".$item['toLogin'].'|'.$item['toId']."\t".$item['message']."\t".$item['attribute'];
		}
		return '_empty';
	}

}