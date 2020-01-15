<?php
namespace plushka\admin\core;

/**
 * @property int $formId ИД формы
 */
class FormField extends \plushka\core\Model {

	function __construct($table=null,$db='db') {
		parent::__construct('frm_field');
		$this->multiLanguage();
	}

	protected function fieldList($isSave) {
		$fieldList='id,formId,title,htmlType,data,defaultValue,required';
		if(!$this->id) $fieldList.=',sort';
		return $fieldList;
	}

	protected function rule() {
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

	protected function beforeInsertUpdate($id=null) {
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