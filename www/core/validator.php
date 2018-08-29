<?php class validator {

	protected $_bool=array(); //список булевых полей (для корректного преобразования)
	protected $_data=array(); //содержит набор данных

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

	/*
	Выполняет валидацию всех данных
	$rule - правила валидации: null - используется static::rule(), массив - воспринимается как правила валидации
	rule: [type, title, required]
	*/
	public function validate($rule=null) {
		core::language('global');
		if($rule===null) $rule=$this->rule();
		foreach($rule as $attribute=>$item) {
			$value=isset($this->_data[$attribute]) ? $this->_data[$attribute] : null;
			if(isset($item[2]) && $item[2] && ($value===null || $value==='')) { //required=true
				core::error(sprintf(LNGFieldCannotByEmpty,$item[1]));
				return false;
			}
			if($this->{'validate'.ucfirst($item[0])}($attribute,$item)===false) return false;
		}
		return true;
	}

	protected function validateBoolean($attribute,$setting) {
		if(!isset($this->_data[$attribute])) $this->_data[$attribute]=null;
		$value=&$this->_data[$attribute];
		$this->_bool[]=$attribute;
		if($value) $value=true; else $value=false;
		return true;
	}

	protected function validateCaptcha($attribute,$setting) {
		if((int)$this->_data[$attribute]!==$_SESSION['captcha']) {
			core::error($setting[1].' '.LNGwroteWrong);
			return;
		}
		return true;
	}

	protected function validateCallback($attribute,$setting) {
		$this->_data[$attribute]=call_user_func_array($setting[3],array($this->$attribute,$attribute));
		if(core::error()) return false;
		return true;
	}

	protected function validateDate($attribute,$setting) {
		$value=&$this->_data[$attribute];
		if($value==='') {
			$value=null;
			return true;
		}
		if(!is_numeric($value)) $value=strtotime($value);
		if($value<1) {
			core::error(sprintf(LNGFieldHasBeDate,$setting[1]));
			return false;
		}
		return true;
	}

	protected function validateEmail($attribute,$setting) {
		$value=$this->_data[$attribute];
		if(filter_var($value,FILTER_VALIDATE_EMAIL)===false) {
			core::error(sprintf(LNGFieldHasBeEMail,$setting[1]));
			return false;
		}
		return true;
	}

	protected function validateFloat($attribute,$setting) {
		$value=&$this->_data[$attribute];
		if($value==='') $value=null; else $value=(float)$value;
		if($value) {
			if(isset($setting['min']) && $setting['min']>$value) {
				core::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
			if(isset($setting['max']) && $setting['max']<$value) {
				core::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
		}
	}

	protected function validateHtml($attribute,$setting) {
		return true;
	}

	protected function validateImage($attribute,$setting) {
		//setting[]: 'minWidth','minHeight','maxWidth','maxHeight'
		core::import('core/picture');
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
				core::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
			if(isset($setting['max']) && $setting['max']<$value) {
				core::error(sprintf(LNGFieldIllegalValue,$setting[1]));
				return false;
			}
		}
	}

	protected function validateLatin($attribute,$setting) {
		$value=$this->_data[$attribute];
		$i=preg_match('/^[a-zA-Z0-9\-_]*?$/',$value);
		if(!$i) {
			core::error(sprintf(LNGFieldCanByLatin,$setting[1]));
			return false;
		}
		if(isset($setting['max']) && strlen($value)>$setting['max']) {
			core::error(sprintf(LNGFieldIllegalValue,$setting[1]));
			return false;
		}
		return true;
	}

	protected function validteRegular($attribute,$setting) {
		$value=$this->_data[$attribute];
		if($value) {
			if(!preg_match('%'.$setting[3].'%',$value)) {
				core::error(sprintf(LNGFieldIllegalValue,$setting[1]));
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
					core::error(sprintf(LNGFieldTextTooShort,$setting[1]));
					return false;
			}
			if(isset($setting['max']) && $setting['max']<mb_strlen($value,'UTF-8')) {
				core::error(sprintf(LNGFieldTextTooLong,$setting[1]));
				return false;
			}
		}
		return true;
	}



	private function _picture($image,$setting) {
		$picture=new picture($image);
		if(core::error()) return false;
		if(isset($setting['minWidth']) || isset($setting['maxWidth'])) {
			$w=$picture->width();
			$h=$picture->height();
			if(isset($setting['minWidth']) && $w<$setting['minWidth']) {
				core::error(sprintf(LNGImageWidthCannotBeLessPixels,$setting['minWidth']));
				return false;
			}
			if(isset($setting['maxWidth']) && $w>$setting['maxWidth']) {
				core::error(sprintf(LNGImageWidthCannotBeMorePixels,$setting['maxWidth']));
				return false;
			}
			if(isset($setting['minHeight']) && $h<$setting['minHeight']) {
				core::error(sprintf(LNGImageHeightCannotBeLessPixels,$setting['minHeight']));
				return false;
			}
			if(isset($setting['maxHeight']) && $h>$setting['maxHeight']) {
				core::error(sprintf(LNGImageHeightCannotBeMorePixels,$setting['maxHeight']));
				return false;
			}
		}
		return $picture;
	}

}