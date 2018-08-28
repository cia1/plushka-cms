<?php class widgetNotificationViberSetting extends widget {

	public function __invoke() {
		$cfg=core::config('notification','viber');
		$this->groupId=$cfg['groupId'];
		$this->qrCode=$cfg['qrCode'];
		return 'NotificationViberSetting';
	}

}