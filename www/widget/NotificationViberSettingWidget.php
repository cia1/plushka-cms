<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/**
 * Настройка подключения канала уведомлений через Viber.
 */
class NotificationViberSettingWidget extends Widget {

	public $groupId;
	public $qrCode;

	public function __invoke(): string {
		$cfg=plushka::config('notification','viber');
		$this->groupId=$cfg['groupId'];
		$this->qrCode=$cfg['qrCode'];
		return 'NotificationViberSetting';
	}

}
