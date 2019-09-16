<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;
use SQLite3;
use SQLite3Result;

/**
 * Олицетворяет подключение к базе данных SQLite
 * Для получения экземпляра класса использовать plushka::db() или plushka::sqlite()
 */
class Sqlite {

	/** @var Sqlite3 Идентификатор подключения (одно подключение для всех) */
	private static $_connectId;
	/** @var SQLite3Result Идентификатор запроса (различно для разных экземпляров класса) */
	private $_queryId;
	/** @var int Для временного хранения количества обработанных записей */
	private $_total;

	/**
	 * Возвращает экранированную строку, заключённую в кавычки
	 * @param string $value Экранируемая строка
	 * @return string
	 */
	public static function escape(string $value): string {
		return "'".SQLite3::escapeString($value)."'";
	}

	/**
	 * Возвращает экранированную строку, в отличии от self::escape() не заключает строку в кавычки
	 * @param string $value Экранируемая строка
	 * @return string
	 */
	public static function getEscape(string $value): string {
		return SQLite3::escapeString($value);
	}

	/**
	 * @param string|null $fileName Имя файла базы данных, если нужно подключиться не к стандартной
	 */
	public function __construct(string $fileName=null) {
		if($fileName===null) $fileName=core::path().'data/database3.db';
		self::$_connectId=new SQLite3($fileName);
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
	 */
	public function insert(string $table,$data): void {
		$field=array();
		if(isset($data[0])===false) $field=array_keys($data);
		else {
			foreach($data as $null=>$item) {
				foreach($item as $key=>$null) {
					if(in_array($key,$field)===false) $field[]=$key;
				}
			}
		}

		$sql='';
		foreach($field as $item) {
			if($sql!=='') $sql.=',';
			$sql.=$item;
		}
		if(isset($data[0])===false) $value=self::_sqlInsert($field,$data);
		else {
			$value='';
			foreach($data as $item) {
				if($value!=='') $value.=',';
				$value.=self::_sqlInsert($field,$item);
			}
		}
		$sql='INSERT INTO '.$table.' ('.$sql.') VALUES '.$value;
		$this->query($sql);
	}

	/**
	 * Выполняет произвольный SQL-запрос
	 * Если параметр $limit указан, то к SQL-запросу SELECT будет добавлена инструкция LIMIT
	 * Если параметр $limit указан, а $page - нет, то номер страницы будет определяться из $_GET['page']
	 * @param string $query SQL-запрос
	 * @param int|null $limit Количество строк для операции SELECT
	 * @param int|null $page Номер страницы пагирации
	 */
	public function query(string $query,int $limit=null,int $page=null): void {
		if($limit!==null) {
			if($page) $page=$page-1; else {
				if(isset($_GET['page'])===true) $page=((int)$_GET['page'])-1; else $page=0;
			}
			$this->_total=$this->fetchValue('SELECT COUNT(*)'.substr($query,stripos($query,' FROM ')));
			$query.=' LIMIT '.($page*$limit).','.$limit;
		}
		$this->_queryId=self::$_connectId->query($query);
		if(!$this->_queryId) throw new DBException("SQLITE QUERY ERROR: \n".$query);
	}

	/**
	 * Возвращает общее количество найденных предыдущим запросом SELECT строк
	 * Предварительно self::query() должен быть вызван с указанием параметра $limit
	 * @return integer
	 */
	public function foundRows(): int {
		return $this->_total;
	}

	/**
	 * Возвращает очередную запись из выборки индексированную целыми числами
	 * @see self::query()
	 * @return string[]|null
	 */
	public function fetch(): ?array {
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/**
	 * Возвращает очередную запись из выборки индексированную именами столбцов
	 * @see self::query()
	 * @return string[]|null
	 */
	public function fetchAssoc(): ?array {
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Выполняет SQL-запрос и возвращает первую запись в виде массива, индексированного целыми числами
	 * @param string $query SQL-запрос
	 * @return string[]|null
	 */
	public function fetchArrayOnce(string $query): ?array {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_NUM);
	}

	/**
	 * Выполняет SQL-запрос и возвращает первую запись в виде массива, индексированного именами столбцов
	 * @param string $query SQL-запрос
	 * @return string[]|null
	 */
	public function fetchArrayOnceAssoc(string $query): ?array {
		$query.=' LIMIT 0,1';
		$this->query($query);
		return $this->_queryId->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Выполняет SQL-запрос и возвращает все найденные записи в виде массива массивов, индексированных целыми числами
	 * @param string $query SQL-запрос
	 * @return array[]|null
	 */
	public function fetchArray(string $query): ?array {
		$this->query($query);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_NUM)) $data[]=$item;
		return $data;
	}

	/**
	 * Выполняет SQL-запрос и возвращает все найденные записи в виде массива массивов, индексированных именами столбцов
	 * @param string $query SQL-запрос
     * @param int|null $limit Ограничение количества извлекаемых записей
     * @param int|null $page Номер страницы пагинации
	 * @return array[]|null
	 */
	public function fetchArrayAssoc(string $query,int $limit=null,int $page=null): ?array {
		$this->query($query,$limit,$page);
		$data=array();
		while($item=$this->_queryId->fetchArray(SQLITE3_ASSOC)) $data[]=$item;
		return $data;
	}

	/**
	 * Выполняет SQL-запрос и возвращает скалярное значение (первое поле первой записи)
	 * @param string $query SQL-запрос
	 * @return string|null
	 */
	public function fetchValue(string $query): ?string {
		return self::$_connectId->querySingle($query);
	}

	/**
	 * Возвращает значение первичного ключа добавленной ранее записи
	 * @return integer|null
	 */
	public function insertId(): ?int {
		return self::$_connectId->lastInsertRowID();
	}

	/**
	 * Возвращает количество изменённых, добавленных или удалённых записей предыдущим SQL-запросом
	 * @return integer|null
	 */
	public function affected(): ?int {
		return self::$_connectId->changes();
	}



	//Возвращет часть SQL-запроса для оператора INSERT
	private static function _sqlInsert(array $fieldList,array $data): ?string {
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
