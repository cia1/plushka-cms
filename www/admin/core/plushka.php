<?php
//Этот файл является частью фреймворка. Внесение изменений не рекомендуется.
namespace plushka\admin\core;
require_once __DIR__.'/core.php';
use plushka\core\core;
use plushka\core\Form;
use plushka\core\Model;
use plushka\core\Validator;
use plushka\core\Widget;

/**
 * Предоставляет базовый API, доступный статически
 */
abstract class plushka extends core {

    /** @var Controller Ссылка на контроллер для доступа из вне */
    public static $controller;

	/**
	 * Очищает пользовательский кеш
	 * @param string $id Идентификатор кеша
	 */
	public static function cacheCustomClear(string $id): void {
		$f=self::path().'cache/custom/'.$id.'.txt';
		if(file_exists($f)===true) unlink($f);
	}

	/**
	 * @inheritdoc
	 * Конфигурация должна находиться в файле /config/{$id}.php или /admin/config/{$id}.php
	 */
	public static function &config(string $name='_core',string $attribute=null) {
		static $_cfg;
		if(isset($_cfg[$name])===false) {
			if($name==='admin') $f=plushka::path().'admin/config/_core.php';
			elseif(substr($name,0,6)==='admin/') $f=plushka::path().'admin/config/'.substr($name,6).'.php';
			else return parent::config($name,$attribute);
			if(file_exists($f)===true) {
			    /** @noinspection PhpIncludeInspection */
			    $_cfg[$name]=include($f);
            } else $_cfg[$name]=null;
		}
		if($attribute===null) return $_cfg[$name];
		if(!isset($_cfg[$name][$attribute])) $value=null;
		else $value=$_cfg[$name][$attribute];
		return $value;
	}

