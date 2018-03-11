<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Конструктор HTML-форм. Автоматически подставляет значения из $_POST, если такие имеются. */
class form {
	protected $_namespace; //имя контроллера-получателя
	protected $_data='';
	public $action; //параметр "action" тега <form>
	public $method='post'; //метод отправки формы (get или post)

	/* $namespace - имя контроллера получателя */
	public function __construct($namespace=null) {
		if($namespace) $this->_namespace=$namespace; else $this->_namespace=$_GET['corePath'][0];
	}

	/* Скрытое поле (<input type="hidden">) */
	public function hidden($name,$value,$html='') {
		$this->_data.=$this->getHidden($name,$value,$html);
	}

	/* Текстовая надпись, не является полем формы */
	public function label($label,$value) {
		$this->_data.='<dt class="label">'.$label.':</dt><dd class="label">'.$value.'</dd>';
	}

	/* Текстовое поле
	$name - имя поля, $label - заголовок рядом с полем, $value - значение по умолчанию, $html - произвольный текст, который нужно добавить к тегу <input> */
	public function text($name,$label,$value='',$html='') {
		$this->_data.='<dt class="text '.$name.'">'.$label.':</dt><dd class="text '.$name.'">'.$this->getText($name,$value,$html).'</dd>';
	}

	/* Поле ввода числа
	$name - имя поля, $label - заголовок рядом с полем, $value - значение по умолчанию, $html - произвольный текст, который нужно добавить к тегу <input> */
	public function number($name,$label,$value='',$html='') {
		$this->_data.='<dt class="text number '.$name.'">'.$label.':</dt><dd class="text number '.$name.'">'.$this->getNumber($name,$value,$html).'</dd>';
	}

	/* Поле ввода e-mail адреса
	$name - имя поля, $label - заголовок рядом с полем, $value - значение по умолчанию, $html - произвольный текст, ктороый нужно добавить к тегу <input> */
	public function email($name,$label,$value='',$html='') {
		$this->_data.='<dt class="text email '.$name.'">'.$label.':</dt><dd class="text email '.$name.'">'.$this->getEmail($name,$value,$html).'</dd>';
	}

	/* Выпадающий список
	$name - имя поля формы, $label - заголовок рядом со списком, $items - массив значений, $value - значение по умолчанию, $nullTitle - заголовок "пустого значения", $html - произвольный текст, который будет добавлен к тегу <select> */
	public function select($name,$label,$items,$value=null,$nullTitle=null,$html='') {
		$this->_data.='<dt class="select '.$name.'">'.$label.':</dt><dd class="select '.$name.'">'.$this->getSelect($name,$items,$value,$nullTitle,$html).'</dd>';
	}

	/* Многострочный список
	$name - имя поля формы, $label - отображаемый заголовок, $items - массив возможных значений, $value - значение по умолчанию, $nullTitle - заголовок "пустого значения", $html - произвольный текст, который будет присоединён к тегу <select> */
	public function listBox($name,$label,$items,$value=null,$nullTitle=null,$html='') {
		$this->_data.='<dt class="list '.$name.'">'.$label.':</dt><dd class="list '.$name.'">'.$this->getListBox($name,$items,$value,$nullTitle,$html).'</dd>';
	}

	/* Группа переключателей
	$name - имя поля формы, $label - отображаемый заголовок, $items - массив возможных значений, $value - значение по умолчанию */
	public function radio($name,$label,$items,$value=null) {
		$this->_data.='<dt class="radio '.$name.'">'.$label.':</dt><dd class="radio '.$name.'">'.$this->getRadio($name,$items,$value).'</dd>';
	}

	/* Чекбокс */
	public function checkbox($name,$label,$value=null,$html='') {
		$this->_data.='<label><dt class="checkbox '.$name.'">'.$label.':</dt><dd class="checkbox '.$name.'">'.$this->getCheckbox($name,$value,$html).'</dd></label>';
	}

	/* Поле <input> для ввода пароля */
	public function password($name,$label,$html='') {
		$this->_data.='<dt class="password '.$name.'">'.$label.':</dt><dd class="password '.$name.'">'.$this->getPassword($name,$html).'</dd>';
	}

