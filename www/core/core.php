<?php
namespace plushka\core;
use plushka;
/**
 * Базовый класс контроллера, все контроллеры должны наследоваться от него
 */
class Controller {
	/**
	 * @var string[] $url Хранит разобранный URL запрошенной страницы исходя из $_GET['corePath'] и правил преобразования ссылок: $url[0] - имя контроллера, $url[1] - имя действия
	 * Конструктор контроллера может изменить controller::$url[1], чтобы перенаправить запрос на нужное действие.
	 */
	public $url=array();
	/**
	 * @var string $pageTitle Заголовок страницы, отображаемый в HTML-теге <h1 class="pageTitle">
	 */
	public $pageTitle='';

	/**
	 * @var string|null HTML-тег <title>, если не задан будет равен self::$pageTitle
	 */
	protected $metaTitle='';
	/**
	 * @var string|null $metaKeyword HTML-тег <meta name="keywords">, если не задан, тег не будет выводиться
	 */
	protected $metaKeyword='';
	/**
	 * @var string|null $metaDescription HTML-тег <meta name="description">, если не задан, тег не будет выводиться
	 */
	protected $metaDescription='';

	private $_head=''; //содержит теги, которые должны быть подключены в секции <head>

	public function __construct() {
		$cfg=plushka::config();
		if(count($cfg['languageList'])>1) {
			if(_LANG==$cfg['languageDefault']) $link=$_SERVER['REQUEST_URI']; else {
				$link=substr($_SERVER['REQUEST_URI'],3);
				if(!$link) $link=plushka::url();
			}
		}
		$this->url=$_GET['corePath'];
		if(!$this->url[1]) $this->url[1]='index';
		plushka::$controller=&$this;
	}

	/**
	 * Подключает JavaScript или другой тег в HTML-область <head>. Вызов имеет смысл только в конструкторе или действиях. Защищает от повторного включения одного и того же файла
	 * @param string $text Имя .js-файла или произвольный тег в формате "<...>"
	 * @param string|null $attribute Любые атрибуты, присоединяемые к тегу <script> (например "defer")
	 * @see plushka::js()
	 */
	public function js($text,$attribute=null) {
		if($text[0]!='<') $text=plushka::js($text,$attribute);
		$this->_head.=$text;
	}

	/**
	 * Служит для подключения CSS или других тегов в область <head>. Вызов имеет смысл только в конструкторе или действиях. В отличии от self::js() не проверяет подключён ли уже этот файл.
	 * @param string $text Имя .css-файла или произвольный тег в формате "<...>"
	*/
	protected function style($text) {
		if($text[0]!='<') $text='<link type="text/css" rel="stylesheet" href="'.plushka::url().'public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/**
	 * Рендерит шаблон и представление. Вызывать метод явно не нужно.
	 * Представлением может быть класс (должен реализовывать метод render($view)) или имя представления (файл /view/{controller}/$view.php). Если представление не задано, ничего выводиться не будет.
	 * @param object|string|bool|null $view Класс представления или имя файла представления
	 * @param bool $renderTemplate Если false, то шаблон обрабатываться не будет (полезно для AJAX-запросов)
	 */
	public function render($view,$renderTemplate=true) {
		plushka::hook('beforeRender',$renderTemplate); //сгенерировать событие ("перед началом вывода в поток")
		if(!plushka::template()) $renderTemplate=false; //шаблон мог быть отключен через вызов plushka::template()
		if(!$view) return; //если представления нет, то ничего не выводить в поток
		$user=plushka::userCore();
		if($user->group>=200) {
			$this->js('jquery.min','defer');
			$this->js('admin','defer');
			$this->style('admin','defer');
		}
		//Вывести верхнюю часть шаблона (до "{{content}}")
		$s=plushka::template();
		if($renderTemplate && $s) {
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
				if(method_exists($this,$link)) {
					$admin=new admin();
					$link=$this->$link();
					foreach($link as $item) {
						if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
					}
				}
			}
		}
		//Вывести сообщение об ошибке, если она произошла
		if(plushka::error()) {
			echo '<div class="messageError">'.plushka::error(false).'</div>';
		}
		//Вывести сообщение об успехе, если оно задано
		if(isset($_SESSION['messageSuccess'])) {
			echo '<div class="messageSuccess">'.$_SESSION['messageSuccess'].'</div>';
			unset($_SESSION['messageSuccess']);
		}
		if(gettype($view)=='object') $view->render();
		elseif($view=='_empty') include(plushka::path().'view/_empty.php');
		else include(plushka::path().'view/'.$this->url[0].$view.'.php');
		if($renderTemplate && $s) include(plushka::path().'cache/template/'.plushka::template().'Footer.php'); //нижняя часть шаблона
	}

