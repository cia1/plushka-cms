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
 * - 'callback' (проверка callback-функцией): [3] - callback-функция
 * - 'date' (дата/время): может быть в формате UNIXTIME или строкой, приемлемой для strtotime. Преобразует дату к UNIXTIME или NULL
 * - 'email' (адрес электронной почты)
 * - 'float' (число с плавающей точкой): [min], [max] - ограничение на допустимое значение
 * - 'image' (изображение): [minWidth], [maxWidth], [minHeight], [maxHeight] - ограничение по размеру изображения
 * - 'integer' (целое число): [min], [max] - предельные возможные значения
 * - 'latin' (строка, содержащая латинские буквы, цифры, "_" и "-"): [max] - максимальная длина строки
 * - 'regular' (регулярное выражение): [3] - рег. выражение
 * - 'string' (произвольная строка): [trim] - обрезать пробелы, [min], [max] - ограничение длины строки
 */
class Validator {

	/** @var string[] Список булевых полей, нужен для корректного преобразования */
	protected $_bool=[];
	/** @var mixed[] Набор валидируемых данных */
	protected $_data=[];

	/**
	 * Устанавливает значение атрибута
	 * @param string $attribute имя атрибута
	 * @param mixed $value Значение атрибута
	 */
	public function __set(string $attribute,$value): void {
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
	public function set(array $data): void {
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
	public function init(): void {
		$this->_data=[];
	}

	/**
	 * Выполняет валидацию и фильтрацию всех данных
	 * @param array|null Правила валидации: null - взять из static::rule(), массив - правила в формате ключ-значение
	 * @return bool Валидны ли данные
	*/
	public function validate(array $rule=null): bool {
		plushka::language('global');
		if($rule===null) $rule=$this->rule();
		foreach($rule as $attribute=>$item) {
			if(isset($this->_data[$attribute])===false) $this->_data[$attribute]=null;
			if(isset($item[2])===false) $item[2]=null;
			if($this->_data[$attribute]===null || $this->_data[$attribute]==='') {
				if($item[2]===true || ($item[0]==='captcha' && $item[2]===null)) { //cannot be empty
					plushka::error(sprintf(LNGFieldCannotByEmpty,$item[1]));
					return false;
				}
				continue;
			}
			$item[2]=(bool)$item[2];
			if($this->{'validate'.ucfirst($item[0])}($attribute,$item)===false) return false;
		}
		return true;
	}

	/**
	 * Валидатор булевого значения
	 * @param string $attribute Имя атрибута
	 * @param array|null $setting не используется
	 * @return bool Результат валидации
	 */
	protected function validateBoolean(string $attribute,array $setting=null): bool {
		$this->_bool[]=$attribute;
		$this->_data[$attribute]=(bool)$this->_data[$attribute];
		return true;
	}

	/**
	 * Валидатор защиты от роботов
	 * @param string $attribute Имя атрибута
	 * @param array $setting: [1] - имя параметра
	 * @return bool Результат валидации
	 */
	protected function validateCaptcha(string $attribute,array $setting): bool {
		$this->_data[$attribute]=(int)$this->_data[$attribute];
		if($this->_data[$attribute]!==$_SESSION['captcha']) {
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
		$this->_data[$attribute]=call_user_func_array($setting[3],[$this->$attribute]);
		if(plushka::error()) return false;
		return true;
	}

	/**
	 * Валидатор даты, преобразует к UNIXTIME
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута
	 * @return bool Результат валидации
	 */
	protected function validateDate(string $attribute,array $setting): bool {
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

	/**
	 * Валидатор адреса электронной почты
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута
	 * @return bool Результат валидации
	 */
	protected function validateEmail(string $attribute,array $setting): bool {
		if(filter_var($this->$attribute,FILTER_VALIDATE_EMAIL)===false) {
			plushka::error(sprintf(LNGFieldHasBeEMail,$setting[1]));
			return false;
		}
		return true;
	}

	/**
	 * Валидатор числа с плавующей точкой, преобразует к float
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута, [min], [max] - мин. и макс. значения
	 * @return bool Результат валидации
	 */
	protected function validateFloat($attribute,array $setting): bool {
		$this->_data[$attribute]=(float)$this->_data[$attribute];
		$value=$this->_data[$attribute];
		if(isset($setting['min'])===true && $setting['min']>$value) {
			plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
			return false;
		}
		if(isset($setting['max'])===true && $setting['max']<$value) {
			plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
			return false;
		}
		return true;
	}

	/**
	 * Валидатор HTML
	 * @param string $attribute Имя атрибута
	 * @param array|null $setting не используется
	 * @return bool Результат валидации
	 */
	protected function validateHtml(string $attribute,array $setting=null): bool {
		return true;
	}

	/**
	 * Валидатор изображения, полученного из файла. преобразует к Picture
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута, [2] - обязательное поле, [minWidth], [maxWidth], [minHeight], [maxHeight] - ограничене размера изображения
	 * @return bool Результат валидации
	 */
	protected function validateImage(string $attribute,array $setting): bool {
		$value=&$this->_data[$attribute];
		if($setting[2]===false && is_array($value)===true) {
			if(isset($value['size'])===true && $value['size']===0) {
				$value=null;
				return true;
			}
		}
		if(is_array($value)===true && isset($value[0])===true) foreach($value as $i=>$item) {
			$item=self::_picture($item,$setting);
			if($item===null) return false;
			$value[$i]=$item;
		} else {
			$value=self::_picture($value,$setting);
			if($value===null) return false;
		}
		return true;
	}

	/**
	 * Валидатор целого числа, преобразует к INT или NULL. Различает NULL и 0.
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута, [min], [max] - ограничене предельных значений
	 * @return bool Результат валидации
	 */
	protected function validateInteger(string $attribute,array $setting): bool {
		$value=&$this->_data[$attribute];
		if($value==='') $value=null; elseif($value!==null) $value=(int)$value;
		if($value!==null) {
			if(isset($setting['min'])===true && $setting['min']>$value) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
			if(isset($setting['max'])===true && $setting['max']<$value) {
				plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
		}
		return true;
	}

	/**
	 * Валидатор строк, содержащих только латинские буквы, цифры и "_", "-"
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута, [max] - максимальная длина строки
	 * @return bool Результат валидации
	 */
	protected function validateLatin(string $attribute,array $setting): bool {
		$value=$this->_data[$attribute];
		$i=preg_match('/^[a-zA-Z0-9\-_]*?$/',$value);
		if($i===false) {
			plushka::error(sprintf(LNGFieldCanByLatin,$setting[1]));
			return false;
		}
		if(isset($setting['max'])===true && strlen($value)>$setting['max']) {
			plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
			return false;
		}
		return true;
	}

	/**
	 * Валидация согласно регулярному выражению
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута, [3] - регулярное выражение
	 * @return bool Результат валидации
	 */
	protected function validateRegular(string $attribute,array $setting): bool {
		$value=$this->_data[$attribute];
		if(preg_match('~'.$setting[3].'~',$value)===0) {
			plushka::error(sprintf(LNGFieldIllegalValue,$setting[1]));
			return false;
		}
		return true;
	}

	/**
	 * Валидация произвольной строки. Вырезает теги.
	 * @param string $attribute Имя атрибута
	 * @param array $setting [1] - название атрибута, [trim] - обрезать пробелы, [min], [max] - ограничение длины строки
	 * @return bool Результат валидации
	 */
	protected function validateString(string $attribute,array $setting): bool {
		$value=&$this->_data[$attribute];
		$value=strip_tags($value);
		if(isset($setting['trim'])===true && $setting['trim']===true) $value=trim($value);
		if(isset($setting['min'])===true && $setting['min']>mb_strlen($value,'UTF-8')) {
			plushka::error(sprintf(LNGFieldTextTooShort,$setting[1]));
			return false;
		}
		if(isset($setting['max'])===true && $setting['max']<mb_strlen($value,'UTF-8')) {
			plushka::error(sprintf(LNGFieldTextTooLong,$setting[1]));
			return false;
		}
		return true;
	}



	private function _picture($image,array $setting): ?Picture {
		$picture=new Picture($image);
		if(plushka::error()) return null;
		if(isset($setting['minWidth'])===true || isset($setting['maxWidth'])===true || isset($setting['minHeight'])===true || isset($setting['maxHeight'])===true) {
			$w=$picture->width();
			$h=$picture->height();
			if(isset($setting['minWidth'])===true && $w<$setting['minWidth']) {
				plushka::error(sprintf(LNGImageWidthCannotBeLessPixels,$setting['minWidth']));
				return null;
			}
			if(isset($setting['maxWidth'])===true && $w>$setting['maxWidth']) {
				plushka::error(sprintf(LNGImageWidthCannotBeMorePixels,$setting['maxWidth']));
				return null;
			}
			if(isset($setting['minHeight'])===true && $h<$setting['minHeight']) {
				plushka::error(sprintf(LNGImageHeightCannotBeLessPixels,$setting['minHeight']));
				return null;
			}
			if(isset($setting['maxHeight'])===true && $h>$setting['maxHeight']) {
				plushka::error(sprintf(LNGImageHeightCannotBeMorePixels,$setting['maxHeight']));
				return null;
			}
		}
		return $picture;
	}

}