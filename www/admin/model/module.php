<?php
/* Библиотека для установки и удаления модулей */
class module {

	private static $_config;

	/* Преобразовывает в удобный для работы вид массив $data, содержащий информацию о модуле (/admin/module/XXX.php) */
	public static function explodeData(&$data) {
		if(isset($data['right']) && $data['right']) { //права доступа
			$data['right']=explode(',',$data['right']);
			for($i=0,$cnt=count($data['right']);$i<$cnt;$i++) $data['right'][$i]=trim($data['right'][$i]);
		} else $data['right']=array();
		if(isset($data['widget']) && $data['widget']) { //типы виджетов
			$data['widget']=explode(',',$data['widget']);
			for($i=0,$cnt=count($data['widget']);$i<$cnt;$i++) $data['widget'][$i]=trim($data['widget'][$i]);
		} else $data['widget']=array();
		if(isset($data['menu']) && $data['menu']) { //типы меню
			$data['menu']=explode(',',$data['menu']);
			for($i=0,$cnt=count($data['menu']);$i<$cnt;$i++) $data['menu'][$i]=trim($data['menu'][$i]);
		} else $data['menu']=array();
		if(isset($data['hook1']) && $data['hook1']) { //события (порождённые модулем) общедоступной части сайта
			$data['hook1']=explode(',',$data['hook1']);
		} else $data['hook1']=array();
		if(isset($data['hook2']) && $data['hook2']) { //события (порождённые модулем) админки
			$data['hook2']=explode(',',$data['hook2']);
		} else $data['hook2']=array();
		if(isset($data['table']) && $data['table']) { //список таблиц БД
			$data['table']=explode(',',$data['table']);
			for($i=0,$cnt=count($data['table']);$i<$cnt;$i++) $data['table'][$i]=trim($data['table'][$i]);
		} else $data['table']=array();
		if(!isset($data['currentVersion'])) $data['currentVersion']=null;
	}

	/* Возвращает список установленных модулей */
	public static function getList() {
		return core::configAdmin('_module');
	}

	/* Возвращает массив с информацией о модуле, находящемся в директории /tmp */
	public static function info() {
		$f=core::path().'tmp/module.ini';
		if(!file_exists($f)) return false;
		$f=file($f);
		$cnt=count($f);
		$data=array('right'=>array(),'widget'=>array(),'menu'=>array());
		//Перебор строк файла: каждая строка - параметр модуля
		for($i=0;$i<$cnt;$i++) {
			$y=strpos($f[$i],':');
			if(!$y) continue;
			$name=strtolower(substr($f[$i],0,$y));
			$value=explode("\t",substr($f[$i],$y+1));
			$data0=array();
			foreach($value as $item) {
				$item=trim($item);
				if(!$item) continue;
				$data0[]=$item;
			}
			if(!count($data0)) $data0=null; elseif(count($data0)==1) $data0=$data0[0];
			if($name=='right' || $name=='widget' || $name=='menu' ) $data[$name][]=$data0; else $data[$name]=$data0;
		}
		if(!isset($data['version'])) $data['verstion']='';
		if(!isset($data['url'])) $data['url']='';
		if(!isset($data['author'])) $data['author']=null;
		if(!isset($data['description'])) $data['description']=null;
		if(!isset($data['depend'])) $data['depend']=null;
		$cfg=core::configAdmin('_module');
		if(isset($cfg[$data['id']])) {
			$data['status']=$cfg[$data['id']]['status'];
			if(isset($cfg[$data['id']]['verstion'])) $data['currentVersion']=$cfg[$data['id']]['version'];
			else $data['currentVersion']=null;
		} else {
			$data['status']=0;
			$data['currentVersion']=null;
		}
		return $data;
	}

	/* Создаёт конфигурацию нового модуля
	string $id - системное имя модуля; string $name - название модуля; string $version - версия модуля; string $url - веб-старица модуля */
	public static function create($id,$name,$version,$url='',$currentVersion=null) {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->right='';
		$cfg->widget='';
		$cfg->menu='';
		$cfg->hook1='';
		$cfg->hook2='';
		$cfg->table='';
		$cfg->file=array();
		$cfg->currentVersion=$currentVersion;
		$cfg->save('../admin/module/'.$id);
		$cfg=new config('admin/_module');
		$cfg->$id=array('name'=>$name,'version'=>$version,'status'=>1,'url'=>$url);
		$cfg->save('admin/_module');
		self::$_config=$cfg;
		return true;
	}

