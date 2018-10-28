<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.

/**
 * Предоставляет базовый API, доступный статически
 */
class core {

	/** @var string имя шаблона, который будет использован при генерации HTML-кода страницы */
	private static $_template='default';

	/**
	 * Проверяет установлен ли указанный модуль
	 * @param string $id идентификатор модуля
	 * @return bool
	 */
	public static function moduleExists($id) {
		$f=core::path().'admin/module/'.self::translit($id).'.php';
		return file_exists($f);
	}

	/**
	 * Переводит строку в транслит, пригодный для использования в URL.
	 * Конвертирование происходит с учётом текущего языка локализации (const _LANG)
	 * @param string $string Исходная строка
	 * @param int $max Максимальная длина генерируемой строки
	 */
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
	/**
	 * Задаёт или возврает шаблон, используемый при генерации HTML-кода страницы.
	 * Вызов метода имеет смысл только до выхода из действия контроллера
	 * @param string $set|null Если задан, то будет установлен указанный шаблон (соответствующий файл должен находиться в директории /template, const _CLIENT_TYPE - тип клиента ("pc" или "pda"))
	 * @return string Имя текущего шаблона
	 */
	public static function template($set=null) {
		if($set!==null) self::$_template=$set;
		return (self::$_template ? _CLIENT_TYPE.'.'.self::$_template : false);
	}

	/**
	 * Возвращает абсолютный путь до корня сайта
	 * @return string
	 */
	public static function path() {
		static $_path;
		if(!$_path) {
			$_path=__DIR__;
			$s=strrpos($_path,'/');
			if(!$s) $s=strrpos($_path,'\\');
			$_path=substr($_path,0,$s+1);
		}
		return $_path;
	}

	/**
	 * Проверяет включён ли режим отладки
	 * @return bool
	 */
	public static function debug() {
		$cfg=self::config();
		if(isset($cfg['debug']) && $cfg['debug']) return true; else return false;
	}

	/**
	 * Возвращает конфигурацию, соответствующую идентификатору
	 * Конфигурация должна находиться в файле /config/{$id}.php
	 * Конфигурация возвращается по ссылке, поэтому возможно внесение изменений "на лету". Внимание! Возможно, это поведение в будущем будет изменено.
	 * @param string $name Идентификатор (имя файла) конфигурации
	 * @param string $attribute|null Если задан, то будет возвращена не вся конфигурация, а значение отдельного атрибута $attribute
	 * @return array|mixed
	 */
	public static function &config($name='_core',$attribute=null) {
		static $_cfg;
		if(!isset($_cfg[$name])) $_cfg[$name]=include(self::path().'config/'.$name.'.php');
		if($attribute===null) return $_cfg[$name];
		if(isset($_cfg[$name][$attribute])===true) $value=$_cfg[$name][$attribute]; else $value=null;
		return $value;
	}

	/**
	 * Подключает указанный php-файл, по сути это обёртка для include_once
	 * @param string $name Имя файла относительно корня сайта
	 */
	public static function import($name) {
		include_once(self::path().$name.'.php');
	}

