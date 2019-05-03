<?php
//Этот файл является частью фреймворка. Внесение изменений не рекомендуется.
namespace plushka\admin\core;
require_once(__DIR__.'/../../core/core.php');

error_reporting(E_ALL);
ini_set('display_errors',1);

/* ---------- CORE ------------------------------------------------------------------- */
/* Базовый класс, содержит основные статические методы */
class core extends \plushka\core\core {

	private static $_template='default'; //Имя используемого шаблона

	//Возвращает true, если модуль с указанным ID установлен
	public static function moduleExists($id) {
		$f=plushka::path().'admin/module/'.self::translit($id).'.php';
		return file_exists($f);
	}

	//Удаляет файл кеша
	public static function cacheCustomClear($id) {
		$f=self::path().'cache/custom/'.$id.'.txt';
		if(file_exists($f)) return unlink($f);
		else return false;
	}

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
		if(isset($_GET['_front'])) return 'front';
		if($set) self::$_template=$set;
		return self::$_template;
	}

	/* Возвращает путь к корню сайта */
	public static function path() {
		static $_path;
		if(!$_path) {
			$_path=__DIR__;
			$s=strrpos($_path,'/');
			if(!$s) $s=strrpos($_path,'\\');
			$_path=substr($_path,0,$s-5);
		}
		return $_path;
	}

	/* Возвращает true если включён отладочный режим и false в противном случае */
	public static function debug() {
		$cfg=plushka::config();
		if(isset($cfg['debug']) && $cfg['debug']) return true; else return false;
	}

	/* Возвращает массив, содержащий конфигурацию с именем $name (/config/$name.php).
	Массив возвращается по ссылке, т.к. конфигурация может быть изменена при помощи класса "config" или другим способом */
	public static function &config($name='_core',$attribute=null) {
		static $_cfg;
		if(!isset($_cfg[$name])) {
			if($name==='admin') $f=plushka::path().'admin/config/_core.php';
			elseif(substr($name,0,6)==='admin/') $f=plushka::path().'admin/config/'.substr($name,6).'.php';
			else $f=plushka::path().'config/'.$name.'.php';
			if(file_exists($f)) $_cfg[$name]=include($f); else $_cfg[$name]=null;
		}
		if($attribute===null) return $_cfg[$name];
		if(!isset($_cfg[$name][$attribute])) $value=null;
		else $value=$_cfg[$name][$attribute];
		return $value;
	}

	/* Подключает указанный php-скрипт */
	public static function import($name) {
		include_once(plushka::path().$name.'.php');
	}

	//Возвращает относительный URL до корня сайта.
	//$lang===true - добавить префикс языка, $lang является строкой - сслыка на страницу с языком $lang;
	//$domain - полная, а не относительная ссылка
	public static function url($lang=false,$domain=false) {
		static $_url;
		if(!$_url) {
			if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
				$_url=str_replace(array('\\','admin'),array('/',''),dirname($_SERVER['SCRIPT_NAME']));
			} else { //CGI-запрос
				$cfg=plushka::config('cgi');
				$_url=$cfg['url'];
			}
		}
		$url=$_url;
		if($lang===true) {
			$cfg=plushka::config();
			if($cfg['languageDefault']!=_LANG) $url.=_LANG.'/';
		} elseif($lang) {
			$cfg=plushka::config();
			if($cfg['languageDefault']!=$lang) $url.=$lang.'/';
		}
		if($domain) {
			if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
				$url='://'.$_SERVER['HTTP_HOST'].$url;
			} else {
				$cfg=plushka::config('cgi');
				$url=$cfg['host'].$_url;
			}
			if(isset($_SERVER['HTTPS'])) {
				if($_SERVER['HTTPS']=='off') $url='http'.$url;
				else $url='https'.$url;
			} elseif(isset($_SERVER['REQUEST_SCHEME'])) $url=$_SERVER['REQUEST_SCHEME'].$url;
			elseif(isset($_SERVER['SERVER_PORT'])) {
				if($_SERVER['SERVER_PORT']==443) $url='https'.$url;
				else $url='http'.$url;
			} else $url='http'.$url;
		}
		return $url;
	}

	//Подключает файл локализации (из общедоступной части сайта)
	public static function language($name) {
		$f=self::path().'language/'.$name.'.'._LANG.'.php';
		if(!file_exists($f)) return false;
		include_once($f);
		return true;
	}

	/* Возвращает класс, олицетворяющий пользователя (экземпляр 'user"). */
	public static function &user() {
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user'];
	}

	/* Возвращает "истинного" пользователя несмотря на режим подмены пользователя */
	public static function &userCore() { return self::user(); }

	/* Возвращает идентификатор текущего пользователя и "0" если пользователь не авторизован */
	public static function userId() {
		if(isset($_SESSION['userCore'])) return $_SESSION['userCore']->id;
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user']->id;
	}

	/* Возвращает экземпляр класса для работы с базой данных SQLite
	Если задан $newQuery, то будет открыто новое подключение */
	public static function sqlite($newQuery=false) {
		static $_sqlite;
		if(!$_sqlite) plushka::import('admin/core/sqlite3');
		if($newQuery) return new sqliteExt(plushka::path().'data/database3.db');
		if(!$_sqlite) $_sqlite=new sqliteExt(plushka::path().'data/database3.db');
		return $_sqlite;
	}

	/* Возвращает экземпляр класса для работы с базой данных MySQL
	Если задан $newQuery, то будет открыто новое подключение */
	public static function mysql($newQuery=false) {
		static $_mysqli;
		if(!$_mysqli) plushka::import('admin/core/mysqli');
		if($newQuery) return new mysqlExti();
		if(!$_mysqli) $_mysqli=new mysqlExti();
		return $_mysqli;
	}

	/* Возвращает экземпляр класса для работы СУБД, указанной в настройках
	Если задан $newQuery, то будет открыто новое подключение */
	public static function db($newQuery=false) {
		static $_db;
		if($newQuery) {
			$driver=self::config();
			$driver=$driver['dbDriver'];
			return self::$driver($newQuery);
		}
		if(!$_db) {
			$driver=self::config();
			$driver=$driver['dbDriver'];
			$_db=self::$driver($newQuery);
		}
		return $_db;
	}

	/* Возвращает группу пользователей, к которой относится текущий пользователь */
	public static function userGroup() {
		$u=plushka::user();
		return $u->group;
	}

	/* Возвращает права пользователя */
	public static function userRight() {
		$u=plushka::user();
		return $u->right;
	}

	public static function link($link,$lang=true,$domain=false) {
		if(substr($link,0,7)==='http://' || substr($link,0,8)==='https://' || $link[0]==='/') return $link;
		if(substr($link,0,6)==='admin/') return self::linkAdmin(substr($link,6),$lang,$domain);
		return self::linkPublic($link,$lang,$domain);
	}

	/* Формирует относительную ссылку, служит главным образом для добавления к ссылке дополнительных параметров */
	public static function linkAdmin($link,$lang=true,$domain=false) {
		$end='&_lang='.($lang===true ? _LANG : $lang);
		if(isset($_GET['_front'])) $end.='&_front';
		if(isset($_GET['backlink'])) $end.='&backlink='.urlencode($_GET['backlink']);
		$tmp=explode('/',str_replace('?','&',$link));
		$link=self::url(false,$domain).'admin/index.php?controller='.$tmp[0];
		if(isset($tmp[1])===true) $link.='&action='.$tmp[1];
		$link.=$end;
		return $link;
	}

	/**
	 * Генерирует относительную или абсолютную ссылку
	 * Для CGI-режима использует /config/cgi.php для определения имени домена и базового URL
	 * @param string $link Исходная ссылка в формате controller/etc...
	 * @param bool $lang Если false, то суффикс языка не будет добавлен
	 * @param bool $domain Если true, то будет сгенерирована абсолютная ссылка
	 */
	public static function linkPublic($link,$lang=true,$domain=false) {
		static $_link;
		static $_main;
		if(!$link) return plushka::url($lang,$domain);
		if(substr($link,0,7)=='http://' || substr($link,0,8)=='https://' || $link[0]=='/') return $link;
		if(!isset($_link)) {
			$cfg=self::config();
			$_link=$cfg['link'];
			$_main=$cfg['mainPath'];
		}
		if($link==$_main) return self::url($lang,$domain);
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
		return self::url($lang,$domain).$link.$end;
	}

	/* Генерирует виджет. Обрабатывает {{widget}}
	$name - имя виджета, $options - какие-либо параметры виджета, $cacheTime - время актуальности кеша, $title - заголовок, $link - страница, для которой выводится виджет (только если процедура вызвана при обработке секции) */
	public static function widget($name,$options=null,$cacheTime=null,$title=null,$link=null) {
		if(is_string($options) && isset($options[1]) && $options[1]==':') $options=unserialize($options);
		//Нужно ли кешировать?
		if($cacheTime) {
			if(is_array($options)) {
				$f='';
				ksort($options);
				foreach($options as $index=>$value) $f.=$index.$value;
			} else $f=$options;
			$f=md5($f);
			$cacheFile=plushka::path().'admin/cache/widget/'.$name.'.'.$f.'.html';
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
		include_once(plushka::path().'widget/'.$name.'.php');
		$w=new $f($options,$link);
		$view=$w();
		if($view!==null && $view!==false) { //Если widget() вернул null или false, то выводить HTML-код ненужно (виджет может выводиться только при определённых условиях)
			echo '<section class="widget'.$name.'">';
			//Если пользователь является администратором, то вывести элементы управления в соответствии его правам
//			$user=plushka::userCore();
//			if($user->group>=200) {
//				$admin=new admin();
//				$link=$w->adminLink();
//				foreach($link as $item) {
//					if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
//				}
//			}
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

	/* Возвращает экземпляр класса form (конструктор HTML-форм). Если $namespace не задан, то будет использовано имя запрошенного контроллера */
	public static function form($url=null) {
		if(class_exists('formEx')===false) include(plushka::path().'admin/core/form.php');
		return new formEx($url);
	}

	/* Возвращает экземпляр класса table, служащего для генерации HTML-таблиц (<table>) */
	public static function table($html=null) {
		if(!class_exists('table')) include(plushka::path().'admin/core/html.php');
		return new table($html);
	}

	//Возвращает экземляр класса валидатора и инициализирует атрибутами, если $attribute указан
	public static function validator($attribute=null) {
		plushka::import('core/validator');
		$validator=new $validator();
		if($attribute) $validator->set($attribute);
		return $validator;
	}

	/* Возвращает экземпляр класса model (универсальная модель) для таблицы $table, $db - имя СУБД */
	public static function model($table,$db='db') {
		if(substr($table,0,6)==='admin/') {
			$table=substr($table,6);
			plushka::import('admin/model/'.$table);
			return new $table();
		}
		$f=plushka::path().'model/'.$table.'.php';
		if(file_exists($f)) {
			include_once($f);
			return new $table();
		}
		if(!class_exists('modelEx')) include(plushka::path().'admin/core/modelEx.php');
		return new modelEx($table,$db);
	}

	/* Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес.
	Если задан $message, то после перенаправления будет выведено указанное сообщение */
	public static function redirect($url,$message=null,$code=302) {
		if(isset($_GET['backlink'])) {
			$url=$_GET['backlink'];
			unset($_GET['backlink']);
			$message='';
		}
		if($message) plushka::success($message);
		if($message || plushka::success()) {
			if(isset($_GET['_front'])) {
				echo '<div id="content">';
				echo '<div class="messageSuccess">'.plushka::success(false).'</div>';
				echo '</div>';
				echo '<link href="'.plushka::url().'admin/public/template/front.css" rel="stylesheet" type="text/css" media="screen" /><script type="text/javascript" src="'.plushka::url().'public/js/jquery.min.js"></script>
				<script type="text/javascript" src="'.plushka::url().'admin/public/js/front.js"></script>
				<script>setTimeout(function() { top.window.location=top.window.location.href; },2000);</script>';
				exit;
			}
		}
		header('Location: '.plushka::link('admin/'.$url),true,$code);
		exit;
	}

	/* Выполняет редирект в общедоступной части сайта */
	public static function redirectPublic($url) {
		if($url[0]!='/') $url=plushka::url().$url;
		if(isset($_GET['_front'])) {
			echo '<script>top.document.location="'.$url.'";</script>';
			exit;
		}
		header('Location: '.$url);
		exit;
	}

	/* Прерывает выполнение скрипта и генерирует ошибку 404 */
	public static function error404() {
		if(isset($_GET['_front'])) echo '<div class="messageError">Запрошенная страница не существует :(</div>';
		else {
			header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
			plushka::$controller->url[0]='error';
			plushka::$controller->error(404);
			$code=404;
			plushka::$controller->render($code);
		}
		exit;
	}

	//Устанавливает или возвращает сообщение об ошибке
	public static function error($message=null) {
		if($message===false) {
			$message=(isset($_SESSION['messageError']) ? $_SESSION['messageError'] : null);
			unset($_SESSION['messageError']);
			return $message;
		}
		if($message!==null) $_SESSION['messageError']=$message;
		return (isset($_SESSION['messageError']) ? $_SESSION['messageError'] : null);
	}

	//Устанавливает или возвращает сообщение об успехе
	public static function success($message=null) {
		if($message===false) {
			$message=$_SESSION['messageSuccess'];
			unset($_SESSION['messageSuccess']);
			return $message;
		}
		if($message!==null) $_SESSION['messageSuccess']=$message;
		return (isset($_SESSION['messageSuccess']) ? $_SESSION['messageSuccess'] : null);
	}

	public static function scriptAdmin($name) {
		static $_script;
		if(!$_script) $_script=array();
		if(in_array($name,$_script)) return '';
		$_script[]=$name;
		return '<script type="text/javascript" src="'.plushka::url().'admin/public/js/'.$name.'.js"></script>';
	}

	/**
	 * Генерирует событие
	 * Обработчики события - это файлы /hook/$name.{module}.php
	 * @param string $name Имя события (файлы )
	 * @param mixed ...$data Произвольные данные, которые будут доступны в обработчике события
	 * @return mixed|false False, если хотя бы один обработчик вернул false, иначе массив значений, возвращённых обработчиками событий
	 */
	public static function hook($name,...$data) {
		$d=opendir(plushka::path().'admin/hook');
		$result=array();
		$len=strlen($name);
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(substr($f,0,$len)!=$name) continue;
			$tmp=self::_hook($f,$data);
			if($tmp===false) {
				closedir($d);
				return false;
			} elseif($tmp!==null) $result[]=$tmp;
		}
		closedir($d);
		return $result;
	}

	private static function _hook($name,$data) {
		if(!include(plushka::path().'admin/hook/'.$name)) return false; else return true;
	}

}
/* --- --- */



/* --- CONTROLLER ---*/
class controller {

	public $url; //предназначен для сохранения запрошенного URL (в виде массива, содержащего два элемента)
	protected $metaTitle='';
	public $pageTitle=''; //отображаемый заголовок, если задан, то будет выведен в теге <H1 class="pageTitle">
	protected $cite=''; //краткий комментарий
	private $_button=''; //HTML код кнопок

	private $_head='';

	public function __construct() {
		$this->url=$_GET['corePath'];
		if(!$_GET['corePath'][1]) $this->url[1]='index';
	}

	/* Служит для подключения JavaScript или других тегов в область <head> */
	public function js($text) {
		if($text[0]!='<') $text=plushka::js($text);
		$this->_head.=$text;
	}

	public function scriptAdmin($text) {
		if($text[0]!='<') $text=plushka::scriptAdmin($text);
		$this->_head.=$text;
	}

	/* Служит для подключения CSS или других тегов в область <head> */
	protected function style($text) {
		if($text[0]!='<') $text='<link type="text/css" rel="stylesheet" href="'.plushka::url().'admin/public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/* Добавляет кнопку в специально отведённую область
	Параметры: string $link - ссылка на страницу админки; string $image - условное имя файла изображения кнопки; string $title - всплывающая подсказка; $alt - текст тега ALT; $html - любой другой HTML-код, который будет добавлен к тегу <a> */
	public function button($link,$image,$title='',$alt='',$html='') {
		if($link==='html') $this->_button.=$image;
		else {
			$this->_button.='<a href="'.plushka::link('admin/'.$link).'"'.($html ? ' '.$html : '').'><img src="'.plushka::url().'admin/public/icon/'.$image.'32.png" alt="'.($alt ? $alt : $title).'" title="'.$title.'" /></a>';
		}
	}

	/* Генерирует HTML-код (шаблон, теги в <head>, кнопки админки, представление)
	$view - представление (null, строка или объект); $renderTemplate - если true, то выводится контент без шаблона (для AJAX-запросов) */
	public function render($view,$renderTemplate=true) {
		if(!plushka::template()) $renderTemplate=false;
		if(!$view) return; //Если нет представления, то ничего не выводить
		if($renderTemplate) { //Вывести верхнюю часть шаблона
			$s=plushka::path().'admin/cache/template/'.plushka::template().'Head.php';
			if(!file_exists($s) || plushka::debug()) {
				\plushka\core\cache::template(plushka::template());
			}
			include($s);
			if($this->pageTitle) echo '<h1 class="pageTitle">'.$this->pageTitle.'</h1>';
		} else echo $this->_head;
		if($this->_button) echo '<div class="_operation">'.$this->_button.'</div>'; //Кнопки
		//Сообщение об ошибке (если есть)
		if(isset($_SESSION['messageError'])) {
			echo '<div class="messageError">'.plushka::error(false).'</div>';
		}

		//Сообщение об успехе (если был редирект с сообщением)
		if(isset($_SESSION['messageSuccess'])) {
			echo '<div class="messageSuccess">'.plushka::success(false).'</div>';
		}
		if(is_object($view)) $view->render();
		elseif($view=='_empty') include(plushka::path().'admin/view/_empty.php');
		else include(plushka::path().'admin/view/'.$this->url[0].$view.'.php');
		if($this->cite) echo '<cite>'.$this->cite.'</cite>'; //Поясняющий текст
		if($renderTemplate) include(plushka::path().'admin/cache/template/'.plushka::template().'Footer.php'); //Нижняя часть шаблона
		elseif(!isset($_GET['_front'])) echo '<div style="clear:both;"></div>';
		if($renderTemplate && isset($_GET['_front'])) {
			$f='help'.$this->url[1];
			if(method_exists($this,$f)) {
				echo '<script type="text/javascript">_adminDialogBoxSetHelp("',$this->$f(),'")</script>';
			}
		}
	}

	/* Служебный метод, используется при провоцировании HTTP-ошибок (только 404) */
	public function error($code) {
		switch($code) {
		case '404':
			$this->pageTitle='Страница не найдена';
			break;
		}
		return $code;
	}

}
/* --- --- */



/* --- WIDGET --- */
//Базовый класс виджета
class widget {
	protected $options; //Предназначена для хранения параметров виджета, может быть переопределён в конструкторе
	protected $link; //Предназначена для хранения той страницы, для которой виджет был сформирован в составе секции
	public function __construct($options,$link) { $this->options=$options; $this->link=$link; }
	public function action() {}
	public function title($title) { //Заголовок виджета. Используется в основном чтобы дать возможность виджету вставить какую-либо ссылку в заголовок
		echo '<p class="title">'.$title.'</p>';
	}
//	public function adminLink() { return array(); }

	public function render($view) {
		if($view!==true) include(plushka::path().'view/widget'.$view.'.php');
	}

	/* Выводит HTML-код кнопок админки для ЭЛЕМЕНТА СПИСКА */
//	public function admin($data) {
//		$u=plushka::userCore();
//		if($u->group<200) return;
//		$admin=new admin();
//		$link=$this->adminLink2($data);
//		foreach($link as $item) {
//			if($u->group==255 || isset($u->right[$item[0]])) $admin->render($item);
//		}
//	}
}
/* --- --- */



/* --- USER --- */
//Класс "пользователь" всегда находится в сессии, получить экземляр можно при помощи plushka::user() и plushka::usercore()
class user {
	public $id;
	public $login;
	public $email;
	public $group=0;

	public function __construct($id=null) {
		if($id) $this->model($id);
	}

	//Возвращает модель, позволяющую управлять пользователями. Если $id задан, то модель будут загружены данные по указанному идентификатору
	public function model($id=null) {
		static $model;
		if(!isset($model)) {
			plushka::import('model/user');
			$model=new modelUser($id,$this);
		}
		return $model;
	}
}
/* --- --- */



/* С этой функции начинается вся основная работа */
function runApplication($renderTemplate=true) {
	session_start();
	plushka::$controller=new sController($_GET['corePath'][1]);
	$alias=plushka::$controller->url[0];
	//Проверка прав доступа к запрошенной странице
	$user=plushka::user();
	if($alias!='user' || plushka::$controller->url[1]!='Login') {
		if($user->group<200) plushka::redirect('user/login');
		if($user->group!=255) {
			if(!method_exists(plushka::$controller,'right')) {
				plushka::error('Недостаточно прав для доступа к разделу');
				plushka::redirect('user/login');
			}
			$right=plushka::$controller->right();
			if(!isset($right[plushka::$controller->url[1]])) {
				plushka::error('Недостаточно прав для доступа к разделу');
				plushka::redirect('user/login');
			}
			$right=explode(',',$right[plushka::$controller->url[1]]);
			foreach($right as $item) {
				if($item=='*') continue;
				if(!isset($user->right[$item])) {
					plushka::error('Недостаточно прав для доступа к разделу');
					plushka::redirect('user/login');
				}
			}
		}
	}
	//Запуск submit-действия
	if(isset($_POST[$alias])) {
		$s='action'.plushka::$controller->url[1].'Submit';
		if(method_exists('sController',$s)===false) plushka::error404();
		//Подготовить данные _POST и _FILES для передачи submit-действию
		if(isset($_FILES[$alias])) {
			$f1=$_FILES[$alias];
			foreach($f1['name'] as $name=>$value) {
				if(is_array($value)) {
					$_POST[$alias][$name]=array();
					foreach($value as $i=>$valueValue) {
						$_POST[$alias][$name][]=array('name'=>$valueValue,'tmpName'=>$f1['tmp_name'][$name][$i],'type'=>$f1['type'][$name][$i],'size'=>$f1['size'][$name][$i]);
					}
				} else $_POST[$alias][$name]=array('name'=>$value,'tmpName'=>$f1['tmp_name'][$name],'type'=>$f1['type'][$name],'size'=>$f1['size'][$name]);
			}
		}
		$post=$_POST[plushka::$controller->url[0]];
		@$data=plushka::$controller->$s($post);
		//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
		if(isset($_GET['_serialize'])) {
			if(plushka::error()) die(plushka::error(false));
			echo "OK\n";
			if(isset($post['cacheTime']) || (is_array($data) && isset($data['cacheTime']))) {
				echo $post['cacheTime'];
				if(is_array($data) && isset($data['cacheTime'])) unset($data['cacheTime']);
			} else echo '0';
			echo "\n";
			if(is_array($data)) echo serialize($data); else echo $data;
			exit;
		}
	} else $data=null;
	//Запуск "обычного" действия
	if(plushka::$controller->url[1]) {
		$s='action'.splushka::$controller->url[1];
		if(method_exists('sController',$s)===false) plushka::error404();
		//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
		if(isset($_GET['_serialize'])===true && isset($_POST['data'])===true) {
			if(substr($_POST['data'],0,2)=='a:' && $_POST['data'][strlen($_POST['data'])-1]=='}') $view=plushka::$controller->$s(unserialize($_POST['data']));
			else $view=plushka::$controller->$s($_POST['data']);
		} else $view=plushka::$controller->$s($data);
	}
	plushka::$controller->render($view,$renderTemplate);
}

/* --- INITIALIZE _GET-variables --- */
header('Content-type:text/html; Charset=utf-8');

//Обработать запрошенный URL и положить его в $_GET['corePath']
if(!isset($_GET['controller'])) {
	$cfg=plushka::config('admin');
	$_GET['corePath']=explode('/',$cfg['mainPath']);
	if(!isset($_GET['corePath'][1])) $_GET['corePath'][1]='Index';
	unset($cfg);
} else {
	$_GET['corePath']=array($_GET['controller']);
	if(isset($_GET['action'])) $_GET['corePath'][1]=$_GET['action']; else $_GET['corePath'][1]='Index';
}