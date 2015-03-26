<?php
//Этот файл является частью фреймворка. Внесение изменений не рекомендуется.
error_reporting(E_ALL);
ini_set('display_errors',1);

/* ---------- CORE ------------------------------------------------------------------- */
/* Базовый класс, содержит основные статические методы */
class core {

	private static $_template='default'; //Имя используемого шаблона

	/* Меняет имя шаблона (по умолчанию "default" - /template/(pc/pda).default.html). Возвращает имя шаблона с указанием типа клиента ("pc" или "pda").
	Разумеетя должна вызываться до начала вывода контента */
	public static function template($set=null) {
		if(isset($_GET['_front'])) return 'front';
		if($set) self::$_template=$set;
//		return _CLIENT_TYPE.'.'.self::$_template;
		return self::$_template;
	}

	/* Возвращает путь к корню сайта */
	public static function path() {
		static $_path;
		if(!$_path) {
			$_path=dirname(__FILE__);
			$s=strrpos($_path,'/');
			if(!$s) $s=strrpos($_path,'\\');
			$_path=substr($_path,0,$s-5);
		}
		return $_path;
	}

	/* Возвращает true если включён отладочный режим и false в противном случае */
	public static function debug() {
		$cfg=core::configAdmin();
		if(isset($cfg['debug']) && $cfg['debug']) return true; else return false;
	}

	/* Возвращает массив, содержащий конфигурацию админки с именем $name (/admin/config/$name.php).
	Массив возвращается по ссылке, т.к. конфигурация может быть изменена при помощи класса "config" или другим способом */
	public static function &configAdmin($name='_core') {
		static $_cfg;
		if(!isset($_cfg[$name])) $_cfg[$name]=include(core::path().'admin/config/'.$name.'.php');
		return $_cfg[$name];
	}

	/* Возвращает массив, содержащий конфигурацию с именем $name (/config/$name.php).
	Массив возвращается по ссылке, т.к. конфигурация может быть изменена при помощи класса "config" или другим способом */
	public static function &config($name='_core') {
		static $_cfg;
		if(!isset($_cfg[$name])) {
			$f=core::path().'config/'.$name.'.php';
			if(file_exists($f)) $_cfg[$name]=include($f); else return null;
		}
		return $_cfg[$name];
	}

	/* Подключает указанный php-скрипт */
	public static function import($name) {
		include_once(core::path().$name.'.php');
	}

	/* Возвращает относительный URL до директория админки */
	public static function url() {
		static $_url;
		if(!$_url) {
			if(isset($_SERVER)) {
				$_url=substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')-5);
			} else $_url='/';
		}
		return $_url;
	}

	/* Возвращает класс, олицетворяющий пользователя (экземпляр 'user"). */
	public static function &user() {
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user'];
	}

	/* Возвращает "истинного" пользователя несмотря на режим подмены пользователя */
	public static function userCore() { return self::user(); }

	/* Возвращает экземпляр класса для работы с базой данных SQLite
	Если задан $newQuery, то будет открыто новое подключение */
	public static function sqlite($newQuery=false) {
		static $_sqlite;
		if(!$_sqlite) core::import('admin/core/sqlite3');
		if($newQuery) return new sqliteExt(core::path().'data/database3.db');
		if(!$_sqlite) $_sqlite=new sqliteExt(core::path().'data/database3.db');
		return $_sqlite;
	}

	/* Возвращает экземпляр класса для работы с базой данных MySQL
	Если задан $newQuery, то будет открыто новое подключение */
	public static function mysql($newQuery=false) {
		static $_mysql;
		if(!$_mysql) core::import('admin/core/mysqli');
		if($newQuery) return new mysqlExt();
		if(!$_mysql) $_mysql=new mysqlExt();
		return $_mysql;
	}

