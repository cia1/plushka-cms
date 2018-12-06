<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.

/**
 * Олицетворяет подключение к базе данных MySQL
 * Для получения экземпляра класса использовать core::db() или core::mysql()
 */
class mysql {

	private static $_connectId; //идентификатор подключения (одно подключение для всех)
	private $_queryId; //идентификатор запроса (различно для разных экземпляров класса)

	/**
	 * Возвращает экранированную строку, заключённую в кавычки
	 * @param string $value Экранируемая строка
	 * @return string
	 */
	public static function escape($value) {
		return '"'.mysqli_escape_string(self::$_connectId,$value).'"';
	}

	/**
	 * Возвращает экранированную строку, в отличии от self::escape() не заключает строку в кавычки
	 * @param string $value Экранируемая строка
	 * @return string
	 */
	public static function getEscape($value) {
		return mysqli_escape_string(self::$_connectId,$value);
	}

	public function __construct() {
		$cfg=core::config();
		@self::$_connectId=new mysqli($cfg['mysqlHost'],$cfg['mysqlUser'],$cfg['mysqlPassword'],$cfg['mysqlDatabase']);
		if(self::$_connectId->connect_errno) {
			header('HTTP/1.1 500 Internal Server Error');
			die('Cannot connect to database.');
			return;
		}
		self::$_connectId->set_charset('utf8');
	}

	/**
	 * Выполняет SQL-запрос INSERT
	 * Атрибут $data должен быть в формате ключ-значение, где ключ - имя поля базы данных
	 * или массив массивов ключ-значение для массовой вставки нескольких строк
	 * @param string $table Имя таблицы
	 * @param array|array[] $data Данные для вставки
	 * @return bool
	 */
	public function insert($table,$data) {
		$field=array();
		if(isset($data[0])===false) {
			$field=array_keys($data);
		} else {
			foreach($data as $null=>$item) {
				foreach($item as $key=>$null) {
					if(in_array($key,$field)===false) $field[]=$key;
				}
			}
		}

		$sql='';
		foreach($field as $item) {
			if($sql) $sql.=',';
			$sql.=$item;
		}
		if(isset($data[0])===false) $value=self::_sqlInsert($field,$data);
		else {
			$value='';
			foreach($data as $item) {
				if($value) $value.=',';
				$value.=self::_sqlInsert($field,$item);
			}
		}
		$sql='INSERT INTO '.$table.' ('.$sql.') VALUES '.$value;
		return $this->query($sql);
	}

	/**
	 * Выполняет произвольный SQL-запрос
	 * Если параметр $limit указан, то к SQL-запросу SELECT будет добавлена инструкция LIMIT
	 * Если параметр $limit указан, а $page - нет, то номер страницы будет определяться из $_GET['page']
	 * @param string $query SQL-запрос
	 * @param int|null $limit Количество строк для операции SELECT
	 * @param int|null $page Номер страницы пагирации
	 * @return bool
	 */
	public function query($query,$limit=null,$page=null) {
		if($limit!==null) {
			if($page!==null) $page=(int)$page-1; else {
				if(isset($_GET['page'])===true) $page=((int)$_GET['page'])-1; else $page=0;
			}
			$query='SELECT SQL_CALC_FOUND_ROWS '.substr($query,7).' LIMIT '.($page*$limit).','.$limit;
		}
		$this->_queryId=self::$_connectId->query($query);
		if($this->_queryId) return true;
		$cfg=core::config();
		if($cfg['debug']) echo '<p>MYSQL QUERY ERROR: &laquo;'.$query.'&raquo;<p>';
		return false;
	}

	/**
	 * Возвращает общее количество найденных предыдущим запросом SELECT строк
	 * Предварительно self::query() должен быть вызван с указанием параметра $limit
	 * @return int
	 */
	public function foundRows() {
		return $this->fetchValue('SELECT FOUND_ROWS()');
	}

	/**
	 * Возвращает очередную запись из выборки индексированную целыми числами
	 * @see self::query()
	 * @return string[]|null
	 */
	public function fetch() {
		return $this->_queryId->fetch_row();
	}

	/**
	 * Возвращает очередную запись из выборки индексированную именами столбцов
	 * @see self::query()
	 * @return string[]|null
	 */
	public function fetchAssoc() {
		return $this->_queryId->fetch_assoc();
	}

	/**
	 * Выполняет SQL-запрос и возвращает первую запись в виде массива, индексированного целыми числами
	 * @param string $query SQL-запрос
	 * @return string[]|null
	 */
	public function fetchArrayOnce($query) {
		$q=self::$_connectId->query($query.' LIMIT 1');
		if(!$q) return false;
		return @$q->fetch_row();
	}

	/**
	 * Выполняет SQL-запрос и возвращает первую запись в виде массива, индексированного именами столбцов
	 * @param string $query SQL-запрос
	 * @return string[]|null
	 */
	public function fetchArrayOnceAssoc($query) {
		$q=self::$_connectId->query($query.' LIMIT 1');
		if(!$q) return false;
		return @$q->fetch_assoc();
	}

	/**
	 * Выполняет SQL-запрос и возвращает все найденные записи в виде массива массивов, индексированных целыми числами
	 * @param string $query SQL-запрос
	 * @return array[]|null
	 */
	public function fetchArray($query) {
		$q=self::$_connectId->query($query);
		if(!$q) return false;
		$data=array();
		while($item=$q->fetch_row()) $data[]=$item;
		return $data;
	}

	/**
	 * Выполняет SQL-запрос и возвращает все найденные записи в виде массива массивов, индексированных именами столбцов
	 * @param string $query SQL-запрос
	 * @return array[]|null
	 */
	public function fetchArrayAssoc($query,$limit=null) {
		if(!$this->query($query,$limit)) return false;
		$data=array();
		while($item=$this->_queryId->fetch_assoc()) $data[]=$item;
		return $data;
	}

	/**
	 * Выполняет SQL-запрос и возвращает скалярное значение (первое поле первой записи)
	 * @param string $query SQL-запрос
	 * @return string|null
	 */
	public function fetchValue($query) {
		$q=self::$_connectId->query($query.' LIMIT 0,1');
		if(!$q) return false;
		$result=@$q->fetch_row();
		return $result[0];
	}

	/**
	 * Возвращает значение первичного ключа добавленной ранее записи
	 * @return string|null
	 */
	public function insertId() {
		return self::$_connectId->insert_id;
	}

	/**
	 * Возвращает количество изменённых, добавленных или удалённых записей предыдущим SQL-запросом
	 * @return int|null
	 */
	public function affected() {
		return self::$_connectId->affected_rows;
	}



	//Возвращет часть SQL-запроса для оператора INSERT
	private static function _sqlInsert($fieldList,$data) {
		$sql='';
		foreach($fieldList as $item) {
			if($sql) $sql.=',';
			if(isset($data[$item])===false) $sql.='null';
			else {
				$value=$data[$item];
				if($value===true || $value===false) $sql.=(int)$value;
				elseif($value===null) $sql.='null';
				elseif(is_numeric($value)===true) $sql.=$value;
				else $sql.=self::escape($value);
			}
		}
		if(!$sql) return null;
		return '('.$sql.')';
	}

}