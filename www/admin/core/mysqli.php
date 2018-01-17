<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует интерфейс с СУБД MySQL. Расширенная версия */
core::import('core/mysqli');
class mysqlExt extends mysql {

	/* Создаёт таблицу в соответствии с описанной в $structure структурой */
	public function create($table,$structure) {
		$q='';
		foreach($structure as $name=>$item) {
			if($q) $q.=',';
			$q.='`'.$name.'` '.self::_type($item);
		}
		$q='CREATE TABLE `'.$table.'` ('.$q.')';
		return $this->query($q);
	}

	/* Добавляет к таблице новое поле */
	public function alterAdd($table,$field,$expression) {
		return $this->query('ALTER TABLE `'.$table.'` ADD `'.$field.'` '.self::_type($expression));
	}

	/* Удаляет поле из таблицы */
	public function alterDrop($table,$field) {
		return $this->query('ALTER TABLE `'.$table.'` DROP COLUMN `'.$field.'`');
	}

	/* Изменяет поле таблицы */
	public function alterChange($table,$oldField,$newField,$expression=null) {
		return $this->query('ALTER TABLE `'.$table.'` CHANGE COLUMN `'.$oldField.'` `'.$newField.'` '.self::_type($expression));
	}

	//Возвращает SQL-азпрос "CREATE TABLE"
	public function getCreateTableQuery($table) {
		$data=$this->fetchArray('SHOW CREATE TABLE '.$table);
		return $data[0][1];
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
				if(preg_match('|[`"\']?([a-zA-Z0-9_-]+)[`"\']?(.+)$|',$s,$data)) {
					if($data[1]!='PRIMARY' && $data[1]!='KEY') {
						$structure[$data[1]]=trim($data[2]);
					}
				} else $structure[]=$s;
				$i1=$i2+1;
			}
		}
		return $structure;
	}

	/* Возвращает строку, описывающую тип $expression */
	private static function _type($expression) {
		if(!is_array($expression)) return $expression;
		if(isset($expression[2]) && $expression[2]) $ai=true; else $ai=false;
		if(isset($expression[1]) && $expression[1]) $key=strtoupper($expression[1]); else $key=false;
		if(isset($expression['default'])) $default=$expression['default']; else $default='';
		$expression=$expression[0].($default ? ' DEFAULT "'.$default.'"' : '');
		if($key) $text=' PRIMARY KEY AUTO_INCREMENT'; else $text='';
		return $expression.$text;
	}

	private function _rollback() {
		$this->query('ROLLBACK');
		core::error('MySQLi: не удалось изменить структуру таблицы');
		return false;
	}

}
?>