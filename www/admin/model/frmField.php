<?php
namespace plushka\admin\core;

plushka::import('core/model');
class frmField extends model {

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
		return array(
			'id'=>array('primary'),
			'formId'=>array('integer'),
			'sort'=>array('integer'),
			'title'=>array('string','название',true),
			'htmlType'=>array('string'),
			'data'=>array('string'),
			'defaultValue'=>array('string'),
			'required'=>array('boolean')
		);
	}

	protected function beforeInsertUpdate($id=null) {
		if(!$this->_data['id']) { //если это новое поле, то вычислить индекс сортировки (целое число)
			$sort=$this->db->fetchValue('SELECT max(sort) FROM frm_field WHERE formId='.$this->_data['formId']);
			$this->_data['sort']=++$sort;
		}
		//Значения для списков задаются в текстовом поле, сохраняются в БД строкой с разделителем "|"
		switch($this->_data['htmlType']) {
		case 'radio': case 'select':
			$this->_data['data']=str_replace(array("\n","\r"),array('|',''),$this->_data['value']);
			break;
		case 'file':
			$this->_data['data']=strtolower(str_replace(array('.',' '),'',$this->_data['fileType']));
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