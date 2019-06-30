<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;
use plushka\core\DBException;
use plushka\core\Sqlite;

/**
 * Реализует интерфейс с СУБД SQLite3. Расширенная версия
 */
class SqliteEx extends Sqlite {

	/**
	 * Создаёт таблицу
	 * @param string $table Имя таблицы
	 * @param string[] $structure Описание структуры, где ключ - имя поля, значение - строка валидного определения MySQL
	 */
	 public function create(string $table,array $structure): void {
		$q='';
		foreach($structure as $name=>$item) {
			if($q!=='') $q.=',';
			$q.='"'.$name.'" '.self::_type($item);
		}
		$q='CREATE TABLE "'.$table.'" ('.$q.')';
		$this->query($q);
	}

	/**
	 * Добавляет к таблице новое поле
	 * @param string $table Имя таблицы
	 * @param string $field Имя поля
	 * @param string|array $expression Валидное определение поля MySQL
	 */
	public function alterAdd(string $table,string $field,$expression): void {
        /** @noinspection SqlResolve */
		$q='ALTER TABLE "'.$table.'" ADD "'.$field.'" '.self::_type($expression);
		$this->query($q);
	}

	/**
	 * Удаляет поле из таблицы
	 * @param string $table Имя таблицы
	 * @param string $field Имя поля
	 */
	public function alterDrop(string $table,string $field): void {
		$q=$this->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$table."'");
		$q=preg_replace('/(["\'` ]+'.$field.'["\'`, ]+.*?[,)])/','',$q);
		$i=strpos($q,$table);
		$q=substr($q,0,$i).'TEMP_'.substr($q,$i);
		$length=strlen($q)-1;
		if($q[$length]===',') $q[$length]=')';
		$this->query('BEGIN TRANSACTION');
		try {
			$this->query($q);
			$i=strpos($q,'(')+1;
			$q=substr($q,$i,$length-$i);
			$field=explode(',',$q);
			$q='';
			foreach($field as $item) {
				if($q!=='') $q.=',';
				$item=trim($item);
				if($item[0]==="'") { //если кавычки были использованы ошибочно
					$item='"'.substr($item,1);
					$item[strpos($item,"'")]='"';
				}
				$q.=substr($item,0,strpos($item,' '));
			}
            /** @noinspection SqlResolve */
			$this->query('INSERT INTO "TEMP_'.$table.'" SELECT '.$q.' FROM "'.$table.'"');
            /** @noinspection SqlResolve */
			$this->query('DROP TABLE "'.$table.'"');
            /** @noinspection SqlResolve */
			$this->query('ALTER TABLE "TEMP_'.$table.'" RENAME TO "'.$table.'"');
		} catch(DBException $e) {
			$this->_rollback();
		}
		$this->query('COMMIT');
	}

	/**
	 * Изменяет поле таблицы
	 * @param string $table Имя таблицы
	 * @param string $fieldName Имя модифицируемого поля
	 * @param string $newFieldName Новое имя поля
     * @param string|array $expression Определение поля в формате массива или в формате MySQL
	 */
	public function alterChange(string $table,string $fieldName,string $newFieldName,$expression=null): void {
		$q=$this->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$table."'");
		if($expression===null) $q=preg_replace('/(["\'`]?'.$fieldName.'["\'`]?)/','"'.$newFieldName.'"',$q);
		else $q=preg_replace('/(["\'` ]*?'.$fieldName.'["\'`]?.*?)([,)])/','"'.$newFieldName.'" '.self::_type($expression).'$2',$q);
		$i=strpos($q,$table);
		$q=substr($q,0,$i).'TEMP_'.substr($q,$i);
		$this->query('BEGIN TRANSACTION');
		try {
			$this->query($q);
            /** @noinspection SqlResolve */
			$this->query('INSERT INTO "TEMP_'.$table.'" SELECT * FROM "'.$table.'"');
            /** @noinspection SqlResolve */
			$this->query('DROP TABLE "'.$table.'"');
            /** @noinspection SqlResolve */
			$this->query('ALTER TABLE "TEMP_'.$table.'" RENAME TO "'.$table.'"');
		} catch(DBException $e) {
			$this->_rollback();
		}
		$this->query('COMMIT');
	}

	/**
	 * Извлекает структуру таблицы и возвращает SQL-запрос "CREATE TABLE"
	 * @param string Имя таблицы
	 * @return string
	 */
	public function getCreateTableQuery(string $table): string {
		return $this->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$table."'");
	}

	/**
	 * Разбирает SQL-запрос "CREATE TABLE" и возвращает структуру таблицы
	 * @param string $sql SQL-заос
	 * @param string $table Перемення-ссылка, куда будет помещено мя таблицы
	 * @return array|null
	 */
	public function parseStructure(string $sql,string &$table=null): ?array {
		//выбор имени таблицы
		if(!preg_match('|CREATE TABLE\s+[`"\']?([A-Z0-9_-]+)|is',$sql,$data)) return null;
		$table=$data[1];
		//разбор полей
		$i1=strpos($sql,'(');
		$i2=strrpos($sql,')');
		$sql=trim(substr($sql,$i1+1,$i2-$i1-1));
		$structure=array();
		$i1=0;
		$quote1=$quote2=false;
		for($i2=0,$cnt=strlen($sql);$i2<$cnt;$i2++) {
			if($sql[$i2]==='(' || $sql[$i2]===')') $quote1=!$quote1;
			elseif($sql[$i2]==='`' || $sql[$i2]==='"' || $sql[$i2]==="'") $quote2=!$quote2;
			if(($sql[$i2]===',' || $i2===$cnt-1) && $quote1===false && $quote2===false) {
				if($i2===$cnt-1) $s=substr($sql,$i1); else $s=substr($sql,$i1,$i2-$i1); //пропустить запятую (кроме5 последнего поля)
				if(preg_match('|[`"\']?([a-zA-Z0-9_-]+)[`"\']?(.+)$|',$s,$data)) $structure[$data[1]]=trim($data[2]);
				else $structure[]=$s;
				$i1=$i2+1;
			}
		}
		return $structure;
	}

	private static function _type($expression): string {
		$key=null;
		if(is_array($expression)===true) {
			if(isset($expression[1])===true && $expression[1]) $key=strtoupper($expression[1]);
			if(isset($expression['default'])===true) $default=$expression['default']; else $default='';
			$expression=$expression[0];
		} else {
			if(strpos($expression,'PRIMARY KEY')!==false) $key='PRIMARY';
			if(preg_match('/DEFAULT\s+["\']?([^"\']+)/i',$expression,$data)) $default=$data[1]; else $default='';
		}
		$i1=strpos($expression,' ');
		$i2=strpos($expression,'(');
		if($i1===false && $i2===false) $type=strtoupper($expression);
		else {
			$i3=((!$i1 || $i2 && $i2<$i1) ? $i2 : $i1);
			$type=strtoupper(substr($expression,0,$i3));
		}
		if($key!==null) $text=' PRIMARY KEY';
		elseif(stripos($expression,'NOT NULL')!==false) {
			$text=' NOT NULL';
			if(stripos($expression,'DEFAULT')===false) { //для значений NOT NULL лучше добавить DEFAULT
				$text.=" DEFAULT ''";
			}
		} else $text='';
		if($default!=='' && $type!=='CHAT' && $type!=='VARCHAR') $text.=" DEFAULT '".$default."'";
		switch($type) {
		case 'INTEGER': case 'INT': case 'TINYINT': case 'MEDIUMINT': case 'BIGINT':
			return 'INTEGER'.$text;
		case 'FLOAT': case 'REAL':
			return 'REAL'.$text;
		case 'CHAR': case 'VARCHAR': case 'MEDIUMTEXT':
			return 'TEXT'.$text;
		case 'BLOB':
			return 'BLOB'.$text;
		default:
			return $type.$text;
		}
	}

	private function _rollback(): void {
		$this->query('ROLLBACK');
		throw new DBException('SQLite: не удалось изменить структуру таблицы');
	}

}
