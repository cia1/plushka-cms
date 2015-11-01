<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Служит для создания и изменения конфигурационных файлов */
class config {
	private $_data=array(); //тут содержатся все данные

	public function __construct($fname=null) {
		if($fname) $this->load($fname);
	}

	/* Загружает в $this->_data конфигурацию из указанного файла */
	public function load($fname) {
		if(substr($fname,0,6)=='admin/') $f=core::path().'admin/config/'.substr($fname,6).'.php'; else $f=core::path().'config/'.$fname.'.php';
		if(!file_exists($f)) {
			controller::$error='Конфигурации '.$fname.' не существует';
			return false;
		}
		$this->_data=include($f);
	}

	/* Возвращает значение параметра $name */
	public function get($name) {
		if(isset($this->_data[$name])) return $this->_data[$name]; else return null;
	}

	/* Устанавливает значение $value для параметра $name */
	public function set($name,$value) {
		$this->_data[$name]=$value;
		return $value;
	}

	/* Удаляет параметр $name */
	public function delete($name) {
		unset($this->_data[$name]);
	}

	/* Возвращает значение параметра $name */
	public function __get($name) {
		if(isset($this->_data[$name])) return $this->_data[$name]; else return null;
	}

	/* Устанавливает значение $value для параметра $name */
	public function __set($name,$value) {
		$this->_data[$name]=$value;
	}

	/* Сохраняет конфигурацию в php-файл */
	public function save($fname) {
		if(substr($fname,0,6)=='admin/') $fname=core::path().'admin/config/'.substr($fname,6).'.php'; else $fname=core::path().'config/'.$fname.'.php';
		$f=fopen($fname,'w');
		if(!$f) {
			controller::$error='Ошибка записи конфигурации '.$fname;
			return false;
		}
		fwrite($f,'<?php return '.$this->_implode($this->_data).'; ?>');
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
?>