<?php abstract class notification {

	//Должна возвращать название метода отправки уведомления, с учётом мультиязычности
	abstract public function title();
	//Должна возвращать true, если метод доставки доступен (настроен) для пользователя
	abstract public function available();

	public $userId;

	//Возвращает массив notification
	public static function transportList($userId) {
		$cfg=core::config('notification');
		$transport=array();
		$userId=(int)$userId;
		foreach($cfg as $attribute=>$item) {
			if($attribute==='group') continue;
			if($item['status']===false) continue;
			$class='notification'.ucfirst($attribute);
			core::import('model/'.$class);
			$class=new $class(false);
			$class->userId=$userId;
			$transport[]=$class;
		}
		return $transport;
	}

	//Возвращает список групп (типов) уведомлений
	public static function groupList($userId) {
		$group=core::config('notification','group');
		$setting=self::userAttribute($userId);
		if($setting===null) $setting=array();
		foreach($group as $attribute=>$title) {
			if(isset($setting[$attribute])) $value=$setting[$attribute]; else $value=false;
			$group[$attribute]=array('title'=>$title,'transport'=>$value);
		}
		return $group;
	}

	public function __construct($userId=null) {
		if($userId===null) $this->userId=core::userId(); else $this->userId=(int)$userId;
	}

	public function id() {
		$class=get_class($this);
		return strtolower($class[12]).substr($class,13);
	}

	//Возвращает дополнительный атрибут $attribute для пользователя с ID $userId
	protected static function userAttribute($userId,$attribute='notification') {
		if($userId==core::userId()) $user=core::user()->model();
		else {
			core::import('model/user');
			$user=new modelUser($userId);
		}
		return $user->attribute($attribute);
	}

}