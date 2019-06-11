<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;
use plushka;

/**
 * Универсальная модель. Может использоваться динамически или как базовый класс ActiveRecord
 * В динамическом варианте использования необходимо вызвать метод save(), передав ему правила валидации. Для статического режима, правила валидации определяются перегрузкой метода rule().
 * Модель поддерживает мультиязычность следующим образом:
 * Обозначения:
 *  мультиязычная таблица - вариант, когда для каждого языка имеется своя копия таблицы (язык указан в окончании имени таблицы),
 *  мультиязычное поле - одна таблица, но копии столбцов для каждого языка (язык указан в окончании имени столбца).
 * - Режим мультиязычности выключен (self::multiLanguage(false) ):
 *  для мультиязычных таблиц операции INSERT и DELETE проводятся только для одной таблицы,
 *  для мультиязычных столбцов и операции INSERT имена столбцов необходимо указывать без суффикса языка,
 *  для мультиязычных столбцов и операции UPDATE имена столбцов необходимо указывать с суффиксом языка.
 * - Режим мультиязычности включён (self::multiLanguage(true) ):
 *  операции INSERT и DELETE выполняются для всех копий таблиц, имена полей указываются без суффикса языка,
 *  операция UPDATE выполняется для одной мультиязычной таблицы, имена полей указываются без суффикса языка.
 */
class Model extends Validator {

	/** @var string Имя таблицы базы данных */
	protected $_table;
	/** @var string|null Имя первичного ключа (если есть) */
	protected $primaryAttribute;
	/** @var mysql|sqlite Экземпляр класса подключения к базе данных */
	protected $db;
	/** @var bool Режим мультиязычности. */
	protected $_multiLanguage=false;
	/**
	 * @var bool|string[] Информация о мультиязычности таблицы (определяется явтамитически):
	 * 	false - не мультиязычная, true - для каждого языка своя копия таблицы,
	 * array - одна копия таблицы, массив содержит список полей, имеющих копии для каждого языка
	 * @see self::_setLanguageDb()
	 */
	protected $_languageDb;
	/** @var string[] Список полей булевого типа (необходимо для корректного преобразования) */
	protected $_bool=array();

	/**
	 * @param string|null $table Имя таблицы базы данных, если не задано, то будет определяться из имени класса
	 * @param string $db Используемая СУБД: "db" (основная СУБД), "mysql" или "sqlite"
	 */
	public function __construct($table=null,$db='db') {
		if($table===null) {
			$className=preg_replace_callback('~[A-Z]~',function($letter) {
				return '_'.strtolower($letter[0]);
			},(new ReflectionClass($this))->getShortName());
		} else $this->_table=$table;
		if($db==='db') $this->db=plushka::db();
		elseif($db==='sqlite') $this->db=plushka::sqlite();
		elseif($db==='mysql') $this->db=plushka::mysql();
		else $this->db=$db;
	}

	/**
	 * Включает или выключает режим мультиязычности
	 * @param bool $value
	 */
	public function multiLanguage($value=true) {
		$this->_multiLanguage=(bool)$value;
	}

	/**
	 * Загружает данные из базы данных в модель
	 * Если в параметре $fieldList указана строка, то она воспринимается как список полей (допустимо указать "*").
	 * Если $fieldList не указан, то список полей будет взят из static::fieldList(false)
	 * @param string $where часть SQL-запроса "WHERE"
	 * @param array|null $fieldList Список полей, которые нужно загрузить
	 * @return bool Были или нет загружены данные модели
	 */
	public function load($where,$fieldList=null) {
		if($this->_multiLanguage===true && $this->_languageDb===null) $this->_setLanguageDb();
		if($fieldList!=='*') {
			if($fieldList===null) $fieldList=$this->fieldList(false);
			if($this->_multiLanguage===true) {
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
			}
			if(is_array($fieldList)) $fieldList=implde(',',$fieldList);
		}
		$this->_data=$this->db->fetchArrayOnceAssoc('SELECT '.$fieldList.' FROM `'.$this->_table.($this->_languageDb===true ? '_'._LANG : '').'` WHERE '.$where);
		if(!$this->_data) return false;
		//Если данамическое использование, мультиязычный режим и указаны все поля (*), то выбрать поля только для одного языка
		if($this->_multiLanguage===true && $fieldList==='*' && is_array($this->_languageDb)) {
			$lang=plushka::config('_core','languageList');
			foreach($this->_languageDb as $attribute) {
				$this->_data[$attribute]=$this->_data[$attribute.'_'._LANG];
				foreach($lang as $item) unset($this->_data[$attribute.'_'.$item]);
			}
		}
		return true;
	}

