<?php
namespace plushka\widget;
use plushka;
use plushka\model\Chat;

class ChatWidget extends \plushka\core\Widget {

	public function __invoke() {
		if(!isset($this->options)) $this->options=MAIN_CHAT_ID;
		$this->content=Chat::content($this->options,4);
		plushka::language('chat');
		return 'Chat';
	}

}