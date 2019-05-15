<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;
use plushka;
use plushka\core\Picture;

/**
 * Валидатор и фильтр данных.
 * Этот класс не только проверяет, но и производит фильтрацию входных данных в соответствии с правилами валидации. Обычно используется для проверки входных данных, полученных от посетителей сайта.
 * Правила валидации - это массив, где ключ - имя атрибута, значение - массив с параметрами валидации:
 * [0] string - имя валидатора (тип поля),
 * [1] string - название атрибута, нужно для формирования сообщения об ошибке (кроме bool)
 * [2] bool - обязательное поле.
 * Список валидаторов и дополнительные параметры для каждого
 * - 'bool' (да/нет)
 * - 'captcha' (защита от роботов)
 * - 'callback' (проверка callback-функцией): [3] - 
 * 

 */
class Validator {

	/** @var string[] Список булевых полей, нужен для корректного преобразования */
	protected $_bool=array();
	/** @var mixed[] Набор валидируемых данных */
	protected $_data=array();

	/**
	 * Устанавливает значение атрибута
	 * @param string $attribute имя атрибута
	 * @param mixed $value Значение атрибута
	 */
	public function __set(string $attribute,$value) {
		$this->_data[$attribute]=$value;
	}

	/**
	 * Возвращает значение атрибута
	 * @param string $attribute Имя атрибута
	 * @return mixed|null Значение атрибута или NULL, если не задан
	 */
	public function __get(string $attribute) {
		return $this->_data[$attribute] ?? null;
	}

	/**
	 * Устанавливает сразу все проверяемые данные
	 * @param array $data массив данных в формате ключ-значение
	 */
	public function set(array $data) {
		$this->_data=$data;
	}

	/**
	 * Возвращает все валидируемые данные
	 * @return array
	 */
	public function get(): array {
		return $this->_data;
	}

	/**
	 * Очищает данные
	 */
	public function init() {
		$this->_data=array();
	}

	/**
	 * Выполняет валидацию и фильтрацию всех данных
	 * @param array|null Правила валидации: null - взять из static::rule(), массив - правила в формате ключ-значение
	 * @return bool Валидны ли данные
	*/
	public function validate($rule=null): bool {
		plushka::language('global');
		if($rule===null) $rule=$this->rule();
		foreach($rule as $attribute=>$item) {
			$value=isset($this->_data[$attribute]) ? $this->_data[$attribute] : null;
			if(isset($item[2]) && $item[2] && ($value===null || $value==='')) { //required=true
				plushka::error(sprintf(LNGFieldCannotByEmpty,$item[1]));
				return false;
			}
			if($this->{'validate'.ucfirst($item[0])}($attribute,$item)===false) return false;
		}
		return true;
	}

	/**
	 * Валидатор булевого значения
	 * @param string $attribute Имя атрибута
	 * @param array $setting не используется
	 * @return bool Результат валидации
	 */
	protected function validateBoolean(string $attribute,$setting): bool {
		if(isset($this->_data[$attribute])===false) $this->_data[$attribute]=false;
		$this->_bool[]=$attribute;
		$this->_data[$attribute]==(bool)$this->_data[$attribute];
		return true;
	}

	/**
	 * Валидатор защиты от роботов
	 * @param string $attribute Имя атрибута
	 * @param array $setting: [1] - имя параметра для формирования сообщения об ошибке
	 * @return bool Результат валидации
	 */
	protected function validateCaptcha(string $attribute,$setting): bool {
		if((int)$this->_data[$attribute]!==$_SESSION['captcha']) {
			plushka::error($setting[1].' '.LNGwroteWrong);
			return false;
		}
		return true;
	}

	/**
	 * Валидатор callback-функцией
	 * @param string $attribute Имя атрибута
	 * @param array $setting: [3] - callback-функция
	 * @return bool Результат валидации
	 */
	protected function validateCallback(string $attribute,array $setting): bool {
		$this->_data[$attribute]=call_user_func_array($setting[3],array($this->$attribute));
		if(plushka::error()) return false;
		return true;
	}

