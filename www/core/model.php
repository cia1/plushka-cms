<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует универсальную модель */
class model {
	protected $_table; //имя таблицы базы данных
	protected $_data=array(); //содержит данные таблицы
	protected $_primary; //имя первичного ключа
	protected $db; //экземпляр класса базы данных (может быть отличным от заданного по умолчанию)
	protected $_multiLanguage=false; //если true, то будут выполнены запросы для всех языков
	protected $_languageDb; //true - мультиязычная таблица, array - мультиязычные поля, false - не мультиязычная таблица

	public function __construct($table=null,$db='db') {
		if($table) $this->_table=$table; else $this->_table=$_GET['corePath'][0];
		if($db==='db') $this->db=core::db();
		elseif($db==='sqlite') $this->db=core::sqlite();
		elseif($db==='mysql') $this->db=core::mysql();
		else $this->db=$db;
	}

	//Устанавливает мультиязычный режим
	public function multiLanguage() {
		$this->_multiLanguage=true;
	}

	/* Устанавливает значение поля $attribute таблицы БД */
	public function __set($attribute,$value) {
		$this->_data[$attribute]=$value;
	}

	/* Возвращает значение поля $attribute таблицы базы данных */
	public function __get($attribute) {
		if(isset($this->_data[$attribute])) return $this->_data[$attribute]; else return null;
	}

	/* Загружает данные для всех полей таблицы */
	public function set($data) {
		$this->_data=$data;
	}

	/* Возвращает массив данных всех полей таблицы */
	public function get() {
		return $this->_data;
	}

	//Очищает данные
	public function init() {
		$this->_data=array();
	}

	/* Загружает данные из БД по указанному условию
	$where - часть SQL-запроса после WHERE;
	$fieldList - список загружаемых полей: null - берётся из self::fieldList(), строка или массив - список полей
	ВАЖНО! В целом нужно анализировать _languageDb и добавлять суффикс языка к именам полей, но оставлю это для следующих версий.
	*/
	public function load($where,$fieldList=null) {
		if(!$fieldList) $fieldList=$this->fieldList('load');
		if(is_array($fieldList)) $fieldList=implde(',',$fieldList);
		if($this->_languageDb===null) $this->_setLanguageDb();
		$this->_data=$this->db->fetchArrayOnceAssoc('SELECT '.$fieldList.' FROM `'.$this->_table.($this->_languageDb===true ? '_'._LANG : '').'` WHERE '.$where);
		if(!$this->_data) return false;
		return true;
	}

	/* Загружает данные записи по первичному ключу */
	public function loadById($id,$fields=null) {
		$this->_data['id']=(int)$id;
		return $this->load('id='.$this->_data['id'],$fields);
	}

	/* Выполняет валидацию всех данных
	$validate - правила валидации: null - используется fieldList()+rule(), строка - используется только rule(), массив - fieldList() и rule() НЕ используются */
	public function validate($validate=null) {
		if($validate===null || is_string($validate)) {
			if($validate===null) {
				$validate=$this->fieldList('validate');
				if(is_string($validate)) $validate=explode(',',$validate);
			} else $validate=explode(',',$validate);
			$validate=array_intersect_key($this->rule(),array_combine($validate,$validate));
		}
		foreach($validate as $name=>$option) {
			if(!$this->_validateField($this->_data[$name],$name,$option)) return false;
		}
		if(!$this->_primary) $this->_primary=false; //обозначить, если первичного ключа нет в правилах
		return true;
	}

