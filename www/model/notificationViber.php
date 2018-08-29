<?php class notificationViber extends notification {

	public function title() {
		return 'Viber';
	}

	public function available() {
		$viber=self::userAttribute($this->userId,'viber');
		if(is_string($viber) && strlen($viber)===24) return true;
		return false;
	}

	public function send($message) {
		core::import('model/user');
		if($this->userId===core::userId()) $user=core::user()->model();
		else {
			$user=new modelUser();
			$user->id=$this->userId;
		}
		$recipient=$user->attribute('viber');
		unset($user);
		if(!$recipient || strlen($recipient)!==24) return false;

		core::import('model/request');
		$request=new request();
		$request->custom('X-Viber-Auth-Token',$this->token,true);
		if($request->post('https://chatapi.viber.com/pa/send_message',json_encode(array(
			'receiver'=>$recipient,
			'type'=>'text',
			'sender'=>array('name'=>$this->groupId),
			'text'=>strip_tags(str_replace(array('<br>','<br />','<br/>'),"\n",$message))
		)))!=200) return false;
		return true;
	}

}