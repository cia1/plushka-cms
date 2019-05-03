<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;

/**
 * Олицетворяет подключение к базе данных SQLite
 * Для получения экземпляра класса использовать plushka::db() или plushka::sqlite()
 */
class Sqlite {

	private static $_connectId; //идентификатор подключения (одно подключение для всех)
	private $_queryId; //идентификатор запроса (различно для разных экземпляров класса)

	/**
	 * Возвращает экранированную строку, заключённую в кавычки
	 * @param string $value Экранируемая строка
	 * @return string
	 */
	public static function escape($value) {
		return "'".SQLite3::escapeString($value)."'";
	}

	/**
	 * Возвращает экранированную строку, в отличии от self::escape() не заключает строку в кавычки
	 * @param string $value Экранируемая строка
	 * @return string
	 */
	public static function getEscape($value) {
		return SQLite3::escapeString($value);
	}

	public function __construct($fname=null) {
		if(!$fname) $fname=plushka::path().'data/database3.db';
		self::$_connectId=new SQLite3($fname);
		self::$_connectId->createFunction('CONCAT',function() {
			return implode('',func_get_args());
		},-1);
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
			if($page) $page=(int)$page-1; else {
				if(isset($_GET['page'])) $page=((int)$_GET['page'])-1; else $page=0;
			}
			$this->_total=$this->fetchValue('SELECT COUNT(*)'.substr($query,stripos($query,' FROM ')));
			$query.=' LIMIT '.($page*$limit).','.$limit;
		}
		$this->_queryId=self::$_connectId->query($query);
		if($this->_queryId) return true;
		$cfg=plushka::config();
		if($cfg['debug']) echo '<p>SQLITE QUERY ERROR: '.$query.'</p>';
		return false;
	}

	/**
	 * Возвращает общее количество найденных предыдущим запросом SELECT строк
	 * Предварительно self::query() должен быть вызван с указанием параметра $limit
	 * @return int
	 */
	public function foundRows() {
		return $this->_total;
	}

	/**
	 * Возвращает очередную запись из выборки индексированную целыми числами
	 * @see self::query()
	 * @return string[]|null
	 */
	public function fetch() {
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/**
	 * Возвращает очередную запись из выборки индексированную именами столбцов
	 * @see self::query()
	 * @return string[]|null
	 */
	public function fetchAssoc() {
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Выполняет SQL-запрос и возвращает первую запись в виде массива, индексированного целыми числами
	 * @param string $query SQL-запрос
	 * @return string[]|null
	 */
	public function fetchArrayOnce($query) {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/**
	 * Выполняет SQL-запрос и возвращает первую запись в виде массива, индексированного именами столбцов
	 * @param string $query SQL-запрос
	 * @return string[]|null
	 */
	public function fetchArrayOnceAssoc($query) {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Выполняет SQL-запрос и возвращает все найденные записи в виде массива массивов, индексированных целыми числами
	 * @param string $query SQL-запрос
	 * @return array[]|null
	 */
	public function fetchArray($query) {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_NUM)) $data[]=$item;
		return $data;
	}

	/**
	 * Выполняет SQL-запрос и возвращает все найденные записи в виде массива массивов, индексированных именами столбцов
	 * @param string $query SQL-запрос
	 * @return array[]|null
	 */
	public function fetchArrayAssoc($query,$limit=null,$page=null) {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_ASSOC)) $data[]=$item;
		return $data;
	}

	/**
	 * Выполняет SQL-запрос и возвращает скалярное значение (первое поле первой записи)
	 * @param string $query SQL-запрос
	 * @return string|null
	 */
	public function fetchValue($query) {
		return self::$_connectId->querySingle($query);
	}

	/**
	 * Возвращает значение первичного ключа добавленной ранее записи
	 * @return string|null
	 */
	public function insertId() {
		return self::$_connectId->lastInsertRowID();
	}

	/**
	 * Возвращает количество изменённых, добавленных или удалённых записей предыдущим SQL-запросом
	 * @return int|null
	 */
	public function affected() {
		return self::$_connectId->changes();
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