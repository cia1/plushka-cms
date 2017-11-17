<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.

/* ---------- CORE ------------------------------------------------------------------- */
/* Базовый класс, содержит основные статические методы */
class core {

	private static $_template='default'; //имя шаблона, который будет использован при выводе контента

	//Переводит строку в транслит, пригодный для использования в URL
	public static function translit($string,$max=60) {
		$string=mb_strtolower($string,'UTF-8');
		$d1=explode(',',LNGtranslit1);
		$d2=explode(',',LNGtranslit2);
		$string=str_replace($d1,$d2,$string);
		$d1=array(' ',',','&','і');
		$d2=array('-','-','-and-','i');
		$string=str_replace($d1,$d2,$string);
		$string=preg_replace('|[^A-Za-z0-9\._+-]|','',$string);
		if(strlen($string)>$max) $string=substr($string,0,$max);
		return $string;
	}

	/* Меняет имя шаблона (по умолчанию "default" - /template/(pc/pda).default.html). Возвращает имя шаблона с указанием типа клиента ("pc" или "pda").
	Разумеетя должна вызываться до начала вывода контента */
	public static function template($set=null) {
		if($set) self::$_template=$set;
		return _CLIENT_TYPE.'.'.self::$_template;
	}

	/* Возвращает путь к корню сайта */
	public static function path() {
		static $_path;
		if(!$_path) {
			$_path=dirname(__FILE__);
			$s=strrpos($_path,'/');
			if(!$s) $s=strrpos($_path,'\\');
			$_path=substr($_path,0,$s+1);
		}
		return $_path;
	}

	/* Возвращает true если включён отладочный режим и false в противном случае */
	public static function debug() {
		$cfg=self::config();
		if(isset($cfg['debug']) && $cfg['debug']) return true; else return false;
	}

	/* Возвращает массив, содержащий конфигурацию с именем $name (/config/$name.php).
	Массив возвращается по ссылке, т.к. конфигурация может быть изменена при помощи класса "config" */
	public static function &config($name='_core') {
		static $_cfg;
		if(!isset($_cfg[$name])) $_cfg[$name]=include(self::path().'config/'.$name.'.php');
		return $_cfg[$name];
	}

	/* Подключает указанный php-скрипт */
	public static function import($name) {
		include_once(self::path().$name.'.php');
	}

	/* Возвращает относительный URL до корня сайта */
	public static function url($lang=false) {
		static $_url;
		if(!$_url) {
			$_url=dirname(__FILE__);
			$_url=substr($_url,0,strlen($_url)-4);
			$_url=substr($_url,strlen($_SERVER['DOCUMENT_ROOT']));
//			if(isset($_SERVER)) {
//				$_url=substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')+1);
//			} else $_url=null;
		}
		if($lang) {
			$cfg=core::config();
			if($cfg['languageDefault']!=_LANG) return $_url._LANG.'/';
		}
		return $_url;
	}

	/* Возвращает класс, олицетворяющий пользователя (экземпляр 'user"). */
	public static function &user() {
		if(isset($_SESSION['userCore'])) return $_SESSION['userCore'];
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user'];
	}

	/* Возвращает "истинного" пользователя несмотря на режим подмены пользователя */
	public static function &userCore() {
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user'];
	}

	/* Возвращает произвольные данные из кеша.
	$id - идентификатор данных (строка), $callback - функция, возвращающая свежие данные, $timeout - время в секундах актуальности кеша */
	public static function cache($id,$callback,$timeout=-1) {
		if($timeout) {
			$cfg=self::config();
			if($cfg['debug']) $timeout=0;
		}
		$f=self::path().'cache/custom/'.$id.'.txt';
		if(file_exists($f)) {
			if($timeout===-1) return unserialize(file_get_contents($f));
			if(time()-filemtime($f)<$timeout) return unserialize(file_get_contents($f));
		}
		$data=call_user_func($callback);
		$f=fopen($f,'w');
		fwrite($f,serialize($data));
		fclose($f);
		return $data;
	}

	/* Возвращает экземпляр класса для работы с базой данных SQLite
	Если задан $nc, то будет открыто новое подключение */
	public static function sqlite($nc=false) {
		static $_sqlite;
		if(!$_sqlite) {
			self::import('core/sqlite3');
			if(self::debug()) self::import('core/sqlite3-debug'); else class_alias('_sqlite','sqlite');
		}
		if($nc) return new sqlite();
		if(!$_sqlite) $_sqlite=new sqlite();
		return $_sqlite;
	}

	/* Возвращает экземпляр класса для работы с базой данных MySQL
	Если задан $nc, то будет открыто новое подключение */
	public static function mysql($nc=false) {
		static $_mysql;
		if(!$_mysql) {
			self::import('core/mysqli');
			if(self::debug()) self::import('core/mysqli-debug'); else class_alias('_mysql','mysql');
		}
		if($nc) return new mysql();
		if(!$_mysql) $_mysql=new mysql();
		return $_mysql;
	}

