<?php class widgetchat extends widget {

	public function __invoke() {
		core::import('model/chat');
		$this->content=chat::content('default',6);
		core::language('chat');
		return 'Chat';
	}

}