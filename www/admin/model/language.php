<?php class language {

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
		return core::hook('languageCreate',$alias);
	}

	//Обновляет таблицы базы данных, удаляя язык $alias
	public static function delete($alias) {
		if(!core::hook('languageDelete',$alias)) return false;
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
		$path=core::path().'admin/module/';
		$table=array();
		$d=opendir($path);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(substr($f,strlen($f)-12)=='.install.php') continue;
			$data=include($path.$f);
			if(!isset($data['table'])) continue;
			$data=explode(',',$data['table']);
			foreach($data as $item) {
				if(!strpos($item,'_LANG')) continue;
				$item=explode('(',$item);
				$table[]=substr($item[0],0,strlen($item[0])-5);
			}
		}
		closedir($d);
		return $table;
	}

	//Возвращает список мультиязычных полей
	private static function _fieldList() {
		$path=core::path().'admin/module/';
		$field=array();
		$d=opendir($path);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(substr($f,strlen($f)-12)=='.install.php') continue;
			$data=include($path.$f);
			if(!isset($data['table'])) continue;
			$data=explode(',',$data['table']);
			foreach($data as $item) {
				$i1=strpos($item,'(');
				if(!$i1) continue;
				$i2=strpos($item,')');
				$table=substr($item,0,$i1);
				$item=explode(' ',substr($item,$i1+1,$i2-$i1-1));
				foreach($item as $fld) $field[]=array($table,$fld);
			}
		}
		closedir($d);
		return $field;
	}

	//Создаёт копию таблицы на основе существующей
	private static function _tableCreate($language,$table) {
		$db=core::db();
		$cfg=core::config();
		$sql=$db->getCreateTableQuery($table.'_'.$cfg['languageDefault']);
		$sql=str_replace($table.'_'.$cfg['languageDefault'],$table.'_'.$language,$sql);
		return $db->query($sql);
	}

	//Добавляет поле к таблице с той же структурой, что и указанное поле
	private static function _fieldCreate($language,$table,$field) {
		$db=core::db();
		$cfg=core::config();
		$data=$db->parseStructure($db->getCreateTableQuery($table));
		$fieldDefault=$field.'_'.$cfg['languageDefault'];
		if(!isset($data[$fieldDefault])) {
			controller::$error='Не могу разобрать структуру таблицы '.$table.' для поля '.$fieldDefault;
			return false;
		}
		$description=$data[$fieldDefault];
		return $db->alterAdd($table,$field.'_'.$language,$data[$fieldDefault]);
	}

	private static function _tableDrop($language,$table) {
		$db=core::db();
		return $db->query('DROP TABLE `'.$table.'_'.$language.'`');
	}


	private static function _fieldDrop($language,$table,$field) {
		$db=core::db();
		return $db->alterDrop($table,$field.'_'.$language);
	}

} ?>