	/* Устанавливает статус $status для модуля с именем $id */
	public static function status($id,$status) {
		if(!self::$_config) {
			core::import('admin/core/config');
			self::$_config=new config('admin/_module');
		}
		$s=self::$_config->get($id);
		$s['status']=$status;
		self::$_config->set($id,$s);
		self::$_config->save('../admin/config/_module');
		return true;
	}

	/* Устанавливает зависимости от других модулей $depend для модуля с именем $id */
	public static function depend($id,$depend) {
		if(!$depend) return true;
		core::import('admin/core/config');
		$cfg=new config('../admin/module/'.$id);
		$cfg->depend=$depend;
		$cfg->save('../admin/module/'.$id);
		return true;
	}

	/* Создаёт набор прав доступа $right для модуля с именем $id и обновляет конфигурацию модуля */
	public static function right($id,$right) {
		core::import('admin/core/config');
		$db=core::db();
		//Построить SQL-запросы
		$s1=$s2='';
		foreach($right as $i=>$item) {
			if(!isset($item[2])) $item[2]=null;
			if(!isset($item[3])) $item[3]=null;
			if($s1) {
				$s1.=','.$db->escape($item[0]);
				$s2.=',('.$db->escape($item[0]).','.$db->escape($item[1]).','.($item[2] ? $db->escape($item[2]) : 'null').','.($item[3] ? $db->escape($item[3]) : 'null').')';
			} else {
				$s1=$db->escape($item[0]);
				if(!isset($item[3])) $item[3]='';
				$s2='('.$db->escape($item[0]).','.$db->escape($item[1]).','.($item[2] ? $db->escape($item[2]) : 'null').','.($item[3] ? $db->escape($item[3]) : 'null').')';
			}
			$right[$i]=$item[0];
		}
		//Удалить и добавить вновь права
		$s1='DELETE FROM userRight WHERE module IN ('.$s1.')';
		$db->query($s1);
		if($right) {
			$s2='INSERT INTO userRight (module,description,groupId,picture) VALUES '.$s2;
			$db->query($s2);
		}
		$cfg=new config('../admin/module/'.$id);
		$cfg->right=implode(',',$right);
		$cfg->save('../admin/module/'.$id);
		return true;
	}

	/* Создаёт типы виджетов в базе данных и обновляет конфигурацию модуля */
	public static function widget($id,$widget) {
	core::import('admin/core/config');
		$db=core::db();
		//Построить SQL-запросы для удаления типов виджетов (если есть) и создания их вновь
		$s1=$s2='';
		foreach($widget as $i=>$item) {
			if($s1) {
				$s1.=','.$db->escape($item[0]);
				$s2.=',('.$db->escape($item[0]).','.$db->escape($item[1]).','.$db->escape($item[2]).','.$db->escape($item[3]).')';
			} else {
				$s1=$db->escape($item[0]);
				$s2='('.$db->escape($item[0]).','.$db->escape($item[1]).','.$db->escape($item[2]).','.$db->escape($item[3]).')';
			}
			$widget[$i]=$item[0];
		}
		$s1='DELETE FROM widgetType WHERE name IN ('.$s1.')';
		$db->query($s1);
		if($widget) {
			$s2='INSERT INTO widgetType (name,title,controller,`action`) VALUES '.$s2;
			$db->query($s2);
		}
		//Обновить конфигурацию модуля
		$cfg=new config('../admin/module/'.$id);
		$cfg->widget=implode(',',$widget);
		$cfg->save('../admin/module/'.$id);
		return true;
	}


	/* Создаёт типы меню, а также обновляет конфигурацию модуля с именем $id */
	public static function menu($id,$menu) {
		core::import('admin/core/config');
		$db=core::db();
		//Составить и выполнить SQL-запросы для удаления создания вновь типов меню
		$s='';
		foreach($menu as $item) {
			if($s) $s.=' OR (controller='.$db->escape($item[1]).' AND action='.$db->escape($item[2]).')';
			else $s='(controller='.$db->escape($item[1]).' AND action='.$db->escape($item[2]).')';
		}
		if($s) {
			$s='DELETE FROM menuType WHERE '.$s;
			$db->query($s);
		}
		$s=array();
		foreach($menu as $item) {
			$db->query('INSERT INTO menuType SET title='.$db->escape($item[0]).',controller='.$db->escape($item[1]).',action='.$db->escape($item[2]));
			$s[]=$db->insertId();
		}
		//Записать информацию о меню в конфигурацию модуля
		$cfg=new config('../admin/module/'.$id);
		$cfg->menu=implode(',',$s);
		$cfg->save('../admin/module/'.$id);
		return true;
	}