	/* Поле многостраничного текста (<textarea>) */
	public function textarea($name,$label,$value='',$html='') {
		$this->_data.='<dt class="textarea '.$name.'">'.$label.':</dt><dd class="textarea '.$name.'">'.$this->getTextarea($name,$value,$html).'</dd>';
	}

	/* Поле с редактором CKEditor */
	public function editor($name,$label,$value='',$config=array()) {
		$this->_data.='<dt class="textarea '.$name.'">'.$label.':</dt><dd class="textarea '.$name.'">'.$this->getEditor($name,$value,$config).'</dd>';
	}

	/* Поле выбора даты */
	public function date($name,$label,$value=null,$html='') {
		$this->_data.='<dt class="text date '.$name.'">'.$label.':</dt><dd class="text date '.$name.'">'.$this->getDate($name,$value,$html).'</dd>';
	}

	//Поле выбора даты и времени
	public function dateTime($name,$label,$value,$html='') {
		$this->_data.='<dt class="text date time '.$name.'">'.$label.':</dt><dd class="text date '.$name.'">'.$this->getDateTime($name,$value,$html).'</dd>';
	}

	/* Поле для загрузки файла (<input type="file")
	$name - имя поля формы, $label - отображаемый заголовок, $multiple - разрешить выбирать несколько файлов, $html - произвольный текст, который будет присоединён к тегу <input> */
	public function file($name,$label,$multiple=false,$html='') {
		$this->_data.='<dt class="file">'.$label.':</dt><dd class="file">'.$this->getFile($name,$multiple,$html).'</dd>';
	}

	/* Поле для ввода проверочного кода */
	public function captcha($name,$label,$html='') {
		$this->_data.='<dt class="captcha">'.$label.':<img src="'.core::url().'captcha.php" alt="'.strip_tags($label).'" title="'.strip_tags($label).'"></dt><dd class="captcha"><input type="text" name="'.$this->_namespace.'['.$name.']" '.$html.' /></dd>';
	}

	/* Кнопка "сбросить" (<input type="reset">) */
	public function reset($label,$html='') {
		$this->_data.='<input type="reset" value="'.$label.'" class="button reset" '.$html.' />';
	}

	/* Кнопка "отправить" (<input type="submit") */
	public function submit($label=LNGContinue,$name=null,$html='') {
		$this->_data.='<dd class="submit">'.$this->getSubmit($label,$name,$html).'</dd>';
	}

	/* Добавляет в форму произвольный HTML-код */
	public function html($html) { $this->_data.=$html; }

	/* Выводит HTML-представление формы
	$action - параметр "action" тега <form>, $html - произвольный код, который будет присоединён к тегу <form> */
	public function render($action=null,$html=null) {
		if($action) $this->action=$action;
		echo '<form action="'.($this->action ? core::link($this->action) : $_SERVER['REQUEST_URI']).'" method="'.$this->method.'" enctype="multipart/form-data" name="'.$this->_namespace.'" class="'.$this->_namespace.'" '.$html.'>
		<dl class="form">';
		echo $this->_data;
		unset($this->_data);
		echo '</dl></form>';
	}

	/* Возвращает HTML-код скрытого поля */
	public function getHidden($name,$value,$html='') {
		return '<input type="hidden" name="'.$this->_namespace.'['.$name.']" value="'.$value.'" '.$html.' />';
	}

