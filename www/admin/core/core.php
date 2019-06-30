<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;
use plushkaAdmin as plushka;
use plushka\core\Cache;
use plushka\core\Controller as ControllerPublic;

require(dirname(__DIR__,2).'/core/core.php');

/**
 * @inheritdoc
 * @method right(): array[] Должен возвращать массив прав доступа к действиям контроллера
 */
class Controller extends ControllerPublic {

	/**
	 * @var string Поясняющий текст для диалогового окна админки
	 */
	protected $cite='';

	private $_button=''; //HTML код кнопок

	public function __construct() {
	    parent::__construct();
		$this->url=[$_GET['controller'],$_GET['action']];
		if(!$this->url[1]) $this->url[1]='index';
	}

	/**
	 * Добавляет кнопку в специально отведённую область
	 * Если $link==="html", то вместо кнопки присоединяется произвольный HTML-код из параметра $image.
	 * @param string $link ссылка на страницу админки или "html"
	 * @param string $image условное имя файла изображения кнопки или произвольный HTML-код
	 * @param string $title всплывающая подсказка
	 * @param string $alt текст атрибута ALT тега <img>
	 * @param string $html любой другой HTML-код, который будет добавлен к тегу <a>
	 */
	public function button(string $link,string $image,string $title='',string $alt='',string $html=''): void {
		if($link==='html') $this->_button.=$image;
		else {
			$this->_button.='<a href="'.plushka::link('admin/'.$link).'" '.
                ($html ? $html : '').
                '><img src="'.plushka::url().'admin/public/icon/'.$image.'32.png" alt="'.($alt ? $alt : $title).'" title="'.$title.'" /></a>';
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function css(string $text): void {
		if(substr($text,0,6)!=='admin/') parent::css($text);
		if($text[0]!=='<') $text='<link type="text/css" rel="stylesheet" href="'.plushka::url().'admin/public/css/'.$text.'.css" />';
		$this->_head.=$text;
	}

	/**
	 * Генерирует HTML-код (шаблон, теги в <head>, кнопки админки, представление)
	 * @param string|object|null $view - представление
	 * @param bool $renderTemplate нужно ли использовать шаблон
	 */
	public function render($view,bool $renderTemplate=true): void {
		if(!plushka::template()) $renderTemplate=false;
		if(!$view) return; //Если нет представления, то ничего не выводить
		if($renderTemplate===true) { //Вывести верхнюю часть шаблона
			$s=plushka::path().'admin/cache/template/'.plushka::template().'Head.php';
			if(file_exists($s)===false || plushka::debug()===true) Cache::template(plushka::template());
			/** @noinspection PhpIncludeInspection */
			include($s);
			if($this->pageTitle) echo '<h1 class="pageTitle">'.$this->pageTitle.'</h1>';
		} else echo $this->_head;
		if($this->_button) echo '<div class="_operation">'.$this->_button.'</div>'; //Кнопки
		//Сообщение об ошибке (если есть)
		if(plushka::error()) {
			echo '<div class="messageError">',plushka::error(false),'</div>';
		}
		//Сообщение об успехе (если был редирект с сообщением)
		if(plushka::success()) {
			echo '<div class="messageSuccess">',plushka::success(false),'</div>';
		}
		if(gettype($view)==='object') $view->render();
		elseif($view==='_empty') {
		    /** @noinspection PhpIncludeInspection */
		    include(plushka::path().'admin/view/_empty.php');
        } else {
            /** @noinspection PhpIncludeInspection */
		    include(plushka::path().'admin/view/'.$this->url[0].$view.'.php');
        }
		if($this->cite) echo '<cite>',$this->cite,'</cite>'; //Поясняющий текст
		if($renderTemplate===true) { //нижняя часть шаблона
            /** @noinspection PhpIncludeInspection */
		    include(plushka::path().'admin/cache/template/'.plushka::template().'Footer.php');
        } elseif(isset($_GET['_front'])===false) echo '<div style="clear:both;"></div>';

		if($renderTemplate===true && isset($_GET['_front'])===true) {
			$f='help'.$this->url[1];
			if(method_exists($this,$f)===true) {
				echo '<script type="text/javascript">_adminDialogBoxSetHelp("',$this->$f(),'")</script>';
			}
		}
	}
}




/**
 * Запускает приложение
 * @param bool $renderTemplate Нужно ли обрабатывать шаблон (false для AJAX-запросов)
 */
function runApplication(bool $renderTemplate=true): void {
	session_start();
	plushka::$controller='\plushka\admin\controller\\'.ucfirst($_GET['controller']).'Controller';
	plushka::$controller=new plushka::$controller();
	$alias=plushka::$controller->url[0];
	//Проверка прав доступа к запрошенной странице
	$user=plushka::userReal();
	if($alias!=='user' || plushka::$controller->url[1]!=='Login') {
		if($user->group<200) plushka::redirect('user/login');
		if($user->group!=255) {
			if(method_exists(plushka::$controller,'right')===false) {
				plushka::error('Недостаточно прав для доступа к разделу');
				plushka::redirect('user/login');
			}
			$right=plushka::$controller->right();
			if(isset($right[plushka::$controller->url[1]])===false) {
				plushka::error('Недостаточно прав для доступа к разделу');
				plushka::redirect('user/login');
			}
			$right=explode(',',$right[plushka::$controller->url[1]]);
			foreach($right as $item) {
				if($item==='*') continue;
				if(isset($user->right[$item])===false) {
					plushka::error('Недостаточно прав для доступа к разделу');
					plushka::redirect('user/login');
				}
			}
		}
	}
	if(isset($_POST[$alias])===true) { //в _POST есть данные, относящиеся к запрошенному контроллеру
		$s='action'.plushka::$controller->url[1].'Submit';
		if(method_exists(plushka::$controller,$s)===false) plushka::error404();
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
		$s='action'.plushka::$controller->url[1];
		if(method_exists(plushka::$controller,$s)===false) plushka::error404();
		//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
		if(isset($_GET['_serialize'])===true && isset($_POST['data'])===true) {
			if(substr($_POST['data'],0,2)==='a:' && $_POST['data'][strlen($_POST['data'])-1]==='}') $view=plushka::$controller->$s(unserialize($_POST['data']));
			else $view=plushka::$controller->$s($_POST['data']);
		} else $view=plushka::$controller->$s($data);
	} else $view=null;
	plushka::$controller->render($view,$renderTemplate);
}
