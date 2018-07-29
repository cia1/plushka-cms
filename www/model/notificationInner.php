<?php class notificationInner extends notification {

	public function title() {
		core::language('notification');
		return LNGMessageInner;
	}

	public function available() {
		return true;
	}

}