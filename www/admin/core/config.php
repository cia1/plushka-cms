<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Служит для создания и изменения конфигурационных файлов */
class config implements IteratorAggregate {
	private $_data=array(); //тут содержатся все данные

	public function __construct($fname=null) {
		if($fname) $this->load($fname);
	}

	public function getIterator() {
		return new ArrayIterator($this->_data);
	}

	/* Загружает в $this->_data конфигурацию из указанного файла */
	public function load($fname=null) {
		if($fname===null && property_exists($this,'fileName')) $fname=$this->fileName;
		if(substr($fname,0,6)=='admin/') $f=core::path().'admin/config/'.substr($fname,6).'.php'; else $f=core::path().'config/'.$fname.'.php';
		if(!file_exists($f)) {
			core::error('Конфигурации '.$fname.' не существует');
			return false;
		}
		$this->_data=include($f);
	}

	/* Возвращает значение параметра */
	public function get($attribute) {
		if(isset($this->_data[$attribute])) return $this->_data[$attribute]; else return null;
	}

	/* Устанавливает значение $value для параметра */
	public function set($attribute,$value) {
		$this->_data[$attribute]=$value;
		return $value;
	}

	public function __isset($attribute) {
		return isset($this->_data[$attribute]);

	}

	/* Возвращает значение параметра */
	public function __get($attribute) {
		if(isset($this->_data[$attribute])) return $this->_data[$attribute]; else return null;
	}

	/* Устанавливает значение $value для параметра */
	public function __set($attribute,$value) {
		$this->_data[$attribute]=$value;
	}

	/* Удаляет параметр $name */
	public function __unset($attribute) {
		unset($this->_data[$attribute]);
	}

	/* Сохраняет конфигурацию в php-файл */
	public function save($fname=null) {
		if($fname===null && property_exists($this,'fileName')) $fname=$this->fileName;
		if(substr($fname,0,6)=='admin/') $fname=core::path().'admin/config/'.substr($fname,6).'.php'; else $fname=core::path().'config/'.$fname.'.php';
		$f=fopen($fname,'w');
		if(!$f) {
			core::error('Ошибка записи конфигурации '.$fname);
			return false;
		}
		fwrite($f,'<?php return '.$this->_implode($this->_data).';');
		fclose($f);
		return true;
	}

	/* Устанавливает сразу все данные */
	public function setData(&$data) {
		$this->_data=&$data;
		return true;
	}

	/* Объединяет массив в корректный PHP-скрипт */
	private static function _implode($data) {
		$s='';
		$i=0;
		foreach($data as $name=>$value) {
			if($s) $s.=",\n";
			if($i===$name) $i++; elseif(is_integer($name)) $s.=$name.'=>'; else $s.='\''.$name.'\'=>';
			$type=gettype($value);
			switch($type) {
			case 'boolean':
				if($value) $s.='true'; else $s.='false';
				break;
			case 'integer': case 'double':
				$s.=$value;
				break;
			case 'string':
				$s.="'".str_replace("'","\'",$value)."'";
				break;
			case 'array':
				$s.=config::_implode($value);
				break;
			default:
				$s.='null';
			}
		}
		$s="array(\n".$s."\n)";
		return $s;
	}

}