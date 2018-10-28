<?php
/* Библиотека универсальной формы */
class mForm {

	private $_id;

	/* Создаёт форму и сохраняет в базе данных
	Параметры: string $title - заголовок формы; string $subject - тема письма; string $successMessage - сообщение после отправки данные формы; string $email - почта, на которую нужно отправить данные формы */
	public function create($title,$subject,$script=null,$successMessage=null,$email=null) {
		if(!$email) $email='cfg';
		$data=array(
			'title'=>$title,
			'email'=>$email,
			'subject'=>$subject,
			'successMessage'=>$successMessage,
			'script'=>$script
		);
		$m=core::model('frm_form');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'title'=>array('string'),
			'email'=>array('string'),
			'subject'=>array('string'),
			'successMessage'=>array('string'),
			'script'=>array('string')
		))) return false;
		$this->_id=$m->id; //Идентификатор формы для последующей работы
		return $this->_id;
	}

	/* Добавляет к форме текстовое поле */
	public function text($title,$required=false,$default='') {
		return $this->_field($title,'text',null,$default,$required);
	}

	/* Добавляет к форме группу переключателей */
	public function radio($title,$list,$default=null) {
		if(is_array($list)) $list=implode('|',$list);
		return $this->_field($title,'radio',$list,$default,false);
	}

	/* Добавляет к форме выдающий список */
	public function select($title,$list,$required=false,$default=null) {
		if(is_array($list)) $list=implode('|',$list);
		return $this->_field($title,'select',$list,$default,false);
	}

	/* Добавляет к форме чекбокс */
	public function checkbox($title,$default=null) {
		return $this->_field($title,'checkbox',null,$default,false);
	}

	/* Добавляет к форме многострочное текстовое поле */
	public function textarea($title,$required=false,$default='') {
		return $this->_field($title,'textarea',null,$default,$required);
	}

	/* Добавляет к форме поле для ввода адреса электронной почты */
	public function email($title,$required=false,$default='cfg') {
		return $this->_field($title,'email',null,$default,$required);
	}

	/* Добавляет к форме поле для загрузки файла */
	public function file($title,$required=false) {
		return $this->_field($title,'file',null,null,$required);
	}

	/* Возвращает идентификатор формы */
	public function id() {
		return $this->_id;
	}

	/* Удаляет форму */
	public static function drop($id) {
		$db=core::db();
		$db->query('DELETE FROM frm_field WHERE formId='.$id);
		$db->query('DELETE FROM frm_form WHERE id='.$id);
		return true;
	}

	/* Добавляет к форме поле */
	private function _field($title,$htmlType,$data,$defaultValue,$required) {
		static $sort;
		$sort++;
		if($required) $required=true; else $required=false;
		$m=core::model('frm_field');
		$data=array(
			'formId'=>$this->_id,
			'title'=>$title,
			'htmlType'=>$htmlType,
			'data'=>$data,
			'defaultValue'=>$defaultValue,
			'required'=>(bool)$required,
			'sort'=>$sort
		);
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'formId'=>array('integer'),
			'title'=>array('string','заголовок',true,'max'=>25),
			'htmlType'=>array('string'),
			'data'=>array('string'),
			'defaultValue'=>array('string'),
			'required'=>array('integer'),
			'sort'=>array('integer')
		))) return false;
		return $m->id;
	}

}