	/* Возвращает HTML-код текстового поля */
	public function getText($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace('"','&quot;',$value);
		return '<input type="text" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').' '.$html.' />';
	}

	/* Возвращает HTML-код поля ввода числа */
	public function getNumber($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace('"','&quot;',$value);
		return '<input type="number" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').' '.$html.' />';
	}

	//Возвращает HTML-код поля ввода адреса электронной почты
	public function getEmail($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace('"','&quot;',$value);
		return '<input type="email" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').' '.$html.' />';
	}

	/* Возвращает HTML-код выпадающего списка */
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

	/* Возвращает HTML-код списка */
	public function getListBox($name,$items,$value=null,$nullTitle=null,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='<select name="'.$this->_namespace.'['.$name.']" size="8" '.$html.'>';
		if($nullTitle!==null) $s.='<option value=""';
		if($value===null || $value==='') $s.=' selected="selected"';
		$s.='>'.$nullTitle.'</option>';
		if(is_string($items)) {
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

	/* Возвращает HTML-код */
	public function getRadio($name,$items,$value=null) {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$s='';
		if(is_string($items)) {
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
			$s.=' />'.$item[1].'</label>';
		}
		return $s;
	}

	/* Возвращает HTML-код чекбокса */
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

	/* Возвращает HTML-код поля для ввода пароля */
	public function getPassword($name,$html='') {
		return '<input type="password" name="'.$this->_namespace.'['.$name.']" '.$html.' />';
	}

	/* Возвращает HTML-код поля для ввода многострочного текста */
	public function getTextarea($name,$value='',$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		return '<textarea name="'.$this->_namespace.'['.$name.']" '.$html.'>'.$value.'</textarea>';
	}

	/* Возвращает HTML-код визуального редактора */
	public function getEditor($name,$value='',$config=array()) {
		$userGroup=core::userGroup();
		if(!$config) $config=array();
		if($userGroup>=200 && !isset($config['uploadTo'])) $config['uploadTo']='public/'; //для админки по умолчанию разрешить загружать изображения куда угодно в пределах директория /public
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		$value=str_replace(array('&lt;','&gt;'),array('&amp;lt;','&amp;gt;'),$value);
		$html='<textarea name="'.$this->_namespace.'['.$name.']" id="'.$name.'"'.(isset($config['html']) && $config['html'] ? ' '.$html : '').'>'.$value.'</textarea>';
		if(!isset($_GET['_lang'])) $html.=core::js('ckeditor/ckeditor');
		$html.='<script>
		if(document.ckeditor==undefined) document.ckeditor=new Array();
		if(document.ckeditor["'.$name.'"]!=undefined) CKEDITOR.remove(document.ckeditor["'.$name.'"]);
		document.ckeditor["'.$name.'"]=CKEDITOR.replace("'.$name.'",{
			customConfig:"'.(isset($_GET['_lang']) ? core::url().'admin/public/js/ckeditor-config.js' : core::url().'public/js/ckeditor-config.js').'"';
		if(isset($config['uploadTo'])) {
			$html.=',uploadUrl:"'.core::url().'upload.php"';
//				',filebrowserUploadUrl:"'.core::url().$config['uploadTo'].'"';
			$_SESSION['_uploadFolder']=$config['uploadTo']; //запомнить, куда разрешено загружать файлы, поддерживается только один директорий
		}
		$html.='});</script>';
		return $html;
	}

	/* Возвращает HTML-код выбора даты */
	public function getDate($name,$value=null,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		if($value && is_numeric($value)) $value=date('Y-m-d',$value);
		return '<input type="date" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').($html ? ' '.$html : '').' />';
	}

	//Возвращает HTML-код поля для выбора даты и времени
	public function getDateTime($name,$value,$html='') {
		if(isset($_POST[$this->_namespace]) && isset($_POST[$this->_namespace][$name])) {
			$value=$_POST[$this->_namespace][$name];
		}
		if($value && is_numeric($value)) $value=date('Y-m-d H:i:s',$value);
		return '<input type="datetime-local" name="'.$this->_namespace.'['.$name.']"'.($value ? ' value="'.$value.'"' : '').($html ? ' '.$html : '').' />';
	}

	/* Возвращает HTML-код поля для загрузки файла */
	public function getFile($name,$multiple=false,$html='') {
		if($multiple) {
			$name.='][';
			$html='multiple="true" '.$html;
		}
		return '<input type="file" name="'.$this->_namespace.'['.$name.']" '.$html.' />';
	}

	/* Возвращает HTML-код submit-кнопки */
	public function getSubmit($label=LNGContinue,$name=null,$html='') {
		$s='<input type="submit" value="'.$label.'"';
		if($name) $s.=' name="'.$this->_namespace.'['.$name.']"';
		$s.=' class="button submit" '.$html.' />';
		return $s;
	}

}