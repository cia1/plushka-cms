<?php
class slog {

	private static $_log;

	public static function init($fileName,$clear=false) {
		self::$_log=new log($fileName,$clear);
	}

	public static function add($text) {
		if(!self::$_log) self::init('common');
		return self::$_log->add($text);
	}

	public static function dump($data,$title=null) {
		if(!self::$_log) self::init('common');
		return self::$_log->dump($data,$title);
	}

}

class log {

	private $_file;

	function __construct($fileName,$clear=false) {
		$f=core::path().'tmp/'.core::translit($fileName).'.txt';
		$this->_file=fopen($f,($clear ? 'w' : 'a'));
	}

	public function add($text) {
		fwrite($this->_file,time()."\t".$text."\n");
	}

	public function dump($data,$title=null) {
		if($title) fwrite($this->_file,$title.":\n");
		ob_start();
		var_dump($data);
		$data=ob_get_contents();
		ob_end_clean();
		fwrite($this->_file,$data);
	}

	function __destruct() {
		if($this->_file) fclose($this->_file);
	}

}