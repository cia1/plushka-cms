<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
use plushka\core\Admin;
use plushka\core\core;
use plushka\core\Widget;

require_once(__DIR__.'/core.php');

/**
 * Предоставляет базовый API, доступный статически
 */
abstract class plushka extends core {

	/**
	 * Возвращает произвольные данные из кэша
	 * Если кэш не существует или устарел и не задан параметр $callback, то будет возвращено NULL
	 * @param string $id Идентификатор кэша
	 * @param callback|null $callback Callback-функция, которая будет вызвана если кэш не существует или устарел.
	 * @param int|null $timeout Время в минутах актуальности кэша
	 * @return mixed|null
	 */
	public static function cache(string $id,callable $callback=null,int $timeout=null) {
		if($timeout!==null && self::debug()===true) $timeout=0;
		$f=self::path().'cache/custom/'.$id.'.txt';
		if(file_exists($f)===true) {
			if($timeout===null || $callback===null) return unserialize(file_get_contents($f));
			if(time()-filemtime($f)<$timeout*60) return unserialize(file_get_contents($f));
		}
		if($callback===null) return null;
		$data=call_user_func($callback);
		$f=fopen($f,'w');
		fwrite($f,serialize($data));
		fclose($f);
		return $data;
	}

	/**
	 * Генерирует событие
	 * Обработчики события - это файлы /hook/$name.{module}.php
	 * @param string $name Имя события
	 * @param mixed ...$data Произвольные данные, которые будут доступны в обработчике события
	 * @return array|false False, если хотя бы один обработчик вернул false, иначе массив значений, возвращённых обработчиками событий
	 */
	public static function hook(string $name,...$data) {
		$d=opendir(plushka::path().'hook');
		$result=[];
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
	 * Прерывает выполнение скрипта и выполняет перенаправление на указанный адрес
	 * @param string $url URL в формате "controller/etc"
	 * @param string|null $message Если задан, то установит текст сообщения об успешно выполненной операции
	 * @param int $code HTTP-код ответа
	 * @see plushka::success()
	 */
	public static function redirect(string $url,string $message=null,int $code=302): void {
		if($message!==null) plushka::success($message);
		header('Location: '.self::link($url),true,$code);
		exit;
	}

	/**
	 * Генерирует и публикует HTML-код секции. Этот метод обрабатывает теги {{section}}
	 * @param string $name Имя секции
	 */
	public static function section(string $name): void {
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
		$u=self::userReal();
		//Если пользователь админ и имеет права управления секциями, то вывести кнопки административного интерфейса
		if($u->group>=200 && ($u->group==255 || isset($u->right['section.*']))) {
			$admin=new Admin();
			$admin->add('?controller=section&name='.$name,'section','Управление виджетами в этой области','Секция');
		}
		//Теперь перебрать все виджеты секции
		$userGroup=plushka::userGroup();
		for($i=0;$i<$cnt;$i++) {
			if($items[$i][6]!==null && $items[$i][6]!=$userGroup) continue;
			$options=$items[$i][1];
			if($items[$i][7]) {
				if(isset($options[1]) && $options[1]==':') {
					$options=unserialize($options);
				} else $options=array('_content'=>$options);
				$options['cssClass']=$items[$i][7];
			}
			plushka::widget($items[$i][0],$options,$items[$i][2],($items[$i][3]=='1' ? $items[$i][4] : null),$items[$i][5]);
		}
		echo '</div>';
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
		if(is_array($options)===true && isset($options['cssClass'])===true) {
			$cssClass=' '.$options['cssClass'];
			unset($options['cssClass']);
		} else $cssClass='';
		if(is_string($options)===true && isset($options[1])===true && $options[1]===':') $options=unserialize($options);
		elseif(is_array($options)===true && count($options)===1 && isset($options['_content'])===true) $options=$options['_content'];
		//Нужно ли кешировать?
		if($cacheTime!==null && self::debug()===true) $cacheTime=null;
		if($cacheTime!==null) {
			if(is_array($options)===true) {
				$f='';
				ksort($options);
				foreach($options as $index=>$value) $f.=$index.$value;
			} else $f=$options;
			$f=md5($f);
			$cacheFile=self::path().'cache/widget/'.$name.'.'.$f;
			$content=$cacheFile.'.html';
			if(file_exists($content)===true) {
				$f=filemtime($content)+$cacheTime*60;
				if($f>time()) {
					echo '<section class="widget',$name,$cssClass,'">';
					$cacheFile.='.json';
					if(file_exists($cacheFile)===true) self::_widgetAdmin(json_decode(file_get_contents($cacheFile),true));
					$content=file_get_contents($content);
					echo $content;
					echo '<div style="clear:both;"></div></section>';
					return;
				}
			}
		} else $cacheFile=null;
		$name=ucfirst($name);
		$w='\plushka\widget\\'.$name.'Widget';
		/** @var Widget $w */
		$w=new $w($options,$link);
		$w->cssClass=$cssClass;
		$view=$w();
		$adminButton=null;
		if($view!==null && $view!==false) { //Если widget() вернул null или false, то выводить HTML-код ненужно (виджет может выводиться только при определённых условиях)
			echo '<section class="widget'.$name.$cssClass.'">';
			//Если пользователь является администратором, то вывести элементы управления в соответствии его правам
			$adminButton=self::_widgetAdmin($w,true);
			if($cacheTime) ob_start();
			if($title) $w->title($title); //вывод заголовка
			if(is_object($view)===true) $view->render(); else $w->render($view);
		}
		if($cacheFile!==null) {
			$f=fopen($cacheFile.'.html','w');
			fwrite($f,ob_get_flush());
			fclose($f);
			if($adminButton!==null) {
				$f=fopen($cacheFile.'.json','w');
				fwrite($f,json_encode($adminButton));
				fclose($f);
			}
		}
		echo '<div style="clear:both;"></div></section>';
	}

	private static function _hook(string $name,/** @noinspection PhpUnusedParameterInspection */$data) {
        /** @noinspection PhpIncludeInspection */
		return include(self::path().'hook/'.$name);
	}

    /**
     * Выводит кнопки админки для виджета
     * @param Widget|array[] $data
     * @param bool $returnAsArray Если true, то вернёт массив кнопок админки для сохранения в кеше
     * @return array[]|null
     */
	private static function _widgetAdmin($data,bool $returnAsArray=false): ?array {
		$user=plushka::userReal();
		if($user->group<200) return null;
		$admin=new Admin();
		if(is_object($data)===true) $data=$data->adminLink();
		foreach($data as $item) {
			if($user->group==255 || isset($user->right[$item[0]])) $admin->render($item);
		}
		return ($returnAsArray===true ? $data : null);
	}
}



/* --- INITIALIZE --- */
if(plushka::debug()) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}