	/* Возвращает экземпляр класса для работы СУБД, указанной в настройках
	Если задан $newQuery, то будет открыто новое подключение */
	public static function db($newQuery=false) {
		static $_db;
		if(!$_db) {
			$cfg=core::config();
			if($cfg['dbDriver']=='mysql') $_db=core::mysql($newQuery); else $_db=core::sqlite($newQuery);
		}
		return $_db;
	}

	/* Возвращает группу пользователей, к которой относится текущий пользователь */
	public static function userGroup() {
		$u=core::user();
		return $u->group;
	}

	/* Возвращает права пользователя */
	public static function userRight() {
		$u=core::user();
		return $u->right;
	}

	/* Формирует относительную ссылку, служит главным образом для добавления к ссылке дополнительных параметров */
	public static function link($link) {
		if($link[0]=='/') return $link;
		if(isset($_GET['_front'])) $front='&_front'; else $front='';
		if(isset($_GET['backlink'])) $backlink='&backlink='.urlencode($_GET['backlink']); else $backlink='';
		if($link[0]=='?') return core::url().'admin/index.php'.$link.$front.$backlink;
		$link=explode('/',$link);
		$s=core::url().'admin/index.php?controller='.$link[0].$backlink;
		if(isset($link[1])) $s.='&action='.$link[1];
		return $s.$front;
	}

	/* Возвращает экземпляр класса form (конструктор HTML-форм). Если $namespace не задан, то будет использовано имя запрошенного контроллера */
	public static function form($url=null) {
		if(!class_exists('form')) include(core::path().'core/form.php');
		return new form($url);
	}

	/* Возвращает экземпляр класса table, служащего для генерации HTML-таблиц (<table>) */
	public static function table($html=null) {
		if(!class_exists('table')) include(core::path().'admin/core/html.php');
		return new table($html);
	}

	/* Возвращает экземпляр класса model (универсальная модель). $namespace - имя таблицы (если нужно), $db - имя СУБД */
	public static function model($namespace=null) {
		if(!class_exists('model')) include(core::path().'core/model.php');
		return new model($namespace);
	}

	/* Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес.
	Если задан $message, то после перенаправления будет выведено указанное сообщение */
	public static function redirect($url,$message=null) {
		if(isset($_GET['backlink'])) {
			$url=$_GET['backlink'];
			unset($_GET['backlink']);
			$message='';
		}
		if($message) {
			if(isset($_GET['_front'])) {
				echo '<div id="content">';
				self::success($message);
				echo '</div>';
				echo '<link href="'.core::url().'admin/public/template/front.css" rel="stylesheet" type="text/css" media="screen" /><script type="text/javascript" src="'.core::url().'public/js/jquery.min.js"></script>
				<script type="text/javascript" src="'.core::url().'admin/public/js/front.js"></script>
				<script type="text/javascript">setTimeout(function() { top.window.location=top.window.location.href; },2000);</script>';
				exit;
			} else $_SESSION['successMessage']=$message;
		}
		header('Location: '.core::link($url));
		exit;
	}

	/* Выполняет редирект в общедоступной части сайта */
	public static function redirectPublic($url) {
		if($url[0]!='/') $url=core::url().$url;
		if(isset($_GET['_front'])) {
			echo '<script type="text/javascript">top.document.location="'.$url.'";</script>';
			exit;
		}
		header('Location: '.$url);
		exit;
	}

