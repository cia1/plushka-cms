<?php
namespace plushka\admin\core;

abstract class NotificationTransport {

	public static function getList() {
		$path=plushka::path().'admin/model/';
		$d=opendir($path);
		$transport=array();
		while($f=readdir($d)) {
			if($f==='.' || $f==='..' || $f==='notificationTransport.php' || substr($f,-4)!=='.php' || substr($f,0,21)!=='notificationTransport') continue;
			$transport[]=substr($f,0,-4);
		}
		closedir($d);
		return $transport;
	}

	public static function instance($className) {
		$cfg=strtolower($className[21]).substr($className,22);
		$cfg=plushka::config('notification',$cfg);
		plushka::import('admin/model/'.$className);
		return new $className($cfg);
	}

	public static function title($className) {
		$className=str_replace('Transport','',$className);
		plushka::import('model/notification');
		plushka::import('model/'.$className);
		$public=new $className();
		return $public->title();
	}

	abstract public function formAppend($form); //Добавляет поля к HTML-форме
	abstract public function form2Setting($data); //Обрабатывает форму и возвращает конфигурацию
//	abstract public function getTitle(); //Возвращает название транспорта (строка или массив)

	private $_setting;

	public function __construct($setting) {
		$this->_setting=$setting;
	}

	public function __get($attribute) {
		return (isset($this->_setting[$attribute]) ? $this->_setting[$attribute] : null);
	}

	public function getId() {
		$id=get_class($this);
		return strtolower($id[21]).substr($id,22);
	}

}
