<?php
namespace plushka\admin\controller;
use plushka\admin\model\notificationTransport;

//Модуль notification. Настройки системы отправки сообщений
class NotificationController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'setting'=>'notification.setting'
		);
	}

	public function actionSetting() {
		$form=plushka::form();
		foreach(NotificationTransport::getList() as $item) {
			$transport=NotificationTransport::instance($item);
			$title=NotificationTransport::title($item);
			$form->html('<p><b>'.$title.'</b>','a');
			$form->checkbox('status]['.$transport->getId(),'Модуль включён',$transport->status);
			$transport->formAppend($form);
		}
		$form->submit();
		return $form;
	}

	public function actionSettingSubmit($data) {
		$cfg=new \plushka\admin\core\Config('notification');
		foreach(NotificationTransport::getList() as $item) {
			$item=NotificationTransport::instance($item);
			$setting=$item->form2Setting($data);
			$setting['status']=isset($data['status'][$item->getId()]);
			if(plushka::error()) return false;
			$cfg->{$item->getId()}=$setting;
		}
		if($cfg->save('notification')===false) return;
		plushka::redirect('notification/setting','Настройки сохранены');
	}

}