	/**
	 * Возвращает абсолютный или относительный URL-адрес главной страницы сайта (обычно "/")
	 * @param bool $lang Если указан, то к URL будет добавлен суффикс текущего языка
	 * @param bool $domain Если указан, то будет будет сгенерирована абсолютная ссылка, а не относительна
	 */
	public static function url($lang=false,$domain=false) {
		static $_url;
		if(!$_url) {
			if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
				$_url=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
			} else { //CGI-запрос
				$cfg=core::config('cgi');
				$_url=$cfg['url'];
			}
		}
		$url=$_url;
		if($lang===true) {
			$cfg=core::config();
			if($cfg['languageDefault']!=_LANG) $url.=_LANG.'/';
		} elseif($lang) {
			$cfg=core::config();
			if($cfg['languageDefault']!=$lang) $url.=$lang.'/';
		}
		if($domain) {
			if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
				$url='://'.$_SERVER['HTTP_HOST'].$url;
			} else {
				$cfg=core::config('cgi');
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

	/**
	 * Возвращает класс, олицетворяющий текущего пользователя.
	 * Для неавторизованных пользователей user::userGroup будет иметь значение "0".
	 * @return user
	 * @see core::userGroup()
	 * @see core/user
	 */
	public static function &user() {
		if(isset($_SESSION['userCore'])) return $_SESSION['userCore'];
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user'];
	}

	/**
	 * Возвращает пользователя, игнорируя режим подмены пользователя.
	 * @return user
	 * @see core::user()
	 * @see core/user
	 */
	public static function &userCore() {
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user'];
	}

	/**
	 * Возвращает произвольные данные из кэша
	 * Если кэш не существует или устарел и не задан параметр $callback, то будет возвращено NULL
	 * @param string $id Идентификатор кэша
	 * @param callback|null $callback Callback-функция, которая будет вызвана если кэш не существует или устарел.
	 * @param int $timeout Время в минутах актуальности кэша
	 * @return mixed|null
	 */
	public static function cache($id,$callback=null,$timeout=-1) {
		if($timeout) {
			$cfg=self::config();
			if($cfg['debug']) $timeout=0;
		}
		$f=self::path().'cache/custom/'.$id.'.txt';
		if(file_exists($f)) {
			if($timeout===-1 || !$callback) return unserialize(file_get_contents($f));
			if(time()-filemtime($f)<$timeout*60) return unserialize(file_get_contents($f));
		}
		if(!$callback) return null;
		$data=call_user_func($callback);
		$f=fopen($f,'w');
		fwrite($f,serialize($data));
		fclose($f);
		return $data;
	}

	/**
	 * Возвращает экземпляр класса для работы с СУБД SQLite
	 * @param bool $newQuery Если задан, то будет открыт новый SQL-запрос, использовать если нужно выполнить несколько запросов одновременно
	 * @return sqlite
	 * @see core/sqlite
	 */
	public static function sqlite($newQuery=false) {
		static $_sqlite;
		if(!$_sqlite) self::import('core/sqlite3');
		if($newQuery) return new sqlite();
		if(!$_sqlite) $_sqlite=new sqlite();
		return $_sqlite;
	}

	/**
	 * Возвращает экземпляр класса для работы с СУБД MySQL
	 * @param bool $newQuery Если задан, то будет открыт новый SQL-запрос, использовать если нужно выполнить несколько запросов одновременно
	 * @return mysql
	 * @see core/mysql
	 */
	public static function mysql($newQuery=false) {
		static $_mysql;
		if(!$_mysql) self::import('core/mysqli');
		if($newQuery) return new mysql();
		if(!$_mysql) $_mysql=new mysql();
		return $_mysql;
	}

	/**
	 * Возвращает класс mysql или sqlite, в зависимости от того, какая СУБД настроена главной.
	 * Главная СУБД определяется в /config/core.php['dbDriver'].
	 * @param bool $newQuery Если задан, то будет открыт новый SQL-запрос, использовать если нужно выполнить несколько запросов одновременно
	 * @return mysql|sqlite
	 * @see core::sqlite()
	 * @see core::mysql()
	 */
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

	/**
	 * Возвращает идентификатор текущего пользователя (db user.id), для не авторизованных - "0"
	 * @return int
	 */
	public static function userId() {
		if(isset($_SESSION['userCore'])) return $_SESSION['userCore']->id;
		if(!isset($_SESSION['user'])) $_SESSION['user']=new user();
		return $_SESSION['user']->id;
	}

	/**
	 * Возвращает группу пользователей, к которой относится текущий пользователь:
	 * 0 - не авторизованный, 1-199 - зарегистрированные пользователи, 200-254 - администраторы, 255 - суперпользователь
	 * @return int
	 */
	public static function userGroup() {
		$u=self::user();
		return $u->group;
	}

	/**
	 * Генерирует относительную или абсолютную ссылку
	 * Для CGI-режима использует /config/cgi.php для определения имени домена и базового URL
	 * @param string $link Исходная ссылка в формате controller/etc...
	 * @param bool $lang Если false, то суффикс языка не будет добавлен
	 * @param bool $domain Если true, то будет сгенерирована абсолютная ссылка
	 */
	public static function link($link,$lang=true,$domain=false) {
		static $_link;
		static $_main;
		if(!$link) return core::url($lang,$domain);
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
	 * Возвращает экземпляр класса form, предназначенного для конструирования HTML-форм
	 * Имена полей формы будут сгенерированы с учётом $namespace: $_POST[$namespace]['someAttribute']
	 * @param string|null $namespace
	 * @return form
	 */
	public static function form($namespace=null) {
		if(!class_exists('form')) include(self::path().'core/form.php');
		return new form($namespace);
	}

	/**
	 * Возвращает экземпляр класса validator, реализующего валидацию входных данных
	 * @param array|null $attribute Ассоциативный массив, содержащий данные для валидации
	 * @return validator
	 */
	public static function validator($attribute=null) {
		core::import('core/validator');
		$validator=new $validator();
		if($attribute) $validator->set($attribute);
		return $validator;
	}

	/**
	 * Создаёт модель ActiveRecord для указанной таблицы базы данных
	 * Если файл /model/$classTable.php существует, то будет создан экземпляр этого класса, если нет - то экземпляр класса core/model, ассоциированный с таблицей $classTable.
	 * @param string $classTable Имя таблицы или класса ActiveRecord
	 * @param string $db Тип СУБД и подключения, который будет использоваться при построении SQL-запросов
	 * @return model
	 */
	public static function model($classTable,$db='db') {
		$f=core::path().'model/'.$table.'.php';
		if(file_exists($f)) {
			include($f);
			return new $table();
		}
		if(!class_exists('model')) include(self::path().'core/model.php');
		return new model($table,$db);
	}

	/**
	 * Устанавливает или возвращает текст сообщения об успешно выполненной операции
	 * @param string|null $message Устанавливаемый текст сообщения
	 * @return string|null Текст сообщения
	 */
	public static function success($message=null) {
		if($message===false) {
			$message=$_SESSION['messageSuccess'];
			unset($_SESSION['messageSuccess']);
			return $message;
		}
		if($message!==null) $_SESSION['messageSuccess']=$message;
		return (isset($_SESSION['messageSuccess']) ? $_SESSION['messageSuccess'] : null);
	}

	/**
	 * Устанавливает и возвращает текст сообщения об ошибке
	 * @param string|null $message Устанавливаемый текст сообщения
	 * @return string|null Текст сообщения или null, если ошибки не было
	 */
	public static function error($message=null) {
		if($message===false) {
			$message=(isset($_SESSION['messageError']) ? $_SESSION['messageError'] : null);
			unset($_SESSION['messageError']);
			return $message;
		}
		if($message!==null) $_SESSION['messageError']=$message;
		return (isset($_SESSION['messageError']) ? $_SESSION['messageError'] : null);
	}

	/**
	 * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
	 * @param string $url URL в формате "controller/etc"
	 * @param string|null $message Если задан, то установит текст сообщения об успешно выполненной операции
	 * @param int $code HTTP-код ответа
	 * @see core:success()
	 */
	public static function redirect($url,$message=null,$code=302) {
		if($message) core::success($message);
		header('Location: '.self::link($url),true,$code);
		exit;
	}

	/**
	 * Прерывает выполнение скрипта и генерирует 404-ю HTTP-ошибку
	 */
	public static function error404() {
		core::hook('404');
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
		core::language('global');
		core::language('error');
		controller::$self->url[0]='error';
		controller::$self->pageTitle=LNGPageNotExists;
		controller::$self->render('404',true);
		exit;
	}

	/**
	 * Создаёт и рендерит виджет. Этот метод обрабатывает теги {{widget}}
	 * @param string $name Имя виджета, соответствует файлу /widget/$name.php
	 * @param mixed $options Произвольные параметры, которые будут переданы виджету
	 * @param int|null $cacheTime Время актуальности кэша. Если null, то виджет кэшироваться не будет
	 * @param string|null $title Заголовок виджета
	 * @param string|null $link Шаблон адреса страницы, на которой публикуется виджет, если виджет вызывается из секции (может быть нужен для некоторых виджетов). Этот адрес соответствует одной из строк в базе данных (section.url)
	 * @see core/widget
	 * @see core/section
	 */
	public static function widget($name,$options=null,$cacheTime=null,$title=null,$link=null) {
		if(is_array($options) && isset($options['cssClass'])) {
			$cssClass=' '.$options['cssClass'];
			unset($options['cssClass']);
		} else $cssClass='';
		if(is_string($options) && isset($options[1]) && $options[1]==':') $options=unserialize($options);
		elseif(is_array($options) && count($options)==1 && isset($options['_content'])) $options=$options['_content'];
		//Нужно ли кешировать?
		if($cacheTime && self::debug()) $cacheTime=false;

		if($cacheTime) {
			if(is_array($options)) {
				$f='';
				ksort($options);
				foreach($options as $index=>$value) $f.=$index.$value;
			} else $f=$options;
			$f=md5($f);
			$cacheFile=self::path().'cache/widget/'.$name.'.'.$f;
			$content=$cacheFile.'.html';
			if(file_exists($content)) {
				$f=filemtime($content)+$cacheTime*60;
				if($f>time()) {
					echo '<section class="widget'.$name.$cssClass.'">';
					$cacheFile.='.json';
					if(file_exists($cacheFile)) self::_widgetAdmin(json_decode(file_get_contents($cacheFile),true));
					$content=file_get_contents($content);
					echo $content;
					echo '<div style="clear:both;"></div></section>';
					return;
				}
			}
		}

		$f=self::path().'override/widget/'.$name.'.php';
		if(file_exists($f)) include_once($f);
		else include_once(self::path().'widget/'.$name.'.php');
		$f='widget'.$name;
		$w=new $f($options,$link);
		$w->cssClass=$cssClass;
		$view=$w();
		if($view!==null && $view!==false) { //Если widget() вернул null или false, то выводить HTML-код ненужно (виджет может выводиться только при определённых условиях)
			echo '<section class="widget'.$name.$cssClass.'">';
			//Если пользователь является администратором, то вывести элементы управления в соответствии его правам
			$cacheAdmin=self::_widgetAdmin($w,true);
			if($cacheTime) ob_start();
			if($title) $w->title($title); //вывод заголовка
			if(is_object($view)) $view->render(); else $w->render($view);
		}
		if($cacheTime) {
			$f=fopen($cacheFile.'.html','w');
			fwrite($f,ob_get_flush());
			fclose($f);
			if($cacheAdmin!==false) {
				$f=fopen($cacheFile.'.json','w');
				fwrite($f,json_encode($cacheAdmin));
				fclose($f);
			}
		}
		echo '<div style="clear:both;"></div></section>';
	}

	//Выводит кнопки админки для виджета и возвращает кеш, если это необходимо.
	//$data - экземпляр виджета или массив ссылок
	private static function _widgetAdmin($data,$cache=false) {
		$user=core::userCore();
		if($user->group<200) return false;
		$admin=new admin();
		if(is_object($data)) $data=$data->adminLink();
		foreach($data as $item) {
			if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
		}
		return ($cache ? $data : null);
	}

	/**
	 * Генерирует и публикует HTML-код секции. Этот метод обрабатывает теги {{section}}
	 * @param string $name Имя секции
	 */
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
		$items=$db->fetchArray('SELECT w.name,w.data,w.cache,w.publicTitle,w.title_'._LANG.',s.url,w.groupId,w.cssClass FROM section s INNER JOIN widget w ON w.id=s.widgetId WHERE s.name='.$db->escape($name).' AND s.url IN('.$query.') ORDER BY s.sort');
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
			$options=$items[$i][1];
			if($items[$i][7]) {
				if(isset($options[1]) && $options[1]==':') {
					$options=unserialize($options);
				} else $options=array('_content'=>$options);
				$options['cssClass']=$items[$i][7];
			}
			core::widget($items[$i][0],$options,$items[$i][2],($items[$i][3]=='1' ? $items[$i][4] : null),$items[$i][5]);
		}
		echo '</div>';
	}

