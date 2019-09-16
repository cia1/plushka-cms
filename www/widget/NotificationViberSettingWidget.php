<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

class NotificationViberSettingWidget extends Widget {

	public function __invoke() {
		$cfg=plushka::config('notification','viber');
		$this->groupId=$cfg['groupId'];
		$this->qrCode=$cfg['qrCode'];
		return 'NotificationViberSetting';
	}

}
