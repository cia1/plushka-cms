<?php class notificationTransportEmail extends notificationTransport {

	public function formAppend($form) {
		$form->text('email][fromEmail','От кого (e-mail)',$this->fromEmail);
		$form->text('email][fromName','От кого (имя)',$this->fromName);
		$form->text('email][subject','Тема письма',$this->subject);
		$form->html('<cite>поле "от кого" используется только если текущий пользователь не авторизован. Впишите "cfg", если хотите чтобы информация была взята из общих настроек сайта.</cite>');
	}

	public function form2Setting($data) {
		return array(
			'subject'=>trim($data['email']['subject']),
			'fromEmail'=>trim($data['email']['fromEmail']),
			'fromName'=>trim($data['email']['fromName'])
		);
	}

}