<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.

/* ---------- CORE ------------------------------------------------------------------- */
/* Базовый класс, содержит основные статические методы */
class core {

	private static $_template='default'; //имя шаблона, который будет использован при выводе контента

	//Переводит строку в транслит, пригодный для использования в URL
	public static function translit($string) {
		$string=mb_strtolower($string,'UTF-8');
		$d1=explode(',',LNGtranslit1);
		$d2=explode(',',LNGtranslit2);
		$string=str_replace($d1,$d2,$string);
		$d1=array(' ',',','/','%','?','@','#','&');
		$d2=array('-','-','','','','','','-and-');
		return str_replace($d1,$d2,$string);
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
		$cfg=core::config();
		if(isset($cfg['debug']) && $cfg['debug']) return true; else return false;
	}

	/* Возвращает массив, содержащий конфигурацию с именем $name (/config/$name.php).
	Массив возвращается по ссылке, т.к. конфигурация может быть изменена при помощи класса "config" */
	public static function &config($name='_core') {
		static $_cfg;
		if(!isset($_cfg[$name])) $_cfg[$name]=include(core::path().'config/'.$name.'.php');
		return $_cfg[$name];
	}

	/* Подключает указанный php-скрипт */
	public static function import($name) {
		include_once(core::path().$name.'.php');
	}

	/* Возвращает относительный URL до корня сайта */
	public static function url() {
		static $_url;
		if(!$_url) {
			if(isset($_SERVER)) {
				$_url=substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')+1);
			} else $_url=null;
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
			$cfg=core::config();
			if($cfg['debug']) $timeout=0;
		}
		$f=core::path().'cache/custom/'.$id.'.txt';
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
		if(!$_sqlite) core::import('core/sqlite3');
		if($nc) return new sqlite();
		if(!$_sqlite) $_sqlite=new sqlite();
		return $_sqlite;
	}

	/* Возвращает экземпляр класса для работы с базой данных MySQL
	Если задан $nc, то будет открыто новое подключение */
	public static function mysql($nc=false) {
		static $_mysql;
		if(!$_mysql) core::import('core/mysqli');
		if($nc) return new mysql();
		if(!$_mysql) $_mysql=new mysql();
		return $_mysql;
	}