	/* Выполняет запрос INSERT или UPDATE (если есть значение первичного ключа)
	$validate - отвечает за предварительную валидацию: null или true - будет вызван self::validate(), array - содержит правила валидации, string - валидация не проводится, поле содержит список полей для INSERT/UPDATE;
	$primaryKeyName - имя первичного ключа (если не заданы правила валидации) - позволяет определить операцию INSERT/UPDATE: null - определяется автоматически, false - ключа нет, выполнить INSERT вместо UPDATE */
	public function save($validate=null,$primaryKeyName=null) {
		//Валидация
		if($validate===null || $validate===true || is_string($validate)) {
			if($validate===null || $validate===true) $validate=$this->fieldList('save');
			if(is_string($validate)) $validate=explode(',',$validate);
			if($validate[0]=='*') $validate=$this->rule();
			else $validate=array_intersect_key($this->rule(),array_combine($validate,$validate));
			if(!$this->validate($validate)) return false;
			foreach($validate as $key=>$null) {
				if($null[0]=='captcha') unset($validate[$key]);
			}
			$validate=array_keys($validate);
		} elseif(is_array($validate)) {
			if(!$this->validate($validate)) return false;
			foreach($validate as $key=>$null) {
				if($null[0]=='captcha') unset($validate[$key]);
				elseif($null[0]=='primary') $this->_primary=$key;
			}
			$validate=array_keys($validate);
		}
		//Поиск первичного ключа (если не был определён в методе validate() )
		if($primaryKeyName!==null) $this->_primary=$primaryKeyName;
		if(is_string($validate)) $validate=explode(',',$validate);
		if($this->_primary===null && $primaryKeyName!==false && method_exists($this,'rule')) { //ситуация: первичный ключ указан явно, но валидация не требуется
			$validate=$this->rule('save');
			foreach($validate as $id=>$null) {
				if($null[0]=='primary') {
					$this->_primary=$id;
					break;
				}
			}
			unset($null);
		}
		if($this->_primary && isset($this->_data[$this->_primary])) $id=$this->_data[$this->_primary];
		else $id=null;
		if($primaryKeyName!==false) { //Удалить первичный ключ из списка полей, за исключением случая, когда нужно выполнить INSERT с заранее определённым значением первичного ключа
			$i=array_search($this->_primary,$validate);
			if($i!==false) unset($validate[$i]);
		}
		if(method_exists($this,'beforeInsertUpdate')) if(!$this->beforeInsertUpdate($id,$validate)) return false;
		$this->_setLanguageDb(); //Подготовить данные о мультиязычности
		//А вот и сам SQL-запрос...
		if($this->_primary && $id) { //Среди полей есть первичный ключ и он задан явно или коссвено, значит нужно выполнить UPDATE
			return $this->_update($validate,$this->_primary,$id);
		} else { //Среди полей нет первичного ключа или он не задан явно или коссвено, значит выполнить INSERT
			return $this->_insert($validate,$this->_primary,$id);
		}
	}

	/* Удаляет запись по первичному ключу */
	public function delete($id=null,$affected=false) {
		if(!$id) $id=$this->id; else $id=(int)$id;
		if($this->_multiLanguage) {
			if($this->_languageDb===null) $this->_languageDb=$this->_setLanguageDb();
			if($this->_languageDb===true) $multiLanguage=true; else $multiLanguage=false;
		} else $multiLanguage=false;
		if($multiLanguage) {
			$lang=core::config();
			if(isset($lang['languageList'])) $lang=$lang['languageList']; else $lang=$lang['languageDefault'];
			foreach($lang as $item) {
				$this->db->query('DELETE FROM '.$this->_table.'_'.$item.' WHERE id='.$id);
			}
		} else $this->db->query('DELETE FROM '.$this->_table.' WHERE id='.$id);
		if($id==$this->id) $this->_data=array();
		if(!$affected) return true;
		if($this->db->affected()) return true; else return false;
	}

	protected function afterInsert($id=null) { return true; } //триггер, может быть перегружен
	protected function afterUpdate($id=null) { return true; } //триггер, может быть перегружен

