<?php
/* Произвольная контактная форма
int $options - идентификатор формы */
class widgetForm extends widget {

	public function __invoke() { return true; }

	public function render() {
		$db=core::db();
		$form=$db->fetchArrayOnceAssoc('SELECT title,formView FROM frmForm WHERE id='.$this->options);
		if(!$form) return;
		//Загрузить список полей формы и загрузить их в класс form
		$items=$db->fetchArrayAssoc('SELECT id,title,htmlType,data,defaultValue,required FROM frmField WHERE formId='.$this->options.' ORDER BY sort');
		$cnt=count($items);
		$f=core::form('form');
		for($i=0;$i<$cnt;$i++) {
			$title=$items[$i]['title'];
			if($items[$i]['required']) $title.='<span class="required">*</span>';
			$type=$items[$i]['htmlType'];
			if($type=='email') $type='text';
			if($type=='radio' || $type=='select') {
				$data=$items[$i]['data'];
				$data=explode('|',$data);
				for($y=0;$y<count($data);$y++) $data[$y]=array($data[$y],$data[$y]);
				$f->field($type,'fld'.$items[$i]['id'],$title,$items[$i]['defaultValue'],$data);
			} else $f->field($type,'fld'.$items[$i]['id'],$title,$items[$i]['defaultValue']);
		}
		$f->field('submit','submit','Отправить');
		if($form['formView']) { //задано индивидуальное MVC-представление
			$this->form=$f;
			$this->formData=$form;
			$this->field=$items;
			$this->view=$form['formView'];
		} else $f->render('form/'.$this->options); //представления не задано
	}

	public function adminLink() {
		return array(
			array('form.*','?controller=form&action=form&id='.$this->options,'setting','Настройка формы'),
			array('form.*','?controller=form&action=field&id='.$this->options,'field','Поля формы')
		);
	}

}
?>