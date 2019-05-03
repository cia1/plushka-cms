<?php
namespace plushka\model;
use plushka;
use plushka\core\Email;

class NotificationEmail extends Notification {

	public function title() {
		return 'E-mail';
	}

	public function available() {
		return true;
	}

	public function send($message) {
		$email=new Email();
		if($this->userId==plushka::userId()) $recepient=plushka::user()->email;
		else $recepient=plushka::db()->fetchValue('SELECT email FROM user WHERE id='.(int)$this->userId);
		if(!$recepient) return false;
		if($user->email) {
			$fromEmail=$user->email;
			$fromName=$user->login;
		} else {
			if($this->fromEmail==='cfg') $fromEmail=plushka::config('_core','adminEmailEmail'); else $fromEmail=$this->fromEmail;
			if($this->fromName==='cfg') $fromName=plushka::config('_core','adminEmailName'); else $fromName=$this->fromName;
		}
		$email->from($fromEmail,$fromName);
		$email->replyTo($fromEmail,$fromName);
		$email->subject($this->subject);
		$email->message($message);
		return $email->send($recepient);
	}

}