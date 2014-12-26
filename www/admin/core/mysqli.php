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

	/* Возвращает строку, описывающую тип $expression */
	private static function _type($expression) {
		if(is_array($expression)) {
			if(isset($expression[2]) && $expression[2]) $ai=true; else $ai=false;
			if(isset($expression[1]) && $expression[1]) $key=strtoupper($expression[1]); else $key=false;
			if(isset($expression['default'])) $default=$expression['default']; else $default='';
			$expression=$expression[0].($default ? ' DEFAULT "'.$default.'"' : '');
		}
		if($key) $text=' PRIMARY KEY AUTO_INCREMENT'; else $text='';
		return $expression.$text;
	}

	private function _rollback() {
		$this->query('ROLLBACK');
		controller::$error='SQLite: не удалось изменить структуру таблицы';
		return false;
	}

}
?>