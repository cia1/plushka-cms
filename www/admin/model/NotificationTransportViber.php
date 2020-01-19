<?php
namespace plushka\admin\model;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\model\Request;

/**
 * Транспорт отправки уведомлений через Viber
 *
 * @property string $groupId Группа Viber
 * @property string $token   Токен доступа Viber
 * @property string $qrCode  URL изображения QR-кода
 */
class NotificationTransportViber extends NotificationTransport {

	/** @inheritDoc */
	public function formAppend(FormEx $form): void {
		$form->text('viber][groupId','ID группы Viber',$this->groupId);
		$form->text('viber][token','Token',$this->token);
		$form->text('viber][qrCode','QR-код',$this->qrCode);
		$form->html('<cite>Как подключить Viber:<br>1) сайт должен быть доступен в сети интернет и работать по протоколу HTTPS; 2) авторизоваться тут: <a href="https://partners.viber.com" target="_blank">https://partners.viber.com</a>;<br>3) нажать кнопку "<a href="https://partners.viber.com/account/create-bot-account" target="_blank">создать бот-аккаунт</a>", указав любые данные;<br>4) на partners.viber.com в меню перейти по ссылке "Info";<br>5) на этой странице в поле "QR-код" вставьте URL изображения QR-кода ("копировать URL картинки" на сайте partners.viber.com), token и ID (uri) и сохранить настройки;<br>6) на мобильном устройстве зайти в созданную группу Viber и нажать кнопку "Показать".</cite>');
	}

	/** @inheritDoc */
	public function form2Setting(array $data): ?array {
		if(isset($data['status']['viber'])===false) return null;
		$url=plushka::url(false,true);
		if(substr($url,0,8)!=='https://') {
			plushka::error('Необходимо чтобы сайт работал по протоколу HTTPS');
			return null;
		}
		$request=new Request();
		$request->custom('X-Viber-Auth-Token',$data['viber']['token'],true);
		$request->post('https://chatapi.viber.com/pa/set_webhook',json_encode([
			'url'=>plushka::url(false,true).'index2.php?controller=viber&action=hook',
			'event_types'=>[
				//'subscribed',
				//'unsubscribed',
				'conversation_started'
			],
			'send_name'=>false,
			'send_photo'=>false
		]));
		$answer=json_decode($request->content(),true);
		if($answer['status']!==0) {
			plushka::error('Ошибка Viber: '.$answer['status_message']);
			return null;
		}
		return [
			'groupId'=>trim($data['viber']['groupId']),
			'token'=>trim($data['viber']['token']),
			'qrCode'=>trim($data['viber']['qrCode'])
		];
	}

}