	/* Возвращает экземпляр класса для работы СУБД, указанной в настройках
	Если задан $nc, то будет открыто новое подключение */
	public static function db($nc=false) {
		static $_db;
		if(!$_db) {
			$cfg=core::config();
			if($cfg['dbDriver']=='mysql') $_db=core::mysql($nc); else $_db=core::sqlite($nc);
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
		$u=core::user();
		return $u->group;
	}

	/* Формирует относительную ссылку используя механизм подмены ссылок */
	public static function link($link) {
		static $_link;
		static $_main;
		static $_lang;
		if(substr($link,0,7)=='http://' || substr($link,0,8)=='https://') return $link;
		if(!isset($_link)) {
			$cfg=core::config();
			$_link=$cfg['link'];
			$_main=$cfg['mainPath'];
			if(_LANG==$cfg['languageDefault']) $_lang=''; else $_lang=_LANG.'/';
		}
		if($link==$_main) return core::url().$_lang;
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
		return core::url().$_lang.$link.$end;
	}

	/* Возвращает экземпляр класса form (конструктор HTML-форм). Если $namespace не задан, то будет использовано имя запрошенного контроллера */
	public static function form($namespace=null) {
		if(!class_exists('form')) include(core::path().'core/form.php');
		return new form($namespace);
	}

	/* Возвращает экземпляр класса model (универсальная модель). $namespace - имя таблицы (если нужно), $db - имя СУБД */
	public static function model($namespace=null,$db='db') {
		if(!class_exists('model')) include(core::path().'core/model.php');
		return new model($namespace,$db);
	}

	/* Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес.
	Если задан $message, то после перенаправления будет выведено указанное сообщение */
	public static function redirect($url,$message=null) {
		if($message) $_SESSION['successMessage']=$message;
		header('Location: '.core::link($url));
		exit;
	}

	/* Прерывает выполнение скрипта и генерирует ошибку 404 */
	public static function error404() {
		include(core::path().'language/'._LANG.'.global.php');
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
		controller::$self->url[0]='error';
		$view=controller::$self->error(404);
		controller::$self->render($view,true);
		exit;
	}

	/* Выводит HTML-код сообщения об ошибке */
	public static function error($error=null) {
		if(!$error) return false;
		echo '<div class="errorMessage">'.$error.'</div>';
		return true;
	}

	/* Выводит HTML-код сообщения об успешно выполненной операции */
	public static function success($message=null) {
		if(!$message) {
			if(isset($_SESSION['successMessage'])) {
				$message=$_SESSION['successMessage'];
				unset($_SESSION['successMessage']);
			} else return false;
		}
		if(!$message) return;
		echo '<div class="successMessage">'.$message.'</div>';
	}

	/* Генерирует виджет. Обрабатывает {{widget}}
	$name - имя виджета, $options - какие-либо параметры виджета, $cacheTime - время актуальности кеша, $title - заголовок, $link - страница, для которой выводится виджет (только если процедура вызвана при обработке секции) */
	public static function widget($name,$options=null,$cacheTime=null,$title=null,$link=null) {
		if(is_string($options) && isset($options[1]) && $options[1]==':') $options=unserialize($options);
		//Нужно ли кешировать?
		$debug=core::debug();
		if($cacheTime && !$debug) {
			if(is_array($options)) {
				$f='';
				ksort($options);
				foreach($options as $index=>$value) $f.=$index.$value;
			} else $f=$options;
			$f=md5($f);
			$cacheFile=core::path().'cache/widget/'.$name.'.'.$f.'.html';
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
		include_once(core::path().'widget/'.$name.'.php');
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
		$db=core::db();
		$items=$db->fetchArray('SELECT w.name,w.data,w.cache,w.publicTitle,w.title_'._LANG.',s.url FROM section s INNER JOIN widget w ON w.id=s.widgetId WHERE s.name='.$db->escape($name).' AND s.url IN('.$query.') ORDER BY s.sort');
		$cnt=count($items);
		echo '<div class="section section'.$name.'">';
		$u=core::userCore();
		//Если пользователь админ и имеет права управления секциями, то вывести кнопки административного интерфейса
		if($u->group>=200 && ($u->group==255 || isset($u->right['section.*']))) {
			$admin=new admin();
			$admin->add('?controller=section&name='.$name,'section','Управление виджетами в этой области','Секция');
		}
		//Теперь перебрать все виджеты секции
		for($i=0;$i<$cnt;$i++) core::widget($items[$i][0],$items[$i][1],$items[$i][2],($items[$i][3]=='1' ? $items[$i][4] : null),$items[$i][5]);
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
		return '<script type="text/javascript" src="'.core::url().'public/js/'.$name.'.js"></script>';
	}

	/* Генерирует событие (прерывание).
	Параметры: 1й - системное имя события, 2й и последующие - индивидуальны для каждого события */
	public static function hook() {
		$data=func_get_args();
		$name=array_shift($data); //имя события
		$cfg=core::config('_hook');
		if(!isset($cfg[$name]) || !$cfg[$name]) return true;
		for($i=0,$cnt=count($cfg[$name]);$i<$cnt;$i++) {
			if(!self::_hook($name.'.'.$cfg[$name][$i],$data)) return false;
		}
		return true;
	}

	//Подключает файл локализации
	public static function language($name) {
		$f=core::path().'language/'._LANG.'.'.$name.'.php';
		if(!file_exists($f)) return false;
		include_once($f);
		return true;
	}

	private static function _hook($name,$data) {
		if(!include(core::path().'hook/'.$name.'.php')) return false; else return true;
	}
}
/* --- --- */



/* --- CONTROLLER ---*/
class controller {
	public $url=array(); //предназначен для сохранения запрошенного URL (в виде массива)
	protected $metaTitle='';
	protected $metaKeyword='';
	protected $metaDescription='';
	public $pageTitle=''; //отображаемый заголовок, если задан, то будет выведен в теге <H1 class="pageTitle">
	protected $view='Index'; //имя действия
	public static $error; //для хранения сообщения об ошибке, если такая случилась
	public static $self; //содержит ссылку на контроллер, чтобы предоставить к нему доступ всем желающим
	private $_head=''; //содержит теги, которые должны быть подключены в секции <head>

	public function __construct() {
		$this->url=$_GET['corePath'];
		if($this->url[1]) $this->url[1]=ucfirst($this->url[1]); else $this->url[1]='Index';
		controller::$self=&$this;
	}

	/* Служит для подключения JavaScript или других тегов в область <head> */
	protected function script($text) {
		if($text[0]!='<') $text=core::script($text);
		$this->_head.=$text;
	}

	/* Служит для подключения CSS или других тегов в область <head> */
	protected function style($text) {
		if($text[0]!='<') $text='<link type="text/css" rel="stylesheet" href="'.core::url().'public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/* Генерирует HTML-код (шаблон, теги в <head>, кнопки админки, представление)
	$view - представление (null, строка или объект); $renderTemplate - если true, то выводится контент без шаблона (для AJAX-запросов) */
	public function render($view,$renderTemplate=true) {
		core::hook('beforeRender',$renderTemplate); //сгенерировать событие ("перед началом вывода в поток")
		if(!core::template()) $renderTemplate=false; //шаблон мог быть отключен через вызов core::template()
		if(!$view) return; //если представления нет, то ничего не выводить в поток
		$user=core::userCore();
		if($user->group>=200) {
			$this->script('jquery.min');
			$this->script('admin');
			$this->style('admin');
		}
		//Вывести верхнюю часть шаблона (до "{{content}}")
		if($renderTemplate) {
			$s=core::path().'cache/template/'.core::template().'Head.php';
			if(!file_exists($s) || core::debug()) { //если кеша нет или отладочный режим, то кешировать шаблон
				core::import('core/cacheTemplate');
				cache::template(core::template());
			}
			include($s);
			if($user->group>=200) { //HTML-код всплывающего диалогового окна админки
				echo '<div id="_adminDialogBox" style="display:none;">
				<div class="_adminHead"><span>title</span><a href="#" onclick="$(\'#_adminDialogBox\').fadeOut();return false;">X</a><b>',_LANG,'</b></div>
				<img id="_adminDialogBoxLoading" src="'.core::url().'admin/public/icon/loadingBig.gif" alt="Загрузка..." />
				<iframe class="container"></iframe>
				</div>';
			}
		}
		//Вывести "общие" кнопки административного интерфейса
		if($user->group>=200) {
			$link='admin'.$this->url[1].'Link';
			if(method_exists($this,$link)) {
				$admin=new admin();
				$link=$this->$link();
				foreach($link as $item) {
					if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
				}
			}
		}
		if(controller::$error) core::error(controller::$error); //вывести сообщение об ошибке, если она произошла
		core::success(); //вывести сообщение об успехе, если ранее был редирект с сообщением
		if(gettype($view)=='object') $view->render();
		else include(core::path().'view/'.$this->url[0].$view.'.php');
		if($renderTemplate) include(core::path().'cache/template/'.core::template().'Footer.php'); //нижняя часть шаблона
	}

	/* Выводит HTML-код хлебных крошек */
	public function breadcrumb() {
		if(core::url()==$_SERVER['REQUEST_URI'] || core::url()._LANG.'/'==$_SERVER['REQUEST_URI']) return; //главная страница
		$b='breadcrumb'.$this->url[1];
		//Если метод контроллера существует, то добавить элементы, а иначе вывести просто ГЛАВНАЯ > ИМЯ_СТРАНИЦЫ
		if(method_exists($this,$b)) {
			$b=$this->$b();
			if($b===null) return;
			if($b) $b=' &raquo; '.implode(' &raquo; ',$b); else $b='';
		} else $b='';
		if($this->pageTitle) $b.=' &raquo; '.$this->pageTitle;
		if(!$b) return;
		$cfg=core::config();
		echo '<div id="breadcrumb" itemprop="breadcrumb"><a href="'.core::url().($cfg['languageDefault']!=_LANG ? _LANG.'/' : '').'" rel="nofollow">'.LNGMain.'</a>'.$b.'</div>';
	}

	/* Служебный метод, используется при провоцировании HTTP-ошибок (только 404) */
	public function error($code) {
		switch($code) {
		case '404':
			$this->pageTitle=LNGPageNotExists;
			break;
		}
		return $code;
	}

	/* Служебный метод, используется для вывода кнопок административного интерфейса */
	protected function admin($data=null) {
		$user=core::userCore();
		if($user->group<200) return;
		$s='admin'.$this->url[1].'Link2';
		$admin=new admin();
		@$link=$this->$s($data);
		foreach($link as $item) {
			if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
		}
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
		echo '<header>'.$title.'</header>';
	}
	public function adminLink() { return array(); }

	public function render($view) {
		if($view!==true) include(core::path().'view/widget'.$view.'.php');
	}

	/* Выводит HTML-код кнопок админки для ЭЛЕМЕНТА СПИСКА */
	public function admin($data) {
		$u=core::userCore();
		if($u->group<200) return;
		$admin=new admin();
		$link=$this->adminLink2($data);
		foreach($link as $item) {
			if($u->group==255 || isset($u->right[$item[0]])) $admin->render($item);
		}
	}
}
/* --- --- */



/* --- USER --- */
//Класс "пользователь" всегда находится в сессии, получить экземляр можно при помощи core::user() и core::usercore()
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
			core::import('model/user');
			$model=new modelUser($id,$this);
		}
		return $model;
	}
}
/* --- --- */



