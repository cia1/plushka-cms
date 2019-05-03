<?php
namespace plushka\model;
use plushka;

class NotificationInner extends Notification {

	public function title() {
		plushka::language('notification');
		return LNGMessageInner;
	}

	public function available() {
		return true;
	}

	public function send($message) {
		$db=plushka::db();
		$user=plushka::user();
		if($this->userId==$user->id) $recepient=array($this->userId,$user->login);
		else {
			$login=$db->fetchValue('SELECT login FROM user WHERE id='.(int)$this->userId);
			if(!$login) return false;
			$recepient=array((int)$this->userId,$login);
		}
		if($user->group) $sender=array($user->id,$user->login);
		else $sender=array($this->defaultUserId,$this->defaultUserLogin);
		if(!$sender[0] || !$sender[1]) return false;
		return $db->insert('user_message',array(
			'user1Id'=>$sender[0],
			'user1Login'=>$sender[1],
			'user2Id'=>$recepient[0],
			'user2Login'=>$recepient[1],
			'message'=>trim($message),
			'date'=>time()
		));
	}

}