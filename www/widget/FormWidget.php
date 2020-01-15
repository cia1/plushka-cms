<?php
namespace plushka\widget;
use plushka\core\Form;
use plushka\core\plushka;
use plushka\core\Widget;

/**
 * Произвольная контактная форма
 * @property-read string       $options Идентификатор формы
 *
 * @property-read Form    $form
 * @property-read array   $formData
 * @property-read array[] $field
 * @property-read string  $view
 */
class FormWidget extends Widget {

	public function __invoke(): bool { return true; }

	public function render($view): void {
		$db=plushka::db();
		$form=$db->fetchArrayOnceAssoc('SELECT title_'._LANG.' title,formView FROM frm_form WHERE id='.$this->options);
		if(!$form) return;
		//Загрузить список полей формы и загрузить их в класс form
		$items=$db->fetchArrayAssoc('SELECT id,title_'._LANG.' title,htmlType,data_'._LANG.' data,defaultValue,required FROM frm_field WHERE formId='.$this->options.' ORDER BY sort');
		$cnt=count($items);
		$f=plushka::form('form');
		for($i=0;$i<$cnt;$i++) {
			$title=$items[$i]['title'];
			if($items[$i]['required']) $title.='<span class="required">*</span>';
			$type=$items[$i]['htmlType'];
			if($type==='email') $type='text';
			if($type==='radio' || $type==='select') {
				$data=$items[$i]['data'];
				$data=explode('|',$data);
				for($y=0;$y<count($data);$y++) $data[$y]=[$data[$y],$data[$y]];
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

	public function adminLink(): array {
		return [
			['form.*','?controller=form&action=form&id='.$this->options,'setting','Настройка формы'],
			['form.*','?controller=form&action=field&id='.$this->options,'field','Поля формы']
		];
	}

}