/* С этой функции начинается вся основная работа */
function runApplication($renderTemplate=true) {
	session_start();
	include(core::path().'language/'._LANG.'.global.php');
	$user=core::userCore();
	if($user->group>=200) include(core::path().'core/admin.php');
	controller::$self=new sController($_GET['corePath'][1]);
	if(!isset($_POST[controller::$self->url[0]])) { //в _POST нет данных, относящихся к запрошенному контроллеру
		if(!method_exists('sController','action'.controller::$self->url[1])) core::error404();
	} else { //в _POST есть данные, относящиеся к запрошенному контроллеру
		if(!method_exists('sController','action'.controller::$self->url[1].'Submit')) core::error404();
	}
	//Подготовить данные $_POST и $_FILES для передачи submit-действию
	if(isset($_POST[controller::$self->url[0]])) {
		if(isset($_FILES[controller::$self->url[0]])) {
			$f1=$_FILES[controller::$self->url[0]];
			foreach($f1['name'] as $name=>$value) {
				$_POST[controller::$self->url[0]][$name]=array('name'=>$value,'tmpName'=>$f1['tmp_name'][$name],'type'=>$f1['type'][$name],'size'=>$f1['size'][$name]);
			}
		}
		$s='action'.controller::$self->url[1].'Submit';
		controller::$self->$s($_POST[controller::$self->url[0]]); //запуск submit-действия, если всё хорошо, то там должен быть выполнен редирект и дальнейшая обработка прерывается
	}
	//Запуск действия (не submit) и вывод контента
	$s='action'.controller::$self->url[1];
	@$view=controller::$self->$s();
	controller::$self->render($view,$renderTemplate);
}

