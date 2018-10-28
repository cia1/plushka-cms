<?php
//Модуль notification. Настройки системы отправки сообщений
class sController extends controller {

	public function right() {
		return array(
			'setting'=>'notification.setting'
		);
	}

	public function actionSetting() {
		core::import('admin/model/notificationTransport');
		$form=core::form();
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
		core::import('admin/model/notificationTransport');
		core::import('admin/core/config');
		$cfg=new config('notification');
		foreach(notificationTransport::getList() as $item) {
			$item=notificationTransport::instance($item);
			$setting=$item->form2Setting($data);
			$setting['status']=isset($data['status'][$item->getId()]);
			if(core::error()) return false;
			$cfg->{$item->getId()}=$setting;
		}
		if($cfg->save('notification')===false) return;
		core::redirect('notification/setting','Настройки сохранены');
	}

}