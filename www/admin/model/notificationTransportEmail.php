<?php class notificationTransportEmail extends notificationTransport {

	public function formAppend($form) {
		$form->text('email][subject','Тема письма',$this->subject);
	}

	public function form2Setting($data) {
		return array(
			'subject'=>trim($data['email']['subject'])
		);
	}

}