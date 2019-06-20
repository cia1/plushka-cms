<?php
namespace plushka\core;
use plushka;

abstract class core {

	/** @var controller Через это свойство можно получить доступ к контроллеру из любой точки */
	public static $controller;

	/** @var string имя шаблона, который будет использован при генерации HTML-кода страницы */
	private static $_template='default';

	/**
	 * Возвращает конфигурацию, соответствующую идентификатору
	 * Конфигурация должна находиться в файле /config/{$id}.php
	 * Конфигурация возвращается по ссылке, поэтому возможно внесение изменений "на лету". Внимание! Возможно, это поведение в будущем будет изменено.
	 * @param string $name Идентификатор (имя файла) конфигурации
	 * @param string $attribute|null Если задан, то будет возвращена не вся конфигурация, а значение отдельного атрибута $attribute
	 * @return mixed
	 */
	public static function &config(string $name='_core',string $attribute=null) {
		static $_cfg;
		if(isset($_cfg[$name])===false) $_cfg[$name]=include(self::path().'config/'.$name.'.php');
		if($attribute===null) return $_cfg[$name];
		if(isset($_cfg[$name][$attribute])===true) $value=$_cfg[$name][$attribute]; else $value=null;
		return $value;
	}

	/**
	 * Возвращает класс mysql или sqlite, в зависимости от того, какая СУБД настроена главной.
	 * Главная СУБД определяется в /config/core.php['dbDriver'].
	 * @param bool $newQuery Если задан, то будет открыт новый SQL-запрос, использовать если нужно выполнить несколько запросов одновременно
	 * @return Mysqli|Sqlite
	 * @see plushka::sqlite()
	 * @see plushka::mysql()
	 */
	public static function db(bool $newQuery=false) {
		static $_db;
		if($newQuery===true) {
			$driver=static::config();
			$driver=$driver['dbDriver'];
			return self::$driver($newQuery);
		}
		if($_db===null) {
			$driver=self::config();
			$driver=$driver['dbDriver'];
			$_db=self::$driver($newQuery);
		}
		return $_db;
	}

	/**
	 * Проверяет включён ли режим отладки
	 * @return bool
	 */
	public static function debug(): bool {
		return self::config('_core','debug');
	}

	/**
	 * Устанавливает и возвращает текст сообщения об ошибке
	 * @param string|null $message Устанавливаемый текст сообщения
	 * @return string|null Текст сообщения или null, если ошибки не было
	 */
	public static function error(string $message=null): ?string {
		if($message===false) {
			$message=(isset($_SESSION['messageError']) ? $_SESSION['messageError'] : null);
			unset($_SESSION['messageError']);
			return $message;
		}
		if($message!==null) $_SESSION['messageError']=$message;
		return $_SESSION['messageError'] ?? null;
	}

	/**
	 * Возвращает экземпляр класса form, предназначенного для конструирования HTML-форм
	 * Имена полей формы будут сгенерированы с учётом $namespace: $_POST[$namespace]['someAttribute']
	 * @param string|null $namespace
	 * @return \plushka\core\Form
	 */
	public static function form(string $namespace=null): \plushka\core\Form {
		return new \plushka\core\Form($namespace);
	}

	/**
	 * Подключает указанный php-файл, по сути это обёртка для include_once
	 * @param string $name Имя файла относительно корня сайта
	 */
	public static function import(string $name): void {
		include_once(self::path().$name.'.php');
	}