	/**
	 * Возвращает строку, содержащую HTML-тег <script...> или пустую строку, если этот скрипт уже подключён. Если параметр $name содержит строку, начинающуюся с "LNG", то установит соответствующую константу локализации, доступную в JS через document._lang['LNGConstantName']
	 * Используется для избежания повторного включения одного скрипта JavaScript.
	 * @param string $name Абсолютное или краткое имя .js-файла или имя языковой константы (LNGxxx)
	 * @param string|null $attribute дополнительные атрибуты, которые будут добавлены к тегу <script> (например "defer")
	 * @return string
	 */
	public static function js($name,$attribute=null) {
		static $_js;
		static $_lang=false;
		if(!$_js) $_js=array();
		if(isset($_js[$name])) return '';
		$_js[$name]=true;
		if($name[0]=='/' || substr($name,0,7)=='http://' || substr($name,0,8)=='https://') return '<script type="text/javascript" src="'.$name.'" '.$attribute.'></script>';
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
	 * Генерирует событие
	 * Обработчики события - это файлы /hook/$name.{module}.php
	 * @param string $name Имя события (файлы )
	 * @param mixed ...$data Произвольные данные, которые будут доступны в обработчике события
	 * @return mixed|false False, если хотя бы один обработчик вернул false, иначе массив значений, возвращённых обработчиками событий
	 */
	public static function hook($name,...$data) {
		$d=opendir(core::path().'hook');
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

	/**
	 * Подключает файл локализации (/language/$name.{_LANG}.php)
	 * @param string $name Имя файла (без ".php" локализации)
	 * @return bool Существует ли файл локализации
	 */
	public static function language($name) {
		$f=self::path().'language/'.$name.'.'._LANG.'.php';
		if(file_exists($f)===false) return false;
		include_once($f);
		return true;
	}

	private static function _hook($name,$data) {
		$result=include(self::path().'hook/'.$name);
		return $result;
	}
}




/**
 * Базовый класс контроллера, все контроллеры должны наследоваться от него
 */
class controller {

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
	 * @var controller $self Ссылка на экземпляр контроллера, через эту переменную можно получить доступ к контроллеру за его пределами (например из виджета или шаблона)
	 */
	public static $self;

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
		$cfg=core::config();
		if(count($cfg['languageList'])>1) {
			if(_LANG==$cfg['languageDefault']) $link=$_SERVER['REQUEST_URI']; else {
				$link=substr($_SERVER['REQUEST_URI'],3);
				if(!$link) $link=core::url();
			}
		}
		$this->url=$_GET['corePath'];
		if(!$this->url[1]) $this->url[1]='index';
		controller::$self=&$this;
	}

	/**
	 * Подключает JavaScript или другой тег в HTML-область <head>. Вызов имеет смысл только в конструкторе или действиях. Защищает от повторного включения одного и того же файла
	 * @param string $text Имя .js-файла или произвольный тег в формате "<...>"
	 * @param string|null $attribute Любые атрибуты, присоединяемые к тегу <script> (например "defer")
	 * @see core::js()
	 */
	public function js($text,$attribute=null) {
		if($text[0]!='<') $text=core::js($text,$attribute);
		$this->_head.=$text;
	}

	/**
	 * Служит для подключения CSS или других тегов в область <head>. Вызов имеет смысл только в конструкторе или действиях. В отличии от self::js() не проверяет подключён ли уже этот файл.
	 * @param string $text Имя .css-файла или произвольный тег в формате "<...>"
	*/
	protected function style($text) {
		if($text[0]!='<') $text='<link type="text/css" rel="stylesheet" href="'.core::url().'public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/**
	 * Рендерит шаблон и представление. Вызывать метод явно не нужно.
	 * Представлением может быть класс (должен реализовывать метод render($view)) или имя представления (файл /view/{controller}/$view.php). Если представление не задано, ничего выводиться не будет.
	 * @param object|string|bool|null $view Класс представления или имя файла представления
	 * @param bool $renderTemplate Если false, то шаблон обрабатываться не будет (полезно для AJAX-запросов)
	 */
	public function render($view,$renderTemplate=true) {
		core::hook('beforeRender',$renderTemplate); //сгенерировать событие ("перед началом вывода в поток")
		if(!core::template()) $renderTemplate=false; //шаблон мог быть отключен через вызов core::template()
		if(!$view) return; //если представления нет, то ничего не выводить в поток
		$user=core::userCore();
		if($user->group>=200) {
			$this->js('jquery.min','defer');
			$this->js('admin','defer');
			$this->style('admin','defer');
		}
		//Вывести верхнюю часть шаблона (до "{{content}}")
		$s=core::template();
		if($renderTemplate && $s) {
			$s=core::path().'cache/template/'.core::template().'Head.php';
			if(!file_exists($s) || core::debug()) { //если кеша нет или отладочный режим, то кешировать шаблон
				core::import('core/cache');
				cache::template(core::template());
			}
			include($s);
			if($user->group>=200) { //HTML-код всплывающего диалогового окна админки
				echo '<div id="_adminDialogBox" style="display:none;">
				<div class="_adminHead"><span>title</span><a href="#" onclick="$(\'#_adminDialogBox\').fadeOut();return false;">X</a><a href="#" onclick="return toggleFullScreen();">&#9643;</a><a class="_adminDialogBoxHelp" onclick="return $.adminDialog(this);" style="display:none;">?</a><b>',_LANG,'</b></div>
				<img id="_adminDialogBoxLoading" src="'.core::url().'admin/public/icon/loadingBig.gif" alt="Загрузка..." />
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
		if(core::error()) {
			echo '<div class="messageError">'.core::error(false).'</div>';
		}
		//Вывести сообщение об успехе, если оно задано
		if(isset($_SESSION['messageSuccess'])) {
			echo '<div class="messageSuccess">'.$_SESSION['messageSuccess'].'</div>';
			unset($_SESSION['messageSuccess']);
		}
		if(gettype($view)=='object') $view->render();
		elseif($view=='_empty') include(core::path().'view/_empty.php');
		else include(core::path().'view/'.$this->url[0].$view.'.php');
		if($renderTemplate && $s) include(core::path().'cache/template/'.core::template().'Footer.php'); //нижняя часть шаблона
	}

	/**
	 * Выводит HTML-код блока хлебных крошек. Вызывается фреймворком при обработке тега шаблона {{breadcrumb}}
	 */
	public function breadcrumb() {
		if(core::url()==$_SERVER['REQUEST_URI'] || core::url()._LANG.'/'==$_SERVER['REQUEST_URI']) return; //главная страница
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
		$cfg=core::config();
		echo '<div id="breadcrumb" itemprop="breadcrumb"><a href="'.core::url().($cfg['languageDefault']!=_LANG ? _LANG.'/' : '').'" rel="nofollow">'.LNGMain.'</a>'.$b.'</div>';
	}

	/**
	 * Выводит HTML-код кнопок админки для элемента списка, явно вызывать метод не нужно
	 * @param mixed $data Произвольные данные, которые будут переданы в метод sController::admin{Action}Link2()
	 */
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




/**
 * Базовый класс виджета. Все виджеты должны быть унаследованы от этого класса
 */
abstract class widget {

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
		if($view!==true) include(core::path().'view/widget'.$view.'.php');
	}

	/**
	 * Выводит HTML-код кнопок админки для элемента списка. Вызывается фреймворком, явный вызов не требуется.
	 */
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




/**
 * Класс олицетворяет пользователя.
 * Этот класс всегда находится в сессии ($_SESSION['user'], $_SESSION['userCore'])
 * @see core::user()
 * @see core::userCore()
 * @see model/user.php
 */
class user {
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
			core::import('model/user');
			$model=new modelUser($id,$this);
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
	include(core::path().'language/global.'._LANG.'.php');
	$user=core::userCore();
	if($user->group>=200) include(core::path().'core/admin.php');
	controller::$self=new sController($_GET['corePath'][1]);
	$alias=controller::$self->url[0];
	if(isset($_POST[$alias])===false) { //в _POST нет данных, относящихся к запрошенному контроллеру
		if(method_exists('sController','action'.controller::$self->url[1])===false) core::error404();
	} else { //в _POST есть данные, относящиеся к запрошенному контроллеру
		if(method_exists('sController','action'.controller::$self->url[1].'Submit')===false) core::error404();
	}
	//Подготовить данные $_POST и $_FILES для передачи submit-действию
	if(isset($_POST[$alias])) {
		core::hook('initPOST',$alias);
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

		$s='action'.controller::$self->url[1].'Submit';
		$data=controller::$self->$s($_POST[$alias]); //запуск submit-действия, если всё хорошо, то там должен быть выполнен редирект и дальнейшая обработка прерывается
	} else $data=null;
	//Запуск действия (не submit) и вывод контента
	$s='action'.controller::$self->url[1];
	@$view=controller::$self->$s($data);
	controller::$self->render($view,$renderTemplate);
}

/* --- INITIALIZE --- */
if(core::debug()) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}
if(isset($_SERVER['HTTP_HOST']) && substr($_SERVER['HTTP_HOST'],0,4)=='pda.') define('_CLIENT_TYPE','pda'); else define('_CLIENT_TYPE','pc');

$cfg=core::config();

//Поиск языка в URL-адресе
if(isset($_GET['corePath'])) {
	$lang=substr($_GET['corePath'],0,2);
	if(in_array($lang,$cfg['languageList'])) {
		define('_LANG',$lang);
		$_GET['corePath']=substr($_GET['corePath'],3);
	} else define('_LANG',$cfg['languageDefault']);
	unset($lang);
} else define('_LANG',$cfg['languageDefault']);

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
	unset($link);
}

//Перехват if-modified-since (работает без учёта мультиязычности)
if(isset($_SERVER['HTTP_HOST'])) { //только для HTTP-запросов (не для CGI)
	$db=core::db();
	if($cfg['languageDefault']==_LANG) $s=$_GET['corePath']; else $s=_LANG.'/'.$_GET['corePath'];
	$lastModified=(int)$db->fetchValue('SELECT time FROM modified WHERE link='.$db->escape($s));
	unset($s);
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

unset($cfg);
$_GET['corePath']=explode('/',$_GET['corePath']);
if(!isset($_GET['corePath'][1])) $_GET['corePath'][1]=null; //чтобы транслятор не выдавал предупреждений
header('Content-type:text/html; Charset=UTF-8');