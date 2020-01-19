<?php
namespace plushka\admin\model;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;

/**
 * Транспорт отправки сообщений по внутренней почте
 *
 * @property int $defaultUserId ID пользователя, отправившего уведомления
 *
 */
class NotificationTransportInner extends NotificationTransport {

	/** @inheritDoc */
	public function formAppend(FormEx $form): void {
		$form->select('defaultUserId','Отправитель по умолчанию','SELECT id,login FROM user WHERE groupId>=200',$this->defaultUserId);
		$form->html('<cite>* используется для не авторизованных пользователей</cite>');
	}

	/** @inheritDoc */
	public function form2Setting(array $data): array {
		$db=plushka::db();
		$login=$db->fetchValue('SELECT login FROM user WHERE id='.(int)$data['defaultUserId']);
		return [
			'defaultUserId'=>(int)$data['defaultUserId'],
			'defaultUserLogin'=>$login
		];
	}

}