<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует интерфейс с СУБД MySQL */
class _sqlite {

	private static $_connectId; //идентификатор подключения (одно подключение для всех)
	private $_queryId; //идентификатор запроса (различно для разных экземпляров класса)

	/* Возвращает экранированную строку $value с добавленными кавычками */
	public static function escape($value) { return "'".SQLite3::escapeString($value)."'"; }

	/* Возвращает экранированную строку $value */
	public static function getEscape($value) { return SQLite3::escapeString($value); }

	/* Подключается к СУБД */
	public function __construct($fname=null) {
		if(!$fname) $fname=core::path().'data/database3.db';
		self::$_connectId=new SQLite3($fname);
		self::$_connectId->createFunction('CONCAT',function() {
			return implode('',func_get_args());
		},-1);
	}

	/* Выполняет произвольный SQL-запрос
	$limit - количество элементов на странице (для пагинации), $page - номер страницы (для пагинации) */
	public function query($query,$limit=null,$page=null) {
		if($limit!==null) {
			if($page) $page=(int)$page-1; else {
				if(isset($_GET['page'])) $page=((int)$_GET['page'])-1; else $page=0;
			}
			$this->_total=$this->fetchValue('SELECT COUNT(*)'.substr($query,stripos($query,' FROM ')));
			$query.=' LIMIT '.($page*$limit).','.$limit;
		}
		$this->_queryId=self::$_connectId->query($query);
		if($this->_queryId) return true;
		$cfg=core::config();
		if($cfg['debug']) echo '<p>SQLITE QUERY ERROR: '.$query.'</p>';
		return false;
	}

	/* Возвращает количество найденный строк (в основном для пагинации) */
	public function foundRows() {
		return $this->_total;
	}

	/* Возвращает очередную запись из выборки в виде массива */
	public function fetch() {
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/* Возвращает очередную запись из выборки в виде ассоциативного массива */
	public function fetchAssoc() {
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/* Возвращает очередную запись из выборки в виде ассоциативного массива */
	public function fetchArrayOnce($query) {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/* Выполняет запрос $query и возвращает первую запись в виде ассоциативного массива */
	public function fetchArrayOnceAssoc($query) {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/* Выполняет запрос $query и возвращает все записи в виде массива */
	public function fetchArray($query) {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_NUM)) $data[]=$item;
		return $data;
	}

	/* Выполняет запрос $query и возвращает все записи в виде ассоциативного массива */
	public function fetchArrayAssoc($query,$limit=null,$page=null) {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_ASSOC)) $data[]=$item;
		return $data;
	}

	/* Выполняет запрос $query и возвращает единственное значение (первое поле первой записи) */
	public function fetchValue($query) {
		return self::$_connectId->querySingle($query);
	}

	/* Возвращает значение первичного ключа добавленной ранее записи */
	public function insertId() {
		return self::$_connectId->lastInsertRowID();
	}

	/* Возвращает количество изменённых и добавленных записей */
	public function affected() {
		return self::$_connectId->changes();
	}

/*
	public static function like($mask,$value) {
		return preg_match('/'.str_replace('%','.?',preg_quote($mask,'/')).'/i',$value);
	}
*/
}
?>