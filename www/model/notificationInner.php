<?php class notificationInner extends notification {

	public function title() {
		core::language('notification');
		return LNGMessageInner;
	}

	public function available() {
		return true;
	}

	public function send($userId,$message) {
		$user=core::user();
		$db=core::db();
		if($userId==$user->id) $recepient=array($user->id,$user->login);
		else {
			$login=$db->fetchValue('SELECT login FROM user WHERE id='.(int)$userId);
			if(!$login) return false;
			$recepient=array((int)$userId,$login);
		}
		if($this->userId==$user->id) $sender=array($user->id,$user->login);
		else $sender=array($this->defaultUserId,$this->defaultUserLogin);
		if(!$sender[0] || !$sender[1]) return false;
		return $db->insert('userMessage',array(
			'user1Id'=>$sender[0],
			'user1Login'=>$sender[1],
			'user2Id'=>$recepient[0],
			'user2Login'=>$recepient[1],
			'message'=>trim($message),
			'date'=>time()
		));
	}

}