	/* Прерывает выполнение скрипта и генерирует ошибку 404 */
	public static function error404() {
		if(isset($_GET['_front'])) core::error('Запрошенная страница не существует :(');
		else {
			header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
			controller::$self->url[0]='error';
			controller::$self->error(404);
			$code=404;
			controller::$self->render($code);
		}
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

	/* Возвращает HTML-тег <script src...> или пустую строку, если скрипт уже подключен. Используется чтобы избежать повторного подключение JavaScript.
	$name - имя скрипта, если скрипт находится на сайте, то указывать относительный путь от /public/js, имя должно быть без ".js" */
	public static function script($name) {
		static $_script;
		if(!$_script) $_script=array();
		if(in_array($name,$_script)) return '';
		$_script[]=$name;
		return '<script type="text/javascript" src="'.core::url().'public/js/'.$name.'.js"></script>';
	}

	public static function scriptAdmin($name) {
		static $_script;
		if(!$_script) $_script=array();
		if(in_array($name,$_script)) return '';
		$_script[]=$name;
		return '<script type="text/javascript" src="'.core::url().'admin/public/js/'.$name.'.js"></script>';
	}

	/* Генерирует событие (прерывание).
	Параметры: 1й - системное имя события, 2й и последующие - индивидуальны для каждого события */
	public static function hook() {
		$data=func_get_args();
		$name=array_shift($data);
		$cfg=core::configAdmin('_hook');
		if(!isset($cfg[$name]) || !$cfg[$name]) return true;
		for($i=0,$cnt=count($cfg[$name]);$i<$cnt;$i++) {
			if(!include(core::path().'admin/hook/'.$name.'.'.$cfg[$name][$i].'.php')) return false;
		}
		return true;
	}
}
/* --- --- */



/* --- CONTROLLER ---*/
class controller {

	public $url; //предназначен для сохранения запрошенного URL (в виде массива, содержащего два элемента)
	protected $metaTitle='';
	public $pageTitle=''; //отображаемый заголовок, если задан, то будет выведен в теге <H1 class="pageTitle">
	protected $view='Index'; //имя действия
	public static $error; //для хранения сообщения об ошибке, если такая случилась
	public static $self; //содержит ссылку на контроллер, чтобы предоставить к нему доступ всем желающим
	protected $cite=''; //краткий комментарий
	private $_button=''; //HTML код кнопок

	private $_head='';

	public function __construct() {
		$this->url=$_GET['corePath'];
		if($_GET['corePath'][1]) $this->url[1]=ucfirst($this->url[1]); else $this->url[1]='Index';
	}

	/* Служит для подключения JavaScript или других тегов в область <head> */
	public function script($text) {
		if($text[0]!='<') $text=core::script($text);
		$this->_head.=$text;
	}

	public function scriptAdmin($text) {
		if($text[0]!='<') $text=core::scriptAdmin($text);
		$this->_head.=$text;
	}

