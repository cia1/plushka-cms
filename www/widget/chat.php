<?php class widgetchat extends widget {

	public function __invoke() {
		core::import('model/chat');
		$this->content=chat::content();
		core::language('chat');
		return 'Chat';
	}

}