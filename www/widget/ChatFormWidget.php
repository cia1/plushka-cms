<?php
namespace plushka\widget;
use plushka;
use plushka\model\Chat;

class ChatFormWidget extends \plushka\core\Widget {

	public function __invoke() {
		if(!isset($this->options['chatId'])) $this->options['chatId']=MAIN_CHAT_ID;
		if(!isset($this->options['urlSubmit'])) $this->options['urlSubmit']='chat';
		if(!isset($this->options['urlContent'])) $this->options['urlContent']=plushka::url().'index2.php?controller=chat&action=content';
		$this->content=Chat::content($this->options['chatId']);
		$user=plushka::user();
		if($user->id) $this->fromLogin=$user->login;
		elseif(isset($_SESSION['chatLogin'])) $this->fromLogin=$_SESSION['chatLogin'];
		else $this->fromLogin=null;
		$this->smile=Chat::smile();
		plushka::language('chat');
		return 'ChatForm';
	}

}