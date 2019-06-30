<?php
namespace plushka\model;
use plushka;
use plushka\core\Email;

/**
 * Транспорт (способ отправки) через электронную почту
 * @property-read string $fromEmail От кого (e-mail)
 * @property-read string $fromName От кого (имя)
 * @property-read string $subject Тема письма
 */
class NotificationEmail extends Notification {

	public function title(): string {
		return 'E-mail';
	}

	public function available(): bool {
		return true;
	}

	public function send(string $message): bool {
		$email=new Email();
		if($this->userId==plushka::userId()) $recepient=plushka::user()->email;
		else {
            /** @noinspection SqlResolve */
            $recepient=plushka::db()->fetchValue('SELECT email FROM user WHERE id='.(int)$this->userId);
        }
		if(!$recepient) return false;
		if($this->fromEmail==='cfg') $fromEmail=plushka::config('_core','adminEmailEmail'); else $fromEmail=$this->fromEmail;
		if($this->fromName==='cfg') $fromName=plushka::config('_core','adminEmailName'); else $fromName=$this->fromName;
		$email->from($fromEmail,$fromName);
		$email->replyTo($fromEmail,$fromName);
		$email->subject($this->subject);
		$email->message($message);
		return $email->send($recepient);
	}

}