	/* Добавляет в конфигугацию модуля информацию об обработчиках событий.
	Информацию берёт из найденных в директориях /hook и /admin/hook файлов */
	public static function hook(&$module) {
		core::import('admin/core/config');
		//Общедоступная часть сайта
		$d=core::path().'tmp/hook/';
		$hook1=$hook2='';
		if(is_dir($d)) {
			$d=opendir($d);
			$cfg=null;
			while($f=readdir($d)) {
				if($f=='.' || $f=='..') continue;
				$f=explode('.',$f);
				if($f[2]!='php') continue;
				//Обновить информацию о собитиях в файле _hook.php
				if(!$cfg) $cfg=new config('_hook');
				$name=$f[0]; //имя события
				$h=$cfg->get($name); //Получить массив
				if(!$h) $h=array();
				if(!in_array($f[1],$h)) { //если по каким-то причинам обработчик уже назначен
					$h[]=$f[1];
					$cfg->set($name,$h); //Установить массив
				}
				if($hook1) $hook1.=','.$name; else $hook1=$name;
			}
			if($cfg) {
				$cfg->save('_hook');
				$module['hook1']=$hook1;
			}
			closedir($d);
		}

		//Админка
		$d=core::path().'tmp/admin/hook/';
		if(is_dir($d)) {
			$d=opendir($d);
			$cfg=null;
			while($f=readdir($d)) {
				if($f=='.' || $f=='..') continue;
				$f=explode('.',$f);
				if($f[2]!='php') continue;
				//Обновить информацию о собитиях в файле _hook.php
				if(!$cfg) $cfg=new config('admin/_hook');
				$name=$f[0]; //имя события
				$h=$cfg->get($name); //Получить массив
				if(!$h) $h=array();
				if(in_array($f[1],$h)) { //если по каким-то причинам обработчик уже назначен
					$h[]=$f[1];
					$cfg->set($name,$h); //Установить массив
				}
				if($hook2) $hook2.=','.$name; else $hook2=$name;
			}
			if($cfg) {
				$cfg->save('admin/_hook');
				$module['hook2']=$hook2;
			}
			closedir($d);
		}
		if($hook1 || $hook2) {
			$cfg=new config('../admin/module/'.$module['id']);
			$cfg->hook1=$hook1;
			$cfg->hook2=$hook2;
			$cfg->save('../admin/module/'.$module['id']);
		}
		return true;
	}

	/* Выполняет SQL-запросы установки модуля (/tmp/instal.СУБД.sql) и сохраняет информацию о созданных таблицах */
	public static function sql($id) {
		$cfg0=core::configAdmin();
		$f=core::path().'tmp/install.'.$cfg0['dbDriver'].'.sql'; //это файл для установки модуля
		if(!file_exists($f)) return true;
		core::import('admin/core/config');
		$sql=file_get_contents($f);
		//Извлечь имена создаваемых таблиц и записать в файл
		preg_match_all('/CREATE TABLE(?:(?:\s+if not exists\s+)|(?:\s+))`?([a-zA-Z0-9_]+)`?\s/is',$sql,$table);
		$table=$table[1];
		$cfg=new config('../admin/module/'.$id);
		$cfg->table=implode(',',$table);
		$cfg->save('../admin/module/'.$id);

		//Теперь поочерёдно выполнить все SQL-запросы
		$db=core::db();
		$cnt=strlen($sql);
		$quote=$escape=false;
		$i0=0;
		$lang=$cfg0['languageList'];
		for($i=0;$i<$cnt;$i++) {
			if($escape) {
				$escape=false;
				continue;
			}
			if($sql[$i]=='\\') {
				$escape=true;
				continue;
			}
			if($sql[$i]=='"' || $sql[$i]=='\'') {
				if(!$quote) $quote=$sql[$i]; else $quote=null;
			} elseif($sql[$i]==';' && !$quote) { //конец очередного sql-запроса
				if(!self::_executeLanguage(trim(substr($sql,$i0,($i-$i0))))) {
					controller::$error='Ошибка выполнения SQL-запросов';
					return false;
				}
				$i0=$i+1;
			}
		}
		return true;
	}

