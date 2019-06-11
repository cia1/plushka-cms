<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;
use plushka;

/**
 * Конструктор HTML-форм. Автоматически подставляет значения из $_POST, если такие имеются
 */
class Form {

	/** @var string Адрес отправки (action) данных формы */
	public $action;
	/** @var string Метод отправки формы ("get" или "post") */
	public $method='post';
	/** @var string Имя контроллера, для которого предназначены данные формы */
	protected $_namespace;
	/** @var string Буфер, содержащий не завершённый HTML-код формы */
	protected $_data='';

	/**
	 * @param string|null $namespace Имя контроллера, для которого предназначенны данные формы
	 */
	public function __construct($namespace=null) {
		if($namespace) $this->_namespace=$namespace; else $this->_namespace=$_GET['corePath'][0];
	}

	/**
	 * Добавляет поле <input type="hidden">
	 * @param string $name Имя поля
	 * @param string $value Значение поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function hidden($name,$value,$html='') {
		$this->_data.=$this->getHidden($name,$value,$html);
	}

	/**
	 * Текстовая надпись, не является полем формы
	 * @param string $label Название поля
	 * @param string $value Значение поля
	 */
	public function label($label,$value) {
		$this->_data.='<dt class="label">'.$label.':</dt><dd class="label">'.$value.'</dd>';
	}

	/* Текстовое поле
	$name - имя поля, $label - заголовок рядом с полем, $value - значение по умолчанию, $html - произвольный текст, который нужно добавить к тегу <input> */
	/**
	 * Добавляет поле <input type="text">
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function text($name,$label,$value='',$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="text '.$class.'">'.$label.':</dt><dd class="text '.$class.'">'.$this->getText($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет поле <input type="number">
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function number($name,$label,$value='',$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="text number '.$class.'">'.$label.':</dt><dd class="text number '.$class.'">'.$this->getNumber($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет поле <input type="email">
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function email($name,$label,$value='',$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="text email '.$class.'">'.$label.':</dt><dd class="text email '.$class.'">'.$this->getEmail($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет выпадающий список <select>
	 * Если параметр $items - строка, то воспринимается как SQL-запрос, возвращающий список параметров (первый столбец - значение параметра, второй - заголовок). В ином случае это должен быть массив вида: [ ['value', 'title'], ['value', 'title'] ... ].
	 * Если $nullTitle задан, то первым значением в выпадающем списке будет <option value="">$nullTitle</option>
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string|array[] $items Список значений выпадающего списка
	 * @param string|null $value Значение по умолчанию
	 * @param string|null $nullTitle Заголовок для "пустого" значения
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <select>
	 */
	public function select($name,$label,$items,$value=null,$nullTitle=null,$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="select '.$class.'">'.$label.':</dt><dd class="select '.$class.'">'.$this->getSelect($name,$items,$value,$nullTitle,$html).'</dd>';
	}

	/**
	 * Добавляет к форме многострочный список <select>
	 * Если параметр $items - строка, то воспринимается как SQL-запрос, возвращающий список параметров (первый столбец - значение параметра, второй - заголовок). В ином случае это должен быть массив вида: [ ['value', 'title'], ['value', 'title'] ... ].
	 * Если $nullTitle задан, то первым значением в выпадающем списке будет <option value="">$nullTitle</option>
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string|array[] $items Список значений выпадающего списка
	 * @param string|null $value Значение по умолчанию
	 * @param string|null $nullTitle Заголовок для "пустого" значения
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <select>
	 */
	public function listBox($name,$label,$items,$value=null,$nullTitle=null,$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="list '.$class.'">'.$label.':</dt><dd class="list '.$class.'">'.$this->getListBox($name,$items,$value,$nullTitle,$html).'</dd>';
	}

