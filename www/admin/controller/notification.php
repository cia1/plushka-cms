<?php
namespace plushka\admin\controller;

//Модуль notification. Настройки системы отправки сообщений
class sController extends controller {

	public function right() {
		return array(
			'setting'=>'notification.setting'
		);
	}

	public function actionSetting() {
		plushka::import('admin/model/notificationTransport');
		$form=plushka::form();
		foreach(notificationTransport::getList() as $item) {
			$transport=notificationTransport::instance($item);
			$title=notificationTransport::title($item);
			$form->html('<p><b>'.$title.'</b>','a');
			$form->checkbox('status]['.$transport->getId(),'Модуль включён',$transport->status);
			$transport->formAppend($form);
		}
		$form->submit();
		return $form;
	}

	public function actionSettingSubmit($data) {
		plushka::import('admin/model/notificationTransport');
		plushka::import('admin/core/config');
		$cfg=new config('notification');
		foreach(notificationTransport::getList() as $item) {
			$item=notificationTransport::instance($item);
			$setting=$item->form2Setting($data);
			$setting['status']=isset($data['status'][$item->getId()]);
			if(plushka::error()) return false;
			$cfg->{$item->getId()}=$setting;
		}
		if($cfg->save('notification')===false) return;
		plushka::redirect('notification/setting','Настройки сохранены');
	}

}