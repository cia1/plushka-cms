<?php
namespace plushka\admin\core;

define('LOG_LIMIT_ON_PAGE',300); //Количество строк на одной странице
class log implements Iterator {

	//Возвращает список настроенных журналов (файлы /tmp/*.log, имеющие соответствующий конфигурационный файл)
	public static function getList() {
		$path=plushka::path().'tmp/';
		$d=opendir($path);
		$data=array();
		while($f=readdir($d)) {
			if($f=='.' || $f=='..' || substr($f,-4)!='.log') continue;
			$cfg=plushka::path().'admin/config/'.$f.'.php';
			if(!file_exists($cfg)) continue;
			$cfg=include($cfg);
			$data[]=array('file'=>$f,'title'=>$cfg['title']);
		}
		return $data;
	}

	private $_file;
	private $_field;
	private $_count;
	private $_current;
	private $_offset;
	private $_limit;
	private $_keyword;

	function __construct($file,$keyword=null,$page=-1,$limit=LOG_LIMIT_ON_PAGE) {
		//Анализ конфигурационного файла (/admin/config/$file.log.php), подготовка формата
		$file=plushka::translit($file); //фильтр имени файла
		$field=plushka::config('admin/'.$file);
		if(!$field) {
			plushka::error('Журнал '.$file.' не настроен');
			return false;
		}
		//Подготовка $field - список полей с описанием типа.
		$field=$field['field'];
		for($i=0,$cnt=count($field);$i<$cnt;$i++) {
			$y=strpos($field[$i][1],'(');
			if($y) {
				$type=substr($field[$i][1],0,$y);
				$param=substr($field[$i][1],$y+1,strlen($field[$i][1])-$y-2);
			} else {
				$type=$field[$i][1];
				$param=null;
			}
			switch($type) {
			case 'callback':
				$y=strrpos($param,'.');
				$f=substr($param,0,$y);
				plushka::import($f);
				$param=substr($param,$y+1);
			}
			$field[$i]=array($field[$i][0],$type,$param);
		}
		$this->_field=$field;
		//Перенести указатель чтения на нужную позицию в файле
		$this->_file=fopen(plushka::path().'tmp/'.$file,'r');
		$this->_count=self::_foundRows($this->_file,$keyword);
		if($page===null) $this->_offset=$this->_count-$limit;
		elseif($page==0) $this->_offset=0;
		else $this->_offset=($page-1)*$limit;
		$skip=0;
		if($this->_offset>0) {
			while($line=fgets($this->_file)) if(++$skip>=$this->_offset) break;
		}
		$this->_keyword=$keyword;
		$this->_limit=$this->_offset+$limit;
		$this->_offset--;
		$this->next();
	}

	function __destruct() {
		if($this->_file) fclose($this->_file);
	}

	//Возвращает список заголовков полей
	public function title() {
		$title=array();
		foreach($this->_field as $item) $title[]=$item[0];
		return $title;
	}

	public function rewind() {}

	public function valid() {
		if($this->_current!==false && $this->_offset<$this->_limit) return true; else return false;
	}

	public function next() {
		$found=false;
		do {
		$line=fgets($this->_file);
			if($line===false) {
				$this->_current=false;
				return;
			}
			if($this->_keyword && mb_stripos($line,$this->_keyword)===false) continue;
			$line=rtrim($line);
			$line=explode("\t",$line);
			foreach($this->_field as $i=>$fld) {
				$method='_format'.ucfirst($fld[1]);
				if($method=='_formatText' || !method_exists($this,$method)) continue;
				$line[$i]=self::$method((isset($line[$i]) ? $line[$i] : null),$fld[2],$line);
			}
			$found=true;
		} while(!$found);
		$this->_offset++;
		$this->_current=$line;
	}

	public function current() {
		return $this->_current;
	}

	public function key() {
		return $this->_offset;
	}

	public function count() {
		return $this->_count;
	}

	//Возвращает количество строк в файле (исключая пустые строки)
	private static function _foundRows($file,$keyword=null) {
		$count=0;
		while($line=fgets($file)) {
			if($keyword && mb_stripos($line,$keyword)===false) continue;
			if($line!=="\n") $count++; //пропустить пустые строки
		}
		rewind($file);
		return $count;
	}

	private static function _formatDate($value,$param) {
		return date($param,$value);
	}

	private static function _formatCallback($value,$param,$line) {
		return $param($value,$line);
	}

}