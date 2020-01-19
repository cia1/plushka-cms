<?php
namespace plushka\admin\model;
use plushka\admin\core\plushka;
use plushka\core\Cache;
use plushka\core\DBException;

/**
 * Управление мультиязычностью
 */
class Language {

	/**
	 * Добавляет новый язык
	 * @param string $alias Язык
	 * @return bool Удалось ли зарегистрировать новый язык
	 * @throws DBException
	 */
	public static function create(string $alias): bool {
		$table=self::_tableList();
		$db=plushka::db();
		$db->transaction();
		foreach($table as $item) self::_tableCreate($alias,$item);
		unset($table);
		$field=self::_fieldList();
		foreach($field as $table=>$item) {
			if(self::_fieldCreate($alias,$item[0],$item[1])===false) return false;
		}
		$result=plushka::hook('languageCreate',$alias);
		if($result===false) {
			$db->rollback();
			return false;
		}
		$db->commit();
		return true;
	}

	/**
	 * Обновляет таблицы базы данных, удаляя язык (мультиязычные таблицы и мультиязычные поля)
	 * @param string $alias Псевдоним языка
	 * @return bool Удалось ли удалить язык
	 */
	public static function delete(string $alias): bool {
		if(plushka::hook('languageDelete',$alias)===false) return false;
		$table=self::_tableList();
		$db=plushka::db();
		$db->transaction();
		foreach($table as $item) self::_tableDrop($alias,$item);
		unset($table);
		$field=self::_fieldList();
		foreach($field as $table=>$item) self::_fieldDrop($alias,$item[0],$item[1]);
		return true;
	}

	/**
	 * Возвращает список мультиязычных таблиц
	 * @return string[]
	 */
	private static function _tableList(): array {
		$f=plushka::path().'cache/language-database.php';
		if(!file_exists($f)) {
			Cache::languageDatabase();
		}
		$lang=plushka::config('../cache/language-database');
		foreach($lang as $table=>$null) {
			if($null!==true) unset($lang[$table]);
		}
		$lang=array_keys($lang);
		return $lang;
	}

	/**
	 * Возвращает список мультиязычных полей
	 * @return string[]
	 */
	private static function _fieldList(): array {
		$lang=plushka::config('../cache/language-database');
		$field=[];
		foreach($lang as $table=>$item) {
			if($item===true) continue;
			for($i=0,$cnt=count($item);$i<$cnt;$i++) $item[$i]=[$table,$item[$i]];
			$field=array_merge($field,$item);
		}
		return $field;
	}

	/**
	 * @param string $language
	 * @param string $table
	 * @throws DBException
	 */
	private static function _tableCreate(string $language,string $table): void {
		$db=plushka::db();
		$languageDefault=plushka::config('_core','languageDefault');
		$sql=$db->getCreateTableQuery($table.'_'.$languageDefault);
		$sql=str_replace($table.'_'.$languageDefault,$table.'_'.$language,$sql);
		$db->query($sql);
	}

	/**
	 * Добавляет поле к таблице с той же структурой, что и указанное поле
	 * @param string $language
	 * @param string $table
	 * @param string $field
	 * @return bool
	 * @throws DBException
	 */
	private static function _fieldCreate(string $language,string $table,string $field): bool {
		$db=plushka::db();
		$cfg=plushka::config();
		$data=$db->parseStructure($db->getCreateTableQuery($table));
		$fieldDefault=$field.'_'.$cfg['languageDefault'];
		if(isset($data[$fieldDefault])===false) {
			plushka::error('Не могу разобрать структуру таблицы '.$table.' для поля '.$fieldDefault);
			return false;
		}
		$db->alterAdd($table,$field.'_'.$language,$data[$fieldDefault]);
		return true;
	}

	/**
	 * @param string $language
	 * @param string $table
	 * @throws DBException
	 */
	private static function _tableDrop(string $language,string $table): void {
		$db=plushka::db();
		$db->query('DROP TABLE `'.$table.'_'.$language.'`');
	}

	/**
	 * @param string $language
	 * @param string $table
	 * @param string $field
	 * @throws DBException
	 */
	private static function _fieldDrop(string $language,string $table,string $field): void {
		$db=plushka::db();
		$db->alterDrop($table,$field.'_'.$language);
	}

}