	/**
	 * Прерывает выполнение скрипта и генерирует 404-ю HTTP-ошибку
	 */
	public static function error404(): void {
		if(isset($_GET['_front'])===true) echo '<div class="messageError">Запрошенная страница не существует :(</div>';
		else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			plushka::$controller->url[0]='error';
			plushka::$controller->render('404',true);
		}
		exit;
	}

	/**
	 * @inheritDoc
	 */
	public static function form(string $namespace=null): Form {
		return new FormEx($namespace);
	}

	/**
	 * Генерирует событие
	 * Обработчики события - это файлы /hook/$name.{module}.php
	 * @param string $name Имя события (файлы )
	 * @param mixed ...$data Произвольные данные, которые будут доступны в обработчике события
	 * @return mixed|bool False, если хотя бы один обработчик вернул false, иначе массив значений, возвращённых обработчиками событий
	 */
	public static function hook(string $name,...$data) {
		$d=opendir(plushka::path().'admin/hook');
		$result=array();
		$len=strlen($name);
		while($f=readdir($d)) {
			if($f==='.' || $f==='..') continue;
			if(substr($f,0,$len)!==$name) continue;
			$tmp=self::_hook($f,$data);
			if($tmp===false) {
				closedir($d);
				return false;
			} elseif($tmp!==null) $result[]=$tmp;
		}
		closedir($d);
		return $result;
	}

	/**
	 * @inheritdoc
	 * Скрипты админки должны начитаться с "admin/"
	 */
	public static function js(string $name,string $attribute=null): string {
		static $_js;
		if(substr($name,0,6)==='admin/') {
			if($_js===null) $_js=[];
			if(in_array($name,$_js)===true) return '';
			$_js[]=$name;
			return '<script type="text/javascript" src="'.self::url().'admin/public/js/'.$name.'.js" '.$attribute.'></script>';
		}
		return parent::js($name,$attribute);
	}

	/**
	 * Генерирует URL-адерс на страницу админки или публичную часть
	 * @inheritDoc
	 * @param string $link ссылка в исходном формате
	 * @param bool $lang с учётом мультиязычности
	 * @param bool $domain абсолютный адрес вместо относительного
	 * @return string
	 */
	public static function link(string $link,bool $lang=true,bool $domain=false): string {
		if(substr($link,0,7)==='http://' || substr($link,0,8)==='https://' || $link[0]==='/') return $link;
		if(substr($link,0,6)==='admin/') return self::linkAdmin(substr($link,6),$lang,$domain);
		return parent::link($link,$lang,$domain);
	}

	/**
	 * Генерирует URL-адерс на страницу админки
	 * @param string $link ссылка в исходном формате
	 * @param bool $lang с учётом мультиязычности
	 * @param bool $domain абсолютный адрес вместо относительного
	 * @return string
	 */
	public static function linkAdmin(string $link,bool $lang=true,bool $domain=false): string {
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
	 * @inheritDoc
	 * Если файл /model/$classTable.php или /admin/model/$classTable.php существует, то будет создан экземпляр этого класса, если нет - то экземпляр класса \plushka\core\model, ассоциированный с таблицей $classTable.
	 */
	public static function model(string $classTable,string $db='db'): Model {
		if(substr($classTable,0,6)!=='admin/') return parent::model($classTable,$db);
		$class='\plushka\admin\model\\'.ucfirst(substr($classTable,6));
		if(class_exists($class)===true) return new $class();
		return new ModelEx($classTable,$db);
	}

	/**
	 * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
	 * @param string $url URL в исходном формате
	 * @param string|null $message Если задан, то установит текст сообщения об успешно выполненной операции
	 * @param int $code HTTP-код ответа
	 * @see plushka::success()
	 */
	public static function redirect(string $url,string $message=null,int $code=302): void {
		if(isset($_GET['backlink'])===true) {
			$url=$_GET['backlink'];
			unset($_GET['backlink']);
			$message='';
		}
		if($message!==null) plushka::success($message);
		if($message!==null || plushka::success()) {
			if(isset($_GET['_front'])===true) {
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

	/**
	 * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
	 * @param string $url URL в формате "controller/etc"
	 */
	public static function redirectPublic(string $url): void {
		if($url[0]!=='/') $url=plushka::url().$url;
		if(isset($_GET['_front'])===true) {
			echo '<script>top.document.location="'.$url.'";</script>';
			exit;
		}
		header('Location: '.$url);
		exit;
	}

	/**
	 * Возвращает экземпляр класса table, служащего для генерации HTML-таблиц
	 * @param string $html произвольный HTML-код, присоединяемый к тегу <table>
	 * @return Table
	 */
	public static function table(string $html=null): Table {
		return new Table($html);
	}

	/**
	 * @inheritdoc
	 */
	public static function template(string $set=null): string {
		if(isset($_GET['_front'])===true) return 'front';
		return parent::template($set);
	}

	/**
	 * Возвращает массив прав пользователя
	 * @return array
	 */
	public static function userRight(): array {
		$user=plushka::userReal();
		return $user->right;
	}

	/**
	 * Возвращает экземляр класса валидатора, предназначенного для валидации данных
	 * @param mixed[] $attribute ассоциативный массив данных для валидации
	 * @return Validator
	 */
	public static function validator($attribute=null): Validator {
		$validator=new Validator();
		if($attribute!==null) $validator->set($attribute);
		return $validator;
	}

	/**
	 * Создаёт и рендерит виджет. Этот метод обрабатывает теги {{widget}}
	 * @param string $name Имя виджета, соответствует файлу /widget/$name.php
	 * @param mixed $options Произвольные параметры, которые будут переданы виджету
	 * @param int|null $cacheTime Время актуальности кэша. Если null, то виджет кэшироваться не будет
	 * @param string|null $title Заголовок виджета
	 * @param string|null $link Шаблон адреса страницы, на которой публикуется виджет, если виджет вызывается из секции (может быть нужен для некоторых виджетов). Этот адрес соответствует одной из строк в базе данных (section.url)
	 * @see \plushka\core\Widget
	 * @see plushka::section
	 */
	public static function widget(string $name,$options=null,int $cacheTime=null,string $title=null,string $link=null): void {
		if(is_string($options)===true && isset($options[1])===true && $options[1]===':') $options=unserialize($options);
		//Нужно ли кешировать?
		if($cacheTime>0 && self::debug()===false) {
			if(is_array($options)===true) {
				$f='';
				ksort($options);
				foreach($options as $index=>$value) $f.=$index.$value;
			} else $f=$options;
			$f=md5($f);
			$cacheFile=plushka::path().'admin/cache/widget/'.$name.'.'.$f.'.html';
			if(file_exists($cacheFile)) {
				$f=filemtime($cacheFile)+$cacheTime*60;
				if($f>time()) {
				    /** @noinspection PhpIncludeInspection */
					include($cacheFile);
					return;
				}
			}
			ob_start();
		} else $cacheFile=null;
		$f='widget'.$name;
		/** @noinspection PhpIncludeInspection */
		include_once(plushka::path().'widget/'.$name.'.php');
		/** @var Widget $w */
		$w=new $f($options,$link);
		$view=$w();
		if($view!==null && $view!==false) { //Если widget() вернул null или false, то выводить HTML-код ненужно (виджет может выводиться только при определённых условиях)
			echo '<section class="widget'.$name.'">';
			//Если пользователь является администратором, то вывести элементы управления в соответствии его правам
			if($title) $w->title($title); //Вывод заголовка
			if(is_object($view)) $view->render(); else $w->render($view);
			echo '<div style="clear:both;"></div></section>';
		}
		if($cacheFile!==null) {
			$f=fopen($cacheFile,'w');
			fwrite($f,ob_get_contents());
			fclose($f);
			ob_end_flush();
		}
	}

	private static function _hook(string $name,/** @noinspection PhpUnusedParameterInspection */$data) {
	    /** @noinspection PhpIncludeInspection */
		return include(plushka::path().'admin/hook/'.$name);
	}

}




/* --- INITIALIZE --- */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


if(isset($_GET['controller'])===false) {
	$uri=explode('/',plushka::config('admin','mainPath'));
	$_GET['controller']=$uri[0];
	if(isset($uri[1])===true) $_GET['action']=$uri[1];
	unset($cfg,$ui);
}
if(isset($_GET['action'])===false) $_GET['action']=null;
if(isset($_GET['lang'])===true) define('_LANG',$_GET['lang']);
else define('_LANG',plushka::config('_core','languageDefault'));
$_GET['corePath']=[$_GET['controller'],$_GET['action']];
