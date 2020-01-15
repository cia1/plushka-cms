<?php
namespace plushka\admin\controller;
use plushka\admin\core\Config;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\admin\model\NotificationTransport;

/**
 * Настройка рассылки уведомлений
 * @package notification
 *
 * `/admin/notification/setting` - настройка транспортов (каналов) отправки уведомлений
 */
class NotificationController extends Controller {

	public function right(): array {
		return [
			'setting'=>'notification.setting'
		];
	}

	public function actionSetting(): FormEx {
		$form=plushka::form();
		foreach(NotificationTransport::getList() as $item) {
			$transport=NotificationTransport::instance($item);
			$title=NotificationTransport::title($item);
			$form->html('<p><b>'.$title.'</b>');
			$form->checkbox('status]['.$transport->getId(),'Модуль включён',$transport->status);
			$transport->formAppend($form);
		}
		$form->submit();
		return $form;
	}

	public function actionSettingSubmit(array $data): void {
		$cfg=new Config('notification');
		foreach(NotificationTransport::getList() as $item) {
			$item=NotificationTransport::instance($item);
			$setting=$item->form2Setting($data);
			$setting['status']=isset($data['status'][$item->getId()]);
			if(plushka::error()) return;
			$cfg->{$item->getId()}=$setting;
		}
		if($cfg->save('notification')===false) return;
		plushka::redirect('notification/setting','Настройки сохранены');
	}

}