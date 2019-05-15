<?php
//Этот файл является частью фреймворка. Внесение изменений не рекомендуется.

require_once(__DIR__.'/core.php');

/**
 * Предоставляет базовый API, доступный статически
 */
abstract class plushka extends \plushka\core\core {

	/**
	 * Возвращает конфигурацию, соответствующую идентификатору
	 * Конфигурация должна находиться в файле /config/{$id}.php или /admin/config/{$id}.php
	 * Конфигурация возвращается по ссылке, поэтому возможно внесение изменений "на лету". Внимание! Возможно, это поведение в будущем будет изменено.
	 * @param string $name Идентификатор (имя файла) конфигурации
	 * @param string $attribute|null Если задан, то будет возвращена не вся конфигурация, а значение отдельного атрибута $attribute
	 * @return mixed
	 */
	public static function &config($name='_core',$attribute=null) {
		static $_cfg;
		if(isset($_cfg[$name])===false) {
			if($name==='admin') $f=plushka::path().'admin/config/_core.php';
			elseif(substr($name,0,6)==='admin/') $f=plushka::path().'admin/config/'.substr($name,6).'.php';
			else return parent::config($name,$attribute);
			if(file_exists($f)===true) $_cfg[$name]=include($f); else $_cfg[$name]=null;
		}
		if($attribute===null) return $_cfg[$name];
		if(!isset($_cfg[$name][$attribute])) $value=null;
		else $value=$_cfg[$name][$attribute];
		return $value;
	}

	/**
	 * Очищает пользовательский кеш по указанному идентификатору
	 * string $id идентификатор кеша
	 */
	public static function cacheCustomClear($id) {
		$f=self::path().'cache/custom/'.$id.'.txt';
		if(file_exists($f)) return unlink($f);
		else return false;
	}

	/**
	 * Прерывает выполнение скрипта и генерирует 404-ю HTTP-ошибку
	 */
	public static function error404() {
		if(isset($_GET['_front'])===true) echo '<div class="messageError">Запрошенная страница не существует :(</div>';
		else {
			header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
			plushka::$controller->url[0]='error';
			plushka::$controller->error(404);
			$code=404;
			plushka::$controller->render($code);
		}
		exit;
	}

	/**
	 * Возвращает экземпляр расширенного класса form, предназначенного для конструирования HTML-форм
	 * Имена полей формы будут сгенерированы с учётом $namespace: $_POST[$namespace]['someAttribute']
	 * @param string|null $namespace
	 * @return \plushka\admin\core\FormEx
	 */
	public static function form($namespace=null) {
		return new \plushka\admin\core\FormEx($namespace);
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

	private static function _hook($name,$data) {
		return include(plushka::path().'admin/hook/'.$name);
	}

	/**
	 * @inheritdoc
	 * Скрипты админки должны начитаться с "admin/"
	 */
	public static function js($name,$attribute=null) {
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
	 * @param string $link ссылка в исходном формате
	 * @param bool $lang с учётом мультиязычности
	 * @param bool $domain абсолютный адрес вместо относительного
	 * @return string
	 */
	public static function link($link,$lang=true,$domain=false) {
		if(substr($link,0,7)==='http://' || substr($link,0,8)==='https://' || $link[0]==='/') return $link;
		if(substr($link,0,6)==='admin/') return self::linkAdmin(substr($link,6),$lang,$domain);
		return self::linkPublic($link,$lang,$domain);
	}

	/**
	 * Генерирует URL-адерс на страницу админки
	 * @param string $link ссылка в исходном формате
	 * @param bool $lang с учётом мультиязычности
	 * @param bool $domain абсолютный адрес вместо относительного
	 * @return string
	 */
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
	 * @return string
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

	/**
	 * Создаёт модель ActiveRecord для указанной таблицы базы данных
	 * Если файл /model/$classTable.php или /model/$classTable.php существует, то будет создан экземпляр этого класса, если нет - то экземпляр класса \plushka\core\model, ассоциированный с таблицей $classTable.
	 * @param string $classTable Имя таблицы или класса ActiveRecord
	 * @param string $db Тип СУБД и подключения, который будет использоваться при построении SQL-запросов
	 * @return \plushka\core\Model
	 */
	public static function model($classTable,$db='db') {
		if(substr($classTable,0,6)!=='admin/') return parent::model($classTable,$db);
		$class='\plushka\admin\model\\'.ucfirst(substr($classTable,6));
		if(class_exists($class)===true) return new $class();
		return new \plushka\admin\core\ModelEx($classTable,$db);
	}

	/**
	 * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
	 * @param string $url URL в исходном формате
	 * @param string|null $message Если задан, то установит текст сообщения об успешно выполненной операции
	 * @param int $code HTTP-код ответа
	 * @see \plushka::success()
	 */
	public static function redirect($url,$message=null,$code=302) {
		if(isset($_GET['backlink'])===true) {
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

	/**
	 * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
	 * @param string $url URL в формате "controller/etc"
	 */
	public static function redirectPublic($url) {
		if($url[0]!='/') $url=plushka::url().$url;
		if(isset($_GET['_front'])) {
			echo '<script>top.document.location="'.$url.'";</script>';
			exit;
		}
		header('Location: '.$url);
		exit;
	}

	/**
	 * Возвращает экземпляр класса table, служащего для генерации HTML-таблиц
	 * @param string $html произвольный HTML-код, присоединяемый к тегу <table>
	 * @return \plushka\admin\core\Table
	 */
	public static function table($html=null) {
		return new \plushka\admin\core\Table($html);
	}

	/**
	 * @inheritdoc
	 */
	public static function template($set=null) {
		if(isset($_GET['_front'])===true) return 'front';
		return parent::template($set);
	}

	/**
	 * Возвращает массив прав пользователя
	 * @return array
	 */
	public static function userRight() {
		$user=plushka::userReal();
		return $user->right;
	}

	/**
	 * Возвращает экземляр класса валидатора, предназначенного для валидации данных
	 * @param mixed[] $attribute ассоциативный массив данных для валидации
	 * @return \plushka\core\Validator
	 */
	public static function validator($attribute=null) {
		$validator=new \plushka\core\Validator();
		if($attribute) $validator->set($attribute);
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