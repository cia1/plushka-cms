<?php
namespace plushka\model;
use plushka;
use plushka\model\User;

abstract class Notification {

	//Возвращает массив notification для пользователя $userId
	public static function transportList($userId,$available=null) {
		$cfg=plushka::config('notification');
		$transport=array();
		$userId=(int)$userId;
		foreach($cfg as $attribute=>$item) {
			if($attribute==='group') continue;
			if($item['status']===false) continue;
			$class='Notification'.ucfirst($attribute);
			$class=new $class($userId);
			if($available!==null && $class->available()!=$available) continue;
			$transport[]=$class;
		}
		return $transport;
	}

	//Возвращает плоский список включённых транспортов
	public static function transportListFloat() {
		$cfg=plushka::config('notification');
		$transport=array();
		foreach($cfg as $attribute=>$item) {
			if($attribute==='group') continue;
			if($item['status']===false) continue;
			$transport[]=$attribute;
		}
		return $transport;
	}

	//Возвращает список групп (типов) уведомлений
	public static function groupList($userId=null) {
		$group=plushka::config('notification','group');
		if($userId===null) $setting=null;
		else $setting=self::userAttribute($userId);
		if($setting===null) $setting=array();
		foreach($group as $attribute=>$title) {
			if(isset($setting[$attribute])) $value=$setting[$attribute]; else $value=false;
			$group[$attribute]=array('title'=>$title,'transport'=>$value);
		}
		return $group;
	}

	public static function sendIfCan($userId,$group,$message) {
		$transport=self::userTransport($userId,$group);
		if($transport===null) return null;
		return $transport->send($message);
	}

	//Возвращает экземпляр класса транспорта для указанной группы или null, если для этой группы уведомления отключены
	public static function userTransport($userId,$group) {
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

	//Возвращает класс транспорта, создавая его по ИД
	public static function instance($id,$userId,$available=true) {
		$transport='notification'.ucfirst(plushka::translit($id));
		if(file_exists(plushka::path().'model/'.$transport.'.php')===false) return null;
		$transport=new $transport($userId);
		if($transport->status===false) return null;
		if($available!==null) if($transport->available()!==$available) return null;
		return $transport;
	}




	//Должна возвращать название метода отправки уведомления, с учётом мультиязычности
	abstract public function title();
	//Должна возвращать true, если метод доставки доступен (настроен) для пользователя
	abstract public function available();

	abstract public function send($message);

	public $userId; //получатель уведомления
	private $_setting;

	public function __construct($userId=null) {
		if($userId===null) $this->userId=plushka::userId(); else $this->userId=(int)$userId;
	}

	//Возвращает идентификатор транспорта
	public function id() {
		$class=get_class($this);
		return strtolower($class[12]).substr($class,13);
	}

	//Возвращает атрибут транспорта из конфигурационного файла
	public function __get($attribute) {
		if($this->_setting===null) {
			$this->_setting=plushka::config('notification',$this->id());
		}
		return (isset($this->_setting[$attribute]) ? $this->_setting[$attribute] : null);
	}

	//Возвращает дополнительный атрибут $attribute для пользователя с ID $userId
	protected static function userAttribute($userId,$attribute='notification') {
		if($userId==plushka::userId()) $user=plushka::user()->model();
		else {
			$user=new User();
			$user->id=$userId;
		}
		return $user->attribute($attribute);
	}

}