/* --- INITIALIZE --- */
if(isset($_SERVER['HTTP_HOST']) && substr($_SERVER['HTTP_HOST'],0,4)=='pda.') define('_CLIENT_TYPE','pda'); else define('_CLIENT_TYPE','pc');

//Поиск языка в URL-адресе
$lang=strpos($_GET['corePath'],'/');
$cfg=core::config();
if(!$lang || !isset($cfg['languageList'])) define('_LANG',$cfg['languageDefault']);
else {
	$lang=substr($_GET['corePath'],0,$lang);
	if(in_array($lang,$cfg['languageList'])) {
		define('_LANG',$lang);
		$_GET['corePath']=substr($_GET['corePath'],strlen($lang)+1);
	} else define('_LANG',$cfg['languageDefault']);
}
unset($lang);

//Обработать запрошенный URL и положить его в $_GET['corePath']
if(!isset($_GET['corePath']) || !$_GET['corePath']) $_GET['corePath']=$cfg['mainPath'];
else {
	$_link=array_flip($cfg['link']);
	$link=&$_GET['corePath'];
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
}
unset($cfg);

//Перехват if-modified-since (работает без учёта мультиязычности)
if(isset($_SERVER['HTTP_HOST'])) { //только для HTTP-запросов (не для CGI)
	$db=core::db();
	$lastModified=(int)$db->fetchValue('SELECT time FROM modified WHERE link='.$db->escape($_GET['corePath']));
	if($lastModified) {
		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T',$lastModified));
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$t=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if($t>$lastModified) {
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}
	}
}

$_GET['corePath']=explode('/',$_GET['corePath']);
if(!isset($_GET['corePath'][1])) $_GET['corePath'][1]=null; //чтобы транслятор не выдавал предупреждений (Warning)
header('Content-type:text/html; Charset=UTF-8');
?>