	/* Служит для подключения CSS или других тегов в область <head> */
	protected function style($text) {
		if($text[0]!='<') $text='<link type="text/css" rel="stylesheet" href="'.core::url().'admin/public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/* Добавляет кнопку в специально отведённую область
	Параметры: string $link - ссылка на страницу админки; string $image - условное имя файла изображения кнопки; string $title - всплывающая подсказка; $alt - текст тега ALT; $html - любой другой HTML-код, который будет добавлен к тегу <a> */
	protected function button($link,$image,$title='',$alt='',$html='') {
		if(strpos($link,'controller=')===false) $link='?controller='.$this->url[0].'&'.$link;
		$this->_button.='<a href="'.core::link($link).'"'.($html ? ' '.$html : '').'><img src="'.core::url().'admin/public/icon/'.$image.'32.png" alt="'.($alt ? $alt : $title).'" title="'.$title.'" /></a>';
	}

	/* Генерирует HTML-код (шаблон, теги в <head>, кнопки админки, представление)
	$view - представление (null, строка или объект); $renderTemplate - если true, то выводится контент без шаблона (для AJAX-запросов) */
	public function render($view,$renderTemplate=true) {
		if(!core::template()) $renderTemplate=false;
		if(!$view) return; //Если нет представления, то ничего не выводить
		if($renderTemplate) { //Вывести верхнюю часть шаблона
			$s=core::path().'admin/cache/template/'.core::template().'Head.php';
			if(!file_exists($s) || core::debug()) {
				core::import('core/cacheTemplate');
				cache::template(core::template());
			}
			include($s);
			if($this->pageTitle) echo '<h1 class="pageTitle">'.$this->pageTitle.'</h1>';
		} else echo $this->_head;
		if($this->_button) echo '<div class="_operation">'.$this->_button.'</div>'; //Кнопки
		if(controller::$error) core::error(controller::$error); //Сообщение об ошибке (если есть)
		core::success(); //Сообщение об успехе (если был редирект с сообщением)
		if(is_object($view)) $view->render('?controller='.$this->url[0].'&action='.$this->url[1]);
		else include(core::path().'admin/view/'.$this->url[0].$view.'.php');
		if($this->cite) echo '<cite>'.$this->cite.'</cite>'; //Поясняющий текст
		if($renderTemplate) include(core::path().'admin/cache/template/'.core::template().'Footer.php'); //Нижняя часть шаблона
		elseif(!isset($_GET['_front'])) echo '<div style="clear:both;"></div>';
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
	controller::$self=new sController($_GET['corePath'][1]);
	//Проверка прав доступа к запрошенной странице
	$user=core::user();
	if(controller::$self->url[0]!='user' || controller::$self->url[1]!='Login') {
		if($user->group<200) core::redirect('user/login');
		if($user->group!=255) {
			if(!controller::$self->right($user->right,controller::$self->url[1])) core::redirect('user/login');
		}
	}
	//Запуск submit-действия
	if(isset($_POST[controller::$self->url[0]])) {
		if(!method_exists('sController','action'.controller::$self->url[1].'Submit')) core::error404();
		$s='action'.controller::$self->url[1].'Submit';
		//Подготовить данные _POST и _FILES для передачи submit-действию
		if(isset($_FILES[controller::$self->url[0]])) {
			$f1=$_FILES[controller::$self->url[0]];
			foreach($f1['name'] as $name=>$value) {
				$_POST[controller::$self->url[0]][$name]=array('name'=>$value,'tmpName'=>$f1['tmp_name'][$name],'type'=>$f1['type'][$name],'size'=>$f1['size'][$name]);
			}
		}
		$post=$_POST[controller::$self->url[0]];
		@$data=&controller::$self->$s($post);
		//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
		if(isset($_GET['_serialize'])) {
			if(controller::$error) die(controller::$error);
			echo "OK\n";
			if(isset($post['cacheTime']) || (is_array($data) && isset($data['cacheTime']))) {
				echo $post['cacheTime'];
				if(is_array($data) && isset($data['cacheTime'])) unset($data['cacheTime']);
			} else echo '0';
			echo "\n";
			if(is_array($data)) echo serialize($data); else echo $data;
			exit;
		}
	}
	//Запуск "обычного" действия
	if(controller::$self->url[1]) {
		if(!method_exists('sController','action'.controller::$self->url[1])) core::error404();
		$s='action'.sController::$self->url[1];
		//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
		if(isset($_GET['_serialize']) && isset($_POST['data'])) {
			if(substr($_POST['data'],0,2)=='a:' && $_POST['data'][strlen($_POST['data'])-1]=='}') $view=controller::$self->$s(unserialize($_POST['data']));
			else $view=controller::$self->$s($_POST['data']);
		} else $view=controller::$self->$s();
	}
	controller::$self->render($view,$renderTemplate);
}

/* --- INITIALIZE _GET-variables --- */
header('Content-type:text/html; Charset=utf-8');

//Обработать запрошенный URL и положить его в $_GET['corePath']
if(!isset($_GET['controller'])) {
	$cfg=core::configAdmin();
	$_GET['corePath']=explode('/',$cfg['mainPath']);
	if(!isset($_GET['corePath'][1])) $_GET['corePath'][1]='Index';
} else {
	$_GET['corePath']=array($_GET['controller']);
	if(isset($_GET['action'])) $_GET['corePath'][1]=$_GET['action']; else $_GET['corePath'][1]='Index';
}
?>