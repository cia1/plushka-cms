<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;

/**
 * Реализует интерфейс с СУБД SQLite3. Расширенная версия
 */
class SqliteEx extends \plushka\core\Sqlite {

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

			$this->query('INSERT INTO "TEMP_'.$table.'" SELECT '.$q.' FROM "'.$table.'"');
			$this->query('DROP TABLE "'.$table.'"');
			$this->query('ALTER TABLE "TEMP_'.$table.'" RENAME TO "'.$table.'"');
		} catch($e) {
			$this->_rollback();
			throw $e;
		}
		$this->query('COMMIT');
	}

	/**
	 * Изменяет поле таблицы
	 * @param string $table Имя таблицы
	 * @param string $fieldName Имя модифицируемого поля
	 * @param string $newFieldName Новое имя поля
	 */
	public function alterChange(string $table,string $fieldName,string $newFieldName,$expression=null) {
		$q=$this->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$table."'");
		if(!$expression) $q=preg_replace('/(["\'`]?'.$fieldName.'["\'`]?)/','"'.$newFieldName.'"',$q);
		else $q=preg_replace('/(["\'` ]*?'.$fieldName.'["\'`]?.*?)([,)])/','"'.$newFieldName.'" '.self::_type($expression).'$2',$q);
		$i=strpos($q,$table);
		$q=substr($q,0,$i).'TEMP_'.substr($q,$i);
		$this->query('BEGIN TRANSACTION');
		if(!$this->query($q)) return $this->_rollback();
		if(!$this->query('INSERT INTO "TEMP_'.$table.'" SELECT * FROM "'.$table.'"')) return $this->_rollback();
		$this->query('DROP TABLE "'.$table.'"');
		if(!$this->query('ALTER TABLE "TEMP_'.$table.'" RENAME TO "'.$table.'"')) return $this->_rollback();
		$this->query('COMMIT');
		return true;
	}

	//Возвращает SQL-азпрос "CREATE TABLE"
	public function getCreateTableQuery($table) {
		return $this->fetchValue("SELECT sql FROM sqlite_master WHERE type='table' AND tbl_name='".$table."'");
	}

	//Возвращает структуру таблицы, разобранную по полям, имя таблицы помещает в &$table
	public function parseStructure($sql,&$table=null) {
		//выбор имени таблицы
		if(!preg_match('|CREATE TABLE\s+[`"\']?([A-Z0-9_-]+)|is',$sql,$data)) return false;
		$table=$data[1];
		//разбор полей
		$i1=strpos($sql,'(');
		$i2=strrpos($sql,')');
		$sql=trim(substr($sql,$i1+1,$i2-$i1-1));
		$structure=array();
		$i1=0;
		$quote1=$quote2=false;
		for($i2=0,$cnt=strlen($sql);$i2<$cnt;$i2++) {

			if($sql[$i2]=='(' || $sql[$i2]==')') $quote1=!$quote1;
			elseif($sql[$i2]=='`' || $sql[$i2]=='"' || $sql[$i2]=="'") $quote2=!$quote2;
			if(($sql[$i2]==',' || $i2==$cnt-1) && !$quote1 && !$quote2) {
				if($i2==$cnt-1) $s=substr($sql,$i1); else $s=substr($sql,$i1,$i2-$i1); //пропустить запятую (кроме5 последнего поля)
				if(preg_match('|[`"\']?([a-zA-Z0-9_-]+)[`"\']?(.+)$|',$s,$data)) $structure[$data[1]]=trim($data[2]);
				else $structure[]=$s;
				$i1=$i2+1;
			}
		}
		return $structure;
	}

	/* Возвращает строку, описывающую тип $expression (должна быть задана в формате MySQL) */
	private static function _type($expression) {
		if(is_array($expression)) {
			if(isset($expression[2]) && $expression[2]) $ai=true; else $ai=false;
			if(isset($expression[1]) && $expression[1]) $key=strtoupper($expression[1]); else $key=false;
			if(isset($expression['default'])) $default=$expression['default']; else $default='';
			$expression=$expression[0];
		} else {
			if(preg_match('/DEFAULT\s+["\']?([^"\']+)/i',$expression,$data)) $default=$data[1]; else $default='';
		}
		$i1=strpos($expression,' ');
		$i2=strpos($expression,'(');
		if(!$i1 && !$i2) $type=strtoupper($expression);
		else {
			$i3=((!$i1 || $i2 && $i2<$i1) ? $i2 : $i1);
			$type=strtoupper(substr($expression,0,$i3));
		}
		if(isset($key)===true && $key) $text=' PRIMARY KEY';
		elseif(stripos($expression,'NOT NULL')!==false) {
			$text=' NOT NULL';
			if(stripos($expression,'DEFAULT')===false) { //для значений NOT NULL лучше добавить DEFAULT
				$text.=" DEFAULT ''";
			}
		} else $text='';
		if($default!='' && $type!='CHAT' && $type!='VARCHAR') $text.=" DEFAULT '".$default."'";
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
			//echo '<p>Warning! Type &laquo;'.$type.'&raquo; defined wrong ('.$expression.')</p>';
			return $type.$text;
		}
	}

	private function _rollback() {
		$this->query('ROLLBACK');
		plushka::error('SQLite: не удалось изменить структуру таблицы');
		return false;
	}

} ?>