	/**
	 * Возвращает строку, содержащую HTML-тег <script...> или пустую строку, если этот скрипт уже подключён. Если параметр $name содержит строку, начинающуюся с "LNG", то установит соответствующую константу локализации, доступную в JS через document._lang['LNGConstantName']
	 * Используется для избежания повторного включения одного скрипта JavaScript.
	 * @param string $name Абсолютное или краткое имя .js-файла или имя языковой константы (LNGxxx)
	 * @param string|null $attribute дополнительные атрибуты, которые будут добавлены к тегу <script> (например "defer")
	 * @return string
	 */
	public static function js(string $name,string $attribute=null): string {
		static $_js;
		static $_lang=false;
		if($_js===null) $_js=[];
		if(in_array($name,$_js)===true) return '';
		$_js[]=$name;
		if($name[0]==='/' || substr($name,0,7)==='http://' || substr($name,0,8)==='https://') return '<script type="text/javascript" src="'.$name.'" '.$attribute.'></script>';
		if(substr($name,0,3)==='LNG') {
			$html='<script type="text/javascript">'.
			($_lang ? '' : 'document._lang=new Array();').
			'document._lang["'.$name.'"]="'.constant($name).'";</script>';
			$_lang=true;
			return $html;
		}
		return '<script type="text/javascript" src="'.self::url().'public/js/'.$name.'.js" '.$attribute.'></script>';
	}

	/**
	 * Подключает файл локализации (/language/$name.{_LANG}.php)
	 * @param string $name Имя файла (без ".php" локализации)
	 */
	public static function language(string $name): void {
		$f=self::path().'language/'.$name.'.'._LANG.'.php';
		if(file_exists($f)===false) return;
		include_once($f);
	}

