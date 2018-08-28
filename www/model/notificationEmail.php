<?php class notificationEmail extends notification {

	public function title() {
		return 'E-mail';
	}

	public function available() {
		return true;
	}

	public function send($userId,$message) {
		core::import('core/email');
		$email=new email();
		$user=core::user();
		if($userId==core::user()) $recepient=$user->email;
		else $recepient=core::db()->fetchValue('SELECT email FROM user WHERE id='.(int)$userId);
		if(!$recepient) return false;
		if($user->email) {
			$fromEmail=$user->email;
			$fromName=$user->login;
		} else {
			if($this->fromEmail==='cfg') $fromEmail=core::config('_core','adminEmailEmail'); else $fromEmail=$this->fromEmail;
			if($this->fromName==='cfg') $fromName=core::config('_core','adminEmailName'); else $fromName=$this->fromName;
		}
		$email->from($fromEmail,$fromName);
		$email->replyTo($fromEmail,$fromName);
		$email->subject($this->subject);
		$email->message($message);
		return $email->send($recepient);
	}

}