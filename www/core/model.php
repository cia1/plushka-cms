<?php
core::import('core/validator');
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует универсальную модель */
class model extends validator {
	protected $_table; //имя таблицы базы данных
	protected $primaryAttribute; //имя первичного ключа
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
	public function multiLanguage($value=true) {
		$this->_multiLanguage=$value;
	}

	/* Загружает данные из БД по указанному условию
	$where - часть SQL-запроса после WHERE;
	$fieldList - список загружаемых полей: null - берётся из self::fieldList(), строка или массив - список полей
	ВАЖНО! В целом нужно анализировать _languageDb и добавлять суффикс языка к именам полей, но оставлю это для следующих версий.
	*/
	public function load($where,$fieldList=null) {
		if(!$fieldList) $fieldList=$this->fieldList('load');
		if($this->_languageDb===null) $this->_setLanguageDb();
		if(is_array($this->_languageDb)) {
			if($fieldList==='*') $fieldList=array_keys($this->rule());
			if(is_string($fieldList)) $fieldList=explode(',',$fieldList);
			$s='';
			foreach($fieldList as $item) {
				if($s) $s.=',';
				$s.=$item;
				if(in_array($item,$this->_languageDb)) $s.='_'._LANG.' '.$item;
			}
			$fieldList=$s;
			unset($s);
		}
		if(is_array($fieldList)) $fieldList=implde(',',$fieldList);
		$this->_data=$this->db->fetchArrayOnceAssoc('SELECT '.$fieldList.' FROM `'.$this->_table.($this->_languageDb===true ? '_'._LANG : '').'` WHERE '.$where);
		if(!$this->_data) return false;
		return true;
	}

	/* Загружает данные записи по первичному ключу */
	public function loadById($id,$fields=null) {
		$this->_data['id']=(int)$id;
		return $this->load('id='.$this->_data['id'],$fields);
	}

	/*
	Выполняет валидацию всех данных
	$validate - правила валидации: null - используется fieldList()+rule(), массив - воспринимается как правила валидации
	*/
	public function validate($rule=null) {
		if($rule===null) {
			$rule=explode(',',$this->fieldList('save'));
			$rule=array_intersect_key($this->rule(),array_combine($rule,$rule));
		}
		if(parent::validate($rule)===false) return false;
		if(!$this->primaryAttribute) $this->primaryAttribute=false; //обозначить, если первичного ключа нет в правилах
		return true;
	}

