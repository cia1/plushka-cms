<?php
namespace plushka\admin\model;
use plushka\core\Model;

/**
 * AR-модель универсальной контактной формы
 * @property int $formId ИД формы
 */
class FormField extends Model {

	function __construct(/** @noinspection PhpUnusedParameterInspection */ string $table=null,string $db='db') {
		parent::__construct('frm_field',$db);
		$this->multiLanguage();
	}

	/** @inheritDoc */
	protected function fieldListLoad(): string {
		return 'id,formId,title,htmlType,data,defaultValue,required';
	}

	/** @inheritDoc */
	protected function fieldListSave(): string {
		return 'id,formId,title,htmlType,data,defaultValue,required,sort';
	}

	protected function rule(): array {
		return [
			'id'=>['primary'],
			'formId'=>['integer'],
			'sort'=>['integer'],
			'title'=>['string','название',true],
			'htmlType'=>['string'],
			'data'=>['string'],
			'defaultValue'=>['string'],
			'required'=>['boolean']
		];
	}

	protected function beforeInsertUpdate(/** @noinspection PhpUnusedParameterInspection */ $id=null) {
		if(!$this->_data['id']) { //если это новое поле, то вычислить индекс сортировки (целое число)
			$sort=$this->db->fetchValue('SELECT max(sort) FROM frm_field WHERE formId='.$this->_data['formId']);
			$this->_data['sort']=++$sort;
		}
		//Значения для списков задаются в текстовом поле, сохраняются в БД строкой с разделителем "|"
		switch($this->_data['htmlType']) {
			case 'radio':
			case 'select':
				$this->_data['data']=str_replace(["\n","\r"],['|',''],$this->_data['value']);
				break;
			case 'file':
				$this->_data['data']=strtolower(str_replace(['.',' '],'',$this->_data['fileType']));
				break;
			case 'captcha':
				$this->_data['defaultValue']=null;
				$this->_data['required']=true;
				break;
			default:
				$this->_data['data']=null;
		}
		return true;
	}

}