	/**
	 * Добавляет к форме группу переключателей <input type="radio">
	 * Если параметр $items - строка, то воспринимается как SQL-запрос, возвращающий список параметров (первый столбец - значение параметра, второй - заголовок). В ином случае это должен быть массив вида: [ ['value', 'title'], ['value', 'title'] ... ].
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string|array[] $items Список значений выпадающего списка
	 * @param string|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function radio($name,$label,$items,$value=null) {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="radio '.$class.'">'.$label.':</dt><dd class="radio '.$class.'">'.$this->getRadio($name,$items,$value).'</dd>';
	}

	/**
	 * Добавляет к форме переключатель <input type="checkbox">
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function checkbox($name,$label,$value=null,$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<label><dt class="checkbox '.$class.'">'.$label.':</dt><dd class="checkbox '.$class.'">'.$this->getCheckbox($name,$value,$html).'</dd></label>';
	}

	/**
	 * Добавляет к форме поле ввода пароля <input type="password">
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function password($name,$label,$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="password '.$class.'">'.$label.':</dt><dd class="password '.$class.'">'.$this->getPassword($name,$html).'</dd>';
	}

	/**
	 * Добавляет к форме многострочное поле ввода <textarea>
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <textarea>
	 */
	public function textarea($name,$label,$value='',$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="textarea '.$class.'">'.$label.':</dt><dd class="textarea '.$class.'">'.$this->getTextarea($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет к форме визуальный редактор CKEditor
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function editor($name,$label,$value='',$config=array()) {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="textarea '.$class.'">'.$label.':</dt><dd class="textarea '.$class.'">'.$this->getEditor($name,$value,$config).'</dd>';
	}

	/**
	 * Добавляет к форме поле выбора даты <input type="date">
	 * Параметр $value может быть задан строкой или Timestamp.
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string|int|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function date($name,$label,$value=null,$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="text date '.$class.'">'.$label.':</dt><dd class="text date '.$class.'">'.$this->getDate($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет к форме поле выбора даты и времени <input type="datetime-local">
	 * Параметр $value может быть задан строкой или Timestamp.
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string|int|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function dateTime($name,$label,$value=null,$html='') {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="text date time '.$class.'">'.$label.':</dt><dd class="text date '.$class.'">'.$this->getDateTime($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет к форме поле для загрузки файла <input type="file">
	 * В Submit-действии контроллера файл будет доступен через параметр $data среди прочих POST-данных.
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param bool $multiple Разрешить загрузку нескольких файлов
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function file($name,$label,$multiple=false,$html='') {
		$this->_data.='<dt class="file">'.$label.':</dt><dd class="file">'.$this->getFile($name,$multiple,$html).'</dd>';
	}

	/**
	 * Добавляет к форме поле ввода проверочного кода (капча)
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function captcha($name,$label,$html='') {
		$this->_data.='<dt class="captcha">'.$label.':<img src="'.plushka::url().'captcha.php" alt="'.strip_tags($label).'" title="'.strip_tags($label).'"></dt><dd class="captcha"><input type="text" name="'.$this->_namespace.'['.$name.']" '.$html.' /></dd>';
	}

	/**
	 * Добавляет к форме кнопку сброса <input type="reset">
	 * @param string $label Заголовок поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function reset($label,$html='') {
		$this->_data.='<input type="reset" value="'.$label.'" class="button reset" '.$html.' />';
	}

	/**
	 * Добавляет к форме кнопку отправки данных <input type="submit">
	 * @param string $label Заголовок поля
	 * @param string|null $name Имя поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function submit($label=LNGContinue,$name=null,$html='') {
		$this->_data.='<dd class="submit">'.$this->getSubmit($label,$name,$html).'</dd>';
	}

	/**
	 * Добавляет к форме произвольный HTML-код
	 * @param string $html HTML-код
	 */
	public function html($html) { $this->_data.=$html; }

	/**
	 * Публикует HTML-код настроенной формы
	 * @param string|null $action Адрес отправлки данных (атрибут action)
	 * @param string|null $html Произвольный HTML-код, который будет добавлен к HTML-тегу <form>
	 * @see form::$action
	 */
	public function render($action=null,$html=null) {
		if($action) $this->action=$action;
		echo '<form action="'.($this->action ? plushka::link($this->action) : $_SERVER['REQUEST_URI']).'" method="'.$this->method.'" enctype="multipart/form-data" name="'.$this->_namespace.'" class="'.$this->_namespace.'" '.$html.'>
		<dl class="form">';
		echo $this->_data;
		unset($this->_data);
		echo '</dl></form>';
	}

	/**
	 * Возвращает HTML-код скрытого поля <input type="hidden">
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <select>
	 */
	public function getHidden($name,$value,$html='') {
		return '<input type="hidden" name="'.$this->_namespace.'['.$name.']" value="'.$value.'" '.$html.' />';
	}

	/**
	 * Возвращает HTML-код поля <input type="text">
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getText($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace('"','&quot;',$value);
		return '<input type="text" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').' '.$html.' />';
	}

	/**
	 * Возвращает HTML-код поля <input type="number">
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getNumber($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace('"','&quot;',$value);
		return '<input type="number" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').' '.$html.' />';
	}

	/**
	 * Возвращает HTML-код поля <input type="email">
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getEmail($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace('"','&quot;',$value);
		return '<input type="email" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').' '.$html.' />';
	}

	/**
	 * Возвращает HTML-код выпадающего списка <select>
	 * Если параметр $items - строка, то воспринимается как SQL-запрос, возвращающий список параметров (первый столбец - значение параметра, второй - заголовок). В ином случае это должен быть массив вида: [ ['value', 'title'], ['value', 'title'] ... ].
	 * Если $nullTitle задан, то первым значением в выпадающем списке будет <option value="">$nullTitle</option>
	 * @param string $name Имя поля
	 * @param string|array[] $items Список значений выпадающего списка
	 * @param string|null $value Значение по умолчанию
	 * @param string|null $nullTitle Заголовок для "пустого" значения
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <select>
	 */
	public function getSelect($name,$items,$value=null,$nullTitle=null,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='<select name="'.$this->_namespace.'['.$name.']" size="1" '.$html.'>';
		if($nullTitle!==null) {
			$s.='<option value=""';
			if($value===null || $value==='') $s.=' selected="selected"';
			$s.='>'.$nullTitle.'</option>';
		}
		if(is_string($items)) {
			$db=plushka::db();
			$db->query($items);
			while($item=$db->fetch()) {
				$s.='<option value="'.$item[0].'"';
				if($item[0]==$value) $s.=' selected="selected"';
				$s.='>'.$item[1].'</option>';
			}
		} else foreach($items as $item) {
			$s.='<option value="'.$item[0].'"';
			if($item[0]==$value && !($item[0]=='0' && ($value===null || $value===''))) $s.=' selected="selected"';
			$s.='>'.$item[1].'</option>';
		}
		$s.='</select>';
		return $s;
	}

	/**
	 * Возвращает HTML-код многострочного списка <select>
	 * Если параметр $items - строка, то воспринимается как SQL-запрос, возвращающий список параметров (первый столбец - значение параметра, второй - заголовок). В ином случае это должен быть массив вида: [ ['value', 'title'], ['value', 'title'] ... ].
	 * Если $nullTitle задан, то первым значением в выпадающем списке будет <option value="">$nullTitle</option>
	 * @param string $name Имя поля
	 * @param string|array[] $items Список значений выпадающего списка
	 * @param string|null $value Значение по умолчанию
	 * @param string|null $nullTitle Заголовок для "пустого" значения
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <select>
	 */
	public function getListBox($name,$items,$value=null,$nullTitle=null,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='<select name="'.$this->_namespace.'['.$name.']" size="8" '.$html.'>';
		if($nullTitle!==null) $s.='<option value=""';
		if($value===null || $value==='') $s.=' selected="selected"';
		$s.='>'.$nullTitle.'</option>';
		if(is_string($items)) {
			$db=plushka::db();
			$db->query($items);
			while($item=$db->fetch()) {
				$s.='<option value="'.$item[0].'"';
				if($item[0]==$value) $s.=' selected="selected"';
				$s.='>'.$item[1].'</option>';
			}
		} else foreach($items as $item) {
			$s.='<option value="'.$item[0].'"';
			if($item[0]==$value) $s.=' selected="selected"';
			$s.='>'.$item[1].'</option>';
		}
		$s.='</select></dd>';
		return $s;
	}

	/**
	 * Возвращает HTML-код группы переключателей <input type="radio">
	 * Если параметр $items - строка, то воспринимается как SQL-запрос, возвращающий список параметров (первый столбец - значение параметра, второй - заголовок). В ином случае это должен быть массив вида: [ ['value', 'title'], ['value', 'title'] ... ].
	 * @param string $name Имя поля
	 * @param string|array[] $items Список значений выпадающего списка
	 * @param string|null $value Значение по умолчанию
	 * @param string|null $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getRadio($name,$items,$value=null,$html=null) {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='';
		if(is_string($items)) {
			$db=plushka::db();
			$db->query($items);
			while($item=$db->fetch()) {
				$s.='<label><input type="radio" name="'.$this->_namespace.'['.$name.']" value="'.$item[0].'"';
				if($item[0]==$value) $s.=' checked="checked"';
				$s.=' /></label>';
			}
		} else foreach($items as $item) {
		$s.='<label><input type="radio" name="'.$this->_namespace.'['.$name.']" value="'.$item[0].'"';
			if($item[0]==$value) $s.=' checked="checked"';
			if($html) $s.=' '.$html;
			$s.=' />'.$item[1].'</label>';
		}
		return $s;
	}

	/**
	 * Возвращает HTML-код переключателя <input type="checkbox">
	 * @param string $name Имя поля
	 * @param string|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getCheckbox($name,$value=null,$html='') {
		if(isset($_POST[$this->_namespace])) {
			$i=strpos($name,'][');
			$value=false;
			if($i) {
				$subname=explode('][',$name);
				if(isset($_POST[$this->_namespace][$subname[0]]) && isset($_POST[$this->_namespace][$subname[0]][$subname[1]])) $value=true;
			} elseif(isset($_POST[$this->_namespace][$name])) $value=true;
		}
		return '<input type="checkbox" name="'.$this->_namespace.'['.$name.']"'.($value ? ' checked="checked"' : '').' '.$html.' />';
	}

	/**
	 * Возвращает HTML-код поля ввода пароля <input type="password">
	 * @param string $name Имя поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getPassword($name,$html='') {
		return '<input type="password" name="'.$this->_namespace.'['.$name.']" '.$html.' />';
	}

	/**
	 * Возвращает HTML-код многострочного поля ввода <textarea>
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <textarea>
	 */
	public function getTextarea($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		return '<textarea name="'.$this->_namespace.'['.$name.']" '.$html.'>'.$value.'</textarea>';
	}

	/**
	 * Возвращает HTML-код визуального редактора CKEditor
	 * Массив $config может содержать следующие параметры:
	 * ['uploadTo'] - диретория, в которую будут загружаться изображения, если указан, то загрузка файлов в другие директории будет запрещена;
	 * ['html'] - произвольный HTML-код, который будет добавлен к HTML-тегу <textarea>
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param array $config Массив настроек редактора
	 */
	public function getEditor($name,$value='',$config=array()) {
		$userGroup=plushka::userGroup();
		if(!$config) $config=array();
		if($userGroup>=200 && !isset($config['uploadTo'])) $config['uploadTo']='public/'; //для админки по умолчанию разрешить загружать изображения куда угодно в пределах директория /public
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace(array('&lt;','&gt;'),array('&amp;lt;','&amp;gt;'),$value);
		$html='<textarea name="'.$this->_namespace.'['.$name.']" id="'.$name.'"'.(isset($config['html']) && $config['html'] ? ' '.$html : '').'>'.$value.'</textarea>';
		if(!isset($_GET['_lang'])) $html.=plushka::js('ckeditor/ckeditor');
		$html.='<script>
		if(document.ckeditor==undefined) document.ckeditor=new Array();
		if(document.ckeditor["'.$name.'"]!=undefined) CKEDITOR.remove(document.ckeditor["'.$name.'"]);
		document.ckeditor["'.$name.'"]=CKEDITOR.replace("'.$name.'",{
			customConfig:"'.(isset($_GET['_lang']) ? plushka::url().'admin/public/js/ckeditor-config.js' : plushka::url().'public/js/ckeditor-config.js').'"';
		if(isset($config['uploadTo'])) {
			$html.=',uploadUrl:"'.plushka::url().'upload.php"';
//				',filebrowserUploadUrl:"'.plushka::url().$config['uploadTo'].'"';
			$_SESSION['_uploadFolder']=$config['uploadTo']; //запомнить, куда разрешено загружать файлы, поддерживается только один директорий
		}
		$html.='});</script>';
		return $html;
	}

	/**
	 * Возвращает HTML-код поля выбора даты <input type="date">
	 * Параметр $value может быть задан строкой или Timestamp.
	 * @param string $name Имя поля
	 * @param string|int|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getDate($name,$value=null,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		if($value && is_numeric($value)) $value=date('Y-m-d',$value);
		return '<input type="date" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').($html ? ' '.$html : '').' />';
	}

	/**
	 * Возвращает HTML-код поля выбора даты и времени <input type="datetime-local">
	 * Параметр $value может быть задан строкой или Timestamp.
	 * @param string $name Имя поля
	 * @param string|int|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getDateTime($name,$value,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		if($value && is_numeric($value)) $value=date('Y-m-d H:i:s',$value);
		return '<input type="datetime-local" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').($html ? ' '.$html : '').' />';
	}

	/**
	 * Возвращает HTML-код поля для загрузки файла/ов <input type="file">
	 * В Submit-действии контроллера файл будет доступен через параметр $data среди прочих POST-данных.
	 * @param string $name Имя поля
	 * @param bool $multiple Разрешить загрузку нескольких файлов
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getFile($name,$multiple=false,$html='') {
		if($multiple) {
			$name.='][';
			$html='multiple="true" '.$html;
		}
		return '<input type="file" name="'.$this->_namespace.'['.$name.']" '.$html.' />';
	}

	/**
	 * Возвращает HTML-код кнопки отправки данных <input type="submit">
	 * @param string $label Надпись на кнопке
	 * @param string|null $name Имя поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function getSubmit($label=LNGContinue,$name=null,$html='') {
		$s='<input type="submit" value="'.$label.'"';
		if($name) $s.=' name="'.$this->_namespace.'['.$name.']"';
		$s.=' class="button submit" '.$html.' />';
		return $s;
	}

}
