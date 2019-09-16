<?php
namespace plushka\model;
use InvalidArgumentException;
use plushka\core\plushka;

/**
 * "Уведомление".
 * Каждый способ доставки должен быть унаследован от этого класса.
 * "Транспорт" - способ передачи сообщения, наследник этого класса.
 * "Группа" (тип уведомления) - тип сообщения, индивидуальный для сайта (config/notification.php['group']).
 * @property-read bool $status Статус транспорта из config/notification.php
 */
abstract class Notification {

    /**
     * Возвращает список доступных для пользователя способов доставки уведомлений
     * @param int $userId ID пользователя
     * @param bool|null $available Фильтр доступности, null - любые
     * @return self[]
     */
	public static function transportList(int $userId,bool $available=null): array {
		$cfg=plushka::config('notification');
		$transport=[];
		foreach($cfg as $attribute=>$item) {
			if($attribute==='group') continue;
			if($item['status']===false) continue; //модуль отключён
			$class='\plushka\model\Notification'.ucfirst($attribute);
			/** @var self $class */
			$class=new $class($userId);
			if(($class instanceof self)===false) continue;
			if($available!==null && $class->available()!=$available) continue;
			$transport[]=$class;
		}
		return $transport;
	}

    /**
     * Возвращает плоский список имён всех включённых способов доставки уведомлений
     * @return string[]
     */
	public static function transportListFloat(): array {
		$cfg=plushka::config('notification');
		$transport=[];
		foreach($cfg as $attribute=>$item) {
			if($attribute==='group') continue;
			if($item['status']===false) continue; //модуль отключён
			$transport[]=$attribute;
		}
		return $transport;
	}

    /**
     * Возвращает список групп (типов) уведомлений
     * @param int|null $userId ID пользователя
     * @return array
     */
	public static function groupList(int $userId=null): array {
		$group=plushka::config('notification','group');
		if($userId===null) $setting=null;
		else $setting=self::userAttribute($userId);
		if($setting===null) $setting=[];
		foreach($group as $attribute=>$title) {
			if(isset($setting[$attribute])===true) $value=$setting[$attribute]; else $value=false;
			$group[$attribute]=['title'=>$title,'transport'=>$value];
		}
		return $group;
	}

    /**
     * Отправляет сообщение пользователю, по указанному в его настройках каналу (транспорту)
     * @param int $userId ID пользователя получателя
     * @param string $group Группа (тип) сообщения
     * @param string $message Текст сообщения
     * @return bool TRUE - сообщение отправлено, FALSE - сообщение не отправлено, NULL - сообщения отключены
     */
	public static function sendIfCan(int $userId,string $group,string $message): ?bool {
		$transport=self::userTransport($userId,$group);
		if($transport===null) return null;
		return $transport->send($message);
	}

    /**
     * Возвращает транспорт для указанной группы и пользователя.
     * @param int $userId ID пользователя
     * @param string $group Группа (тип) сообщения
     * @return self|null Возвращает NULL, если группа сообщений для пользователя отключена или недоступна
     */
    public static function userTransport(int $userId,string $group): ?self {
		if($userId===plushka::userId()) $notification=plushka::user()->model()->attribute('notification');
		else {
			plushka::import('model/user');
			$notification=new User();
			$notification->id=$userId;
			$notification=$notification->attribute('notification');
		}
		if(isset($notification[$group])===false) return null;
		return Notification::instance($notification[$group],$userId);
	}

    /**
     * Возвращает транспорт, инициированный указанным пользователем
     * @param string $id Имя транспорта
     * @param int $userId ID пользователя
     * @param bool $available Вернуть NULL, если доступность транспорта не соответствует указанной в этом параметре
     * @return Notification|null
     * @throws InvalidArgumentException
     */
	public static function instance(string $id,int $userId,bool $available=true): ?self {
		$transport='notification'.ucfirst(plushka::translit($id));
		if(file_exists(plushka::path().'model/'.$transport.'.php')===false) return null;
		/** @var self $transport */
		$transport=new $transport($userId);
		if(($transport instanceof self)===false) throw new InvalidArgumentException('Cannot initialize transport '.$id);
		if($transport->status===false) return null;
		if($available!==null) if($transport->available()!==$available) return null;
		return $transport;
	}




    /**
     * Должна возвращать название способа отправки уведомления с учётом мультиязычности
     * @return string
     */
	abstract public function title(): string;

    /**
     * Должна возвращать true, если метод доставки доступен (настроен) для пользователя
     * @return bool
     */
	abstract public function available(): bool;

    /**
     * Отправляет сообщение
     * @param string $message Текст сообщения
     * @return bool Удалось ли отправить
     */
	abstract public function send(string $message): bool;

	/** @var int ID пользователя получателя уведомления */
	public $userId;
	/** @var string Массив настроек из /config/notification.php */
	private $_setting;

    /**
     * @param int|null $userId ID пользователя
     */
	public function __construct($userId=null) {
		if($userId===null) $this->userId=plushka::userId(); else $this->userId=(int)$userId;
	}

    /**
     * Возвращает идентификатор транспорта
     * @return string
     */
	public function id(): string {
		$class=get_class($this);
		return strtolower($class[12]).substr($class,13);
	}

    /**
     * Возвращает атрибут транспорта из конфигурационного файла
     * @param string $attribute Имя атрибута
     * @return mixed|null
     */
	public function __get(string $attribute) {
		if($this->_setting===null) {
			$this->_setting=plushka::config('notification',$this->id());
		}
		return $this->_setting[$attribute] ?? null;
	}

    /**
     * Возвращает значение дополнительного атрибута для пользвателя
     * @param int $userId ID пользователя
     * @param string $attribute Имя атрибута
     * @return mixed|null
     */
	protected static function userAttribute(int $userId,string $attribute='notification') {
		if($userId==plushka::userId()) $user=plushka::user()->model();
		else {
			$user=new User();
			$user->id=$userId;
		}
		return $user->attribute($attribute);
	}

}