	/**
	 * Выводит HTML-код блока хлебных крошек. Вызывается фреймворком при обработке тега шаблона {{breadcrumb}}
	 */
	public function breadcrumb() {
		if(plushka::url()==$_SERVER['REQUEST_URI'] || plushka::url()._LANG.'/'==$_SERVER['REQUEST_URI']) return; //главная страница
		$b='breadcrumb'.$this->url[1];
		//Если метод контроллера существует, то добавить элементы, а иначе не выводить хлебные крошки
		if(!method_exists($this,$b)) return;
		$b=$this->$b();
		if(!$b) return;
		$last=count($b)-1;
		if($b[$last]=='{{pageTitle}}') {
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
	protected function admin($data=null) {
		$user=plushka::userCore();
		if($user->group<200) return;
		$s='admin'.$this->url[1].'Link2';
		$admin=new admin();
		@$link=$this->$s($data);
		foreach($link as $item) {
			if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
		}
	}
}




/**
 * Базовый класс виджета. Все виджеты должны быть унаследованы от этого класса
 */
abstract class Widget {

	/**
	 * Метод запуска обработки виджета
	 * Если возвращаемое значение false или null, виджет не будет выводиться. Если true, то будут выведены только кнопки админки виджета
	 * @return object|string|bool|null Класс представления (должен реализовывать метод render()) или имя файла представления (/view/widget{Result}.php).
	 */
	abstract public function __invoke();

	/**
	 * @var mixed $options Настойки и другие данные виджета, зависит от конкретной реализации
	 */
	protected $options;
	/**
	 * @var string|null $link Шаблон адреса страницы, на которой публикуется виджет, если виджет вызывается из секции (может быть нужен для некоторых виджетов). Этот адрес соответствует одной из строк в базе данных (section.url)
	 */
	protected $link;

	public function __construct($options,$link) { $this->options=$options; $this->link=$link; }

	/**
	 * Выводит HTML код заголовка виджета. Может быть переопределён, если, к примеру, нужно вставить ссылку в заголовок
	 * @param string $title Заголовок, заданный в админке или шаблоне (тег {{widget}})
	 */
	public function title($title) {
		echo '<header>'.$title.'</header>';
	}

	/**
	 * Должен возвращать массив с правилами для генерации кнопок административного интерфейса
	 * @return array[]
	 */
	public function adminLink() { return array(); }

	/**
	 * Генерирует HTML-код виджета. Запускается фреймворком, если widget::__invoke() не вернул false или null.
	 * Этот метод необходим чтобы из представления был доступ к виджету через переменную $this.
	 * @param string Имя файла представления
	 */
	public function render($view) {
		if($view!==true) include(plushka::path().'view/widget'.$view.'.php');
	}

	/**
	 * Выводит HTML-код кнопок админки для элемента списка. Вызывается фреймворком, явный вызов не требуется.
	 */
	public function admin($data) {
		$u=plushka::userCore();
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
 * Этот класс всегда находится в сессии ($_SESSION['user'], $_SESSION['userCore'])
 * @see plushka::user()
 * @see plushka::userCore()
 * @see model/user.php
 */
class User {
	/**
	 * @var int $id Идентификатор пользователя, "0" для неавторизованных
	 */
	public $id;

	/**
	 * @var string|null $login Имя пользователя
	 */
	public $login;

	/**
	 * @var string|null $email Адрес электронной почты
	 */
	public $email;

	/**
	 * @var int $group Группа пользователя: 0 - не авторизованный, 1-199 - зарегистрированный, 200-254 - администратор, 255 - суперпользователь
	 */
	public $group=0;

	/**
	 * @param int|null $id Если задан, то из базы данных будут загружены данные пользователя с этим идентификатором
	 */
	public function __construct($id=null) {
		if($id) $this->model($id);
	}

	//Возвращает модель, позволяющую управлять пользователями. Если $id задан, то модель будут загружены данные по указанному идентификатору
	/**
	 * Возвращает ActiveRecord-модель на основе текущего пользователя.
	 * Если текущий пользователь авторизован, то модель будет содержать данные этого пользователя.
	 * @param int|null $id Идентификатор пользователя. Если задан, то будут загружены соответствующие данные из базы данных, текущий пользователь будет "замещён" загруженным.
	 * @return modelUser
	 */
	public function model($id=null) {
		static $model;
		if(!isset($model) || $id!==null) {
			$model=new \plushka\model\User($id,$this);
		}
		return $model;
	}
}




/**
 * Запускает цикл обработки запроса.
 * @param bool $renderTemplate Нужно ли обрабатывать шаблон (false для AJAX-запросов)
 */
function runApplication($renderTemplate=true) {
	session_start();
	include(plushka::path().'language/global.'._LANG.'.php');
	$user=plushka::userCore();
	if($user->group>=200) include(plushka::path().'core/admin.php');
	plushka::$controller='\plushka\controller\\'.ucfirst($_GET['corePath'][0]).'Controller';
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
						$_POST[$alias][$name][]=array('name'=>$f1['name'][$name][$i],'tmpName'=>$f1['tmp_name'][$name][$i],'type'=>$f1['type'][$name][$i],'size'=>$size);
					}
				} else {
					$_POST[$alias][$name]=array('name'=>$f1['name'][$name],'tmpName'=>$f1['tmp_name'][$name],'type'=>$f1['type'][$name],'size'=>$value);
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
}