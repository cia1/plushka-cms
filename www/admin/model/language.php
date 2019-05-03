<?php
namespace plushka\admin\core;

class language {

	//Обновляет таблицы базы данных, добавляя новый язык $alias
	public static function create($alias) {
//Тут, наверно, лучше использовать транзакции
		$table=self::_tableList();
		foreach($table as $item) {
			if(!self::_tableCreate($alias,$item)) return false;
		}
		unset($table);
		$field=self::_fieldList();
		foreach($field as $table=>$item) {
			if(!self::_fieldCreate($alias,$item[0],$item[1])) return false;
		}
		return plushka::hook('languageCreate',$alias);
	}

	//Обновляет таблицы базы данных, удаляя язык $alias
	public static function delete($alias) {
		if(plushka::hook('languageDelete',$alias===false)) return false;
//Тут, наверно, лучше использовать транзакции
		$table=self::_tableList();
		foreach($table as $item) {
			if(!self::_tableDrop($alias,$item)) return false;
		}
		unset($table);
		$field=self::_fieldList();
		foreach($field as $table=>$item) {
			if(!self::_fieldDrop($alias,$item[0],$item[1])) return false;
		}
		return true;
	}

	//Возвращает список мультиязычных таблиц
	private static function _tableList() {
		$f=plushka::path().'cache/language-database.php';
		if(!file_exists($f)) {
			plushka::import('core/cache');
			cache::languageDatabase();
		}
		$lang=plushka::config('../cache/language-database');
		foreach($lang as $table=>$null) {
			if($null!==true) unset($lang[$table]);
		}
		$lang=array_keys($lang);
		return $lang;
	}

	//Возвращает список мультиязычных полей
	private static function _fieldList() {
		$lang=plushka::config('../cache/language-database');
		$field=array();
		foreach($lang as $table=>$item) {
			if($item===true) continue;
			for($i=0,$cnt=count($item);$i<$cnt;$i++) $item[$i]=array($table,$item[$i]);
			$field=array_merge($field,$item);
		}
		return $field;
	}

	//Создаёт копию таблицы на основе существующей
	private static function _tableCreate($language,$table) {
		$db=plushka::db();
		$cfg=plushka::config();
		$sql=$db->getCreateTableQuery($table.'_'.$cfg['languageDefault']);
		$sql=str_replace($table.'_'.$cfg['languageDefault'],$table.'_'.$language,$sql);
		return $db->query($sql);
	}

	//Добавляет поле к таблице с той же структурой, что и указанное поле
	private static function _fieldCreate($language,$table,$field) {
		$db=plushka::db();
		$cfg=plushka::config();
		$data=$db->parseStructure($db->getCreateTableQuery($table));
		$fieldDefault=$field.'_'.$cfg['languageDefault'];
		if(!isset($data[$fieldDefault])) {
			plushka::error('Не могу разобрать структуру таблицы '.$table.' для поля '.$fieldDefault);
			return false;
		}
		$description=$data[$fieldDefault];
		return $db->alterAdd($table,$field.'_'.$language,$data[$fieldDefault]);
	}

	private static function _tableDrop($language,$table) {
		$db=plushka::db();
		return $db->query('DROP TABLE `'.$table.'_'.$language.'`');
	}


	private static function _fieldDrop($language,$table,$field) {
		$db=plushka::db();
		return $db->alterDrop($table,$field.'_'.$language);
	}

} ?>
