<?php
/* Универсальная контактная форма
	ЧПУ: /form/ИД (actionIndex) - вывод формы; /form/ИД/success (actionSuccess) - сообщение после отправки формы
*/
class sController extends controller {

	public function __construct() {
		parent::__construct();
		$this->id=(int)$this->url[1]; //идентификатор формы
		if(!$this->id) core::error404();
		if(isset($this->url[2])) $this->url[1]=$this->url[2]; else $this->url[1]='Index';
		}

	/* Вывод формы */
	public function actionIndex() {
		core::import('model/form');
		$f=new mForm($this->id); //$this->id - идентификатор формы (таблица form)
		if($f->formView) { //если для формы задано индивидуальное представление
			$this->form=$f;
			$view=$f->formView;
		} else $view=$f;
		$this->pageTitle=$this->metaTitle=$f->title;
		return $view;
	}

	public function adminIndexLink() {
		return array(
			array('form.*','?controller=form&action=form&id='.$this->id,'setting','Настройка формы'),
			array('form.*','?controller=form&action=field&id='.$this->id,'field','Поля формы')
		);
	}

	public function actionIndexSubmit($data) {
		core::import('model/form');
		$m=new mForm();
		if(!$m->execute($this->id,$data)) return false; //false в случае ошибки или если задан скрипт обработки данных форм
		core::redirect('form/'.$this->id.'/success');
	}

	/* Выводит сообщение об успехе */
	public function actionSuccess() {
		$db=core::db();
		$form=$db->fetchArrayOnce('SELECT title,successMessage,redirect FROM frmForm WHERE id='.$this->id);
		if(!$form) core::error404();
		if($form[2]) core::redirect($form[2],$form[1]);
		if(!$form[1]) $this->message='<p>'.LNGWeGotYourMessage.'</p>'; else $this->message=$form[1];
		$this->redirect=$form[2];

		$this->pageTitle=$form[0];
		return 'Success';
	}

}
?>