	/* Возвращает экземпляр класса для работы СУБД, указанной в настройках
	Если задан $nc, то будет открыто новое подключение */
	public static function db($nc=false) {
		static $_db;
		if(!$_db) {
			$cfg=self::config();
			if($cfg['dbDriver']=='mysql') $_db=self::mysql($nc); else $_db=self::sqlite($nc);
		}
		return $_db;
	}

	/* Возвращает идентификатор текущего пользователя и "0" если пользователь не авторизован */
	public static function userId() {
		if(isset($_SESSION['userCore'])) return $_SESSION['userCore']->id;
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user']->id;
	}

	/* Возвращает группу пользователей, к которой относится текущий пользователь */
	public static function userGroup() {
		$u=self::user();
		return $u->group;
	}

	/* Формирует относительную ссылку используя механизм подмены ссылок */
	public static function link($link) {
		static $_link;
		static $_main;
		static $_lang;
		if(substr($link,0,7)=='http://' || substr($link,0,8)=='https://') return $link;
/*
		if(!isset($_link)) {
			$cfg=self::config();
			$_link=$cfg['link'];
			$_main=$cfg['mainPath'];
			if(_LANG==$cfg['languageDefault']) $_lang=''; else $_lang=_LANG.'/';
		}
		if($link==$_main) return self::url().$_lang;
		$i=strpos($link,'?');
		if($i) {
			$end=substr($link,$i);
			$link=substr($link,0,$i);
		} else $end='';
		$i=$len=strlen($link);
		do {
			$s=substr($link,0,$i);
			$i=strrpos($s,'/');
		} while($s && !isset($_link[$s]));
		if($s) {
			$len2=strlen($s);
			if($len2==$len) $link=$_link[$s]; else $link=$_link[$s].substr($link,$len2);
		}
*/
		return self::url().$_lang.$link.$end;
	}

	/* Возвращает экземпляр класса form (конструктор HTML-форм). Если $namespace не задан, то будет использовано имя запрошенного контроллера */
	public static function form($namespace=null) {
		if(!class_exists('form')) include(self::path().'core/form.php');
		return new form($namespace);
	}

	/* Возвращает экземпляр класса model (универсальная модель). $namespace - имя таблицы (если нужно), $db - имя СУБД */
	public static function model($namespace=null,$db=null) {
		if(!class_exists('model')) include(self::path().'core/model.php');
		return new model($namespace,$db);
	}

	//Устанавливает или возвращает текст сообщения об успешно выполненной операции
	public static function success($message=null) {
		if($message===false) {
			$message=$_SESSION['messageSuccess'];
			unset($_SESSION['messageSuccess']);
			return $message;
		}
		if($message!==null) $_SESSION['messageSuccess']=$message;
		return (isset($_SESSION['messageSuccess']) ? $_SESSION['messageSuccess'] : null);
	}

	//Устанавливает и возвращает текст сообщения об ошибке
	public static function error($message=null) {
		if($message===false) {
			$message=$_SESSION['messageError'];
			unset($_SESSION['messageError']);
			return $message;
		}
		if($message!==null) $_SESSION['messageError']=$message;
		return (isset($_SESSION['messageError']) ? $_SESSION['messageError'] : null);
	}
	/* Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес.
	Если задан $message, то после перенаправления будет выведено указанное сообщение */
	public static function redirect($url,$message=null,$code=302) {
		if($message) core::success($message);
		header('Location: '.self::link($url),true,$code);
		exit;
	}

	/* Прерывает выполнение скрипта и генерирует ошибку 404 */
	public static function error404() {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
		core::language('error');
		controller::$self->url[0]='error';
		controller::$self->pageTitle=LNGPageNotExists1;
		controller::$self->render('404',true);
		exit;
	}

	/* Генерирует виджет. Обрабатывает {{widget}}
	$name - имя виджета, $options - какие-либо параметры виджета, $cacheTime - время актуальности кеша, $title - заголовок, $link - страница, для которой выводится виджет (только если процедура вызвана при обработке секции) */
	public static function widget($name,$options=null,$cacheTime=null,$title=null,$link=null) {
		if(is_string($options) && isset($options[1]) && $options[1]==':') $options=unserialize($options);
		//Нужно ли кешировать?
		$debug=self::debug();
		if($cacheTime && !$debug) {
			if(is_array($options)) {
				$f='';
				ksort($options);
				foreach($options as $index=>$value) $f.=$index.$value;
			} else $f=$options;
			$f=md5($f);
			$cacheFile=self::path().'cache/widget/'.$name.'.'.$f.'.html';
			if(file_exists($cacheFile)) {
				$f=filemtime($cacheFile)+$cacheTime*60;
				if($f>time()) {
					include($cacheFile);
					return;
				}
			}
			ob_start();
		}
		$f='widget'.$name;
		include_once(self::path().'widget/'.$name.'.php');
		$w=new $f($options,$link);
		$view=$w();
		if($view!==null && $view!==false) { //Если widget() вернул null или false, то выводить HTML-код ненужно (виджет может выводиться только при определённых условиях)
			echo '<section class="widget'.$name.'">';
			//Если пользователь является администратором, то вывести элементы управления в соответствии его правам
			$user=core::userCore();
			if($user->group>=200) {
				$admin=new admin();
				$link=$w->adminLink();
				foreach($link as $item) {
					if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
				}
			}
			if($title) $w->title($title); //Вывод заголовка
			if(is_object($view)) $view->render(); else $w->render($view);
			echo '<div style="clear:both;"></div></section>';
		}
		if($cacheTime && !$debug) {
			$f=fopen($cacheFile,'w');
			fwrite($f,ob_get_contents());
			fclose($f);
			ob_end_flush();
		}
	}

