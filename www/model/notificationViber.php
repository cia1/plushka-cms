<?php class notificationViber extends notification {

	public function title() {
		return 'Viber';
	}

	public function available() {
		$viber=self::userAttribute($this->userId,'viber');
		if(is_string($viber) && strlen($viber)===24) return true;
		return false;
	}

}