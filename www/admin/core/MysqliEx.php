<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;

use plushka\core\Mysqli;

/**
 * Реализует интерфейс с СУБД MySQL. Расширенная версия
 */
class MysqliEx extends Mysqli {

	/**
	 * Создаёт таблицу
	 * @param string $table Имя таблицы
	 * @param string[] $structure Описание структуры, где ключ - имя поля, значение - строка валидного определения MySQL
	 */
	public function create(string $table,array $structure): void {
		$q='';
		foreach($structure as $name=>$item) {
			if($q!=='') $q.=',';
			$q.='`'.$name.'` '.self::_type($item);
		}
		$q='CREATE TABLE `'.$table.'` ('.$q.')';
		$this->query($q);
	}

	/**
	 * Добавляет к таблице новое поле
	 * @param string $table Имя таблицы
	 * @param string $field Имя поля
	 * @param string|array $expression Валидное определение поля MySQL
	 */
	public function alterAdd(string $table,string $field,$expression): void {
		$this->query('ALTER TABLE `'.$table.'` ADD `'.$field.'` '.self::_type($expression));
	}

	/**
	 * Удаляет поле из таблицы
	 * @param string $table Имя таблицы
	 * @param string $field Имя поля
	 */
	public function alterDrop(string $table,string $field): void {
		$this->query('ALTER TABLE `'.$table.'` DROP COLUMN `'.$field.'`');
	}

	/**
	 * Изменяет поле таблицы
	 * @param string $table Имя таблицы
	 * @param string $fieldName Имя модифицируемого поля
	 * @param string $newFieldName Новое имя поля
	 * @param string|array $expression Определение поля в формате массива или в формате MySQL
	 */
	public function alterChange(string $table,string $fieldName,string $newFieldName,string $expression=null): void {
		$this->query('ALTER TABLE `'.$table.'` CHANGE COLUMN `'.$fieldName.'` `'.$newFieldName.'` '.self::_type($expression));
	}

	/**
	 * Извлекает структуру таблицы и возвращает SQL-запрос "CREATE TABLE"
	 * @param string Имя таблицы
	 * @return string
	 */
	public function getCreateTableQuery(string $table): string {
		$data=$this->fetchArray('SHOW CREATE TABLE '.$table);
		return $data[0][1];
	}

	/**
	 * Разбирает SQL-запрос "CREATE TABLE" и возвращает структуру таблицы
	 * @param string $sql SQL-заос
	 * @param string $table Перемення-ссылка, куда будет помещено мя таблицы
	 * @return array|null
	 */
	public function parseStructure(string $sql,string &$table=null): ?array {
		//выбор имени таблицы
		if(!preg_match('~CREATE TABLE\s+[`"\']?([A-Z0-9_-]+)~is',$sql,$data)) return null;
		$table=$data[1];
		//разбор полей
		$i1=strpos($sql,'(');
		$i2=strrpos($sql,')');
		$sql=trim(substr($sql,$i1+1,$i2-$i1-1));
		$structure=[];
		$i1=0;
		$quote1=$quote2=false;
		for($i2=0,$cnt=strlen($sql);$i2<$cnt;$i2++) {
			if($sql[$i2]==='(' || $sql[$i2]===')') $quote1=!$quote1;
			elseif($sql[$i2]==='`' || $sql[$i2]==='"' || $sql[$i2]==="'") $quote2=!$quote2;
			if(($sql[$i2]===',' || $i2===$cnt-1) && $quote1===false && $quote2===false) {
				if($i2===$cnt-1) $s=substr($sql,$i1); else $s=substr($sql,$i1,$i2-$i1); //пропустить запятую (кроме последнего поля)
				if(preg_match('~[`"\']?([a-zA-Z0-9_-]+)[`"\']?(.+)$~',$s,$data)) {
					if($data[1]!=='PRIMARY' && $data[1]!=='KEY') {
						$structure[$data[1]]=trim($data[2]);
					}
				} else $structure[]=$s;
				$i1=$i2+1;
			}
		}
		return $structure;
	}

	private static function _type($expression): string {
		if(is_array($expression)===false) return $expression;
		$ai=(bool)($expression[2] ?? false);
		if(isset($expression[1])===true && $expression[1]) $key=strtoupper($expression[1]); else $key=null;
		$default=$expression['default'] ?? '';
		$expression=$expression[0].($default ? ' DEFAULT "'.$default.'"' : '');
		if($key!==null) $text=' PRIMARY KEY AUTO_INCREMENT'; else $text='';
		return $expression.$text;
	}

}