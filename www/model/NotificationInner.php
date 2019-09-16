<?php
namespace plushka\model;
use plushka\core\plushka;

/**
 * Транспорт (способ отправки) через внутреннюю почту сайта
 * @property-read int $defaultUserId ID пользователя отправителя, если отправитель не авторизован
 * @property-read string $defaultUserLogin Имя пользователя отправителя, если отправитель не авторизован
 */
class NotificationInner extends Notification {

	public function title(): string {
		plushka::language('notification');
		return LNGMessageInner;
	}

	public function available(): bool {
		return true;
	}

	public function send(string $message): bool {
		$db=plushka::db();
		$user=plushka::user();
		if($this->userId==$user->id) $recepient=array($this->userId,$user->login);
		else {
            /** @noinspection SqlResolve */
            $login=$db->fetchValue('SELECT login FROM user WHERE id='.(int)$this->userId);
			if(!$login) return false;
			$recepient=[(int)$this->userId,$login];
		}
		if($user->group) $sender=[$user->id,$user->login];
		else $sender=[$this->defaultUserId,$this->defaultUserLogin];
		if(!$sender[0] || !$sender[1]) return false;
		$db->insert('user_message',array(
			'user1Id'=>$sender[0],
			'user1Login'=>$sender[1],
			'user2Id'=>$recepient[0],
			'user2Login'=>$recepient[1],
			'message'=>trim($message),
			'date'=>time()
		));
		return true;
	}

}