	/**
	 * Загружает данные в модель по первичному ключу
	 * @param int $id Значение первичного ключа
	 * @param string|null Список необходимых полей
	 * @return bool Были ли загруженны данные
	 * @see self::load()
	 */
	public function loadById($id,$fieldList=null) {
		$this->_data['id']=(int)$id;
		return $this->load('id='.$this->_data['id'],$fieldList);
	}

	/**
	 * Выполняет валидацию данных модели
	 * Если параметр $rule не задан, то правила будут взяты из static::rule().
	 * @param array|null Правила валидации
	 * @return bool TRUE - валидация прошла успешно, FALSE - во вермя проверки возникли ошибки (@see plushka::error())
	 * @see /core/validator.php
	 */
	public function validate($rule=null) {
		if($rule===null) {
			$rule=explode(',',$this->fieldList(true));
			$rule=array_intersect_key($this->rule(),array_combine($rule,$rule));
		}
		if(parent::validate($rule)===false) return false;
		if(!$this->primaryAttribute) $this->primaryAttribute=false; //обозначить, если первичного ключа нет в правилах
		return true;
	}

	/**
	 * Сохраняет модель в базу данных, выполняя запрос INSERT или UPDATE
	 * Если режим мультиязчности включён для данной модели, то может быть выполнено несколько запросов INSERT.
	 * @param array|null $valiadate Правила валидации (@see self::validate())
	 * @param string|null $primaryAttribute Имя первичного ключа
	 * @return bool Была ли сохранена запись
	 */
	public function save($validate=null,$primaryAttribute=null) {
		//Валидация
		if($validate===null || $validate===true || is_string($validate)) {
			if($validate===null || $validate===true) $validate=$this->fieldList(true);
			if(is_string($validate)) $validate=explode(',',$validate);
			if($validate[0]==='*') $validate=$this->rule();
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
		if($validate===false) { //валидация не требуется, определить список полей
			$validate=explode(',',$this->fieldList(true));
			if($validate[0]==='*') $validate=array_keys($this->rule());
			foreach($validate as $i=>$item) { //оставить только поля, для которых явно задано значение
				if(isset($this->_data[$item])===false) unset($validate[$i]);
			}
		}
		if($this->primaryAttribute && isset($this->_data[$this->primaryAttribute])) $id=$this->_data[$this->primaryAttribute];
		else $id=null;
		if($primaryAttribute!==null) { //Удалить первичный ключ из списка полей, за исключением случая, когда нужно выполнить INSERT с заранее определённым значением первичного ключа
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

	/**
	 * Удаляет запись по первичному ключу
	 * Если режим мультиязычности включён, удаляет запись из всех мультиязычных таблиц
	 * @param int|null $id Значение первичного ключа, если не указано, будует использовано static::$_data['id']
	 * @param bool $affected Если TRUE, будет возвращенно количество удалённых записей
	 * @return bool|int Количество удалённых записей или true
	 */
	public function delete($id=null,$affected=false) {
		if($id===null) $id=$this->id; else $id=(int)$id;
		if($this->_languageDb===null) $this->_setLanguageDb();

		if($this->_multiLanguage===true && $this->_languageDb===true) {
			$lang=plushka::config('_core','languageList');
			foreach($lang as $item) {
				$this->db->query('DELETE FROM '.$this->_table.'_'.$item.' WHERE id='.$id);
			}
		} else $this->db->query('DELETE FROM '.$this->_table.($this->_languageDb===true ? '_'._LANG : '').' WHERE id='.$id);
		if($id==$this->id) $this->init();
		if($affected===false) return true;
		if($this->db->affected()) return true; else return false;
	}

	/**
	 * Валидатор первичного ключа
	 * @param string $attribute Имя первичного ключа
	 * @param mixed Настройки валидатора
	 * @return bool
	 */
	protected function validatePrimary($attribute,$setting) {
		$this->primaryAttribute=$attribute;
		if(!isset($this->_data[$attribute]) || !$this->_data[$attribute]) $this->_data[$attribute]=null;
		return true;
	}

	/**
	 * Обработчик события, генерируемого после выполнения операции(й) INSERT
	 * @param int|null $id Значение первичного ключа, если в таблице есть первичный ключ
	 * @return bool
	 */
	protected function afterInsert($id=null) { return true; } //триггер, может быть перегружен

	/**
	 * Обработчик события, генерируемого после выполенения операции UPDATE
	 * @param int|null Значение первичного ключа, если в таблице есть первичный ключ
	 * @return bool
	 */
	protected function afterUpdate($id=null) { return true; } //триггер, может быть перегружен

	/**
	 * Возвращает массив правил валидации
	 * @return array[]
	 */
	protected function rule() {
		die('You have to override model::rule() to use class this way.');
	}

	/**
	 * Возвращает список полей для операций SELECT и INSERT/UPDATE
	 * @param bool $isSave FALSE - список полей для операции SELECT, TRUE - список полей для INSERT/UPDATE
	 */
	protected function fieldList($isSave) {
		return '*';
	}

	/**
	 * Подготавливает self::$_multilanguage, содеращий информацию о мультиязычности таблицы для данной модели:
	 * FALSE - не мультиязычная таблица, TRUE - мультиязычная таблица, ARRAY - список мультиязычных полей
	 */
	private function _setLanguageDb() {
		$f=plushka::path().'cache/language-database.php';
		if(!file_exists($f)) Cache::languageDatabase();
		$lang=plushka::config('../cache/language-database',$this->_table);
		if($lang===null) $lang=false;
		$this->_languageDb=$lang;
	}

	//Собирает и выполняет SQL-запрос INSERT
	private function _insert($fieldList,$primary) {
		if($this->_languageDb) {
			$languageList=plushka::config();
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
				if($this->_multiLanguage===true) {
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
		if($this->_languageDb===true) {
			foreach($languageList as $i=>$item) {
				if(!$i) { //первичный ключ определить только один раз
					$query='INSERT INTO `'.$this->_table.'_'.$item.'` ('.$s1.') VALUES ('.$s2.')';
					if(!$this->db->query($query)) return false;
					$this->_data[$primary]=$this->db->insertId(); //обновить значение первичного ключа
					$s1.=',`'.$primary.'`';
					$s2.=','.$this->db->escape($this->_data[$primary]);
				} else {
					$query='INSERT INTO `'.$this->_table.'_'.$item.'` ('.$s1.') VALUES ('.$s2.')';
					if(!$this->db->query($query)) return false;
				}
			}
		} else {
			$query='INSERT INTO `'.$this->_table.'` ('.$s1.') VALUES ('.$s2.')';
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
			if($this->_multiLanguage===true && is_array($this->_languageDb) && in_array($name,$this->_languageDb)) $s.='_'._LANG;
			if($this->_data[$name]===null) $value='null';
			elseif($this->_data[$name]===true) $value='1';
			elseif($this->_data[$name]===false) $value='0';
			else $value=$this->db->escape($this->_data[$name]);
			$s.='`='.$value;
		}
		if($this->_languageDb===true) $s='UPDATE `'.$this->_table.'_'._LANG.'` SET '.$s.' WHERE '.$primary.'='.$this->db->escape($id);
		else $s='UPDATE `'.$this->_table.'` SET '.$s.' WHERE '.$primary.'='.$this->db->escape($id);
		if(!$this->db->query($s)) return false;
		return $this->afterUpdate($this->$primary); //триггер "после UPDATE"
	}

}