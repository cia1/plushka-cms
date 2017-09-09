<?php class widgetchat extends widget {

	public function __invoke() {
		core::import('model/chat');
		$this->content=chat::content(6);
		core::language('chat');
		return 'Chat';
	}

}