	//Выполняет SQL-запрос с учётом мультиязынчх преобразований
	private static function _executeLanguage($sql) {
		$cfg=core::config();
		$lang=$cfg['languageList'];
		//Если это создание мультиязычной таблицы
		$db=core::db();
		if(preg_match('/CREATE TABLE(?:(?:\s+if not exists\s+)|(?:\s+))`?([a-zA-Z0-9_]+_LANG)`?\s/is',$sql,$table)) {
			$table=$table[1];
			$langTable=substr($table,0,strlen($table)-4); //имя таблицы без префикса языка
			foreach($lang as $item) {
				if(!$db->query(str_replace($table,$langTable.$item,$sql))) return false;
			}
			return true;
		}
		//Поиск мультиязычных полей
		$data=$db->parseStructure($sql);
		if($data) { //это запрос "CREATE TABLE...": нужно добавить дубли мультиязычных полей
			foreach($data as $field=>$item) {
				if(substr($field,strlen($field)-5)!='_LANG') continue;
				$i1=strpos($sql,$field);
				$quote=$sql[$i1-1];
				if($sql[$i1-1]=='`' || $sql[$i1-1]=='"' || $sql[$i1-1]=="'") $i1--;
				$i2=strpos($sql,$item,$i1)+strlen($item);
				$field=substr($field,0,strlen($field)-5);
				$query='';
				foreach($lang as $i=>$lng) {
					if($i) $query.=',';
					$query.=$field.'_'.$lng.' '.$item;
				}
				$sql=substr($sql,0,$i1).$query.substr($sql,$i2);;
			}
		}
		return $db->query($sql);
	}

	/* Составляет список файлов в директории /tmp и заносит его в конфигурацию модуля с именем $id */
	public static function fileList($id,$continueIfExists=false) {
		$exists=array();
		$data=self::_scanDirectory('',$exists);
		if($exists && !$continueIfExists) return $exists; //Если какие-то файлы уже существуют, то прервать установку
		core::import('admin/core/config');
		$cfg=new config('../admin/module/'.$id);
		$cfg->file=$data;
		$cfg->save('../admin/module/'.$id);
		return true;
	}

	/* Копирует файлы из /tmp в директории движка */
	public static function copy($id) {
		$file=core::configAdmin('../module/'.$id);
		$file=$file['file'];
		$path2=str_replace('\\','/',core::path());
		$path1=$path2.'tmp/';
		foreach($file as $i=>$item) {
			$f1=$path1.$item;
			$f2=$path2.$item;
			if(is_dir($f1)) @mkdir($f2); else copy($f1,$f2);
		}
		return true;
	}

	/* Удаляет информацию о обработчиках событий */
	public static function dropHook($module,$front=true) {
		if($front) {
			$folder='hook/';
			$cfgFile='_hook';
			$moduleHook=$module['hook1'];
		} else {
			$folder='admin/hook/';
			$cfgFile='../admin/config/_hook';
			$moduleHook=$module['hook2'];
		}
		$l=strlen($folder);
		$hook=array();
		foreach($moduleHook as $item) $hook[]=array($item,'*');
		foreach($module['file'] as $item) {
			if(substr($item,0,$l)!=$folder) continue;
			$item=explode('.',substr($item,$l));
			if(in_array($item[0],$moduleHook)) continue;
			$hook[]=array($item[0],$item[1]);
		}
		if(!$hook) return;
		core::import('admin/core/config');
		$cfg=new config($cfgFile);
		foreach($hook as $item) {
			if($item[1]=='*') $cfg->delete($item[0]);
			else {
				$c=$cfg->get($item[0]);
				unset($c[@array_search($item[1],$c)]);
				if(!$c) $cfg->delete($item[0]); else $cfg->set($item[0],array_values($c));
			}
		}
		$cfg->save($cfgFile);
		return true;
	}

	/* Разрушает все таблицы, созданные модулем, а также удаляет информцию о типах меню и виджетах */
	public static function dropDb($module) {
		$db=core::db();
		$lang=core::config();
		$lang=$lang['languageList'];
		foreach($module['table'] as $item) {
			if(substr($item,strlen($item)-5)=='_LANG') {
				$item=substr($item,0,strlen($item)-4);
				foreach($lang as $itemLang) {
					$db->query('DROP TABLE '.$item.$itemLang);
				}
			} else $db->query('DROP TABLE '.$item);
		}
		if($module['menu']) $db->query('DELETE FROM menuType WHERE id IN ('.implode(',',$module['menu']).')');
		if($module['widget']) {
			$s='';
			foreach($module['widget'] as $item) if($s) $s.=','.$db->escape($item); else $s=$db->escape($item);
			$db->query('DELETE FROM widgetType WHERE name IN ('.$s.')');
		}
		if($module['right']) {
			$s='';
			foreach($module['right'] as $item) if($s) $s.=','.$db->escape($item); else $s=$db->escape($item);
			$db->query('DELETE FROM userRight WHERE module IN ('.$s.')');
		}
		return true;
	}