	/* Выполняет валидацию одного поля
	$value - содержимое поля (по ссылке, т.к. _validateField также проводит фильтрацию);
	$name - имя поля;
	$options - параметры валидации ([0]=>тип,[1]=>заголовок,[2]=>может ли быть null,[min]=>минимальное,[max]=>максимальное */
	protected function _validateField(&$value,$name,$options) {
		if(!isset($options[2])) $options[2]=false;
		if($options[0]!='primary' && ($value===null || $value==='' || ($options[0]=='image' && !$value)) && $options[2]) {
			core::error(sprintf(LNGFieldCannotByEmpty,$options[1]));
			return false;
		}
		if($value===null && $options[0]!='primary') return true;
		//Валидация в зависимости от типа поля
		switch($options[0]) {
		case 'primary':
			$this->_primary=$name;
			if(!$value) $value=null;
			break;
		case 'integer':
			if($value==='') $value=null; else $value=(int)$value;
			if($value) {
				if(isset($options['min']) && $options['min']>$value) {
					core::error(sprintf(LNGFieldIllegalValue,$options[1]));
					return false;
				}
				if(isset($options['max']) && $options['max']<$value) {
					core::error(sprintf(LNGFieldIllegalValue,$options[1]));
					return false;
				}
			}
			break;
		case 'float':
			if($value==='') $value=null; else $value=(float)$value;
			if($value) {
				if(isset($options['min']) && $options['min']>$value) {
					core::error(sprintf(LNGFieldIllegalValue,$options[1]));
					return false;
				}
				if(isset($options['max']) && $options['max']<$value) {
					core::error(sprintf(LNGFieldIllegalValue,$options[1]));
					return false;
				}
			}
			break;
		case 'boolean':
			if($value) $value=true; else $value=false;
			break;
		case 'date':
			if($value==='') {
				$value=null;
				break;
			}
			if(!is_numeric($value)) $value=strtotime($value);
			if(!$value) {
				core::error(sprintf(LNGFieldHasBeDate,$options[1]));
				return false;
			}
			break;
		case 'string':
			$value=strip_tags($value);
			if(!isset($options['trim']) || $options['trim']) $value=trim($value);
			if($value) {
				if(isset($options['min']) && $options['min']>mb_strlen($value,'UTF-8')) {
					core::error(sprintf(LNGFieldTextTooShort,$options[1]));
					return false;
				}
				if(isset($options['max']) && $options['max']<mb_strlen($value,'UTF-8')) {
					core::error(sprintf(LNGFieldTextTooLong,$options[1]));
					return false;
				}
			}
			break;
		case 'html':
			break;
		case 'latin':
			$i=preg_match('/^[a-zA-Z0-9\-_]*?$/',$value);
			if(!$i) {
				core::error(sprintf(LNGFieldCanByLatin,$options[1]));
				return false;
			}
			if(isset($options['max']) && strlen($value)>$options['max']) {
				core::error(sprintf(LNGFieldIllegalValue,$options[1]));
				return false;
			}
			break;
		case 'email':
			if($value) {
				$i=preg_match('/^[-a-z0-9!#$%&\'*+\/=?^_`{|}~]+(?:\.[-a-z0-9!#$%&\'*+\/=?^_`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/',$value);
				if(!$i) {
					core::error(sprintf(LNGFieldHasBeEMail,$options[1]));
					return false;
				}
			}
			break;
		case 'regular':
			if($value) {
				$i=preg_match('%'.$options[3].'%',$value);
				if(!$i) {
					core::error(sprintf(LNGFieldIllegalValue,$options[1]));
					return false;
				}
			}
			break;
		case 'captcha':
			if((int)$value!==$_SESSION['captcha']) {
				core::error($options[1].' '.LNGwroteWrong);
				return;
			}
			break;
		case 'callback':
			$value=call_user_func_array($options[3],array($value,$name));
			if(core::error()) return false;
			break;
		case 'image': //options[]: 'minWidth','minHeight','maxWidth','maxHeight'
			core::import('core/picture');
			if(is_array($value) && !$options[2] && !$value['size']) {
				$value=null;
				return true;
			}
			if(is_array($value) && isset($value[0])) foreach($value as $i=>$item) {
				$item=self::_picture($item,$options);
				if(!$item) return false;
				$value[$i]=$item;
			} else {
				$value=self::_picture($value,$options);
				if(!$value) return false;
			}
		}
		return true;
	}

	private function _picture($image,$option) {
		$picture=new picture($image);
		if(core::error()) return false;
		if(isset($option['minWidth']) || isset($option['maxWidth'])) {
			$w=$picture->width();
			$h=$picture->height();
			if(isset($option['minWidth']) && $w<$option['minWidth']) {
				core::error(sprintf(LNGImageWidthCannotBeLessPixels,$option['minWidth']));
				return false;
			}
			if(isset($option['maxWidth']) && $w>$option['maxWidth']) {
				core::error(sprintf(LNGImageWidthCannotBeMorePixels,$option['maxWidth']));
				return false;
			}
			if(isset($option['minHeight']) && $h<$option['minHeight']) {
				core::error(sprintf(LNGImageHeightCannotBeLessPixels,$option['minHeight']));
				return false;
			}
			if(isset($option['maxHeight']) && $h>$option['maxHeight']) {
				core::error(sprintf(LNGImageHeightCannotBeMorePixels,$option['maxHeight']));
				return false;
			}
		}
		return $picture;
	}

