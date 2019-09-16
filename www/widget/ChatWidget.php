<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;
use plushka\model\Chat;

class ChatWidget extends Widget {

	public function __invoke() {
		if(!isset($this->options)) $this->options=MAIN_CHAT_ID;
		$this->content=Chat::content($this->options,4);
		plushka::language('chat');
		return 'Chat';
	}

}