	/* Удаляет список файлов $file */
	public static function unlink($file) {
		$cnt=count($file)-1;
		$path=core::path();
		$languageFile=array();
		for($i=$cnt;$i>=0;$i--) {
			if(substr($file[$i],0,11)=='data/email/' || substr($file[$i],0,9)=='language/') {
				self::_unlinkLanguage($file[$i]);
				continue;
			}
			$s=$path.$file[$i];
			if(is_dir($s)) {
				if(!self::clearDirectory($s)) return false;
			} elseif(file_exists($s)) {
				if(!unlink($s)) {
					controller::$error='Не удаётся удалить файл &laquo;'.$s.'&raquo;';
					return false;
				}
			}
		}
		return true;
	}

	/* Удаляет информацию о модуле и его конфигурационный файл */
	public static function delete($id) {
		unlink(core::path().'admin/module/'.$id.'.php');
		$cfg=new config('admin/_module');
		$cfg->delete($id);
		$cfg->save('admin/_module');
		return true;
	}

	/* Возвращает версию модуля $name или false, если модуль не установлен */
	public static function version($name) {
		$cfg=core::configAdmin('_module');
		if(!isset($cfg[$name]) || $cfg[$name]['status']!=100) return false;
		return $cfg[$name]['version'];
	}

	/* Возвращает список модулей, которые завият от модуля с именем $module */
	public static function dependSearch($module) {
		$cfg=core::configAdmin('_module');
		$found=array();
		foreach($cfg as $id=>$data) {
			if($id=='core') continue;
			$m=core::configAdmin('../module/'.$id);
			if(!isset($m['depend'])) continue;
			$s=explode(',',$m['depend']);
			foreach($s as $item) {
				$i=strrpos($item,' ver');
				if($module==trim(substr($item,0,$i))) $found[]=$data['name'];
			}
		}
		if(!$found) return false;
		return $found;
	}

	/* Возвращает список файлов и директориев в /tmp/$path, в $exists сохраняет список уже существующих файлов */
	private static function _scanDirectory($path,&$exists) {
		$path1=core::Path();
		$path0=$path1.'tmp/'.$path;
		$d=opendir($path0);
		$data=array();
		while($f=readdir($d)) {
			if($f=='.' || $f=='..' || $f=='module.ini' || $f=='install.mysql.sql' || $f=='install.sqlite.sql' || $f=='install.php' || $f=='uninstall.php') continue;
			if(is_dir($path0.$f)) {
				$f=$path.$f;
				if(!is_dir($path1.$f)) $data[]=$f.'/';
				$data=array_merge($data,self::_scanDirectory($f.'/',$exists));
			} else {
			if($path=='config/' || $path=='admin/config/') if($f=='_hook.php') continue;
				if(file_exists($path1.$path.$f)) $exists[]=$path.$f;
				$data[]=$path.$f;
			}
		}
		closedir($d);
		return $data;
	}

	/* Рекурсивно удаляет файлы из директория $path */
	public static function clearDirectory($path,$self=true) {
		if($path[strlen($path)-1]!='/') $path.='/';
		$d=opendir($path);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			$f=$path.$f;
			if(is_dir($f)) {
				if(!self::clearDirectory($f)) return false;
			} else {
				if(!unlink($f)) {
					controller::$error='Не удаётся удалить файл &laquo;'.$f.'&raquo;';
					return false;
				}
			}
		}
		closedir($d);
		if($self) {
			if(!rmdir($path)) {
				controller::$error='Не удаётся удалить директорий &laquo;'.$path.'&raquo;';
				return false;
			}
		}
		return true;
	}

	//Удаляет мультиязычные файлы (шаблоны писем и файлы локализации)
	private static function _unlinkLanguage($f) {
		$i=strrpos($f,'/')+1;
		$name=preg_replace('|^[a-z][a-z]\.|','',substr($f,$i));
		$f=substr($f,0,$i).'??.'.$name;
		$f=glob(core::path().$f,GLOB_NOSORT);
		foreach($f as $item) unlink($item);
	}
}
?>