<?php
core::import('model/chat');
class widgetChatForm extends widget {

	public function __invoke() {
		if(!isset($this->options['chatId'])) $this->options['chatId']=MAIN_CHAT_ID;
		if(!isset($this->options['urlSubmit'])) $this->options['urlSubmit']='chat';
		if(!isset($this->options['urlContent'])) $this->options['urlContent']=core::url().'index2.php?controller=chat&action=content';
		$this->content=chat::content($this->options['chatId']);
		$user=core::user();
		if($user->id) $this->fromLogin=$user->login;
		elseif(isset($_SESSION['chatLogin'])) $this->fromLogin=$_SESSION['chatLogin'];
		else $this->fromLogin=null;
		$this->smile=chat::smile();
		core::language('chat');
		return 'ChatForm';
	}

}