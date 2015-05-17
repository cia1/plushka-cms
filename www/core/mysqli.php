<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует интерфейс с СУБД MySQL */
class mysql {

	private static $_connectId; //идентификатор подключения (одно подключение для всех)
	private $_queryId; //идентификатор запроса (различно для разных экземпляров класса)

	/* Возвращает экранированную строку $value с добавленными кавычками */
	public static function escape($value) { return '"'.mysqli_escape_string(self::$_connectId,$value).'"'; }

	/* Возвращает экранированную строку $value */
	public static function getEscape($value) { return mysqli_escape_string(self::$_connectId,$value); }

	/* Подключается к СУБД */
	public function __construct() {
		$cfg=core::config();
		self::$_connectId=new mysqli($cfg['mysqlHost'],$cfg['mysqlUser'],$cfg['mysqlPassword'],$cfg['mysqlDatabase']);
		if(self::$_connectId->connect_errno) {
			controller::$error='Не могу подключиться к базе данных';
			return;
		}
		self::$_connectId->set_charset('utf8');
	}

	/* Выполняет произвольный SQL-запрос
	$limit - количество элементов на странице (для пагинации), $page - номер страницы (для пагинации) */
	public function query($query,$limit=null,$page=null) {
		if($limit!==null) {
			if($page) $page=(int)$page; else {
				if(isset($_GET['page'])) $page=((int)$_GET['page'])-1; else $page=0;
			}
			$query='SELECT SQL_CALC_FOUND_ROWS '.substr($query,7).' LIMIT '.($page*$limit).','.$limit;
		}
		$this->_queryId=self::$_connectId->query($query);
		if($this->_queryId) return true;
		$cfg=core::config();
		if($cfg['debug']) echo '<p>MYSQL QUERY ERROR: &laquo;'.$query.'&raquo;<p>';
		return false;
	}

	/* Возвращает количество найденный строк (в основном для пагинации) */
	public function foundRows() {
		return $this->fetchValue('SELECT FOUND_ROWS()');
	}

	/* Возвращает очередную запись из выборки в виде массива */
	public function fetch() {
		return $this->_queryId->fetch_row();
	}

	/* Возвращает очередную запись из выборки в виде ассоциативного массива */
	public function fetchAssoc() {
		return $this->_queryId->fetch_assoc();
	}

	/* Выполняет запрос $query и возвращает первую запись в виде массива */
	public function fetchArrayOnce($query) {
		$q=self::$_connectId->query($query.' LIMIT 0,1');
		if(!$q) return false;
		return @$q->fetch_row();
	}

	/* Выполняет запрос $query и возвращает первую запись в виде ассоциативного массива */
	public function fetchArrayOnceAssoc($query) {
		$q=self::$_connectId->query($query.' LIMIT 0,1');
		if(!$q) return false;
		return @$q->fetch_assoc();
	}

	/* Выполняет запрос $query и возвращает все записи в виде массива */
	public function fetchArray($query) {
		$q=self::$_connectId->query($query);
		if(!$q) return false;
		$data=array();
		while($item=$q->fetch_row()) $data[]=$item;
		return $data;
	}

	/* Выполняет запрос $query и возвращает все записи в виде ассоциативного массива */
	public function fetchArrayAssoc($query) {
		$q=self::$_connectId->query($query);
		$data=array();
		while($item=$q->fetch_assoc()) $data[]=$item;
		return $data;
	}

	/* Выполняет запрос $query и возвращает единственное значение (первое поле первой записи) */
	public function fetchValue($query) {
		$q=self::$_connectId->query($query.' LIMIT 0,1');
		if(!$q) return false;
		$result=@$q->fetch_row();
		return $result[0];
	}

	/* Возвращает значение первичного ключа добавленной ранее записи */
	public function insertId() {
		return self::$_connectId->insert_id;
	}

	/* Возвращает количество изменённых и добавленных записей */
	public function affected() {
		return $this->_queryId->affected_rows;
	}
}
?>