	//Возвращает информацию о мультиязычных таблицах
	private function _setLanguageDb() {
		$f=core::path().'cache/language-database.php';
		if(!file_exists($f)) {
			core::import('core/cache');
			cache::languageDatabase();
		}
		$lang=core::config('../cache/language-database');
		if(isset($lang[$this->_table])) {
			$this->_languageDb=$lang[$this->_table];
		} else $this->_languageDb=false;
		return $this->_languageDb;
	}

	//Собирает и выполняет SQL-запрос INSERT
	private function _insert($fieldList,$primary) {
		if($this->_languageDb) {
			$languageList=core::config();
			$languageList=$languageList['languageList'];
		}
		$s1=$s2='';
		foreach($fieldList as $field) {
			if($this->_data[$field]===null) continue;
			$value=$this->db->escape($this->_data[$field]);
			if(is_array($this->_languageDb) && in_array($field,$this->_languageDb)) { //это поле является мультиязычным
				if($this->_multiLanguage) {
					foreach($languageList as $lang) { //добавить поля для каждого языка
						if($lang==_LANG) continue;
						if($s1) {
							$s1.=',`'.$field.'_'.$lang.'`';
							$s2.=','.$value;
						} else {
							$s1='`'.$field.'_'.$lang.'`';
							$s2=$value;
						}
					}
				}
				$field.='_'._LANG;
			}
			if($s1) {
				$s1.=',`'.$field.'`';
				$s2.=','.$value;
			} else {
				$s1='`'.$field.'`';
				$s2=$value;
			}
		}
		//Если мультиязычная таблица (_langDb===true), то выполнить несколько запросов
		if($this->_multiLanguage && $this->_languageDb===true) {
			foreach($languageList as $i=>$item) {
				if(!$i) { //первичный ключ определить только один раз
					$query='INSERT INTO `'.$this->_table.'_'.$item.'` ('.$s1.') VALUES ('.$s2.')';
					if(!$this->db->query($query)) return false;
					$this->_data[$primary]=$this->db->insertId(); //обновить значение первичного ключа
				} else {
					$s1.=',`'.$primary.'`';
					$s2.=','.$this->db->escape($this->_data[$primary]);
					$query='INSERT INTO `'.$this->_table.'_'.$item.'` ('.$s1.') VALUES ('.$s2.')';
//var_dump($query);exit;
					if(!$this->db->query($query)) return false;
				}
			}
		} else {
			$query='INSERT INTO `'.$this->_table.'` ('.$s1.') VALUES ('.$s2.')';
//var_dump($query);exit;
			if(!$this->db->query($query)) return false;
			if($primary) {
				$this->_data[$primary]=$this->db->insertId(); //обновить значение первичного ключа
			}
		}
		if($primary) return $this->afterInsert($this->$primary); //триггер "после INSERT"
		else return $this->afterInsert(); //триггер "после INSERT"
	}

	//Собирает и выполняет SQL-запрос UPDATE
	private function _update($fieldList,$primary,$id) {
		$s='';
		foreach($fieldList as $name) {
			if($s) $s.=',';
			$s.='`'.$name;
			if($this->_multiLanguage && is_array($this->_languageDb) && in_array($name,$this->_languageDb)) $s.='_'._LANG;
			$s.='`='.($this->_data[$name]===null ? 'null' : $this->db->escape($this->_data[$name]));
		}
		if($this->_languageDb===true) $s='UPDATE `'.$this->_table.'_'._LANG.'` SET '.$s.' WHERE '.$primary.'='.$this->db->escape($id);
		else $s='UPDATE `'.$this->_table.'` SET '.$s.' WHERE '.$primary.'='.$this->db->escape($id);
//var_dumP($s);exit;
		if(!$this->db->query($s)) return false;
		return $this->afterUpdate($this->$primary); //триггер "после UPDATE"
	}

}