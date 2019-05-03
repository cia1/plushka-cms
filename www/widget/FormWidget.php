<?php
namespace plushka\widget;
use plushka;

/* Произвольная контактная форма
int $options - идентификатор формы */
class FormWidget extends \plushka\core\Widget {

	public function __invoke() { return true; }

	public function render($view) {
		$db=plushka::db();
		$form=$db->fetchArrayOnceAssoc('SELECT title,formView FROM frm_form WHERE id='.$this->options);
		if(!$form) return;
		//Загрузить список полей формы и загрузить их в класс form
		$items=$db->fetchArrayAssoc('SELECT id,title,htmlType,data,defaultValue,required FROM frm_field WHERE formId='.$this->options.' ORDER BY sort');
		$cnt=count($items);
		$f=plushka::form('form');
		for($i=0;$i<$cnt;$i++) {
			$title=$items[$i]['title'];
			if($items[$i]['required']) $title.='<span class="required">*</span>';
			$type=$items[$i]['htmlType'];
			if($type=='email') $type='text';
			if($type=='radio' || $type=='select') {
				$data=$items[$i]['data'];
				$data=explode('|',$data);
				for($y=0;$y<count($data);$y++) $data[$y]=array($data[$y],$data[$y]);
				$f->$type('fld'.$items[$i]['id'],$title,$data,$items[$i]['defaultValue']);
			} else $f->$type('fld'.$items[$i]['id'],$title,$items[$i]['defaultValue']);
		}
		$f->submit(LNGSend);
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