	protected function validateDate($attribute,$setting) {
		$value=&$this->_data[$attribute];
		if($value==='' || $value===null) {
			$value=null;
			return true;
		}
		if(ctype_digit($value)===false) $value=strtotime($value);
		$value=(int)$value;
		if($value<1) {
			plushka::error(sprintf(LNGFieldHasBeDate,$setting[1]));
			return false;
		}
		return true;
	}

	protected function validateEmail($attribute,$setting) {
		$value=$this->_data[$attribute];
		if(filter_var($value,FILTER_VALIDATE_EMAIL)===false) {
			plushka::error(sprintf(LNGFieldHasBeEMail,$setting[1]));
			return false;
		}
		return true;
	}

	protected function validateFloat($attribute,$setting) {
		$value=&$this->_data[$attribute];
		if($value==='') $value=null; else $value=(float)$value;
		if($value) {
			if(isset($setting['min']) && $setting['min']>$value) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
			if(isset($setting['max']) && $setting['max']<$value) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
		}
	}

	protected function validateHtml($attribute,$setting) {
		return true;
	}

	protected function validateImage($attribute,$setting) {
		//setting[]: 'minWidth','minHeight','maxWidth','maxHeight'
		$value=&$this->_data[$attribute];
		if(is_array($value) && !$setting[2] && !$setting['size']) {
			$value=null;
			return true;
		}
		if(is_array($value) && isset($value[0])) foreach($value as $i=>$item) {
			$item=self::_picture($item,$setting);
			if(!$item) return false;
			$value[$i]=$item;
		} else {
			$value=self::_picture($value,$setting);
			if(!$value) return false;
		}
		return true;
	}

	protected function validateInteger($attribute,$setting) {
		$value=&$this->_data[$attribute];
		if($value==='') $value=null; elseif($value!==null) $value=(int)$value;
		if($value) {
			if(isset($setting['min']) && $setting['min']>$value) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
			if(isset($setting['max']) && $setting['max']<$value) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
		}
	}

	protected function validateLatin($attribute,$setting) {
		$value=$this->_data[$attribute];
		$i=preg_match('/^[a-zA-Z0-9\-_]*?$/',$value);
		if(!$i) {
			plushka::error(sprintf(LNGFieldCanByLatin,$setting[1]));
			return false;
		}
		if(isset($setting['max']) && strlen($value)>$setting['max']) {
			plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
			return false;
		}
		return true;
	}

	protected function validteRegular($attribute,$setting) {
		$value=$this->_data[$attribute];
		if($value) {
			if(!preg_match('%'.$setting[3].'%',$value)) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
		}
		return true;
	}

	protected function validateString($attribute,$setting) {
		$value=&$this->_data[$attribute];
		$value=strip_tags($value);
		if(!isset($setting['trim']) || $setting['trim']) $value=trim($value);
		if($value) {
			if(isset($setting['min']) && $setting['min']>mb_strlen($value,'UTF-8')) {
					plushka::error(sprintf(LNGFieldTextTooShort,$setting[1]));
					return false;
			}
			if(isset($setting['max']) && $setting['max']<mb_strlen($value,'UTF-8')) {
				plushka::error(sprintf(LNGFieldTextTooLong,$setting[1]));
				return false;
			}
		}
		return true;
	}



	private function _picture($image,$setting) {
		$picture=new Picture($image);
		if(plushka::error()) return false;
		if(isset($setting['minWidth']) || isset($setting['maxWidth'])) {
			$w=$picture->width();
			$h=$picture->height();
			if(isset($setting['minWidth']) && $w<$setting['minWidth']) {
				plushka::error(sprintf(LNGImageWidthCannotBeLessPixels,$setting['minWidth']));
				return false;
			}
			if(isset($setting['maxWidth']) && $w>$setting['maxWidth']) {
				plushka::error(sprintf(LNGImageWidthCannotBeMorePixels,$setting['maxWidth']));
				return false;
			}
			if(isset($setting['minHeight']) && $h<$setting['minHeight']) {
				plushka::error(sprintf(LNGImageHeightCannotBeLessPixels,$setting['minHeight']));
				return false;
			}
			if(isset($setting['maxHeight']) && $h>$setting['maxHeight']) {
				plushka::error(sprintf(LNGImageHeightCannotBeMorePixels,$setting['maxHeight']));
				return false;
			}
		}
		return $picture;
	}

}