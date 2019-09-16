<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;

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
	public function __construct(string $namespace=null) {
		if($namespace!==null) $this->_namespace=$namespace; else $this->_namespace=$_GET['corePath'][0];
	}

	/**
	 * Добавляет поле <input type="hidden">
	 * @param string $name Имя поля
	 * @param string $value Значение поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function hidden(string $name,string $value,string $html=''): void {
		$this->_data.=$this->getHidden($name,$value,$html);
	}

	/**
	 * Текстовая надпись, не является полем формы
	 * @param string $label Название поля
	 * @param string $value Значение поля
	 */
	public function label(string $label,string $value): void {
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
	public function text(string $name,string $label,string $value='',string $html=''): void {
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
	public function number(string $name,string $label,string $value='',string $html=''): void {
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
	public function email(string $name,string $label,string $value='',string $html=''): void {
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
	public function select(string $name,string $label,$items,string $value=null,string $nullTitle=null,string $html=''): void {
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
	public function listBox(string $name,string $label,$items,string $value=null,string $nullTitle=null,string $html=''): void {
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
	 */
	public function radio(string $name,string $label,$items,string $value=null): void {
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
	public function checkbox(string $name,string $label,string $value=null,string $html=''): void {
		$class=str_replace('][','-',$name);
		$this->_data.='<label><dt class="checkbox '.$class.'">'.$label.':</dt><dd class="checkbox '.$class.'">'.$this->getCheckbox($name,$value,$html).'</dd></label>';
	}

	/**
	 * Добавляет к форме поле ввода пароля <input type="password">
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function password(string $name,string $label,string $html=''): void {
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
	public function textarea(string $name,string $label,string $value='',string $html=''): void {
		$class=str_replace('][','-',$name);
		$this->_data.='<dt class="textarea '.$class.'">'.$label.':</dt><dd class="textarea '.$class.'">'.$this->getTextarea($name,$value,$html).'</dd>';
	}

	/**
	 * Добавляет к форме визуальный редактор CKEditor
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $value Значение по умолчанию
	 * @param array $config Дополнительные настройки
	 */
	public function editor(string $name,string $label,string $value='',array $config=[]) {
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
	public function date(string $name,string $label,string $value=null,string $html=''): void {
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
	public function dateTime(string $name,string $label,string $value=null,string $html=''): void {
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
	public function file(string $name,string $label,bool $multiple=false,string $html=''): void {
		$this->_data.='<dt class="file">'.$label.':</dt><dd class="file">'.$this->getFile($name,$multiple,$html).'</dd>';
	}

	/**
	 * Добавляет к форме поле ввода проверочного кода (капча)
	 * @param string $name Имя поля
	 * @param string $label Заголовок поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function captcha(string $name,string $label,string $html=''): void {
		$this->_data.='<dt class="captcha">'.$label.':<img src="'.core::url().'captcha.php" alt="'.strip_tags($label).'" title="'.strip_tags($label).'"></dt><dd class="captcha"><input type="text" name="'.$this->_namespace.'['.$name.']" '.$html.' /></dd>';
	}

	/**
	 * Добавляет к форме кнопку сброса <input type="reset">
	 * @param string $label Заголовок поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function reset(string $label,string $html=''): void {
		$this->_data.='<input type="reset" value="'.$label.'" class="button reset" '.$html.' />';
	}

	/**
	 * Добавляет к форме кнопку отправки данных <input type="submit">
	 * @param string $label Заголовок поля
	 * @param string|null $name Имя поля
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
	 */
	public function submit(string $label=LNGContinue,string $name=null,string $html=''): void {
		$this->_data.='<dd class="submit">'.$this->getSubmit($label,$name,$html).'</dd>';
	}

	/**
	 * Добавляет к форме произвольный HTML-код
	 * @param string $html HTML-код
	 */
	public function html(string $html): void {
		$this->_data.=$html;
	}

	/**
	 * Публикует HTML-код настроенной формы
	 * @param string|null $action Адрес отправлки данных (атрибут action)
	 * @param string|null $html Произвольный HTML-код, который будет добавлен к HTML-тегу <form>
	 * @see form::$action
	 */
	public function render(string $action=null,string $html=null): void {
		if($action!==null) $this->action=$action;
		echo '<form action="',($this->action ? core::link($this->action) : $_SERVER['REQUEST_URI']),'" method="',$this->method,'" enctype="multipart/form-data" name="',$this->_namespace,'" class="',$this->_namespace,'" ',$html,'>
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
     * @return string
	 */
	public function getHidden(string $name,string $value,string $html=''): string {
		return '<input type="hidden" name="'.$this->_namespace.'['.$name.']" value="'.$value.'" '.$html.' />';
	}

	/**
	 * Возвращает HTML-код поля <input type="text">
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
     * @return string
	 */
	public function getText(string $name,string $value='',string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
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
     * @return string
	 */
	public function getNumber(string $name,string $value='',string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
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
     * @return string
	 */
	public function getEmail(string $name,string $value='',string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
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
     * @return string
	 */
	public function getSelect(string $name,$items,string $value=null,string $nullTitle=null,string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='<select name="'.$this->_namespace.'['.$name.']" size="1" '.$html.'>';
		if($nullTitle!==null) {
			$s.='<option value=""';
			if($value===null || $value==='') $s.=' selected="selected"';
			$s.='>'.$nullTitle.'</option>';
		}
		if(is_string($items)===true) {
			$db=core::db();
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
     * @return string
	 */
	public function getListBox(string $name,$items,string $value=null,string $nullTitle=null,string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='<select name="'.$this->_namespace.'['.$name.']" size="8" '.$html.'>';
		if($nullTitle!==null) $s.='<option value=""';
		if($value===null || $value==='') $s.=' selected="selected"';
		$s.='>'.$nullTitle.'</option>';
		if(is_string($items)===true) {
			$db=core::db();
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
     * @return string
	 */
	public function getRadio(string $name,$items,string $value=null,string $html=null): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='';
		if(is_string($items)===true) {
			$db=core::db();
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
     * @return string
	 */
	public function getCheckbox(string $name,string $value=null,string $html=''): string {
		if(isset($_POST[$this->_namespace])===true) {
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
     * @return string
	 */
	public function getPassword(string $name,string $html=''): string {
		return '<input type="password" name="'.$this->_namespace.'['.$name.']" '.$html.' />';
	}

	/**
	 * Возвращает HTML-код многострочного поля ввода <textarea>
	 * @param string $name Имя поля
	 * @param string $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <textarea>
     * @return string
	 */
	public function getTextarea(string $name,string $value='',string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
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
     * @return string
	 */
	public function getEditor(string $name,string $value='',array $config=[]): string {
		$userGroup=core::userGroup();
		if($userGroup>=200 && isset($config['uploadTo'])===false) $config['uploadTo']='public/'; //для админки по умолчанию разрешить загружать изображения куда угодно в пределах директория /public
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace(array('&lt;','&gt;'),array('&amp;lt;','&amp;gt;'),$value);
		$html='<textarea name="'.$this->_namespace.'['.$name.']" id="'.$name.'"';
		if(isset($config['html'])===true && $config['html']) $html.=' '.$html;
		$html.='>'.$value.'</textarea>';
		if(isset($_GET['_lang'])===false) $html.=core::js('ckeditor/ckeditor');
		$html.='<script type="text/javascript">
		if(document.ckeditor===undefined) document.ckeditor=[];
		if(document.ckeditor["'.$name.'"]!==undefined) CKEDITOR.remove(document.ckeditor["'.$name.'"]);
		document.ckeditor["'.$name.'"]=CKEDITOR.replace("'.$name.'",{
			customConfig:"'.core::url();
		if(isset($_GET['_lang'])===true) $html.='admin/public/js/ckeditor-config.js'; else $html.='public/js/ckeditor-config.js';
		$html.='"';
		if(isset($config['uploadTo'])) {
			$html.=',uploadUrl:"'.core::url().'upload.php"';
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
     * @return string
	 */
	public function getDate(string $name,$value=null,string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
			$value=$_POST[$this->_namespace][$name];
		}
		if($value>0 && is_numeric($value)===true) $value=date('Y-m-d',$value);
		return '<input type="date" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').($html ? ' '.$html : '').' />';
	}

	/**
	 * Возвращает HTML-код поля выбора даты и времени <input type="datetime-local">
	 * Параметр $value может быть задан строкой или Timestamp.
	 * @param string $name Имя поля
	 * @param string|int|null $value Значение по умолчанию
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
     * @return string
	 */
	public function getDateTime(string $name,$value=null,string $html=''): string {
		if(isset($_POST[$this->_namespace])===true && isset($_POST[$this->_namespace][$name])===true) {
			$value=$_POST[$this->_namespace][$name];
		}
		if($value>0 && is_numeric($value)===true) $value=date('Y-m-d H:i:s',$value);
		return '<input type="datetime-local" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').($html ? ' '.$html : '').' />';
	}

	/**
	 * Возвращает HTML-код поля для загрузки файла/ов <input type="file">
	 * В Submit-действии контроллера файл будет доступен через параметр $data среди прочих POST-данных.
	 * @param string $name Имя поля
	 * @param bool $multiple Разрешить загрузку нескольких файлов
	 * @param string $html Произвольный HTML, который будет дописан к HTML-тегу <input>
     * @return string
	 */
	public function getFile(string $name,bool $multiple=false,string $html=''): string {
		if($multiple===true) {
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
     * @return string
	 */
	public function getSubmit(string $label=LNGContinue,string $name=null,string $html=''): string {
		$s='<input type="submit" value="'.$label.'"';
		if($name!==null) $s.=' name="'.$this->_namespace.'['.$name.']"';
		$s.=' class="button submit" '.$html.' />';
		return $s;
	}

}