$cfg=plushka::config();


//Обработка URI, формирование $_GET['corePath'].
if(substr($_SERVER['SCRIPT_NAME'],-11)==='/index2.php') $_GET['corePath']=$_GET['controller'].(isset($_GET['action'])===true ? '/'.$_GET['action'] : '') ?? false;
elseif(isset($_SERVER['REQUEST_URI'])===true) {
	$i=strpos($_SERVER['REQUEST_URI'],'?');
	if($i>0) $i--; else $i=9999;
	$_GET['corePath']=substr($_SERVER['REQUEST_URI'],1,$i);
	unset($i);
} else $_GET['corePath']=false;
//Поиск языка в URL-адресе
if($_GET['corePath']===false) {
	$lang=substr($_GET['corePath'],0,2);
	if(in_array($lang,$cfg['languageList'])) {
		define('_LANG',$lang);
		$_GET['corePath']=substr($_GET['corePath'],3);
	} else define('_LANG',$cfg['languageDefault']);
	unset($lang);
} else define('_LANG',$cfg['languageDefault']);
//Преобразования подмены ссылок
if($_GET['corePath']===false || !$_GET['corePath']) $_GET['corePath']=$cfg['mainPath'];
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
	$db=plushka::db();
	if($cfg['languageDefault']==_LANG) $s=$_GET['corePath']; else $s=_LANG.'/'.$_GET['corePath'];
	/** @noinspection SqlResolve */
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
if(isset($_GET['corePath'][1])===false) $_GET['corePath'][1]=null; //ВСЕГДА должно быть по крайней мере два элемента

plushka::language('global');
