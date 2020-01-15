<?php
namespace plushka\admin\model;
use plushka\admin\core\FormEx;

/**
 * Транспорт отправки уведомлений по электронной почте
 * @package notification
 */
class NotificationTransportEmail extends NotificationTransport {

	public function formAppend(FormEx $form): void {
		$form->text('email][fromEmail','От кого (e-mail)',$this->fromEmail);
		$form->text('email][fromName','От кого (имя)',$this->fromName);
		$form->text('email][subject','Тема письма',$this->subject);
		$form->html('<cite>поле "от кого" используется только если текущий пользователь не авторизован. Впишите "cfg", если хотите чтобы информация была взята из общих настроек сайта.</cite>');
	}

	public function form2Setting(array $data): array {
		return [
			'subject'=>trim($data['email']['subject']),
			'fromEmail'=>trim($data['email']['fromEmail']),
			'fromName'=>trim($data['email']['fromName'])
		];
	}

}
