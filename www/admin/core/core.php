<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;
require dirname(__DIR__,2).'/core/core.php';
use plushka\core\DBException;
use plushka\core\HTTPException;
use plushka\controller\ErrorController;
use plushka\core\Cache;
use plushka\core\Controller as ControllerPublic;
use Throwable;

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

	/**
	 * Добавляет кнопку в специально отведённую область
	 * Если $link==="html", то вместо кнопки присоединяется произвольный HTML-код из параметра $image.
	 * @param string $link  ссылка на страницу админки или "html"
	 * @param string $image условное имя файла изображения кнопки или произвольный HTML-код
	 * @param string $title всплывающая подсказка
	 * @param string $alt   текст атрибута ALT тега <img>
	 * @param string $html  любой другой HTML-код, который будет добавлен к тегу <a>
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
	 * @param bool $renderTemplate нужно ли использовать шаблон
	 */
	public function render(bool $renderTemplate=true): void {
		$alias=$this->url[0];
		//Проверка прав доступа к запрошенной странице
		$user=plushka::userReal();
		if($alias!=='user' || $this->url[1]!=='Login') {
			if($user->group<200) plushka::redirect('user/login');
			if($user->group!==255) {
				if(method_exists($this,'right')===false) {
					plushka::error('Недостаточно прав для доступа к разделу');
					plushka::redirect('user/login');
				}
				$right=$this->right();
				if(isset($right[$this->url[1]])===false) {
					plushka::error('Недостаточно прав для доступа к разделу');
					plushka::redirect('user/login');
				}
				$right=explode(',',$right[$this->url[1]]);
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
			if(method_exists($this,'action'.$this->url[1].'Submit')===false) throw new HTTPException(404);
			self::filesToPost($alias);
			$s='action'.$this->url[1].'Submit';
			$post=$_POST[$alias];
			$data=$this->$s($post); //запуск submit-действия, если всё хорошо, то там должен быть выполнен редирект и дальнейшая обработка прерывается
			//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
			if(isset($_GET['_serialize'])===true) {
				if(plushka::error()) die(plushka::error(false));
				echo "OK\n";
				if(isset($post['cacheTime'])===true || (is_array($data)===true && isset($data['cacheTime'])===true)) {
					echo $post['cacheTime'];
					if(is_array($data)===true && isset($data['cacheTime'])===true) unset($data['cacheTime']);
				} else echo '0';
				echo "\n";
				if(is_array($data)===true) echo serialize($data); else echo $data;
				exit;
			}
		} else $data=null;

		$s='action'.$this->url[1];
		if(method_exists($this,$s)===false) throw new HTTPException(404);

		//Если есть сериализованные данные, то восстановить их (нужно для меню и виджетов)
		if(isset($_GET['_serialize'])===true && isset($_POST['data'])===true) {
			if(substr($_POST['data'],0,2)==='a:' && $_POST['data'][strlen($_POST['data'])-1]==='}') $view=$this->$s(unserialize($_POST['data']));
			else $view=$this->$s($_POST['data']);
		} else $view=$this->$s($data);

		//Генерация HTML-страницы
		if(!plushka::template()) $renderTemplate=false;
		if(!$view) return; //Если нет представления, то ничего не выводить
		if($renderTemplate===true) { //Вывести верхнюю часть шаблона
			$s=plushka::path().'admin/cache/template/'.plushka::template().'Head.php';
			if(file_exists($s)===false || plushka::debug()===true) Cache::template(plushka::template());
			/** @noinspection PhpIncludeInspection */
			include($s);
			if($this->pageTitle) echo '<h1 class="pageTitle">',$this->pageTitle,'</h1>';
		} else echo $this->_head;
		if($this->_button) echo '<div class="_operation">',$this->_button,'</div>'; //Кнопки
		//Сообщение об ошибке (если есть)
		if(plushka::error()) {
			echo '<div class="messageError">',plushka::error(false),'</div>';
		}
		//Сообщение об успехе (если был редирект с сообщением)
		if(plushka::success()) {
			echo '<div class="messageSuccess">',plushka::success(false),'</div>';
		}
		if(gettype($view)==='object' && method_exists($view,'render')) $view->render();
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
	try {
		try {
			plushka::$controller=new plushka::$controller();
		} catch(Throwable $e) {
			throw new HTTPException(404);
		}
		plushka::$controller->render($renderTemplate);
	} catch(DBException $e) {
		header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
		if(plushka::debug()===true) echo '<p>',$e,'</p>';
	} catch(HTTPException $e) {
		$controller=new ErrorController($e);
		$controller->render($renderTemplate);
	}
}