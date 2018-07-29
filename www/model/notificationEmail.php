<?php class notificationEmail extends notification {

	public function title() {
		return 'E-mail';
	}

	public function available() {
		return true;
	}

}