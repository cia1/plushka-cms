<?php
namespace plushka\widget;
use plushka;

class NotificationViberSettingWidget extends \plushka\core\Widget {

	public function __invoke() {
		$cfg=plushka::config('notification','viber');
		$this->groupId=$cfg['groupId'];
		$this->qrCode=$cfg['qrCode'];
		return 'NotificationViberSetting';
	}

}