	/**
	 * Генерирует относительную или абсолютную ссылку
	 * Для CGI-режима использует /config/cgi.php для определения имени домена и базового URL
	 * @param string $link Исходная ссылка в формате controller/etc...
	 * @param bool $lang Если false, то суффикс языка не будет добавлен
	 * @param bool $domain Если true, то будет сгенерирована абсолютная ссылка
	 * @return string
	 */
	public static function link(string $link,bool $lang=true,bool $domain=false): string {
		static $_link;
		static $_main;
		if(!$link) return plushka::url($lang,$domain);
		if(substr($link,0,7)==='http://' || substr($link,0,8)==='https://' || $link[0]==='/') return $link;
		if($_link===null) {
			$cfg=self::config();
			$_link=$cfg['link'];
			$_main=$cfg['mainPath'];
		}
		if($link===$_main) return self::url($lang,$domain);
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
			if($len2===$len) $link=$_link[$s]; else $link=$_link[$s].substr($link,$len2);
		}
		return self::url($lang,$domain).$link.$end;
	}

	/**
	 * Создаёт модель ActiveRecord для указанной таблицы базы данных
	 * Если файл /model/$classTable.php существует, то будет создан экземпляр этого класса, если нет - то экземпляр класса \plushka\core\model, ассоциированный с таблицей $classTable.
	 * @param string $classTable Имя таблицы или класса ActiveRecord
	 * @param string $db Тип СУБД и подключения, который будет использоваться при построении SQL-запросов
	 * @return plushka/core/Model
	 */
	public static function model(string $classTable,string $db='db'): plushka\core\Model {
		$class='\plushka\model\\'.ucfirst($classTable);
var_dump($class);
		if(class_exists($class)===true) return new $class;
		return new \plushka\core\Model($classTable,$db);
	}

	/**
	 * Проверяет установлен ли указанный модуль
	 * @param string $id идентификатор модуля
	 * @return bool
	 */
	public static function moduleExists(string $id): bool {
		return file_exists(plushka::path().'admin/module/'.self::translit($id).'.php');
	}

	/**
	 * Возвращает экземпляр класса для работы с СУБД MySQL
	 * @param bool $newQuery Если задан, то будет открыт новый SQL-запрос, использовать если нужно выполнить несколько запросов одновременно
	 * @return mysqli
	 * @see \plushka\core\Mysqli
	 */
	public static function mysql(bool $newQuery=false): \plushka\core\Mysqli {
		static $_mysqli;
		if($newQuery===true) return new \plushka\core\Mysqli();
		if($_mysqli===null) $_mysqli=new \plushka\core\Mysqli();
		return $_mysqli;
	}

	/**
	 * Возвращает абсолютный путь до корня сайта
	 * @return string
	 */
	public static function path(): string {
		static $_path;
		if($_path===null) {
			$_path=__DIR__;
			$s=strrpos($_path,'/');
			if($s===false) $s=strrpos($_path,'\\');
			$_path=substr($_path,0,$s+1);
		}
		return $_path;
	}

	/**
	 * Возвращает экземпляр класса для работы с СУБД SQLite
	 * @param bool $newQuery Если задан, то будет открыт новый SQL-запрос, использовать если нужно выполнить несколько запросов одновременно
	 * @return sqlite
	 * @see \plushka\core\Sqlite
	 */
	public static function sqlite(bool $newQuery=false): \plushka\core\Sqlite {
		static $_sqlite;
		if($newQuery===true) return new Sqlite();
		if($_sqlite===null) $_sqlite=new Sqlite();
		return $_sqlite;
	}

	/**
	 * Устанавливает или возвращает текст сообщения об успешно выполненной операции
	 * @param string|null $message Устанавливаемый текст сообщения
	 * @return string|null Текст сообщения
	 */
	public static function success(string $message=null): ?string {
		if($message===false) {
			$message=$_SESSION['messageSuccess'];
			unset($_SESSION['messageSuccess']);
			return $message;
		}
		if($message!==null) $_SESSION['messageSuccess']=$message;
		return (isset($_SESSION['messageSuccess']) ? $_SESSION['messageSuccess'] : null);
	}

	/**
	 * Устанавливает или возврает имя шаблона, используемого при генерации HTML-кода страницы.
	 * @param string $set|null Если задан, то будет установлен указанный шаблон (соответствующий файл должен находиться в директории /template)
	 * @return string Имя текущего шаблона
	 */
	public static function template(string $set=null): string {
		if($set!==null) self::$_template=$set;
		return self::$_template;
	}

	/**
	 * Переводит строку в транслит, пригодный для использования в URL.
	 * Конвертирование происходит с учётом текущего языка локализации (const _LANG)
	 * @param string $string Исходная строка
	 * @param int $max Максимальная длина генерируемой строки
	 */
	public static function translit(string $string,int $max=60): string {
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

	/**
	 * Возвращает абсолютный или относительный URL-адрес главной страницы сайта (обычно "/")
	 * @param bool|string $lang Если указан, то к URL будет добавлен суффикс текущего языка
	 * @param bool $domain Если указан, то будет будет сгенерирована абсолютная ссылка, а не относительна
	 */
	public static function url($lang=false,bool $domain=false): string {
		static $_url;
		if($_url===null) {
			if(isset($_SERVER)===true && isset($_SERVER['DOCUMENT_ROOT'])===true && $_SERVER['DOCUMENT_ROOT']) {
				$_url=str_replace('\\','/',substr(__FILE__,strlen($_SERVER['DOCUMENT_ROOT']),-13));
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
		if($domain===true) {
			if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
				$url='://'.$_SERVER['HTTP_HOST'].$url;
			} else {
				$cfg=plushka::config('cgi');
				$url=$cfg['host'].$_url;
			}
			if(isset($_SERVER['HTTPS'])===true) {
				if($_SERVER['HTTPS']==='off' || $_SERVER['HTTPS']==='') $url='http'.$url;
				else $url='https'.$url;
			} elseif(isset($_SERVER['REQUEST_SCHEME'])) $url=$_SERVER['REQUEST_SCHEME'].$url;
			elseif(isset($_SERVER['SERVER_PORT'])===true) {
				if($_SERVER['SERVER_PORT']==443) $url='https'.$url;
				else $url='http'.$url;
			} else $url='http'.$url;
		}
		return $url;
	}

	/**
	 * Возвращает класс, олицетворяющий текущего пользователя.
	 * Для неавторизованных пользователей user::userGroup будет иметь значение "0".
	 * @return user
	 * @see plushka::userGroup()
	 * @see \plushka\core\User
	 */
	public static function &user(): \plushka\core\User {
		if(isset($_SESSION['user'])===false) $_SESSION['user']=new User();
		return $_SESSION['user'];
	}

	/**
	 * Возвращает пользователя, игнорируя режим подмены пользователя.
	 * @return user
	 * @see plushka::user()
	 * @see \plushka\core\User
	 */
	public static function &userReal(): \plushka\core\User {
		if(isset($_SESSION['userReal'])===true) return $_SESSION['userReal'];
		else return self::user();
	}

	/**
	 * Возвращает группу пользователей, к которой относится текущий пользователь:
	 * 0 - не авторизованный, 1-199 - зарегистрированные пользователи, 200-254 - администраторы, 255 - суперпользователь
	 * @return integer
	 */
	public static function userGroup(): int {
		return self::user()->group;
	}

	/**
	 * Возвращает идентификатор текущего пользователя (db user.id), для не авторизованных - 0
	 * @return integer
	 */
	public static function userId(): int {
		if(isset($_SESSION['user'])===false) $_SESSION['user']=new User();
		return $_SESSION['user']->id;
	}
}




/**
 * Базовый класс контроллера, все контроллеры должны наследоваться от него
 */
class Controller {

	/**
	 * @var string[] Хранит разобранный URL запрошенной страницы исходя из $_GET['corePath'] и правил преобразования ссылок: $url[0] - имя контроллера, $url[1] - имя действия
	 * Конструктор контроллера может изменить controller::$url[1], чтобы перенаправить запрос на нужное действие.
	 */
	public $url=[];
	/**
	 * @var string Заголовок страницы, отображаемый в HTML-теге <h1 class="pageTitle">
	 */
	public $pageTitle='';

	/**
	 * @var string|null HTML-тег <title>, если не задан будет равен self::$pageTitle
	 */
	protected $metaTitle='';
	/**
	 * @var string|null HTML-тег <meta name="keywords">, если не задан, тег не будет выводиться
	 */
	protected $metaKeyword='';
	/**
	 * @var string|null HTML-тег <meta name="description">, если не задан, тег не будет выводиться
	 */
	protected $metaDescription='';

	protected $_head=''; //содержит теги, которые должны быть подключены в секции <head>

	public function __construct() {
		$this->url=$_GET['corePath'];
		if(!$this->url[1]) $this->url[1]='index';
	}

	/**
	 * Подключает JavaScript или другой тег в HTML-область <head>. Вызов имеет смысл только в конструкторе или действиях. Защищает от повторного включения одного и того же файла
	 * @param string $text Имя .js-файла или произвольный тег в формате "<...>"
	 * @param string|null $attribute Любые атрибуты, присоединяемые к тегу <script> (например "defer")
	 * @see plushka::js()
	 */
	public function js(string $text,string $attribute=null): void {
		if($text[0]!=='<') $text=plushka::js($text,$attribute);
		$this->_head.=$text;
	}

	/**
	 * Служит для подключения CSS или других тегов в область <head>. Вызов имеет смысл только в конструкторе или действиях. В отличии от self::js() не проверяет подключён ли уже этот файл.
	 * @param string $text Имя .css-файла или произвольный тег в формате "<...>"
	 */
	protected function css(string $text): void {
		if($text[0]!=='<') $text='<link type="text/css" rel="stylesheet" href="'.plushka::url().'public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/**
	 * Рендерит шаблон и представление. Вызывать метод явно не нужно.
	 * Представлением может быть класс (должен реализовывать метод render($view)) или имя представления (файл /view/{controller}/$view.php). Если представление не задано, ничего выводиться не будет.
	 * @param object|string|bool|null $view Класс представления или имя файла представления
	 * @param bool $renderTemplate Если false, то шаблон обрабатываться не будет (полезно для AJAX-запросов)
	 */
	public function render($view,bool $renderTemplate=true): void {
		plushka::hook('beforeRender',$renderTemplate); //сгенерировать событие ("перед началом вывода в поток")
		if(!plushka::template()) $renderTemplate=false; //шаблон мог быть отключен через вызов plushka::template()
		if(!$view) return; //если представления нет, то ничего не выводить в поток
		$user=plushka::userReal();
		if($user->group>=200) {
			$this->js('jquery.min','defer');
			$this->js('admin','defer');
			$this->css('admin');
		}
		//Вывести верхнюю часть шаблона (до "{{content}}")
		$s=plushka::template();
		if($renderTemplate===true && $s) {
			$s=plushka::path().'cache/template/'.plushka::template().'Head.php';
			if(!file_exists($s) || plushka::debug()) { //если кеша нет или отладочный режим, то кешировать шаблон
				Cache::template(plushka::template());
			}
			include($s);
			if($user->group>=200) { //HTML-код всплывающего диалогового окна админки
				echo '<div id="_adminDialogBox" style="display:none;">
				<div class="_adminHead"><span>title</span><a href="#" onclick="$(\'#_adminDialogBox\').fadeOut();return false;">X</a><a href="#" onclick="return toggleFullScreen();">&#9643;</a><a class="_adminDialogBoxHelp" onclick="return $.adminDialog(this);" style="display:none;">?</a><b>',_LANG,'</b></div>
				<img id="_adminDialogBoxLoading" src="'.plushka::url().'admin/public/icon/loadingBig.gif" alt="Загрузка..." />
				<iframe class="container"></iframe>
				</div>';
			}
		}
		if($s) {
			//Вывести "общие" кнопки административного интерфейса
			if($user->group>=200) {
				$link='admin'.$this->url[1].'Link';
				if(method_exists($this,$link)===true) {
					$admin=new Admin();
					$link=$this->$link();
					foreach($link as $item) {
						if($user->group===255 || isset($user->right[$item[0]])) $admin->render($item);
					}
				}
			}
		}
		//Вывести сообщение об ошибке, если она произошла
		if(plushka::error()) {
			echo '<div class="messageError">',plushka::error(false),'</div>';
		}
		//Вывести сообщение об успехе, если оно задано
		if(plushka::success()) {
			echo '<div class="messageSuccess">',plushka::success(false),'</div>';
		}
		if(gettype($view)==='object') $view->render();
		elseif($view==='_empty') include(plushka::path().'view/_empty.php');
		else include(plushka::path().'view/'.$this->url[0].$view.'.php');
		if($renderTemplate===true && $s) include(plushka::path().'cache/template/'.plushka::template().'Footer.php'); //нижняя часть шаблона
	}

	/**
	 * Выводит HTML-код блока хлебных крошек. Вызывается фреймворком при обработке тега шаблона {{breadcrumb}}
	 */
	public function breadcrumb(): void {
		if(plushka::url()===$_SERVER['REQUEST_URI'] || plushka::url()._LANG.'/'===$_SERVER['REQUEST_URI']) return; //главная страница
		$b='breadcrumb'.$this->url[1];
		//Если метод контроллера существует, то добавить элементы, а иначе не выводить хлебные крошки
		if(method_exists($this,$b)===false) return;
		$b=$this->$b();
		if(!$b) return;
		$last=count($b)-1;
		if($b[$last]==='{{pageTitle}}') {
			if($this->pageTitle) $b[$last]=$this->pageTitle; else unset($b[$last]);
		}
		$b=' &raquo; '.implode(' &raquo; ',$b);
		$cfg=plushka::config();
		echo '<div id="breadcrumb" itemprop="breadcrumb"><a href="'.plushka::url().($cfg['languageDefault']!=_LANG ? _LANG.'/' : '').'" rel="nofollow">'.LNGMain.'</a>'.$b.'</div>';
	}

	/**
	 * Выводит HTML-код кнопок админки для элемента списка, явно вызывать метод не нужно
	 * @param mixed $data Произвольные данные, которые будут переданы в метод controller::admin{Action}Link2()
	 */
	protected function admin($data=null): void {
		$user=plushka::userReal();
		if($user->group<200) return;
		$s='admin'.$this->url[1].'Link2';
		$admin=new Admin();
		@$link=$this->$s($data);
		foreach($link as $item) {
			if($user->group==255 || isset($user->right[$item[0]])===true) $admin->render($item);
		}
	}
}




/**
 * Базовый класс виджета. Все виджеты должны быть унаследованы от этого класса
 */
abstract class Widget {

	/**
	 * @var mixed Настойки и другие данные виджета, зависит от конкретной реализации
	 */
	protected $options;
	/**
	 * @var string|null Шаблон адреса страницы, на которой публикуется виджет, если виджет вызывается из секции.
	 * Может быть нужен для некоторых виджетов. Этот адрес соответствует одной из строк в базе данных (section.url)
	 */
	protected $link;

	/**
	 * @param midex $options Настройки и любые другие данные необходимые виджету, @see self::$options
	 * @param string|null $link Шаблон адреса страницы, @see self::$link
	 */
	public function __construct($options,string $link=null) {
		$this->options=$options;
		$this->link=$link;
	}

	/**
	 * Метод запуска обработки виджета
	 * Если возвращаемое значение false или null, виджет не будет выводиться. Если true, то будут выведены только кнопки админки виджета
	 * @return object|string|bool|null Класс представления (должен реализовывать метод render()) или имя файла представления (/view/widget{Result}.php).
	 */
	abstract public function __invoke();

	/**
	 * Выводит HTML код заголовка виджета
	 * Может быть переопределён, если, к примеру, нужно вставить ссылку в заголовок.
	 * @param string $title Заголовок, заданный в админке или шаблоне
	 */
	public function title(string $title): void {
		echo '<header>',$title,'</header>';
	}

	/**
	 * Должен возвращать массив с правилами для генерации кнопок административного интерфейса
	 * @return array[]
	 */
	public function adminLink(): array {
		return array();
	}

	/**
	 * Генерирует HTML-код виджета
	 * Запускается фреймворком, если widget::__invoke() не вернул false или null. Этот метод необходим чтобы из представления был доступ к виджету через переменную $this.
	 * @param string|bool Имя файла представления
	 */
	public function render($view): void {
		if($view!==true) include(plushka::path().'view/widget'.$view.'.php');
	}

	/**
	 * Выводит HTML-код кнопок админки для элемента списка
	 * Вызывается фреймворком, явный вызов не требуется.
	 * @param array[] $data
	 */
	public function admin(array $data): void {
		$u=plushka::userReal();
		if($u->group<200) return;
		$admin=new admin();
		$link=$this->adminLink2($data);
		foreach($link as $item) {
			if($u->group==255 || isset($u->right[$item[0]])) $admin->render($item);
		}
	}
}




/**
 * Класс олицетворяет пользователя.
 * Этот класс всегда находится в сессии ($_SESSION['user'], $_SESSION['userReal'])
 * @see plushka::user()
 * @see plushka::userReal()
 * @see model/user.php
 */
class User {

	/**
	 * @var string|null $email Адрес электронной почты
	 */
	public $email;

	/**
	 * @var int $group Группа пользователя: 0 - не авторизованный, 1-199 - зарегистрированный, 200-254 - администратор, 255 - суперпользователь
	 */
	public $group=0;

	/**
	 * @var int $id Идентификатор пользователя, "0" для неавторизованных
	 */
	public $id;

	/**
	 * @var string|null $login Имя пользователя
	 */
	public $login;

	/**
	 * @param integer|null $id Если задан, то из базы данных будут загружены данные пользователя с этим идентификатором
	 */
	public function __construct(int $id=null) {
		if($id!==null) $this->model($id);
	}

	/**
	 * Возвращает ActiveRecord-модель на основе текущего пользователя.
	 * Модель будет содержать данные авторизованного пользователя. Если задан параметр $id, то соответствующий пользователь будет авторизован (используйте new \plushka\model\User(), если это нежелательное поведение).
	 * @param integer|null $id Идентификатор пользователя, которогу нужно авторизовать
	 * @return \plushka\model\User
	 */
	public function model(int $id=null): \plushka\model\User {
		static $model;
		if(isset($model)===false || $id!==null) {
			$model=new \plushka\model\User($id,$this);
		}
		return $model;
	}
}



/**
 * Исключение подключения к базе данных или исполнения SQL-запросов
 */
class DBException extends \RuntimeException {}

/**
 * Исключение 404-й HTTP-ошибки
 */
class HTTP404Exception extends \RuntimeException {} 

/**
 * Ошибка маршрутизации
 */
class RouteException extends \RuntimeException {}

/**
 * Запускает приложение
 * @param bool $renderTemplate Нужно ли обрабатывать шаблон (false для AJAX-запросов)
 */
function runApplication(bool $renderTemplate=true): void {
	session_start();
	$user=plushka::userReal();
	if($user->group>=200) include(plushka::path().'core/admin.php');
	plushka::$controller='\plushka\controller\\'.ucfirst($_GET['corePath'][0]).'Controller';
	try {
		plushka::$controller=new plushka::$controller();
		$alias=plushka::$controller->url[0];
		if(isset($_POST[$alias])===false) { //в _POST нет данных, относящихся к запрошенному контроллеру
			if(method_exists(plushka::$controller,'action'.plushka::$controller->url[1])===false) plushka::error404();
		} else { //в _POST есть данные, относящиеся к запрошенному контроллеру
			if(method_exists(plushka::$controller,'action'.plushka::$controller->url[1].'Submit')===false) plushka::error404();
		}
		//Подготовить данные $_POST и $_FILES для передачи submit-действию
		if(isset($_POST[$alias])) {
			plushka::hook('initPOST',$alias);
			if(isset($_FILES[$alias])) {
				$f1=$_FILES[$alias];
				foreach($f1['size'] as $name=>$value) {
					if(is_array($value)) {
						$_POST[$alias][$name]=array();
						foreach($value as $i=>$size) {
							if(!$size) continue;
							$_POST[$alias][$name][]=[
								'name'=>$f1['name'][$name][$i],
								'tmpName'=>$f1['tmp_name'][$name][$i],
								'type'=>$f1['type'][$name][$i],
								'size'=>$size
							];
						}
					} else {
						$_POST[$alias][$name]=[
							'name'=>$f1['name'][$name],
							'tmpName'=>$f1['tmp_name'][$name],
							'type'=>$f1['type'][$name],
							'size'=>$value
						];
					}
				}
			}

			$s='action'.plushka::$controller->url[1].'Submit';
			$data=plushka::$controller->$s($_POST[$alias]); //запуск submit-действия, если всё хорошо, то там должен быть выполнен редирект и дальнейшая обработка прерывается
		} else $data=null;
		//Запуск действия (не submit) и вывод контента
		$s='action'.plushka::$controller->url[1];
		$view=plushka::$controller->$s($data);
		plushka::$controller->render($view,$renderTemplate);
	} catch(DBException $e) {
		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
		if(plushka::debug()===true) echo '<p>',$e,'</p>';
	}
}




/* Регистрация автозагрузчика
 * Исключение добавлено для более внятного описания ошибки, на пространство имён "plushka" не должны реагировать другие загрузчики.
 */
spl_autoload_register(function($class) {
	if(substr($class,0,8)!=='plushka\\') return;
	$class=substr($class,8).'.php';
	$f=plushka::path().'override/'.$class;
	if(file_exists($f)===false) $f=plushka::path().$class;
	if(file_exists($f)===false) {
		$debug=debug_backtrace()[1];
		throw new \BadMethodCallException('Undefined class '.$class.' in '.$debug['file'].': '.$debug['line']);
	}
	require_once($f);
},true);

header('Content-type:text/html; Charset=UTF-8');