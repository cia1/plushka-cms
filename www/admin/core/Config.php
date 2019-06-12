<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;
use plushka;

/**
 * Служит для создания и изменения конфигурационных файлов
 */
class Config implements \IteratorAggregate {
	private $_data=[]; //тут содержатся все данные

	/**
	 * @param string|null $fileName Относительное имя файла конфигурации
	 */
	public function __construct(string $fileName=null) {
		if($fileName!==null) $this->load($fileName);
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator($this->_data);
	}

	/**
	 * Загружает конфигурацию
	 * @param string $fileName Относительное имя файла конфигурации
	 */
	public function load($fname=null): void {
		if($fileName===null && property_exists($this,'fileName')===true) $fileName=$this->fileName;
    $this->_data=plushka::config($fname);
    if(is_array($this->_data)===false) $this->_data=[];
	}

	/**
	 * Возвращает значение параметра
	 * @param string $attribute Имя параметра
	 * @return mixed
	 */
	public function get(string $attribute) {
		return $this->_data[$attribute] ?? null;
	}

	/**
	 * Устанавливает значение параметра
	 * @param string $attribute Имя параметра
	 * @param mixed $value Значение параметра
	 * @return mixed Возвращает имя параметра
	 */
	public function set(string $attribute,$value) {
		return $this->_data[$attribute]=$value;
	}

	public function __isset(string $attribute): bool {
		return isset($this->_data[$attribute]);
	}

	public function __get(string $attribute) {
		return $this->_data[$attribute] ?? null;
	}

	public function __set(string $attribute,$value) {
		return $this->_data[$attribute]=$value;
	}

	public function __unset(string $attribute): void {
		unset($this->_data[$attribute]);
	}

	/**
	 * Сохраняет конфигурацию в файл
	 * @param string $fileName|null
	 */
	public function save($fileName=null): void {
		if($fileName===null && property_exists($this,'fileName')===true) $fileName=$this->fileName;
		if(substr($fileName,0,6)==='admin/') $fileName=plushka::path().'admin/config/'.substr($fileName,6).'.php'; else $fileName=plushka::path().'config/'.$fileName.'.php';
		$f=fopen($fileName,'w');
		fwrite($f,'<?php return '.$this->_implode($this->_data).';');
		fclose($f);
	}

	/**
	 * Устанавливает сразу все данные
	 * @param array @data
	 */
	public function setData(&$data): void {
		$this->_data=&$data;
	}

	private static function _implode(array $data): string {
		$s='';
		$i=0;
		foreach($data as $name=>$value) {
			if($s) $s.=",\n";
			if($i===$name) $i++;
			elseif(is_integer($name)===true) $s.=$name.'=>';
			else $s.='\''.$name.'\'=>';
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