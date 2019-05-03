<?php
namespace plushka\model;
use plushka;
use plushka\model\User;
use plushka\model\Request;

class NotificationViber extends Notification {

	public function title() {
		return 'Viber';
	}

	public function available() {
		$viber=self::userAttribute($this->userId,'viber');
		if(is_string($viber) && strlen($viber)===24) return true;
		return false;
	}

	public function send($message) {
		plushka::import('model/user');
		if($this->userId===plushka::userId()) $user=plushka::user()->model();
		else {
			$user=new User();
			$user->id=$this->userId;
		}
		$recipient=$user->attribute('viber');
		unset($user);
		if(!$recipient || strlen($recipient)!==24) return false;

		$request=new Request();
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