	/* Выполняет запрос INSERT или UPDATE (если есть значение первичного ключа)
	$validate - отвечает за предварительную валидацию: null или true - будет вызван self::validate(), array - содержит правила валидации, string - валидация не проводится, поле содержит список полей для INSERT/UPDATE;
	$primaryAttribute - имя первичного ключа (если не заданы правила валидации) - позволяет определить операцию INSERT/UPDATE: null - определяется автоматически, false - ключа нет, выполнить INSERT вместо UPDATE */
	public function save($validate=null,$primaryAttribute=null) {
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
			foreach($validate as $attribute=>$setting) {
				if($setting[0]=='captcha') unset($validate[$attribute]);
				elseif($setting[0]=='primary') $this->primaryAttribute=$attribute;
			}
			$validate=array_keys($validate);
		}
		//Поиск первичного ключа (если не был определён в методе validate() )
		if($primaryAttribute!==null) $this->primaryAttribute=$primaryAttribute;
//		if(is_string($validate)) $validate=explode(',',$validate);
		if($this->primaryAttribute===null && $primaryAttribute!==false && method_exists($this,'rule')) { //ситуация: первичный ключ указан явно, но валидация не требуется
			$validate=$this->rule('save');
			foreach($validate as $attribute=>$setting) {
				if($setting[0]==='primary') {
					$this->primaryAttribute=$attribute;
					break;
				}
			}
			unset($null);
		}
		if($this->primaryAttribute && isset($this->_data[$this->primaryAttribute])) $id=$this->_data[$this->primaryAttribute];
		else $id=null;
		if($primaryAttribute!==false) { //Удалить первичный ключ из списка полей, за исключением случая, когда нужно выполнить INSERT с заранее определённым значением первичного ключа
			$i=array_search($this->primaryAttribute,$validate);
			if($i!==false) unset($validate[$i]);
		}
		if(method_exists($this,'beforeInsertUpdate')) if($this->beforeInsertUpdate($id,$validate)===false) return false;
		$this->_setLanguageDb(); //Подготовить данные о мультиязычности
		//А вот и сам SQL-запрос...
		if($this->primaryAttribute && $id) { //Среди полей есть первичный ключ и он задан явно или коссвено, значит нужно выполнить UPDATE
			return $this->_update($validate,$this->primaryAttribute,$id);
		} else { //Среди полей нет первичного ключа или он не задан явно или коссвено, значит выполнить INSERT
			return $this->_insert($validate,$this->primaryAttribute,$id);
		}
	}

	/* Удаляет запись по первичному ключу */
	public function delete($id=null,$affected=false) {
		if(!$id) $id=$this->id; else $id=(int)$id;
		if($this->_multiLanguage) {
			if($this->_languageDb===null) $this->_setLanguageDb();
		} else $multiLanguage=false;
		if($this->_languageDb===true) {
			$lang=core::config('_core','languageList');
			foreach($lang as $item) {
				$this->db->query('DELETE FROM '.$this->_table.'_'.$item.' WHERE id='.$id);
			}
		} else $this->db->query('DELETE FROM '.$this->_table.' WHERE id='.$id);
		if($id==$this->id) $this->init();
		if($affected===false) return true;
		if($this->db->affected()) return true; else return false;
	}

	protected function afterInsert($id=null) { return true; } //триггер, может быть перегружен
	protected function afterUpdate($id=null) { return true; } //триггер, может быть перегружен

	protected function validatePrimary($attribute,$setting) {
		$this->primaryAttribute=$attribute;
		if(!$this->_data[$attribute]) $this->_data[$attribute]=null;
		return true;
	}

	//Возвращает информацию о мультиязычных таблицах:
	//false - не мультиязычная, true - мультиязычная таблица, array - список полей
	private function _setLanguageDb() {
		$f=core::path().'cache/language-database.php';
		if(!file_exists($f)) {
			core::import('core/cache');
			cache::languageDatabase();
		}
		$lang=core::config('../cache/language-database',$this->_table);
		if($lang===null) $lang=false;
		$this->_languageDb=$lang;
	}

	//Собирает и выполняет SQL-запрос INSERT
	private function _insert($fieldList,$primary) {
		if($this->_languageDb) {
			$languageList=core::config();
			$languageList=$languageList['languageList'];
		}
		$s1=$s2='';
		foreach($fieldList as $field) {
			$value=$this->_data[$field];
			if($value===null) continue;
			if(in_array($field,$this->_bool)) {
				$value=($value ? '1' : '0');
			} else $value=$this->db->escape($this->_data[$field]);
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
		//Если мультиязычная таблица (_languageDb===true), то выполнить несколько запросов
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
			if($this->_data[$name]===null) $value='null';
			elseif($this->_data[$name]===true) $value='1';
			elseif($this->_data[$name]===false) $value='0';
			else $value=$this->db->escape($this->_data[$name]);
			$s.='`='.$value;
		}
		if($this->_languageDb===true) $s='UPDATE `'.$this->_table.'_'._LANG.'` SET '.$s.' WHERE '.$primary.'='.$this->db->escape($id);
		else $s='UPDATE `'.$this->_table.'` SET '.$s.' WHERE '.$primary.'='.$this->db->escape($id);
//var_dumP($s);exit;
		if(!$this->db->query($s)) return false;
		return $this->afterUpdate($this->$primary); //триггер "после UPDATE"
	}

}