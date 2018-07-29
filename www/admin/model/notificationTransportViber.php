<?php class notificationTransportViber extends notificationTransport {

	public function formAppend($form) {
		$form->text('viber][groupId','ID группы Viber',$this->groupId);
		$form->text('viber][token','Token',$this->token);
	}

	public function form2Setting($data) {
		return array(
			'groupId'=>trim($data['viber']['groupId']),
			'token'=>trim($data['viber']['token'])
		);
	}

}