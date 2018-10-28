<?php class widgetChat extends widget {

	public function __invoke() {
		core::import('model/chat');
		if(!isset($this->options)) $this->options=MAIN_CHAT_ID;
		$this->content=chat::content($this->options,4);
		core::language('chat');
		return 'Chat';
	}

}