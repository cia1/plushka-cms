<?php class notificationTransportInner extends notificationTransport {

	public function formAppend($form) {
		$form->select('defaultUserId','Отправитель по умолчанию','SELECT id,login FROM user WHERE groupId>=200',$this->defaultUserId);
		$form->html('<cite>* используется для не авторизованных пользователей</cite>');
	}

	public function form2Setting($data) {
		$db=core::db();
		$login=$db->fetchValue('SELECT login FROM user WHERE id='.(int)$data['defaultUserId']);
		return array(
			'defaultUserId'=>(int)$data['defaultUserId'],
			'defaultUserLogin'=>$login
		);
	}

}