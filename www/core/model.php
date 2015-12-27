<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
/* Реализует универсальную модель */
class model {
	protected $_table; //имя таблицы базы данных
	protected $_data=array(); //содержит данные таблицы
	protected $_primary; //имя первичного ключа
	protected $db; //экземпляр класса базы данных (может быть отличным от заданного по умолчанию)

	public function __construct($namespace=null,$db='db') {
		if($namespace) $this->_table=$namespace; else $this->_table=$_GET['corePath'][0];
		if($db==='db') $this->db=core::db();
		elseif($db==='sqlite') $this->db=core::sqlite();
		elseif($db==='mysql') $this->db=core::mysql();
		else $this->db=$db;
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

	/* Загружает данные из БД по указанному условию */
	public function &load($where,$fields=null) {
		if(!$fields) $fields=$this->fields;
		$this->_data=$this->db->fetchArrayOnceAssoc('SELECT '.$fields.' FROM `'.$this->_table.'` WHERE '.$where);
		if(!$this->_data) return false;
		return $this->_data;
	}

	/* Загружает данные записи по первичному ключу */
	public function &loadById($id,$fields=null) {
		$this->_data['id']=(int)$id;
		return $this->load('id='.$this->_data['id'],$fields);
	}

	/* Выполняет валидацию всех данных
	$validate - правила валидации */
	public function validate($validate=null) {
		if(!$validate && method_exists($this,'validateRule')) $validate=$this->validateRule();
		foreach($validate as $name=>$options) {
			if(!$this->_validateField($this->_data[$name],$name,$options)) return false;
		}
		return true;
	}

	/* Выполняет запрос INSERT или UPDATE (если есть значение первичного ключа)
	$validate - отвечает за предварительную валидацию, $fields - список полей для обновления (если нет валидации), $id - значение первичного ключа (если нет валидации)
	ВНИМАНИЕ! Если передан список полей в первом параметре, то среди этого списка должен быть первичный ключ */
	public function save($validate=true,$fields=null,$id=null) {
		if($validate===true) $validate=$this->validateRule();
		elseif(is_string($validate)) { //если строка, значит содержит список полей
			$fields=explode(',',$validate);
			//в правила валидации взять только указанные поля
			$tmpRule=$this->validateRule();
			$validate=array();
			foreach($fields as $item) $validate[$item]=$tmpRule[$item];
			unset($tmpRule);
		}
		if($validate!==false) { //может содержать false, true или массив с правилами валидации
			if(!$this->validate($validate,$fields)) return false;
		} else $this->searchPrimary($this->validateRule()); //поиск первичного ключа и установка его имени в $this->_primary
		if(!$fields) { //Если список полей не задан явно, то извлечь его из правил валидации
			if(is_array($validate)) $fields=array_keys($validate);
			elseif($validate===false) $fields=array_keys($this->validateRule());
			else $fields=$validate;
		} elseif(is_string($fields)) $fields=explode(',',$fields);
		$primary=$this->_primary; //название первичного ключа (просто синоним для удобства).
		//Если ID задан явно (не NULL), то восспринимать его как ключ возможно существующей записи, однако, в INSERT и UPDATE использовать ключ, заданный коссвенно (в списке полей).
		if($id===null) $id=$this->_data[$primary];
		//Триггер "до INSERT/UPDATE"
		if(method_exists($this,'beforeInsertUpdate')) if(!$this->beforeInsertUpdate($id,$fields)) return false;

		//А вот и сам SQL-запрос...
		$db=core::db();
		$s='';
		if($primary && $id) { //Среди полей есть первичный ключ и он задан явно или коссвено, значит нужно выполнить UPDATE
			foreach($fields as $name) {
				if(isset($validate[$name])) $options=$validate[$name]; else $options=null;
				if(($options[0]=='primary' && $id==$this->_data[$primary]) || $options[0]=='captcha') continue; //Пропустить каптчу, а также первичный ключ, если он совпадает с явно заданным (нет необходимости обновлять первичный ключ).
				if($options[0]=='boolean') if(!$this->_data[$name]) $this->_data[$name]='0'; else $this->_data[$name]='1';
				if($s) $s.=',`'.$name.'`='.($this->_data[$name]===null ? 'null' : $this->db->escape($this->_data[$name])) ; else $s='`'.$name.'`='.($this->_data[$name]===null ? 'null' : $this->db->escape($this->_data[$name]));
			}
			if(!$this->db->query('UPDATE `'.$this->_table.'` SET '.$s.' WHERE '.$primary.'='.$db->escape($id))) return false;
			return $this->afterUpdate($this->$primary); //триггер "после UPDATE"
		} else { //Среди полей нет первичного ключа или он не задан явно или коссвено, значит выполнить INSERT
			$s1=$s2='';
			foreach($fields as $name) {
				if(isset($validate[$name])) $options=$validate[$name]; else $options=null;
				if(($options[0]=='primary' && !$this->_data[$primary]) || $options[0]=='captcha') continue; //Пропустить каптчу, а также первичный ключ, если он не задан (должен быть сформирован автоматически).
				if($options[0]=='boolean') if(!$this->_data[$name]) $this->_data[$name]='0'; else $this->_data[$name]='1';
				if($this->_data[$name]===null) if(isset($options['default'])) $this->_data[$name]=$options['default']; else continue;
				if($s1) {
					$s1.=',`'.$name.'`';
					$s2.=','.($this->_data[$name]===null ? 'null' : $this->db->escape($this->_data[$name]));
				} else {
					$s1='`'.$name.'`';
					$s2=($this->_data[$name]===null ? 'null' : $this->db->escape($this->_data[$name]));
				}
			}
//var_dump('INSERT INTO `'.$this->_table.'` ('.$s1.') VALUES ('.$s2.')');
			if(!$this->db->query('INSERT INTO `'.$this->_table.'` ('.$s1.') VALUES ('.$s2.')')) return false;
			if($primary) {
				$this->_data[$primary]=$this->db->insertId(); //обновить значение первичного ключа
				return $this->afterInsert($this->$primary); //триггер "после INSERT"
			} else return $this->afterInsert(); //триггер "после INSERT"
		}
	}

	/* Удаляет запись по первичному ключу */
	public function delete($id=null,$affected=false) {
		if(!$id) $id=$this->id; else $id=(int)$id;
		$this->db->query('DELETE FROM '.$this->_table.' WHERE id='.$id);
		if($id==$this->id) $this->_data=array();
		if(!$affected) return true;
		if($this->db->affected()) return true; else return false;
	}

	/* Возвращает имя поля, являющегося первичным ключом в правилах валидации $data */
	protected function searchPrimary($data) {
		foreach($data as $index=>$options) {
			if($options[0]=='primary') {
				$this->_primary=$index;
				return true;
			}
		}
		return false;
	}

	protected function afterInsert($id=null) { return true; } //триггер, может быть перегружен
	protected function afterUpdate($id=null) { return true; } //триггер, может быть перегружен

	/* Выполняет валидацию одного поля
	$value - содержимое поля (значение), $name - имя поля, $options - параметры валидации ([0]=>тип,[1]=>заголвок,[2]=>может ли быть null,[min]=>минимальное,[max]=>максимальное */
	protected function _validateField(&$value,$name,$options) {
		if(!isset($options[2])) $options[2]=false;
		if($options[0]!='primary' && ($value===null || $value==='') && $options[2]) {
			controller::$error=sprintf(LNGFieldCannotByEmpty,$options[1]);
			return false;
		}
		//Валидация в зависимости от типа поля
		switch($options[0]) {
		case 'primary':
			$this->_primary=$name;
			if(!$value) $value=null;
			break;
		case 'id':
			if(!$value) $value=(int)$value;
			break;
		case 'integer':
			if($value==='') $value=null; else $value=(int)$value;
			if($value) {
				if(isset($options['min']) && $options['min']>$value) {
					controller::$error=sprintf(LNGFieldIllegalValue,$options[1]);
					return false;
				}
				if(isset($options['max']) && $options['max']<$value) {
					controller::$error=sprintf(LNGFieldIllegalValue,$options[1]);
					return false;
				}
			}
			break;
		case 'float':
			if($value==='') $value=null; else $value=(float)$value;
			if($value) {
				if(isset($options['min']) && $options['min']>$value) {
					controller::$error=sprintf(LNGFieldIllegalValue,$options[1]);
					return false;
				}
				if(isset($options['max']) && $options['max']<$value) {
					controller::$error=sprintf(LNGFieldIllegalValue,$options[1]);
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
				controller::$error=sprintf(LNGFieldHasBeDate,$options[1]);
				return false;
			}
			break;
		case 'string':
			$value=strip_tags($value);
			if(!isset($options['trim']) || $options['trim']) $value=trim($value);
			if($value) {
				if(isset($options['min']) && $options['min']>strlen($value)) {
					controller::$error=sprintf(LNGFieldTextTooShort,$options[1]);
					return false;
				}
				if(isset($options['max']) && $options['max']<strlen($value)) {
					controller::$error=sprintf(LNGFieldTextTooLong,$options[1]);
					return false;
				}
			}
			break;
		case 'html':
			break;
		case 'latin':
			$i=preg_match('/^[a-zA-Z0-9\-_]*?$/',$value);
			if(!$i) {
				controller::$error=sprintf(LNGFieldCanByLatin,$options[1]);
				return false;
			}
			if(isset($options['max']) && strlen($value)>$options['max']) {
				controller::$error=sprintf(LNGFieldIllegalValue,$options[1]);
				return false;
			}
			break;
		case 'email':
			if($value) {
				$i=preg_match('/^[-a-z0-9!#$%&\'*+\/=?^_`{|}~]+(?:\.[-a-z0-9!#$%&\'*+\/=?^_`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/',$value);
				if(!$i) {
					controller::$error=sprintf(LNGFieldHasBeEMail,$options[1]);
					return false;
				}
			}
			break;
		case 'regular':
			if($value) {
				$i=preg_match('%'.$options[3].'%',$value);
				if(!$i) {
					controller::$error=sprintf(LNGFieldIllegalValue,$options[1]);
					return false;
				}
			}
			break;
		case 'captcha':
			if($value!==$_SESSION['captcha']) {
				controller::$error=$options[1].' '.LNGwroteWrong;
				return;
			}
			break;
		case 'callback':
			$value=call_user_func_array($options[3],array($name,$value));
			if(controller::$error) return false;
			break;
		}
		return true;
	}
}
?>