	/* Генерирует HTML-представление секции. Обрабатывает {{section}}
	$name - имя секции */
	public static function section($name) {
		//Выборка виджетов секции. Построить SQL-запрос, включающий все варианты страниц
		$s=$query='';
		$cnt=count($_GET['corePath'])-1;
		if(!$_GET['corePath'][1]) $cnt--;
		foreach($_GET['corePath'] as $i=>$item) {
			if(!$item) break;
			if($s) {
				$s.='/';
				$query.=',';
			}
			$s.=$item;
			if($i<$cnt) $query.='"'.$s.'/","'.$s.'*"'; else $query.='"'.$s.'/","'.$s.'."';
		}
		$db=self::db();
		$items=$db->fetchArray('SELECT w.name,w.data,w.cache,w.publicTitle,w.title_'._LANG.',s.url,w.groupId FROM section s INNER JOIN widget w ON w.id=s.widgetId WHERE s.name='.$db->escape($name).' AND s.url IN('.$query.') ORDER BY s.sort');
		$cnt=count($items);
		echo '<div class="section section'.$name.'">';
		$u=self::userCore();
		//Если пользователь админ и имеет права управления секциями, то вывести кнопки административного интерфейса
		if($u->group>=200 && ($u->group==255 || isset($u->right['section.*']))) {
			$admin=new admin();
			$admin->add('?controller=section&name='.$name,'section','Управление виджетами в этой области','Секция');
		}
		//Теперь перебрать все виджеты секции
		$userGroup=core::userGroup();
		for($i=0;$i<$cnt;$i++) {
			if($items[$i][6]!==null && $items[$i][6]!=$userGroup) continue;
			core::widget($items[$i][0],$items[$i][1],$items[$i][2],($items[$i][3]=='1' ? $items[$i][4] : null),$items[$i][5]);
		}
		echo '</div>';
	}

	/* Возвращает HTML-тег <script src...> или пустую строку, если скрипт уже подключен. Используется чтобы избежать повторного подключение JavaScript.
	$name - имя скрипта, если скрипт находится на сайте, то указывать относительный путь от /public/js, имя должно быть без ".js" */
	public static function script($name) {
		static $_script;
		if(!$_script) $_script=array();
		if(isset($_script[$name])) return '';
		$_script[$name]=true;
		if(substr($name,0,7)=='http://') return '<script type="text/javascript" src="'.$name.'"></script>';
		return '<script type="text/javascript" src="'.self::url().'public/js/'.$name.'.js"></script>';
	}

	/* Генерирует событие (прерывание).
	Параметры: 1й - системное имя события, 2й и последующие - индивидуальны для каждого события */
	public static function hook() {
		$data=func_get_args();
		$name=array_shift($data);
		$d=opendir(core::path().'hook');
		$result=array();
		$len=strlen($name);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(substr($f,0,$len)!=$name) continue;
			$tmp=self::_hook($f,$data);
			if($tmp===false || $tmp===null) {
				closedir($d);
				return false;
			}
			$result[]=$tmp;
		}
		return $result;
	}

	//Подключает файл локализации
	public static function language($name) {
		$f=self::path().'language/'._LANG.'.'.$name.'.php';
		if(!file_exists($f)) return false;
		include_once($f);
		return true;
	}

	private static function _hook($name,$data) {
		if(!include(self::path().'hook/'.$name)) return false; else return true;
	}
}
/* --- --- */

core::language('global');
$_GET['corePath']=array('plushka','');
if(isset($_FILES['plushka'])) {
	$f1=$_FILES['plushka'];
	foreach($f1['name'] as $name=>$value) {
		if(!is_array($value)) {
			$_POST['plushka'][$name]=array('name'=>$value,'tmpName'=>$f1['tmp_name'][$name],'type'=>$f1['type'][$name],'size'=>$f1['size'][$name]);
		} else {
			$_POST['plushka'][$name]=array();
			for($i=0,$cnt=count($value);$i<$cnt;$i++) {
				$_POST['plushka'][$name][]=array('name'=>$value[$i],'tmpName'=>$f1['tmp_name'][$name][$i],'type'=>$f1['type'][$name][$i],'size'=>$f1['size'][$name][$i]);
			}
		}
	}
}